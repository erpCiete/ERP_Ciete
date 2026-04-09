<?php

namespace App\Traits;

use App\Models\Scopes\ContextScope;
use Illuminate\Database\Eloquent\Model;

trait HasContext
{
    /**
     * Arranca el trait para el modelo, aplicando el Scope Global.
     */
    protected static function bootHasContext(): void
    {
        // 1. Aplica el filtro en consultas (SELECT, UPDATE, DELETE)
        static::addGlobalScope(new ContextScope);

        // 2. Inyecta el id_contexto automáticamente en los INSERTS (Creación)
        static::creating(function (Model $model) {
            if (auth()->check() && auth()->user()->id_contexto && empty($model->id_contexto)) {
                $model->id_contexto = auth()->user()->id_contexto;
            }
        });
    }
}