<?php

namespace Tests\Feature;

use App\Models\ContextoCliente;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Importacion;
use App\Models\ImportacionFila;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\Importacion\CieteExcelImportService;
use App\Services\Importacion\ExcelHeaderNormalizer;
use App\Services\Importacion\PrivateSeederGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_normaliza_cabeceras_reales_con_acentos_y_caracteres_raros(): void
    {
        $normalizer = new ExcelHeaderNormalizer();

        $this->assertSame('notice_number', $normalizer->canonical('Nº AVISO / ORDEN MANTEN.'));
        $this->assertSame('requested_amount', $normalizer->canonical('IMPORTE SOLICTADO'));
        $this->assertSame('order_request_date', $normalizer->canonical('Fecha (reclamo APP) Solicitud Pedido'));
        $this->assertSame('station_code', $normalizer->canonical('CÓDIGO ESTACIÓN'));
        $this->assertSame('station_code', $normalizer->canonical('CONCESIÓN'));
        $this->assertSame('station_code', $normalizer->canonical('Nº ES'));
    }

    public function test_inventario_detecta_excel_moeve_y_repsol(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);

        $inventory = app(CieteExcelImportService::class)->inventory($root);

        $this->assertCount(3, $inventory);
        $this->assertEqualsCanonicalizing(['MOEVE', 'MOEVE', 'REPSOL'], array_column($inventory, 'context'));
        $this->assertSame('Trabajos', $inventory[0]['sheets'][0]['name']);
    }

    public function test_dry_run_no_persiste_datos(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);

        $result = app(CieteExcelImportService::class)->import($root, commit: false);

        $this->assertTrue($result->dryRun);
        $this->assertGreaterThan(0, $result->rowsImported);
        $this->assertSame(0, ContextoCliente::count());
        $this->assertSame(0, Trabajo::count());
        $this->assertSame(0, FacturaItem::count());
    }

    public function test_commit_importa_modelo_vivo_sin_legacy(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);

        $result = app(CieteExcelImportService::class)->import($root, commit: true);

        $this->assertFalse($result->dryRun);
        $this->assertGreaterThanOrEqual(2, Trabajo::count());
        $this->assertGreaterThanOrEqual(2, Pedido::count());
        $this->assertGreaterThanOrEqual(2, PedidoItem::count());
        $this->assertGreaterThanOrEqual(2, Factura::count());
        $this->assertGreaterThanOrEqual(2, FacturaItem::count());

        $this->assertFalse(Schema::hasTable('factura_pedidos'));
        $this->assertSame(0, Trabajo::query()->whereIn('estado', ['borrador', 'cerrado'])->count());
        $this->assertSame(0, Pedido::query()->whereIn('estado', ['borrador', 'cerrado'])->count());
        $this->assertSame(0, DB::table('factura_items')
            ->leftJoin('pedido_items', 'pedido_items.id_pedido_item', '=', 'factura_items.id_pedido_item')
            ->whereNull('pedido_items.id_pedido_item')
            ->count());
        $this->assertSame(0, DB::table('facturas')
            ->whereRaw('not exists (select 1 from factura_items where factura_items.id_factura = facturas.id_factura)')
            ->count());
    }

    public function test_commit_guarda_detalle_de_avisos_y_clasificacion(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);
        User::factory()->create(['email' => 'admin@ciete.es']);

        app(CieteExcelImportService::class)->import($root, commit: true);

        $this->assertGreaterThanOrEqual(2, Importacion::count());
        $this->assertGreaterThan(0, ImportacionFila::query()->where('codigo', 'unknown_columns')->count());
        $this->assertGreaterThan(0, ImportacionFila::query()->where('clasificacion', 'functional_decision')->count());
        $this->assertSame(0, ImportacionFila::query()->where('clasificacion', 'do_not_invent')->count());
    }

    public function test_no_crea_pedido_item_sin_pedido_y_registra_aviso_funcional(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root, includeMoeveWithoutOrder: true);
        User::factory()->create(['email' => 'admin@ciete.es']);

        app(CieteExcelImportService::class)->import($root, commit: true);

        $this->assertSame(0, PedidoItem::query()->where('descripcion_servicio', 'Servicio sin pedido')->count());
        $this->assertSame(1, ImportacionFila::query()
            ->where('codigo', 'work_amount_without_order')
            ->where('clasificacion', 'functional_decision')
            ->count());
    }

    public function test_no_duplica_estacion_en_mismo_contexto_y_permite_mismo_codigo_en_contextos_distintos(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);

        app(CieteExcelImportService::class)->import($root, commit: true);

        $this->assertSame(1, EstacionServicio::query()
            ->whereHas('contexto', fn($query) => $query->where('codigo', 'MOEVE'))
            ->where('codigo_estacion', '1001')
            ->count());
        $this->assertSame(1, EstacionServicio::query()
            ->whereHas('contexto', fn($query) => $query->where('codigo', 'REPSOL'))
            ->where('codigo_estacion', '1001')
            ->count());
        $this->assertSame(2, EstacionServicio::query()->where('codigo_estacion', '1001')->count());
    }

    public function test_private_seeder_generator_crea_estructura_local_sin_legacy_critico(): void
    {
        $root = $this->fixtureRoot();
        $this->createExcelFixtures($root);

        app(CieteExcelImportService::class)->import($root, commit: true);

        $written = app(PrivateSeederGeneratorService::class)->writeSeeders();

        $masterPath = base_path('database/seeders/private/CieteRealDataSeeder.php');
        $catalogosPath = base_path('database/seeders/private/CieteRealCatalogosSeeder.php');
        $facturasPath = base_path('database/seeders/private/CieteRealFacturasSeeder.php');

        $this->assertFileExists($masterPath);
        $this->assertFileExists($catalogosPath);
        $this->assertFileExists($facturasPath);
        $this->assertSame(base_path('database/seeders/private'), $written['directory']);
        $this->assertStringContainsString('CieteRealFacturasSeeder::class', (string) file_get_contents($masterPath));
        $masterContents = (string) file_get_contents($masterPath);
        $this->assertTrue(
            strpos($masterContents, 'CieteRealCatalogosSeeder::class') < strpos($masterContents, 'CieteRealTrabajosSeeder::class')
        );
        $this->assertStringNotContainsString('factura_pedidos', (string) file_get_contents($facturasPath));
        $this->assertStringNotContainsString("'borrador'", (string) file_get_contents($masterPath));
        $this->assertStringNotContainsString("'cerrado'", (string) file_get_contents($masterPath));
    }

    private function fixtureRoot(): string
    {
        $root = storage_path('framework/testing/excels-p1-12');
        File::deleteDirectory($root);
        File::makeDirectory($root . '/Moeve/Moeve', 0755, true);
        File::makeDirectory($root . '/Repsol/Repsol', 0755, true);

        return $root;
    }

    private function createExcelFixtures(string $root, bool $includeMoeveWithoutOrder = false): void
    {
        $moeveRows = [
            ['Nº', 'ES', 'Nombre', 'LOCALIDAD', 'Provincia', 'Nº PEDIDO', 'IMPORTE PEDIDO', 'Facturación Solicitada', 'FACTURADO Solo Esther', 'Fecha Encargo', 'Fecha T', 'DESCRIPCION DEL SERVICIO', 'RESPONSABLE CIETE', 'RESPONSABLE MOEVE', 'OBSERVACIONES', 'Nº Factura', 'Fecha factura', 'Contrato', 'Status', 'Categoría'],
            [1, '1001', 'MOEVE Uno', 'Madrid', 'Madrid', 'PED-M-1', 100, 100, 100, '01/01/2026', '02/01/2026', 'Servicio MOEVE', 'Pablo', 'Gestor MOEVE', '', 'M26001', '03/01/2026', '772', 'Facturado', 'Categoria A'],
        ];

        if ($includeMoeveWithoutOrder) {
            $moeveRows[] = [2, '1002', 'MOEVE Dos', 'Sevilla', 'Sevilla', '', 250, 250, 0, '05/01/2026', '', 'Servicio sin pedido', 'Pablo', 'Gestor MOEVE', '', '', '', '772', 'Pendiente', 'Categoria B'];
        }

        $this->writeWorkbook($root . '/Moeve/Moeve/01 Control de Trabajos Moeve.xlsx', [
            'Trabajos' => $moeveRows,
            'FACTURAS EMITIDAS' => [
                ['SOCIEDAD', 'Nº FACTURA CCP', 'Nº FACTURA CIETE', 'FECHA FACTURA', 'IMPORTE FACTURADO'],
                ['MOEVE', 'CCP-M-1', 'M26001', '03/01/2026', 100],
            ],
        ]);

        $this->writeWorkbook($root . '/Moeve/Moeve/02 Listado EESS España y Portugal 16-03-26.xlsx', [
            'España 16-03-26' => [
                ['CONCESIÓN', 'NOMBRE', 'direccion', 'COD. POSTAL', 'POBLACION', 'Provincia', 'ESTADO', 'COD RETAILGAS', 'COD. SOCIEDAD', 'SOCIEDAD', 'TÉCNICO GESTIÓN'],
                ['1001', 'MOEVE Uno', 'Calle Uno', '28001', 'Madrid', 'Madrid', 'Activa', 'RG-1', 'MOEVE', 'MOEVE', 'Responsable local'],
            ],
        ]);

        $this->writeWorkbook($root . '/Repsol/Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx', [
            'AUTOFACTURACION' => [
                ['Nº', 'C.EMP', 'NOMBRE', 'LOCALIDAD', 'PROVINCIA', 'Nº AVISO / ORDEN MANTEN.', 'ORDEN MANTEN.', 'Nº PEDIDO', 'FECHA ENCARGO', 'TIPO DE TRABAJO', 'DESCRIPCION DEL TRABAJO', 'FECHA TERMINACIÓN TRABAJO', 'CÓDIGO SERVICIO', 'Número Tarifa (con punto)', 'DESCRIPCION DEL SERVICIO', 'IMPORTE UNITARIO', 'UDs DEL PEDIDO', 'IMPORTE PEDIDO', 'UDs SOLICITADAS', 'IMPORTE SOLICTADO', 'IMPORTE FACTURADO', 'FACTURA', 'FECHA SOLICITUD FACTURA', 'STATUS', 'Nº ES'],
                [1, '1001', 'REPSOL Uno', 'Madrid', 'Madrid', 'AV-1', 'OM-1', 'PED-R-1', '01/02/2026', 'MTO', 'Trabajo REPSOL', '03/02/2026', 'SRV-R', '1.1', 'Servicio REPSOL', 120, 1, 120, 1, 120, 120, 'R26001', '04/02/2026', 'Facturado', 'ES-1001'],
            ],
            'LISTADO EESS' => [
                ['C.EMP', 'NOMBRE COMERCIAL', 'LOCALIDAD', 'PROVINCIA', 'CODIGO SOLRED', 'Ltrs 21', 'CLIENTE'],
                ['1001', 'REPSOL Uno', 'Madrid', 'Madrid', 'SOL-1', 1000, 'REPSOL'],
            ],
            'TARIFA 23-27' => [
                ['Grupo', 'Codigo', 'Descripcion', 'Importe'],
                ['1', '1.1', 'Servicio REPSOL', 120],
            ],
        ]);
    }

    /**
     * @param array<string, array<int, array<int, mixed>>> $sheets
     */
    private function writeWorkbook(string $path, array $sheets): void
    {
        $spreadsheet = new Spreadsheet();
        $first = true;

        foreach ($sheets as $title => $rows) {
            $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;
            $sheet->setTitle($title);
            $sheet->fromArray($rows, null, 'A1');
        }

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }
}
