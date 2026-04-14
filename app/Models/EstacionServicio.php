<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EstacionServicio extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'estaciones_servicio';

    protected $primaryKey = 'id_estacion_servicio';

    protected $fillable = [
        'id_contexto',
        'id_empresa_cliente',
        'codigo_estacion',
        'nombre',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'pais',
        'latitud_wgs84',
        'longitud_wgs84',
        'estado',
        'f_baja',
        'observaciones',
        'activo',
    ];

    protected $attributes = [
        'pais' => 'Espana',
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'latitud_wgs84' => 'decimal:8',
            'longitud_wgs84' => 'decimal:8',
            'f_baja' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function moeveExt(): HasOne
    {
        return $this->hasOne(EstacionMoeveExt::class, 'id_estacion_servicio', 'id_estacion_servicio');
    }

    public function repsolExt(): HasOne
    {
        return $this->hasOne(EstacionRepsolExt::class, 'id_estacion_servicio', 'id_estacion_servicio');
    }
}
