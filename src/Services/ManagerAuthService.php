<?php

namespace Esanj\Manager\Services;

use Carbon\Carbon;
use Esanj\AuthBridge\Contracts\AuthBridgeServiceInterface;
use Esanj\AuthBridge\Exceptions\TokenExchangeException;
use Esanj\Manager\Models\Manager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class ManagerAuthService
{
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

    public function getRateLimitKey(): string
    {
        return 'manager-' . request()->ip();
    }

    /**
     * Issue a manager access token for the given manager.
     *
     * The accounting refresh token is embedded (encrypted) inside the token so
     * that, when the short access window lapses, the token can be silently
     * renewed against accounting without the client holding a refresh token.
     *
     * @return array{access_token: string, expires_in: int, expires_at: int}
     */
    public function generateAccessToken(Manager $manager, ?string $accountingRefreshToken = null): array
    {
        $extra = [];
        if (!empty($accountingRefreshToken)) {
            $extra['acc_rt'] = Crypt::encryptString($accountingRefreshToken);
        }

        $token = $this->buildToken($manager, 'access', (int) config('esanj.manager.access_token_expires_in'), $extra);

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
     * @return array{manager: Manager, access_token?: string, expires_in?: int}|JsonResponse
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

        if (!$this->isExpired($payload)) {
            return ['manager' => $manager];
        }

        return $this->renewAgainstAccounting($manager, $payload);
    }

    /**
     * Silently mint a fresh access token — but only if the manager is still
     * valid at accounting. When accounting has blocked/revoked the manager the
     * refresh fails and no new access is granted.
     *
     * @return array{manager: Manager, access_token: string, expires_in: int}|JsonResponse
     */
    private function renewAgainstAccounting(Manager $manager, array $payload): array|JsonResponse
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

        $fresh = $this->generateAccessToken($manager, $accounting->refreshToken);

        return [
            'manager' => $manager,
            'access_token' => $fresh['access_token'],
            'expires_in' => $fresh['expires_in'],
        ];
    }

    /**
     * Build a signed, stateless token of the given type.
     *
     * @param array<string, mixed> $extra
     * @return array{token: string, expires_in: int, expires_at: int}
     */
    private function buildToken(Manager $manager, string $type, int $ttlMinutes, array $extra = []): array
    {
        $expiresAt = now()->addMinutes($ttlMinutes);

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
            'expires_in' => $ttlMinutes * 60,
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
        if (!is_array($payload) || empty($payload['manager_id']) || empty($payload['issued_at']) || empty($payload['expires_at'])) {
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
        return Carbon::createFromTimestamp($payload['expires_at'])->isPast();
    }

    private function errorResponse(string $messageKey, int $status): JsonResponse
    {
        return response()->json([
            'message' => trans($messageKey)
        ], $status);
    }
}