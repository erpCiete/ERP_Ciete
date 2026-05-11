<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Importacion\CieteExcelImportService;
use App\Services\Importacion\PrivateSeederGeneratorService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ciete:import-excels-actualizados
    {--path=excelsactualizados : Carpeta o archivo Excel a revisar/importar}
    {--context= : Filtra por MOEVE o REPSOL}
    {--file= : Filtra por parte del nombre de archivo}
    {--limit= : Limita el numero de archivos procesados}
    {--dry-run : Ejecuta lectura y mapeo sin persistir datos}
    {--commit : Persiste la importacion en la base local/demo}', function () {
        /** @var CieteExcelImportService $service */
        $service = app(CieteExcelImportService::class);
        $path = (string) ($this->option('path') ?: 'excelsactualizados');
        $context = $this->option('context') ? mb_strtoupper((string) $this->option('context'), 'UTF-8') : null;
        $file = $this->option('file') ? (string) $this->option('file') : null;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $commit = (bool) $this->option('commit');

        if ($commit && app()->environment('production')) {
            $this->error('P1-12 no permite importar Excel reales en production.');

            return 1;
        }

        $inventory = $service->inventory($path, $context, $file);
        $this->info('Inventario Excel actualizado');
        $this->table(
            ['Archivo', 'Contexto', 'Hojas', 'Filas detectadas'],
            array_map(static function (array $fileInfo): array {
                return [
                    $fileInfo['file'],
                    $fileInfo['context'],
                    count($fileInfo['sheets']),
                    array_sum(array_map(static fn (array $sheet): int => (int) $sheet['rows'], $fileInfo['sheets'])),
                ];
            }, $inventory)
        );

        $result = $service->import($path, $commit, $context, $file, $limit);
        $summary = $result->toArray();

        $this->info($commit ? 'Importacion P1-12 persistida.' : 'Dry-run P1-12 completado sin persistir datos.');
        $this->table(
            ['Entidad', 'Importadas/creadas'],
            array_map(
                static fn (string $entity, int $count): array => [$entity, $count],
                array_keys($summary['imported']),
                $summary['imported']
            )
        );

        $this->table(
            ['Metrica', 'Valor'],
            [
                ['filas_leidas', $summary['rows_seen']],
                ['filas_importadas', $summary['rows_imported']],
                ['filas_ignoradas', $summary['rows_ignored']],
                ['filas_con_aviso', $summary['rows_with_warning']],
                ['filas_con_error', $summary['rows_with_error']],
                ['archivos', count($summary['files'])],
                ['columnas_desconocidas_en_hojas', count($summary['unknown_columns'])],
            ]
        );

        $this->info('Integridad post-importacion');
        $this->table(
            ['Comprobacion', 'Valor'],
            array_map(
                static fn (string $key, mixed $value): array => [$key, is_bool($value) ? ($value ? 'si' : 'no') : $value],
                array_keys($service->integritySummary()),
                $service->integritySummary()
            )
        );

        foreach (array_slice($summary['warnings'], 0, 10) as $warning) {
            $this->warn("Aviso {$warning['file']} :: {$warning['sheet']} fila {$warning['row']}: {$warning['message']}");
        }

        foreach (array_slice($summary['errors'], 0, 10) as $error) {
            $this->error("Error {$error['file']} :: {$error['sheet']} fila {$error['row']}: {$error['message']}");
        }

        return 0;
    })->purpose('Inventaria e importa en local/demo los Excel actualizados MOEVE/REPSOL');

Artisan::command('ciete:generate-private-seeders-from-excel
    {--path=docs/Abaco/excelsactualizados : Carpeta base de Excel actualizados}
    {--dry-run : Solo inventaria y resume datasets disponibles}
    {--write-seeders : Genera seeders privados locales}
    {--commit : Reimporta desde Excel antes de generar seeders}
    {--limit= : Limita archivos al reimportar con --commit}
    {--only= : Filtra por MOEVE o REPSOL}', function () {
        /** @var PrivateSeederGeneratorService $generator */
        $generator = app(PrivateSeederGeneratorService::class);
        /** @var CieteExcelImportService $importer */
        $importer = app(CieteExcelImportService::class);

        $path = (string) ($this->option('path') ?: 'docs/Abaco/excelsactualizados');
        $only = $this->option('only') ? mb_strtoupper((string) $this->option('only'), 'UTF-8') : null;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $doCommit = (bool) $this->option('commit');
        $writeSeeders = (bool) $this->option('write-seeders');
        $dryRun = (bool) $this->option('dry-run') || (! $doCommit && ! $writeSeeders);

        if (! app()->environment('local')) {
            $this->error('P1-13 solo permite generar seeders privados en entorno local.');
            return 1;
        }

        if ($doCommit) {
            $this->warn('Se reutiliza el importador actual para refrescar la base local antes de exportar seeders privados.');
            $importer->import($path, commit: true, context: $only, file: null, limit: $limit);
        }

        $inspection = $generator->inspect($path, $only);
        $this->info('Inventario Excel para seeders privados');
        $this->table(
            ['Archivo', 'Contexto', 'Hojas'],
            array_map(static fn (array $file): array => [
                $file['file'],
                $file['context'],
                count($file['sheets']),
            ], $inspection['inventory'])
        );

        $this->info('Datasets disponibles en la base actual');
        $this->table(
            ['Seeder', 'Filas'],
            array_map(
                static fn (string $class, int $count): array => [$class, $count],
                array_keys($inspection['datasets']),
                $inspection['datasets']
            )
        );

        if ($dryRun && ! $writeSeeders) {
            $this->info('Dry-run completado. No se escribieron seeders privados.');
            return 0;
        }

        $written = $generator->writeSeeders($only);
        $this->info('Seeders privados generados en local');
        $this->table(
            ['Clase', 'Tabla', 'Filas'],
            array_map(static fn (array $dataset): array => [
                $dataset['class'],
                $dataset['table'],
                $dataset['rows'],
            ], $written['datasets'])
        );
        $this->line('Directorio: ' . $written['directory']);
        $this->line('Recuerda ejecutar composer dump-autoload antes de php artisan db:seed --class="Database\\Seeders\\Private\\CieteRealDataSeeder".');

        return 0;
    })->purpose('Genera seeders privados locales desde la carga real de Excel MOEVE/REPSOL');
