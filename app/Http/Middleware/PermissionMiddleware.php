<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($permissions === [] || ! $user->hasAnyPermission($permissions)) {
            abort(403, 'No tienes permisos suficientes para acceder a este recurso.');
        }

        return $next($request);
    }
}
