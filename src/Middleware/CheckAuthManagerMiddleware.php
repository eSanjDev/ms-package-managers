<?php

namespace Esanj\Manager\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuthManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $manager = Auth::guard("manager")->user();

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }
}
