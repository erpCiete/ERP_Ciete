<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioSoporte extends Model
{
    protected $table = 'comentarios_soporte';

    protected $primaryKey = 'id_comentario_soporte';

    protected $fillable = [
        'id_solicitud_soporte',
        'id_usuario',
        'tipo_autor',
        'mensaje',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudSoporte::class, 'id_solicitud_soporte', 'id_solicitud_soporte');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
