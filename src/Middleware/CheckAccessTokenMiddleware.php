<?php

namespace Esanj\Manager\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ManagerService = app(ManagerService::class);
        $managerID = session('auth_manager.manager_id');

        $manager = $ManagerService->findByManagerID($managerID ?? 0);

        if (!$managerID || !$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('oauth.redirect');
        }

        return $next($request);
    }
}
