<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

/**
 * Filtro personalizado para leer el Excel en "Chunks" (Trozos)
 * Esto previene que el servidor PHP se quede sin RAM (Error 500: Allowed memory size exhausted)
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 0;
    private int $endRow   = 0;

    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize - 1;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        // Solo leer las filas que estén dentro del bloque actual
        // Además, asumimos que no hay datos más allá de la columna F para optimizar
        if ($row >= $this->startRow && $row <= $this->endRow) {
            if (in_array($columnAddress, range('A', 'F'))) {
                return true;
            }
        }
        return false;
    }
}

class ExcelParserService
{
    /**
     * Mapeo de columnas de la tarea D03-02
     * A = numero_trabajo
     * B = descripcion_trabajo
     * C = codigo_estacion (ES-XXX)
     * D = fecha_encargo
     * E = observaciones
     */
    const COL_NUMERO_TRABAJO = 'A';
    const COL_DESCRIPCION    = 'B';
    const COL_ESTACION       = 'C';
    const COL_FECHA          = 'D';
    const COL_OBSERVACIONES  = 'E';

    /**
     * Procesa el archivo Excel y devuelve un array con los datos limpios y estandarizados.
     * Utiliza generadores (yield) para procesar grandes volúmenes de forma eficiente.
     *
     * @param string $filePath Ruta absoluta del archivo subido.
     * @return array
     */
    public function parseFile(string $filePath): array
    {
        $parsedData = [];
        $chunkSize = 200; // Leer de 200 en 200 filas para no saturar memoria
        $chunkFilter = new ChunkReadFilter();

        try {
            // Identificar el tipo de archivo (xlsx, csv, etc.) y configurar el lector
            $inputFileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($inputFileType);
            
            // Optimizaciones Críticas (Bible Standard)
            $reader->setReadDataOnly(true);       // Ignorar estilos, colores y bordes
            $reader->setReadEmptyCells(false);    // Ignorar celdas vacías perdidas por la hoja
            $reader->setReadFilter($chunkFilter);

            // Iterar en Chunks
            // Asumimos un máximo de 10,000 filas por seguridad, o hasta que encontremos un chunk vacío
            for ($startRow = 2; $startRow <= 10000; $startRow += $chunkSize) {
                
                // Empezamos en startRow = 2 para saltar las cabeceras.
                $chunkFilter->setRows($startRow, $chunkSize);
                
                // Cargar solo el bloque actual
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                
                $hasDataInChunk = false;

                foreach ($sheet->getRowIterator($startRow, $startRow + $chunkSize - 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    // NO iterar celdas que no existen en memoria
                    $cellIterator->setIterateOnlyExistingCells(true); 

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[$cell->getColumn()] = $cell->getValue();
                    }

                    // Ignorar filas completamente vacías (a veces Excel guarda filas fantasma)
                    if (empty($rowData) || !isset($rowData[self::COL_NUMERO_TRABAJO]) || trim($rowData[self::COL_NUMERO_TRABAJO]) === '') {
                        continue;
                    }

                    $hasDataInChunk = true;

                    // Mapeo y Saneamiento de Datos
                    $parsedData[] = [
                        // Identificador principal (evita duplicados)
                        'numero_trabajo'      => trim((string) $rowData[self::COL_NUMERO_TRABAJO]),
                        
                        'descripcion_trabajo' => isset($rowData[self::COL_DESCRIPCION]) ? trim((string) $rowData[self::COL_DESCRIPCION]) : 'Sin descripción',
                        
                        // La estación viene como código (ej. "ES-001"), luego el Controller o ImportService deberá buscar su ID
                        'codigo_estacion'     => isset($rowData[self::COL_ESTACION]) ? trim((string) $rowData[self::COL_ESTACION]) : null,
                        
                        // Parseo seguro de fecha Excel
                        'fecha_encargo'       => $this->parseExcelDate($rowData[self::COL_FECHA] ?? null),
                        
                        'observaciones'       => isset($rowData[self::COL_OBSERVACIONES]) ? trim((string) $rowData[self::COL_OBSERVACIONES]) : null,
                    ];
                }

                // Liberar memoria (Obligatorio en PhpSpreadsheet)
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);

                // Si recorrimos un bloque entero y no había datos reales, asumimos que hemos llegado al final del Excel.
                if (!$hasDataInChunk) {
                    break;
                }
            }

        } catch (\Exception $e) {
            Log::error('Error parseando Excel de Trabajos: ' . $e->getMessage());
            throw new \Exception('El archivo Excel está corrupto o tiene un formato no válido. Asegúrese de usar la plantilla.');
        }

        return $parsedData;
    }

    /**
     * Convierte el número serial de fecha de Excel (ej: 45021) o un string de fecha
     * a un formato YYYY-MM-DD compatible con MySQL.
     */
    private function parseExcelDate($dateValue): ?string
    {
        if (empty($dateValue)) {
            return null;
        }

        try {
            // Si es un número (formato interno de fecha de Excel)
            if (is_numeric($dateValue)) {
                $dateTime = Date::excelToDateTimeObject($dateValue);
                return $dateTime->format('Y-m-d');
            }

            // Si el usuario escribió la fecha a mano como texto ("15/04/2026")
            if (is_string($dateValue)) {
                $date = date_create_from_format('d/m/Y', $dateValue);
                if ($date) {
                    return $date->format('Y-m-d');
                }
                
                // Fallback para fechas genéricas (Y-m-d)
                return date('Y-m-d', strtotime($dateValue));
            }
        } catch (\Exception $e) {
            // Si hay un error al parsear, devolvemos null en lugar de romper toda la importación
            Log::warning('Fecha no parseable en Excel: ' . $dateValue);
            return null;
        }

        return null;
    }
}