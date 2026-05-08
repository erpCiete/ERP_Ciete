<?php

declare(strict_types=1);

namespace App\Services\Importacion;

final class ImportValidationResult
{
    private const MAX_SUMMARY_ISSUES = 250;

    /**
     * @var array<string, int>
     */
    public array $imported = [
        'contextos' => 0,
        'empresas' => 0,
        'estaciones' => 0,
        'contratos' => 0,
        'tarifarios' => 0,
        'tarifario_lineas' => 0,
        'trabajos' => 0,
        'pedidos' => 0,
        'pedido_items' => 0,
        'facturas' => 0,
        'factura_items' => 0,
        'contrato_empresas_facturadoras' => 0,
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $files = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $errors = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $warnings = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $issues = [];

    /**
     * @var array<string, array<int, string>>
     */
    public array $unknownColumns = [];

    public int $rowsSeen = 0;

    public int $rowsImported = 0;

    public int $rowsIgnored = 0;

    public int $rowsWithWarning = 0;

    public int $rowsWithError = 0;

    public bool $dryRun = true;

    public function __construct(bool $dryRun = true)
    {
        $this->dryRun = $dryRun;
    }

    public function increment(string $entity, int $amount = 1): void
    {
        $this->imported[$entity] = ($this->imported[$entity] ?? 0) + $amount;
    }

    public function rowImported(?string $file = null, ?string $sheet = null): void
    {
        $this->rowsImported++;
        $this->bumpFileMetric($file, $sheet, 'rows_imported');
    }

    public function rowIgnored(?string $file = null, ?string $sheet = null): void
    {
        $this->rowsIgnored++;
        $this->bumpFileMetric($file, $sheet, 'rows_ignored');
    }

    public function rowSeen(?string $file = null, ?string $sheet = null): void
    {
        $this->rowsSeen++;
        $this->bumpFileMetric($file, $sheet, 'rows_seen');
    }

    public function addFile(string $path, array $summary): void
    {
        $existingCounters = $this->files[$path]['counters'] ?? $this->emptyCounters();

        $this->files[$path] = [
            ...$summary,
            'counters' => $existingCounters,
        ];
    }

    public function addUnknownColumns(
        string $file,
        string $sheet,
        array $columns,
        string $classification = 'functional_decision',
        ?string $decision = null,
        string $code = 'unknown_columns'
    ): void
    {
        $columns = array_values(array_filter(array_unique($columns)));

        if ($columns === []) {
            return;
        }

        $key = $file . ' :: ' . $sheet;
        $this->unknownColumns[$key] = array_values(array_unique([
            ...($this->unknownColumns[$key] ?? []),
            ...$columns,
        ]));

        $this->issue(
            severity: 'warning',
            file: $file,
            sheet: $sheet,
            row: 0,
            message: 'Columnas sin destino claro detectadas en la hoja.',
            code: $code,
            classification: $classification,
            decision: $decision ?? 'Revisar si alguna columna merece destino de datos maestro o puede seguir fuera del flujo operativo.',
            result: 'warning',
            payload: ['columns' => $columns]
        );
    }

    public function warn(
        string $file,
        string $sheet,
        int $row,
        string $message,
        string $code = 'warning',
        string $classification = 'acceptable',
        ?string $decision = null,
        array $payload = []
    ): void
    {
        $this->rowsWithWarning++;
        $this->bumpFileMetric($file, $sheet, 'rows_with_warning');

        $issue = $this->issue(
            severity: 'warning',
            file: $file,
            sheet: $sheet,
            row: $row,
            message: $message,
            code: $code,
            classification: $classification,
            decision: $decision,
            result: 'warning',
            payload: $payload
        );

        if (count($this->warnings) < self::MAX_SUMMARY_ISSUES) {
            $this->warnings[] = $issue;
        }
    }

    public function ignore(
        string $file,
        string $sheet,
        int $row,
        string $message,
        string $code,
        string $classification,
        ?string $decision = null,
        array $payload = [],
        string $severity = 'warning'
    ): void
    {
        $this->rowIgnored($file, $sheet);

        if ($severity === 'error') {
            $this->error($file, $sheet, $row, $message, $code, $classification, $decision, $payload, 'ignored');
            return;
        }

        $this->warn($file, $sheet, $row, $message, $code, $classification, $decision, [
            ...$payload,
            'row_result' => 'ignored',
        ]);
        $this->markLastIssueResult('ignored');
    }

    public function error(
        string $file,
        string $sheet,
        int $row,
        string $message,
        string $code = 'error',
        string $classification = 'technical_improvement',
        ?string $decision = null,
        array $payload = [],
        string $result = 'error'
    ): void
    {
        $this->rowsWithError++;
        $this->bumpFileMetric($file, $sheet, 'rows_with_error');

        $issue = $this->issue(
            severity: 'error',
            file: $file,
            sheet: $sheet,
            row: $row,
            message: $message,
            code: $code,
            classification: $classification,
            decision: $decision,
            result: $result,
            payload: $payload
        );

        if (count($this->errors) < self::MAX_SUMMARY_ISSUES) {
            $this->errors[] = $issue;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dry_run' => $this->dryRun,
            'rows_seen' => $this->rowsSeen,
            'rows_imported' => $this->rowsImported,
            'rows_ignored' => $this->rowsIgnored,
            'rows_with_warning' => $this->rowsWithWarning,
            'rows_with_error' => $this->rowsWithError,
            'imported' => $this->imported,
            'files' => $this->files,
            'unknown_columns' => $this->unknownColumns,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'issues' => $this->issues,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyCounters(): array
    {
        return [
            'rows_seen' => 0,
            'rows_imported' => 0,
            'rows_ignored' => 0,
            'rows_with_warning' => 0,
            'rows_with_error' => 0,
        ];
    }

    private function bumpFileMetric(?string $file, ?string $sheet, string $metric): void
    {
        if ($file === null || $file === '') {
            return;
        }

        if (! isset($this->files[$file])) {
            $this->files[$file] = ['counters' => $this->emptyCounters()];
        }

        $this->files[$file]['counters'] ??= $this->emptyCounters();
        $this->files[$file]['counters'][$metric] = ($this->files[$file]['counters'][$metric] ?? 0) + 1;

        if ($sheet === null || $sheet === '') {
            return;
        }

        $this->files[$file]['sheet_counters'] ??= [];
        $this->files[$file]['sheet_counters'][$sheet] ??= $this->emptyCounters();
        $this->files[$file]['sheet_counters'][$sheet][$metric] = ($this->files[$file]['sheet_counters'][$sheet][$metric] ?? 0) + 1;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function issue(
        string $severity,
        string $file,
        string $sheet,
        int $row,
        string $message,
        string $code,
        string $classification,
        ?string $decision,
        string $result,
        array $payload = []
    ): array
    {
        $issue = [
            'severity' => $severity,
            'file' => $file,
            'sheet' => $sheet,
            'row' => $row,
            'message' => $message,
            'code' => $code,
            'classification' => $classification,
            'decision' => $decision,
            'result' => $result,
            'payload' => $payload,
        ];

        $this->issues[] = $issue;

        return $issue;
    }

    private function markLastIssueResult(string $result): void
    {
        $lastKey = array_key_last($this->issues);
        if ($lastKey !== null) {
            $this->issues[$lastKey]['result'] = $result;
        }

        $lastWarningKey = array_key_last($this->warnings);
        if ($lastWarningKey !== null) {
            $this->warnings[$lastWarningKey]['result'] = $result;
        }
    }
}
