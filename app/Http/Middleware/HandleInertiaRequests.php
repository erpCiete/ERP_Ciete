<?php

namespace App\Http\Middleware;

use App\Models\MensajeInterno;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $defaultLocale = strtolower((string) config('app.locale', 'es'));
        $supportedLocales = collect(config('app.supported_locales', []))
            ->map(fn($locale) => strtolower((string) $locale))
            ->filter()
            ->unique()
            ->values();

        if (! $supportedLocales->contains($defaultLocale)) {
            $supportedLocales->prepend($defaultLocale);
        }

        $currentLocale = strtolower((string) app()->getLocale());
        if (! $supportedLocales->contains($currentLocale)) {
            $currentLocale = $defaultLocale;
        }

        if ($user) {
            $user->loadMissing([
                'contexto:id_contexto,nombre,codigo',
                'roles:id_rol,slug,nombre',
            ]);
        }

        $avatarCatalog = collect(config('profile.avatar_catalog', []));
        $fallbackAvatar = $avatarCatalog->first();
        $selectedAvatar = $user ? ($avatarCatalog->get($user->avatar_key) ?? $fallbackAvatar) : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id_usuario' => $user->id_usuario,
                    'id_contexto' => $user->id_contexto,
                    'nombre' => $user->nombre,
                    'apellidos' => $user->apellidos,
                    'nombre_usuario' => $user->nombre_usuario,
                    'email' => $user->email,
                    'email_recuperacion' => $user->email_recuperacion,
                    'telefono' => $user->telefono,
                    'avatar_key' => $user->avatar_key,
                    'avatar_url' => $selectedAvatar ? asset($selectedAvatar['file']) : null,
                    'activo' => $user->activo,
                    'is_admin' => $user->is_admin,
                    'roles' => $user->roles->map(fn($role) => [
                        'id_rol' => $role->id_rol,
                        'nombre' => $role->nombre,
                        'slug' => $role->slug,
                    ])->values(),
                    'role_slugs' => $user->role_slugs,
                    'permission_slugs' => $user->permission_slugs,
                    'contexto' => $user->contexto ? [
                        'id_contexto' => $user->contexto->id_contexto,
                        'nombre' => $user->contexto->nombre,
                        'codigo' => $user->contexto->codigo,
                    ] : null,
                ] : null,
                'session' => [
                    'id' => $request->session()->getId(),
                ],
            ],
            'locale' => [
                'current' => $currentLocale,
                'supported' => $supportedLocales->values()->all(),
            ],
            'maintenance' => [
                'active' => file_exists(storage_path('framework/maintenance_mode')),
            ],
            'unreadMessages' => $user
                ? MensajeInterno::where('id_destinatario', $user->id_usuario)
                ->whereNull('leido_at')
                ->where('archivado', false)
                ->count()
                : 0,
        ];
    }
}
