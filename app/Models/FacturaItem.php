<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaItem extends Model
{
    use HasFactory;

    protected $table = 'factura_items';

    protected $primaryKey = 'id_factura_item';

    protected $fillable = [
        'id_factura',
        'id_pedido_item',
        'unidades_facturadas',
        'importe_facturado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'importe_facturado' => 'decimal:2',
            'unidades_facturadas' => 'decimal:3',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }

    public function pedidoItem(): BelongsTo
    {
        return $this->belongsTo(PedidoItem::class, 'id_pedido_item', 'id_pedido_item');
    }
}
