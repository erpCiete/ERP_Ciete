<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAccessMiddleware
{
    /**
     * Keep audit access enforced by explicit capability, without role bypass.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->canViewAudit()) {
            abort(403, 'No tienes permisos suficientes para acceder al módulo de auditoría.');
        }

        return $next($request);
    }
}
