<?php

namespace Esanj\Manager\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;

class CheckAuthManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $managerService = app(ManagerService::class);

        $managerID = session('auth_manager.manager_id');

        $manager = $managerService->findByManagerID($managerID ?? 0);

        if (!$managerID || !$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }
}
