<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuthManagerMiddleware
{
    public function __construct(
        protected ManagerAuthService $managerAuthService
    )
    {
    }

    public function handle(Request $request, Closure $next, string $guard = 'web')
    {
        auth()->shouldUse('manager');

        if ($guard == 'api' && !auth()->check()) {
            $manager = $this->managerAuthService->authenticateWithToken();

            if ($manager instanceof JsonResponse) {
                return $manager;
            }

            Auth::login($manager);
        }

        $manager = Auth::user();

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }
}
