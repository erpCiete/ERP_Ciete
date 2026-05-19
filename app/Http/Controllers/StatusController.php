<?php

namespace App\Http\Controllers;

use App\Models\Importacion;
use App\Models\SolicitudSoporte;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatusController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $canViewTechnicalDetails = $user?->canAccessAdminPanel() ?? false;
        $maintenanceFile = storage_path('framework/maintenance_mode');
        $maintenanceActive = file_exists($maintenanceFile) || app()->isDownForMaintenance();
        $checks = [];
        $connectionName = config('database.default');
        $databaseConfig = config('database.connections.' . $connectionName, []);

        // Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'driver' => $connectionName,
                'name'   => $databaseConfig['database'] ?? '—',
                'host'   => $databaseConfig['host'] ?? 'localhost',
            ];
        } catch (\Throwable $e) {
            $checks['database'] = [
                'status' => 'error',
                'driver' => $connectionName,
                'name' => '—',
                'host' => $databaseConfig['host'] ?? 'localhost',
            ];
        }

        // Storage
        $storagePath = storage_path('app');
        $writable = is_writable($storagePath);
        $freeSpace = @disk_free_space($storagePath);
        $totalSpace = @disk_total_space($storagePath);
        $freeMb  = is_numeric($freeSpace) ? round($freeSpace / 1048576) : null;
        $totalMb = is_numeric($totalSpace) ? round($totalSpace / 1048576) : null;
        $usedPercent = $freeMb !== null && $totalMb
            ? max(0, min(100, round((($totalMb - $freeMb) / $totalMb) * 100)))
            : null;
        $checks['storage'] = [
            'status'   => ! $writable ? 'error' : (($freeMb !== null && $freeMb < 1024) ? 'warning' : 'ok'),
            'writable' => $writable,
            'free_mb'  => $freeMb,
            'total_mb' => $totalMb,
            'used_percent' => $usedPercent,
        ];

        // Mail
        $mailDriver = config('mail.default');
        $mailConfig = config('mail.mailers.' . $mailDriver, []);
        $checks['mail'] = [
            'status' => in_array($mailDriver, ['smtp', 'ses', 'mailgun', 'postmark']) ? 'ok' : 'warning',
            'driver' => $mailDriver,
            'host' => $mailConfig['host'] ?? null,
            'from' => config('mail.from.address'),
        ];

        // Queue
        $queueDriver = config('queue.default');
        $checks['queue'] = [
            'status' => $queueDriver === 'sync' ? 'warning' : 'ok',
            'driver' => $queueDriver,
        ];

        // App
        $checks['app'] = [
            'status'      => 'ok',
            'version'     => 'v2.1.0',
            'environment' => app()->environment(),
            'php'         => PHP_VERSION,
            'laravel'     => app()->version(),
            'locale'      => app()->getLocale(),
            'timezone'    => config('app.timezone'),
            'debug'       => (bool) config('app.debug'),
        ];

        // Maintenance
        $checks['maintenance'] = [
            'status' => $maintenanceActive ? 'warning' : 'ok',
            'active' => $maintenanceActive,
            'file_present' => file_exists($maintenanceFile),
        ];

        $latestImportAt = Importacion::query()->latest('created_at')->value('created_at');

        if ($latestImportAt instanceof \DateTimeInterface) {
            $latestImportAt = $latestImportAt->format(DATE_ATOM);
        } elseif ($latestImportAt !== null) {
            $latestImportAt = (string) $latestImportAt;
        }

        $summary = [
            'ok_count' => collect($checks)->where('status', 'ok')->count(),
            'warning_count' => collect($checks)->where('status', 'warning')->count(),
            'error_count' => collect($checks)->where('status', 'error')->count(),
            'maintenance_active' => $maintenanceActive,
            'active_context_name' => $user?->contexto
                ? trim(($user->contexto->codigo ? $user->contexto->codigo . ' · ' : '') . $user->contexto->nombre)
                : null,
            'accessible_contexts' => count($user?->getActiveContextIds() ?? []),
            'open_support_tickets' => SolicitudSoporte::query()
                ->whereIn('estado', ['pending', 'in_review'])
                ->count(),
            'imports_total' => Importacion::query()->count(),
            'latest_import_at' => $latestImportAt,
        ];

        // For non-admin users, strip sensitive infrastructure details
        if (! $canViewTechnicalDetails) {
            $checks = [
                'app' => [
                    'status'  => $checks['app']['status'],
                    'version' => $checks['app']['version'],
                    'locale' => $checks['app']['locale'],
                ],
                'storage' => [
                    'status' => $checks['storage']['status'],
                    'writable' => $checks['storage']['writable'],
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
            'canViewTechnicalDetails' => $canViewTechnicalDetails,
            'summary' => $summary,
        ]);
    }
}
