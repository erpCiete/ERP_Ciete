<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarifario extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'tarifarios';

    protected $primaryKey = 'id_tarifario';

    protected $fillable = [
        'id_contexto',
        'id_contrato',
        'nombre',
        'version',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'factor_multiplicador',
        'moneda',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio_vigencia' => 'date',
            'fecha_fin_vigencia' => 'date',
            'factor_multiplicador' => 'decimal:4',
            'activo' => 'boolean',
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(TarifarioLinea::class, 'id_tarifario', 'id_tarifario');
    }
}
