<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    /**
     * Acciones normalizadas para nuevas escrituras de auditoría.
     *
     * @var list<string>
     */
    public const ACTIONS = [
        'crear',
        'actualizar',
        'eliminar',
        'activar',
        'desactivar',
        'cambiar_estado',
        'importar',
        'exportar',
        'limpiar_logs',
        'cambiar_contexto',
    ];

    protected $table = 'audit_log';

    protected $primaryKey = 'id_audit';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_contexto',
        'accion',
        'tabla',
        'modulo',
        'entity_type',
        'entity_id',
        'registro_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'datos_anteriores',
        'datos_nuevos',
        'descripcion',
        'ip',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'registro_id' => 'integer',
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
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

    public static function normalizeAction(string $action): string
    {
        $normalized = Str::of($action)->lower()->trim()->value();

        return match ($normalized) {
            'editar' => 'actualizar',
            'cerrar', 'reabrir' => 'cambiar_estado',
            default => in_array($normalized, self::ACTIONS, true) ? $normalized : 'actualizar',
        };
    }
}
