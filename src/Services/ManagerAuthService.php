<?php

namespace Esanj\Manager\Services;

use Carbon\Carbon;
use Esanj\Manager\Models\Manager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ManagerAuthService
{
    public function __construct(protected ManagerService $managerService)
    {
    }

    public function extractEsanjIdFromJwt(string $jwt): string|null
    {
        $publicKeyPath = config('esanj.manager.public_key_path');

        throw_if(
            !File::exists($publicKeyPath),
            FileNotFoundException::class,
            trans('manager::manager.errors.public_key_not_found')
        );

        $publicKey = file_get_contents($publicKeyPath);

        try {
            $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
        } catch (\Throwable $e) {
            session()->forget('auth_bridge');
            return null;
        }

        return $decoded->sub ?? '';
    }

    public function hitRateLimit(): void
    {
        RateLimiter::hit($this->getRateLimitKey(), config('esanj.manager.rate_limit.decay_minutes'));
    }

    public function getRateLimitKey(): string
    {
        return 'manager-' . request()->ip();
    }

    public function generateAccessToken(Manager $manager): array
    {
        $expiresAt = now()->addMinutes(config('esanj.manager.access_token_expires_in'))->timestamp;

        $payload = [
            'manager_id' => $manager->id,
            'jti' => Str::uuid()->toString(),
            'issued_at' => now()->timestamp,
            'expires_at' => $expiresAt,

        ];

        $json = json_encode($payload);
        $base64 = base64_encode($json);
        $signature = hash_hmac('sha256', $base64, $manager->secret_key . config('app.key'));

        return [
            'access_token' => $base64 . '.' . $signature,
            'expires_in' => $expiresAt,
        ];
    }

    public function authenticateWithToken(): Manager|JsonResponse|null
    {
        $token = request()->bearerToken();

        if (!$token) {
            return $this->errorResponse('manager::manager.errors.unauthorized', 401);
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        [$base64, $signature] = $parts;

        if (empty($base64) || empty($signature)) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        $payload = json_decode(base64_decode($base64), true);

        if (empty($payload['manager_id']) || empty($payload['issued_at']) || empty($payload['expires_at'])) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        $manager = $this->managerService->findById($payload['manager_id']);
        if (!$manager) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        if (Carbon::createFromTimestamp($payload['expires_at'])->isPast()) {
            return $this->errorResponse('manager::manager.errors.token_expired', 400);
        }

        $validSignature = hash_hmac('sha256', $base64, $manager->secret_key . config('app.key'));
        if (!hash_equals($validSignature, $signature)) {
            return $this->errorResponse('manager::manager.errors.token_incorrect', 400);
        }

        return $manager;
    }

    private function errorResponse(string $messageKey, int $status): JsonResponse
    {
        return response()->json([
            'message' => trans($messageKey)
        ], $status);
    }
}
