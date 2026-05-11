<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'empresas';

    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'id_contexto',
        'empresa_padre_id',
        'nombre',
        'nombre_comercial',
        'razon_social',
        'cif',
        'tipo_empresa',
        'web',
        'observaciones',
        'activo',
    ];

    protected $attributes = [
        'tipo_empresa' => 'cliente',
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function estaciones(): HasMany
    {
        return $this->hasMany(EstacionServicio::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function scopeClientes(Builder $query): Builder
    {
        return $query->whereIn('tipo_empresa', ['cliente', 'cliente_proveedor']);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Contratos para los que esta empresa está autorizada como facturadora.
     */
    public function contratosPermitidos(): BelongsToMany
    {
        return $this->belongsToMany(
            Contrato::class,
            'contrato_empresas_facturadoras',
            'id_empresa',
            'id_contrato'
        )
            ->withPivot(['id_contexto', 'activo', 'observaciones'])
            ->withTimestamps()
            ->wherePivot('activo', true);
    }
}
