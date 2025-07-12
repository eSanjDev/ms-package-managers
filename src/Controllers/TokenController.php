<?php

namespace Esanj\Manager\Controllers;

use App\Http\Controllers\Controller;
use Esanj\Manager\Enums\AuthManagerStatusResponsesEnum;
use Esanj\Manager\Services\ManagerService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;

class TokenController extends Controller
{
    public function index()
    {
        $accessToken = session('auth_bridge.access_token');

        if (!$accessToken) {
            return redirect()->route('auth-bridge.redirect');
        }

        return view("manager::token");
    }

    public function login(Request $request, ManagerService $managerService)
    {
        if ($redirect = $this->checkRateLimit()) {
            return $redirect;
        }

        $request->validate([
            'token' => ['required', 'string', 'max:255']
        ]);

        $accessToken = session('auth_bridge.access_token');

        if (!$accessToken) {
            return redirect()->route('auth-bridge.redirect');
        }

        throw_if(
            !File::exists(config('manager.public_key_path')),
            FileNotFoundException::class,
            trans("manager::manager.errors.public_key_not_found")
        );

        $publicKey = file_get_contents(config('manager.public_key_path'));

        $decode = JWT::decode($accessToken, new Key($publicKey, 'RS256'));

        $managerID = $decode->sub;

        $manager = $managerService->findByManagerID($managerID);

        if (!$managerService->checkManagerToken($manager, $request->input('token'))) {
            RateLimiter::hit('manager-' . request()->ip(), config('manager.rate_limit.decay_minutes'));
            return redirect()->route('manager.index')
                ->withErrors(['token' => trans('manager::manager.errors.token_incorrect')]);
        }

        if (!$manager->is_active) {
            return redirect()->route('manager.index')
                ->withErrors(['token' => trans('manager::manager.errors.manager_not_active')]);
        }

        $managerService->updateLastLogin($manager->id);

        session()->put('auth_manager.manager_id', $managerID);

        return redirect(config('manager.success_redirect'));
    }

    private function checkRateLimit(): ?RedirectResponse
    {
        $rateLimitEnabled = config('manager.rate_limit.is_enabled');
        $maxAttempts = config('manager.rate_limit.max_attempts');
        $key = 'manager-' . request()->ip();

        if ($rateLimitEnabled && RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return redirect()->route('manager.index')
                ->withErrors(['token' => trans('manager::manager.errors.too_many_attempts')]);
        }

        return null;
    }
}
