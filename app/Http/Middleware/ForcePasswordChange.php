<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user && $user->force_password_change) {
            // Allow access to the change-password page and logout — nothing else
            if (! $request->routeIs('tenant.password.change', 'tenant.password.update', 'logout')) {
                return redirect()->route('tenant.password.change')
                    ->with('warning', 'You must set a new password before continuing.');
            }
        }

        return $next($request);
    }
}
