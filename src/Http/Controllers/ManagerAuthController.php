<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\Manager\Models\Manager;
use Esanj\Manager\Services\ManagerAuthService;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ManagerAuthController extends BaseController
{
    public function __construct(
        protected ManagerService     $managerService,
        protected ManagerAuthService $managerAuthService)
    {
    }

    public function index(): RedirectResponse|View
    {
        $manager = $this->resolveManagerFromSession();

        if ($manager && !$manager instanceof Manager) {
            return $manager;
        }

        if ($manager && !$manager->uses_token) {
            return $this->handleSuccessLogin($manager);
        }

        return view('manager::auth.token');
    }

    public function login(Request $request): RedirectResponse
    {
        $manager = $this->resolveManagerFromSession();

        if (!$manager instanceof Manager) {
            return $manager;
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        if (!$manager || ($manager->uses_token && !$this->managerService->checkManagerToken($manager, $validated['token'] ?? null))) {
            $this->managerAuthService->hitRateLimit();
            return $this->handleFailedLogin(trans('manager::manager.errors.token_incorrect'));
        }

        return $this->handleSuccessLogin($manager);
    }

    public function logout()
    {
        Auth::guard('manager')->logout();
        Session::forget('auth_bridge');

        return redirect()->route('auth-bridge.redirect');
    }

    private function handleFailedLogin(string $message): RedirectResponse
    {
        return redirect()->route('managers.auth.index')->withErrors(['token' => $message]);
    }

    private function handleSuccessLogin(Manager $manager)
    {
        if (!$manager->is_active) {
            return $this->handleFailedLogin(trans('manager::manager.errors.manager_not_active'));
        }

        $this->managerService->updateLastLogin($manager->id);

        Auth::guard('manager')->loginUsingId($manager->id);

        return redirect()->to(config('esanj.manager.success_redirect'));
    }

    private function resolveManagerFromSession()
    {
        $accessToken = session('auth_bridge.access_token');
        if (!$accessToken) {
            return redirect()->route('auth-bridge.redirect');
        }

        $esanjId = $this->managerAuthService->extractEsanjIdFromJwt($accessToken);
        if (!$esanjId) {
            return null;
        }

        return $this->managerService->findByEsanjId($esanjId);
    }
}
