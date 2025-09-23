<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class EnsureRequestIsNotRateLimitedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $service = app(ManagerAuthService::class);

        $key = $service->getRateLimitKey();
        $maxAttempts = config('esanj.manager.rate_limit.max_attempts');

        // Check if rate limiting is enabled and if the request has exceeded the limit
        if (config('esanj.manager.rate_limit.is_enabled') && RateLimiter::tooManyAttempts($key, $maxAttempts)) {

            // If the request expects a JSON response, return a JSON error message
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'message' => trans('manager::manager.errors.too_many_attempts')
                ], 429);
            }


            abort(429, trans('manager::manager.errors.too_many_attempts'));
        }

        return $next($request);
    }
}
