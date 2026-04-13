<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Importacion extends Model
{
    use HasContext;

    protected $table = 'importaciones';

    protected $primaryKey = 'id_importacion';

    protected $fillable = [
        'id_contexto',
        'id_usuario',
        'tipo',
        'nombre_archivo',
        'estado',
        'total_filas',
        'filas_ok',
        'filas_error',
        'resultado_json',
    ];

    protected function casts(): array
    {
        return [
            'resultado_json' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function filas()
    {
        return $this->hasMany(ImportacionFila::class, 'id_importacion', 'id_importacion');
    }
}
