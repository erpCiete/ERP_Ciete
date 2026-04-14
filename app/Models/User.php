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
            'ultimo_login_at' => 'datetime',
        ];
    }


    public function getAuthIdentifierName(): string
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

    public function hasRole(string $role): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        if (! $this->exists || $roles === []) {
            return false;
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->permissions()->where('permisos.slug', $permission)->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (! $this->exists || $permissions === []) {
            return false;
        }

        return $this->permissions()->whereIn('permisos.slug', $permissions)->exists();
    }

    public function isAdmin(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->hasRole('admin');
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
