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
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $table = (string) $builder->getModel()->getTable();

        $contextIds = $user->getActiveContextIds();

        $builder->whereIn("{$table}.id_contexto", $contextIds);
    }
}
