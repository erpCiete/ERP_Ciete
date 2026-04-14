<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $primaryKey = 'id_audit';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_contexto',
        'accion',
        'tabla',
        'registro_id',
        'datos',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function contexto()
    {
        return $this->belongsTo(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }
}
