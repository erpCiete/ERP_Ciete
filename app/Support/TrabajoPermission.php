<?php

namespace App\Support;

use App\Models\Trabajo;
use App\Models\User;

final class TrabajoPermission
{
    public static function canView(?User $user): bool
    {
        return $user?->hasPermission('trabajos.ver') ?? false;
    }

    public static function canCreate(?User $user): bool
    {
        return $user?->hasPermission('trabajos.crear') ?? false;
    }

    public static function canEdit(?User $user, Trabajo $trabajo): bool
    {
        if (self::isClosed($trabajo)) {
            return self::canEditClosed($user);
        }

        return $user?->hasPermission('trabajos.editar') ?? false;
    }

    public static function canEditClosed(?User $user): bool
    {
        return $user?->hasPermission('trabajos.editar_finalizado') ?? false;
    }

    public static function canDelete(?User $user, Trabajo $trabajo): bool
    {
        if (! ($user?->hasPermission('trabajos.eliminar') ?? false)) {
            return false;
        }

        return ! self::isClosed($trabajo) || self::canEditClosed($user);
    }

    public static function canChangeState(?User $user): bool
    {
        return $user?->hasPermission('trabajos.cambiar_estado') ?? false;
    }

    public static function canMarkFinished(?User $user): bool
    {
        return $user?->hasPermission('trabajos.marcar_terminado') ?? false;
    }

    public static function canClose(?User $user): bool
    {
        return $user?->hasPermission('trabajos.finalizar') ?? false;
    }

    public static function isClosed(Trabajo $trabajo): bool
    {
        return $trabajo->isProtectedFinalizedState();
    }
}
