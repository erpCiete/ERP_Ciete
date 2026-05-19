<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeNotice;
use App\Services\AuditLogger;
use App\Support\HomeNoticeCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NoticeController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedNoticePayload($request);

        DB::transaction(function () use ($request, $payload) {
            $notice = new HomeNotice([
                ...$payload,
                'created_by' => $request->user()?->id_usuario,
                'updated_by' => $request->user()?->id_usuario,
            ]);

            $notice->save();

            if ($notice->is_featured && $notice->is_active) {
                $this->clearOtherFeaturedNotices($notice, $request);
            }

            $this->logNoticeChange($request, 'crear', $notice, null, $this->noticeSnapshot($notice), 'Mensaje de inicio creado.');
        });

        return back();
    }

    public function update(Request $request, HomeNotice $homeNotice): RedirectResponse
    {
        $payload = $this->validatedNoticePayload($request);

        DB::transaction(function () use ($request, $homeNotice, $payload) {
            $before = $this->noticeSnapshot($homeNotice);

            $homeNotice->fill([
                ...$payload,
                'updated_by' => $request->user()?->id_usuario,
            ]);

            $homeNotice->save();

            if ($homeNotice->is_featured && $homeNotice->is_active) {
                $this->clearOtherFeaturedNotices($homeNotice, $request);
            }

            $this->logNoticeChange($request, 'actualizar', $homeNotice, $before, $this->noticeSnapshot($homeNotice), 'Mensaje de inicio actualizado.');
        });

        return back();
    }

    public function toggle(Request $request, HomeNotice $homeNotice): RedirectResponse
    {
        $targetActive = $request->has('is_active')
            ? $request->boolean('is_active')
            : ! $homeNotice->is_active;

        DB::transaction(function () use ($request, $homeNotice, $targetActive) {
            $before = $this->noticeSnapshot($homeNotice);

            $homeNotice->forceFill([
                'is_active' => $targetActive,
                'is_featured' => $targetActive ? $homeNotice->is_featured : false,
                'updated_by' => $request->user()?->id_usuario,
            ])->save();

            if ($homeNotice->is_featured && $homeNotice->is_active) {
                $this->clearOtherFeaturedNotices($homeNotice, $request);
            }

            $this->logNoticeChange(
                $request,
                $targetActive ? 'activar' : 'desactivar',
                $homeNotice,
                $before,
                $this->noticeSnapshot($homeNotice),
                $targetActive ? 'Mensaje de inicio activado.' : 'Mensaje de inicio desactivado.',
            );
        });

        return back();
    }

    public function feature(Request $request, HomeNotice $homeNotice): RedirectResponse
    {
        $targetFeatured = $request->has('is_featured')
            ? $request->boolean('is_featured')
            : ! $homeNotice->is_featured;

        DB::transaction(function () use ($request, $homeNotice, $targetFeatured) {
            $before = $this->noticeSnapshot($homeNotice);

            $homeNotice->forceFill([
                'is_active' => $targetFeatured ? true : $homeNotice->is_active,
                'is_featured' => $targetFeatured,
                'updated_by' => $request->user()?->id_usuario,
            ])->save();

            if ($targetFeatured) {
                $this->clearOtherFeaturedNotices($homeNotice, $request);
            }

            $this->logNoticeChange(
                $request,
                'actualizar',
                $homeNotice,
                $before,
                $this->noticeSnapshot($homeNotice),
                $targetFeatured ? 'Mensaje de inicio marcado como destacado.' : 'Mensaje de inicio quitado de destacados.',
                'is_featured',
            );
        });

        return back();
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'notices'     => 'required|array|max:5',
            'updates'     => 'required|array|max:5',
            'companyNews' => 'required|array|max:5',

            'notices.*.id'     => 'nullable|string|max:120',
            'notices.*.es'     => 'required|string|max:300',
            'notices.*.en'     => 'required|string|max:300',
            'notices.*.featured' => 'nullable|boolean',
            'updates.*.id'     => 'nullable|string|max:120',
            'updates.*.es'     => 'required|string|max:300',
            'updates.*.en'     => 'required|string|max:300',
            'updates.*.featured' => 'nullable|boolean',
            'companyNews.*.id' => 'nullable|string|max:120',
            'companyNews.*.es' => 'required|string|max:300',
            'companyNews.*.en' => 'required|string|max:300',
            'companyNews.*.featured' => 'nullable|boolean',
        ]);

        $this->ensureSingleFeaturedPayload($request);

        HomeNoticeCatalog::save($request->only(HomeNoticeCatalog::categories()), $request->user());

        return back();
    }

    public static function load(): array
    {
        return HomeNoticeCatalog::load();
    }

    public static function loadActive(): array
    {
        return HomeNoticeCatalog::loadActive();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedNoticePayload(Request $request): array
    {
        $category = HomeNotice::categoryFromInput((string) $request->input('category'));

        if ($category !== null) {
            $request->merge(['category' => $category]);
        }

        $payload = $request->validate([
            'category' => ['required', 'string', Rule::in(HomeNotice::databaseCategories())],
            'title_es' => ['required', 'string', 'max:180'],
            'body_es' => ['required', 'string', 'max:1200'],
            'title_en' => ['required', 'string', 'max:180'],
            'body_en' => ['required', 'string', 'max:1200'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $payload['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $payload['is_featured'] = $request->boolean('is_featured');

        if (! $payload['is_active']) {
            $payload['is_featured'] = false;
        }

        return $payload;
    }

    private function ensureSingleFeaturedPayload(Request $request): void
    {
        $featuredCount = collect(HomeNoticeCatalog::categories())
            ->flatMap(fn (string $category) => collect($request->input($category, [])))
            ->filter(fn (mixed $item) => is_array($item))
            ->filter(fn (array $item) => (bool) ($item['featured'] ?? $item['is_featured'] ?? false))
            ->filter(fn (array $item) => (bool) ($item['is_active'] ?? true))
            ->count();

        if ($featuredCount <= 1) {
            return;
        }

        throw ValidationException::withMessages([
            'is_featured' => 'Solo puede haber un mensaje destacado activo.',
        ]);
    }

    private function clearOtherFeaturedNotices(HomeNotice $selectedNotice, Request $request): void
    {
        HomeNotice::query()
            ->where('id_home_notice', '!=', $selectedNotice->getKey())
            ->where('is_featured', true)
            ->get()
            ->each(function (HomeNotice $notice) use ($request) {
                $before = $this->noticeSnapshot($notice);

                $notice->forceFill([
                    'is_featured' => false,
                    'updated_by' => $request->user()?->id_usuario,
                ])->save();

                $this->logNoticeChange(
                    $request,
                    'actualizar',
                    $notice,
                    $before,
                    $this->noticeSnapshot($notice),
                    'Mensaje de inicio quitado de destacados por nuevo destacado activo.',
                    'is_featured',
                );
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function noticeSnapshot(HomeNotice $notice): array
    {
        return [
            'category' => $notice->category,
            'title_es' => $notice->title_es,
            'body_es' => $notice->body_es,
            'title_en' => $notice->title_en,
            'body_en' => $notice->body_en,
            'is_active' => (bool) $notice->is_active,
            'is_featured' => (bool) $notice->is_featured,
            'starts_at' => $notice->starts_at?->toDateTimeString(),
            'ends_at' => $notice->ends_at?->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function logNoticeChange(
        Request $request,
        string $action,
        HomeNotice $notice,
        ?array $before,
        ?array $after,
        string $description,
        ?string $field = null,
    ): void {
        $firstChange = $before && $after
            ? $this->auditLogger->resolveFirstChange($before, $after)
            : ['campo' => $field, 'valor_anterior' => null, 'valor_nuevo' => null];

        $this->auditLogger->log([
            'accion' => $action,
            'tabla' => 'home_notices',
            'modulo' => 'avisos_inicio',
            'entity_type' => 'home_notice',
            'registro_id' => $notice->getKey(),
            'campo' => $field ?? $firstChange['campo'],
            'valor_anterior' => $field && $before ? ($before[$field] ?? null) : $firstChange['valor_anterior'],
            'valor_nuevo' => $field && $after ? ($after[$field] ?? null) : $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $description,
        ], $request);
    }
}
