<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContextoCliente;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const TECHNICAL_PERMISSION_SLUGS = [
        'admin.panel.ver',
        'usuarios.ver',
        'usuarios.crear',
        'usuarios.editar',
        'usuarios.gestionar',
        'soporte.gestionar',
        'auditoria.ver',
        'auditoria.exportar',
        'auditoria.limpiar',
        'mantenimiento.gestionar',
        'avisos.gestionar',
        'importaciones.ver',
        'importaciones.ejecutar',
        'importaciones.confirmar',
    ];

    private const OPERATIONAL_READ_PERMISSION_SLUGS = [
        'trabajos.ver',
        'pedidos.ver',
        'facturas.ver',
        'clientes.ver',
        'estaciones.ver',
        'maestros.ver',
        'contratos.ver',
        'sociedades_facturadoras.ver',
        'tarifarios.ver',
        'tarifario_lineas.ver',
    ];

    private const OPERATIONAL_MUTATION_PERMISSION_SLUGS = [
        'trabajos.crear',
        'trabajos.editar',
        'trabajos.eliminar',
        'trabajos.finalizar',
        'trabajos.cambiar_estado',
        'trabajos.marcar_terminado',
        'trabajos.editar_finalizado',
        'pedidos.crear',
        'pedidos.editar',
        'pedidos.eliminar',
        'facturas.crear',
        'facturas.editar',
        'facturas.eliminar',
        'clientes.crear',
        'clientes.editar',
        'clientes.eliminar',
        'estaciones.crear',
        'estaciones.editar',
        'estaciones.eliminar',
        'maestros.gestionar',
        'contratos.crear',
        'contratos.editar',
        'contratos.eliminar',
        'sociedades_facturadoras.crear',
        'sociedades_facturadoras.editar',
        'sociedades_facturadoras.eliminar',
        'tarifarios.crear',
        'tarifarios.editar',
        'tarifarios.eliminar',
        'tarifario_lineas.crear',
        'tarifario_lineas.editar',
        'tarifario_lineas.eliminar',
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Request $request): Response
    {
        $query = User::query()
            ->with([
                'roles.permissions:id_permiso,slug,nombre',
                'contexto:id_contexto,nombre,codigo',
                'contextos:id_contexto,nombre,codigo',
            ]);

        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('apellidos', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('nombre_usuario', 'like', $term);
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $users = $query->orderBy('nombre')->paginate(10)->withQueryString()
            ->through(function (User $user): array {
                return [
                    ...$user->toArray(),
                    'scope_summary' => $this->buildPermissionScopeSummary($user->roles->flatMap(fn(Role $role) => $role->permissions)),
                    'contextos_asignados' => $user->contextos
                        ->map(fn(ContextoCliente $contexto): array => [
                            'id_contexto' => $contexto->id_contexto,
                            'codigo' => $contexto->codigo,
                            'nombre' => $contexto->nombre,
                        ])
                        ->values()
                        ->all(),
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filtros' => $request->only(['search', 'activo']),
            'rolesInfo' => $this->buildRoleCatalog(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'roles'     => $this->buildRoleCatalog(),
            'contextos' => ContextoCliente::where('activo', true)->get(['id_contexto', 'nombre', 'codigo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'apellidos'       => ['nullable', 'string', 'max:150'],
            'nombre_usuario'  => ['required', 'string', 'max:50', Rule::unique('usuarios', 'nombre_usuario')],
            'email'           => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')],
            'password'        => ['required', Password::defaults()],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'id_contexto'     => ['required', 'exists:contextos_cliente,id_contexto'],
            'activo'          => ['boolean'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['exists:roles,id_rol'],
            'contextos'       => ['nullable', 'array'],
            'contextos.*'     => ['exists:contextos_cliente,id_contexto'],
        ]);

        $user = User::create([
            'nombre'         => $validated['nombre'],
            'apellidos'      => $validated['apellidos'] ?? null,
            'nombre_usuario' => $validated['nombre_usuario'],
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'telefono'       => $validated['telefono'] ?? null,
            'id_contexto'    => $validated['id_contexto'],
            'activo'         => $validated['activo'] ?? true,
        ]);

        if (! empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        if (! empty($validated['contextos'])) {
            $pivotData = [];
            foreach ($validated['contextos'] as $ctxId) {
                $pivotData[$ctxId] = [
                    'es_contexto_principal' => (int) $ctxId === (int) $validated['id_contexto'],
                    'activo'                => true,
                ];
            }
            $user->contextos()->sync($pivotData);
        }

        $snapshot = $this->buildUserAuditSnapshot($user->fresh());
        $this->logAction($request, 'crear', 'usuarios', $user->id_usuario, [
            'entity_type' => User::class,
            'campo' => 'id_usuario',
            'valor_nuevo' => $user->id_usuario,
            'datos_nuevos' => $snapshot,
            'descripcion' => 'Alta de usuario en administración.',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): Response
    {
        $user->load(['roles:id_rol,slug,nombre', 'contextos:id_contexto,nombre,codigo']);

        return Inertia::render('Admin/Users/Form', [
            'user'      => $user,
            'roles'     => $this->buildRoleCatalog(),
            'contextos' => ContextoCliente::where('activo', true)->get(['id_contexto', 'nombre', 'codigo']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $beforeSnapshot = $this->buildUserAuditSnapshot($user);

        $validated = $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'apellidos'       => ['nullable', 'string', 'max:150'],
            'nombre_usuario'  => ['required', 'string', 'max:50', Rule::unique('usuarios', 'nombre_usuario')->ignore($user->id_usuario, 'id_usuario')],
            'email'           => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($user->id_usuario, 'id_usuario')],
            'password'        => ['nullable', Password::defaults()],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'id_contexto'     => ['required', 'exists:contextos_cliente,id_contexto'],
            'activo'          => ['boolean'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['exists:roles,id_rol'],
            'contextos'       => ['nullable', 'array'],
            'contextos.*'     => ['exists:contextos_cliente,id_contexto'],
        ]);

        $user->update([
            'nombre'         => $validated['nombre'],
            'apellidos'      => $validated['apellidos'] ?? null,
            'nombre_usuario' => $validated['nombre_usuario'],
            'email'          => $validated['email'],
            'telefono'       => $validated['telefono'] ?? null,
            'id_contexto'    => $validated['id_contexto'],
            'activo'         => $validated['activo'] ?? $user->activo,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->roles()->sync($validated['roles'] ?? []);

        $pivotData = [];
        foreach (($validated['contextos'] ?? []) as $ctxId) {
            $pivotData[$ctxId] = [
                'es_contexto_principal' => (int) $ctxId === (int) $validated['id_contexto'],
                'activo'                => true,
            ];
        }
        $user->contextos()->sync($pivotData);

        $user->refresh();
        $afterSnapshot = $this->buildUserAuditSnapshot($user);
        $firstChangedField = $this->resolveFirstChangedField($beforeSnapshot, $afterSnapshot);

        $this->logAction($request, 'actualizar', 'usuarios', $user->id_usuario, [
            'entity_type' => User::class,
            'campo' => $firstChangedField,
            'valor_anterior' => $firstChangedField ? ($beforeSnapshot[$firstChangedField] ?? null) : null,
            'valor_nuevo' => $firstChangedField ? ($afterSnapshot[$firstChangedField] ?? null) : null,
            'datos_anteriores' => $beforeSnapshot,
            'datos_nuevos' => $afterSnapshot,
            'descripcion' => $this->buildUpdateDescription($beforeSnapshot, $afterSnapshot),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id_usuario === $request->user()->id_usuario) {
            return back()->withErrors(['toggle' => 'No puedes desactivarte a ti mismo.']);
        }

        $beforeStatus = (bool) $user->activo;
        $user->update(['activo' => ! $user->activo]);
        $user->refresh();
        $afterStatus = (bool) $user->activo;

        $action = $afterStatus ? 'activar' : 'desactivar';
        $this->logAction($request, $action, 'usuarios', $user->id_usuario, [
            'entity_type' => User::class,
            'campo' => 'activo',
            'valor_anterior' => $beforeStatus,
            'valor_nuevo' => $afterStatus,
            'datos_anteriores' => ['activo' => $beforeStatus],
            'datos_nuevos' => ['activo' => $afterStatus],
            'descripcion' => $afterStatus ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function audit(Request $request): Response
    {
        $hasModuloColumn = Schema::hasColumn('audit_log', 'modulo');

        $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'id_usuario' => ['nullable', 'integer', 'exists:usuarios,id_usuario'],
            'modulo' => ['nullable', 'string', 'max:80'],
            'accion' => ['nullable', 'string', 'max:40'],
            'id_contexto' => ['nullable', 'integer', 'exists:contextos_cliente,id_contexto'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $filtros = [
            'search' => trim((string) $request->input('search', '')),
            'id_usuario' => $request->input('id_usuario'),
            'modulo' => trim((string) $request->input('modulo', '')),
            'accion' => trim((string) $request->input('accion', '')),
            'id_contexto' => $request->input('id_contexto'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
        ];

        $query = AuditLog::query()
            ->with(['usuario:id_usuario,nombre,apellidos', 'contexto:id_contexto,nombre']);

        if ($filtros['search'] !== '') {
            $term = '%' . $filtros['search'] . '%';
            $query->where(function ($q) use ($term, $hasModuloColumn) {
                $q->where('accion', 'like', $term)
                    ->orWhere('tabla', 'like', $term)
                    ->orWhere('campo', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhere('valor_anterior', 'like', $term)
                    ->orWhere('valor_nuevo', 'like', $term);

                if ($hasModuloColumn) {
                    $q->orWhere('modulo', 'like', $term);
                }
            });
        }

        if (! empty($filtros['id_usuario'])) {
            $query->where('id_usuario', $filtros['id_usuario']);
        }

        if ($filtros['modulo'] !== '') {
            if ($hasModuloColumn) {
                $query->where(function ($q) use ($filtros) {
                    $q->where('modulo', $filtros['modulo'])
                        ->orWhere(function ($sub) use ($filtros) {
                            $sub->whereNull('modulo')
                                ->where('tabla', $filtros['modulo']);
                        });
                });
            } else {
                $query->where('tabla', $filtros['modulo']);
            }
        }

        if ($filtros['accion'] !== '') {
            $query->where('accion', $filtros['accion']);
        }

        if (! empty($filtros['id_contexto'])) {
            $query->where('id_contexto', $filtros['id_contexto']);
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->where('created_at', '>=', Carbon::parse((string) $filtros['fecha_desde'])->startOfDay());
        }

        if (! empty($filtros['fecha_hasta'])) {
            $query->where('created_at', '<=', Carbon::parse((string) $filtros['fecha_hasta'])->endOfDay());
        }

        $logs = $query->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(function (AuditLog $log) use ($hasModuloColumn) {
                $item = $log->toArray();
                $item['modulo_resuelto'] = $hasModuloColumn ? ($log->modulo ?: $log->tabla) : $log->tabla;
                $item['entity_id_resuelto'] = $log->entity_id ?: $log->registro_id;
                $item['entity_type_resuelto'] = $log->entity_type ?: $item['modulo_resuelto'];

                if (! $log->valor_anterior && $log->campo && is_array($log->datos_anteriores)) {
                    $item['valor_anterior_resuelto'] = $log->datos_anteriores[$log->campo] ?? null;
                } else {
                    $item['valor_anterior_resuelto'] = $log->valor_anterior;
                }

                if (! $log->valor_nuevo && $log->campo && is_array($log->datos_nuevos)) {
                    $item['valor_nuevo_resuelto'] = $log->datos_nuevos[$log->campo] ?? null;
                } else {
                    $item['valor_nuevo_resuelto'] = $log->valor_nuevo;
                }

                return $item;
            });

        $modulosFiltro = $hasModuloColumn
            ? AuditLog::query()
            ->select(['modulo', 'tabla'])
            ->orderBy('tabla')
            ->get()
            ->map(fn(AuditLog $log) => $log->modulo ?: $log->tabla)
            ->filter()
            ->unique()
            ->values()
            : AuditLog::query()
            ->select('tabla')
            ->whereNotNull('tabla')
            ->orderBy('tabla')
            ->distinct()
            ->pluck('tabla')
            ->values();

        $accionesFiltro = collect(AuditLog::ACTIONS)
            ->merge(AuditLog::query()->select('accion')->distinct()->pluck('accion'))
            ->filter()
            ->unique()
            ->values();

        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
            'filtros' => $filtros,
            'returnRoute' => $request->user()?->hasRole('admin') ? 'admin.dashboard' : 'cierre.dashboard',
            'usuariosFiltro' => User::query()
                ->select('id_usuario', 'nombre', 'apellidos')
                ->orderBy('nombre')
                ->limit(200)
                ->get(),
            'modulosFiltro' => $modulosFiltro,
            'accionesFiltro' => $accionesFiltro,
            'contextosFiltro' => ContextoCliente::query()
                ->select('id_contexto', 'nombre', 'codigo')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    private function logAction(Request $request, string $action, string $table, ?int $recordId = null, array $data = []): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'tabla' => $table,
            'modulo' => $data['modulo'] ?? $table,
            'entity_type' => $data['entity_type'] ?? $table,
            'entity_id' => $data['entity_id'] ?? $recordId,
            'registro_id' => $recordId,
            'campo' => $data['campo'] ?? null,
            'valor_anterior' => $data['valor_anterior'] ?? null,
            'valor_nuevo' => $data['valor_nuevo'] ?? null,
            'datos_anteriores' => $data['datos_anteriores'] ?? null,
            'datos_nuevos' => $data['datos_nuevos'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'id_contexto' => $data['id_contexto'] ?? null,
        ], $request);
    }

    private function buildUserAuditSnapshot(User $user): array
    {
        return Arr::only($user->toArray(), [
            'id_usuario',
            'nombre',
            'apellidos',
            'nombre_usuario',
            'email',
            'telefono',
            'id_contexto',
            'activo',
        ]);
    }

    private function resolveFirstChangedField(array $before, array $after): ?string
    {
        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                return $field;
            }
        }

        return null;
    }

    private function buildUpdateDescription(array $before, array $after): string
    {
        $changedFields = [];

        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changedFields[] = $field;
            }
        }

        if ($changedFields === []) {
            return 'Edición de usuario sin cambios persistidos.';
        }

        return 'Edición de usuario. Campos modificados: ' . implode(', ', $changedFields) . '.';
    }

    private function buildRoleCatalog(): array
    {
        return Role::query()
            ->with('permissions:id_permiso,slug,nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_rol', 'nombre', 'slug', 'descripcion'])
            ->map(function (Role $role): array {
                return [
                    'id_rol' => $role->id_rol,
                    'nombre' => $role->nombre,
                    'slug' => $role->slug,
                    'descripcion' => $role->descripcion,
                    'scope_summary' => $this->buildPermissionScopeSummary($role->permissions),
                ];
            })
            ->values()
            ->all();
    }

    private function buildPermissionScopeSummary(Collection $permissions): array
    {
        $indexed = $permissions
            ->map(fn($permission): array => [
                'slug' => $permission->slug,
                'nombre' => $permission->nombre,
            ])
            ->unique('slug')
            ->values();

        return [
            'technical' => $indexed
                ->filter(fn(array $permission): bool => in_array($permission['slug'], self::TECHNICAL_PERMISSION_SLUGS, true))
                ->values()
                ->all(),
            'operational_read' => $indexed
                ->filter(fn(array $permission): bool => in_array($permission['slug'], self::OPERATIONAL_READ_PERMISSION_SLUGS, true))
                ->values()
                ->all(),
            'operational_mutation' => $indexed
                ->filter(fn(array $permission): bool => in_array($permission['slug'], self::OPERATIONAL_MUTATION_PERMISSION_SLUGS, true))
                ->values()
                ->all(),
        ];
    }
}
