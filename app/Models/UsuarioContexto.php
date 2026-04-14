<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioContexto extends Model
{
    protected $table = 'usuario_contextos';

    protected $primaryKey = 'id_usuario_contexto';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_contexto',
        'es_contexto_principal',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'es_contexto_principal' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function contexto(): BelongsTo
    {
        return $this->belongsTo(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }
}
