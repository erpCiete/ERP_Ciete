<?php

namespace App\Traits;

use App\Models\Scopes\ContextScope;
use Illuminate\Database\Eloquent\Model;

trait HasContext
{
    protected static function bootHasContext(): void
    {
        static::addGlobalScope(new ContextScope);

        static::creating(function (Model $model): void {
            $contextId = auth()->user()?->id_contexto;

            if ($contextId !== null && empty($model->id_contexto)) {
                $model->id_contexto = $contextId;
            }
        });
    }
}
