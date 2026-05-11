<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use Generator;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use ZipArchive;

final class XlsxWorkbookReader
{
    /**
     * @return array<int, array{title: string, path: string}>
     */
    public function sheets(string $filePath): array
    {
        return $this->withZip($filePath, function (ZipArchive $zip): array {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if ($workbookXml === false || $relsXml === false) {
                return [];
            }

            $workbook = simplexml_load_string($workbookXml);
            $relationships = simplexml_load_string($relsXml);

            if (! $workbook instanceof SimpleXMLElement || ! $relationships instanceof SimpleXMLElement) {
                return [];
            }

            $targets = [];
            foreach ($relationships->Relationship ?? [] as $relationship) {
                $targets[(string) $relationship['Id']] = $this->normalizeSheetTarget((string) $relationship['Target']);
            }

            $namespaces = $workbook->getNamespaces(true);
            $sheets = [];

            foreach ($workbook->sheets->sheet ?? [] as $sheet) {
                $attrs = $sheet->attributes($namespaces['r'] ?? '');
                $relationshipId = (string) ($attrs['id'] ?? '');

                if ($relationshipId !== '' && isset($targets[$relationshipId])) {
                    $sheets[] = [
                        'title' => (string) $sheet['name'],
                        'path' => $targets[$relationshipId],
                    ];
                }
            }

            return $sheets;
        });
    }

    /**
     * @return array<int, string>
     */
    public function sheetNames(string $filePath): array
    {
        return array_map(
            static fn (array $sheet): string => $sheet['title'],
            $this->sheets($filePath)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function inspect(string $filePath): array
    {
        return $this->withZip($filePath, function (ZipArchive $zip) use ($filePath): array {
            $sharedStrings = $this->sharedStrings($zip);
            $result = [];

            foreach ($this->sheets($filePath) as $sheet) {
                $result[] = [
                    'title' => $sheet['title'],
                    ...$this->inspectSheet($zip, $sheet['path'], $sharedStrings),
                ];
            }

            return $result;
        });
    }

    /**
     * @return Generator<array{row: int, values: array<int, string|null>}>
     */
    public function rows(string $filePath, string $sheetTitle): Generator
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("No se pudo abrir el Excel: {$filePath}");
        }

        try {
            $sheetPath = null;
            foreach ($this->sheets($filePath) as $sheet) {
                if ($sheet['title'] === $sheetTitle) {
                    $sheetPath = $sheet['path'];
                    break;
                }
            }

            if ($sheetPath === null) {
                return;
            }

            $xml = $zip->getFromName($sheetPath);
            if ($xml === false) {
                return;
            }

            $sharedStrings = $this->sharedStrings($zip);
            $reader = new XMLReader();
            $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);

            try {
                while ($reader->read()) {
                    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                        continue;
                    }

                    $rowNumber = (int) $reader->getAttribute('r');
                    $row = simplexml_load_string($reader->readOuterXML());

                    if (! $row instanceof SimpleXMLElement) {
                        continue;
                    }

                    $values = [];
                    foreach ($row->c ?? [] as $cell) {
                        $columnIndex = $this->columnIndex((string) $cell['r']);
                        $values[$columnIndex] = $this->cellValue($cell, $sharedStrings);
                    }

                    if ($values !== []) {
                        ksort($values);
                        yield [
                            'row' => $rowNumber,
                            'values' => $values,
                        ];
                    }
                }
            } finally {
                $reader->close();
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * @template T
     * @param callable(ZipArchive): T $callback
     * @return T
     */
    private function withZip(string $filePath, callable $callback): mixed
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("No se pudo abrir el Excel: {$filePath}");
        }

        try {
            return $callback($zip);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $index = $zip->locateName('xl/sharedStrings.xml');
        if ($index === false) {
            return [];
        }

        $xml = simplexml_load_string($zip->getFromIndex($index));
        if (! $xml instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];
        foreach ($xml->si ?? [] as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r ?? [] as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array{rows: int, header_row: int, headers: array<int, string>}
     */
    private function inspectSheet(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            return ['rows' => 0, 'header_row' => 1, 'headers' => []];
        }

        $reader = new XMLReader();
        $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);

        $maxRow = 0;
        $bestRow = 1;
        $bestCount = 0;
        $bestValues = [];

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                    continue;
                }

                $rowNumber = (int) $reader->getAttribute('r');
                $maxRow = max($maxRow, $rowNumber);

                if ($rowNumber > 30) {
                    continue;
                }

                $row = simplexml_load_string($reader->readOuterXML());
                if (! $row instanceof SimpleXMLElement) {
                    continue;
                }

                $values = [];
                foreach ($row->c ?? [] as $cell) {
                    $value = $this->cellValue($cell, $sharedStrings);
                    if ($value !== null && trim($value) !== '') {
                        $values[$this->columnIndex((string) $cell['r'])] = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);
                    }
                }

                if (count($values) > $bestCount) {
                    $bestCount = count($values);
                    $bestRow = $rowNumber;
                    ksort($values);
                    $bestValues = $values;
                }
            }
        } finally {
            $reader->close();
        }

        return [
            'rows' => $maxRow,
            'header_row' => $bestRow,
            'headers' => $bestValues,
        ];
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? null;
        }

        if ($type === 'inlineStr') {
            return isset($cell->is->t) ? (string) $cell->is->t : null;
        }

        if ($type === 'str') {
            return isset($cell->v) ? (string) $cell->v : null;
        }

        return isset($cell->v) ? (string) $cell->v : null;
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/[A-Z]+/i', $cellReference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeSheetTarget(string $target): string
    {
        $target = str_replace('\\', '/', $target);

        if (str_starts_with($target, '/xl/')) {
            return ltrim($target, '/');
        }

        if (str_starts_with($target, 'xl/')) {
            return $target;
        }

        return 'xl/' . ltrim($target, '/');
    }
}
