<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoTrabajo extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'tipos_trabajo';

    protected $primaryKey = 'id_tipo_trabajo';

    protected $fillable = [
        'id_contexto',
        'id_tipo_documento',
        'codigo',
        'nombre',
        'responsable_ciete_defecto',
        'responsable_cliente_defecto',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento', 'id_tipo_documento');
    }
}
