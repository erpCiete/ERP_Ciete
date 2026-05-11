<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    /**
     * @var list<string>|null
     */
    private ?array $auditLogColumns = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public function log(array $data, ?Request $request = null): AuditLog
    {
        $user = $data['user'] ?? $request?->user();
        $contextId = $data['id_contexto'] ?? $user?->id_contexto ?? null;
        $action = AuditLog::normalizeAction((string) ($data['accion'] ?? 'actualizar'));

        $ipAddress = $data['ip_address']
            ?? $data['ip']
            ?? $request?->ip();

        $payload = [
            'id_usuario' => $data['id_usuario'] ?? $user?->id_usuario,
            'id_contexto' => $contextId,
            'accion' => $action,
            'tabla' => (string) ($data['tabla'] ?? $data['modulo'] ?? 'desconocido'),
            'modulo' => $data['modulo'] ?? $data['tabla'] ?? null,
            'entity_type' => $data['entity_type'] ?? $data['tabla'] ?? $data['modulo'] ?? null,
            'entity_id' => $data['entity_id'] ?? $data['registro_id'] ?? null,
            'registro_id' => $data['registro_id'] ?? $data['entity_id'] ?? null,
            'campo' => $data['campo'] ?? null,
            'valor_anterior' => $this->serializeValue($data['valor_anterior'] ?? null),
            'valor_nuevo' => $this->serializeValue($data['valor_nuevo'] ?? null),
            'datos_anteriores' => $this->normalizeArrayValue($data['datos_anteriores'] ?? null),
            'datos_nuevos' => $this->normalizeArrayValue($data['datos_nuevos'] ?? null),
            'descripcion' => $data['descripcion'] ?? null,
            'ip' => $ipAddress,
            'ip_address' => $ipAddress,
            'user_agent' => mb_substr((string) ($data['user_agent'] ?? $request?->userAgent()), 0, 500),
            'created_at' => $data['created_at'] ?? now(),
        ];

        return AuditLog::create($this->filterPayloadForCurrentSchema($payload));
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, mixed>
     */
    public function resolveFirstChange(array $before, array $after): array
    {
        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                return [
                    'campo' => $field,
                    'valor_anterior' => $oldValue,
                    'valor_nuevo' => $newValue,
                ];
            }
        }

        return [
            'campo' => null,
            'valor_anterior' => null,
            'valor_nuevo' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public function buildChangedFieldsDescription(array $before, array $after, string $prefix): string
    {
        $changed = [];

        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changed[] = $field;
            }
        }

        if ($changed === []) {
            return $prefix . ' sin cambios persistidos.';
        }

        return $prefix . '. Campos modificados: ' . implode(', ', $changed) . '.';
    }

    /**
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */
    public function snapshotModel(object $model, array $keys): array
    {
        if (method_exists($model, 'toArray')) {
            /** @var array<string, mixed> $values */
            $values = $model->toArray();
            return Arr::only($values, $keys);
        }

        return [];
    }

    private function serializeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeArrayValue(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return ['_value' => $value];
    }

    /**
     * Keep audit writes compatible with environments where incremental columns
     * such as "modulo" have not been migrated yet.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterPayloadForCurrentSchema(array $payload): array
    {
        $availableColumns = $this->auditLogColumns ??= Schema::getColumnListing('audit_log');

        return Arr::only($payload, $availableColumns);
    }
}
