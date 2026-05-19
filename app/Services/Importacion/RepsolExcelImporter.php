<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use App\Models\PedidoItem;
use App\Models\Trabajo;
use SplFileInfo;

final class RepsolExcelImporter
{
    private const CONTEXT = 'REPSOL';

    public function __construct(
        private readonly CieteExcelImportService $service,
        private readonly XlsxWorkbookReader $reader,
        private readonly ExcelHeaderNormalizer $normalizer,
    ) {}

    public function import(SplFileInfo $file, ImportValidationResult $result): void
    {
        $path = $file->getPathname();
        $relative = $this->relativePath($path);
        $inspect = $this->reader->inspect($path);
        $document = $this->documentFromFile($relative);

        $result->addFile($relative, [
            'context' => self::CONTEXT,
            'type' => 'trabajos_pedidos_facturas',
            'document' => $document['code'],
            'sheets' => array_map(function (array $sheet): array {
                $headers = $sheet['headers'] ?? [];

                return [
                    'name' => $sheet['title'],
                    'rows' => $sheet['rows'],
                    'header_row' => $sheet['header_row'],
                    'mapped_columns' => array_keys($this->normalizer->mapHeaders($headers)),
                    'unknown_columns' => $this->normalizer->unknownHeaders($headers),
                ];
            }, $inspect),
        ]);

        $documentId = $this->service->ensureTipoDocumento(
            self::CONTEXT,
            $document['code'],
            $document['name'],
            $document['double_invoice'],
            true,
            true,
            $result
        );

        foreach ($inspect as $sheet) {
            $title = (string) $sheet['title'];
            $headers = $sheet['headers'] ?? [];
            $map = $this->normalizer->mapHeaders($headers);
            $result->addUnknownColumns(
                $relative,
                $title,
                $this->normalizer->unknownHeaders($headers),
                classification: $this->unknownColumnClassification($title, $map),
                decision: $this->unknownColumnDecision($title, $map),
                code: 'unknown_columns'
            );

            if ($this->isStationSheet($title, $map)) {
                $this->importStations($path, $relative, $title, (int) $sheet['header_row'], $map, $result);
                continue;
            }

            if ($this->isTariffSheet($title)) {
                $this->importTariff($path, $relative, $title, $result);
                continue;
            }

            if ($this->isWorkSheet($title, $map)) {
                $this->importWorks($path, $relative, $title, (int) $sheet['header_row'], $map, $documentId, $result);
            }
        }
    }

    /**
     * @param array<string, int> $map
     */
    private function importStations(string $path, string $relative, string $sheet, int $headerRow, array $map, ImportValidationResult $result): void
    {
        foreach ($this->reader->rows($path, $sheet) as $row) {
            if ($row['row'] <= $headerRow || $this->blank($row['values'])) {
                continue;
            }

            $result->rowSeen($relative, $sheet);
            $stationId = $this->service->ensureStation(self::CONTEXT, [
                'code' => $this->service->value($row['values'], $map, 'station_code'),
                'name' => $this->service->value($row['values'], $map, 'station_name'),
                'city' => $this->service->value($row['values'], $map, 'city'),
                'province' => $this->service->value($row['values'], $map, 'province'),
                'solred_code' => $this->service->value($row['values'], $map, 'solred_code'),
                'liters_21' => $this->service->value($row['values'], $map, 'liters_21'),
                'client_name' => $this->service->value($row['values'], $map, 'client_name'),
                'manager_name' => $this->service->value($row['values'], $map, 'manager_name'),
                'director_name' => $this->service->value($row['values'], $map, 'director_name'),
                'phone' => $this->service->value($row['values'], $map, 'phone'),
                'margin' => $this->service->value($row['values'], $map, 'margin'),
                'regional' => $this->service->value($row['values'], $map, 'regional'),
            ], $result);

            if ($stationId === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Fila de estacion REPSOL sin C.EMP util.',
                    'station_missing_code',
                    'acceptable',
                    'Mantener fuera salvo que CIETE confirme un identificador alternativo estable.'
                );
                continue;
            }

            $result->rowImported($relative, $sheet);
        }
    }

    private function importTariff(string $path, string $relative, string $sheet, ImportValidationResult $result): void
    {
        $contractId = $this->service->ensureContract(self::CONTEXT, 'REPSOL-2023-2027', 'Tarifa Repsol 2023-2027', $result);
        $tarifarioId = $this->service->ensureTarifario(self::CONTEXT, $contractId, 'TARIFA 23-27 REPSOL', '2023-2027', $result);

        foreach ($this->reader->rows($path, $sheet) as $row) {
            if ($this->blank($row['values'])) {
                continue;
            }

            $candidate = $this->tariffCandidate($row['values']);
            if ($candidate === null) {
                continue;
            }

            $result->rowSeen($relative, $sheet);
            [$code, $index] = $candidate;
            $description = $this->descriptionAfter($row['values'], $index);
            $price = $this->lastFloat($row['values']);

            if ($description === null || $price === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Linea tarifaria REPSOL sin descripcion o importe interpretable.',
                    'tariff_line_unusable',
                    'acceptable',
                    'Mantener fuera si la fila es subtotal, cabecera interna o formato auxiliar.'
                );
                continue;
            }

            $this->service->ensureTarifarioLinea(self::CONTEXT, $tarifarioId, $code, null, $description, $price, $price, $result);
            $result->rowImported($relative, $sheet);
        }
    }

    /**
     * @param array<string, int> $map
     */
    private function importWorks(string $path, string $relative, string $sheet, int $headerRow, array $map, int $documentId, ImportValidationResult $result): void
    {
        foreach ($this->reader->rows($path, $sheet) as $row) {
            if ($row['row'] <= $headerRow || $this->blank($row['values'])) {
                continue;
            }

            $result->rowSeen($relative, $sheet);
            $data = $this->workData($row['values'], $map, $relative, $sheet, (int) $row['row']);
            $work = $this->service->createOrUpdateWork(self::CONTEXT, $documentId, $data, $result);

            if (! $work instanceof Trabajo) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Fila REPSOL sin número de trabajo usable.',
                    'work_missing_number',
                    'acceptable',
                    'Mantener fuera si es cabecera repetida, subtotal o linea auxiliar.'
                );
                continue;
            }

            $hasSplitInvoice = $this->hasSplitInvoice($data);
            if ($hasSplitInvoice) {
                $item = $this->createSplitInvoicePedidoItem($work, $data, $result);
            } else {
                $item = $this->service->createPedidoItemAndOptionalInvoice(self::CONTEXT, $work, $data, $result);
            }

            if (! $item instanceof PedidoItem && $this->service->toFloat($data['order_amount'] ?? null) !== null) {
                $result->warn(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Trabajo REPSOL con importe pero sin número de pedido; no se crea pedido_item.',
                    'work_amount_without_order',
                    'functional_decision',
                    'Confirmar con CIETE si estos trabajos deben quedar sin pedido o requieren otra fuente para completar el pedido.'
                );
            }

            $result->rowImported($relative, $sheet);
        }
    }

    /**
     * @param array<int, string|null> $values
     * @param array<string, int>      $map
     * @return array<string, mixed>
     */
    private function workData(array $values, array $map, string $relative, string $sheet, int $row): array
    {
        $amount1 = $this->service->toFloat($this->service->value($values, $map, 'invoice_amount_1')) ?? 0.0;
        $amount2 = $this->service->toFloat($this->service->value($values, $map, 'invoice_amount_2')) ?? 0.0;
        $invoiced = $this->service->toFloat($this->service->value($values, $map, 'invoiced_amount'));

        return [
            '_file' => $relative,
            '_sheet' => $sheet,
            '_row' => $row,
            'work_number' => $this->service->value($values, $map, 'work_number'),
            'station_code' => $this->service->value($values, $map, 'station_code'),
            'station_name' => $this->service->value($values, $map, 'station_name'),
            'city' => $this->service->value($values, $map, 'city'),
            'province' => $this->service->value($values, $map, 'province'),
            'notice_number' => $this->service->value($values, $map, 'notice_number'),
            'maintenance_order' => $this->service->value($values, $map, 'maintenance_order'),
            'order_number' => $this->service->value($values, $map, 'order_number'),
            'order_request_date' => $this->service->value($values, $map, 'order_request_date'),
            'assignment_date' => $this->service->value($values, $map, 'assignment_date'),
            'completion_date' => $this->service->value($values, $map, 'completion_date'),
            'category' => $this->service->value($values, $map, 'category'),
            'work_description' => $this->service->value($values, $map, 'work_description'),
            'service_code' => $this->service->value($values, $map, 'service_code'),
            'tariff_number' => $this->service->value($values, $map, 'tariff_number'),
            'service_description' => $this->service->value($values, $map, 'service_description'),
            'unit_price' => $this->service->value($values, $map, 'unit_price'),
            'quantity' => $this->service->value($values, $map, 'quantity'),
            'order_amount' => $this->service->value($values, $map, 'order_amount'),
            'requested_units' => $this->service->value($values, $map, 'requested_units'),
            'requested_amount' => $this->service->value($values, $map, 'requested_amount'),
            'invoiced_amount' => ($amount1 + $amount2) > 0 ? (string) ($amount1 + $amount2) : $invoiced,
            'invoice_number' => $this->service->value($values, $map, 'invoice_number'),
            'invoice_date' => $this->service->value($values, $map, 'invoice_date'),
            'invoice_number_1' => $this->service->value($values, $map, 'invoice_number_1'),
            'invoice_number_2' => $this->service->value($values, $map, 'invoice_number_2'),
            'invoice_amount_1' => $this->service->value($values, $map, 'invoice_amount_1'),
            'invoice_amount_2' => $this->service->value($values, $map, 'invoice_amount_2'),
            'invoice_date_1' => $this->service->value($values, $map, 'invoice_date_1'),
            'invoice_date_2' => $this->service->value($values, $map, 'invoice_date_2'),
            'responsible_ciete' => $this->service->value($values, $map, 'responsible_ciete'),
            'responsible_client' => $this->service->value($values, $map, 'responsible_client'),
            'observations' => $this->service->value($values, $map, 'observations'),
            'status' => $this->service->value($values, $map, 'status'),
            'contract_code' => 'REPSOL-2023-2027',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createSplitInvoicePedidoItem(Trabajo $work, array $data, ImportValidationResult $result): ?PedidoItem
    {
        $item = $this->service->createPedidoItemAndOptionalInvoice(self::CONTEXT, $work, $data, $result, false);
        if (! $item instanceof PedidoItem) {
            return null;
        }

        $amount1 = $this->service->toFloat($data['invoice_amount_1'] ?? null) ?? 0.0;
        $amount2 = $this->service->toFloat($data['invoice_amount_2'] ?? null) ?? 0.0;
        $total = $this->service->toFloat($data['invoiced_amount'] ?? null) ?? 0.0;

        if ($amount1 <= 0.0 && $amount2 <= 0.0 && $total > 0.0) {
            $amount1 = $total;
        }

        if ($amount1 > 0.0) {
            $this->service->createFacturaItem(self::CONTEXT, $work, $item, [
                ...$data,
                'invoice_number' => $data['invoice_number_1'] ?? null,
                'invoice_date' => $data['invoice_date_1'] ?? null,
            ], $amount1, $result);
        }

        if ($amount2 > 0.0) {
            $this->service->createFacturaItem(self::CONTEXT, $work, $item, [
                ...$data,
                'invoice_number' => $data['invoice_number_2'] ?? null,
                'invoice_date' => $data['invoice_date_2'] ?? null,
            ], $amount2, $result);
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hasSplitInvoice(array $data): bool
    {
        return $this->service->toFloat($data['invoice_amount_1'] ?? null) !== null
            || $this->service->toFloat($data['invoice_amount_2'] ?? null) !== null
            || $this->service->cleanText($data['invoice_number_1'] ?? null) !== null
            || $this->service->cleanText($data['invoice_number_2'] ?? null) !== null;
    }

    /**
     * @return array{code: string, name: string, double_invoice: bool}
     */
    private function documentFromFile(string $relative): array
    {
        $normalized = $this->normalizer->normalize($relative);

        return match (true) {
            str_contains($normalized, 'edificacion') => ['code' => 'EDIFICACION', 'name' => 'Edificacion REPSOL', 'double_invoice' => true],
            str_contains($normalized, 'obras repsol z10') => ['code' => 'OBRAS_Z10', 'name' => 'Obras REPSOL Z10', 'double_invoice' => true],
            str_contains($normalized, 'obras repsol z50') => ['code' => 'OBRAS_Z50', 'name' => 'Obras REPSOL Z50', 'double_invoice' => true],
            str_contains($normalized, 'licencias') => ['code' => 'LICENCIAS', 'name' => 'Licencias REPSOL', 'double_invoice' => false],
            str_contains($normalized, 'fv') => ['code' => 'FOTOVOLTAICA', 'name' => 'Fotovoltaica REPSOL', 'double_invoice' => false],
            str_contains($normalized, 'estructuras') => ['code' => 'ESTRUCTURAS_VERTIDOS', 'name' => 'Estructuras y vertidos REPSOL', 'double_invoice' => false],
            str_contains($normalized, 'mto') => ['code' => 'MTO', 'name' => 'Mantenimiento REPSOL', 'double_invoice' => false],
            str_contains($normalized, 'puntos de recarga') => ['code' => 'PUNTOS_RECARGA', 'name' => 'Puntos de recarga REPSOL', 'double_invoice' => false],
            str_contains($normalized, 'diseno') => ['code' => 'DISENO', 'name' => 'Diseno REPSOL', 'double_invoice' => false],
            default => ['code' => 'AUTOFACTURACION', 'name' => 'Autofacturacion REPSOL', 'double_invoice' => false],
        };
    }

    /**
     * @param array<string, int> $map
     */
    private function isStationSheet(string $sheet, array $map): bool
    {
        $sheet = $this->normalizer->normalize($sheet);

        return str_contains($sheet, 'listado eess') || isset($map['solred_code']);
    }

    private function isTariffSheet(string $sheet): bool
    {
        $sheet = $this->normalizer->normalize($sheet);

        return str_contains($sheet, 'tarifa') || str_contains($sheet, 'adjud');
    }

    /**
     * @param array<string, int> $map
     */
    private function isWorkSheet(string $sheet, array $map): bool
    {
        $sheet = $this->normalizer->normalize($sheet);

        return isset($map['work_number'])
            && (
                str_contains($sheet, 'autofacturacion')
                || str_contains($sheet, 'alfonso')
                || $sheet === 'otros'
            );
    }

    /**
     * @param array<int, string|null> $values
     * @return array{string, int}|null
     */
    private function tariffCandidate(array $values): ?array
    {
        foreach ($values as $index => $value) {
            $value = $this->service->cleanText($value);
            if ($value !== null && preg_match('/^\d{1,4}(\.\d{1,4}){1,5}$/', $value)) {
                return [$value, (int) $index];
            }
        }

        return null;
    }

    /**
     * @param array<int, string|null> $values
     */
    private function descriptionAfter(array $values, int $index): ?string
    {
        foreach ($values as $cellIndex => $value) {
            if ($cellIndex <= $index) {
                continue;
            }

            $value = $this->service->cleanText($value);
            if ($value !== null && preg_match('/[a-zA-Z]/', $value) && strlen($value) > 5) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param array<int, string|null> $values
     */
    private function lastFloat(array $values): ?float
    {
        krsort($values);
        foreach ($values as $value) {
            $float = $this->service->toFloat($value);
            if ($float !== null && $float >= 0) {
                return $float;
            }
        }

        return null;
    }

    /**
     * @param array<int, string|null> $values
     */
    private function blank(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->service->cleanText($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function relativePath(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', base_path()), '/');
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $base . '/') ? substr($path, strlen($base) + 1) : $path;
    }

    /**
     * @param array<string, int> $map
     */
    private function unknownColumnClassification(string $sheet, array $map): string
    {
        if ($this->isStationSheet($sheet, $map)) {
            return 'functional_decision';
        }

        if ($this->isWorkSheet($sheet, $map)) {
            return 'technical_improvement';
        }

        return 'acceptable';
    }

    /**
     * @param array<string, int> $map
     */
    private function unknownColumnDecision(string $sheet, array $map): string
    {
        if ($this->isStationSheet($sheet, $map)) {
            return 'Revisar si datos de contacto, gestion o licencias deben entrar en maestros vivos de estaciones.';
        }

        if ($this->isWorkSheet($sheet, $map)) {
            return 'Revisar normalización de cabeceras y códigos antes de ampliar el modelo de trabajo/pedido.';
        }

        return 'Hoja auxiliar o técnica; puede seguir fuera del flujo vivo si no aporta dato operativo.';
    }
}
