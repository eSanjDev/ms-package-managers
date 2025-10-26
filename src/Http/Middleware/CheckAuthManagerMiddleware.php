<?php

namespace Esanj\Manager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuthManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        auth()->shouldUse('manager');

        $manager = Auth::user();

        if (!$manager || !$manager->is_active) {
            session()->forget('auth_manager');
            return redirect()->route('auth-bridge.redirect');
        }

        return $next($request);
    }
}
