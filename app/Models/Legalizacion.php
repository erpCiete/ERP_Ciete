<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Legalizacion extends Model
{
    use HasContext;

    protected $table = 'legalizaciones';

    protected $primaryKey = 'id_legalizacion';

    protected $fillable = [
        'id_contexto',
        'id_trabajo',
        'id_usuario_responsable',
        'tipo_legalizacion',
        'numero_expediente',
        'organismo',
        'estado',
        'descripcion_seleccionable',
        'descripcion_libre',
        'fecha_inicio',
        'fecha_limite',
        'fecha_resolucion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_limite' => 'date',
            'fecha_resolucion' => 'date',
        ];
    }

    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_responsable', 'id_usuario');
    }

    public function contactos()
    {
        return $this->hasMany(LegalizacionContacto::class, 'id_legalizacion', 'id_legalizacion');
    }

    public function comentarios()
    {
        return $this->hasMany(ComentarioLegalizacion::class, 'id_legalizacion', 'id_legalizacion');
    }
}
