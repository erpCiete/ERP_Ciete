<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudSoporte extends Model
{
    public const ESTADOS = ['pending', 'in_review', 'resolved', 'archived'];

    public const PRIORIDADES = ['baja', 'normal', 'alta', 'urgente'];

    protected $table = 'solicitudes_soporte';

    protected $primaryKey = 'id_solicitud_soporte';

    protected $fillable = [
        'id_usuario_solicitante',
        'id_usuario_asignado',
        'tema',
        'asunto',
        'estado',
        'prioridad',
        'ultimo_mensaje_at',
        'resuelta_at',
        'archivada_at',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_mensaje_at' => 'datetime',
            'resuelta_at' => 'datetime',
            'archivada_at' => 'datetime',
        ];
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_solicitante', 'id_usuario');
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_asignado', 'id_usuario');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ComentarioSoporte::class, 'id_solicitud_soporte', 'id_solicitud_soporte');
    }

    public function ultimoComentario(): HasOne
    {
        return $this->hasOne(ComentarioSoporte::class, 'id_solicitud_soporte', 'id_solicitud_soporte')
            ->latestOfMany('id_comentario_soporte');
    }

    public function mensajesInternos(): HasMany
    {
        return $this->hasMany(MensajeInterno::class, 'id_solicitud_soporte', 'id_solicitud_soporte');
    }

    public function scopeVisibleFor(Builder $query, User $user): Builder
    {
        if ($user->canManageSupport()) {
            return $query;
        }

        return $query->where('id_usuario_solicitante', $user->id_usuario);
    }

    public function puedeSerVistaPor(User $user): bool
    {
        return $user->canManageSupport() || $this->id_usuario_solicitante === $user->id_usuario;
    }
}
