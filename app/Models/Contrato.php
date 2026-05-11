<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrato extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'contratos';

    protected $primaryKey = 'id_contrato';

    protected $fillable = [
        'id_contexto',
        'id_empresa_cliente',
        'codigo_contrato',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function tarifarios(): HasMany
    {
        return $this->hasMany(Tarifario::class, 'id_contrato', 'id_contrato');
    }

    public function sociedadesFacturadoras(): HasMany
    {
        return $this->hasMany(ContratoEmpresaFacturadora::class, 'id_contrato', 'id_contrato');
    }

    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class, 'id_contrato', 'id_contrato');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_contrato', 'id_contrato');
    }

    /**
     * Empresas/sociedades autorizadas para facturar bajo este contrato.
     */
    public function empresasFacturadoras(): BelongsToMany
    {
        return $this->belongsToMany(
            Empresa::class,
            'contrato_empresas_facturadoras',
            'id_contrato',
            'id_empresa'
        )
            ->withPivot(['id_contexto', 'activo', 'observaciones'])
            ->withTimestamps()
            ->wherePivot('activo', true);
    }
}
