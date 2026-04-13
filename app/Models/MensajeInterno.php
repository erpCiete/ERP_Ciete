<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeInterno extends Model
{
    protected $table = 'mensajes_internos';

    protected $primaryKey = 'id_mensaje';

    protected $fillable = [
        'id_remitente',
        'id_destinatario',
        'asunto',
        'cuerpo',
        'prioridad',
        'es_aviso_sistema',
        'leido_at',
        'archivado',
    ];

    protected function casts(): array
    {
        return [
            'es_aviso_sistema' => 'boolean',
            'archivado' => 'boolean',
            'leido_at' => 'datetime',
        ];
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_remitente', 'id_usuario');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinatario', 'id_usuario');
    }
}
