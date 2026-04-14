<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presupuesto extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'presupuestos';

    protected $primaryKey = 'id_presupuesto';

    protected $fillable = [
        'id_contexto',
        'id_trabajo',
        'id_empresa_cliente',
        'id_contacto_empresa_cliente',
        'id_estacion_servicio',
        'id_tarifario',
        'id_usuario_responsable',
        'id_usuario_cierre',
        'codigo_presupuesto',
        'nombre_presupuesto',
        'estado',
        'fecha_emision',
        'fecha_validez',
        'base_imponible',
        'iva',
        'retencion',
        'total',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_validez' => 'date',
            'base_imponible' => 'decimal:2',
            'iva' => 'decimal:2',
            'retencion' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function lineas()
    {
        return $this->hasMany(PresupuestoLinea::class, 'id_presupuesto', 'id_presupuesto');
    }
}
