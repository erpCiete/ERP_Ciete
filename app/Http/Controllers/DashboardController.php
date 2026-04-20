<?php

namespace App\Http\Controllers;

use App\Models\Legalizacion;
use App\Models\Pedido;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $contextIds = $user->getAccessibleContextIds();

        $obras = Trabajo::query()
            ->whereIn('id_contexto', $contextIds)
            ->whereIn('estado', ['borrador', 'en_curso'])
            ->with('empresa:id_empresa,nombre_comercial')
            ->latest('fecha_encargo')
            ->limit(10)
            ->get()
            ->map(fn(Trabajo $t) => [
                'id'      => $t->id_trabajo,
                'nombre'  => $t->numero_trabajo . ' — ' . $t->descripcion_trabajo,
                'cliente' => $t->empresa?->nombre_comercial ?? '—',
                'estado'  => $t->estado,
            ]);

        $pedidos = Pedido::query()
            ->whereIn('id_contexto', $contextIds)
            ->where('estado', 'pendiente')
            ->with('trabajo:id_trabajo,numero_trabajo')
            ->latest('fecha_solicitud')
            ->limit(10)
            ->get()
            ->map(fn(Pedido $p) => [
                'ref'   => $p->numero_pedido ?? $p->id_pedido,
                'obra'  => $p->trabajo?->numero_trabajo ?? '—',
                'estado' => $p->estado,
            ]);

        $legalizaciones = Legalizacion::query()
            ->whereIn('id_contexto', $contextIds)
            ->whereIn('estado', ['pendiente', 'en_tramite'])
            ->with('trabajo:id_trabajo,numero_trabajo')
            ->latest('fecha_limite')
            ->limit(10)
            ->get()
            ->map(fn(Legalizacion $l) => [
                'ref'         => $l->numero_expediente ?? $l->id_legalizacion,
                'vencimiento' => $l->fecha_limite?->format('d/m/Y') ?? '—',
                'estado'      => $l->estado,
            ]);

        return Inertia::render('Dashboard', [
            'obras'           => $obras,
            'pedidos'         => $pedidos,
            'legalizaciones'  => $legalizaciones,
        ]);
    }
}
