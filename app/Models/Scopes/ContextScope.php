<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ContextScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $contextId = Auth::user()?->id_contexto;

        if ($contextId === null) {
            return;
        }

        $table = (string) $builder->getModel()->getTable();

        $builder->where("{$table}.id_contexto", '=', $contextId);
    }
}
