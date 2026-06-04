<?php

namespace App\Services\Exports;

use App\Models\Pedido;

class MoevePedidoExportService
{
    private const PENDING_TEXT = 'Pendiente de parametrizar';

    private const DEFAULT_NOTE = 'Serán de aplicación las condiciones del Contrato para Proyecto de Ingeniería entre MOEVE y Ciete.';

    /**
     * @return array{pedido: Pedido, summary: array<string, mixed>, rows: array<int, array<string, mixed>>}
     */
    public function build(Pedido $pedido): array
    {
        $pedido = $pedido->load([
            'items' => function ($query): void {
                $query->with(['tarifarioLinea.unidad'])->orderBy('id_pedido_item');
            },
            'trabajo.estacion.moeveExt',
            'trabajo.contrato.empresa',
            'trabajo.responsableCiete',
            'tarifario.contrato.empresa',
        ]);

        if (! $pedido->trabajo) {
            abort(422, 'El pedido no tiene trabajo asociado y no se puede exportar.');
        }

        if (! $pedido->tarifario) {
            abort(422, 'El pedido no tiene tarifario asociado y no se puede exportar.');
        }

        if ($pedido->items->isEmpty()) {
            abort(422, 'El pedido no tiene lineas exportables.');
        }

        $trabajo = $pedido->trabajo;
        $estacion = $trabajo->estacion;
        $moeveExt = $estacion?->moeveExt;
        $contrato = $trabajo->contrato ?? $pedido->tarifario->contrato;

        $importeCalculado = 0.0;
        $unidadesCalculadas = 0.0;
        $rows = [];

        foreach ($pedido->items as $index => $item) {
            $linea = $item->tarifarioLinea;

            if (! $linea || (int) $linea->id_tarifario !== (int) $pedido->id_tarifario) {
                abort(422, 'El pedido contiene lineas que no pertenecen al tarifario asociado.');
            }

            $cantidad = round((float) $item->cantidad, 3);
            $precioUnitario = round((float) $item->precio_unitario, 2);
            $totalLinea = round((float) $item->total_linea, 2);
            $producto = $this->firstFilled([
                $item->codigo_servicio,
                $linea->codigo_tarifa,
                $item->numero_tarifa,
            ]);
            $textoProveedor = $this->firstFilled([
                $item->descripcion_servicio,
                $linea->actuacion,
                $linea->descripcion,
            ]);
            $detalle = $this->normalizeText($linea->descripcion);

            if ($detalle !== null && $textoProveedor !== null && mb_strtoupper($detalle) === mb_strtoupper($textoProveedor)) {
                $detalle = null;
            }

            $importeCalculado += $totalLinea;
            $unidadesCalculadas += $cantidad;

            $rows[] = [
                'line_number' => $index + 1,
                'item' => $index + 1,
                'producto' => $producto ?? self::PENDING_TEXT,
                'codigo_moeve' => $producto ?? self::PENDING_TEXT,
                'id_producto_contrato' => $this->normalizeText($item->numero_tarifa) ?? '',
                'texto_proveedor' => $textoProveedor ?? self::PENDING_TEXT,
                'descripcion' => $textoProveedor ?? self::PENDING_TEXT,
                'detalle' => $detalle,
                'unidad' => (string) ($linea->unidad?->abreviatura ?: $linea->unidad?->nombre ?: ''),
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'total_linea' => $totalLinea,
            ];
        }

        $importeCalculado = round($importeCalculado, 2);
        $unidadesCalculadas = round($unidadesCalculadas, 3);

        if (abs($importeCalculado - round((float) $pedido->importe_pedido, 2)) >= 0.01) {
            abort(422, 'El importe del pedido no coincide con la suma de sus lineas.');
        }

        if (abs($unidadesCalculadas - round((float) $pedido->unidades_pedido, 3)) >= 0.001) {
            abort(422, 'Las unidades del pedido no coinciden con la suma de sus lineas.');
        }

        $pendingFields = [];
        $stationCode = $this->valueOrPending($this->normalizeText($estacion?->codigo_estacion), 'E.S. Nº', $pendingFields);
        $stationName = $this->valueOrPending($this->normalizeText($estacion?->nombre), 'Nombre estación', $pendingFields);
        $localidad = $this->valueOrPending(
            $this->buildLocalidad($estacion?->poblacion, $estacion?->provincia),
            'Localidad',
            $pendingFields
        );
        $encargado = $this->valueOrPending(
            $this->firstFilled([
                $trabajo->responsable_cliente,
                $moeveExt?->tecnico_gestion,
                $moeveExt?->responsable_gestor,
                $this->userName($trabajo->responsableCiete),
            ]),
            'Trabajo encargado por',
            $pendingFields
        );
        $descripcionTrabajo = $this->valueOrPending(
            $this->firstFilled([
                $trabajo->descripcion_trabajo,
                $trabajo->categoria,
            ]),
            'Descripción del trabajo',
            $pendingFields
        );
        $sociedadAriba = $this->valueOrPending(
            $this->firstFilled([
                $moeveExt?->cod_sociedad,
                $contrato?->ariba_sociedad,
            ]),
            'Sociedad',
            $pendingFields
        );
        $ctaMayor = $this->valueOrPending($this->normalizeText($contrato?->ariba_cta_mayor), 'Cta. de Mayor', $pendingFields);
        $propuestaOpex = $this->valueOrPending($this->normalizeText($contrato?->ariba_propuesta_opex), 'Propuesta de Inversión / Opex acción gasto', $pendingFields);
        $accionGasto = $this->valueOrPending($this->normalizeText($contrato?->ariba_accion_gasto), 'Acción de gasto AC', $pendingFields);
        $proveedorContrato = $this->valueOrPending($this->resolveProveedorContrato($contrato), 'Proveedor / Contrato', $pendingFields);
        $aprobadoPor = $this->valueOrPending(
            $this->firstFilled([
                $this->userName($trabajo->responsableCiete),
                $trabajo->responsable_cliente,
            ]),
            'Aprobado por',
            $pendingFields
        );

        return [
            'pedido' => $pedido,
            'summary' => [
                'offer_title' => 'OFERTA PRECIOS ACUERDO',
                'numero_pedido' => (string) ($pedido->numero_pedido ?? ''),
                'fecha' => optional($pedido->fecha_solicitud)->format('d/m/Y') ?: '',
                'fecha_iso' => optional($pedido->fecha_solicitud)->format('Y-m-d') ?: (string) ($pedido->fecha_solicitud ?? ''),
                'es_numero' => $stationCode,
                'codigo_estacion' => $stationCode,
                'client_code' => $stationCode,
                'nombre_estacion' => $stationName,
                'localidad' => $localidad,
                'direccion_estacion' => (string) collect([
                    $estacion?->direccion,
                    $estacion?->codigo_postal,
                    $estacion?->poblacion,
                    $estacion?->provincia,
                ])->filter()->implode(' · '),
                'trabajo_encargado_por' => $encargado,
                'trabajo_numero' => $trabajo->numeroTrabajoVisible(),
                'trabajo_descripcion' => $descripcionTrabajo,
                'descripcion' => $descripcionTrabajo,
                'sociedad_ariba' => $sociedadAriba,
                'cta_mayor' => $ctaMayor,
                'propuesta_opex' => $propuestaOpex,
                'accion_gasto' => $accionGasto,
                'proveedor_contrato' => $proveedorContrato,
                'confirmar' => 'SI/NO',
                'nota' => self::DEFAULT_NOTE,
                'aprobado_por' => $aprobadoPor,
                'contrato_codigo' => (string) ($contrato?->codigo_contrato ?? ''),
                'contrato_nombre' => (string) ($contrato?->nombre ?? ''),
                'tarifario_nombre' => (string) ($pedido->tarifario->nombre ?? ''),
                'tarifario_version' => (string) ($pedido->tarifario->version ?? ''),
                'line_count' => count($rows),
                'importe_pedido' => $importeCalculado,
                'unidades_pedido' => $unidadesCalculadas,
                'has_pending_fields' => $pendingFields !== [],
                'pending_fields' => array_values(array_unique($pendingFields)),
            ],
            'rows' => $rows,
        ];
    }

    private function resolveProveedorContrato($contrato): ?string
    {
        if (! $contrato) {
            return null;
        }

        $codigo = $this->normalizeText($contrato->codigo_contrato);

        if (! $codigo) {
            return null;
        }

        $nombreProveedor = $this->normalizeText($contrato->ariba_nombre_proveedor);

        return $nombreProveedor
            ? $codigo . ' / ' . $nombreProveedor
            : $codigo . ' / ' . self::PENDING_TEXT;
    }

    private function buildLocalidad(mixed $poblacion, mixed $provincia): ?string
    {
        $city = $this->normalizeText($poblacion);
        $province = $this->normalizeText($provincia);

        if ($city && $province) {
            return sprintf('%s (%s)', $city, mb_strtoupper($province));
        }

        return $city ?? $province;
    }

    private function userName($user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->normalizeText(trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')));
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $normalized = $this->normalizeText($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $pendingFields
     */
    private function valueOrPending(?string $value, string $label, array &$pendingFields): string
    {
        if ($value === null || $value === '') {
            $pendingFields[] = $label;

            return self::PENDING_TEXT;
        }

        return $value;
    }

    private function normalizeText(mixed $value, string $suffix = ''): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        return $suffix !== '' ? $text . $suffix : $text;
    }
}
