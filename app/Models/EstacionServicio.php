<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstacionServicio extends Model
{
    use HasFactory, HasContext;

    protected $table = 'estaciones_servicio';
    protected $primaryKey = 'id_estacion_servicio';

    protected $fillable = [
        'id_contexto',
        'id_empresa_cliente',
        'nombre',
        'codigo_estacion_interno',
        'cod_repsol',
        'cod_cepsa',
        'concesion',
        'tipo',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'pais',
        'latitud_wgs84',
        'longitud_wgs84',
        'delegacion',
        'delegado',
        'tecnico_gestion',
        'telefono_tecnico_gestion',
        'email_tecnico_gestion',
        'responsable_es_gestor',
        'telefono_movil',
        'telefono_oficina',
        'sede',
        'tipo_mantenimiento',
        'f_baja',
        'razon_modificacion',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'latitud_wgs84' => 'decimal:8',
            'longitud_wgs84' => 'decimal:8',
            'activo' => 'boolean',
            'f_baja' => 'date',
        ];
    }

    /**
     * Relación con la Empresa Cliente (Repsol o Cepsa).
     * Asegura que el modelo Empresa existe o ajústalo al nombre real de tu modelo.
     */
    public function empresaCliente(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }
}