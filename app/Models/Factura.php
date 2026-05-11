<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    use HasFactory;
    use HasContext;

    public const ESTADOS_FUNCIONALES = [
        'pendiente',
        'solicitada',
        'emitida',
        'enviada',
        'anulada',
    ];

    protected $table = 'facturas';

    protected $primaryKey = 'id_factura';

    protected $fillable = [
        'id_contexto',
        'id_trabajo',
        'id_contrato',
        'id_empresa_facturadora',
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

    /**
     * id_trabajo es cabecera auxiliar/derivada para filtros y lectura rapida.
     * La relacion funcional principal de facturacion vive en items().
     */
    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function contexto(): BelongsTo
    {
        return $this->belongsTo(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function empresaFacturadora(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_facturadora', 'id_empresa');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FacturaItem::class, 'id_factura', 'id_factura');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function cobros(): HasMany
    {
        return $this->hasMany(Cobro::class, 'id_factura', 'id_factura');
    }

    public function scopeWithSociedadCifValidada(Builder $query): Builder
    {
        return $query->addSelect([
            'sociedad_cif_validada_preload' => ContratoEmpresaFacturadora::query()
                ->selectRaw('1')
                ->whereColumn('contrato_empresas_facturadoras.id_contrato', 'facturas.id_contrato')
                ->whereColumn('contrato_empresas_facturadoras.id_empresa', 'facturas.id_empresa_facturadora')
                ->whereColumn('contrato_empresas_facturadoras.id_contexto', 'facturas.id_contexto')
                ->where('contrato_empresas_facturadoras.activo', true)
                ->limit(1),
        ]);
    }
}
