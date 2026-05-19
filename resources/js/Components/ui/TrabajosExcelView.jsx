import Modal from '@/Components/Modal';
import WorkspaceContextIndicator from '@/Components/WorkspaceContextIndicator';
import BadgeTrabajo from '@/Components/ui/BadgeTrabajo';
import PaginationControls from '@/Components/ui/PaginationControls';
import { useOptimisticField } from '@/Hooks/useOptimisticField';
import { router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useMemo, useRef, useState } from 'react';

const ESTADO_OPTIONS = [
    { value: 'en_curso', label: 'En curso' },
    { value: 'terminado', label: 'Terminado por ejecución' },
    { value: 'pendiente_facturar', label: 'Pendiente de facturar' },
    { value: 'facturado', label: 'Facturado' },
    { value: 'finalizado', label: 'Finalizado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const ESTADO_LABEL = Object.fromEntries(ESTADO_OPTIONS.map((option) => [option.value, option.label]));
const ESTADO_ORDER = Object.fromEntries(ESTADO_OPTIONS.map((option, index) => [option.value, index]));

const FIELD_LABELS = {
    estado: 'Estado',
    descripcion: 'Descripcion',
    descripcion_trabajo: 'Descripción',
    fecha_terminacion: 'Fecha terminación',
    id_responsable_ciete: 'Responsable',
    observaciones: 'Observaciones',
    numero_trabajo: 'Nº trabajo',
    numero_trabajo_operativo: 'Nº trabajo CIETE',
    codigo_estacion: 'Código estación',
    nombre_estacion: 'Nombre estación',
    municipio: 'Municipio',
    provincia: 'Provincia',
    categoria: 'Categoría',
    id_pedido_principal: 'Nº pedido',
    id_tipo_documento: 'Tipo documental',
    id_tipo_trabajo: 'Tipo de trabajo',
};

const TABLE_COLUMNS = [
    { label: 'Nº CIETE / interno', width: 'w-44' },
    { label: 'Código estación', width: 'w-32' },
    { label: 'Nombre estación', width: 'w-56' },
    { label: 'Municipio', width: 'w-36' },
    { label: 'Provincia', width: 'w-32' },
    { label: 'Tipo / categoría de trabajo', width: 'w-48' },
    { label: 'Descripción', width: 'w-72' },
    { label: 'Nº pedido', width: 'w-40' },
    { label: 'Contrato / tarifa', width: 'w-56' },
    { label: 'Importe pedido', width: 'w-32 text-right' },
    { label: 'Importe solicitado', width: 'w-36 text-right' },
    { label: 'Importe facturado', width: 'w-36 text-right' },
    { label: 'Estado', width: 'w-48' },
    { label: 'Responsable', width: 'w-48' },
    { label: 'Fecha encargo', width: 'w-32' },
    { label: 'Fecha solicitud pedido', width: 'w-40' },
    { label: 'Fecha terminación', width: 'w-36' },
    { label: 'Observaciones', width: 'w-64' },
    { label: 'Acciones', width: 'w-32' },
];

const TODAY = new Date().toISOString().slice(0, 10);

function normalizeContextCode(context) {
    return String(context?.codigo ?? context?.workspace_key ?? context?.nombre ?? '').trim().toLowerCase();
}

function isMoeveContext(context) {
    return Number(context?.id_contexto) === 1 || normalizeContextCode(context).includes('moeve');
}

function isRepsolContext(context) {
    return Number(context?.id_contexto) === 2 || normalizeContextCode(context).includes('repsol');
}

function emptyNewTrabajo(activeContext) {
    return {
        __isNew: true,
        __tempId: `tmp-${Date.now()}`,
        id_contexto: activeContext?.id_contexto ?? '',
        estado: 'en_curso',
        numero_trabajo: '',
        numero_trabajo_operativo: '',
        id_estacion_servicio: '',
        descripcion_trabajo: '',
        categoria: '',
        id_contrato: '',
        id_tipo_documento: '',
        id_tipo_trabajo: '',
        id_responsable_ciete: '',
        fecha_encargo: TODAY,
        fecha_terminacion: '',
        observaciones: '',
    };
}

function fmt(value) {
    if (value === null || value === undefined || value === '') return '—';
    return value;
}

function fmtMoney(value) {
    if (value === null || value === undefined || value === '') return '—';
    const amount = Number(value);
    if (Number.isNaN(amount)) return '—';
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

function toDateInput(value) {
    if (!value) return '';
    const asString = String(value);
    if (/^\d{4}-\d{2}-\d{2}/.test(asString)) return asString.slice(0, 10);
    const date = new Date(asString);
    if (Number.isNaN(date.getTime())) return '';
    return date.toISOString().slice(0, 10);
}

function fmtDate(value) {
    const input = toDateInput(value);
    if (!input) return '—';
    return new Intl.DateTimeFormat('es-ES').format(new Date(`${input}T00:00:00`));
}

function formatWorkNumber(value) {
    if (value === null || value === undefined || value === '') return '—';
    const asString = String(value);
    return /^\d+$/.test(asString) ? asString.padStart(4, '0') : asString;
}

function workNumberForDisplay(trabajo) {
    return trabajo?.numero_trabajo_visible
        ?? trabajo?.numero_trabajo_operativo
        ?? trabajo?.numero_trabajo
        ?? null;
}

function contractTariff(trabajo) {
    const parts = [trabajo.nombre_contrato, trabajo.nombre_tarifa].filter(Boolean);
    return parts.length ? parts.join(' / ') : null;
}

function responsableName(responsables, value) {
    if (!value) return null;
    return responsables.find((responsable) => String(responsable.id) === String(value))?.nombre ?? null;
}

function pedidoLabel(pedido) {
    if (!pedido) return null;

    const parts = [
        pedido.numero,
        pedido.id_trabajo ? `Trabajo ${pedido.id_trabajo}` : null,
        pedido.fecha_solicitud,
    ].filter(Boolean);

    return parts.join(' - ');
}

function stationLabel(estacion) {
    if (!estacion) return null;

    return `${estacion.codigo ? `${estacion.codigo} - ` : ''}${estacion.nombre ?? 'Sin nombre'}`;
}

function formatConflictValue(fieldName, value, responsables = []) {
    if (fieldName === 'estado') return ESTADO_LABEL[value] ?? fmt(value);
    if (fieldName === 'id_responsable_ciete') return responsableName(responsables, value) ?? fmt(value);
    if (fieldName === 'fecha_terminacion') return fmtDate(value);
    return fmt(value);
}

function mergePatchedRow(row, payload, fieldName, responsables = []) {
    const next = payload?.trabajo ? { ...row, ...payload.trabajo } : { ...row };
    const campo = payload?.campo ?? fieldName;

    if (campo && Object.prototype.hasOwnProperty.call(payload ?? {}, 'valor')) {
        next[campo] = payload.valor;
        if (campo === 'fecha_terminacion') {
            next.fecha_terminado = payload.valor;
        }
        if (campo === 'id_responsable_ciete') {
            next.nombre_responsable = responsableName(responsables, payload.valor);
        }
        if (campo === 'numero_trabajo_operativo') {
            next.numero_trabajo_visible = payload.valor || next.numero_trabajo;
        }
    }

    if (payload?.updated_at) {
        next.updated_at = payload.updated_at;
    }

    return next;
}

function FieldError({ message }) {
    if (!message) return null;

    return (
        <span className="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full border border-red-200 bg-red-50 text-[10px] font-bold text-red-700" title={message}>
            !
        </span>
    );
}

function SavingMark({ show }) {
    if (!show) return null;

    return (
        <span className="ml-1 inline-flex h-1.5 w-1.5 rounded-full bg-(--ciete-red)" title="Guardando" />
    );
}

function ConflictDialog({
    conflict,
    fieldName,
    responsables = [],
    canKeepMine = false,
    isSaving = false,
    onReload,
    onCancel,
    onKeepMine,
}) {
    const fieldLabel = FIELD_LABELS[fieldName] ?? conflict?.campo ?? 'Campo';
    const conflictMessage = conflict?.message ?? 'Este campo fue modificado por otro usuario.';
    const conflictHint = conflict?.modificadoRecientemente
        ? 'Este campo fue modificado recientemente por otro usuario. Revisa los valores antes de sobrescribir.'
        : 'Revisa los valores antes de continuar para no sobrescribir cambios recientes.';

    return (
        <Modal show={Boolean(conflict)} maxWidth="lg" closeable={!isSaving} onClose={onCancel}>
            <div className="bg-surface">
                <div className="border-b border-border px-6 py-5">
                    <h3 className="text-lg font-semibold text-text-main">{conflictMessage}</h3>
                    <p className="mt-2 text-sm text-text-muted">
                        {conflictHint}
                    </p>
                </div>

                <div className="space-y-4 px-6 py-5 text-sm">
                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="rounded-lg border border-border bg-surface-2 p-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Campo afectado</p>
                            <p className="mt-1 font-medium text-text-main">{fieldLabel}</p>
                        </div>
                        <div className="rounded-lg border border-border bg-surface-2 p-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Fecha de modificación</p>
                            <p className="mt-1 font-medium text-text-main">{fmt(conflict?.currentUpdatedAt)}</p>
                        </div>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="rounded-lg border border-border bg-surface p-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Valor actual del servidor</p>
                            <p className="mt-1 break-words text-text-main">
                                {formatConflictValue(fieldName, conflict?.currentValue, responsables)}
                            </p>
                        </div>
                        <div className="rounded-lg border border-border bg-surface p-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Valor que intentabas guardar</p>
                            <p className="mt-1 break-words text-text-main">
                                {formatConflictValue(fieldName, conflict?.myValue, responsables)}
                            </p>
                        </div>
                    </div>

                    <div className="rounded-lg border border-border bg-surface-2 p-3">
                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Usuario de modificación</p>
                        <p className="mt-1 text-text-main">{fmt(conflict?.usuarioModificacion)}</p>
                    </div>
                </div>

                <div className="flex flex-col-reverse gap-3 border-t border-border px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        onClick={onReload}
                        disabled={isSaving}
                        className="rounded-md border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:opacity-50"
                    >
                        Recargar valor actual
                    </button>
                    <button
                        type="button"
                        onClick={onCancel}
                        disabled={isSaving}
                        className="rounded-md border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    {canKeepMine && (
                        <button
                            type="button"
                            onClick={onKeepMine}
                            disabled={isSaving}
                            className="rounded-md bg-(--ciete-red) px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-50"
                        >
                            Mantener mi cambio
                        </button>
                    )}
                </div>
            </div>
        </Modal>
    );
}

function EditableTextCell({
    trabajo,
    fieldName,
    onPatched,
    canEdit = false,
    fallbackValue = null,
    maxWidthClass = 'max-w-[250px]',
    textClassName = 'text-text-muted',
}) {
    const [editing, setEditing] = useState(false);
    const { value, setValue, isSaving, error, conflict, save, cancel, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo[fieldName] ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });

    async function commit() {
        const ok = await save(value === '' ? null : value);
        if (ok) setEditing(false);
    }

    function rollback() {
        cancel();
        setEditing(false);
    }

    if (!canEdit) {
        const displayValue = trabajo[fieldName] || fallbackValue;

        return (
            <span className={`block ${maxWidthClass} truncate ${textClassName}`} title={displayValue ?? trabajo[fieldName]}>
                {fmt(displayValue)}
            </span>
        );
    }

    const displayValue = value || fallbackValue;

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={async () => {
                    await resolveConflict('reload');
                    setEditing(false);
                }}
                onCancel={rollback}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) setEditing(false);
                }}
            />

            {editing ? (
                <input
                    autoFocus
                    value={value ?? ''}
                    maxLength={1000}
                    onChange={(event) => setValue(event.target.value)}
                    onBlur={commit}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter') commit();
                        if (event.key === 'Escape') rollback();
                    }}
                    className="h-9 w-full rounded-lg border border-(--ciete-red) bg-surface px-3 text-sm text-text-main outline-none ring-1 ring-(--ciete-red)"
                />
            ) : (
                <button
                    type="button"
                    onClick={() => setEditing(true)}
                    disabled={isSaving}
                    className={`group flex ${maxWidthClass} items-center rounded-md px-1.5 py-1 text-left transition hover:bg-surface-2 hover:text-text-main disabled:cursor-wait`}
                    title={String(displayValue || 'Editar campo')}
                >
                    <span className={`truncate ${textClassName}`}>{fmt(displayValue)}</span>
                    <SavingMark show={isSaving} />
                    <FieldError message={error && !conflict ? error : null} />
                </button>
            )}
        </>
    );
}

function EditableDateCell({ trabajo, onPatched, canEdit = false }) {
    const fieldName = 'fecha_terminacion';
    const initialDate = trabajo.fecha_terminacion ?? trabajo.fecha_terminado ?? '';
    const { value, setValue, isSaving, error, conflict, save, cancel, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: toDateInput(initialDate),
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });
    const [editing, setEditing] = useState(false);

    async function commit() {
        const ok = await save(value ? toDateInput(value) : null);
        if (ok) setEditing(false);
    }

    function rollback() {
        cancel();
        setEditing(false);
    }

    if (!canEdit) {
        return <span className="text-text-muted">{fmtDate(initialDate)}</span>;
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={async () => {
                    await resolveConflict('reload');
                    setEditing(false);
                }}
                onCancel={rollback}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) setEditing(false);
                }}
            />

            {editing ? (
                <input
                    autoFocus
                    type="date"
                    value={toDateInput(value)}
                    onChange={(event) => setValue(event.target.value)}
                    onBlur={commit}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter') commit();
                        if (event.key === 'Escape') rollback();
                    }}
                    className="h-9 rounded-lg border border-(--ciete-red) bg-surface px-3 text-sm text-text-main outline-none ring-1 ring-(--ciete-red)"
                />
            ) : (
                <button
                    type="button"
                    onClick={() => setEditing(true)}
                    disabled={isSaving}
                    className="inline-flex items-center rounded px-1 py-0.5 text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:cursor-wait"
                >
                    <span>{fmtDate(value)}</span>
                    <SavingMark show={isSaving} />
                    <FieldError message={error && !conflict ? error : null} />
                </button>
            )}
        </>
    );
}

function EditableEstadoCell({ trabajo, onPatched, canEdit = false }) {
    const fieldName = 'estado';
    const { value, setValue, isSaving, error, conflict, save, cancel, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.estado,
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });
    const [editing, setEditing] = useState(false);

    function rollback() {
        cancel();
        setEditing(false);
    }

    if (!canEdit) {
        return <BadgeTrabajo estado={value} />;
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={async () => {
                    await resolveConflict('reload');
                    setEditing(false);
                }}
                onCancel={rollback}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) setEditing(false);
                }}
            />

            {editing ? (
                <select
                    autoFocus
                    value={value ?? ''}
                    onChange={async (event) => {
                        const nextValue = event.target.value;
                        setValue(nextValue);
                        const ok = await save(nextValue);
                        if (ok) setEditing(false);
                    }}
                    onBlur={() => setEditing(false)}
                    onKeyDown={(event) => {
                        if (event.key === 'Escape') rollback();
                    }}
                    className="h-9 min-w-[12rem] rounded-lg border border-(--ciete-red) bg-surface px-3 pr-9 text-sm text-text-main outline-none ring-1 ring-(--ciete-red)"
                >
                    {ESTADO_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                </select>
            ) : (
                <button
                    type="button"
                    onClick={() => setEditing(true)}
                    disabled={isSaving}
                    className="inline-flex items-center rounded px-1 py-0.5 transition hover:bg-surface-2 disabled:cursor-wait"
                    title="Editar estado"
                >
                    <BadgeTrabajo estado={value} />
                    <SavingMark show={isSaving} />
                    <FieldError message={error && !conflict ? error : null} />
                </button>
            )}
        </>
    );
}

function EditableResponsableCell({ trabajo, responsables = [], onPatched, canEdit = false }) {
    const fieldName = 'id_responsable_ciete';
    const hasResponsables = responsables.length > 0;
    const { value, setValue, isSaving, error, conflict, save, cancel, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.id_responsable_ciete ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });
    const [editing, setEditing] = useState(false);

    function rollback() {
        cancel();
        setEditing(false);
    }

    if (!canEdit || !hasResponsables) {
        return (
            <span className="block max-w-[220px] truncate text-text-muted" title={trabajo.nombre_responsable}>
                {fmt(trabajo.nombre_responsable)}
            </span>
        );
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                responsables={responsables}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={async () => {
                    await resolveConflict('reload');
                    setEditing(false);
                }}
                onCancel={rollback}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) setEditing(false);
                }}
            />

            {editing ? (
                <select
                    autoFocus
                    value={value ?? ''}
                    onChange={async (event) => {
                        const raw = event.target.value;
                        const nextValue = raw ? Number(raw) : null;
                        setValue(nextValue ?? '');
                        const ok = await save(nextValue);
                        if (ok) setEditing(false);
                    }}
                    onBlur={() => setEditing(false)}
                    onKeyDown={(event) => {
                        if (event.key === 'Escape') rollback();
                    }}
                    className="h-9 w-full max-w-[14rem] rounded-lg border border-(--ciete-red) bg-surface px-3 pr-9 text-sm text-text-main outline-none ring-1 ring-(--ciete-red)"
                >
                    <option value="">Sin responsable</option>
                    {responsables.map((responsable) => (
                        <option key={responsable.id} value={responsable.id}>{responsable.nombre}</option>
                    ))}
                </select>
            ) : (
                <button
                    type="button"
                    onClick={() => setEditing(true)}
                    disabled={isSaving}
                    className="flex max-w-[220px] items-center rounded-md px-1.5 py-1 text-left text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:cursor-wait"
                    title={trabajo.nombre_responsable ?? 'Asignar responsable'}
                >
                    <span className="truncate">{fmt(responsableName(responsables, value) ?? trabajo.nombre_responsable)}</span>
                    <SavingMark show={isSaving} />
                    <FieldError message={error && !conflict ? error : null} />
                </button>
            )}
        </>
    );
}

function EditablePedidoCell({ trabajo, pedidos = [], onPatched, canEdit = false }) {
    const fieldName = 'id_pedido_principal';
    const contextOptions = pedidos.filter((pedido) => String(pedido.id_contexto) === String(trabajo.id_contexto));
    const hasCurrentPedido = contextOptions.some((pedido) => String(pedido.id) === String(trabajo.id_pedido_principal));
    const options = !hasCurrentPedido && trabajo.id_pedido_principal
        ? [
            {
                id: trabajo.id_pedido_principal,
                id_contexto: trabajo.id_contexto,
                id_trabajo: trabajo.id_trabajo,
                numero: trabajo.numero_pedido_principal,
                fecha_solicitud: trabajo.fecha_solicitud_pedido,
            },
            ...contextOptions,
        ]
        : contextOptions;
    const selectedPedido = options.find((pedido) => String(pedido.id) === String(trabajo.id_pedido_principal)) ?? null;
    const selectedLabel = pedidoLabel(selectedPedido) ?? trabajo.numero_pedido_principal;
    const { value, isSaving, error, conflict, save, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.id_pedido_principal ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });

    if (!canEdit) {
        return (
            <span className="block max-w-[190px] truncate font-mono text-text-muted" title={selectedLabel ?? trabajo.numero_pedido_principal}>
                {fmt(trabajo.numero_pedido_principal)}
            </span>
        );
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={() => resolveConflict('reload')}
                onCancel={() => resolveConflict('cancel')}
                onKeepMine={() => resolveConflict('keepMine')}
            />
            <div className="flex items-center">
                <select
                    value={value}
                    onChange={(event) => save(event.target.value)}
                    disabled={isSaving || options.length === 0}
                    className={`h-9 w-full min-w-[14rem] rounded-lg border bg-surface px-3 pr-9 font-mono text-sm leading-5 text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:cursor-not-allowed disabled:bg-surface-2 disabled:text-text-hint ${
                        error ? 'border-red-300 bg-red-50/40' : 'border-border'
                    }`}
                    title={error ?? selectedLabel ?? 'Selecciona pedido'}
                >
                    <option value="" disabled>{options.length === 0 ? 'Sin pedidos disponibles' : 'Selecciona pedido'}</option>
                    {options.map((pedido) => (
                        <option key={pedido.id} value={pedido.id}>
                            {pedidoLabel(pedido)}
                        </option>
                    ))}
                </select>
                <SavingMark show={isSaving} />
                <FieldError message={error} />
            </div>
        </>
    );
}

function EditableStationCell({ trabajo, estaciones = [], onPatched, canEdit = false }) {
    const fieldName = 'id_estacion_servicio';
    const contextOptions = estaciones.filter((estacion) => String(estacion.id_contexto) === String(trabajo.id_contexto));
    const hasCurrentStation = contextOptions.some((estacion) => String(estacion.id) === String(trabajo.id_estacion_servicio));
    const options = !hasCurrentStation && trabajo.id_estacion_servicio
        ? [
            {
                id: trabajo.id_estacion_servicio,
                id_contexto: trabajo.id_contexto,
                codigo: trabajo.codigo_estacion,
                nombre: trabajo.nombre_estacion,
                municipio: trabajo.municipio,
                provincia: trabajo.provincia,
            },
            ...contextOptions,
        ]
        : contextOptions;
    const selectedStation = options.find((estacion) => String(estacion.id) === String(trabajo.id_estacion_servicio)) ?? null;
    const selectedLabel = stationLabel(selectedStation) ?? trabajo.codigo_estacion;
    const { value, isSaving, error, conflict, save, resolveConflict } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.id_estacion_servicio ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });

    if (!canEdit) {
        return (
            <span className="block max-w-[190px] truncate font-mono text-(--ciete-red)" title={selectedLabel ?? trabajo.codigo_estacion}>
                {fmt(trabajo.codigo_estacion)}
            </span>
        );
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={() => resolveConflict('reload')}
                onCancel={() => resolveConflict('cancel')}
                onKeepMine={() => resolveConflict('keepMine')}
            />
            <div className="flex items-center">
                <select
                    value={value}
                    onChange={(event) => save(event.target.value)}
                    disabled={isSaving || options.length === 0}
                    className={`h-9 w-full min-w-[15rem] rounded-lg border bg-surface px-3 pr-9 font-mono text-sm leading-5 text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:cursor-not-allowed disabled:bg-surface-2 disabled:text-text-hint ${
                        error ? 'border-red-300 bg-red-50/40' : 'border-border'
                    }`}
                    title={error ?? selectedLabel ?? 'Selecciona estacion'}
                >
                    <option value="" disabled>{options.length === 0 ? 'Sin estaciones disponibles' : 'Selecciona estacion'}</option>
                    {options.map((estacion) => (
                        <option key={estacion.id} value={estacion.id}>
                            {stationLabel(estacion)}
                        </option>
                    ))}
                </select>
                <SavingMark show={isSaving} />
                <FieldError message={error} />
            </div>
        </>
    );
}

function EditableCategoriaCell({
    trabajo,
    tiposDocumento = [],
    tiposTrabajo = [],
    onPatched,
    canEdit = false,
}) {
    const isRepsol = Number(trabajo.id_contexto) === 2;
    const contextDocumentTypes = tiposDocumento.filter((tipo) => String(tipo.id_contexto) === String(trabajo.id_contexto));
    const contextWorkTypes = tiposTrabajo.filter((tipo) => String(tipo.id_contexto) === String(trabajo.id_contexto));
    const documentField = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName: 'id_tipo_documento',
        initialValue: trabajo.id_tipo_documento ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, 'id_tipo_documento'),
    });
    const workTypeField = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName: 'id_tipo_trabajo',
        initialValue: trabajo.id_tipo_trabajo ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, 'id_tipo_trabajo'),
    });

    if (!isRepsol) {
        return (
            <EditableTextCell
                trabajo={trabajo}
                fieldName="categoria"
                onPatched={onPatched}
                canEdit={canEdit}
            />
        );
    }

    if (!canEdit) {
        return (
            <span className="block max-w-[220px] truncate text-text-muted" title={trabajo.tipo_trabajo_nombre}>
                {fmt(trabajo.tipo_trabajo_nombre)}
            </span>
        );
    }

    const selectedDocumentId = documentField.value || trabajo.id_tipo_documento || '';
    const availableWorkTypes = selectedDocumentId
        ? contextWorkTypes.filter((tipo) => String(tipo.id_tipo_documento) === String(selectedDocumentId))
        : contextWorkTypes;
    const isSaving = documentField.isSaving || workTypeField.isSaving;
    const error = documentField.error || workTypeField.error;

    return (
        <>
            <ConflictDialog
                conflict={documentField.conflict}
                fieldName="id_tipo_documento"
                canKeepMine={canEdit}
                isSaving={documentField.isSaving}
                onReload={() => documentField.resolveConflict('reload')}
                onCancel={() => documentField.resolveConflict('cancel')}
                onKeepMine={() => documentField.resolveConflict('keepMine')}
            />
            <ConflictDialog
                conflict={workTypeField.conflict}
                fieldName="id_tipo_trabajo"
                canKeepMine={canEdit}
                isSaving={workTypeField.isSaving}
                onReload={() => workTypeField.resolveConflict('reload')}
                onCancel={() => workTypeField.resolveConflict('cancel')}
                onKeepMine={() => workTypeField.resolveConflict('keepMine')}
            />
            <div className="grid min-w-52 gap-1.5">
                <select
                    value={documentField.value}
                    onChange={(event) => documentField.save(event.target.value)}
                    disabled={isSaving || contextDocumentTypes.length === 0}
                    className={`h-9 rounded-lg border bg-surface px-3 pr-9 text-sm text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:bg-surface-2 disabled:text-text-hint ${
                        documentField.error ? 'border-red-300 bg-red-50/40' : 'border-border'
                    }`}
                    title={documentField.error ?? 'Tipo documental'}
                >
                    <option value="" disabled>Tipo doc...</option>
                    {contextDocumentTypes.map((tipo) => (
                        <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                    ))}
                </select>
                <div className="flex items-center">
                    <select
                        value={workTypeField.value}
                        onChange={(event) => workTypeField.save(event.target.value)}
                        disabled={isSaving || availableWorkTypes.length === 0}
                        className={`h-9 w-full rounded-lg border bg-surface px-3 pr-9 text-sm text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:bg-surface-2 disabled:text-text-hint ${
                            workTypeField.error ? 'border-red-300 bg-red-50/40' : 'border-border'
                        }`}
                        title={workTypeField.error ?? 'Tipo de trabajo'}
                    >
                        <option value="" disabled>Tipo trabajo...</option>
                        {availableWorkTypes.map((tipo) => (
                            <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                        ))}
                    </select>
                    <SavingMark show={isSaving} />
                    <FieldError message={error} />
                </div>
            </div>
        </>
    );
}

function ObservacionesModal({ trabajo, onClose, onPatched, canEdit = false }) {
    const fieldName = 'observaciones';
    const textareaRef = useRef(null);
    const { isSaving, error, conflict, save, resolveConflict, cancel } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.observaciones ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });
    const [draft, setDraft] = useState(trabajo.observaciones ?? '');

    useEffect(() => {
        setDraft(trabajo.observaciones ?? '');
    }, [trabajo.id_trabajo, trabajo.observaciones]);

    useEffect(() => {
        const focusTimer = window.setTimeout(() => textareaRef.current?.focus(), 80);

        return () => window.clearTimeout(focusTimer);
    }, []);

    async function handleSave() {
        if (!canEdit || isSaving) return;
        const ok = await save(draft === '' ? null : draft);
        if (ok) onClose();
    }

    function handleCancel() {
        cancel();
        onClose();
    }

    return (
        <>
            <Modal show maxWidth="2xl" closeable={false} onClose={handleCancel}>
                <div className="bg-surface">
                    <div className="border-b border-border px-6 py-5">
                        <h3 className="text-lg font-semibold text-text-main">Editar observaciones</h3>
                        <p className="mt-1 text-sm font-medium text-text-muted">
                            Trabajo {formatWorkNumber(workNumberForDisplay(trabajo))}
                            {trabajo.nombre_estacion ? ` · ${trabajo.nombre_estacion}` : ''}
                        </p>
                        <p className="mt-3 text-sm text-text-muted">
                            Modifica únicamente las observaciones de este trabajo. El cambio quedará registrado en Auditoría.
                        </p>
                    </div>

                    <div className="px-6 py-5">
                        {error && !conflict && (
                            <p className="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {error}
                            </p>
                        )}
                        {!canEdit && (
                            <p className="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                No tienes permisos para modificar las observaciones de este trabajo.
                            </p>
                        )}
                        <textarea
                            ref={textareaRef}
                            value={draft}
                            onChange={(event) => setDraft(event.target.value)}
                            rows={9}
                            maxLength={5000}
                            readOnly={!canEdit || isSaving}
                            className="min-h-56 w-full resize-y rounded-xl border border-border bg-surface-2 px-4 py-3 text-sm text-text-main outline-none transition placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                            placeholder="Sin observaciones"
                        />
                        <p className="mt-2 text-right text-xs text-text-hint">{draft.length}/5000</p>
                    </div>

                    <div className="flex flex-col-reverse gap-3 border-t border-border px-6 py-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            onClick={handleCancel}
                            disabled={isSaving}
                            className="rounded-md border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleSave}
                            disabled={!canEdit || isSaving}
                            className="rounded-md bg-(--ciete-red) px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-50"
                        >
                            {isSaving ? 'Guardando...' : 'Guardar observaciones'}
                        </button>
                    </div>
                </div>
            </Modal>

            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={async () => {
                    const serverValue = conflict?.currentValue ?? '';
                    setDraft(serverValue);
                    await resolveConflict('reload');
                }}
                onCancel={handleCancel}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) onClose();
                }}
            />
        </>
    );
}

function ObservacionesCell({ trabajo, onPatched, canEdit = false }) {
    const [open, setOpen] = useState(false);
    const value = trabajo.observaciones;

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                className={`block max-w-[220px] truncate rounded px-1 py-0.5 text-left transition hover:bg-surface-2 hover:text-text-main ${
                    value ? 'text-text-muted' : 'italic text-text-hint'
                }`}
                title={value || 'Sin observaciones'}
            >
                {value ? String(value) : 'Sin observaciones'}
            </button>

            {open && (
                <ObservacionesModal
                    trabajo={trabajo}
                    onClose={() => setOpen(false)}
                    onPatched={onPatched}
                    canEdit={canEdit}
                />
            )}
        </>
    );
}

function NewObservacionesModal({ value, onClose, onSave }) {
    const [draft, setDraft] = useState(value ?? '');

    return (
        <Modal show maxWidth="2xl" closeable={false} onClose={onClose}>
            <div className="bg-surface">
                <div className="border-b border-border px-6 py-5">
                    <h3 className="text-lg font-semibold text-text-main">Editar observaciones</h3>
                    <p className="mt-2 text-sm text-text-muted">
                        Estas observaciones se guardaran cuando guardes el trabajo nuevo.
                    </p>
                </div>

                <div className="px-6 py-5">
                    <textarea
                        autoFocus
                        value={draft}
                        onChange={(event) => setDraft(event.target.value)}
                        rows={9}
                        maxLength={5000}
                        className="min-h-56 w-full resize-y rounded-xl border border-border bg-surface-2 px-4 py-3 text-sm text-text-main outline-none transition placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                        placeholder="Sin observaciones"
                    />
                    <p className="mt-2 text-right text-xs text-text-hint">{draft.length}/5000</p>
                </div>

                <div className="flex flex-col-reverse gap-3 border-t border-border px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-muted transition hover:bg-surface-2 hover:text-text-main"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        onClick={() => onSave(draft)}
                        className="rounded-md bg-(--ciete-red) px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark)"
                    >
                        Guardar observaciones
                    </button>
                </div>
            </div>
        </Modal>
    );
}

function NewObservacionesCell({ value, onChange }) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                className={`block max-w-[220px] truncate rounded px-1 py-0.5 text-left transition hover:bg-surface-2 hover:text-text-main ${
                    value ? 'text-text-muted' : 'italic text-text-hint'
                }`}
                title={value || 'Sin observaciones'}
            >
                {value ? String(value) : 'Sin observaciones'}
            </button>

            {open && (
                <NewObservacionesModal
                    value={value}
                    onClose={() => setOpen(false)}
                    onSave={(nextValue) => {
                        onChange(nextValue);
                        setOpen(false);
                    }}
                />
            )}
        </>
    );
}

function NewTrabajoRow({
    row,
    activeContext,
    creationCatalogs,
    responsables,
    errors,
    isSaving,
    onChange,
    onSave,
    onCancel,
}) {
    const estaciones = creationCatalogs?.estaciones ?? [];
    const contratos = creationCatalogs?.contratos ?? [];
    const tiposDocumento = creationCatalogs?.tiposDocumento ?? [];
    const tiposTrabajo = creationCatalogs?.tiposTrabajo ?? [];
    const selectedStation = estaciones.find((item) => String(item.id) === String(row.id_estacion_servicio)) ?? null;
    const isMoeve = isMoeveContext(activeContext);
    const isRepsol = isRepsolContext(activeContext);
    const availableWorkTypes = row.id_tipo_documento
        ? tiposTrabajo.filter((item) => String(item.id_tipo_documento) === String(row.id_tipo_documento))
        : tiposTrabajo;

    const cellInput = (field) => `h-11 w-full rounded-lg border bg-surface px-3 text-sm leading-5 text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) ${
        errors?.[field] ? 'border-red-300 bg-red-50/40' : 'border-border'
    }`;
    const cellSelect = (field) => `${cellInput(field)} pr-10`;
    const readOnlyCell = 'block max-w-[220px] truncate text-[12px] leading-5 text-text-muted';

    return (
        <tr className="border-y border-(--ciete-red)/30 bg-(--ciete-red)/[0.06] align-middle shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]">
            <td className="whitespace-nowrap px-3 py-3 align-top">
                <div className="grid gap-2.5">
                    <div className="flex flex-wrap items-center gap-2.5">
                        <span className="rounded-full border border-(--ciete-red)/25 bg-(--ciete-red)/12 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-(--ciete-red)">
                            Nuevo
                        </span>
                        <input
                            type="number"
                            autoFocus
                            value={row.numero_trabajo}
                            onChange={(event) => onChange('numero_trabajo', event.target.value)}
                            className={`${cellInput('numero_trabajo')} max-w-28 font-mono`}
                            placeholder="Interno"
                        />
                    </div>
                    <input
                        value={row.numero_trabajo_operativo}
                        onChange={(event) => onChange('numero_trabajo_operativo', event.target.value)}
                        className={`${cellInput('numero_trabajo_operativo')} font-mono`}
                        placeholder="Nº CIETE opcional"
                    />
                </div>
            </td>
            <td className="px-3 py-3 align-top">
                <select
                    value={row.id_estacion_servicio}
                    onChange={(event) => onChange('id_estacion_servicio', event.target.value)}
                    className={cellSelect('id_estacion_servicio')}
                >
                    <option value="">Estacion...</option>
                    {estaciones.map((estacion) => (
                        <option key={estacion.id} value={estacion.id}>
                            {estacion.codigo ? `${estacion.codigo} - ` : ''}{estacion.nombre}
                        </option>
                    ))}
                </select>
            </td>
            <td className="px-3 py-3 align-top">
                <span className={readOnlyCell} title={selectedStation?.nombre}>{fmt(selectedStation?.nombre)}</span>
            </td>
            <td className="px-3 py-3 align-top">
                <span className={readOnlyCell}>{fmt(selectedStation?.municipio)}</span>
            </td>
            <td className="px-3 py-3 align-top">
                <span className={readOnlyCell}>{fmt(selectedStation?.provincia)}</span>
            </td>
            <td className="px-3 py-3 align-top">
                {isRepsol ? (
                    <div className="grid gap-2">
                        <select
                            value={row.id_tipo_documento}
                            onChange={(event) => {
                                onChange('id_tipo_documento', event.target.value);
                                onChange('id_tipo_trabajo', '');
                            }}
                            className={cellSelect('id_tipo_documento')}
                        >
                            <option value="">Tipo doc...</option>
                            {tiposDocumento.map((tipo) => (
                                <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                            ))}
                        </select>
                        <select
                            value={row.id_tipo_trabajo}
                            onChange={(event) => onChange('id_tipo_trabajo', event.target.value)}
                            className={cellSelect('id_tipo_trabajo')}
                        >
                            <option value="">Tipo trabajo...</option>
                            {availableWorkTypes.map((tipo) => (
                                <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                            ))}
                        </select>
                    </div>
                ) : (
                    <input
                        value={row.categoria}
                        onChange={(event) => onChange('categoria', event.target.value)}
                        className={cellInput('categoria')}
                        placeholder={isMoeve ? 'Categoria' : 'Tipo / categoria'}
                    />
                )}
            </td>
            <td className="px-3 py-3 align-top">
                <input
                    value={row.descripcion_trabajo}
                    onChange={(event) => onChange('descripcion_trabajo', event.target.value)}
                    className={cellInput('descripcion_trabajo')}
                    placeholder="Descripcion"
                />
            </td>
            <td className="px-3 py-3 align-top text-text-hint">-</td>
            <td className="px-3 py-3 align-top">
                {isMoeve ? (
                    <select
                        value={row.id_contrato}
                        onChange={(event) => onChange('id_contrato', event.target.value)}
                        className={cellSelect('id_contrato')}
                    >
                        <option value="">Contrato...</option>
                        {contratos.map((contrato) => (
                            <option key={contrato.id} value={contrato.id}>{contrato.nombre ?? contrato.codigo}</option>
                        ))}
                    </select>
                ) : (
                    <span className="rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[11px] text-text-hint" title="Campo bloqueado para este contexto">
                        No aplica
                    </span>
                )}
            </td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 align-top">
                <select
                    value={row.estado}
                    onChange={(event) => onChange('estado', event.target.value)}
                    className={cellSelect('estado')}
                >
                    {ESTADO_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                </select>
            </td>
            <td className="px-3 py-3 align-top">
                <select
                    value={row.id_responsable_ciete}
                    onChange={(event) => onChange('id_responsable_ciete', event.target.value)}
                    className={cellSelect('id_responsable_ciete')}
                >
                    <option value="">Sin responsable</option>
                    {responsables.map((responsable) => (
                        <option key={responsable.id} value={responsable.id}>{responsable.nombre}</option>
                    ))}
                </select>
            </td>
            <td className="px-3 py-3 align-top">
                <input
                    type="date"
                    value={toDateInput(row.fecha_encargo)}
                    onChange={(event) => onChange('fecha_encargo', event.target.value)}
                    className={cellInput('fecha_encargo')}
                />
            </td>
            <td className="px-3 py-3 align-top text-text-hint">-</td>
            <td className="px-3 py-3 align-top">
                <input
                    type="date"
                    value={toDateInput(row.fecha_terminacion)}
                    onChange={(event) => onChange('fecha_terminacion', event.target.value)}
                    className={cellInput('fecha_terminacion')}
                />
            </td>
            <td className="px-3 py-3 align-top">
                <NewObservacionesCell value={row.observaciones} onChange={(value) => onChange('observaciones', value)} />
            </td>
            <td className="px-3 py-3 align-top">
                <div className="flex min-w-40 flex-col gap-2">
                    <button
                        type="button"
                        onClick={onSave}
                        disabled={isSaving}
                        className="rounded-md bg-(--ciete-red) px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-50"
                    >
                        {isSaving ? 'Guardando...' : 'Guardar'}
                    </button>
                    <button
                        type="button"
                        onClick={onCancel}
                        disabled={isSaving}
                        className="rounded-md border border-border bg-surface px-3 py-2.5 text-xs font-medium text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                </div>
            </td>
        </tr>
    );
}

export default function TrabajosExcelView({
    trabajos = [],
    filters = {},
    aplicarFiltros,
    canCreate = true,
    pagination = null,
    responsables = [],
    creationCatalogs = {},
}) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;
    const pedidos = creationCatalogs?.pedidos ?? [];
    const estaciones = creationCatalogs?.estaciones ?? [];
    const tiposDocumento = creationCatalogs?.tiposDocumento ?? [];
    const tiposTrabajo = creationCatalogs?.tiposTrabajo ?? [];
    const canCreateInContext = canCreate && !activeContext?.is_all;
    const [rows, setRows] = useState(trabajos);
    const [search, setSearch] = useState(filters.search ?? '');
    const [estado, setEstado] = useState(filters.estado ?? '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde ?? '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta ?? '');
    const [municipio, setMunicipio] = useState(filters.municipio ?? '');
    const [provincia, setProvincia] = useState(filters.provincia ?? '');
    const [codigoEstacion, setCodigoEstacion] = useState(filters.codigo_estacion ?? '');
    const [responsableId, setResponsableId] = useState(filters.id_responsable_ciete ?? '');
    const [newRow, setNewRow] = useState(null);
    const [newRowErrors, setNewRowErrors] = useState({});
    const [newRowMessage, setNewRowMessage] = useState('');
    const [isCreating, setIsCreating] = useState(false);

    useEffect(() => {
        setRows(trabajos);
    }, [trabajos]);

    const orderedRows = useMemo(() => (
        [...rows].sort((a, b) => {
            if (a.estado === 'cancelado' && b.estado !== 'cancelado') return 1;
            if (a.estado !== 'cancelado' && b.estado === 'cancelado') return -1;
            return (ESTADO_ORDER[a.estado] ?? 99) - (ESTADO_ORDER[b.estado] ?? 99);
        })
    ), [rows]);

    function onPatched(idTrabajo, payload, fieldName) {
        setRows((currentRows) => currentRows.map((row) => (
            row.id_trabajo === idTrabajo ? mergePatchedRow(row, payload, fieldName, responsables) : row
        )));
    }

    function doFilter(overrides = {}) {
        aplicarFiltros({
            search: overrides.search !== undefined ? overrides.search : search,
            estado: overrides.estado !== undefined ? overrides.estado : estado,
            fecha_desde: overrides.fecha_desde !== undefined ? overrides.fecha_desde : fechaDesde,
            fecha_hasta: overrides.fecha_hasta !== undefined ? overrides.fecha_hasta : fechaHasta,
            municipio: overrides.municipio !== undefined ? overrides.municipio : municipio,
            provincia: overrides.provincia !== undefined ? overrides.provincia : provincia,
            codigo_estacion: overrides.codigo_estacion !== undefined ? overrides.codigo_estacion : codigoEstacion,
            id_responsable_ciete: overrides.id_responsable_ciete !== undefined ? overrides.id_responsable_ciete : responsableId,
            ...(overrides.page !== undefined ? { page: overrides.page } : {}),
        });
    }

    function clearFilters() {
        setSearch('');
        setEstado('');
        setFechaDesde('');
        setFechaHasta('');
        setMunicipio('');
        setProvincia('');
        setCodigoEstacion('');
        setResponsableId('');
        aplicarFiltros({
            search: '',
            estado: '',
            fecha_desde: '',
            fecha_hasta: '',
            municipio: '',
            provincia: '',
            codigo_estacion: '',
            id_responsable_ciete: '',
        });
    }

    function handleCreate() {
        setNewRowMessage('');

        if (!canCreate) {
            setNewRowMessage('No tienes permisos para crear trabajos.');
            return;
        }

        if (activeContext?.is_all) {
            setNewRowMessage('Para crear un trabajo nuevo, selecciona primero un contexto concreto: MOEVE, REPSOL u OTROS CLIENTES.');
            return;
        }

        setNewRow((current) => current ?? emptyNewTrabajo(activeContext));
        setNewRowErrors({});
    }

    function updateNewRow(field, value) {
        setNewRow((current) => current ? { ...current, [field]: value } : current);
        setNewRowErrors((current) => {
            const next = { ...current };
            delete next[field];
            return next;
        });
    }

    function validateNewRow() {
        const errors = {};
        const allowedStates = new Set(ESTADO_OPTIONS.map((option) => option.value));

        if (!String(newRow?.numero_trabajo ?? '').trim()) errors.numero_trabajo = 'El número de trabajo es obligatorio.';
        if (!String(newRow?.id_estacion_servicio ?? '').trim()) errors.id_estacion_servicio = 'La estación es obligatoria.';
        if (!String(newRow?.descripcion_trabajo ?? '').trim()) errors.descripcion_trabajo = 'La descripción es obligatoria.';
        if (!String(newRow?.fecha_encargo ?? '').trim()) errors.fecha_encargo = 'La fecha de encargo es obligatoria.';
        if (newRow?.estado && !allowedStates.has(newRow.estado)) errors.estado = 'Estado no válido.';
        if (isMoeveContext(activeContext) && !String(newRow?.id_contrato ?? '').trim()) errors.id_contrato = 'El contrato es obligatorio para MOEVE.';
        if (isRepsolContext(activeContext) && !String(newRow?.id_tipo_documento ?? '').trim()) errors.id_tipo_documento = 'El tipo documental es obligatorio para REPSOL.';
        if (isRepsolContext(activeContext) && !String(newRow?.id_tipo_trabajo ?? '').trim()) errors.id_tipo_trabajo = 'El tipo de trabajo es obligatorio para REPSOL.';

        return errors;
    }

    function buildNewRowPayload() {
        return {
            id_contexto: activeContext?.id_contexto ? Number(activeContext.id_contexto) : undefined,
            numero_trabajo: Number(newRow.numero_trabajo),
            numero_trabajo_operativo: String(newRow.numero_trabajo_operativo ?? '').trim() || null,
            id_estacion_servicio: Number(newRow.id_estacion_servicio),
            descripcion_trabajo: String(newRow.descripcion_trabajo).trim(),
            estado: newRow.estado,
            fecha_encargo: newRow.fecha_encargo,
            fecha_terminacion: newRow.fecha_terminacion || null,
            observaciones: newRow.observaciones || null,
            id_responsable_ciete: newRow.id_responsable_ciete ? Number(newRow.id_responsable_ciete) : null,
            id_contrato: isMoeveContext(activeContext) && newRow.id_contrato ? Number(newRow.id_contrato) : null,
            categoria: !isRepsolContext(activeContext) && newRow.categoria ? String(newRow.categoria).trim() : null,
            id_tipo_documento: isRepsolContext(activeContext) && newRow.id_tipo_documento ? Number(newRow.id_tipo_documento) : null,
            id_tipo_trabajo: isRepsolContext(activeContext) && newRow.id_tipo_trabajo ? Number(newRow.id_tipo_trabajo) : null,
        };
    }

    async function saveNewRow() {
        if (!newRow || isCreating) return;

        const errors = validateNewRow();
        if (Object.keys(errors).length > 0) {
            setNewRowErrors(errors);
            setNewRowMessage('Completa los campos obligatorios antes de guardar.');
            return;
        }

        setIsCreating(true);
        setNewRowErrors({});
        setNewRowMessage('');

        try {
            const response = await axios.post(route('trabajos.store'), buildNewRowPayload(), {
                headers: { Accept: 'application/json' },
            });
            const createdTrabajo = response.data?.trabajo;

            if (createdTrabajo) {
                setRows((currentRows) => [createdTrabajo, ...currentRows]);
            } else {
                router.reload({ only: ['trabajos'] });
            }

            setNewRow(null);
            setNewRowMessage('Trabajo creado correctamente.');
        } catch (error) {
            const data = error.response?.data ?? {};
            setNewRowErrors(data.errors ?? {});
            setNewRowMessage(
                data.message
                    ?? Object.values(data.errors ?? {})?.flat()?.[0]
                    ?? 'No se pudo crear el trabajo.'
            );
        } finally {
            setIsCreating(false);
        }
    }

    function cancelNewRow() {
        if (!newRow) return;

        const hasDraft = Object.entries(newRow).some(([key, value]) => (
            !key.startsWith('__')
            && !['estado', 'fecha_encargo', 'id_contexto'].includes(key)
            && value !== ''
            && value !== null
            && value !== undefined
        ));

        if (hasDraft && !window.confirm('Se descartara el trabajo nuevo sin guardar.')) {
            return;
        }

        setNewRow(null);
        setNewRowErrors({});
        setNewRowMessage('');
    }

    const hasFilters = Boolean(search || estado || fechaDesde || fechaHasta || municipio || provincia || codigoEstacion || responsableId);

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <div className="mr-2 flex min-h-8 items-center rounded-lg border border-border bg-surface-2 px-3">
                    <WorkspaceContextIndicator compact />
                    {activeContext?.is_all && (
                        <span className="ml-2 text-[11px] font-medium text-text-hint">vista global operativa</span>
                    )}
                </div>

                <input
                    type="search"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    onKeyDown={(event) => event.key === 'Enter' && doFilter({ search })}
                    placeholder="Buscar trabajo, aviso o descripción"
                    className="h-8 w-64 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none transition placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />

                <select
                    value={estado}
                    onChange={(event) => {
                        setEstado(event.target.value);
                        doFilter({ estado: event.target.value });
                    }}
                    className="h-9 min-w-[11rem] rounded-md border border-border bg-surface px-3 pr-9 text-sm text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                >
                    <option value="">Todos los estados</option>
                    {ESTADO_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                </select>

                {responsables.length > 0 && (
                    <select
                        value={responsableId}
                        onChange={(event) => {
                            setResponsableId(event.target.value);
                            doFilter({ id_responsable_ciete: event.target.value });
                        }}
                        className="h-9 min-w-[12rem] max-w-56 rounded-md border border-border bg-surface px-3 pr-9 text-sm text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                    >
                        <option value="">Todos los responsables</option>
                        {responsables.map((responsable) => (
                            <option key={responsable.id} value={responsable.id}>{responsable.nombre}</option>
                        ))}
                    </select>
                )}

                <input
                    type="date"
                    value={fechaDesde}
                    onChange={(event) => {
                        setFechaDesde(event.target.value);
                        doFilter({ fecha_desde: event.target.value });
                    }}
                    title="Fecha desde"
                    className="h-8 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />
                <input
                    type="date"
                    value={fechaHasta}
                    onChange={(event) => {
                        setFechaHasta(event.target.value);
                        doFilter({ fecha_hasta: event.target.value });
                    }}
                    title="Fecha hasta"
                    className="h-8 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />

                <input
                    type="search"
                    value={codigoEstacion}
                    onChange={(event) => setCodigoEstacion(event.target.value)}
                    onKeyDown={(event) => event.key === 'Enter' && doFilter({ codigo_estacion: codigoEstacion })}
                    placeholder="Código estación"
                    className="h-8 w-32 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />
                <input
                    type="search"
                    value={municipio}
                    onChange={(event) => setMunicipio(event.target.value)}
                    onKeyDown={(event) => event.key === 'Enter' && doFilter({ municipio })}
                    placeholder="Municipio"
                    className="h-8 w-32 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />
                <input
                    type="search"
                    value={provincia}
                    onChange={(event) => setProvincia(event.target.value)}
                    onKeyDown={(event) => event.key === 'Enter' && doFilter({ provincia })}
                    placeholder="Provincia"
                    className="h-8 w-32 rounded-md border border-border bg-surface px-2 text-xs text-text-main outline-none placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                />

                {hasFilters && (
                    <button
                        type="button"
                        onClick={clearFilters}
                        className="h-8 rounded-md border border-border px-3 text-xs font-medium text-text-muted transition hover:bg-surface-2 hover:text-text-main"
                    >
                        Limpiar
                    </button>
                )}

                {pagination?.total !== undefined && (
                    <span className="ml-auto text-xs text-text-hint">{pagination.total} trabajos</span>
                )}

                {canCreate && (
                    <button
                        type="button"
                        onClick={handleCreate}
                        disabled={Boolean(newRow)}
                        className={`h-8 rounded-md px-3 text-xs font-semibold transition ${
                            newRow
                                ? 'border border-border bg-surface-2 text-text-hint'
                                : canCreateInContext
                                ? 'bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)'
                                : 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100'
                        } disabled:cursor-not-allowed`}
                    >
                        {newRow ? 'Trabajo nuevo en edición' : 'Nuevo trabajo'}
                    </button>
                )}
            </div>

            {newRow && (
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-(--ciete-red)/20 bg-(--ciete-red)/[0.045] px-4 py-3">
                    <div>
                        <p className="text-sm font-semibold text-text-main">Trabajo nuevo sin guardar</p>
                        <p className="text-xs text-text-muted">
                            Rellena la fila superior. Puedes guardarla o cancelarla desde aquí aunque no veas la columna Acciones.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={saveNewRow}
                            disabled={isCreating}
                            className="rounded-md bg-(--ciete-red) px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-50"
                        >
                            {isCreating ? 'Guardando...' : 'Guardar trabajo'}
                        </button>
                        <button
                            type="button"
                            onClick={cancelNewRow}
                            disabled={isCreating}
                            className="rounded-md border border-border bg-surface px-3 py-1.5 text-xs font-semibold text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            )}

            {newRowMessage && (
                <div className={`rounded-lg border px-3 py-2 text-sm ${
                    newRowMessage === 'Trabajo creado correctamente.'
                        ? 'border-green-200 bg-green-50 text-green-800'
                        : 'border-amber-200 bg-amber-50 text-amber-800'
                }`}>
                    {newRowMessage}
                </div>
            )}

            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="ciete-excel-table w-full min-w-[2500px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {TABLE_COLUMNS.map((column) => (
                                <th
                                    key={column.label}
                                    className={`${column.width} whitespace-nowrap border-b border-border/70 px-3 py-3 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {newRow && (
                            <NewTrabajoRow
                                row={newRow}
                                activeContext={activeContext}
                                creationCatalogs={creationCatalogs}
                                responsables={responsables}
                                errors={newRowErrors}
                                isSaving={isCreating}
                                onChange={updateNewRow}
                                onSave={saveNewRow}
                                onCancel={cancelNewRow}
                            />
                        )}

                        {orderedRows.length === 0 && !newRow && (
                            <tr>
                                <td colSpan={TABLE_COLUMNS.length} className="px-3 py-10 text-center text-text-hint">
                                    No hay trabajos con los filtros aplicados.
                                </td>
                            </tr>
                        )}

                        {orderedRows.map((trabajo) => {
                            const canEditRow = Boolean(trabajo.can?.update);
                            const isCancelled = trabajo.estado === 'cancelado';

                            return (
                                <tr
                                    key={trabajo.id_trabajo}
                                    className={`transition hover:bg-surface-2/65 ${isCancelled ? 'bg-surface-2/35 opacity-70' : ''}`}
                                >
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top font-mono font-semibold text-(--ciete-red)">
                                        <EditableTextCell
                                            trabajo={trabajo}
                                            fieldName="numero_trabajo_operativo"
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                            fallbackValue={formatWorkNumber(trabajo.numero_trabajo)}
                                            maxWidthClass="max-w-[170px]"
                                        />
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top font-mono font-semibold text-(--ciete-red)">
                                        <EditableStationCell
                                            trabajo={trabajo}
                                            estaciones={estaciones}
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                        />
                                    </td>
                                    <td className="max-w-[260px] px-3 py-2.5 align-top font-medium text-text-main" title={trabajo.nombre_estacion}>
                                        <span className="block max-w-[260px] truncate leading-5 text-text-main">{fmt(trabajo.nombre_estacion)}</span>
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top text-text-muted">
                                        <span className="block max-w-[170px] truncate leading-5 text-text-muted">{fmt(trabajo.municipio)}</span>
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top text-text-muted">
                                        <span className="block max-w-[170px] truncate leading-5 text-text-muted">{fmt(trabajo.provincia)}</span>
                                    </td>
                                    <td className="max-w-[230px] px-3 py-2.5 align-top text-text-muted" title={trabajo.tipo_trabajo_nombre ?? trabajo.categoria}>
                                        <EditableCategoriaCell
                                            trabajo={trabajo}
                                            tiposDocumento={tiposDocumento}
                                            tiposTrabajo={tiposTrabajo}
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                        />
                                    </td>
                                    <td className="px-3 py-2.5 align-top">
                                        <EditableTextCell
                                            trabajo={trabajo}
                                            fieldName="descripcion_trabajo"
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                            maxWidthClass="max-w-[380px]"
                                            textClassName="text-text-main"
                                        />
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top">
                                        <EditablePedidoCell
                                            trabajo={trabajo}
                                            pedidos={pedidos}
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                        />
                                    </td>
                                    <td className="max-w-[260px] px-3 py-2.5 align-top text-text-muted" title={contractTariff(trabajo)}>
                                        <span className="block max-w-[260px] truncate leading-5 text-text-main/90">
                                            {fmt(contractTariff(trabajo))}
                                        </span>
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 text-right align-top text-text-muted">{fmtMoney(trabajo.importe_pedido_total)}</td>
                                    <td className="whitespace-nowrap px-3 py-2.5 text-right align-top text-text-muted">{fmtMoney(trabajo.importe_solicitado_total)}</td>
                                    <td className="whitespace-nowrap px-3 py-2.5 text-right align-top text-text-muted">{fmtMoney(trabajo.importe_facturado_total)}</td>
                                    <td className="px-3 py-2.5 align-top">
                                        <EditableEstadoCell trabajo={trabajo} onPatched={onPatched} canEdit={canEditRow} />
                                    </td>
                                    <td className="px-3 py-2.5 align-top">
                                        <EditableResponsableCell
                                            trabajo={trabajo}
                                            responsables={responsables}
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                        />
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top text-text-muted">{fmtDate(trabajo.fecha_encargo)}</td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top text-text-muted">{fmtDate(trabajo.fecha_solicitud_pedido)}</td>
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top">
                                        <EditableDateCell trabajo={trabajo} onPatched={onPatched} canEdit={canEditRow} />
                                    </td>
                                    <td className="px-3 py-2.5 align-top">
                                        <ObservacionesCell trabajo={trabajo} onPatched={onPatched} canEdit={canEditRow} />
                                    </td>
                                    <td className="px-3 py-2.5 align-top">
                                        {canEditRow ? (
                                            <button
                                                type="button"
                                                onClick={() => router.visit(route('trabajos.edit', trabajo.id_trabajo))}
                                                className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-muted transition hover:bg-surface-2 hover:text-(--ciete-red)"
                                            >
                                                Abrir ficha
                                            </button>
                                        ) : (
                                            <span className="text-[11px] font-medium uppercase tracking-widest text-text-hint">
                                                Solo lectura
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            <PaginationControls
                pagination={pagination}
                onPageChange={(p) => doFilter({ page: p })}
                entityLabel="trabajos"
            />
        </div>
    );
}
