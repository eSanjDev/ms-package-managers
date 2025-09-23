<?php

namespace Esanj\Manager\Services;

use Esanj\Manager\Models\Manager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ManagerAuthService
{
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
}
