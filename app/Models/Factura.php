<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'facturas';

    protected $primaryKey = 'id_factura';

    protected $fillable = [
        'id_contexto',
        'id_trabajo',
        'id_empresa_cliente',
        'numero_factura',
        'numero_factura_ccp',
        'serie',
        'orden_factura',
        'fecha_solicitud',
        'fecha_emision',
        'fecha_vencimiento',
        'importe',
        'base_imponible',
        'iva',
        'retencion',
        'total',
        'estado',
        'autofactura',
        'sociedad',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'importe' => 'decimal:2',
            'base_imponible' => 'decimal:2',
            'iva' => 'decimal:2',
            'retencion' => 'decimal:2',
            'total' => 'decimal:2',
            'autofactura' => 'boolean',
        ];
    }

    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'factura_pedidos', 'id_factura', 'id_pedido')
            ->withPivot('importe_aplicado')
            ->withTimestamps();
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(Cobro::class, 'id_factura', 'id_factura');
    }
}
