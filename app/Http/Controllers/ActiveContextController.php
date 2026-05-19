<?php

namespace App\Http\Controllers;

use App\Models\ContextoCliente;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\ContextGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActiveContextController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contexto' => ['required'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $beforeSelection = $user->getActiveContextSelection();
        $requestedSelection = $validated['contexto'];
        $requestedNormalized = strtolower(trim((string) $requestedSelection));

        if (in_array($requestedNormalized, [User::ACTIVE_CONTEXT_ALL, 'todos', 'todo'], true)) {
            throw ValidationException::withMessages([
                'contexto' => 'Selecciona un contexto operativo real (MOEVE, REPSOL u OTROS CLIENTES).',
            ]);
        }

        if (! $user->canSelectContext($requestedSelection)) {
            throw ValidationException::withMessages([
                'contexto' => 'El contexto seleccionado no está disponible para este usuario.',
            ]);
        }

        $resolvedSelection = $user->setActiveContextSelection($requestedSelection);

        $this->auditLogger->log([
            'user' => $user,
            'accion' => 'cambiar_contexto',
            'modulo' => 'contexto',
            'tabla' => 'usuarios',
            'entity_type' => User::class,
            'entity_id' => $user->id_usuario,
            'registro_id' => $user->id_usuario,
            'campo' => 'id_contexto',
            'valor_anterior' => $this->selectionToScalar($beforeSelection),
            'valor_nuevo' => $this->selectionToScalar($resolvedSelection),
            'datos_anteriores' => ['contexto' => $this->selectionToScalar($beforeSelection)],
            'datos_nuevos' => ['contexto' => $this->selectionToScalar($resolvedSelection)],
            'descripcion' => $this->buildDescription($beforeSelection, $resolvedSelection),
            'id_contexto' => is_int($resolvedSelection) ? $resolvedSelection : null,
        ], $request);

        return back(303);
    }

    private function selectionToScalar(int|string $selection): string|int
    {
        if ($selection === User::ACTIVE_CONTEXT_ALL) {
            return User::ACTIVE_CONTEXT_ALL;
        }

        return (int) $selection;
    }

    private function buildDescription(int|string $before, int|string $after): string
    {
        return sprintf(
            'Cambio de contexto activo: %s -> %s.',
            $this->resolveContextLabel($before),
            $this->resolveContextLabel($after),
        );
    }

    private function resolveContextLabel(int|string $selection): string
    {
        if ($selection === User::ACTIVE_CONTEXT_ALL) {
            return 'TODOS';
        }

        $context = ContextoCliente::query()
            ->select('nombre', 'codigo')
            ->find((int) $selection);

        if (! $context) {
            return '#' . (int) $selection;
        }

        return ContextGuard::displayName($context->codigo, $context->nombre)
            ?? trim(($context->codigo ? "{$context->codigo} " : '') . $context->nombre);
    }
}
