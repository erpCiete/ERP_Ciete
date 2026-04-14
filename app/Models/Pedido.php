<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'pedidos';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'id_contexto',
        'id_trabajo',
        'id_tarifario',
        'numero_pedido',
        'fecha_solicitud',
        'fecha_recepcion',
        'importe_pedido',
        'importe_solicitado',
        'importe_facturado',
        'unidades_pedido',
        'unidades_solicitadas',
        'estado',
        'pedido_completo',
        'tiene_mas_de_1_item',
        'facturado_completo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_recepcion' => 'date',
            'importe_pedido' => 'decimal:2',
            'importe_solicitado' => 'decimal:2',
            'importe_facturado' => 'decimal:2',
            'unidades_pedido' => 'decimal:3',
            'unidades_solicitadas' => 'decimal:3',
            'pedido_completo' => 'boolean',
            'tiene_mas_de_1_item' => 'boolean',
            'facturado_completo' => 'boolean',
        ];
    }

    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'id_pedido', 'id_pedido');
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'factura_pedidos', 'id_pedido', 'id_factura')
            ->withPivot('importe_aplicado')
            ->withTimestamps();
    }
}
