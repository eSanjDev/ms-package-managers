<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckManagerPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission = "")
    {
        auth()->shouldUse('manager');

        $managerService = app(ManagerService::class);

        $manager = $managerService->findById(id: Auth::id() ?? 0);

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('managers.auth.login');
        }

        $hasPermission = $managerService->hasPermission($manager->id, $permission);

        if ($hasPermission || !$permission) {
            return $next($request);
        }

        return redirect(config('esanj.manager.access_denied_redirect'));
    }
}
