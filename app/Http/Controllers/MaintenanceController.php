<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Toggle maintenance mode on/off.
     */
    public function toggle(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageMaintenance(), 403, 'No tienes permisos para gestionar mantenimiento.');

        $file = storage_path('framework/maintenance_mode');

        if (file_exists($file)) {
            unlink($file);
        } else {
            file_put_contents($file, json_encode([
                'time' => now()->toIso8601String(),
                'user' => $request->user()->nombre,
            ]));
        }

        return back();
    }
}
