<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class HomeNoticeCatalog
{
    private const FILE = 'notices.json';

    private const CATEGORIES = ['notices', 'updates', 'companyNews'];

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    public static function empty(): array
    {
        return [
            'notices' => [],
            'updates' => [],
            'companyNews' => [],
        ];
    }

    public static function load(): array
    {
        if (! Storage::exists(self::FILE)) {
            return self::empty();
        }

        $raw = json_decode((string) Storage::get(self::FILE), true);

        if (! is_array($raw)) {
            return self::empty();
        }

        $catalog = self::empty();

        foreach (self::CATEGORIES as $category) {
            $catalog[$category] = collect($raw[$category] ?? [])
                ->filter(fn ($item) => is_array($item))
                ->values()
                ->map(fn (array $item, int $index) => self::normalizeItem($item, $category, $index))
                ->all();
        }

        return $catalog;
    }

    public static function save(array $payload): array
    {
        $existing = self::load();
        $catalog = self::empty();

        foreach (self::CATEGORIES as $category) {
            $existingById = collect($existing[$category] ?? [])
                ->keyBy(fn (array $item) => $item['id']);

            $catalog[$category] = collect($payload[$category] ?? [])
                ->filter(fn ($item) => is_array($item))
                ->values()
                ->map(function (array $item, int $index) use ($category, $existingById) {
                    $normalized = self::normalizeItem($item, $category, $index);
                    $previous = $existingById->get($normalized['id']);

                    $contentChanged = ! $previous
                        || $previous['es'] !== $normalized['es']
                        || $previous['en'] !== $normalized['en'];
                    $featuredToggledOn = $normalized['featured'] && ! ($previous['featured'] ?? false);

                    $normalized['updated_at'] = $contentChanged
                        ? now()->toISOString()
                        : ($previous['updated_at'] ?? $normalized['updated_at']);

                    $normalized['featured_at'] = $normalized['featured']
                        ? ($featuredToggledOn ? now()->toISOString() : ($previous['featured_at'] ?? $normalized['updated_at']))
                        : null;

                    return $normalized;
                })
                ->all();
        }

        Storage::put(self::FILE, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $catalog;
    }

    public static function featured(array $catalog): ?array
    {
        return collect(self::CATEGORIES)
            ->flatMap(fn (string $category) => collect($catalog[$category] ?? [])->map(
                fn (array $item) => [...$item, 'category' => $category],
            ))
            ->filter(fn (array $item) => (bool) ($item['featured'] ?? false))
            ->sortByDesc(fn (array $item) => strtotime((string) ($item['featured_at'] ?? $item['updated_at'] ?? '1970-01-01')))
            ->first();
    }

    private static function normalizeItem(array $item, string $category, int $index): array
    {
        $id = trim((string) ($item['id'] ?? ''));
        $es = trim((string) ($item['es'] ?? ''));
        $en = trim((string) ($item['en'] ?? ''));
        $updatedAt = $item['updated_at'] ?? null;
        $featuredAt = $item['featured_at'] ?? null;

        return [
            'id' => $id !== '' ? $id : self::makeId($category, $index, $es, $en),
            'es' => $es,
            'en' => $en,
            'featured' => (bool) ($item['featured'] ?? false),
            'updated_at' => self::normalizeTimestamp($updatedAt) ?? now()->toISOString(),
            'featured_at' => self::normalizeTimestamp($featuredAt),
        ];
    }

    private static function normalizeTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toISOString();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function makeId(string $category, int $index, string $es, string $en): string
    {
        $seed = trim($es !== '' ? $es : $en);
        $slug = Str::slug(Str::limit($seed, 40, ''), '-');

        return trim($category . '-' . ($slug !== '' ? $slug : (string) ($index + 1)), '-');
    }
}
