<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContextoCliente;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /** Cached once per request — avoids Schema::hasColumn in every row transform. */
    private bool $hasModulo;

    /**
     * Allowlist estricta de módulos que son datos operativos de negocio.
     * Solo estos aparecen en la vista "Solo actividad operativa".
     */
    private const MODULOS_OPERATIVOS = [
        'trabajos',
        'pedidos',
        'pedido_items',
        'facturas',
        'factura_items',
        'clientes',
        'empresas',
        'estaciones',
        'estaciones_servicio',
        'contratos',
        'tarifarios',
        'tarifario_lineas',
        'tipos_trabajo',
        'cobros',
        'legalizaciones',
        'presupuestos',
        'importacion',
        'importacion_filas',
    ];

    /**
     * Acciones que son datos operativos de negocio.
     * Solo estas aparecen en la vista "Solo actividad operativa".
     */
    private const ACCIONES_OPERATIVAS = [
        'crear',
        'actualizar',
        'eliminar',
        'desactivar',
        'cambiar_estado',
        'importar',
    ];

    /** Campos cosméticos/sistema excluidos en modo operativo. */
    private const CAMPOS_RUIDO = [
        'interface_mode',
        'visual_style',
        'theme',
        'dark_mode',
        'locale',
        'remember_token',
        'password',
        'email_verified_at',
        'last_login_at',
        'last_seen_at',
        'last_login',
        'session',
        'active_context',
        'contexto_activo',
        'id_contexto_activo',
        'id_ultimo_contexto_activo',
    ];

    /** Etiquetas legibles de módulos. */
    private const MODULE_LABELS = [
        'trabajos'             => 'Trabajos',
        'pedidos'              => 'Pedidos',
        'pedido_items'         => 'Líneas de pedido',
        'facturas'             => 'Facturas',
        'factura_items'        => 'Líneas de factura',
        'clientes'             => 'Clientes',
        'empresas'             => 'Empresas',
        'estaciones'           => 'Estaciones',
        'estaciones_servicio'  => 'Estaciones de servicio',
        'cobros'               => 'Cobros',
        'importacion'          => 'Importaciones',
        'importacion_filas'    => 'Filas de importación',
        'legalizaciones'       => 'Legalizaciones',
        'presupuestos'         => 'Presupuestos',
        'contratos'            => 'Contratos',
        'tarifarios'           => 'Tarifarios',
        'tarifario_lineas'     => 'Líneas de tarifario',
        'tipos_trabajo'        => 'Tipos de trabajo',
        'usuarios'             => 'Usuarios',
        'roles'                => 'Roles',
        'permisos'             => 'Permisos',
        'perfil'               => 'Preferencias de usuario',
        'audit_log'            => 'Auditoría',
        'sistema'              => 'Sistema',
        'demo_operativa'       => 'Demo operativa',
    ];

    /** Etiquetas legibles de campos. */
    private const FIELD_LABELS = [
        'interface_mode'       => 'Modo de interfaz',
        'visual_style'         => 'Estilo visual',
        'locale'               => 'Idioma',
        'id_responsable_ciete' => 'Responsable',
        'fecha_terminacion'    => 'Fecha terminación',
        'fecha_encargo'        => 'Fecha encargo',
        'observaciones'        => 'Observaciones',
        'estado'               => 'Estado',
        'nombre'               => 'Nombre',
        'nombre_comercial'     => 'Nombre comercial',
        'descripcion_trabajo'  => 'Descripción',
        'numero_trabajo'       => 'Número de trabajo',
        'importe'              => 'Importe',
        'importe_total'        => 'Importe total',
        'precio_unitario'      => 'Precio unitario',
        'cantidad'             => 'Cantidad',
        'email'                => 'Email',
        'telefono'             => 'Teléfono',
        'direccion'            => 'Dirección',
        'codigo_postal'        => 'Código postal',
        'municipio'            => 'Municipio',
        'provincia'            => 'Provincia',
        'finalizado'           => 'Finalizado',
        'activo'               => 'Activo',
        'is_admin'             => 'Administrador',
        'id_contexto'          => 'Contexto',
        'id_empresa'           => 'Cliente',
        'id_estacion'          => 'Estación',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
        $this->hasModulo = Schema::hasColumn('audit_log', 'modulo');
    }

    public function index(Request $request): Response
    {
        $filtros = $this->parseFiltros($request);
        $query = $this->buildQuery($filtros);

        $logs = $query->latest('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (AuditLog $log) => $this->transformLog($log));

        return Inertia::render('AuditLog/Index', [
            'logs'           => $logs,
            'filtros'        => $filtros,
            'modulosFiltro'  => $this->getModulos(),
            'accionesFiltro' => $this->getAcciones(),
            'contextosFiltro' => ContextoCliente::query()
                ->select('id_contexto', 'nombre', 'codigo')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
            'usuariosFiltro' => User::query()
                ->select('id_usuario', 'nombre', 'apellidos', 'email', 'nombre_usuario')
                ->orderBy('nombre')
                ->limit(200)
                ->get(),
            'canLimpiar' => $request->user()?->hasPermission('auditoria.limpiar') ?? false,
        ]);
    }

    public function exportar(Request $request): StreamedResponse|BinaryFileResponse
    {
        $filtros = $this->parseFiltros($request);
        $formato = $request->input('formato', 'csv');
        $query = $this->buildQuery($filtros);

        $registros = $query->latest('created_at')->get();

        $columns = ['Fecha', 'Usuario', 'Módulo', 'Acción', 'Registro ID', 'Campo', 'Valor anterior', 'Valor nuevo', 'Contexto', 'Descripción', 'IP'];

        $rows = $registros->map(function (AuditLog $log) {
            $transformed = $this->transformLog($log);
            return [
                $log->created_at?->format('Y-m-d H:i:s'),
                isset($log->usuario) ? trim(($log->usuario->nombre ?? '') . ' ' . ($log->usuario->apellidos ?? '')) : null,
                $transformed['modulo_resuelto'],
                $log->accion,
                $transformed['entity_id_resuelto'],
                $log->campo,
                $transformed['valor_anterior_resuelto'],
                $transformed['valor_nuevo_resuelto'],
                $log->contexto?->nombre,
                $log->descripcion,
                $log->ip_address ?? $log->ip,
            ];
        });

        // Log the export action
        $filtrosDesc = collect($filtros)->filter()->map(fn($v, $k) => "$k: $v")->implode(', ');
        $this->auditLogger->log([
            'user'        => $request->user(),
            'accion'      => 'exportar',
            'modulo'      => 'audit_log',
            'tabla'       => 'audit_log',
            'descripcion' => "Exportación de registros de auditoría. Formato: {$formato}. Filtros: {$filtrosDesc}",
            'id_contexto' => $request->user()?->getActiveContextIds()[0] ?? null,
        ], $request);

        if ($formato === 'xlsx') {
            return $this->exportXlsx($columns, $rows->toArray());
        }

        return $this->exportCsv($columns, $rows->toArray());
    }

    public function limpiar(Request $request): RedirectResponse
    {
        $request->validate([
            'fecha_hasta'    => ['required', 'date'],
            'modulo'         => ['nullable', 'string', 'max:80'],
            'confirmar'      => ['required', 'accepted'],
            'con_exportacion' => ['nullable', 'boolean'],
        ]);

        $fechaHasta = Carbon::parse((string) $request->input('fecha_hasta'))->endOfDay();
        $modulo = trim((string) $request->input('modulo', ''));
        $conExportacion = (bool) $request->input('con_exportacion', false);

        $query = AuditLog::query()->where('created_at', '<=', $fechaHasta);
        if ($modulo !== '') {
            $query->where(function ($q) use ($modulo) {
                $q->where('modulo', $modulo)->orWhere(function ($sub) use ($modulo) {
                    $sub->whereNull('modulo')->where('tabla', $modulo);
                });
            });
        }

        $count = $query->count();
        $query->delete();

        $this->auditLogger->log([
            'user'        => $request->user(),
            'accion'      => 'limpiar_logs',
            'modulo'      => 'audit_log',
            'tabla'       => 'audit_log',
            'descripcion' => sprintf(
                'Limpieza de registros de auditoría. Hasta: %s. Módulo: %s. Registros eliminados: %d. Con exportación previa: %s.',
                $fechaHasta->toDateTimeString(),
                $modulo ?: 'todos',
                $count,
                $conExportacion ? 'sí' : 'no'
            ),
            'id_contexto' => $request->user()?->getActiveContextIds()[0] ?? null,
        ], $request);

        return back()->with('success', "Se eliminaron {$count} registros de auditoría.");
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function parseFiltros(Request $request): array
    {
        $request->validate([
            'search'      => ['nullable', 'string', 'max:120'],
            'id_usuario'  => ['nullable', 'integer'],
            'modulo'      => ['nullable', 'string', 'max:80'],
            'accion'      => ['nullable', 'string', 'max:40'],
            'id_contexto' => ['nullable', 'integer'],
            'entity_id'   => ['nullable', 'integer'],
            'campo'       => ['nullable', 'string', 'max:120'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'solo_datos'  => ['nullable', 'string'],
        ]);

        return [
            'search'      => trim((string) $request->input('search', '')),
            'id_usuario'  => $request->input('id_usuario'),
            'modulo'      => trim((string) $request->input('modulo', '')),
            'accion'      => trim((string) $request->input('accion', '')),
            'id_contexto' => $request->input('id_contexto'),
            'entity_id'   => $request->input('entity_id'),
            'campo'       => trim((string) $request->input('campo', '')),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            // true por defecto: oculta exportaciones, limpiezas y cambios de contexto
            'solo_datos'  => filter_var($request->input('solo_datos', 'true'), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /** @param array<string, mixed> $filtros */
    private function buildQuery(array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        $hasModulo = $this->hasModulo;

        $query = AuditLog::query()
            ->with(['usuario:id_usuario,nombre,apellidos,email,nombre_usuario', 'contexto:id_contexto,nombre,codigo']);

        // Vista "Solo actividad operativa": allowlist estricta de módulos + acciones de negocio.
        // NO aparecen: usuarios, roles, permisos, perfil, contexto, audit_log,
        // sistema, exportaciones, limpiezas ni cambios cosméticos.
        if (!empty($filtros['solo_datos'])) {
            $col = $hasModulo ? 'modulo' : 'tabla';

            // Solo módulos operativos (allowlist estricta)
            $query->where(function ($q) use ($col, $hasModulo) {
                $q->whereIn($col, self::MODULOS_OPERATIVOS);
                if ($hasModulo) {
                    // Fallback: modulo nulo pero tabla operativa
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('modulo')
                           ->whereIn('tabla', self::MODULOS_OPERATIVOS);
                    });
                }
            });

            // Solo acciones operativas (allowlist estricta)
            $query->whereIn('accion', self::ACCIONES_OPERATIVAS);

            // Excluir campos cosméticos/sistema
            $query->where(function ($q) {
                $q->whereNull('campo')
                  ->orWhereNotIn('campo', self::CAMPOS_RUIDO);
            });

            // Excluir cambios noop: valor anterior igual a valor nuevo
            $query->where(function ($q) {
                $q->whereNull('valor_anterior')
                  ->orWhereNull('valor_nuevo')
                  ->orWhereColumn('valor_anterior', '!=', 'valor_nuevo');
            });
        }

        if ($filtros['search'] !== '') {
            $term = '%' . $filtros['search'] . '%';
            $query->where(function ($q) use ($term, $hasModulo) {
                $q->where('accion', 'like', $term)
                    ->orWhere('tabla', 'like', $term)
                    ->orWhere('campo', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhere('valor_anterior', 'like', $term)
                    ->orWhere('valor_nuevo', 'like', $term)
                    ->orWhereHas('usuario', function ($userQuery) use ($term) {
                        $userQuery
                            ->where('nombre', 'like', $term)
                            ->orWhere('apellidos', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('nombre_usuario', 'like', $term);
                    });
                if ($hasModulo) {
                    $q->orWhere('modulo', 'like', $term);
                }
            });
        }

        if (!empty($filtros['id_usuario'])) {
            $query->where('id_usuario', $filtros['id_usuario']);
        }

        if ($filtros['modulo'] !== '') {
            if ($hasModulo) {
                $query->where(function ($q) use ($filtros) {
                    $q->where('modulo', $filtros['modulo'])
                        ->orWhere(function ($sub) use ($filtros) {
                            $sub->whereNull('modulo')->where('tabla', $filtros['modulo']);
                        });
                });
            } else {
                $query->where('tabla', $filtros['modulo']);
            }
        }

        if ($filtros['accion'] !== '') {
            $query->where('accion', $filtros['accion']);
        }

        if (!empty($filtros['id_contexto'])) {
            $query->where('id_contexto', $filtros['id_contexto']);
        }

        if (!empty($filtros['entity_id'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('entity_id', $filtros['entity_id'])
                    ->orWhere('registro_id', $filtros['entity_id']);
            });
        }

        if ($filtros['campo'] !== '') {
            $query->where('campo', $filtros['campo']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('created_at', '>=', Carbon::parse((string) $filtros['fecha_desde'])->startOfDay());
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('created_at', '<=', Carbon::parse((string) $filtros['fecha_hasta'])->endOfDay());
        }

        return $query;
    }

    /** @return array<string, mixed> */
    private function transformLog(AuditLog $log): array
    {
        $item = $log->toArray();
        $item['modulo_resuelto'] = $this->hasModulo ? ($log->modulo ?: $log->tabla) : $log->tabla;
        $item['entity_id_resuelto'] = $log->entity_id ?: $log->registro_id;
        $item['entity_type_resuelto'] = $log->entity_type ?: $item['modulo_resuelto'];
        $item['usuario_resuelto'] = $this->resolveUserLabel($log);
        $item['accion_resuelta'] = $this->resolveActionLabel((string) $log->accion);
        $item['cambios_resueltos'] = $this->resolveChangedFields($log);
        $item['resumen_resuelto'] = $this->resolveSummary($log, $item['modulo_resuelto'], $item['entity_id_resuelto'], $item['cambios_resueltos']);
        $item['modulo_label'] = self::MODULE_LABELS[$item['modulo_resuelto'] ?? ''] ?? ucfirst(str_replace('_', ' ', $item['modulo_resuelto'] ?? ''));
        $item['campo_label'] = $log->campo ? (self::FIELD_LABELS[$log->campo] ?? $log->campo) : null;

        if (!$log->valor_anterior && $log->campo && is_array($log->datos_anteriores)) {
            $item['valor_anterior_resuelto'] = $log->datos_anteriores[$log->campo] ?? null;
        } else {
            $item['valor_anterior_resuelto'] = $log->valor_anterior;
        }

        if (!$log->valor_nuevo && $log->campo && is_array($log->datos_nuevos)) {
            $item['valor_nuevo_resuelto'] = $log->datos_nuevos[$log->campo] ?? null;
        } else {
            $item['valor_nuevo_resuelto'] = $log->valor_nuevo;
        }

        return $item;
    }

    private function resolveUserLabel(AuditLog $log): string
    {
        if (! $log->usuario) {
            return 'Sistema';
        }

        $name = trim((string) (($log->usuario->nombre ?? '') . ' ' . ($log->usuario->apellidos ?? '')));

        return $name !== ''
            ? $name . ' · ' . ($log->usuario->email ?? $log->usuario->nombre_usuario ?? 'sin email')
            : (string) ($log->usuario->email ?? $log->usuario->nombre_usuario ?? 'Usuario #' . $log->id_usuario);
    }

    private function resolveActionLabel(string $action): string
    {
        return match ($action) {
            'crear'            => 'Creó',
            'actualizar'       => 'Editó',
            'eliminar'         => 'Eliminó',
            'activar'          => 'Activó',
            'desactivar'       => 'Desactivó',
            'cambiar_estado'   => 'Cambió estado',
            'importar'         => 'Importó',
            'exportar'         => 'Exportó',
            'limpiar_logs'     => 'Limpió logs',
            'cambiar_contexto' => 'Cambió contexto',
            default            => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    /**
     * @return list<array{campo:string, anterior:mixed, nuevo:mixed}>
     */
    private function resolveChangedFields(AuditLog $log): array
    {
        $before = is_array($log->datos_anteriores) ? $log->datos_anteriores : [];
        $after = is_array($log->datos_nuevos) ? $log->datos_nuevos : [];

        if ($before === [] && $after === [] && $log->campo) {
            return [[
                'campo'       => $log->campo,
                'campo_label' => self::FIELD_LABELS[$log->campo] ?? $log->campo,
                'anterior'    => $log->valor_anterior,
                'nuevo'       => $log->valor_nuevo,
            ]];
        }

        $fields = array_values(array_unique([...array_keys($before), ...array_keys($after)]));
        $changes = [];

        foreach ($fields as $field) {
            $oldValue = $before[$field] ?? null;
            $newValue = $after[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'campo'       => (string) $field,
                    'campo_label' => self::FIELD_LABELS[$field] ?? $field,
                    'anterior'    => $oldValue,
                    'nuevo'       => $newValue,
                ];
            }
        }

        if ($changes === [] && $log->campo) {
            $changes[] = [
                'campo'       => $log->campo,
                'campo_label' => self::FIELD_LABELS[$log->campo] ?? $log->campo,
                'anterior'    => $log->valor_anterior,
                'nuevo'       => $log->valor_nuevo,
            ];
        }

        return $changes;
    }

    /**
     * @param list<array{campo:string, anterior:mixed, nuevo:mixed}> $changes
     */
    private function resolveSummary(AuditLog $log, ?string $module, mixed $entityId, array $changes): string
    {
        $action = $this->resolveActionLabel((string) $log->accion);
        $rawTarget = $module ?: $log->tabla ?: 'registro';
        $target = trim(self::MODULE_LABELS[$rawTarget] ?? ucfirst(str_replace('_', ' ', $rawTarget)));
        $id = $entityId ? " #{$entityId}" : '';
        $changed = count($changes);

        if ($changed > 1) {
            return "{$action} {$target}{$id}. {$changed} campos afectados.";
        }

        if ($changed === 1) {
            return "{$action} {$target}{$id}. Campo: {$changes[0]['campo']}.";
        }

        return "{$action} {$target}{$id}.";
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function getModulos(): \Illuminate\Support\Collection
    {
        if ($this->hasModulo) {
            return AuditLog::query()
                ->select(['modulo', 'tabla'])
                ->orderBy('tabla')
                ->get()
                ->map(fn (AuditLog $log) => $log->modulo ?: $log->tabla)
                ->filter()
                ->unique()
                ->values();
        }

        return AuditLog::query()
            ->select('tabla')
            ->whereNotNull('tabla')
            ->orderBy('tabla')
            ->distinct()
            ->pluck('tabla')
            ->values();
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function getAcciones(): \Illuminate\Support\Collection
    {
        return collect(AuditLog::ACTIONS)
            ->merge(AuditLog::query()->select('accion')->distinct()->pluck('accion'))
            ->filter()
            ->unique()
            ->values();
    }

    /** @param list<string> $columns @param list<list<mixed>> $rows */
    private function exportCsv(array $columns, array $rows): StreamedResponse
    {
        $filename = 'auditoria_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @param list<string> $columns @param list<list<mixed>> $rows */
    private function exportXlsx(array $columns, array $rows): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$columns], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $tmpFile = tempnam(sys_get_temp_dir(), 'audit_') . '.xlsx';
        (new XlsxWriter($spreadsheet))->save($tmpFile);

        $filename = 'auditoria_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }
}
