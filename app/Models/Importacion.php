<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importacion extends Model
{
    use HasContext;

    protected $table = 'importaciones';

    protected $primaryKey = 'id_importacion';

    protected $fillable = [
        'id_contexto',
        'id_usuario',
        'tipo',
        'archivo_original',
        'total_filas',
        'filas_importadas',
        'filas_ignoradas',
        'filas_con_error',
        'filas_con_aviso',
        'filas_duplicadas',
        'estado',
        'version_importacion',
        'started_at',
        'finished_at',
        'resumen_json',
    ];

    protected function casts(): array
    {
        return [
            'total_filas' => 'integer',
            'filas_importadas' => 'integer',
            'filas_ignoradas' => 'integer',
            'filas_con_error' => 'integer',
            'filas_con_aviso' => 'integer',
            'filas_duplicadas' => 'integer',
            'version_importacion' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'resumen_json' => 'array',
        ];
    }

    public function contexto(): BelongsTo
    {
        return $this->belongsTo(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function filas(): HasMany
    {
        return $this->hasMany(ImportacionFila::class, 'id_importacion', 'id_importacion');
    }
}
