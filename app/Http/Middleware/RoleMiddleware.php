<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // If the user is not logged in, redirect to login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // If the user's role is not in the allowed roles, abort
        if (! in_array(Auth::user()->role, $roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        // User is authorized
        return $next($request);
    }
}
