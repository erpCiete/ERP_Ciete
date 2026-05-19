<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Importacion;
use App\Models\SolicitudSoporte;
use App\Models\User;
use App\Support\HomeNoticeCatalog;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $homeNotices = NoticeController::load();

        $userContexts = auth()->user()->contextos()
            ->select('contextos_cliente.id_contexto', 'nombre', 'codigo')
            ->wherePivot('activo', true)
            ->get();

        $noticeCount = collect(HomeNoticeCatalog::categories())
            ->sum(fn(string $category): int => count($homeNotices[$category] ?? []));

        $stats = [
            'open_tickets' => SolicitudSoporte::query()
                ->whereIn('estado', ['pending', 'in_review'])
                ->count(),
            'active_users' => User::query()->where('activo', true)->count(),
            'imports' => Importacion::query()->count(),
            'audit_today' => AuditLog::query()->whereDate('created_at', today())->count(),
            'notices' => $noticeCount,
            'contexts' => $userContexts->count(),
        ];

        $usersTodayQuery = AuditLog::query()
            ->selectRaw('id_usuario, MAX(created_at) as last_activity_at')
            ->whereDate('created_at', today())
            ->whereNotNull('id_usuario')
            ->groupBy('id_usuario');

        $usersPaginator = User::query()
            ->joinSub($usersTodayQuery, 'active_today', function ($join) {
                $join->on('usuarios.id_usuario', '=', 'active_today.id_usuario');
            })
            ->where('usuarios.activo', true)
            ->select('usuarios.id_usuario', 'usuarios.nombre', 'usuarios.apellidos', 'usuarios.email')
            ->orderByDesc('active_today.last_activity_at')
            ->paginate(10, ['*'], 'users_page');

        $activityPaginator = AuditLog::query()
            ->with('usuario:id_usuario,nombre,apellidos')
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->paginate(10, ['*'], 'activity_page');

        return Inertia::render('Admin/Dashboard', [
            'stats'        => $stats,
            'users'        => [
                'data' => collect($usersPaginator->items())
                    ->map(fn(User $member) => [
                        'id_usuario' => $member->id_usuario,
                        'nombre' => $member->nombre,
                        'apellidos' => $member->apellidos,
                        'email' => $member->email,
                    ])
                    ->values(),
                'meta' => $this->paginationMeta($usersPaginator),
            ],
            'activity'     => [
                'data' => collect($activityPaginator->items())
                    ->map(fn(AuditLog $log) => [
                        'id' => $log->id_audit,
                        'initials' => mb_substr($log->usuario?->nombre ?? '?', 0, 1)
                            . mb_substr($log->usuario?->apellidos ?? '', 0, 1),
                        'name' => trim(($log->usuario?->nombre ?? '') . ' ' . ($log->usuario?->apellidos ?? '')),
                        'action' => $log->accion,
                        'target' => $log->tabla . ($log->registro_id ? ' #' . $log->registro_id : ''),
                        'time' => $log->created_at?->diffForHumans() ?? '',
                    ])
                    ->values(),
                'meta' => $this->paginationMeta($activityPaginator),
            ],
            'userContexts' => $userContexts,
            'homeNotices'  => $homeNotices,
            'featuredNotice' => HomeNoticeCatalog::featuredActive(),
        ]);
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
            'has_previous_page' => $paginator->currentPage() > 1,
            'has_next_page' => $paginator->hasMorePages(),
        ];
    }
}
