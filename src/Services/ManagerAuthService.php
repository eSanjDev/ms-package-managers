<?php

namespace Esanj\Manager\Services;

use Carbon\Carbon;
use Esanj\AuthBridge\Contracts\AuthBridgeServiceInterface;
use Esanj\AuthBridge\DTOs\TokenData;
use Esanj\AuthBridge\Exceptions\TokenExchangeException;
use Esanj\Manager\Models\Manager;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class ManagerAuthService
{
    private const RENEWAL_PREFIX = 'manager_renewal_';
    private const RENEWAL_SHARE_SECONDS = 30;
    private const RENEWAL_LOCK_SECONDS = 15;
    private const RENEWAL_WAIT_SECONDS = 10;

    public function __construct(
        protected ManagerService $managerService,
        protected AuthBridgeServiceInterface $authBridge,
    )
    {
    }

    public function hitRateLimit(): void
    {
        RateLimiter::hit($this->getRateLimitKey(), (int) config('esanj.manager.rate_limit.decay_seconds', 600));
    }

    public function clearRateLimit(): void
    {
        RateLimiter::clear($this->getRateLimitKey());
    }

    public function getRateLimitKey(): string
    {
        return 'manager-' . request()->ip();
    }

    /**
     * Issue a manager access token for the given manager.
     *
     * The accounting tokens are embedded (encrypted) inside the token so
     * that, when the short access window lapses, the token can be silently
     * renewed against accounting without the client holding a refresh token.
     * Its lifetime never exceeds the Accounting access token's expiry.
     *
     * @return array{access_token: string, expires_in: int, expires_at: int}
     */
    public function generateAccessToken(Manager $manager, ?string $accountingRefreshToken = null, ?TokenData $accountingToken = null): array
    {
        $extra = [];
        if (!empty($accountingRefreshToken)) {
            $extra['acc_rt'] = Crypt::encryptString($accountingRefreshToken);
        }

        if ($accountingToken !== null) {
            $data = $accountingToken->toArray();
            unset($data['refresh_token']);
            $extra['acc_at'] = Crypt::encryptString(json_encode($data, JSON_THROW_ON_ERROR));
        }

        $token = $this->buildToken($manager, 'access', (int) config('esanj.manager.access_token_expires_in'), $extra, $accountingToken);

        return [
            'access_token' => $token['token'],
            'expires_in' => $token['expires_in'],
            'expires_at' => $token['expires_at'],
        ];
    }

    /**
     * Resolve the manager for the current bearer token, transparently renewing
     * an expired token against accounting.
     *
     * Returns:
     *  - ['manager' => Manager]                              — token still valid
     *  - ['manager' => Manager, 'access_token' => string,
     *     'expires_in' => int]                              — token was renewed
     *  - JsonResponse                                        — auth failed (401/403/400)
     *
     * @return array{manager: Manager, accounting_token?: TokenData|null, access_token?: string, expires_in?: int}|JsonResponse
     */
    public function authenticate(): array|JsonResponse
    {
        $token = request()->bearerToken();

        if (!$token) {
            return $this->errorResponse('manager::manager.errors.unauthorized', 401);
        }

        $verified = $this->decodeVerified($token);
        if ($verified === null) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        [$payload, $manager] = $verified;

        if (($payload['type'] ?? 'access') !== 'access') {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        if (!$manager->isActive()) {
            return $this->errorResponse('manager::manager.errors.manager_not_active', 403);
        }

        // Older bearer tokens with a refresh grant can acquire the Accounting identity once.
        $needsAccountingToken = !isset($payload['acc_at']) && !empty($payload['acc_rt']);

        if (!$this->isExpired($payload) && !$needsAccountingToken) {
            return ['manager' => $manager, 'accounting_token' => $this->accountingToken($payload)];
        }

        return $this->renewAgainstAccounting($manager, $payload);
    }

    /**
     * Silently mint a fresh access token — but only if the manager is still
     * valid at accounting. When accounting has blocked/revoked the manager the
     * refresh fails and no new access is granted.
     *
     * @return array{manager: Manager, accounting_token: TokenData|null, access_token: string, expires_in: int}|JsonResponse
     */
    private function renewAgainstAccounting(Manager $manager, array $payload): array|JsonResponse
    {
        $jti = is_string($payload['jti'] ?? null) ? $payload['jti'] : null;

        if ($jti === null) {
            return $this->renew($manager, $payload);
        }

        // Accounting spends a refresh token on first use: parallel requests carrying the same expired token share one renewal.
        $key = self::RENEWAL_PREFIX . hash('sha256', $jti);

        if (($shared = $this->sharedRenewal($manager, $key)) !== null) {
            return $shared;
        }

        if (!Cache::getStore() instanceof LockProvider) {
            return $this->renewAndShare($manager, $payload, $key);
        }

        $lock = Cache::lock($key . ':lock', self::RENEWAL_LOCK_SECONDS);

        try {
            $lock->block(self::RENEWAL_WAIT_SECONDS);
        } catch (LockTimeoutException) {
            $lock = null;
        }

        try {
            return $this->sharedRenewal($manager, $key) ?? $this->renewAndShare($manager, $payload, $key);
        } finally {
            $lock?->release();
        }
    }

    private function sharedRenewal(Manager $manager, string $key): ?array
    {
        $shared = Cache::get($key);

        if (!is_array($shared) || !is_string($shared['access_token'] ?? null)) {
            return null;
        }

        $verified = $this->decodeVerified($shared['access_token']);
        if ($verified === null || $this->isExpired($verified[0])) {
            return null;
        }

        return [
            'manager' => $manager,
            'access_token' => $shared['access_token'],
            'expires_in' => max(0, $verified[0]['expires_at'] - now()->timestamp),
            'accounting_token' => $this->accountingToken($verified[0]),
        ];
    }

    private function renewAndShare(Manager $manager, array $payload, string $key): array|JsonResponse
    {
        $result = $this->renew($manager, $payload);

        if (is_array($result)) {
            Cache::put($key, [
                'access_token' => $result['access_token'],
                'expires_in' => $result['expires_in'],
            ], self::RENEWAL_SHARE_SECONDS);
        }

        return $result;
    }

    private function renew(Manager $manager, array $payload): array|JsonResponse
    {
        $encrypted = $payload['acc_rt'] ?? null;
        if (empty($encrypted)) {
            return $this->errorResponse('manager::manager.errors.token_expired', 401);
        }

        try {
            $accountingRefreshToken = Crypt::decryptString($encrypted);
        } catch (Throwable) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        try {
            $accounting = $this->authBridge->refreshAccessToken($accountingRefreshToken);
        } catch (TokenExchangeException) {
            // Accounting refused the refresh — the manager is blocked/revoked there.
            return $this->errorResponse('manager::manager.errors.unauthorized', 401);
        }

        $fresh = $this->generateAccessToken(
            $manager,
            $accounting->hasRefreshToken() ? $accounting->refreshToken : $accountingRefreshToken,
            $accounting,
        );

        return [
            'manager' => $manager,
            'access_token' => $fresh['access_token'],
            'expires_in' => $fresh['expires_in'],
            'accounting_token' => $accounting,
        ];
    }

    private function accountingToken(array $payload): ?TokenData
    {
        if (!is_string($payload['acc_at'] ?? null)) {
            return null;
        }

        try {
            return TokenData::fromStorage(json_decode(Crypt::decryptString($payload['acc_at']), true, flags: JSON_THROW_ON_ERROR));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Build a signed, stateless token of the given type.
     *
     * @param array<string, mixed> $extra
     * @return array{token: string, expires_in: int, expires_at: int}
     */
    private function buildToken(Manager $manager, string $type, int $ttlMinutes, array $extra = [], ?TokenData $accountingToken = null): array
    {
        $expiresAt = now()->addMinutes($ttlMinutes);
        if ($accountingToken !== null && $accountingToken->expiresAt->getTimestamp() < $expiresAt->timestamp) {
            $expiresAt = Carbon::createFromTimestamp($accountingToken->expiresAt->getTimestamp());
        }

        $payload = array_merge($extra, [
            'manager_id' => $manager->id,
            'type' => $type,
            'jti' => Str::uuid()->toString(),
            'issued_at' => now()->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ]);

        $base64 = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64, $manager->secret_key . config('app.key'));

        return [
            'token' => $base64 . '.' . $signature,
            'expires_in' => max(0, $expiresAt->timestamp - now()->timestamp),
            'expires_at' => $expiresAt->timestamp,
        ];
    }

    /**
     * Decode a token and verify its signature (ignoring expiry, so an expired
     * token can still be renewed). Returns [payload, manager] or null.
     *
     * @return array{0: array, 1: Manager}|null
     */
    private function decodeVerified(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$base64, $signature] = $parts;
        if ($base64 === '' || $signature === '') {
            return null;
        }

        $payload = json_decode(base64_decode($base64), true);
        if (!is_array($payload)
            || !is_int($payload['manager_id'] ?? null) || $payload['manager_id'] < 1
            || !is_int($payload['issued_at'] ?? null)
            || !is_int($payload['expires_at'] ?? null)) {
            return null;
        }

        $manager = $this->managerService->findById($payload['manager_id']);
        if (!$manager) {
            return null;
        }

        $validSignature = hash_hmac('sha256', $base64, $manager->secret_key . config('app.key'));
        if (!hash_equals($validSignature, $signature)) {
            return null;
        }

        return [$payload, $manager];
    }

    private function isExpired(array $payload): bool
    {
        return $payload['expires_at'] <= now()->timestamp;
    }

    private function errorResponse(string $messageKey, int $status): JsonResponse
    {
        return response()->json([
            'message' => trans($messageKey)
        ], $status);
    }
}
