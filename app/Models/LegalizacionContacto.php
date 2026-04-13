<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalizacionContacto extends Model
{
    use HasContext;

    protected $table = 'legalizaciones_contactos';

    protected $primaryKey = 'id_legalizacion_contacto';

    protected $fillable = [
        'id_contexto',
        'id_legalizacion',
        'id_contacto_empresa',
        'rol_en_legalizacion',
        'principal',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
        ];
    }

    public function legalizacion(): BelongsTo
    {
        return $this->belongsTo(Legalizacion::class, 'id_legalizacion', 'id_legalizacion');
    }
}
