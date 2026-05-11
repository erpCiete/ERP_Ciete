<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use SplFileInfo;

final class ExcelInventoryService
{
    public function __construct(
        private readonly XlsxWorkbookReader $reader,
        private readonly ExcelHeaderNormalizer $normalizer,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function inventory(string $path, ?string $context = null, ?string $fileFilter = null): array
    {
        $root = $this->resolvePath($path);
        $files = $this->files($root, $context, $fileFilter);
        $inventory = [];

        foreach ($files as $file) {
            $absolute = $file->getPathname();
            $relative = $this->relativePath($absolute);
            $sheets = [];

            foreach ($this->reader->inspect($absolute) as $sheet) {
                $headers = $sheet['headers'] ?? [];
                $sheets[] = [
                    'name' => $sheet['title'],
                    'rows' => $sheet['rows'],
                    'header_row' => $sheet['header_row'],
                    'headers' => $headers,
                    'mapped_columns' => array_keys($this->normalizer->mapHeaders($headers)),
                    'unknown_columns' => $this->normalizer->unknownHeaders($headers),
                    'probable_type' => $this->detectSheetType((string) $sheet['title'], $headers, $relative),
                ];
            }

            $inventory[] = [
                'file' => $relative,
                'context' => $this->detectContext($relative),
                'size' => $file->getSize(),
                'sheets' => $sheets,
            ];
        }

        return $inventory;
    }

    public function resolvePath(string $path): string
    {
        if (is_dir($path) || is_file($path)) {
            return $path;
        }

        $candidate = base_path($path);
        if (is_dir($candidate) || is_file($candidate)) {
            return $candidate;
        }

        if (trim($path, '/\\') === 'excelsactualizados') {
            $fallback = base_path('docs/Abaco/excelsactualizados');
            if (is_dir($fallback)) {
                return $fallback;
            }
        }

        return $candidate;
    }

    /**
     * @return array<int, SplFileInfo>
     */
    public function files(string $root, ?string $context = null, ?string $fileFilter = null): array
    {
        if (is_file($root)) {
            return [new SplFileInfo($root)];
        }

        if (! is_dir($root)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            if (! preg_match('/\.xlsx$/i', $file->getFilename())) {
                continue;
            }

            $relative = $this->relativePath($file->getPathname());
            if ($context !== null && $context !== '' && $this->detectContext($relative) !== mb_strtoupper($context, 'UTF-8')) {
                continue;
            }

            if ($fileFilter !== null && $fileFilter !== '' && ! str_contains(mb_strtolower($relative), mb_strtolower($fileFilter))) {
                continue;
            }

            $files[] = $file;
        }

        usort($files, static fn (SplFileInfo $a, SplFileInfo $b): int => strcmp($a->getPathname(), $b->getPathname()));

        return $files;
    }

    public function relativePath(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', base_path()), '/');
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $base . '/')
            ? substr($path, strlen($base) + 1)
            : $path;
    }

    public function detectContext(string $path): string
    {
        $normalized = mb_strtolower(str_replace('\\', '/', $path), 'UTF-8');

        return match (true) {
            str_contains($normalized, 'mapeo') => 'DESCONOCIDO',
            str_contains($normalized, '/moeve/') || str_contains($normalized, 'moeve') => 'MOEVE',
            str_contains($normalized, '/repsol/') || str_contains($normalized, 'repsol') => 'REPSOL',
            default => 'DESCONOCIDO',
        };
    }

    /**
     * @param array<int, string> $headers
     */
    private function detectSheetType(string $sheet, array $headers, string $file): string
    {
        $sheet = $this->normalizer->normalize($sheet);
        $mapped = $this->normalizer->mapHeaders($headers);
        $file = $this->normalizer->normalize($file);

        if (str_contains($sheet, 'listado eess') || str_contains($file, 'listado eess') || isset($mapped['solred_code']) || isset($mapped['retailgas_code'])) {
            return 'estaciones';
        }

        if (str_contains($sheet, 'tarifa') || str_contains($sheet, 'adjud')) {
            return 'tarifario';
        }

        if (str_contains($sheet, 'facturas emitidas')) {
            return 'facturas_emitidas';
        }

        if (isset($mapped['work_number']) && (isset($mapped['order_number']) || isset($mapped['service_description']))) {
            return 'trabajos_pedidos_facturas';
        }

        if (str_contains($file, 'mapeo')) {
            return 'mapeo';
        }

        return 'auxiliar';
    }
}
