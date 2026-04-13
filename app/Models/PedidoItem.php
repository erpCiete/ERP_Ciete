<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'pedido_items';

    protected $primaryKey = 'id_pedido_item';

    protected $fillable = [
        'id_contexto',
        'id_pedido',
        'id_tarifario_linea',
        'codigo_servicio',
        'numero_tarifa',
        'descripcion_servicio',
        'precio_unitario',
        'cantidad',
        'total_linea',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'cantidad' => 'decimal:3',
            'total_linea' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function tarifarioLinea(): BelongsTo
    {
        return $this->belongsTo(TarifarioLinea::class, 'id_tarifario_linea', 'id_tarifario_linea');
    }
}
