<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoLinea extends Model
{
    use HasContext;

    protected $table = 'presupuesto_lineas';

    protected $primaryKey = 'id_linea_presupuesto';

    protected $fillable = [
        'id_contexto',
        'id_presupuesto',
        'id_tarifario_linea',
        'orden',
        'concepto_seleccionable',
        'concepto_libre',
        'cantidad',
        'precio_unitario',
        'iva_porcentaje',
        'total_linea',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'precio_unitario' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'total_linea' => 'decimal:2',
        ];
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class, 'id_presupuesto', 'id_presupuesto');
    }

    public function tarifarioLinea(): BelongsTo
    {
        return $this->belongsTo(TarifarioLinea::class, 'id_tarifario_linea', 'id_tarifario_linea');
    }
}
