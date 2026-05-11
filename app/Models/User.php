<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_contexto',
        'id_contacto_empresa',
        'nombre',
        'apellidos',
        'nombre_usuario',
        'email',
        'email_recuperacion',
        'email_verificado_at',
        'password',
        'telefono',
        'avatar_key',
        'remember_token',
        'activo',
        'interface_mode',
        'ultimo_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to model arrays.
     *
     * @var list<string>
     */
    protected $appends = [
        'is_admin',
        'role_slugs',
        'permission_slugs',
    ];

    public const ACTIVE_CONTEXT_SESSION_KEY = 'ciete.active_context';

    public const ACTIVE_CONTEXT_ALL = 'all';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verificado_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'interface_mode' => 'string',
            'ultimo_login_at' => 'datetime',
        ];
    }


    public function getAuthIdentifierName(): string
    {
        return 'id_usuario';
    }

    public function getRouteKeyName(): string
    {
        return 'id_usuario';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->getAttribute('id_usuario');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'usuario_roles',
            'id_usuario',
            'id_rol'
        );
    }


    public function contexto(): HasOne
    {
        return $this->hasOne(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }

    public function contextos(): BelongsToMany
    {
        return $this->belongsToMany(
            ContextoCliente::class,
            'usuario_contextos',
            'id_usuario',
            'id_contexto'
        )->withPivot('es_contexto_principal', 'activo');
    }


    public function permissions()
    {
        return Permission::query()
            ->join('rol_permisos', 'permisos.id_permiso', '=', 'rol_permisos.id_permiso')
            ->join('usuario_roles', 'rol_permisos.id_rol', '=', 'usuario_roles.id_rol')
            ->where('usuario_roles.id_usuario', $this->id_usuario)
            ->select('permisos.*')
            ->distinct();
    }

    /** @var array<string, bool>|null In-memory cache: permission slug → true */
    private ?array $cachedPermissionSet = null;

    /** @var array<string, bool>|null In-memory cache: role slug → true */
    private ?array $cachedRoleSet = null;

    private function resolvePermissionSet(): array
    {
        if ($this->cachedPermissionSet === null) {
            $slugs = $this->permissions()->pluck('permisos.slug')->all();
            $this->cachedPermissionSet = array_fill_keys($slugs, true);
        }

        return $this->cachedPermissionSet;
    }

    private function resolveRoleSet(): array
    {
        if ($this->cachedRoleSet === null) {
            $slugs = $this->roles()->pluck('slug')->all();
            $this->cachedRoleSet = array_fill_keys($slugs, true);
        }

        return $this->cachedRoleSet;
    }

    public function hasRole(string $role): bool
    {
        if (! $this->exists) {
            return false;
        }

        return isset($this->resolveRoleSet()[$role]);
    }

    public function hasAnyRole(array $roles): bool
    {
        if (! $this->exists || $roles === []) {
            return false;
        }

        $set = $this->resolveRoleSet();
        foreach ($roles as $role) {
            if (isset($set[$role])) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->exists) {
            return false;
        }

        $set = $this->resolvePermissionSet();

        if (isset($set[$permission])) {
            return true;
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (! $this->exists || $permissions === []) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('admin');
    }

    public function isDirector(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('director');
    }

    public function canAccessDirectionPanel(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasAnyRole(['admin', 'director']);
    }

    public function isExecution(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('ejecucion');
    }

    public function isExecutionMoeve(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('ejecucion_moeve');
    }

    public function isExecutionRepsol(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('ejecucion_repsol');
    }

    public function isAccounting(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('contable');
    }

    public function canManageSupport(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->isAdmin() || $this->hasPermission('soporte.gestionar');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    public function getRoleSlugsAttribute(): array
    {
        return $this->roles()
            ->pluck('slug')
            ->map(fn(string $slug): string => $slug)
            ->values()
            ->all();
    }

    public function getPermissionSlugsAttribute(): array
    {
        return $this->permissions()
            ->pluck('permisos.slug')
            ->map(fn(string $slug): string => $slug)
            ->values()
            ->all();
    }

    public function getAccessibleContextIds(): array
    {
        $ids = $this->contextos()
            ->wherePivot('activo', true)
            ->pluck('contextos_cliente.id_contexto')
            ->all();

        return $ids !== [] ? $ids : [$this->id_contexto];
    }

    public function getDefaultContextId(): int
    {
        $principal = $this->contextos()
            ->wherePivot('activo', true)
            ->wherePivot('es_contexto_principal', true)
            ->value('contextos_cliente.id_contexto');

        return (int) ($principal ?? $this->id_contexto);
    }

    public function getActiveContextSelection(): int|string
    {
        $accessibleIds = array_map('intval', $this->getAccessibleContextIds());
        $stored = $this->readActiveContextFromSession();

        if ($stored === self::ACTIVE_CONTEXT_ALL && count($accessibleIds) > 1) {
            return self::ACTIVE_CONTEXT_ALL;
        }

        if (is_int($stored) && in_array($stored, $accessibleIds, true)) {
            return $stored;
        }

        return $this->getDefaultContextId();
    }

    /**
     * @return array<int, int>
     */
    public function getActiveContextIds(): array
    {
        $selection = $this->getActiveContextSelection();

        if ($selection === self::ACTIVE_CONTEXT_ALL) {
            return array_map('intval', $this->getAccessibleContextIds());
        }

        return [(int) $selection];
    }

    public function canSelectContext(int|string $selection): bool
    {
        $normalizedSelection = $this->normalizeContextSelection($selection);
        $accessibleIds = array_map('intval', $this->getAccessibleContextIds());

        if ($normalizedSelection === self::ACTIVE_CONTEXT_ALL) {
            return count($accessibleIds) > 1;
        }

        return is_int($normalizedSelection) && in_array($normalizedSelection, $accessibleIds, true);
    }

    public function setActiveContextSelection(int|string $selection): int|string
    {
        $normalizedSelection = $this->normalizeContextSelection($selection);

        if (! $this->canSelectContext($normalizedSelection)) {
            $normalizedSelection = $this->getDefaultContextId();
        }

        if (app()->bound('session')) {
            session([self::ACTIVE_CONTEXT_SESSION_KEY => $normalizedSelection]);
        }

        return $normalizedSelection;
    }

    public function clearActiveContextSelection(): void
    {
        if (app()->bound('session')) {
            session()->forget(self::ACTIVE_CONTEXT_SESSION_KEY);
        }
    }

    private function readActiveContextFromSession(): int|string|null
    {
        if (! app()->bound('session')) {
            return null;
        }

        return $this->normalizeContextSelection(session(self::ACTIVE_CONTEXT_SESSION_KEY));
    }

    private function normalizeContextSelection(mixed $selection): int|string|null
    {
        if (is_string($selection)) {
            $trimmed = strtolower(trim($selection));

            if ($trimmed === self::ACTIVE_CONTEXT_ALL) {
                return self::ACTIVE_CONTEXT_ALL;
            }

            if ($trimmed !== '' && ctype_digit($trimmed)) {
                return (int) $trimmed;
            }
        }

        if (is_int($selection)) {
            return $selection;
        }

        return null;
    }

    /**
     * Send the password reset notification in Spanish.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Route notifications to the recovery email if set, otherwise the main email.
     */
    public function routeNotificationForMail($notification = null): string
    {
        return $this->email_recuperacion ?: $this->email;
    }

    public function mensajesRecibidos(): HasMany
    {
        return $this->hasMany(MensajeInterno::class, 'id_destinatario', 'id_usuario');
    }

    public function mensajesEnviados(): HasMany
    {
        return $this->hasMany(MensajeInterno::class, 'id_remitente', 'id_usuario');
    }
}
