<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckManagerPermissionMiddleware
{
    public function __construct(
        protected ManagerService $managerService
    ) {
    }

    public function handle(Request $request, Closure $next, string $permission = ''): Response
    {
        auth()->shouldUse('manager');

        $manager = Auth::user();

        if (!$this->isValidManager($manager)) {
            return $this->unauthorizedResponse($request);
        }

        if (!$this->hasRequiredPermission($manager->id, $permission)) {
            return $this->accessDeniedResponse($request);
        }

        return $next($request);
    }

    private function isValidManager($manager): bool
    {
        return $manager && $manager->is_active;
    }

    private function hasRequiredPermission(int $managerId, string $permission): bool
    {
        return empty($permission) || $this->managerService->hasPermission($managerId, $permission);
    }

    private function unauthorizedResponse(Request $request): Response
    {
        session()->forget('auth_bridge');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('manager::manager.errors.unauthorized'),
            ], 401);
        }

        return redirect()->route('managers.auth.login');
    }

    private function accessDeniedResponse(Request $request): Response
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('manager::manager.errors.access_denied'),
            ], 403);
        }

        return redirect(config('esanj.manager.access_denied_redirect'));
    }
}
