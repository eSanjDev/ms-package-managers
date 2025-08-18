<?php

namespace Esanj\Manager\Http\Controllers;

use App\Http\Controllers\Controller;
use Esanj\Manager\Services\ManagerService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(): RedirectResponse|View
    {
        if (!session()->has('auth_bridge.access_token')) {
            return redirect()->route('auth-bridge.redirect');
        }

        return view('manager::auth.token');
    }

    public function login(Request $request, ManagerService $managerService): RedirectResponse
    {
        if ($redirect = $this->checkRateLimit()) {
            return $redirect;
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $accessToken = session('auth_bridge.access_token');

        if (!$accessToken) {
            return redirect()->route('auth-bridge.redirect');
        }

        $esanjId = $this->extractEsanjIdFromJwt($accessToken);

        $manager = $managerService->findByEsanjId($esanjId);

        if (!$manager || !$managerService->checkManagerToken($manager, $validated['token'])) {
            $this->hitRateLimit();
            return $this->handleFailedLogin(trans('manager::manager.errors.token_incorrect'));
        }

        if (!$manager->is_active) {
            return $this->handleFailedLogin(trans('manager::manager.errors.manager_not_active'));
        }

        $managerService->updateLastLogin($manager->id);

        Auth::guard('manager')->loginUsingId($manager->id);

        return redirect(config('esanj.manager.success_redirect'));
    }

    private function checkRateLimit(): ?RedirectResponse
    {
        if (!config('esanj.manager.rate_limit.is_enabled')) {
            return null;
        }

        $key = $this->getRateLimitKey();
        $maxAttempts = config('esanj.manager.rate_limit.max_attempts');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->handleFailedLogin(trans('manager::manager.errors.too_many_attempts'));
        }

        return null;
    }

    private function hitRateLimit(): void
    {
        RateLimiter::hit($this->getRateLimitKey(), config('esanj.manager.rate_limit.decay_minutes'));
    }

    private function getRateLimitKey(): string
    {
        return 'manager-' . request()->ip();
    }

    private function handleFailedLogin(string $message): RedirectResponse
    {
        return redirect()->route('manager.auth.index')->withErrors(['token' => $message]);
    }

    /**
     * Decode JWT and extract Esanj ID
     *
     * @param string $jwt
     * @return string
     * @throws \Throwable
     */
    private function extractEsanjIdFromJwt(string $jwt): string
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
            abort(401, trans('manager::manager.errors.token_invalid_or_expired'));
        }

        return $decoded->sub ?? '';
    }
}
