<?php

namespace App\Services;

use App\Models\ContextoCliente;
use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClosureDashboardService
{
    public function buildDashboardPayload(User $user): array
    {
        $trabajos = Trabajo::query()
            ->with([
                'empresa:id_empresa,nombre,nombre_comercial',
                'estacion:id_estacion_servicio,nombre',
                'tipoTrabajo:id_tipo_trabajo,nombre',
                'responsableCiete:id_usuario,nombre,apellidos',
                'pedidos:id_pedido,id_trabajo,numero_pedido,importe_pedido,importe_facturado,facturado_completo,estado,updated_at',
                'pedidos.items:id_pedido_item,id_pedido,total_linea,cantidad',
                'pedidos.items.facturaItems:id_factura_item,id_pedido_item,importe_facturado',
                'facturas:id_factura,id_trabajo,total,estado,updated_at',
                'legalizaciones:id_legalizacion,id_trabajo,estado,descripcion_seleccionable,descripcion_libre,observaciones,updated_at',
            ])
            ->where(function ($query) {
                $query->whereIn('estado', ['terminado', 'pendiente_facturar', 'facturado', 'finalizado', 'cancelado'])
                    ->orWhere('bloqueado_cierre', true);
            })
            ->orderByDesc('fecha_encargo')
            ->orderByDesc('updated_at')
            ->get();

        $contextos = ContextoCliente::query()
            ->whereIn('id_contexto', $user->getAccessibleContextIds())
            ->orderBy('nombre')
            ->get(['id_contexto', 'nombre', 'codigo'])
            ->map(fn (ContextoCliente $contexto) => [
                'id' => (string) $contexto->id_contexto,
                'label' => $contexto->nombre,
            ])
            ->values()
            ->all();

        return [
            'works' => $trabajos
                ->map(fn (Trabajo $trabajo) => $this->serializeTrabajo($trabajo))
                ->values()
                ->all(),
            'contexts' => $contextos,
        ];
    }

    public function markReviewed(User $user, array $ids): int
    {
        $trabajos = $this->loadSelectedTrabajos($ids);

        return DB::transaction(function () use ($trabajos, $user) {
            $count = 0;

            foreach ($trabajos as $trabajo) {
                if ($trabajo->isProtectedFinalizedState()) {
                    continue;
                }

                $trabajo->update([
                    'bloqueado_cierre' => $this->hasBlockingChecks($trabajo),
                ]);

                $count++;
            }

            return $count;
        });
    }

    public function closeMany(User $user, array $ids): int
    {
        $trabajos = $this->loadSelectedTrabajos($ids);

        return DB::transaction(function () use ($trabajos, $user) {
            $count = 0;

            foreach ($trabajos as $trabajo) {
                if (! $this->canBeClosed($trabajo)) {
                    continue;
                }

                $this->applyClosure($trabajo, $user);
                $count++;
            }

            return $count;
        });
    }

    public function closeOne(Trabajo $trabajo, User $user): void
    {
        $trabajo->loadMissing([
            'pedidos:id_pedido,id_trabajo,numero_pedido,importe_pedido,importe_facturado,facturado_completo,estado,updated_at',
            'pedidos.items:id_pedido_item,id_pedido,total_linea,cantidad',
            'pedidos.items.facturaItems:id_factura_item,id_pedido_item,importe_facturado',
            'facturas:id_factura,id_trabajo,total,estado,updated_at',
            'legalizaciones:id_legalizacion,id_trabajo,estado,descripcion_seleccionable,descripcion_libre,observaciones,updated_at',
        ]);

        if (! $this->canBeClosed($trabajo)) {
            throw ValidationException::withMessages([
                'message' => 'La obra todavía no cumple el checklist mínimo de cierre.',
            ]);
        }

        $this->applyClosure($trabajo, $user);
    }

    private function loadSelectedTrabajos(array $ids): Collection
    {
        $uniqueIds = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($uniqueIds->isEmpty()) {
            throw ValidationException::withMessages([
                'ids' => 'Selecciona al menos una obra.',
            ]);
        }

        $trabajos = Trabajo::query()
            ->with([
                'pedidos:id_pedido,id_trabajo,numero_pedido,importe_pedido,importe_facturado,facturado_completo,estado,updated_at',
                'pedidos.items:id_pedido_item,id_pedido,total_linea,cantidad',
                'pedidos.items.facturaItems:id_factura_item,id_pedido_item,importe_facturado',
                'facturas:id_factura,id_trabajo,total,estado,updated_at',
                'legalizaciones:id_legalizacion,id_trabajo,estado,descripcion_seleccionable,descripcion_libre,observaciones,updated_at',
            ])
            ->whereIn('id_trabajo', $uniqueIds)
            ->get();

        if ($trabajos->count() !== $uniqueIds->count()) {
            abort(403, 'No tienes acceso a una o varias obras seleccionadas.');
        }

        return $trabajos;
    }

    private function applyClosure(Trabajo $trabajo, User $user): void
    {
        $trabajo->update([
            'estado' => 'finalizado',
            'bloqueado_cierre' => false,
        ]);
    }

    private function canBeClosed(Trabajo $trabajo): bool
    {
        if ($trabajo->isProtectedFinalizedState()) {
            return false;
        }

        if ($trabajo->estado === 'cancelado') {
            return false;
        }

        return $this->passesFinalizationRequirements($trabajo);
    }

    private function hasBlockingChecks(Trabajo $trabajo): bool
    {
        return collect($this->buildValidationSnapshot($trabajo))
            ->where('blocking', true)
            ->contains(fn (array $item) => ! $item['ok']);
    }

    private function buildValidationSnapshot(Trabajo $trabajo): array
    {
        $pedidoData = $this->extractPedidoData($trabajo);
        $economyData = $this->extractEconomyData($trabajo, $pedidoData);
        $legalizacionData = $this->extractLegalizacionData($trabajo);

        $mandatoryFieldsComplete = filled($trabajo->numero_trabajo)
            && filled($trabajo->descripcion_trabajo)
            && filled($trabajo->fecha_encargo)
            && $trabajo->empresa !== null
            && $trabajo->estacion !== null;

        $finishedConfirmed = $this->isFinishedForFinalizationFlow($trabajo);
        $flowReady = $trabajo->isProtectedFinalizedState()
            || in_array($trabajo->estado, ['terminado', 'pendiente_facturar', 'facturado'], true);
        $readyToFinalize = $this->passesFinalizationRequirements(
            $trabajo,
            $pedidoData,
            $economyData,
            $mandatoryFieldsComplete
        );

        return [
            ['id' => 'finishedConfirmed', 'ok' => $finishedConfirmed, 'blocking' => true],
            ['id' => 'endDate', 'ok' => filled($trabajo->fecha_terminacion), 'blocking' => true],
            ['id' => 'validOrder', 'ok' => $pedidoData['exists'], 'blocking' => true],
            ['id' => 'amountReviewed', 'ok' => ! $economyData['exceeds'], 'blocking' => true],
            ['id' => 'legalizationChecked', 'ok' => ! $legalizacionData['critical'], 'blocking' => false],
            ['id' => 'mandatoryFields', 'ok' => $mandatoryFieldsComplete, 'blocking' => true],
            ['id' => 'noBlockingIncidents', 'ok' => ! $trabajo->bloqueado_cierre, 'blocking' => true],
            ['id' => 'contractTariff', 'ok' => filled($trabajo->id_contrato) && filled($trabajo->id_tarifario), 'blocking' => true],
            ['id' => 'flowReady', 'ok' => $flowReady, 'blocking' => true],
            ['id' => 'readyToFinalize', 'ok' => $readyToFinalize, 'blocking' => false],
        ];
    }

    private function serializeTrabajo(Trabajo $trabajo): array
    {
        $pedidoData = $this->extractPedidoData($trabajo);
        $economyData = $this->extractEconomyData($trabajo, $pedidoData);
        $legalizacionData = $this->extractLegalizacionData($trabajo);
        $incidencias = $this->buildIncidentLabels($trabajo, $pedidoData, $economyData, $legalizacionData);
        $responsable = $this->resolveResponsibleName($trabajo);
        $finalizado = $trabajo->isProtectedFinalizedState();
        $readyToFinalize = $this->passesFinalizationRequirements($trabajo, $pedidoData, $economyData);
        $fechaFinalizacion = $trabajo->isFunctionallyFinalized()
            ? optional($trabajo->updated_at)->toIso8601String()
            : null;

        return [
            'id' => $trabajo->id_trabajo,
            'contextoId' => (string) $trabajo->id_contexto,
            'cliente' => $trabajo->empresa?->nombre_comercial ?: $trabajo->empresa?->nombre ?: ('Contexto ' . $trabajo->id_contexto),
            'estacion' => $trabajo->estacion?->nombre ?: ('Trabajo #' . $trabajo->id_trabajo),
            'numeroAviso' => $trabajo->numero_aviso ?: ('TR-' . $trabajo->numero_trabajo),
            'numeroPedido' => $pedidoData['number'],
            'tipoTrabajo' => $trabajo->tipoTrabajo?->nombre ?: ($trabajo->categoria ?: 'Sin tipificar'),
            'responsable' => $responsable ?: 'Sin asignar',
            'fechaEncargo' => optional($trabajo->fecha_encargo)?->toDateString(),
            'fechaFinReal' => optional($trabajo->fecha_terminacion)?->toDateString(),
            'importePedido' => $pedidoData['amount'],
            'importeTrabajo' => $economyData['amount'],
            'legalizacionEstado' => $legalizacionData['status'],
            'legalizacionObservaciones' => $legalizacionData['notes'],
            'legalizacionCritica' => $legalizacionData['critical'],
            'incidenciaBloqueante' => $incidencias !== [],
            'incidencias' => $incidencias,
            'estado' => $trabajo->estado,
            'terminadoConfirmado' => $this->isFinishedForFinalizationFlow($trabajo),
            'camposObligatoriosCompletos' => filled($trabajo->numero_trabajo)
                && filled($trabajo->descripcion_trabajo)
                && filled($trabajo->fecha_encargo)
                && $trabajo->empresa !== null
                && $trabajo->estacion !== null,
            'revisionEconomicaAprobada' => ! $economyData['exceeds'],
            'requiereContratoTarifario' => true,
            'contratoTarifarioValidado' => filled($trabajo->id_contrato) && filled($trabajo->id_tarifario),
            'faseActual' => $finalizado ? 'finalizado' : ($readyToFinalize ? 'revision_finalizacion' : $this->functionalPhase($trabajo)),
            'revisionFinalizacionMarcada' => $readyToFinalize || $finalizado,
            'finalizado' => $finalizado,
            'finalizadoLegacy' => false,
            'trazabilidad' => [
                'marcadoTerminadoPor' => $responsable,
                'fechaMarcadoTerminado' => optional($trabajo->fecha_terminacion)?->toDateString(),
                'fechaFinInformadaPor' => $responsable,
                'fechaFinInformadaAt' => optional($trabajo->fecha_terminacion)?->toDateString(),
                'importeActualizadoPor' => null,
                'importeActualizadoAt' => $economyData['updated_at'],
                'finalizadoPor' => null,
                'fechaFinalizacion' => $fechaFinalizacion,
            ],
        ];
    }

    private function extractPedidoData(Trabajo $trabajo): array
    {
        $pedidos = $trabajo->pedidos->sortByDesc('fecha_solicitud')->values();
        $primary = $pedidos->first();

        return [
            'exists' => $primary !== null,
            'number' => $primary?->numero_pedido ?: '—',
            'amount' => (float) $trabajo->pedidos->sum(fn ($pedido) => (float) ($pedido->importe_pedido ?? 0)),
            'updated_at' => optional($primary?->updated_at)->toIso8601String(),
        ];
    }

    private function extractEconomyData(Trabajo $trabajo, array $pedidoData): array
    {
        $facturaItemsTotal = (float) $trabajo->pedidos->sum(fn ($pedido) => $pedido->items->sum(
            fn ($item) => $item->facturaItems->sum(fn ($facturaItem) => (float) ($facturaItem->importe_facturado ?? 0))
        ));
        $facturaTotal = (float) $trabajo->facturas->sum(fn ($factura) => (float) ($factura->total ?? 0));
        $pedidoFacturado = (float) $trabajo->pedidos->sum(fn ($pedido) => (float) ($pedido->importe_facturado ?? 0));
        $amount = $facturaItemsTotal > 0 ? $facturaItemsTotal : ($facturaTotal > 0 ? $facturaTotal : $pedidoFacturado);
        $latestFactura = $trabajo->facturas->sortByDesc('updated_at')->first();

        return [
            'amount' => $amount,
            'exceeds' => $pedidoData['amount'] > 0 && $amount > $pedidoData['amount'],
            'updated_at' => optional($latestFactura?->updated_at)->toIso8601String() ?: $pedidoData['updated_at'],
        ];
    }

    private function extractLegalizacionData(Trabajo $trabajo): array
    {
        if ($trabajo->legalizaciones->isEmpty()) {
            return [
                'status' => 'no_aplica',
                'notes' => 'Sin legalizaciones asociadas.',
                'critical' => false,
            ];
        }

        $legalizaciones = $trabajo->legalizaciones;
        $notes = $legalizaciones
            ->sortByDesc('updated_at')
            ->map(fn ($legalizacion) => $legalizacion->observaciones ?: $legalizacion->descripcion_libre ?: $legalizacion->descripcion_seleccionable)
            ->filter()
            ->first() ?: 'Seguimiento legalizatorio en curso.';

        if ($legalizaciones->contains(fn ($legalizacion) => $legalizacion->estado === 'pendiente')) {
            return ['status' => 'pendiente', 'notes' => $notes, 'critical' => true];
        }

        if ($legalizaciones->contains(fn ($legalizacion) => $legalizacion->estado === 'en_tramite')) {
            return ['status' => 'en_revision', 'notes' => $notes, 'critical' => true];
        }

        if ($legalizaciones->every(fn ($legalizacion) => $legalizacion->estado === 'cancelada')) {
            return ['status' => 'no_aplica', 'notes' => $notes, 'critical' => false];
        }

        return ['status' => 'completa', 'notes' => $notes, 'critical' => false];
    }

    private function buildIncidentLabels(Trabajo $trabajo, array $pedidoData, array $economyData, array $legalizacionData): array
    {
        $incidents = [];

        if ($this->isFinishedForFinalizationFlow($trabajo) && ! $trabajo->fecha_terminacion) {
            $incidents[] = 'Trabajo terminado sin fecha real de finalización';
        }

        if (! $pedidoData['exists']) {
            $incidents[] = 'Falta pedido asociado para validar el cierre';
        }

        if ($economyData['exceeds']) {
            $incidents[] = 'El importe ejecutado supera el pedido';
        }

        if (! filled($trabajo->numero_trabajo) || ! filled($trabajo->descripcion_trabajo)) {
            $incidents[] = 'Hay campos obligatorios incompletos';
        }

        if ($trabajo->bloqueado_cierre) {
            $incidents[] = 'La obra está marcada como bloqueada para cierre';
        }

        return $incidents;
    }

    private function resolveResponsibleName(Trabajo $trabajo): ?string
    {
        return $this->formatUserName($trabajo->responsableCiete) ?: $trabajo->responsable_cliente;
    }

    private function formatUserName(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return trim(implode(' ', array_filter([$user->nombre, $user->apellidos])));
    }

    private function isFinishedForFinalizationFlow(Trabajo $trabajo): bool
    {
        return in_array($trabajo->estado, ['terminado', 'pendiente_facturar', 'facturado', 'finalizado'], true)
            || filled($trabajo->fecha_terminacion);
    }

    private function passesFinalizationRequirements(
        Trabajo $trabajo,
        ?array $pedidoData = null,
        ?array $economyData = null,
        ?bool $mandatoryFieldsComplete = null,
    ): bool {
        $pedidoData ??= $this->extractPedidoData($trabajo);
        $economyData ??= $this->extractEconomyData($trabajo, $pedidoData);
        $mandatoryFieldsComplete ??= filled($trabajo->numero_trabajo)
            && filled($trabajo->descripcion_trabajo)
            && filled($trabajo->fecha_encargo)
            && $trabajo->empresa !== null
            && $trabajo->estacion !== null;

        return $this->isFinishedForFinalizationFlow($trabajo)
            && filled($trabajo->fecha_terminacion)
            && $pedidoData['exists']
            && ! $economyData['exceeds']
            && $mandatoryFieldsComplete
            && ! $trabajo->bloqueado_cierre
            && filled($trabajo->id_contrato)
            && filled($trabajo->id_tarifario);
    }

    private function functionalPhase(Trabajo $trabajo): string
    {
        return match ($trabajo->estado) {
            'pendiente_facturar' => 'pendiente_facturar',
            'facturado' => 'facturado',
            'cancelado' => 'cancelado',
            default => 'terminado',
        };
    }

    private function appendClosureNote(?string $observaciones, User $user, ?string $reason): string
    {
        $message = trim((string) $reason) !== ''
            ? trim((string) $reason)
            : 'Reapertura solicitada desde el panel de dirección.';

        $prefix = sprintf(
            '[cierre-ajuste %s %s] ',
            now()->format('Y-m-d H:i'),
            $this->formatUserName($user) ?: $user->email
        );

        return trim(collect([trim((string) $observaciones), $prefix . $message])->filter()->implode(PHP_EOL));
    }

    private function extractLastClosureNote(?string $observaciones): ?string
    {
        if (! $observaciones) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $observaciones) ?: [];

        foreach (array_reverse($lines) as $line) {
            if (str_starts_with(trim($line), '[cierre-ajuste ')) {
                $parts = explode('] ', $line, 2);
                return $parts[1] ?? null;
            }
        }

        return null;
    }
}
