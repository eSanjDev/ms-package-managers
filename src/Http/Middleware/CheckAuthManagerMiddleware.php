<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthManagerMiddleware
{
    public function __construct(
        protected ManagerAuthService $authService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @param  string  $guard
     * @return Response|RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        auth()->shouldUse('manager');

        return match ($guard) {
            'web' => $this->handleWebGuard($request, $next),
            'api' => $this->handleApiGuard($request, $next),
            default => response()->json(['message' => 'Unauthorized.'], 401),
        };
    }

    /**
     * Handle web guard authentication.
     */
    protected function handleWebGuard(Request $request, Closure $next): Response
    {
        $manager = Auth::user();

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_bridge');

            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }

    /**
     * Handle API guard authentication.
     */
    protected function handleApiGuard(Request $request, Closure $next): Response
    {
        $manager = $this->authService->authenticateWithToken();

        if ($manager instanceof JsonResponse) {
            return $manager;
        }

        Auth::login($manager);

        return $next($request);
    }
}
