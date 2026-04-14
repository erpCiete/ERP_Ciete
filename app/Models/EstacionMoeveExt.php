<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstacionMoeveExt extends Model
{
    protected $table = 'estaciones_moeve_ext';

    protected $primaryKey = 'id_estacion_servicio';

    public $incrementing = false;

    protected $fillable = [
        'id_estacion_servicio',
        'n_margenes',
        'tecnico_gestion',
        'telefono_tecnico',
        'email_tecnico',
        'responsable_gestor',
        'telefono_gestor',
        'telefono_oficina',
        'sede_email',
        'vinculo_1',
        'vinculo_2',
        'f_alta_modificacion',
        'cod_retailgas',
        'cod_sociedad',
        'sociedad',
    ];

    protected function casts(): array
    {
        return [
            'f_alta_modificacion' => 'date',
        ];
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(EstacionServicio::class, 'id_estacion_servicio', 'id_estacion_servicio');
    }
}
