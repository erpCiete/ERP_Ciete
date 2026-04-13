<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cobro extends Model
{
    use HasFactory;
    use HasContext;

    protected $table = 'cobros';

    protected $primaryKey = 'id_cobro';

    protected $fillable = [
        'id_contexto',
        'id_factura',
        'id_usuario_registro',
        'fecha_cobro',
        'importe',
        'metodo_cobro',
        'referencia',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cobro' => 'date',
            'importe' => 'decimal:2',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }
}
