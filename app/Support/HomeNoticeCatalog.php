<?php

namespace App\Support;

use App\Models\HomeNotice;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class HomeNoticeCatalog
{
    private const FILE = 'notices.json';

    private const CATEGORIES = ['notices', 'updates', 'companyNews'];

    public static function categories(): array
    {
        return HomeNotice::uiCategories();
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
        if (! Schema::hasTable('home_notices')) {
            return self::loadLegacyFile();
        }

        return self::catalogFromQuery(HomeNotice::query());
    }

    public static function loadActive(): array
    {
        if (! Schema::hasTable('home_notices')) {
            return self::loadLegacyFile();
        }

        return self::catalogFromQuery(
            HomeNotice::query()
                ->activeVisible()
                ->where('is_featured', false),
        );
    }

    public static function save(array $payload, ?User $user = null): array
    {
        if (! Schema::hasTable('home_notices')) {
            return self::saveLegacyFile($payload);
        }

        return DB::transaction(function () use ($payload, $user) {
            $featuredId = null;

            foreach (self::categories() as $categoryKey) {
                $category = HomeNotice::categoryFromInput($categoryKey);

                collect($payload[$categoryKey] ?? [])
                    ->filter(fn ($item) => is_array($item))
                    ->values()
                    ->each(function (array $item, int $index) use ($category, $categoryKey, $user, &$featuredId) {
                        $normalized = self::normalizeItem($item, $categoryKey, $index);
                        $titleEs = trim((string) ($item['title_es'] ?? Str::limit($normalized['es'], 120, '')));
                        $titleEn = trim((string) ($item['title_en'] ?? Str::limit($normalized['en'], 120, '')));

                        $notice = is_numeric($normalized['id'])
                            ? HomeNotice::query()->find((int) $normalized['id'])
                            : null;
                        $notice ??= new HomeNotice();
                        $notice->fill([
                            'category' => $category,
                            'title_es' => $titleEs !== '' ? $titleEs : $normalized['es'],
                            'body_es' => trim((string) ($item['body_es'] ?? $normalized['es'])),
                            'title_en' => $titleEn !== '' ? $titleEn : $normalized['en'],
                            'body_en' => trim((string) ($item['body_en'] ?? $normalized['en'])),
                            'is_active' => (bool) ($item['is_active'] ?? true),
                            'is_featured' => (bool) ($item['featured'] ?? $item['is_featured'] ?? false),
                            'starts_at' => $item['starts_at'] ?? null,
                            'ends_at' => $item['ends_at'] ?? null,
                            'updated_by' => $user?->id_usuario,
                        ]);

                        if (! $notice->exists) {
                            $notice->created_by = $user?->id_usuario;
                        }

                        if (! $notice->is_active) {
                            $notice->is_featured = false;
                        }

                        $notice->save();

                        if ($notice->is_featured && $notice->is_active) {
                            $featuredId = $notice->getKey();
                        }
                    });
            }

            if ($featuredId) {
                HomeNotice::query()
                    ->where('id_home_notice', '!=', $featuredId)
                    ->where('is_featured', true)
                    ->update([
                        'is_featured' => false,
                        'updated_by' => $user?->id_usuario,
                        'updated_at' => now(),
                    ]);
            }

            return self::load();
        });
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

    public static function featuredActive(): ?array
    {
        if (! Schema::hasTable('home_notices')) {
            return self::featured(self::loadLegacyFile());
        }

        return HomeNotice::query()
            ->activeVisible()
            ->where('is_featured', true)
            ->latest('updated_at')
            ->first()
            ?->toHomePayload();
    }

    private static function catalogFromQuery(\Illuminate\Database\Eloquent\Builder $query): array
    {
        $catalog = self::empty();

        $query
            ->orderByRaw("CASE category WHEN 'internal_notice' THEN 1 WHEN 'system_update' THEN 2 WHEN 'company_news' THEN 3 ELSE 4 END")
            ->orderByDesc('is_featured')
            ->orderBy('id_home_notice')
            ->get()
            ->each(function (HomeNotice $notice) use (&$catalog) {
                $catalog[HomeNotice::uiKeyForCategory($notice->category)][] = $notice->toHomePayload();
            });

        return $catalog;
    }

    private static function loadLegacyFile(): array
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

    private static function saveLegacyFile(array $payload): array
    {
        $existing = self::loadLegacyFile();
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
