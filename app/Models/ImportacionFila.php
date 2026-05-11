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
        'archivo_origen',
        'hoja_origen',
        'numero_fila',
        'datos_json',
        'estado',
        'resultado',
        'tipo_fila',
        'severidad',
        'codigo',
        'clasificacion',
        'mensaje_error',
        'decision_sugerida',
        'id_registro_destino',
        'tipo_registro_destino',
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
