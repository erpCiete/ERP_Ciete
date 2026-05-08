<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $primaryKey = 'id_unidad';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function lineasTarifario(): HasMany
    {
        return $this->hasMany(TarifarioLinea::class, 'id_unidad', 'id_unidad');
    }
}
