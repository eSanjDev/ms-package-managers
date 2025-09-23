<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $managerService = app(ManagerService::class);

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json([
                'message' => trans('manager::manager.errors.unauthorized')
            ], 401);
        }

        $token = $matches[1] ?? null;
        $explodeToken = explode('.', $token);

        if (count($explodeToken) !== 2) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_incorrect')
            ], 400);
        }
        [$base64, $signature] = explode('.', $token);

        $payload = json_decode(base64_decode($base64), true);
        $manager = $managerService->findById($payload['manager_id'] ?? 0) ?? null;

        if (!$manager || !isset($payload['issued_at'], $payload['expires_at'])) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_incorrect')
            ], 400);
        }

        if ($payload['expires_at'] < now()->timestamp) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_expired')
            ], 400);
        }

        $validSignature = hash_hmac('sha256', $base64, $manager->secret_key . config('app.key'));

        if (!hash_equals($validSignature, $signature)) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_incorrect')
            ], 400);
        }

        Auth::guard('manager')->login($manager);


        return $next($request);
    }
}
