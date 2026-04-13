<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstacionRepsolExt extends Model
{
    protected $table = 'estaciones_repsol_ext';

    protected $primaryKey = 'id_estacion_servicio';

    public $incrementing = false;

    protected $fillable = [
        'id_estacion_servicio',
        'codigo_solred',
        'litros_21',
        'gnas_95_21',
        'gnas_98_21',
        'gasoleo_a_21',
        'eplus10_21',
        'glp_21',
        'adblue_21',
        'cliente_nombre',
        'nom_encargado',
        'nom_gerente',
        'tfno_instalacion',
        'fax_instalacion',
        'tfno_movil_gerente',
        'tfno_movil_encargado',
        'margen',
        'provincial',
    ];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(EstacionServicio::class, 'id_estacion_servicio', 'id_estacion_servicio');
    }
}
