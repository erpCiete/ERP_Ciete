<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use App\Models\ContextoCliente;
use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\EstacionMoeveExt;
use App\Models\EstacionRepsolExt;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Tarifario;
use App\Models\TarifarioLinea;
use App\Models\TipoDocumento;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use SplFileInfo;

final class CieteExcelImportService
{
    public const MARKER = '[p1-12-excel-real]';

    /**
     * @var array<string, int>
     */
    private array $contextos = [];

    /**
     * @var array<string, int>
     */
    private array $empresas = [];

    /**
     * @var array<int, int>
     */
    private array $touchedFacturas = [];

    public function __construct(
        private readonly ExcelInventoryService $inventory,
        private readonly XlsxWorkbookReader $reader,
        private readonly ExcelHeaderNormalizer $normalizer,
    ) {}

    public function import(string $path, bool $commit = false, ?string $context = null, ?string $file = null, ?int $limit = null): ImportValidationResult
    {
        if (app()->environment('production')) {
            throw new RuntimeException('La importación real desde Excel no se ejecuta en production.');
        }

        $result = new ImportValidationResult(dryRun: ! $commit);
        $root = $this->inventory->resolvePath($path);
        $files = $this->inventory->files($root, $context, $file);

        if ($files === []) {
            $result->error($path, '-', 0, 'No se encontraron Excel .xlsx para importar.');

            return $result;
        }

        if ($limit !== null) {
            $files = array_slice($files, 0, max(0, $limit));
        }

        $useTransaction = ! $commit;

        if ($useTransaction) {
            DB::beginTransaction();
        }

        try {
            $this->ensureBaseData($result);
            $this->purgePreviousOperationalImport();

            $moeve = new MoeveExcelImporter($this, $this->reader, $this->normalizer);
            $repsol = new RepsolExcelImporter($this, $this->reader, $this->normalizer);

            foreach ($files as $fileInfo) {
                $relative = $this->inventory->relativePath($fileInfo->getPathname());
                $contexto = $this->inventory->detectContext($relative);

                match ($contexto) {
                    'MOEVE' => $moeve->import($fileInfo, $result),
                    'REPSOL' => $repsol->import($fileInfo, $result),
                    default => $result->warn(
                        $relative,
                        '-',
                        0,
                        'Excel sin contexto claro; se inventaria pero no se importa.',
                        'file_without_context',
                        'acceptable',
                        'Archivo auxiliar o de mapeo. Mantener fuera salvo decision expresa de CIETE.'
                    ),
                };
            }

            $this->importMoeveTarifarioMaterial($result);
            $this->refreshInvoiceTotals();
            $this->writeImportLog($result, $files);

            if ($useTransaction) {
                DB::rollBack();
            }
        } catch (\Throwable $throwable) {
            if ($useTransaction) {
                DB::rollBack();
            }
            throw $throwable;
        }

        return $result;
    }

    public function inventory(string $path, ?string $context = null, ?string $file = null): array
    {
        return $this->inventory->inventory($path, $context, $file);
    }

    public function contextId(string $code, ImportValidationResult $result): int
    {
        $code = mb_strtoupper($code, 'UTF-8');

        if (isset($this->contextos[$code])) {
            return $this->contextos[$code];
        }

        $row = match ($code) {
            'MOEVE' => ['codigo' => 'MOEVE', 'nombre' => 'MOEVE'],
            'REPSOL' => ['codigo' => 'REPSOL', 'nombre' => 'REPSOL'],
            default => ['codigo' => 'OTROS', 'nombre' => 'OTROS CLIENTES'],
        };

        $contexto = ContextoCliente::query()->updateOrCreate(
            ['codigo' => $row['codigo']],
            ['nombre' => $row['nombre'], 'descripcion' => 'Contexto operativo importación Excel P1-12', 'activo' => true]
        );

        $this->contextos[$code] = (int) $contexto->id_contexto;
        $result->increment('contextos', $contexto->wasRecentlyCreated ? 1 : 0);

        return $this->contextos[$code];
    }

    public function clientEmpresa(string $context, ImportValidationResult $result): int
    {
        $context = mb_strtoupper($context, 'UTF-8');

        if (isset($this->empresas[$context])) {
            return $this->empresas[$context];
        }

        $contextId = $this->contextId($context, $result);
        $data = match ($context) {
            'MOEVE' => ['nombre' => 'MOEVE', 'razon_social' => 'Moeve Energy S.A.', 'cif' => 'A28003119'],
            'REPSOL' => ['nombre' => 'REPSOL', 'razon_social' => 'Repsol S.A.', 'cif' => 'A78374725'],
            default => ['nombre' => 'OTROS CLIENTES', 'razon_social' => 'OTROS CLIENTES', 'cif' => 'B00000000'],
        };

        $empresa = Empresa::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'nombre' => $data['nombre']],
            [
                'nombre_comercial' => $data['nombre'],
                'razon_social' => $data['razon_social'],
                'cif' => $data['cif'],
                'tipo_empresa' => 'cliente',
                'observaciones' => self::MARKER . ' Empresa cliente base para importación real.',
                'activo' => true,
            ]
        );

        $this->empresas[$context] = (int) $empresa->id_empresa;
        $result->increment('empresas', $empresa->wasRecentlyCreated ? 1 : 0);

        return $this->empresas[$context];
    }

    public function ensureContract(string $context, ?string $code, ?string $name, ImportValidationResult $result): int
    {
        $context = mb_strtoupper($context, 'UTF-8');
        $contextId = $this->contextId($context, $result);
        $companyId = $this->clientEmpresa($context, $result);
        $code = $this->cleanText($code) ?: ($context === 'REPSOL' ? 'REPSOL-2023-2027' : 'SIN_CONTRATO');
        $name = $this->cleanText($name) ?: "Contrato {$code} {$context}";

        $contract = Contrato::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'codigo_contrato' => $code],
            [
                'id_empresa_cliente' => $companyId,
                'nombre' => $name,
                'tipo' => 'marco',
                'estado' => 'vigente',
                'observaciones' => self::MARKER . ' Contrato detectado/importado desde Excel actualizado.',
                'activo' => true,
            ]
        );

        $result->increment('contratos', $contract->wasRecentlyCreated ? 1 : 0);
        $this->ensureFacturadora($contract, $companyId, $result);

        return (int) $contract->id_contrato;
    }

    public function ensureTarifario(string $context, int $contractId, string $name, ?string $version, ImportValidationResult $result): int
    {
        $contextId = $this->contextId($context, $result);
        $tarifario = Tarifario::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'id_contrato' => $contractId, 'nombre' => $name, 'version' => $version],
            [
                'factor_multiplicador' => 1,
                'moneda' => 'EUR',
                'observaciones' => self::MARKER . ' Tarifario importado desde Excel actualizado.',
                'activo' => true,
            ]
        );

        $result->increment('tarifarios', $tarifario->wasRecentlyCreated ? 1 : 0);

        return (int) $tarifario->id_tarifario;
    }

    public function ensureTarifarioLinea(string $context, int $tarifarioId, ?string $code, ?string $number, ?string $description, ?float $base, ?float $applied, ImportValidationResult $result): ?int
    {
        $code = $this->cleanText($code) ?: $this->cleanText($number);
        $description = $this->cleanText($description);

        if ($code === null || $description === null) {
            return null;
        }

        $code = $this->shortCode($this->code($code), 30);
        $contextId = $this->contextId($context, $result);
        $line = TarifarioLinea::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'id_tarifario' => $tarifarioId, 'codigo_tarifa' => $code],
            [
                'grupo' => $this->cleanText($number),
                'actuacion' => mb_substr($description, 0, 255),
                'descripcion' => self::MARKER . ' Línea tarifaria detectada en Excel actualizado.',
                'tarifa_anterior' => null,
                'tarifa_base' => $base ?? $applied ?? 0,
                'tarifa_aplicada' => $applied ?? $base ?? 0,
                'id_unidad' => DB::table('unidades')->where('abreviatura', 'ud')->value('id_unidad'),
                'activo' => true,
            ]
        );

        $result->increment('tarifario_lineas', $line->wasRecentlyCreated ? 1 : 0);

        return (int) $line->id_tarifario_linea;
    }

    public function ensureStation(string $context, array $data, ImportValidationResult $result): ?int
    {
        $code = $this->cleanText($data['code'] ?? null);
        if ($code === null) {
            return null;
        }

        $contextId = $this->contextId($context, $result);
        $companyId = $this->clientEmpresa($context, $result);
        $station = EstacionServicio::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'codigo_estacion' => $code],
            [
                'id_empresa_cliente' => $companyId,
                'nombre' => $this->cleanText($data['name'] ?? null) ?: $code,
                'direccion' => $this->cleanText($data['address'] ?? null),
                'codigo_postal' => $this->cleanText($data['postal_code'] ?? null),
                'poblacion' => $this->cleanText($data['city'] ?? null),
                'provincia' => $this->cleanText($data['province'] ?? null),
                'pais' => 'Espana',
                'latitud_wgs84' => $this->toFloat($data['latitude'] ?? null),
                'longitud_wgs84' => $this->toFloat($data['longitude'] ?? null),
                'estado' => $this->cleanText($data['status'] ?? null),
                'f_baja' => $this->toDate($data['inactive_date'] ?? null),
                'observaciones' => self::MARKER . ' Estación importada/actualizada desde Excel real.',
                'activo' => $this->toDate($data['inactive_date'] ?? null) === null,
            ]
        );

        $result->increment('estaciones', $station->wasRecentlyCreated ? 1 : 0);

        if (mb_strtoupper($context, 'UTF-8') === 'MOEVE') {
            EstacionMoeveExt::query()->updateOrCreate(
                ['id_estacion_servicio' => $station->id_estacion_servicio],
                [
                    'n_margenes' => $this->cleanText($data['margin_number'] ?? null),
                    'cod_retailgas' => $this->cleanText($data['retailgas_code'] ?? null),
                    'cod_sociedad' => $this->cleanText($data['billing_company_code'] ?? null),
                    'sociedad' => $this->cleanText($data['billing_company'] ?? null),
                ]
            );
        } else {
            EstacionRepsolExt::query()->updateOrCreate(
                ['id_estacion_servicio' => $station->id_estacion_servicio],
                [
                    'codigo_solred' => $this->cleanText($data['solred_code'] ?? null),
                    'litros_21' => $this->toFloat($data['liters_21'] ?? null),
                    'cliente_nombre' => $this->cleanText($data['client_name'] ?? null),
                    'nom_encargado' => $this->cleanText($data['manager_name'] ?? null),
                    'nom_gerente' => $this->cleanText($data['director_name'] ?? null),
                    'tfno_instalacion' => $this->cleanText($data['phone'] ?? null),
                    'margen' => $this->cleanText($data['margin'] ?? null),
                    'provincial' => $this->cleanText($data['regional'] ?? null),
                ]
            );
        }

        return (int) $station->id_estacion_servicio;
    }

    public function ensureTipoDocumento(string $context, string $code, string $name, bool $doubleInvoice, bool $maintenanceOrder, bool $tariffNumber, ImportValidationResult $result): int
    {
        $contextId = $this->contextId($context, $result);
        $document = TipoDocumento::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'codigo' => $code],
            [
                'nombre' => $name,
                'tiene_doble_factura' => $doubleInvoice,
                'tiene_orden_mto' => $maintenanceOrder,
                'tiene_num_tarifa' => $tariffNumber,
                'activo' => true,
            ]
        );

        return (int) $document->id_tipo_documento;
    }

    public function ensureTipoTrabajo(string $context, int $documentId, ?string $category, ImportValidationResult $result): int
    {
        $contextId = $this->contextId($context, $result);
        $category = $this->cleanText($category) ?: 'Sin categoría';
        $code = $this->shortCode($this->code($category), 30);
        $type = TipoTrabajo::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'id_tipo_documento' => $documentId, 'codigo' => $code],
            ['nombre' => $this->limitText($category, 120), 'activo' => true]
        );

        return (int) $type->id_tipo_trabajo;
    }

    public function createOrUpdateWork(string $context, int $documentId, array $data, ImportValidationResult $result): ?Trabajo
    {
        $operationalNumber = $this->limitText($data['work_number'] ?? null, 100);
        $number = $this->toInt($data['work_number'] ?? null);
        if ($number === null) {
            return null;
        }

        $contextId = $this->contextId($context, $result);
        $companyId = $this->clientEmpresa($context, $result);
        $stationId = $this->ensureStation($context, [
            'code' => $data['station_code'] ?? null,
            'name' => $data['station_name'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
        ], $result);
        $contractId = $this->ensureContract($context, $data['contract_code'] ?? null, null, $result);
        $tarifarioId = $this->ensureTarifario($context, $contractId, $context === 'REPSOL' ? 'TARIFA 23-27 REPSOL' : 'Tarifario ' . ($data['contract_code'] ?? 'general') . ' MOEVE', $context === 'REPSOL' ? '2023-2027' : $this->cleanText($data['contract_code'] ?? null), $result);
        $typeId = $this->ensureTipoTrabajo($context, $documentId, $data['category'] ?? $data['service_description'] ?? null, $result);

        $work = Trabajo::query()->updateOrCreate(
            ['id_contexto' => $contextId, 'id_tipo_documento' => $documentId, 'numero_trabajo' => $number],
            [
                'id_empresa_cliente' => $companyId,
                'id_estacion_servicio' => $stationId,
                'id_tipo_trabajo' => $typeId,
                'id_contrato' => $contractId,
                'id_tarifario' => $tarifarioId,
                'numero_trabajo_operativo' => $operationalNumber,
                'numero_estacion' => $this->limitText($data['station_code'] ?? null, 30),
                'descripcion_trabajo' => $this->cleanText($data['work_description'] ?? null) ?: $this->cleanText($data['service_description'] ?? null),
                'fecha_encargo' => $this->toDate($data['assignment_date'] ?? null),
                'fecha_terminacion' => $this->toDate($data['completion_date'] ?? null),
                'observaciones' => self::MARKER . ' Trabajo importado desde Excel real.',
                'numero_aviso' => $this->limitText($data['notice_number'] ?? null, 100),
                'orden_mantenimiento' => $this->limitText($data['maintenance_order'] ?? null, 100),
                'categoria' => $this->limitText($data['category'] ?? null, 100),
                'responsable_cliente' => $this->limitText($data['responsible_client'] ?? null, 150),
                'estado' => $this->workStatus($data),
                'bloqueado_cierre' => false,
            ]
        );

        $result->increment('trabajos', $work->wasRecentlyCreated ? 1 : 0);

        return $work;
    }

    public function createPedidoItemAndOptionalInvoice(string $context, Trabajo $work, array $data, ImportValidationResult $result, bool $createInvoice = true): ?PedidoItem
    {
        $orderNumber = $this->cleanText($data['order_number'] ?? null);
        if ($orderNumber === null) {
            return null;
        }

        $contextId = $this->contextId($context, $result);
        $orderAmount = $this->toFloat($data['order_amount'] ?? null) ?? $this->toFloat($data['unit_price'] ?? null) ?? 0.0;
        $requestedAmount = $this->toFloat($data['requested_amount'] ?? null) ?? 0.0;
        $invoicedAmount = $this->toFloat($data['invoiced_amount'] ?? null) ?? 0.0;
        $quantity = $this->toFloat($data['quantity'] ?? null) ?? 1.0;
        $unitPrice = $this->toFloat($data['unit_price'] ?? null) ?? ($quantity > 0 ? $orderAmount / $quantity : $orderAmount);
        $tarifarioId = (int) ($work->id_tarifario ?? 0);
        $lineId = null;

        if ($tarifarioId > 0) {
            $lineId = $this->ensureTarifarioLinea(
                $context,
                $tarifarioId,
                $data['service_code'] ?? null,
                $data['tariff_number'] ?? null,
                $data['service_description'] ?? $data['work_description'] ?? null,
                $unitPrice,
                $unitPrice,
                $result
            );
        }

        $pedido = Pedido::query()->firstOrCreate(
            ['id_contexto' => $contextId, 'id_trabajo' => $work->id_trabajo, 'numero_pedido' => $orderNumber],
            [
                'id_tarifario' => $work->id_tarifario,
                'fecha_solicitud' => $this->toDate($data['order_request_date'] ?? null),
                'fecha_recepcion' => $this->toDate($data['order_received_date'] ?? null),
                'importe_pedido' => 0,
                'importe_solicitado' => 0,
                'importe_facturado' => 0,
                'unidades_pedido' => 0,
                'unidades_solicitadas' => 0,
                'estado' => 'pendiente',
                'pedido_completo' => false,
                'tiene_mas_de_1_item' => false,
                'facturado_completo' => false,
                'observaciones' => self::MARKER . ' Pedido importado desde Excel real.',
            ]
        );

        $result->increment('pedidos', $pedido->wasRecentlyCreated ? 1 : 0);

        $item = PedidoItem::query()->create([
            'id_contexto' => $contextId,
            'id_pedido' => $pedido->id_pedido,
            'id_tarifario_linea' => $lineId,
            'codigo_servicio' => $this->cleanText($data['service_code'] ?? null),
            'numero_tarifa' => $this->cleanText($data['tariff_number'] ?? null),
            'descripcion_servicio' => mb_substr($this->cleanText($data['service_description'] ?? null) ?: ($work->descripcion_trabajo ?: 'Servicio importado'), 0, 255),
            'precio_unitario' => $unitPrice,
            'cantidad' => $quantity,
            'total_linea' => $orderAmount,
        ]);
        $result->increment('pedido_items');

        $pedido->importe_pedido = (float) $pedido->importe_pedido + $orderAmount;
        $pedido->importe_solicitado = (float) $pedido->importe_solicitado + $requestedAmount;
        $pedido->importe_facturado = (float) $pedido->importe_facturado + $invoicedAmount;
        $pedido->unidades_pedido = (float) $pedido->unidades_pedido + $quantity;
        $pedido->unidades_solicitadas = (float) $pedido->unidades_solicitadas + ($this->toFloat($data['requested_units'] ?? null) ?? 0);
        $pedido->estado = $this->pedidoStatus((float) $pedido->importe_pedido, (float) $pedido->importe_facturado, (float) $pedido->importe_solicitado);
        $pedido->pedido_completo = (float) $pedido->importe_pedido > 0;
        $pedido->tiene_mas_de_1_item = $pedido->items()->count() > 1;
        $pedido->facturado_completo = (float) $pedido->importe_pedido > 0 && abs((float) $pedido->importe_pedido - (float) $pedido->importe_facturado) < 0.01;
        $pedido->save();

        if ($createInvoice && $invoicedAmount > 0.0) {
            $this->createFacturaItem($context, $work, $item, $data, $invoicedAmount, $result);
        }

        return $item;
    }

    public function createFacturaItem(string $context, Trabajo $work, PedidoItem $item, array $data, float $amount, ImportValidationResult $result): void
    {
        $invoiceNumber = $this->invoiceNumber($context, $work, $data);
        if ($invoiceNumber === null) {
            $invoiceNumber = 'SIN_NUMERO-' . mb_strtoupper($context, 'UTF-8') . '-' . $work->id_trabajo . '-' . ($this->toDate($data['invoice_date'] ?? $data['invoice_date_1'] ?? null) ?? 'SIN_FECHA');
            $result->warn(
                $data['_file'] ?? '-',
                $data['_sheet'] ?? '-',
                (int) ($data['_row'] ?? 0),
                "Factura sin número explícito; se usa {$invoiceNumber} como identificador interno.",
                'invoice_number_normalized',
                'technical_improvement',
                'Revisar si el origen debe traer número explícito o si este identificador interno es suficiente para conciliación local.'
            );
        }

        $contextId = $this->contextId($context, $result);
        $empresaFacturadoraId = $this->clientEmpresa($context, $result);
        $factura = Factura::query()->firstOrCreate(
            [
                'id_contexto' => $contextId,
                'id_empresa_facturadora' => $empresaFacturadoraId,
                'numero_factura' => $invoiceNumber,
            ],
            [
                'id_trabajo' => $work->id_trabajo,
                'id_contrato' => $work->id_contrato,
                'id_empresa_cliente' => $work->id_empresa_cliente,
                'numero_factura_ccp' => $this->cleanText($data['invoice_ccp'] ?? null),
                'serie' => $context === 'REPSOL' ? 'R' : null,
                'orden_factura' => 1,
                'fecha_solicitud' => $this->toDate($data['invoice_date'] ?? $data['invoice_date_1'] ?? null),
                'fecha_emision' => $this->toDate($data['invoice_date'] ?? $data['invoice_date_1'] ?? null),
                'importe' => 0,
                'base_imponible' => 0,
                'iva' => 0,
                'retencion' => 0,
                'total' => 0,
                'estado' => 'emitida',
                'autofactura' => $context === 'REPSOL',
                'sociedad' => $this->cleanText($data['billing_company'] ?? null),
                'observaciones' => self::MARKER . ' Factura cabecera importada; detalle por factura_items.',
            ]
        );

        $result->increment('facturas', $factura->wasRecentlyCreated ? 1 : 0);

        FacturaItem::query()->create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $item->id_pedido_item,
            'unidades_facturadas' => $this->toFloat($data['requested_units'] ?? null) ?? $this->toFloat($data['quantity'] ?? null) ?? 1,
            'importe_facturado' => $amount,
            'observaciones' => self::MARKER . ' Línea de factura importada desde Excel real.',
        ]);

        $result->increment('factura_items');
        $this->touchedFacturas[(int) $factura->id_factura] = (int) $factura->id_factura;
    }

    public function value(array $row, array $map, string $key): ?string
    {
        if (! isset($map[$key])) {
            return null;
        }

        return $row[$map[$key]] ?? null;
    }

    public function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        if ($value === '' || in_array(mb_strtoupper($value, 'UTF-8'), ['-', '--', 'NA', 'N/A', '´--', '#VALUE!'], true)) {
            return null;
        }

        return $value;
    }

    public function limitText(mixed $value, int $limit): ?string
    {
        $value = $this->cleanText($value);

        return $value === null ? null : mb_substr($value, 0, $limit);
    }

    public function toFloat(mixed $value): ?float
    {
        $value = $this->cleanText($value);
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            $value = str_replace(['€', ' '], '', $value);
            if (str_contains($value, ',') && ! str_contains($value, '.')) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        }

        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    public function toInt(mixed $value): ?int
    {
        $value = $this->cleanText($value);
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        preg_match('/\d+/', $value, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }

    public function toDate(mixed $value): ?string
    {
        $value = $this->cleanText($value);
        if ($value === null) {
            return null;
        }

        if (is_numeric($value) && (float) $value > 1000) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    public function code(string $value): string
    {
        $normalized = $this->normalizer->normalize($value);
        $code = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? 'sin_codigo';

        return mb_strtoupper(trim($code, '_') ?: 'SIN_CODIGO', 'UTF-8');
    }

    public function shortCode(string $code, int $limit): string
    {
        if (strlen($code) <= $limit) {
            return $code;
        }

        $hash = substr(sha1($code), 0, 7);

        return rtrim(substr($code, 0, max(1, $limit - 8)), '_') . '_' . $hash;
    }

    /**
     * @return array<string, mixed>
     */
    public function integritySummary(): array
    {
        return [
            'trabajos_borrador' => DB::table('trabajos')->where('estado', 'borrador')->count(),
            'trabajos_cerrado' => DB::table('trabajos')->where('estado', 'cerrado')->count(),
            'pedidos_borrador' => DB::table('pedidos')->where('estado', 'borrador')->count(),
            'pedidos_cerrado' => DB::table('pedidos')->where('estado', 'cerrado')->count(),
            'factura_pedidos_exists' => Schema::hasTable('factura_pedidos'),
            'facturas_sin_items' => DB::table('facturas')->whereRaw('not exists (select 1 from factura_items where factura_items.id_factura = facturas.id_factura)')->count(),
            'factura_items_sin_pedido_item' => DB::table('factura_items')->leftJoin('pedido_items', 'pedido_items.id_pedido_item', '=', 'factura_items.id_pedido_item')->whereNull('pedido_items.id_pedido_item')->count(),
            'pedido_items_sin_pedido' => DB::table('pedido_items')->leftJoin('pedidos', 'pedidos.id_pedido', '=', 'pedido_items.id_pedido')->whereNull('pedidos.id_pedido')->count(),
            'pedidos_sin_trabajo' => DB::table('pedidos')->leftJoin('trabajos', 'trabajos.id_trabajo', '=', 'pedidos.id_trabajo')->whereNull('trabajos.id_trabajo')->count(),
            'trabajos_sin_contexto' => DB::table('trabajos')->whereNull('id_contexto')->count(),
            'estaciones_sin_contexto' => DB::table('estaciones_servicio')->whereNull('id_contexto')->count(),
            'estaciones_duplicadas_contexto' => DB::table('estaciones_servicio')->select('id_contexto', 'codigo_estacion')->groupBy('id_contexto', 'codigo_estacion')->havingRaw('count(*) > 1')->count(),
            'sociedades_facturadoras_sin_cif' => DB::table('facturas')->join('empresas', 'empresas.id_empresa', '=', 'facturas.id_empresa_facturadora')->whereNotNull('facturas.id_empresa_facturadora')->whereNull('empresas.cif')->count(),
            'facturas_con_items_sin_sociedad_valida' => DB::table('facturas')
                ->whereRaw('exists (select 1 from factura_items where factura_items.id_factura = facturas.id_factura)')
                ->whereRaw('not exists (select 1 from contrato_empresas_facturadoras cef where cef.id_contrato = facturas.id_contrato and cef.id_empresa = facturas.id_empresa_facturadora and cef.id_contexto = facturas.id_contexto and cef.activo = 1)')
                ->count(),
        ];
    }

    private function ensureBaseData(ImportValidationResult $result): void
    {
        foreach (['MOEVE', 'REPSOL', 'OTROS'] as $context) {
            $this->contextId($context, $result);
        }

        $this->clientEmpresa('MOEVE', $result);
        $this->clientEmpresa('REPSOL', $result);

        $moeve772 = $this->ensureContract('MOEVE', '772', 'Contrato 772 MOEVE', $result);
        $this->ensureTarifario('MOEVE', $moeve772, 'Contrato 772 MOEVE - Tarifario', 'Contrato 772', $result);

        $repsol = $this->ensureContract('REPSOL', 'REPSOL-2023-2027', 'Tarifa Repsol 2023-2027', $result);
        $this->ensureTarifario('REPSOL', $repsol, 'TARIFA 23-27 REPSOL', '2023-2027', $result);
    }

    private function ensureFacturadora(Contrato $contract, int $companyId, ImportValidationResult $result): void
    {
        $pivot = ContratoEmpresaFacturadora::query()->updateOrCreate(
            ['id_contrato' => $contract->id_contrato, 'id_empresa' => $companyId, 'id_contexto' => $contract->id_contexto],
            ['activo' => true, 'observaciones' => self::MARKER . ' Sociedad facturadora validada para contrato importado.']
        );

        $result->increment('contrato_empresas_facturadoras', $pivot->wasRecentlyCreated ? 1 : 0);
    }

    private function purgePreviousOperationalImport(): void
    {
        $markers = [self::MARKER . '%', '[demo-ciete-excel]%', '[demo-operativa]%', '[demo-volumen]%'];

        $workIds = DB::table('trabajos')
            ->where(function ($query) use ($markers): void {
                foreach ($markers as $marker) {
                    $query->orWhere('observaciones', 'like', $marker);
                }
            })
            ->pluck('id_trabajo');

        $pedidoIds = DB::table('pedidos')->whereIn('id_trabajo', $workIds)
            ->orWhere(function ($query) use ($markers): void {
                foreach ($markers as $marker) {
                    $query->orWhere('observaciones', 'like', $marker);
                }
            })
            ->pluck('id_pedido');

        $pedidoItemIds = DB::table('pedido_items')->whereIn('id_pedido', $pedidoIds)->pluck('id_pedido_item');
        $facturaIds = DB::table('facturas')
            ->whereIn('id_trabajo', $workIds)
            ->orWhere(function ($query) use ($markers): void {
                foreach ($markers as $marker) {
                    $query->orWhere('observaciones', 'like', $marker);
                }
            })
            ->pluck('id_factura');

        DB::table('factura_items')
            ->whereIn('id_factura', $facturaIds)
            ->orWhereIn('id_pedido_item', $pedidoItemIds)
            ->delete();
        DB::table('facturas')->whereIn('id_factura', $facturaIds)->delete();
        DB::table('pedido_items')->whereIn('id_pedido_item', $pedidoItemIds)->delete();
        DB::table('pedidos')->whereIn('id_pedido', $pedidoIds)->delete();
        DB::table('trabajos')->whereIn('id_trabajo', $workIds)->delete();
    }

    private function importMoeveTarifarioMaterial(ImportValidationResult $result): void
    {
        $path = base_path('docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx');
        if (! is_file($path)) {
            return;
        }

        $contractId = $this->ensureContract('MOEVE', '772', 'Contrato 772 MOEVE', $result);
        $tarifarioId = $this->ensureTarifario('MOEVE', $contractId, 'Contrato 772 MOEVE - Tarifario', 'Contrato 772', $result);

        foreach ($this->reader->rows($path, 'TARIFARIO') as $row) {
            if ($row['row'] <= 2) {
                continue;
            }

            $values = $row['values'];
            $this->ensureTarifarioLinea('MOEVE', $tarifarioId, $values[0] ?? null, null, $values[1] ?? null, $this->toFloat($values[3] ?? null), $this->toFloat($values[3] ?? null), $result);
        }
    }

    private function refreshInvoiceTotals(): void
    {
        foreach ($this->touchedFacturas as $facturaId) {
            $total = (float) FacturaItem::query()->where('id_factura', $facturaId)->sum('importe_facturado');
            Factura::query()->whereKey($facturaId)->update([
                'importe' => $total,
                'base_imponible' => $total,
                'iva' => round($total * 0.21, 2),
                'total' => $total,
            ]);
        }
    }

    /**
     * @param array<int, SplFileInfo> $files
     */
    private function writeImportLog(ImportValidationResult $result, array $files): void
    {
        if (! Schema::hasTable('importaciones')) {
            return;
        }

        $adminId = DB::table('usuarios')->where('email', 'admin@ciete.es')->value('id_usuario')
            ?? DB::table('usuarios')->value('id_usuario');

        if ($adminId === null) {
            return;
        }

        foreach ($files as $file) {
            $relative = $this->inventory->relativePath($file->getPathname());
            $context = $this->inventory->detectContext($relative);
            if (! in_array($context, ['MOEVE', 'REPSOL'], true)) {
                continue;
            }

            $contextId = $this->contextId($context, $result);
            $fileSummary = $result->files[$relative] ?? [];
            $counters = $fileSummary['counters'] ?? [];
            $issues = array_values(array_filter(
                $result->issues,
                static fn (array $issue): bool => ($issue['file'] ?? null) === $relative
            ));

            $importId = DB::table('importaciones')->insertGetId([
                'id_contexto' => $contextId,
                'id_usuario' => $adminId,
                'tipo' => $this->importTypeForLog($fileSummary['type'] ?? null),
                'archivo_original' => basename($relative),
                'total_filas' => (int) ($counters['rows_seen'] ?? 0),
                'filas_importadas' => (int) ($counters['rows_imported'] ?? 0),
                'filas_ignoradas' => (int) ($counters['rows_ignored'] ?? 0),
                'filas_con_error' => (int) ($counters['rows_with_error'] ?? 0),
                'filas_con_aviso' => (int) ($counters['rows_with_warning'] ?? 0),
                'filas_duplicadas' => 0,
                'estado' => 'completado',
                'version_importacion' => 13,
                'started_at' => now(),
                'finished_at' => now(),
                'resumen_json' => json_encode([
                    'context' => $context,
                    'summary' => $fileSummary,
                    'issues_by_classification' => $this->groupIssuesByKey($issues, 'classification'),
                    'issues_by_code' => $this->groupIssuesByKey($issues, 'code'),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detailRows = [];
            $now = now();

            foreach ($issues as $issue) {
                $detailRows[] = [
                    'id_importacion' => $importId,
                    'archivo_origen' => basename($relative),
                    'hoja_origen' => $issue['sheet'] ?? '-',
                    'numero_fila' => max(0, (int) ($issue['row'] ?? 0)),
                    'datos_json' => json_encode($issue['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'estado' => ($issue['severity'] ?? 'warning') === 'error' ? 'error' : 'pendiente',
                    'resultado' => $issue['result'] ?? 'warning',
                    'tipo_fila' => $issue['code'] ?? 'warning',
                    'severidad' => $issue['severity'] ?? 'warning',
                    'codigo' => $issue['code'] ?? 'warning',
                    'clasificacion' => $issue['classification'] ?? 'acceptable',
                    'mensaje_error' => $issue['message'] ?? null,
                    'decision_sugerida' => $issue['decision'] ?? null,
                    'id_registro_destino' => null,
                    'tipo_registro_destino' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($detailRows, 500) as $chunk) {
                DB::table('importacion_filas')->insert($chunk);
            }
        }

        if (Schema::hasTable('audit_log')) {
            $summary = $result->toArray();
            DB::table('audit_log')->insert([
                'id_contexto' => null,
                'id_usuario' => $adminId,
                'accion' => 'importar',
                'tabla' => 'importaciones',
                'modulo' => 'importaciones',
                'entity_type' => self::class,
                'entity_id' => null,
                'registro_id' => null,
                'campo' => null,
                'valor_anterior' => null,
                'valor_nuevo' => 'P1-13',
                'datos_anteriores' => null,
                'datos_nuevos' => json_encode([
                    'files' => array_map(fn (SplFileInfo $file): string => $this->inventory->relativePath($file->getPathname()), $files),
                    'rows_seen' => $summary['rows_seen'],
                    'rows_imported' => $summary['rows_imported'],
                    'rows_ignored' => $summary['rows_ignored'],
                    'rows_with_warning' => $summary['rows_with_warning'],
                    'rows_with_error' => $summary['rows_with_error'],
                    'imported' => $summary['imported'],
                    'files_count' => count($summary['files']),
                    'top_warning_codes' => array_slice($this->groupIssuesByKey($result->warnings, 'code'), 0, 15, true),
                    'top_warning_classes' => array_slice($this->groupIssuesByKey($result->warnings, 'classification'), 0, 10, true),
                    'top_error_codes' => array_slice($this->groupIssuesByKey($result->errors, 'code'), 0, 10, true),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'descripcion' => self::MARKER . ' Importación real controlada desde Excel actualizados.',
                'created_at' => now(),
            ]);
        }
    }

    private function importTypeForLog(?string $type): string
    {
        return match ($type) {
            'estaciones' => 'estaciones',
            'tarifario' => 'tarifario',
            'facturas_emitidas', 'facturas' => 'facturas',
            default => 'trabajos',
        };
    }

    /**
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int>
     */
    private function groupIssuesByKey(array $issues, string $key): array
    {
        $grouped = [];

        foreach ($issues as $issue) {
            $group = (string) ($issue[$key] ?? 'unknown');
            $grouped[$group] = ($grouped[$group] ?? 0) + 1;
        }

        arsort($grouped);

        return $grouped;
    }

    private function workStatus(array $data): string
    {
        $status = $this->normalizer->normalize($this->cleanText($data['status'] ?? null));
        $invoiced = $this->toFloat($data['invoiced_amount'] ?? null) ?? 0.0;

        if (str_contains($status, 'anulad') || str_contains($status, 'cancel')) {
            return 'cancelado';
        }

        if ($invoiced > 0.0) {
            return 'facturado';
        }

        if (str_contains($status, 'solicitar') || str_contains($status, 'pte')) {
            return 'pendiente_facturar';
        }

        if ($this->toDate($data['completion_date'] ?? null) !== null) {
            return 'terminado';
        }

        return 'en_curso';
    }

    private function pedidoStatus(float $orderAmount, float $invoicedAmount, float $requestedAmount): string
    {
        if ($orderAmount > 0 && abs($orderAmount - $invoicedAmount) < 0.01) {
            return 'facturado';
        }

        if ($invoicedAmount > 0) {
            return 'facturado_parcial';
        }

        if ($requestedAmount > 0) {
            return 'recibido';
        }

        return 'solicitado';
    }

    private function invoiceNumber(string $context, Trabajo $work, array $data): ?string
    {
        foreach (['invoice_number', 'invoice_number_1', 'invoice_number_2', 'invoice_ccp'] as $key) {
            $value = $this->cleanText($data[$key] ?? null);
            if ($value !== null && ! is_numeric($value)) {
                return $value;
            }
        }

        if ($context === 'MOEVE') {
            return $this->cleanText($data['invoice_number'] ?? null);
        }

        return null;
    }
}
