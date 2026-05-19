<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeNotice extends Model
{
    public const CATEGORY_INTERNAL_NOTICE = 'internal_notice';
    public const CATEGORY_SYSTEM_UPDATE = 'system_update';
    public const CATEGORY_COMPANY_NEWS = 'company_news';

    public const CATEGORY_TO_UI_KEY = [
        self::CATEGORY_INTERNAL_NOTICE => 'notices',
        self::CATEGORY_SYSTEM_UPDATE => 'updates',
        self::CATEGORY_COMPANY_NEWS => 'companyNews',
    ];

    public const UI_KEY_TO_CATEGORY = [
        'notices' => self::CATEGORY_INTERNAL_NOTICE,
        'updates' => self::CATEGORY_SYSTEM_UPDATE,
        'companyNews' => self::CATEGORY_COMPANY_NEWS,
    ];

    protected $table = 'home_notices';

    protected $primaryKey = 'id_home_notice';

    protected $fillable = [
        'category',
        'title_es',
        'body_es',
        'title_en',
        'body_en',
        'is_active',
        'is_featured',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public static function databaseCategories(): array
    {
        return array_keys(self::CATEGORY_TO_UI_KEY);
    }

    /**
     * @return list<string>
     */
    public static function uiCategories(): array
    {
        return array_values(self::CATEGORY_TO_UI_KEY);
    }

    public static function categoryFromInput(?string $category): ?string
    {
        $value = trim((string) $category);

        return self::UI_KEY_TO_CATEGORY[$value] ?? (in_array($value, self::databaseCategories(), true) ? $value : null);
    }

    public static function uiKeyForCategory(?string $category): string
    {
        return self::CATEGORY_TO_UI_KEY[$category] ?? 'notices';
    }

    public function scopeActiveVisible(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_usuario');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_usuario');
    }

    /**
     * @return array<string, mixed>
     */
    public function toHomePayload(): array
    {
        $categoryKey = self::uiKeyForCategory($this->category);

        return [
            'id' => $this->getKey(),
            'category' => $categoryKey,
            'category_value' => $this->category,
            'title_es' => $this->title_es,
            'body_es' => $this->body_es,
            'title_en' => $this->title_en,
            'body_en' => $this->body_en,
            'es' => trim($this->title_es . "\n" . $this->body_es),
            'en' => trim($this->title_en . "\n" . $this->body_en),
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'featured' => (bool) $this->is_featured,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
