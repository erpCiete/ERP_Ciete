<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifarioLinea extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'tarifario_lineas';

    protected $primaryKey = 'id_tarifario_linea';

    protected $fillable = [
        'id_contexto',
        'id_tarifario',
        'codigo_tarifa',
        'grupo',
        'actuacion',
        'descripcion',
        'tarifa_anterior',
        'tarifa_base',
        'tarifa_aplicada',
        'id_unidad',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'tarifa_anterior' => 'decimal:2',
            'tarifa_base' => 'decimal:2',
            'tarifa_aplicada' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function tarifario(): BelongsTo
    {
        return $this->belongsTo(Tarifario::class, 'id_tarifario', 'id_tarifario');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'id_unidad', 'id_unidad');
    }

    public function pedidoItems(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'id_tarifario_linea', 'id_tarifario_linea');
    }
}
