<?php

namespace Esanj\Manager\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckManagerPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission = "")
    {
        $managerService = app(ManagerService::class);

        $manager = $managerService->findById(Auth::guard('manager')?->id() ?? 0);

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('manager.auth.login');
        }

        $hasPermission = $managerService->hasPermission($manager->id, $permission);

        if ($hasPermission || !$permission) {
            return $next($request);
        }

        return redirect(config('manager.access_denied_redirect'));
    }
}
