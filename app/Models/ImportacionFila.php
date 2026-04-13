<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacionFila extends Model
{
    protected $table = 'importacion_filas';

    protected $primaryKey = 'id_importacion_fila';

    protected $fillable = [
        'id_importacion',
        'fila_numero',
        'datos_json',
        'estado',
        'error_mensaje',
    ];

    protected function casts(): array
    {
        return [
            'datos_json' => 'array',
        ];
    }

    public function importacion(): BelongsTo
    {
        return $this->belongsTo(Importacion::class, 'id_importacion', 'id_importacion');
    }
}
