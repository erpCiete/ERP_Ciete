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
                'usuarioCierre:id_usuario,nombre,apellidos',
                'pedidos:id_pedido,id_trabajo,numero_pedido,importe_pedido,importe_facturado,facturado_completo,estado,updated_at',
                'facturas:id_factura,id_trabajo,total,estado,updated_at',
                'legalizaciones:id_legalizacion,id_trabajo,estado,descripcion_seleccionable,descripcion_libre,observaciones,updated_at',
            ])
            ->where(function ($query) {
                $query->whereIn('estado', ['terminado', 'cerrado'])
                    ->orWhere('cerrado', true)
                    ->orWhereNotNull('fecha_cierre')
                    ->orWhereNotNull('id_usuario_cierre')
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
                if ($trabajo->cerrado) {
                    continue;
                }

                $trabajo->update([
                    'id_usuario_cierre' => $user->id_usuario,
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
            'pedidos',
            'facturas',
            'legalizaciones',
            'usuarioCierre:id_usuario,nombre,apellidos',
        ]);

        if (! $this->canBeClosed($trabajo)) {
            throw ValidationException::withMessages([
                'message' => 'La obra todavía no cumple el checklist mínimo de cierre.',
            ]);
        }

        $this->applyClosure($trabajo, $user);
    }

    public function reopenOne(Trabajo $trabajo, User $user, ?string $reason = null): void
    {
        if (! $trabajo->cerrado) {
            throw ValidationException::withMessages([
                'message' => 'Solo se pueden reabrir obras ya cerradas.',
            ]);
        }

        $trabajo->update([
            'estado' => 'terminado',
            'cerrado' => false,
            'bloqueado_cierre' => false,
            'fecha_cierre' => null,
            'id_usuario_cierre' => null,
            'observaciones' => $this->appendReopenNote($trabajo->observaciones, $user, $reason),
        ]);
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
            ->with(['pedidos', 'facturas', 'legalizaciones', 'usuarioCierre:id_usuario,nombre,apellidos'])
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
            'estado' => 'cerrado',
            'cerrado' => true,
            'bloqueado_cierre' => false,
            'fecha_cierre' => now(),
            'id_usuario_cierre' => $user->id_usuario,
        ]);
    }

    private function canBeClosed(Trabajo $trabajo): bool
    {
        if ($trabajo->cerrado) {
            return false;
        }

        if (! $trabajo->id_usuario_cierre) {
            return false;
        }

        return ! $this->hasBlockingChecks($trabajo);
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

        $finishedConfirmed = in_array($trabajo->estado, ['terminado', 'cerrado'], true) || filled($trabajo->fecha_terminacion);
        $flowReady = $trabajo->cerrado || $trabajo->id_usuario_cierre !== null || $trabajo->estado === 'terminado';

        return [
            ['id' => 'finishedConfirmed', 'ok' => $finishedConfirmed, 'blocking' => true],
            ['id' => 'endDate', 'ok' => filled($trabajo->fecha_terminacion), 'blocking' => true],
            ['id' => 'validOrder', 'ok' => $pedidoData['exists'], 'blocking' => true],
            ['id' => 'amountReviewed', 'ok' => ! $economyData['exceeds'], 'blocking' => true],
            ['id' => 'legalizationChecked', 'ok' => ! $legalizacionData['critical'], 'blocking' => true],
            ['id' => 'mandatoryFields', 'ok' => $mandatoryFieldsComplete, 'blocking' => true],
            ['id' => 'noBlockingIncidents', 'ok' => ! $trabajo->bloqueado_cierre, 'blocking' => true],
            ['id' => 'contractTariff', 'ok' => filled($trabajo->id_contrato) && filled($trabajo->id_tarifario), 'blocking' => true],
            ['id' => 'flowReady', 'ok' => $flowReady, 'blocking' => true],
            ['id' => 'readyToClose', 'ok' => $trabajo->id_usuario_cierre !== null || $trabajo->cerrado, 'blocking' => false],
        ];
    }

    private function serializeTrabajo(Trabajo $trabajo): array
    {
        $pedidoData = $this->extractPedidoData($trabajo);
        $economyData = $this->extractEconomyData($trabajo, $pedidoData);
        $legalizacionData = $this->extractLegalizacionData($trabajo);
        $incidencias = $this->buildIncidentLabels($trabajo, $pedidoData, $economyData, $legalizacionData);
        $responsable = $this->resolveResponsibleName($trabajo);
        $cierreUserName = $this->formatUserName($trabajo->usuarioCierre);

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
            'terminadoConfirmado' => in_array($trabajo->estado, ['terminado', 'cerrado'], true) || filled($trabajo->fecha_terminacion),
            'camposObligatoriosCompletos' => filled($trabajo->numero_trabajo)
                && filled($trabajo->descripcion_trabajo)
                && filled($trabajo->fecha_encargo)
                && $trabajo->empresa !== null
                && $trabajo->estacion !== null,
            'revisionEconomicaAprobada' => ! $economyData['exceeds'],
            'requiereContratoTarifario' => true,
            'contratoTarifarioValidado' => filled($trabajo->id_contrato) && filled($trabajo->id_tarifario),
            'faseActual' => $trabajo->cerrado ? 'cerrado' : ($trabajo->id_usuario_cierre ? 'revision_cierre' : 'terminado'),
            'revisionCierreMarcada' => $trabajo->id_usuario_cierre !== null || $trabajo->cerrado,
            'cerrado' => (bool) $trabajo->cerrado,
            'trazabilidad' => [
                'marcadoTerminadoPor' => $responsable,
                'fechaMarcadoTerminado' => optional($trabajo->fecha_terminacion)?->toDateString(),
                'fechaFinInformadaPor' => $responsable,
                'fechaFinInformadaAt' => optional($trabajo->fecha_terminacion)?->toDateString(),
                'importeActualizadoPor' => $cierreUserName,
                'importeActualizadoAt' => $economyData['updated_at'],
                'cerradoPor' => $trabajo->cerrado ? $cierreUserName : null,
                'fechaCierre' => optional($trabajo->fecha_cierre)?->toIso8601String(),
                'reabiertoPor' => null,
                'fechaReapertura' => null,
                'reaperturaMotivo' => $this->extractLastReopenReason($trabajo->observaciones),
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
        $facturaTotal = (float) $trabajo->facturas->sum(fn ($factura) => (float) ($factura->total ?? 0));
        $pedidoFacturado = (float) $trabajo->pedidos->sum(fn ($pedido) => (float) ($pedido->importe_facturado ?? 0));
        $amount = $facturaTotal > 0 ? $facturaTotal : $pedidoFacturado;
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

        if (in_array($trabajo->estado, ['terminado', 'cerrado'], true) && ! $trabajo->fecha_terminacion) {
            $incidents[] = 'Trabajo terminado sin fecha real de finalización';
        }

        if (! $pedidoData['exists']) {
            $incidents[] = 'Falta pedido asociado para validar el cierre';
        }

        if ($economyData['exceeds']) {
            $incidents[] = 'El importe ejecutado supera el pedido';
        }

        if ($legalizacionData['critical']) {
            $incidents[] = 'La legalización sigue pendiente o en trámite';
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

    private function appendReopenNote(?string $observaciones, User $user, ?string $reason): string
    {
        $message = trim((string) $reason) !== ''
            ? trim((string) $reason)
            : 'Reapertura solicitada desde el panel de dirección.';

        $prefix = sprintf(
            '[reapertura-cierre %s %s] ',
            now()->format('Y-m-d H:i'),
            $this->formatUserName($user) ?: $user->email
        );

        return trim(collect([trim((string) $observaciones), $prefix . $message])->filter()->implode(PHP_EOL));
    }

    private function extractLastReopenReason(?string $observaciones): ?string
    {
        if (! $observaciones) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $observaciones) ?: [];

        foreach (array_reverse($lines) as $line) {
            if (str_starts_with(trim($line), '[reapertura-cierre ')) {
                $parts = explode('] ', $line, 2);
                return $parts[1] ?? null;
            }
        }

        return null;
    }
}
