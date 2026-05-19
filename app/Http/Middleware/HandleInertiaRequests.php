<?php

namespace App\Http\Middleware;

use App\Models\MensajeInterno;
use App\Models\ContextoCliente;
use App\Models\User;
use App\Support\ContextGuard;
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
                'contextos:id_contexto,nombre,codigo',
                'roles:id_rol,slug,nombre',
            ]);
        }

        $workspaceKeyResolver = static fn(?string $code, ?string $name = null): string => ContextGuard::workspaceKey($code, $name);

        $availableContexts = collect();
        $activeContextSelection = null;
        $activeContext = null;
        $canUseAllContexts = false;

        if ($user) {
            $availableContextIds = collect($user->getAccessibleContextIds())
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();

            $availableContexts = ContextoCliente::query()
                ->select('id_contexto', 'nombre', 'codigo')
                ->whereIn('id_contexto', $availableContextIds)
                ->orderBy('nombre')
                ->get();

            $activeContextSelection = $user->getActiveContextSelection();
            $canUseAllContexts = false;

            if (
                $activeContextSelection === User::ACTIVE_CONTEXT_ALL
                && $availableContexts->isNotEmpty()
                && ! $request->is('api/*')
            ) {
                $defaultContextId = (int) $user->getDefaultContextId();
                $activeContext = $availableContexts->firstWhere('id_contexto', $defaultContextId) ?? $availableContexts->first();
                $activeContextSelection = (int) $activeContext->id_contexto;
            }

            $activeContextSelection = $user->setActiveContextSelection($activeContextSelection);

            if (is_int($activeContextSelection)) {
                $activeContext = $availableContexts->firstWhere('id_contexto', $activeContextSelection);
            }

            if (! $activeContext && $availableContexts->isNotEmpty()) {
                $activeContext = $availableContexts->first();
                $activeContextSelection = (int) $activeContext->id_contexto;
            }
        }

        $availableContextsPayload = $availableContexts
            ->map(function ($contexto) use ($workspaceKeyResolver): array {
                return [
                    'id_contexto' => (int) $contexto->id_contexto,
                    'value' => (int) $contexto->id_contexto,
                    'codigo' => $contexto->codigo,
                    'nombre' => ContextGuard::displayName($contexto->codigo, $contexto->nombre),
                    'workspace_key' => $workspaceKeyResolver($contexto->codigo, $contexto->nombre),
                ];
            })
            ->values();

        $activeContextPayload = [
            'id_contexto' => $activeContext?->id_contexto ? (int) $activeContext->id_contexto : null,
            'value' => $activeContext?->id_contexto ? (int) $activeContext->id_contexto : null,
            'codigo' => $activeContext?->codigo,
            'nombre' => ContextGuard::displayName($activeContext?->codigo, $activeContext?->nombre),
            'workspace_key' => $workspaceKeyResolver($activeContext?->codigo, $activeContext?->nombre),
            'is_all' => false,
        ];

        $roleSlugs = $user
            ? $user->roles->pluck('slug')->map(fn(string $slug): string => $slug)->values()->all()
            : [];
        $permissionSlugs = $user?->permission_slugs ?? [];
        $primaryRole = $user?->roles->first();

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
                    'is_technical_admin' => $user->isTechnicalAdmin(),
                    'is_director' => in_array('director', $roleSlugs, true)
                        || in_array('direccion', $roleSlugs, true),
                    'is_execution' => in_array('ejecucion', $roleSlugs, true),
                    'is_execution_moeve' => in_array('ejecucion_moeve', $roleSlugs, true),
                    'is_execution_repsol' => in_array('ejecucion_repsol', $roleSlugs, true),
                    'is_accounting' => in_array('contable', $roleSlugs, true),
                    'can_access_admin_panel' => $user->canAccessAdminPanel(),
                    'can_manage_users' => $user->canManageUsers(),
                    'can_manage_support' => $user->canManageSupport(),
                    'can_manage_maintenance' => $user->canManageMaintenance(),
                    'can_manage_notices' => $user->canManageNotices(),
                    'can_view_audit' => $user->canViewAudit(),
                    'can_manage_imports' => $user->canManageImports(),
                    'can_view_operational_data' => $user->canViewOperationalData(),
                    'can_mutate_operational_data' => $user->canMutateOperationalData(),
                    'can_access_direction_panel' => $user->canAccessDirectionPanel(),
                    'can_access_closure' => $user->canAccessClosure(),
                    'primary_role_slug' => $primaryRole?->slug,
                    'primary_role_name' => $primaryRole?->nombre,
                    'roles' => $user->roles->map(fn($role) => [
                        'id_rol' => $role->id_rol,
                        'nombre' => $role->nombre,
                        'slug' => $role->slug,
                    ])->values(),
                    'role_slugs' => $roleSlugs,
                    'permission_slugs' => $permissionSlugs,
                    'contexto' => $user->contexto ? [
                        'id_contexto' => $user->contexto->id_contexto,
                        'nombre' => $user->contexto->nombre,
                        'codigo' => $user->contexto->codigo,
                    ] : null,
                    'active_context' => $activeContextPayload,
                    'available_contexts' => $availableContextsPayload->values()->all(),
                    'can_use_all_contexts' => $canUseAllContexts,
                    'interface_mode' => $user->interface_mode ?? 'ciete_moderno',
                ] : null,
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
