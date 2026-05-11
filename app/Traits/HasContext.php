<?php

namespace App\Traits;

use App\Models\Scopes\ContextScope;
use App\Support\ContextGuard;
use Illuminate\Database\Eloquent\Model;

trait HasContext
{
    protected static function bootHasContext(): void
    {
        static::addGlobalScope(new ContextScope);

        static::creating(function (Model $model): void {
            $user = auth()->user();
            $contextId = ContextGuard::activeContextIdForCreate($user);

            if ($contextId !== null && empty($model->id_contexto)) {
                $model->id_contexto = $contextId;
            }
        });
    }
}
