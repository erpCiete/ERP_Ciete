<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioLegalizacion extends Model
{
    use HasContext;

    protected $table = 'comentarios_legalizaciones';

    protected $primaryKey = 'id_comentario_legalizacion';

    protected $fillable = [
        'id_contexto',
        'id_legalizacion',
        'id_usuario',
        'fecha_comentario',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_comentario' => 'datetime',
        ];
    }

    public function legalizacion(): BelongsTo
    {
        return $this->belongsTo(Legalizacion::class, 'id_legalizacion', 'id_legalizacion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
