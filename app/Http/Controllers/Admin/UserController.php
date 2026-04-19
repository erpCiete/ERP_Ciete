<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContextoCliente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->with(['roles:id_rol,slug,nombre', 'contexto:id_contexto,nombre,codigo']);

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

        $users = $query->orderBy('nombre')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filtros' => $request->only(['search', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'roles'     => Role::where('activo', true)->get(['id_rol', 'nombre', 'slug']),
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

        $this->logAction($request, 'crear', 'usuarios', $user->id_usuario);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): Response
    {
        $user->load(['roles:id_rol,slug,nombre', 'contextos:id_contexto,nombre,codigo']);

        return Inertia::render('Admin/Users/Form', [
            'user'      => $user,
            'roles'     => Role::where('activo', true)->get(['id_rol', 'nombre', 'slug']),
            'contextos' => ContextoCliente::where('activo', true)->get(['id_contexto', 'nombre', 'codigo']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
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

        $this->logAction($request, 'actualizar', 'usuarios', $user->id_usuario);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id_usuario === $request->user()->id_usuario) {
            return back()->withErrors(['toggle' => 'No puedes desactivarte a ti mismo.']);
        }

        $user->update(['activo' => ! $user->activo]);

        $action = $user->activo ? 'activar' : 'desactivar';
        $this->logAction($request, $action, 'usuarios', $user->id_usuario);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function audit(Request $request): Response
    {
        $query = AuditLog::query()
            ->with(['usuario:id_usuario,nombre,apellidos', 'contexto:id_contexto,nombre']);

        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('accion', 'like', $term)
                    ->orWhere('tabla', 'like', $term);
            });
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        return Inertia::render('Admin/Audit/Index', [
            'logs'    => $logs,
            'filtros' => $request->only(['search']),
        ]);
    }

    private function logAction(Request $request, string $action, string $table, int $recordId): void
    {
        AuditLog::create([
            'id_usuario'  => $request->user()->id_usuario,
            'id_contexto' => $request->user()->id_contexto,
            'accion'      => $action,
            'tabla'        => $table,
            'registro_id'  => $recordId,
            'ip'           => $request->ip(),
            'created_at'   => now(),
        ]);
    }
}
