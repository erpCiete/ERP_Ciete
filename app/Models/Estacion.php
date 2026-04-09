<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacion extends Model
{
    use HasFactory;

    // ESTA LÍNEA ES OBLIGATORIA para que Laravel no busque "estacions"
    protected $table = 'estaciones'; 

 protected $fillable = [
    'nombre',
    'codigo',
    'direccion',
    'cliente_id',
];
}