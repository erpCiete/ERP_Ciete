<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

final class PrivateSeederGeneratorService
{
    private const PRIVATE_NAMESPACE = 'Database\\Seeders\\Private';

    /**
     * @return array<string, mixed>
     */
    public function inspect(string $path, ?string $only = null): array
    {
        return [
            'inventory' => app(ExcelInventoryService::class)->inventory($path, $only ? mb_strtoupper($only, 'UTF-8') : null),
            'datasets' => $this->datasetSummary($only),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function writeSeeders(?string $only = null): array
    {
        $directory = base_path('database/seeders/private');
        File::ensureDirectoryExists($directory);

        $datasets = $this->datasets($only);

        foreach ($datasets as $dataset) {
            File::put(
                $directory . DIRECTORY_SEPARATOR . $dataset['class'] . '.php',
                $this->renderSeeder($dataset['class'], $dataset['sets'], $dataset['note'] ?? null)
            );
        }

        File::put(
            $directory . DIRECTORY_SEPARATOR . 'CieteRealDataSeeder.php',
            $this->renderMasterSeeder(array_column($datasets, 'class'))
        );

        return [
            'directory' => $directory,
            'datasets' => array_map(static fn (array $dataset): array => [
                'class' => $dataset['class'],
                'table' => implode(', ', array_column($dataset['sets'], 'table')),
                'rows' => array_sum(array_map(static fn (array $set): int => count($set['rows']), $dataset['sets'])),
            ], $datasets),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function datasetSummary(?string $only = null): array
    {
        $datasets = $this->datasets($only);
        $summary = [];

        foreach ($datasets as $dataset) {
            $summary[$dataset['class']] = array_sum(array_map(
                static fn (array $set): int => count($set['rows']),
                $dataset['sets']
            ));
        }

        return $summary;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function datasets(?string $only = null): array
    {
        $ids = $this->realEntityIds($only);
        $contextIds = $ids['context_ids'];
        $clientIds = $ids['client_company_ids'];
        $billingCompanyIds = $ids['billing_company_ids'];
        $empresaIds = array_values(array_unique(array_merge($clientIds, $billingCompanyIds)));

        return [
            [
                'class' => 'CieteRealCatalogosSeeder',
                'sets' => [
                    $this->set('contextos_cliente', 'id_contexto', fn ($query) => $query->whereIn('id_contexto', $contextIds)),
                    $this->set('unidades', 'id_unidad', fn ($query) => $query->whereIn('id_unidad', $ids['unit_ids'])),
                    $this->set('tipos_documento', 'id_tipo_documento', fn ($query) => $query->whereIn('id_tipo_documento', $ids['document_type_ids'])),
                    $this->set('tipos_trabajo', 'id_tipo_trabajo', fn ($query) => $query->whereIn('id_tipo_trabajo', $ids['work_type_ids'])),
                ],
            ],
            [
                'class' => 'CieteRealEmpresasSeeder',
                'sets' => [
                    $this->set('empresas', 'id_empresa', fn ($query) => $query->whereIn('id_empresa', $empresaIds)),
                ],
                'note' => 'Incluye clientes operativos y sociedades facturadoras reales necesarias por facturas/pivots.',
            ],
            [
                'class' => 'CieteRealContratosTarifariosSeeder',
                'sets' => [
                    $this->set('contratos', 'id_contrato', fn ($query) => $query->whereIn('id_contrato', $ids['contract_ids'])),
                    $this->set('tarifarios', 'id_tarifario', fn ($query) => $query->whereIn('id_tarifario', $ids['tariff_ids'])),
                    $this->set('tarifario_lineas', 'id_tarifario_linea', fn ($query) => $query->whereIn('id_tarifario_linea', $ids['tariff_line_ids'])),
                    $this->set('contrato_empresas_facturadoras', 'id', fn ($query) => $query->whereIn('id', $ids['contract_company_ids'])),
                ],
            ],
            [
                'class' => 'CieteRealEstacionesSeeder',
                'sets' => [
                    $this->set('estaciones_servicio', 'id_estacion_servicio', fn ($query) => $query->whereIn('id_estacion_servicio', $ids['station_ids'])),
                    $this->set('estaciones_moeve_ext', 'id_estacion_servicio', fn ($query) => $query->whereIn('id_estacion_servicio', $ids['station_ids'])),
                    $this->set('estaciones_repsol_ext', 'id_estacion_servicio', fn ($query) => $query->whereIn('id_estacion_servicio', $ids['station_ids'])),
                ],
            ],
            [
                'class' => 'CieteRealTrabajosSeeder',
                'sets' => [
                    $this->set('trabajos', 'id_trabajo', fn ($query) => $query->whereIn('id_trabajo', $ids['work_ids'])),
                ],
            ],
            [
                'class' => 'CieteRealPedidosSeeder',
                'sets' => [
                    $this->set('pedidos', 'id_pedido', fn ($query) => $query->whereIn('id_pedido', $ids['order_ids'])),
                    $this->set('pedido_items', 'id_pedido_item', fn ($query) => $query->whereIn('id_pedido_item', $ids['order_item_ids'])),
                ],
            ],
            [
                'class' => 'CieteRealFacturasSeeder',
                'sets' => [
                    $this->set('facturas', 'id_factura', fn ($query) => $query->whereIn('id_factura', $ids['invoice_ids'])),
                    $this->set('factura_items', 'id_factura_item', fn ($query) => $query->whereIn('id_factura_item', $ids['invoice_item_ids'])),
                ],
            ],
        ];

        $datasets = [
            [
                'class' => 'CieteRealContextosSeeder',
                'table' => 'contextos_cliente',
                'primary_key' => 'id_contexto',
                'rows' => $this->rows('contextos_cliente', 'id_contexto', fn ($query) => $query->whereIn('id_contexto', $contextIds)),
            ],
            [
                'class' => 'CieteRealClientesSeeder',
                'table' => 'empresas',
                'primary_key' => 'id_empresa',
                'rows' => $this->rows('empresas', 'id_empresa', fn ($query) => $query->whereIn('id_empresa', $clientIds)),
            ],
            [
                'class' => 'CieteRealSociedadesSeeder',
                'table' => 'empresas',
                'primary_key' => 'id_empresa',
                'rows' => $this->rows('empresas', 'id_empresa', fn ($query) => $query->whereIn('id_empresa', array_values(array_diff($billingCompanyIds, $clientIds)))),
                'note' => 'Las sociedades que coinciden con los clientes principales ya quedan cubiertas por CieteRealClientesSeeder.',
            ],
            [
                'class' => 'CieteRealUnidadesSeeder',
                'table' => 'unidades',
                'primary_key' => 'id_unidad',
                'rows' => $this->rows('unidades', 'id_unidad', fn ($query) => $query->whereIn('id_unidad', $ids['unit_ids'])),
            ],
            [
                'class' => 'CieteRealTiposDocumentoSeeder',
                'table' => 'tipos_documento',
                'primary_key' => 'id_tipo_documento',
                'rows' => $this->rows('tipos_documento', 'id_tipo_documento', fn ($query) => $query->whereIn('id_tipo_documento', $ids['document_type_ids'])),
            ],
            [
                'class' => 'CieteRealTiposTrabajoSeeder',
                'table' => 'tipos_trabajo',
                'primary_key' => 'id_tipo_trabajo',
                'rows' => $this->rows('tipos_trabajo', 'id_tipo_trabajo', fn ($query) => $query->whereIn('id_tipo_trabajo', $ids['work_type_ids'])),
            ],
            [
                'class' => 'CieteRealContratosSeeder',
                'table' => 'contratos',
                'primary_key' => 'id_contrato',
                'rows' => $this->rows('contratos', 'id_contrato', fn ($query) => $query->whereIn('id_contrato', $ids['contract_ids'])),
            ],
            [
                'class' => 'CieteRealTarifariosSeeder',
                'table' => 'tarifarios',
                'primary_key' => 'id_tarifario',
                'rows' => $this->rows('tarifarios', 'id_tarifario', fn ($query) => $query->whereIn('id_tarifario', $ids['tariff_ids'])),
            ],
            [
                'class' => 'CieteRealTarifarioLineasSeeder',
                'table' => 'tarifario_lineas',
                'primary_key' => 'id_tarifario_linea',
                'rows' => $this->rows('tarifario_lineas', 'id_tarifario_linea', fn ($query) => $query->whereIn('id_tarifario_linea', $ids['tariff_line_ids'])),
            ],
            [
                'class' => 'CieteRealContratoSociedadSeeder',
                'table' => 'contrato_empresas_facturadoras',
                'primary_key' => 'id',
                'rows' => $this->rows('contrato_empresas_facturadoras', 'id', fn ($query) => $query->whereIn('id', $ids['contract_company_ids'])),
            ],
            [
                'class' => 'CieteRealEstacionesSeeder',
                'table' => 'estaciones_servicio',
                'primary_key' => 'id_estacion_servicio',
                'rows' => $this->rows('estaciones_servicio', 'id_estacion_servicio', fn ($query) => $query->whereIn('id_estacion_servicio', $ids['station_ids'])),
            ],
            [
                'class' => 'CieteRealTrabajosSeeder',
                'table' => 'trabajos',
                'primary_key' => 'id_trabajo',
                'rows' => $this->rows('trabajos', 'id_trabajo', fn ($query) => $query->whereIn('id_trabajo', $ids['work_ids'])),
            ],
            [
                'class' => 'CieteRealPedidosSeeder',
                'table' => 'pedidos',
                'primary_key' => 'id_pedido',
                'rows' => $this->rows('pedidos', 'id_pedido', fn ($query) => $query->whereIn('id_pedido', $ids['order_ids'])),
            ],
            [
                'class' => 'CieteRealPedidoItemsSeeder',
                'table' => 'pedido_items',
                'primary_key' => 'id_pedido_item',
                'rows' => $this->rows('pedido_items', 'id_pedido_item', fn ($query) => $query->whereIn('id_pedido_item', $ids['order_item_ids'])),
            ],
            [
                'class' => 'CieteRealFacturasSeeder',
                'table' => 'facturas',
                'primary_key' => 'id_factura',
                'rows' => $this->rows('facturas', 'id_factura', fn ($query) => $query->whereIn('id_factura', $ids['invoice_ids'])),
            ],
            [
                'class' => 'CieteRealFacturaItemsSeeder',
                'table' => 'factura_items',
                'primary_key' => 'id_factura_item',
                'rows' => $this->rows('factura_items', 'id_factura_item', fn ($query) => $query->whereIn('id_factura_item', $ids['invoice_item_ids'])),
            ],
            [
                'class' => 'CieteRealUsuariosSeeder',
                'table' => 'usuarios',
                'primary_key' => 'id_usuario',
                'rows' => [],
                'note' => 'No se exportan usuarios reales desde CIETE. Se mantienen los usuarios demo/roles del seeding público.',
            ],
        ];

        return $datasets;
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function realEntityIds(?string $only = null): array
    {
        $contextCodes = match (mb_strtoupper((string) $only, 'UTF-8')) {
            'MOEVE' => ['MOEVE'],
            'REPSOL' => ['REPSOL'],
            default => ['MOEVE', 'REPSOL'],
        };

        $contextIds = DB::table('contextos_cliente')
            ->whereIn('codigo', array_merge($contextCodes, ['OTROS']))
            ->orderBy('id_contexto')
            ->pluck('id_contexto')
            ->map(fn ($value) => (int) $value)
            ->all();

        $stationIds = DB::table('estaciones_servicio')
            ->whereIn('id_contexto', $contextIds)
            ->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
            ->pluck('id_estacion_servicio')
            ->map(fn ($value) => (int) $value)
            ->all();

        $workIds = DB::table('trabajos')
            ->whereIn('id_contexto', $contextIds)
            ->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
            ->pluck('id_trabajo')
            ->map(fn ($value) => (int) $value)
            ->all();

        $orderIds = DB::table('pedidos')
            ->whereIn('id_contexto', $contextIds)
            ->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
            ->pluck('id_pedido')
            ->map(fn ($value) => (int) $value)
            ->all();

        $orderItemIds = DB::table('pedido_items')
            ->whereIn('id_pedido', $orderIds)
            ->pluck('id_pedido_item')
            ->map(fn ($value) => (int) $value)
            ->all();

        $invoiceIds = DB::table('facturas')
            ->whereIn('id_contexto', $contextIds)
            ->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
            ->pluck('id_factura')
            ->map(fn ($value) => (int) $value)
            ->all();

        $invoiceItemIds = DB::table('factura_items')
            ->whereIn('id_factura', $invoiceIds)
            ->pluck('id_factura_item')
            ->map(fn ($value) => (int) $value)
            ->all();

        $contractIds = DB::table('contratos')
            ->whereIn('id_contexto', $contextIds)
            ->where(function ($query) use ($workIds, $invoiceIds): void {
                $query->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
                    ->orWhereIn('id_contrato', DB::table('trabajos')->whereIn('id_trabajo', $workIds)->select('id_contrato'))
                    ->orWhereIn('id_contrato', DB::table('facturas')->whereIn('id_factura', $invoiceIds)->select('id_contrato'));
            })
            ->pluck('id_contrato')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $tariffIds = DB::table('tarifarios')
            ->whereIn('id_contexto', $contextIds)
            ->where(function ($query) use ($workIds, $orderIds): void {
                $query->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
                    ->orWhereIn('id_tarifario', DB::table('trabajos')->whereIn('id_trabajo', $workIds)->select('id_tarifario'))
                    ->orWhereIn('id_tarifario', DB::table('pedidos')->whereIn('id_pedido', $orderIds)->select('id_tarifario'));
            })
            ->pluck('id_tarifario')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $tariffLineIds = DB::table('tarifario_lineas')
            ->whereIn('id_contexto', $contextIds)
            ->where(function ($query) use ($orderItemIds): void {
                $query->where('descripcion', 'like', CieteExcelImportService::MARKER . '%')
                    ->orWhereIn('id_tarifario_linea', DB::table('pedido_items')->whereIn('id_pedido_item', $orderItemIds)->select('id_tarifario_linea'));
            })
            ->pluck('id_tarifario_linea')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $contractCompanyIds = DB::table('contrato_empresas_facturadoras')
            ->whereIn('id_contexto', $contextIds)
            ->where(function ($query) use ($contractIds): void {
                $query->where('observaciones', 'like', CieteExcelImportService::MARKER . '%')
                    ->orWhereIn('id_contrato', $contractIds);
            })
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $clientCompanyIds = DB::table('empresas')
            ->whereIn('id_contexto', $contextIds)
            ->where('tipo_empresa', 'cliente')
            ->whereIn('id_empresa', DB::table('trabajos')->whereIn('id_trabajo', $workIds)->select('id_empresa_cliente'))
            ->pluck('id_empresa')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $billingCompanyIds = DB::table('facturas')
            ->whereIn('id_factura', $invoiceIds)
            ->whereNotNull('id_empresa_facturadora')
            ->pluck('id_empresa_facturadora')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $documentTypeIds = DB::table('trabajos')
            ->whereIn('id_trabajo', $workIds)
            ->whereNotNull('id_tipo_documento')
            ->pluck('id_tipo_documento')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $workTypeIds = DB::table('trabajos')
            ->whereIn('id_trabajo', $workIds)
            ->whereNotNull('id_tipo_trabajo')
            ->pluck('id_tipo_trabajo')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $unitIds = DB::table('tarifario_lineas')
            ->whereIn('id_tarifario_linea', $tariffLineIds)
            ->whereNotNull('id_unidad')
            ->pluck('id_unidad')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        return [
            'context_ids' => $contextIds,
            'client_company_ids' => $clientCompanyIds,
            'billing_company_ids' => $billingCompanyIds,
            'unit_ids' => $unitIds,
            'document_type_ids' => $documentTypeIds,
            'work_type_ids' => $workTypeIds,
            'contract_ids' => $contractIds,
            'tariff_ids' => $tariffIds,
            'tariff_line_ids' => $tariffLineIds,
            'contract_company_ids' => $contractCompanyIds,
            'station_ids' => $stationIds,
            'work_ids' => $workIds,
            'order_ids' => $orderIds,
            'order_item_ids' => $orderItemIds,
            'invoice_ids' => $invoiceIds,
            'invoice_item_ids' => $invoiceItemIds,
        ];
    }

    /**
     * @param callable(\Illuminate\Database\Query\Builder): void $scope
     * @return array<int, array<string, mixed>>
     */
    private function rows(string $table, string $primaryKey, callable $scope): array
    {
        $query = DB::table($table);
        $scope($query);

        return $query
            ->orderBy($primaryKey)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * @param callable(\Illuminate\Database\Query\Builder): void $scope
     * @return array{table: string, primary_key: string, rows: array<int, array<string, mixed>>}
     */
    private function set(string $table, string $primaryKey, callable $scope): array
    {
        return [
            'table' => $table,
            'primary_key' => $primaryKey,
            'rows' => $this->rows($table, $primaryKey, $scope),
        ];
    }

    /**
     * @param array<int, array{table: string, primary_key: string, rows: array<int, array<string, mixed>>}> $sets
     */
    private function renderSeeder(string $class, array $sets, ?string $note = null): string
    {
        $renderedSets = [];

        foreach ($sets as $set) {
            $rows = $set['rows'];
            $primaryKey = $set['primary_key'];
            $updateColumns = [];

            if ($rows !== []) {
                $updateColumns = array_values(array_filter(
                    array_keys($rows[0]),
                    static fn (string $column): bool => $column !== $primaryKey
                ));
            }

            $renderedSets[] = [
                'table' => $set['table'],
                'primary_key' => $primaryKey,
                'update_columns' => $updateColumns,
                'rows' => $rows,
            ];
        }

        $setsExport = var_export($renderedSets, true);
        $noteComment = $note !== null ? "        // {$note}\n" : '';

        return <<<PHP
<?php

declare(strict_types=1);

namespace Database\\Seeders\\Private;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class {$class} extends Seeder
{
    public function run(): void
    {
{$noteComment}        \$datasets = {$setsExport};

        foreach (\$datasets as \$dataset) {
            \$rows = \$dataset['rows'];

            if (\$rows === []) {
                continue;
            }

            foreach (array_chunk(\$rows, 500) as \$chunk) {
                DB::table(\$dataset['table'])->upsert(
                    \$chunk,
                    [\$dataset['primary_key']],
                    \$dataset['update_columns']
                );
            }
        }
    }
}
PHP;
    }

    /**
     * @param array<int, string> $classes
     */
    private function renderMasterSeeder(array $classes): string
    {
        $classList = implode(",\n            ", array_map(static fn (string $class): string => "{$class}::class", $classes));

        return <<<PHP
<?php

declare(strict_types=1);

namespace Database\\Seeders\\Private;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class CieteRealDataSeeder extends Seeder
{
    public function run(): void
    {
        \$this->purgeOperationalDemoData();

        \$this->call([
            {$classList},
        ]);
    }

    private function purgeOperationalDemoData(): void
    {
        \$markers = ['[p1-12-excel-real]%', '[demo-ciete-excel]%', '[demo-operativa]%', '[demo-volumen]%'];

        \$workIds = DB::table('trabajos')
            ->where(function (\$query) use (\$markers): void {
                foreach (\$markers as \$marker) {
                    \$query->orWhere('observaciones', 'like', \$marker);
                }
            })
            ->pluck('id_trabajo');

        \$pedidoIds = DB::table('pedidos')
            ->whereIn('id_trabajo', \$workIds)
            ->orWhere(function (\$query) use (\$markers): void {
                foreach (\$markers as \$marker) {
                    \$query->orWhere('observaciones', 'like', \$marker);
                }
            })
            ->pluck('id_pedido');

        \$pedidoItemIds = DB::table('pedido_items')->whereIn('id_pedido', \$pedidoIds)->pluck('id_pedido_item');

        \$facturaIds = DB::table('facturas')
            ->whereIn('id_trabajo', \$workIds)
            ->orWhere(function (\$query) use (\$markers): void {
                foreach (\$markers as \$marker) {
                    \$query->orWhere('observaciones', 'like', \$marker);
                }
            })
            ->pluck('id_factura');

        DB::table('factura_items')
            ->whereIn('id_factura', \$facturaIds)
            ->orWhereIn('id_pedido_item', \$pedidoItemIds)
            ->delete();

        DB::table('facturas')->whereIn('id_factura', \$facturaIds)->delete();
        DB::table('pedido_items')->whereIn('id_pedido_item', \$pedidoItemIds)->delete();
        DB::table('pedidos')->whereIn('id_pedido', \$pedidoIds)->delete();
        DB::table('trabajos')->whereIn('id_trabajo', \$workIds)->delete();

        DB::table('estaciones_servicio')
            ->where(function (\$query) use (\$markers): void {
                foreach (\$markers as \$marker) {
                    \$query->orWhere('observaciones', 'like', \$marker);
                }
            })
            ->delete();
    }
}
PHP;
    }
}
