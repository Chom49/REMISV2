<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionLock
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::viaRemember() || session('auth.locked')) {
            session([
                'auth.locked'        => true,
                'auth.lock.intended' => $request->fullUrl(),
            ]);
            return redirect()->route('auth.lock');
        }

        return $next($request);
    }
}
