<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use App\Models\Factura;
use SplFileInfo;

final class MoeveExcelImporter
{
    private const CONTEXT = 'MOEVE';

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

        $result->addFile($relative, [
            'context' => self::CONTEXT,
            'type' => $this->fileType($relative),
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

        foreach ($inspect as $sheet) {
            $title = (string) $sheet['title'];
            $headers = $sheet['headers'] ?? [];
            $map = $this->normalizer->mapHeaders($headers);
            $result->addUnknownColumns(
                $relative,
                $title,
                $this->normalizer->unknownHeaders($headers),
                classification: $this->unknownColumnClassification($relative, $title, $map),
                decision: $this->unknownColumnDecision($relative, $title, $map),
                code: 'unknown_columns'
            );

            if ($this->isStationSheet($relative, $title, $map)) {
                $this->importStations($path, $relative, $title, (int) $sheet['header_row'], $map, $result);
                continue;
            }

            if ($this->normalizer->normalize($title) === 'trabajos') {
                $this->importWorks($path, $relative, $title, (int) $sheet['header_row'], $map, $result);
                continue;
            }

            if ($this->normalizer->normalize($title) === 'facturas emitidas') {
                $this->syncIssuedInvoices($path, $relative, $title, (int) $sheet['header_row'], $map, $result);
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
                'address' => $this->service->value($row['values'], $map, 'address'),
                'postal_code' => $this->service->value($row['values'], $map, 'postal_code'),
                'city' => $this->service->value($row['values'], $map, 'city'),
                'province' => $this->service->value($row['values'], $map, 'province'),
                'latitude' => $this->service->value($row['values'], $map, 'latitude'),
                'longitude' => $this->service->value($row['values'], $map, 'longitude'),
                'status' => $this->service->value($row['values'], $map, 'status'),
                'inactive_date' => $this->service->value($row['values'], $map, 'inactive_date'),
                'margin_number' => $this->service->value($row['values'], $map, 'margin_number'),
                'retailgas_code' => $this->service->value($row['values'], $map, 'retailgas_code'),
                'billing_company_code' => $this->service->value($row['values'], $map, 'billing_company_code'),
                'billing_company' => $this->service->value($row['values'], $map, 'billing_company'),
            ], $result);

            if ($stationId === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Fila de estación MOEVE sin código útil.',
                    'station_missing_code',
                    'acceptable',
                    'Mantener fuera salvo que CIETE confirme un código alternativo fiable para esa estación.'
                );
                continue;
            }

            $result->rowImported($relative, $sheet);
        }
    }

    /**
     * @param array<string, int> $map
     */
    private function importWorks(string $path, string $relative, string $sheet, int $headerRow, array $map, ImportValidationResult $result): void
    {
        $documentId = $this->service->ensureTipoDocumento(self::CONTEXT, 'MOEVE_CONTROL', 'Control trabajos MOEVE', false, false, false, $result);

        foreach ($this->reader->rows($path, $sheet) as $row) {
            if ($row['row'] <= $headerRow || $this->blank($row['values'])) {
                continue;
            }

            $result->rowSeen($relative, $sheet);
            $data = [
                '_file' => $relative,
                '_sheet' => $sheet,
                '_row' => (int) $row['row'],
                'work_number' => $this->service->value($row['values'], $map, 'work_number'),
                'station_code' => $this->service->value($row['values'], $map, 'station_code'),
                'station_name' => $this->service->value($row['values'], $map, 'station_name'),
                'city' => $this->service->value($row['values'], $map, 'city'),
                'province' => $this->service->value($row['values'], $map, 'province'),
                'order_number' => $this->service->value($row['values'], $map, 'order_number'),
                'order_amount' => $this->service->value($row['values'], $map, 'order_amount'),
                'requested_amount' => $this->service->value($row['values'], $map, 'requested_amount'),
                'invoiced_amount' => $this->service->value($row['values'], $map, 'invoiced_amount'),
                'assignment_date' => $this->service->value($row['values'], $map, 'assignment_date'),
                'completion_date' => $this->service->value($row['values'], $map, 'completion_date'),
                'service_description' => $this->service->value($row['values'], $map, 'service_description'),
                'work_description' => $this->service->value($row['values'], $map, 'work_description'),
                'responsible_ciete' => $this->service->value($row['values'], $map, 'responsible_ciete'),
                'responsible_client' => $this->service->value($row['values'], $map, 'responsible_client'),
                'observations' => $this->service->value($row['values'], $map, 'observations'),
                'invoice_number' => $this->service->value($row['values'], $map, 'invoice_number'),
                'invoice_date' => $this->service->value($row['values'], $map, 'invoice_date'),
                'contract_code' => $this->service->value($row['values'], $map, 'contract_code') ?: '772',
                'status' => $this->service->value($row['values'], $map, 'status'),
                'category' => $this->service->value($row['values'], $map, 'category'),
            ];

            $work = $this->service->createOrUpdateWork(self::CONTEXT, $documentId, $data, $result);
            if ($work === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Fila de trabajo MOEVE sin número de trabajo.',
                    'work_missing_number',
                    'acceptable',
                    'Mantener fuera si la fila es cabecera repetida, subtotal o linea no operativa.'
                );
                continue;
            }

            $item = $this->service->createPedidoItemAndOptionalInvoice(self::CONTEXT, $work, $data, $result);
            if ($item === null && $this->service->toFloat($data['order_amount'] ?? null) !== null) {
                $result->warn(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Trabajo MOEVE con importe pero sin número de pedido; no se crea pedido_item.',
                    'work_amount_without_order',
                    'functional_decision',
                    'Confirmar con CIETE si estas lineas deben quedar como trabajo sin pedido o requieren otro origen documental.'
                );
            }

            $result->rowImported($relative, $sheet);
        }
    }

    /**
     * @param array<string, int> $map
     */
    private function syncIssuedInvoices(string $path, string $relative, string $sheet, int $headerRow, array $map, ImportValidationResult $result): void
    {
        $contextId = $this->service->contextId(self::CONTEXT, $result);

        foreach ($this->reader->rows($path, $sheet) as $row) {
            if ($row['row'] <= $headerRow || $this->blank($row['values'])) {
                continue;
            }

            $result->rowSeen($relative, $sheet);
            $number = $this->service->cleanText($this->service->value($row['values'], $map, 'invoice_number'));
            if ($number === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    'Fila de factura emitida MOEVE sin número de factura.',
                    'invoice_missing_number',
                    'acceptable',
                    'Mantener fuera si la fila es subtotal, nota o linea de apoyo.'
                );
                continue;
            }

            $factura = Factura::query()
                ->where('id_contexto', $contextId)
                ->where('numero_factura', $number)
                ->first();

            if ($factura === null) {
                $result->ignore(
                    $relative,
                    $sheet,
                    (int) $row['row'],
                    "Factura MOEVE {$number} no enlazada a factura_items; se deja como aviso.",
                    'historical_invoice_without_items',
                    'do_not_invent',
                    'No enlazar por aproximacion. Requiere pedido_item real o decision funcional expresa de tratamiento historico.'
                );
                continue;
            }

            $factura->fill([
                'numero_factura_ccp' => $this->service->value($row['values'], $map, 'invoice_ccp') ?: $factura->numero_factura_ccp,
                'fecha_emision' => $this->service->toDate($this->service->value($row['values'], $map, 'invoice_date')) ?: $factura->fecha_emision,
                'sociedad' => $this->service->value($row['values'], $map, 'billing_company') ?: $factura->sociedad,
            ])->save();

            $result->rowImported($relative, $sheet);
        }
    }

    /**
     * @param array<string, int> $map
     */
    private function isStationSheet(string $relative, string $sheet, array $map): bool
    {
        $file = $this->normalizer->normalize($relative);
        $sheet = $this->normalizer->normalize($sheet);

        return (str_contains($file, 'listado eess') && (str_contains($sheet, 'espana') || isset($map['station_code'])))
            || str_contains($sheet, 'espana')
            || isset($map['retailgas_code']);
    }

    private function fileType(string $relative): string
    {
        $normalized = $this->normalizer->normalize($relative);

        return match (true) {
            str_contains($normalized, 'listado eess') => 'estaciones',
            str_contains($normalized, 'contrato') || str_contains($normalized, 'tarifario') => 'tarifario',
            str_contains($normalized, 'control') => 'trabajos_pedidos_facturas',
            default => 'auxiliar',
        };
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
    private function unknownColumnClassification(string $relative, string $sheet, array $map): string
    {
        if ($this->isStationSheet($relative, $sheet, $map)) {
            return 'functional_decision';
        }

        return $this->normalizer->normalize($sheet) === 'facturas emitidas'
            ? 'do_not_invent'
            : 'acceptable';
    }

    /**
     * @param array<string, int> $map
     */
    private function unknownColumnDecision(string $relative, string $sheet, array $map): string
    {
        if ($this->isStationSheet($relative, $sheet, $map)) {
            return 'Revisar si columnas de contacto, licencias o mantenimiento merecen destino de maestro de estaciones.';
        }

        return $this->normalizer->normalize($sheet) === 'facturas emitidas'
            ? 'No crear detalle historico sin pedido_item real; solo revisar si conviene conservar metadatos de cabecera.'
            : 'Hoja auxiliar o columnas no operativas; se pueden mantener fuera si no aportan flujo vivo.';
    }
}
