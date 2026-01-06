<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\AuthBridge\Exceptions\ExtractJWTException;
use Esanj\AuthBridge\Services\ClientCredentialsService;
use Esanj\Manager\Exceptions\ManagerAccessDenied;
use Esanj\Manager\Exceptions\ManagerException;
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
        protected ManagerService           $managerService,
        protected ManagerAuthService       $managerAuthService,
        protected ClientCredentialsService $clientCredentialsService,
    )
    {
    }

    /**
     * @return RedirectResponse|View
     */
    public function index(): RedirectResponse|View
    {
        $manager = $this->resolveManagerFromSession();

        if (!$manager->needToken()) {
            return $this->handleSuccessLogin($manager);
        }

        return view('manager::auth.token');
    }

    public function login(Request $request): RedirectResponse
    {
        $manager = $this->resolveManagerFromSession();

        $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->input('token', "");

        if ($manager->needToken()) {
            try {
                $this->managerService->checkManagerToken($manager, $token);
            } catch (ManagerAccessDenied $e) {
                $this->managerAuthService->hitRateLimit();
                return $this->abort($e->getCode(), $e->getMessage());
            }
        }

        return $this->handleSuccessLogin($manager);
    }

    public function logout()
    {
        Auth::guard('manager')->logout();
        Session::forget('auth_bridge');

        return redirect()->route('auth-bridge.redirect');
    }

    private function handleSuccessLogin(Manager $manager)
    {
        $this->managerService->updateLastLogin($manager->id);

        Auth::guard('manager')->loginUsingId($manager->id);

        $this->managerService->setActivity('manager.login', [
            'login_type' => 'web',
        ]);

        return redirect()->to(config('esanj.manager.success_redirect'));
    }

    /**
     * @throws ExtractJWTException
     */
    private function extractAccessToken(): mixed
    {
        $accessToken = session('auth_bridge.access_token');

        if (!$accessToken) {
            return redirect()->route('auth-bridge.redirect');
        }

        $decoded = $this->clientCredentialsService->extractJwt($accessToken);

        return $decoded->sub;
    }


    private function resolveManagerFromSession()
    {
        try {
            $esanjId = $this->extractAccessToken();

            $manager = $this->managerService->findByEsanjId($esanjId);

            if (!$manager) {
                throw ManagerAccessDenied::managerNotFound();
            } elseif (!$manager->isActive()) {
                throw ManagerAccessDenied::managerNotActive();
            }

            return $manager;
        } catch (ManagerException|ExtractJWTException $e) {
            return $this->abort($e->getCode(), $e->getMessage());
        }
    }

    private function abort(int $code, ?string $message = ""): RedirectResponse
    {
        abort($code, $message);
    }
}
