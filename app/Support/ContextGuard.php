<?php

namespace App\Support;

use App\Models\ContextoCliente;
use App\Models\User;

final class ContextGuard
{
    public const CREATE_FROM_ALL_MESSAGE = 'No puedes crear registros desde TODOS. Selecciona primero un contexto real: MOEVE, REPSOL u OTROS CLIENTES.';

    /** @var array<int, string|null> */
    private static array $workspaceKeyCache = [];

    public static function canCreateInActiveContext(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return ! self::isAllSelection($user->getActiveContextSelection());
    }

    public static function activeContextIdForCreate(?User $user): ?int
    {
        if (! self::canCreateInActiveContext($user)) {
            return null;
        }

        $selection = $user->getActiveContextSelection();

        if (is_numeric($selection)) {
            return (int) $selection;
        }

        $activeContextIds = array_map('intval', $user->getActiveContextIds());

        return count($activeContextIds) === 1 ? $activeContextIds[0] : null;
    }

    public static function canOperateContext(?User $user, ?int $contextId): bool
    {
        if (! $user || ! $contextId) {
            return false;
        }

        return in_array((int) $contextId, array_map('intval', $user->getActiveContextIds()), true);
    }

    public static function isAllSelection(mixed $selection): bool
    {
        if ($selection === null) {
            return true;
        }

        $normalized = strtolower(trim((string) $selection));

        return $normalized === ''
            || in_array($normalized, [User::ACTIVE_CONTEXT_ALL, 'todos', 'todo'], true);
    }

    public static function isMoeveContextId(?int $contextId): bool
    {
        return self::workspaceKeyForContextId($contextId) === 'moeve';
    }

    public static function isRepsolContextId(?int $contextId): bool
    {
        return self::workspaceKeyForContextId($contextId) === 'repsol';
    }

    public static function workspaceKeyForContextId(?int $contextId): ?string
    {
        if (! $contextId) {
            return null;
        }

        if (array_key_exists($contextId, self::$workspaceKeyCache)) {
            return self::$workspaceKeyCache[$contextId];
        }

        $context = ContextoCliente::query()
            ->select('codigo', 'nombre')
            ->find($contextId);

        return self::$workspaceKeyCache[$contextId] = $context
            ? self::workspaceKey($context->codigo, $context->nombre)
            : null;
    }

    public static function workspaceKey(?string $code, ?string $name = null): string
    {
        $value = self::normalizeContextText($code . ' ' . $name);

        if (str_contains($value, 'moeve')) {
            return 'moeve';
        }

        if (str_contains($value, 'repsol')) {
            return 'repsol';
        }

        if (str_contains($value, 'todos') || $value === User::ACTIVE_CONTEXT_ALL) {
            return 'todos';
        }

        return 'otros';
    }

    public static function displayName(?string $code, ?string $name = null): ?string
    {
        if (trim((string) $code) === '' && trim((string) $name) === '') {
            return null;
        }

        return match (self::workspaceKey($code, $name)) {
            'moeve' => 'MOEVE',
            'repsol' => 'REPSOL',
            'otros' => 'OTROS CLIENTES',
            'todos' => 'TODOS',
            default => $name ?: $code,
        };
    }

    private static function normalizeContextText(string $value): string
    {
        return strtolower(trim($value));
    }
}
