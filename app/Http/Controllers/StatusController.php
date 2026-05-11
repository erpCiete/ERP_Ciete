<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatusController extends Controller
{
    public function index()
    {
        $isAdmin = auth()->user()?->is_admin ?? false;
        $checks = [];

        // Database
        try {
            $pdo = DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'driver' => config('database.default'),
                'name'   => config('database.connections.' . config('database.default') . '.database'),
            ];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'driver' => config('database.default'), 'name' => '—'];
        }

        // Storage
        $storagePath = storage_path('app');
        $writable = is_writable($storagePath);
        $freeMb  = round(@disk_free_space($storagePath) / 1048576);
        $totalMb = round(@disk_total_space($storagePath) / 1048576);
        $checks['storage'] = [
            'status'   => $writable ? 'ok' : 'warning',
            'writable' => $writable,
            'free_mb'  => $freeMb,
            'total_mb' => $totalMb,
        ];

        // Mail
        $mailDriver = config('mail.default');
        $checks['mail'] = [
            'status' => in_array($mailDriver, ['smtp', 'ses', 'mailgun', 'postmark']) ? 'ok' : 'warning',
            'driver' => $mailDriver,
        ];

        // Queue
        $queueDriver = config('queue.default');
        $checks['queue'] = [
            'status' => 'ok',
            'driver' => $queueDriver,
        ];

        // App
        $checks['app'] = [
            'status'      => 'ok',
            'version'     => 'v2.0',
            'environment' => app()->environment(),
            'php'         => PHP_VERSION,
            'laravel'     => app()->version(),
            'locale'      => app()->getLocale(),
            'timezone'    => config('app.timezone'),
        ];

        // Maintenance
        $maintenanceActive = app()->isDownForMaintenance();
        $checks['maintenance'] = [
            'status' => $maintenanceActive ? 'warning' : 'ok',
            'active' => $maintenanceActive,
        ];

        // For non-admin users, strip sensitive infrastructure details
        if (! $isAdmin) {
            $checks = [
                'app' => [
                    'status'  => $checks['app']['status'],
                    'version' => $checks['app']['version'],
                ],
                'mail' => [
                    'status' => $checks['mail']['status'],
                ],
                'maintenance' => $checks['maintenance'],
            ];
        }

        return Inertia::render('Status', [
            'checks'    => $checks,
            'timestamp' => now()->toIso8601String(),
            'isAdmin'   => $isAdmin,
        ]);
    }
}
