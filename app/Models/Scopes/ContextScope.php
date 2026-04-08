<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ContextScope implements Scope
{
    /**
     * Aplica el scope a una consulta Eloquent builder dada.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Verificamos si hay un usuario autenticado.
        // 2. Verificamos que el usuario tenga un id_contexto asignado en su sesión/perfil.
        // Nota: Si el usuario es un "Super Admin" (ej. César), se podría saltar esta regla aquí.
        if (auth()->check() && auth()->user()->id_contexto) {
            
            // Especificamos la tabla para evitar ambigüedades en consultas con JOINs
            $builder->where($model->getTable() . '.id_contexto', auth()->user()->id_contexto);
            
        }
    }
}