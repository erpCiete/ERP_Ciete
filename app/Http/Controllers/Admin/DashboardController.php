<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Legalizacion;
use App\Models\Pedido;
use App\Models\Trabajo;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'active_works'   => Trabajo::whereIn('estado', ['borrador', 'en_curso'])->count(),
            'pending_orders' => Pedido::where('estado', 'pendiente')->count(),
            'billing'        => '—',
            'legalizaciones' => Legalizacion::whereIn('estado', ['pendiente', 'en_tramite'])->count(),
            'clientes'       => Empresa::count(),
            'estaciones'     => EstacionServicio::where('activo', true)->count(),
        ];

        $users = User::where('activo', true)
            ->select('id_usuario', 'nombre', 'apellidos', 'email')
            ->orderBy('nombre')
            ->limit(20)
            ->get();

        $activity = AuditLog::query()
            ->with('usuario:id_usuario,nombre,apellidos')
            ->latest('created_at')
            ->limit(15)
            ->get()
            ->map(fn(AuditLog $log) => [
                'initials' => mb_substr($log->usuario?->nombre ?? '?', 0, 1)
                    . mb_substr($log->usuario?->apellidos ?? '', 0, 1),
                'name'     => trim(($log->usuario?->nombre ?? '') . ' ' . ($log->usuario?->apellidos ?? '')),
                'action'   => $log->accion,
                'target'   => $log->tabla . ($log->registro_id ? ' #' . $log->registro_id : ''),
                'time'     => $log->created_at?->diffForHumans() ?? '',
            ]);

        $userContexts = auth()->user()->contextos()
            ->select('contextos_cliente.id_contexto', 'nombre', 'codigo')
            ->wherePivot('activo', true)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats'        => $stats,
            'users'        => $users,
            'activity'     => $activity,
            'userContexts' => $userContexts,
            'homeNotices'  => NoticeController::load(),
        ]);
    }
}
