<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * If maintenance mode is active, non-admin users see the maintenance page.
     * Admin users bypass maintenance and can use the system normally.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! file_exists(storage_path('framework/maintenance_mode'))) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        return Inertia::render('Maintenance')->toResponse($request);
    }
}
