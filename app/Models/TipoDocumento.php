<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'tipos_documento';

    protected $primaryKey = 'id_tipo_documento';

    protected $fillable = [
        'id_contexto',
        'codigo',
        'nombre',
        'tiene_doble_factura',
        'tiene_orden_mto',
        'tiene_num_tarifa',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'tiene_doble_factura' => 'boolean',
            'tiene_orden_mto' => 'boolean',
            'tiene_num_tarifa' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function tiposTrabajo()
    {
        return $this->hasMany(TipoTrabajo::class, 'id_tipo_documento', 'id_tipo_documento');
    }
}
