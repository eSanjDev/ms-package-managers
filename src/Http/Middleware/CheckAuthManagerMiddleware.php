<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\AuthBridge\Facades\AuthBridge;
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
    )
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @param string $guard
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

        if (!$manager || !$manager->isActive() || !$this->accountingAccessIsAlive()) {
            Auth::guard('manager')->logout();
            session()->forget(config('esanj.auth_bridge.session_token_key'));

            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }

    /**
     * The manager's web session is only alive while the accounting token can
     * still be (silently) refreshed. When accounting blocks/revokes the user,
     * the refresh fails and this returns false — cutting access here too.
     */
    protected function accountingAccessIsAlive(): bool
    {
        return AuthBridge::hasToken();
    }

    /**
     * Handle API guard authentication.
     */
    protected function handleApiGuard(Request $request, Closure $next): Response
    {
        $result = $this->authService->authenticate();

        if ($result instanceof JsonResponse) {
            return $result;
        }

        Auth::login($result['manager']);

        $response = $next($request);
        
        if (!empty($result['access_token'])) {
            $response->headers->set('X-Manager-Access-Token', $result['access_token']);
            $response->headers->set('X-Manager-Token-Expires-In', (string) $result['expires_in']);
        }

        return $response;
    }
}
