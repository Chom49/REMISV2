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
            $allowed = ['tenant.password.change', 'tenant.password.update',
                        'fo.password.change', 'fo.password.update', 'logout'];

            if (! $request->routeIs(...$allowed)) {
                $route = $user->isFinancialOfficer() ? 'fo.password.change' : 'tenant.password.change';
                return redirect()->route($route)
                    ->with('warning', 'You must set a new password before continuing.');
            }
        }

        return $next($request);
    }
}
