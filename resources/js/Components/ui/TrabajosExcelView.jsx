import Modal from '@/Components/Modal';
import WorkspaceContextIndicator from '@/Components/WorkspaceContextIndicator';
import PaginationControls from '@/Components/ui/PaginationControls';
import { useOptimisticField } from '@/Hooks/useOptimisticField';
import { router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useMemo, useRef, useState } from 'react';

const ESTADO_OPTIONS = [
    { value: 'en_curso', label: 'Trabajo en curso' },
    { value: 'terminado', label: 'Terminado' },
    { value: 'pendiente_facturar', label: 'Pendiente de facturar' },
    { value: 'facturado', label: 'Facturado' },
    { value: 'finalizado', label: 'Finalizado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const ESTADO_OPTIONS_EDITABLES = [
    { value: 'en_curso', label: 'Trabajo en curso' },
    { value: 'terminado', label: 'Terminado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const ESTADO_LABEL = Object.fromEntries(ESTADO_OPTIONS.map((option) => [option.value, option.label]));
const ESTADO_ORDER = Object.fromEntries(ESTADO_OPTIONS.map((option, index) => [option.value, index]));
const hasPermission = (user, permission, aliases = []) =>
    Boolean(user?.permission_slugs?.some((slug) => slug === permission || aliases.includes(slug)));

const FIELD_LABELS = {
    estado: 'Estado',
    descripcion: 'Descripcion',
    descripcion_trabajo: 'Descripción',
    fecha_terminacion: 'Fecha terminación',
    id_responsable_ciete: 'Responsable',
    observaciones: 'Observaciones',
    numero_trabajo: 'Nº trabajo',
    numero_trabajo_operativo: 'Nº trabajo',
    codigo_estacion: 'Nº estación',
    nombre_estacion: 'Nombre estación',
    municipio: 'Municipio',
    provincia: 'Provincia',
    categoria: 'Categoría de trabajo',
    id_tarifario: 'Tarifario',
    id_tipo_documento: 'Categoría de trabajo',
    id_tipo_trabajo: 'Tipo de trabajo',
};

const TABLE_COLUMNS = [
    { id: 'numero_trabajo', label: 'Nº trabajo', width: 'w-44', sortKey: 'numero_trabajo' },
    { id: 'codigo_estacion', label: 'Nº estación', width: 'w-52', sortKey: 'codigo_estacion' },
    { id: 'nombre_estacion', label: 'Nombre estación', width: 'w-52', sortKey: 'nombre_estacion' },
    { id: 'municipio', label: 'Municipio', width: 'w-32', sortKey: 'municipio' },
    { id: 'provincia', label: 'Provincia', width: 'w-28', sortKey: 'provincia' },
    { id: 'categoria', label: 'Categoría de trabajo', width: 'w-48', sortKey: 'categoria' },
    { id: 'descripcion_trabajo', label: 'Descripción', width: 'w-64', sortKey: 'descripcion_trabajo' },
    { id: 'tarifario', label: 'Tarifario', width: 'w-64', sortKey: 'tarifario' },
    { id: 'pedidos', label: 'Pedidos', width: 'w-64' },
    { id: 'accion_pedido', label: 'Acción pedido', width: 'w-40' },
    { id: 'importe_pedido_total', label: 'Importe pedido', width: 'w-28 text-right', sortKey: 'importe_pedido_total' },
    { id: 'importe_solicitado_total', label: 'Solicitado', width: 'w-28 text-right', sortKey: 'importe_solicitado_total' },
    { id: 'importe_facturado_total', label: 'Facturado', width: 'w-28 text-right', sortKey: 'importe_facturado_total' },
    { id: 'estado', label: 'Estado', width: 'w-72', sortKey: 'estado' },
    { id: 'responsable', label: 'Responsable', width: 'w-44', sortKey: 'responsable' },
    { id: 'fecha_encargo', label: 'Fecha encargo', width: 'w-32', sortKey: 'fecha_encargo' },
    { id: 'fecha_solicitud_pedido', label: 'Fecha solicitud pedido', width: 'w-44', sortKey: 'fecha_solicitud_pedido' },
    { id: 'fecha_terminacion', label: 'Fecha terminación', width: 'w-40', sortKey: 'fecha_terminacion' },
    { id: 'observaciones', label: 'Observaciones', width: 'w-56' },
    { id: 'acciones', label: 'Acciones', width: 'w-28' },
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
        id_tarifario: '',
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

function internalWorkReference(trabajo) {
    const visibleNumber = String(trabajo?.numero_trabajo_operativo ?? '').trim();
    if (!visibleNumber) return null;

    const internalNumber = formatWorkNumber(trabajo?.numero_trabajo);
    const rawInternalNumber = String(trabajo?.numero_trabajo ?? '').trim();
    if (visibleNumber === rawInternalNumber || visibleNumber === internalNumber) return null;

    return internalNumber === '—' ? null : `Ref. interna ${internalNumber}`;
}

function contractTariff(trabajo) {
    const parts = [trabajo.nombre_contrato, trabajo.nombre_tarifa].filter(Boolean);
    return parts.length ? parts.join(' / ') : null;
}

function tarifarioOptionLabel(option) {
    const contract = option?.nombre_contrato ?? '';
    const tariff = option?.nombre_tarifa ?? '';
    const code = option?.codigo_contrato ? `${option.codigo_contrato} · ` : '';

    if (contract && tariff) {
        return `${code}${contract} / ${tariff}`;
    }

    return `${code}${contract || tariff || 'Seleccionar tarifario'}`;
}

function EstadoBadge({ estado }) {
    const normalized = String(estado ?? '').trim().toLowerCase();
    const label = ESTADO_LABEL[normalized] ?? estado ?? '—';
    const palette = {
        en_curso: {
            bg: 'var(--color-state-progress-bg)',
            text: 'var(--color-state-progress-text)',
            dot: 'var(--color-state-progress-dot)',
        },
        terminado: {
            bg: 'var(--color-state-done-bg)',
            text: 'var(--color-state-done-text)',
            dot: 'var(--color-state-done-dot)',
        },
        pendiente_facturar: {
            bg: 'var(--color-state-pending-bg)',
            text: 'var(--color-state-pending-text)',
            dot: 'var(--color-state-pending-dot)',
        },
        facturado: {
            bg: 'var(--color-state-billed-bg)',
            text: 'var(--color-state-billed-text)',
            dot: 'var(--color-state-billed-dot)',
        },
        finalizado: {
            bg: 'var(--color-state-closed-bg)',
            text: 'var(--color-state-closed-text)',
            dot: 'var(--color-state-closed-dot)',
        },
        cancelado: {
            bg: 'var(--color-state-blocked-bg)',
            text: 'var(--color-state-blocked-text)',
            dot: 'var(--color-state-blocked-dot)',
        },
    }[normalized] ?? {
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    };

    return (
        <span
            className="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
            style={{ backgroundColor: palette.bg, color: palette.text }}
        >
            <span className="h-1.5 w-1.5 shrink-0 rounded-full" style={{ backgroundColor: palette.dot }} />
            {label}
        </span>
    );
}

function SecondaryStatusChip({ label, tone = 'neutral' }) {
    const palette = {
        neutral: {
            bg: 'rgba(148, 163, 184, 0.16)',
            text: 'var(--color-text-muted)',
            border: 'rgba(148, 163, 184, 0.22)',
        },
        pending: {
            bg: 'var(--color-state-pending-bg)',
            text: 'var(--color-state-pending-text)',
            border: 'rgba(217, 119, 6, 0.18)',
        },
        blocked: {
            bg: 'var(--color-state-blocked-bg)',
            text: 'var(--color-state-blocked-text)',
            border: 'rgba(220, 38, 38, 0.18)',
        },
    }[tone];

    return (
        <span
            className="inline-flex whitespace-nowrap items-center rounded-full border px-2 py-0.5 text-[11px] font-medium"
            style={{ backgroundColor: palette.bg, color: palette.text, borderColor: palette.border }}
        >
            {label}
        </span>
    );
}

function EstadoMeta({ trabajo }) {
    const chips = [];

    if (trabajo?.estado_facturacion === 'facturado_parcial') {
        chips.push({ key: 'facturacion-parcial', label: 'Facturación parcial', tone: 'pending' });
    }

    if (trabajo?.cierre_secundario === 'listo_para_cierre') {
        chips.push({ key: 'cierre-pendiente', label: 'Cierre pendiente', tone: 'neutral' });
    }

    if (trabajo?.cierre_secundario === 'bloqueado') {
        chips.push({ key: 'cierre-bloqueado', label: 'Cierre bloqueado', tone: 'blocked' });
    }

    if (chips.length === 0) return null;

    return (
        <span className="inline-flex max-w-full flex-nowrap items-center gap-1 overflow-hidden whitespace-nowrap">
            {chips.map((chip) => (
                <SecondaryStatusChip key={chip.key} label={chip.label} tone={chip.tone} />
            ))}
        </span>
    );
}

function responsableName(responsables, value) {
    if (!value) return null;
    return responsables.find((responsable) => String(responsable.id) === String(value))?.nombre ?? null;
}

function pedidoCreateFromTrabajoUrl(trabajo) {
    if (!trabajo?.id_trabajo) return null;

    return `${route('pedidos.create')}?trabajo_id=${encodeURIComponent(trabajo.id_trabajo)}`;
}

function pedidoLookupUrl(numeroPedido) {
    if (!numeroPedido) return null;

    return `${route('pedidos.index')}?search=${encodeURIComponent(numeroPedido)}`;
}

function pedidoDisplayLabel(pedido, index) {
    const number = pedido?.numero_pedido ?? pedido?.numero ?? '';
    return `Pedido ${index + 1} · ${number || 'Sin número'}`;
}

function normalizePedidoSummary(trabajo) {
    return [...(trabajo?.pedidos_resumen ?? [])].sort((a, b) => (Number(a.id_pedido) || 0) - (Number(b.id_pedido) || 0));
}

function stationLabel(estacion) {
    if (!estacion) return null;

    return `${estacion.codigo ? `${estacion.codigo} - ` : ''}${estacion.nombre ?? 'Sin nombre'}`;
}

function contractOptionLabel(contract) {
    if (!contract) return null;

    const code = contract.codigo ? `${contract.codigo} · ` : '';
    return `${code}${contract.nombre ?? 'Contrato sin nombre'}`;
}

function categoryOptionLabel(option) {
    if (!option) return null;

    return option.codigo ? `${option.codigo} · ${option.nombre}` : option.nombre;
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

function normalizeSearch(value) {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function SearchableInlineSelect({
    value,
    options = [],
    onSelect,
    placeholder,
    searchPlaceholder,
    disabled = false,
    error = '',
    emptyLabel = 'Sin resultados',
    selectedLabel = '',
    className = '',
    openOnMount = false,
    onClose,
}) {
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);
    const [open, setOpen] = useState(openOnMount);
    const [search, setSearch] = useState('');
    const selected = options.find((option) => String(option.value) === String(value)) ?? null;
    const label = selected?.label ?? selectedLabel ?? '';
    const filteredOptions = useMemo(() => {
        const needle = normalizeSearch(search);
        const source = needle
            ? options.filter((option) => normalizeSearch(option.searchText ?? option.label).includes(needle))
            : options;

        return source.slice(0, 80);
    }, [options, search]);

    useEffect(() => {
        if (!open) return;

        const timer = window.setTimeout(() => inputRef.current?.focus(), 30);
        return () => window.clearTimeout(timer);
    }, [open]);

    function close() {
        setOpen(false);
        setSearch('');
        onClose?.();
    }

    async function commit(nextValue) {
        if (disabled) return;

        await onSelect(nextValue);
        close();
    }

    return (
        <div
            ref={wrapperRef}
            className={`relative min-w-0 ${className}`}
            onBlur={(event) => {
                if (!wrapperRef.current?.contains(event.relatedTarget)) {
                    close();
                }
            }}
        >
            <button
                type="button"
                onClick={() => {
                    if (disabled) return;
                    if (open) {
                        close();
                        return;
                    }
                    setOpen(true);
                }}
                disabled={disabled}
                className={`flex h-8 w-full min-w-0 items-center justify-between gap-2 rounded-md border bg-surface px-2 text-left text-[11px] leading-4 text-text-main outline-none transition hover:bg-surface-2 focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:cursor-not-allowed disabled:bg-surface-2 disabled:text-text-hint ${
                    error ? 'border-red-300 bg-red-50/40' : 'border-border'
                }`}
                title={error || label || placeholder}
            >
                <span className="truncate font-mono">{label || placeholder}</span>
                <span className="shrink-0 text-[10px] text-text-hint">{open ? '▲' : '⌕'}</span>
            </button>

            {open && (
                <div className="absolute left-0 top-9 z-30 w-[min(24rem,calc(100vw-2rem))] rounded-lg border border-border bg-surface p-2 shadow-xl">
                    <input
                        ref={inputRef}
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        onKeyDown={(event) => {
                            if (event.key === 'Escape') {
                                event.preventDefault();
                                close();
                            }
                            if (event.key === 'Enter' && filteredOptions.length === 1) {
                                event.preventDefault();
                                commit(filteredOptions[0].value);
                            }
                        }}
                        placeholder={searchPlaceholder}
                        className="h-8 w-full rounded-md border border-border bg-surface-2 px-2 text-xs text-text-main outline-none placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)"
                    />
                    <div className="mt-2 max-h-64 overflow-y-auto rounded-md border border-border/70">
                        {filteredOptions.length === 0 ? (
                            <div className="px-3 py-3 text-xs text-text-hint">{emptyLabel}</div>
                        ) : (
                            filteredOptions.map((option) => {
                                const isSelected = String(option.value) === String(value);

                                return (
                                    <button
                                        key={option.value}
                                        type="button"
                                        onMouseDown={(event) => event.preventDefault()}
                                        onClick={() => commit(option.value)}
                                        className={`block w-full px-3 py-2 text-left text-xs transition ${
                                            isSelected
                                                ? 'bg-(--ciete-red)/10 font-semibold text-(--ciete-red)'
                                                : 'text-text-main hover:bg-surface-2'
                                        }`}
                                        title={option.label}
                                    >
                                        <span className="block truncate">{option.label}</span>
                                        {option.hint && (
                                            <span className="mt-0.5 block truncate text-[10px] font-normal text-text-hint">
                                                {option.hint}
                                            </span>
                                        )}
                                    </button>
                                );
                            })
                        )}
                    </div>
                    <p className="mt-1 text-[10px] text-text-hint">
                        {filteredOptions.length} de {options.length} visibles. Escribe código, nombre o número.
                    </p>
                </div>
            )}
        </div>
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
    const estadoOptions = ESTADO_OPTIONS_EDITABLES.some((option) => option.value === value)
        ? ESTADO_OPTIONS_EDITABLES
        : [
            { value, label: ESTADO_LABEL[String(value ?? '').trim().toLowerCase()] ?? value ?? '—' },
            ...ESTADO_OPTIONS_EDITABLES,
        ];

    function rollback() {
        cancel();
        setEditing(false);
    }

    if (!canEdit) {
        return (
            <span className="inline-flex max-w-full flex-nowrap items-center gap-1 overflow-hidden whitespace-nowrap">
                <EstadoBadge estado={value} />
                <EstadoMeta trabajo={trabajo} />
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
                    {estadoOptions.map((option) => (
                        <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                </select>
            ) : (
                <button
                    type="button"
                    onClick={() => setEditing(true)}
                    disabled={isSaving}
                    className="inline-flex max-w-full flex-nowrap items-center gap-1 overflow-hidden whitespace-nowrap rounded px-1 py-0.5 transition hover:bg-surface-2 disabled:cursor-wait"
                    title="Editar estado"
                >
                    <EstadoBadge estado={value} />
                    <EstadoMeta trabajo={trabajo} />
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

function TrabajoPedidoFlowCells({
    trabajo,
    tarifarios = [],
    onPatched,
    canEdit = false,
    canCreatePedidos = false,
    canEditPedidos = false,
}) {
    const fieldName = 'id_tarifario';
    const pedidosResumen = useMemo(() => normalizePedidoSummary(trabajo), [trabajo]);
    const hasPedidos = pedidosResumen.length > 0 || Number(trabajo.pedidos_count ?? 0) > 0;
    const options = useMemo(() => {
        return tarifarios.filter((item) => {
            if (String(item.id_contexto) !== String(trabajo.id_contexto)) {
                return false;
            }

            if (!trabajo.id_empresa_cliente) {
                return true;
            }

            return String(item.id_empresa_cliente) === String(trabajo.id_empresa_cliente);
        });
    }, [tarifarios, trabajo.id_contexto, trabajo.id_empresa_cliente]);
    const currentOption = options.find((item) => String(item.id) === String(trabajo.id_tarifario)) ?? null;
    const defaultOption = options.find((item) => Boolean(item.is_default)) ?? null;
    const suggestedOption = currentOption ?? defaultOption ?? (options.length === 1 ? options[0] : null);
    const [selectedTarifario, setSelectedTarifario] = useState(() => String(suggestedOption?.id ?? ''));
    const [showAllPedidos, setShowAllPedidos] = useState(false);
    const { value, isSaving, error, conflict, save, resolveConflict, cancel } = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName,
        initialValue: trabajo.id_tarifario ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, fieldName),
    });

    useEffect(() => {
        setSelectedTarifario(String(suggestedOption?.id ?? ''));
    }, [trabajo.id_trabajo, trabajo.id_tarifario, suggestedOption?.id]);

    useEffect(() => {
        setShowAllPedidos(false);
    }, [trabajo.id_trabajo, pedidosResumen.length]);

    const persistedTarifario = String(value ?? '');
    const effectiveTarifario = hasPedidos ? persistedTarifario : String(selectedTarifario ?? '');
    const selectedOption = options.find((item) => String(item.id) === effectiveTarifario) ?? currentOption ?? suggestedOption;
    const currentLabel = selectedOption ? tarifarioOptionLabel(selectedOption) : contractTariff(trabajo);
    const visiblePedidos = showAllPedidos ? pedidosResumen : pedidosResumen.slice(0, 3);
    const hiddenPedidosCount = Math.max(0, pedidosResumen.length - 3);
    const shouldShowPlaceholderOption = options.length === 0 || (!currentOption && !defaultOption && options.length > 1);
    const actionLabel = effectiveTarifario
        ? (hasPedidos ? 'Crear otro pedido' : 'Crear pedido')
        : 'Seleccionar tarifario';
    const actionDisabled = isSaving || !canCreatePedidos || !effectiveTarifario;

    function openPedido(pedido) {
        if (canEditPedidos) {
            router.visit(route('pedidos.edit', pedido.id_pedido));
            return;
        }

        const lookupUrl = pedidoLookupUrl(pedido.numero_pedido ?? pedido.numero);
        router.visit(lookupUrl ?? route('pedidos.index'));
    }

    async function persistTarifarioIfNeeded() {
        if (!effectiveTarifario) {
            return false;
        }

        if (String(persistedTarifario) === String(effectiveTarifario)) {
            return true;
        }

        return save(Number(effectiveTarifario));
    }

    async function handleCreatePedido() {
        if (actionDisabled) {
            return;
        }

        const ok = await persistTarifarioIfNeeded();
        if (!ok) {
            return;
        }

        const createUrl = pedidoCreateFromTrabajoUrl(trabajo);
        if (createUrl) {
            router.visit(createUrl);
        }
    }

    return (
        <>
            <ConflictDialog
                conflict={conflict}
                fieldName={fieldName}
                canKeepMine={canEdit && !hasPedidos}
                isSaving={isSaving}
                onReload={async () => {
                    await resolveConflict('reload');
                    setSelectedTarifario(String(conflict?.currentValue ?? currentOption?.id ?? ''));
                }}
                onCancel={() => {
                    cancel();
                    setSelectedTarifario(String(suggestedOption?.id ?? ''));
                }}
                onKeepMine={async () => {
                    const ok = await resolveConflict('keepMine');
                    if (ok) {
                        setSelectedTarifario(String(conflict?.myValue ?? effectiveTarifario ?? ''));
                    }
                }}
            />

            <td className="max-w-[320px] px-3 py-2.5 align-top">
                {canEdit && !hasPedidos ? (
                    <div className="flex min-w-0 items-center gap-1">
                        <select
                            value={effectiveTarifario}
                            onChange={async (event) => {
                                const nextValue = event.target.value;
                                setSelectedTarifario(nextValue);
                                if (nextValue) {
                                    await save(Number(nextValue));
                                }
                            }}
                            disabled={isSaving || options.length === 0}
                            className={`h-8 w-full min-w-[15rem] rounded-md border bg-surface px-2 pr-8 text-[11px] leading-4 text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) disabled:cursor-not-allowed disabled:bg-surface-2 disabled:text-text-hint ${
                                error ? 'border-red-300 bg-red-50/40' : 'border-border'
                            }`}
                        >
                            {shouldShowPlaceholderOption && (
                                <option value="">{options.length === 0 ? 'Sin opciones disponibles' : 'Seleccionar tarifario'}</option>
                            )}
                            {options.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {tarifarioOptionLabel(option)}
                                </option>
                            ))}
                        </select>
                        <SavingMark show={isSaving} />
                        <FieldError message={error && !conflict ? error : null} />
                    </div>
                ) : (
                    <span className="block max-w-[320px] truncate leading-5 text-text-main">
                        {currentLabel || 'Seleccionar tarifario'}
                    </span>
                )}
            </td>

            <td className="max-w-[320px] px-3 py-2.5 align-top">
                {pedidosResumen.length === 0 ? (
                    <span className="block text-text-muted">Sin pedidos</span>
                ) : (
                    <div className="space-y-1">
                        {visiblePedidos.map((pedido, index) => {
                            const absoluteIndex = showAllPedidos ? index : index;
                            const displayIndex = showAllPedidos ? index : index;
                            const label = pedidoDisplayLabel(pedido, showAllPedidos ? absoluteIndex : displayIndex);

                            return (
                                <button
                                    key={pedido.id_pedido}
                                    type="button"
                                    onClick={() => openPedido(pedido)}
                                    className="block max-w-[300px] truncate rounded px-1 py-0.5 text-left text-text-main transition hover:bg-surface-2 hover:text-(--ciete-red)"
                                    title={label}
                                >
                                    {pedidoDisplayLabel(pedido, pedidosResumen.findIndex((item) => item.id_pedido === pedido.id_pedido))}
                                </button>
                            );
                        })}
                        {hiddenPedidosCount > 0 && (
                            <button
                                type="button"
                                onClick={() => setShowAllPedidos((current) => !current)}
                                className="rounded border border-border bg-surface px-2 py-1 text-[11px] font-medium text-text-muted transition hover:bg-surface-2 hover:text-text-main"
                            >
                                {showAllPedidos ? 'Ver menos' : `Ver ${pedidosResumen.length} pedidos`}
                            </button>
                        )}
                    </div>
                )}
            </td>

            <td className="whitespace-nowrap px-3 py-2.5 align-top">
                <button
                    type="button"
                    onClick={handleCreatePedido}
                    disabled={actionDisabled}
                    className={`rounded-md border px-2.5 py-1.5 text-[11px] font-semibold transition ${
                        actionDisabled
                            ? 'cursor-not-allowed border-border bg-surface-2 text-text-hint'
                            : 'border-border bg-surface text-text-main hover:bg-surface-2 hover:text-(--ciete-red)'
                    }`}
                >
                    {actionLabel}
                </button>
            </td>
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
    const stationOptions = options.map((estacion) => ({
        value: estacion.id,
        label: stationLabel(estacion),
        hint: [estacion.municipio, estacion.provincia].filter(Boolean).join(' · '),
        searchText: [estacion.codigo, estacion.nombre, estacion.municipio, estacion.provincia].filter(Boolean).join(' '),
    }));

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
            <div className="flex min-w-0 items-center gap-1">
                <SearchableInlineSelect
                    value={value}
                    options={stationOptions}
                    onSelect={(nextValue) => save(nextValue)}
                    disabled={isSaving || options.length === 0}
                    error={error}
                    selectedLabel={selectedLabel ?? trabajo.codigo_estacion}
                    placeholder={options.length === 0 ? 'Sin estaciones' : 'Buscar nº estación'}
                    searchPlaceholder="Buscar por nº, nombre o municipio"
                    emptyLabel="No hay estaciones con esa búsqueda"
                />
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
    const [editing, setEditing] = useState(false);
    const contextDocumentTypes = tiposDocumento.filter((tipo) => String(tipo.id_contexto) === String(trabajo.id_contexto));
    const contextWorkTypes = tiposTrabajo.filter((tipo) => String(tipo.id_contexto) === String(trabajo.id_contexto));
    const categoriaField = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName: 'categoria',
        initialValue: trabajo.categoria ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, 'categoria'),
    });
    const workTypeField = useOptimisticField({
        entityId: trabajo.id_trabajo,
        entityUpdatedAt: trabajo.updated_at,
        fieldName: 'id_tipo_trabajo',
        initialValue: trabajo.id_tipo_trabajo ?? '',
        patchRoute: route('trabajos.patch-field', trabajo.id_trabajo),
        onSaved: (payload) => onPatched(trabajo.id_trabajo, payload, 'id_tipo_trabajo'),
    });
    const documentTypesById = new Map(contextDocumentTypes.map((item) => [String(item.id), item]));
    const categoryCatalogOptions = isRepsol
        ? contextWorkTypes.map((tipo) => {
            const documentType = documentTypesById.get(String(tipo.id_tipo_documento));

            return {
                value: String(tipo.id),
                label: categoryOptionLabel(tipo),
                hint: documentType ? categoryOptionLabel(documentType) : null,
                searchText: [tipo.codigo, tipo.nombre, documentType?.codigo, documentType?.nombre].filter(Boolean).join(' '),
            };
        })
        : (() => {
            const contextCategorySource = contextWorkTypes.length > 0 ? contextWorkTypes : contextDocumentTypes;

            return contextCategorySource.map((item) => ({
                value: String(item.nombre ?? ''),
                label: categoryOptionLabel(item),
                hint: item.codigo ? `Código ${item.codigo}` : null,
                searchText: [item.codigo, item.nombre].filter(Boolean).join(' '),
            }));
        })();

    if (!canEdit) {
        return (
            <span className="block max-w-[220px] truncate text-text-muted" title={trabajo.tipo_trabajo_nombre ?? trabajo.categoria}>
                {fmt(trabajo.tipo_trabajo_nombre ?? trabajo.categoria)}
            </span>
        );
    }

    if (categoryCatalogOptions.length === 0) {
        return (
            <span
                className="inline-flex min-h-9 items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-medium text-amber-900"
                title="Este contexto no tiene catálogo real de categorías/tipos activo."
            >
                Catálogo pendiente
            </span>
        );
    }

    const isSaving = isRepsol ? workTypeField.isSaving : categoriaField.isSaving;
    const error = isRepsol ? workTypeField.error : categoriaField.error;
    const selectedValue = isRepsol ? String(workTypeField.value ?? trabajo.id_tipo_trabajo ?? '') : String(categoriaField.value ?? trabajo.categoria ?? '');
    const selectedLabel = isRepsol
        ? (categoryCatalogOptions.find((option) => String(option.value) === selectedValue)?.label ?? trabajo.tipo_trabajo_nombre ?? '')
        : String(categoriaField.value ?? trabajo.categoria ?? '');

    if (!editing) {
        return (
            <button
                type="button"
                onClick={() => setEditing(true)}
                className="block max-w-[220px] truncate text-left leading-5 text-text-muted transition hover:text-(--ciete-red)"
                title={selectedLabel || 'Editar categoría de trabajo'}
            >
                {fmt(selectedLabel)}
            </button>
        );
    }

    return (
        <>
            <ConflictDialog
                conflict={isRepsol ? workTypeField.conflict : categoriaField.conflict}
                fieldName={isRepsol ? 'id_tipo_trabajo' : 'categoria'}
                canKeepMine={canEdit}
                isSaving={isSaving}
                onReload={() => (isRepsol ? workTypeField.resolveConflict('reload') : categoriaField.resolveConflict('reload'))}
                onCancel={() => (isRepsol ? workTypeField.resolveConflict('cancel') : categoriaField.resolveConflict('cancel'))}
                onKeepMine={() => (isRepsol ? workTypeField.resolveConflict('keepMine') : categoriaField.resolveConflict('keepMine'))}
            />
            <div className="flex min-w-0 items-center gap-1">
                <SearchableInlineSelect
                    value={selectedValue}
                    options={categoryCatalogOptions}
                    onSelect={(nextValue) => (isRepsol ? workTypeField.save(nextValue) : categoriaField.save(nextValue))}
                    disabled={isSaving}
                    error={error}
                    selectedLabel={selectedLabel}
                    placeholder="Buscar categoría"
                    searchPlaceholder="Código o nombre"
                    emptyLabel="No hay categorías con esa búsqueda"
                    className="min-w-[12rem]"
                    openOnMount
                    onClose={() => setEditing(false)}
                />
                <SavingMark show={isSaving} />
                <FieldError message={error} />
            </div>
        </>
    );
}

function ObservacionesModal({ trabajo, onClose, onPatched, canEdit = false }) {
    const fieldName = 'observaciones';
    const textareaRef = useRef(null);
    const { isSaving, error, conflict, save, resolveConflict, cancel, dismissConflict } = useOptimisticField({
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

    // Cierra solo el ConflictDialog y vuelve al modal principal con el borrador intacto.
    function handleDismissConflict() {
        dismissConflict();
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
                onCancel={handleDismissConflict}
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
    onSaveAndCreatePedido,
    onCancel,
}) {
    const estaciones = creationCatalogs?.estaciones ?? [];
    const tarifarios = creationCatalogs?.tarifarios ?? [];
    const tiposDocumento = creationCatalogs?.tiposDocumento ?? [];
    const tiposTrabajo = creationCatalogs?.tiposTrabajo ?? [];
    const selectedStation = estaciones.find((item) => String(item.id) === String(row.id_estacion_servicio)) ?? null;
    const isRepsol = isRepsolContext(activeContext);
    const contextId = String(row.id_contexto || activeContext?.id_contexto || '');
    const contextDocumentTypes = tiposDocumento.filter((item) => String(item.id_contexto) === contextId);
    const contextWorkTypes = tiposTrabajo.filter((item) => String(item.id_contexto) === contextId);
    const availableWorkTypes = row.id_tipo_documento
        ? contextWorkTypes.filter((item) => String(item.id_tipo_documento) === String(row.id_tipo_documento))
        : contextWorkTypes;
    const documentTypesById = new Map(contextDocumentTypes.map((item) => [String(item.id), item]));
    const categoryCatalogOptions = isRepsol
        ? availableWorkTypes.map((tipo) => {
            const documentType = documentTypesById.get(String(tipo.id_tipo_documento));

            return {
                value: String(tipo.id),
                label: categoryOptionLabel(tipo),
                hint: documentType ? categoryOptionLabel(documentType) : null,
                searchText: [tipo.codigo, tipo.nombre, documentType?.codigo, documentType?.nombre].filter(Boolean).join(' '),
            };
        })
        : (() => {
            const contextCategorySource = contextWorkTypes.length > 0 ? contextWorkTypes : contextDocumentTypes;

            return contextCategorySource.map((item) => ({
                value: String(item.nombre ?? ''),
                label: categoryOptionLabel(item),
                hint: item.codigo ? `Código ${item.codigo}` : null,
                searchText: [item.codigo, item.nombre].filter(Boolean).join(' '),
            }));
        })();
    const availableTarifarios = tarifarios.filter((item) => {
        if (String(item.id_contexto) !== contextId) {
            return false;
        }

        if (!selectedStation?.id_empresa_cliente) {
            return false;
        }

        return String(item.id_empresa_cliente) === String(selectedStation.id_empresa_cliente);
    });
    const defaultTarifario = availableTarifarios.find((item) => Boolean(item.is_default)) ?? null;
    const selectedTarifario = availableTarifarios.find((item) => String(item.id) === String(row.id_tarifario)) ?? null;
    const tarifarioOptions = availableTarifarios.map((option) => ({
        value: String(option.id),
        label: tarifarioOptionLabel(option),
        hint: null,
        searchText: [option.codigo_contrato, option.nombre_contrato, option.nombre_tarifa].filter(Boolean).join(' '),
    }));
    const stationOptions = estaciones.map((estacion) => ({
        value: estacion.id,
        label: stationLabel(estacion),
        hint: [estacion.municipio, estacion.provincia].filter(Boolean).join(' · '),
        searchText: [estacion.codigo, estacion.nombre, estacion.municipio, estacion.provincia].filter(Boolean).join(' '),
    }));
    const categoryError = errors?.id_tipo_trabajo || errors?.id_tipo_documento || errors?.categoria || '';
    const tarifaError = errors?.id_tarifario || errors?.id_contrato || '';
    const pedidoActionLabel = row.id_tarifario ? 'Crear pedido' : 'Seleccionar tarifario';

    const cellInput = (field) => `h-11 w-full rounded-lg border bg-surface px-3 text-sm leading-5 text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red) ${
        errors?.[field] ? 'border-red-300 bg-red-50/40' : 'border-border'
    }`;
    const readOnlyCell = 'block max-w-[220px] truncate text-[12px] leading-5 text-text-muted';

    useEffect(() => {
        if (selectedTarifario) {
            if (String(row.id_contrato ?? '') !== String(selectedTarifario.id_contrato ?? '')) {
                onChange('id_contrato', String(selectedTarifario.id_contrato ?? ''));
            }
            return;
        }

        if (defaultTarifario) {
            if (String(row.id_tarifario ?? '') !== String(defaultTarifario.id)) {
                onChange('id_tarifario', String(defaultTarifario.id));
            }
            if (String(row.id_contrato ?? '') !== String(defaultTarifario.id_contrato ?? '')) {
                onChange('id_contrato', String(defaultTarifario.id_contrato ?? ''));
            }
            return;
        }

        if (availableTarifarios.length === 1) {
            const [onlyOption] = availableTarifarios;

            if (String(row.id_tarifario ?? '') !== String(onlyOption.id)) {
                onChange('id_tarifario', String(onlyOption.id));
            }
            if (String(row.id_contrato ?? '') !== String(onlyOption.id_contrato ?? '')) {
                onChange('id_contrato', String(onlyOption.id_contrato ?? ''));
            }
            return;
        }

        if (String(row.id_tarifario ?? '').trim() !== '') {
            onChange('id_tarifario', '');
        }
        if (String(row.id_contrato ?? '').trim() !== '') {
            onChange('id_contrato', '');
        }
    }, [availableTarifarios, defaultTarifario, onChange, row.id_contrato, row.id_tarifario, selectedTarifario]);

    return (
        <tr className="border-y border-(--ciete-red)/30 bg-(--ciete-red)/[0.06] align-middle shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]">
            <td className="whitespace-nowrap px-3 py-3 align-top">
                <span className="inline-flex rounded-md border border-border bg-surface px-3 py-2 text-[11px] font-medium text-text-hint">
                    Se generará automáticamente
                </span>
            </td>
            <td className="px-3 py-3 align-top">
                <SearchableInlineSelect
                    value={row.id_estacion_servicio}
                    options={stationOptions}
                    onSelect={(nextValue) => onChange('id_estacion_servicio', nextValue)}
                    disabled={isSaving || estaciones.length === 0}
                    error={errors?.id_estacion_servicio}
                    selectedLabel={selectedStation ? stationLabel(selectedStation) : ''}
                    placeholder={estaciones.length === 0 ? 'Sin estaciones' : 'Buscar nº estación'}
                    searchPlaceholder="Nº o nombre de estación"
                    emptyLabel="No hay estaciones con esa búsqueda"
                    className="min-w-[12rem]"
                />
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
                {categoryCatalogOptions.length > 0 ? (
                    <SearchableInlineSelect
                        value={isRepsol ? row.id_tipo_trabajo : row.categoria}
                        options={categoryCatalogOptions}
                        onSelect={(nextValue) => {
                            if (isRepsol) {
                                const selectedOption = availableWorkTypes.find((item) => String(item.id) === String(nextValue));
                                onChange('id_tipo_trabajo', nextValue);
                                onChange('id_tipo_documento', selectedOption?.id_tipo_documento ? String(selectedOption.id_tipo_documento) : '');
                                return;
                            }

                            onChange('categoria', nextValue);
                        }}
                        disabled={isSaving}
                        error={categoryError}
                        selectedLabel={isRepsol
                            ? (categoryCatalogOptions.find((option) => String(option.value) === String(row.id_tipo_trabajo))?.label ?? '')
                            : (row.categoria || '')}
                        placeholder="Buscar categoría"
                        searchPlaceholder="Código o nombre"
                        emptyLabel="No hay categorías con esa búsqueda"
                        className="min-w-[12rem]"
                    />
                ) : (
                    <span
                        className="inline-flex min-h-11 min-w-[12rem] items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-medium leading-5 text-amber-900"
                        title="Este contexto no tiene catálogo real de categorías/tipos activo."
                    >
                        Catálogo pendiente en este contexto
                    </span>
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
            <td className="px-3 py-3 align-top">
                <SearchableInlineSelect
                    value={row.id_tarifario}
                    options={tarifarioOptions}
                    onSelect={(nextValue) => {
                        const selectedOption = availableTarifarios.find((item) => String(item.id) === String(nextValue));
                        onChange('id_tarifario', nextValue);
                        onChange('id_contrato', selectedOption?.id_contrato ? String(selectedOption.id_contrato) : '');
                    }}
                    disabled={isSaving || !selectedStation || tarifarioOptions.length === 0}
                    error={tarifaError}
                    selectedLabel={selectedTarifario ? tarifarioOptionLabel(selectedTarifario) : ''}
                    placeholder={!selectedStation
                        ? 'Selecciona estación primero'
                        : tarifarioOptions.length === 0
                            ? 'Sin tarifario'
                            : 'Seleccionar tarifario'}
                    searchPlaceholder="Código, contrato o tarifario"
                    emptyLabel="No hay tarifarios con esa búsqueda"
                    className="min-w-[15rem]"
                />
            </td>
            <td className="px-3 py-3 align-top text-text-muted">Sin pedidos</td>
            <td className="px-3 py-3 align-top">
                <button
                    type="button"
                    onClick={onSaveAndCreatePedido}
                    disabled={isSaving}
                    className={`rounded-md border px-3 py-2 text-xs font-semibold transition ${
                        isSaving
                            ? 'cursor-not-allowed border-border bg-surface-2 text-text-hint'
                            : 'border-border bg-surface text-text-main hover:bg-surface-2 hover:text-(--ciete-red)'
                    }`}
                >
                    {isSaving ? 'Guardando...' : pedidoActionLabel}
                </button>
            </td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 text-right align-top text-text-hint">-</td>
            <td className="px-3 py-3 align-top">
                <select
                    value={row.estado}
                    onChange={(event) => onChange('estado', event.target.value)}
                    className={`${cellInput('estado')} pr-10`}
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
                    className={`${cellInput('id_responsable_ciete')} pr-10`}
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
    const safeFilters = filters && typeof filters === 'object' ? filters : {};
    const safeTrabajos = Array.isArray(trabajos) ? trabajos : [];
    const activeContext = auth?.user?.active_context;
    const estaciones = creationCatalogs?.estaciones ?? [];
    const contratos = creationCatalogs?.contratos ?? [];
    const tarifarios = creationCatalogs?.tarifarios ?? [];
    const tiposDocumento = creationCatalogs?.tiposDocumento ?? [];
    const tiposTrabajo = creationCatalogs?.tiposTrabajo ?? [];
    const canCreateInContext = canCreate && !activeContext?.is_all;
    const canCreatePedidos = hasPermission(auth?.user, 'pedidos.crear') && !activeContext?.is_all;
    const canEditPedidos = hasPermission(auth?.user, 'pedidos.editar');
    const [rows, setRows] = useState(safeTrabajos);
    const [search, setSearch] = useState(safeFilters.search ?? '');
    const [estado, setEstado] = useState(safeFilters.estado ?? '');
    const [fechaDesde, setFechaDesde] = useState(safeFilters.fecha_desde ?? '');
    const [fechaHasta, setFechaHasta] = useState(safeFilters.fecha_hasta ?? '');
    const [municipio, setMunicipio] = useState(safeFilters.municipio ?? '');
    const [provincia, setProvincia] = useState(safeFilters.provincia ?? '');
    const [codigoEstacion, setCodigoEstacion] = useState(safeFilters.codigo_estacion ?? '');
    const [responsableId, setResponsableId] = useState(safeFilters.id_responsable_ciete ?? '');
    const [pedidoNumero, setPedidoNumero] = useState(safeFilters.pedido_numero ?? '');
    const [tarifarioId, setTarifarioId] = useState(safeFilters.id_tarifario ?? '');
    const [contratoId, setContratoId] = useState(safeFilters.id_contrato ?? '');
    const [estacionId, setEstacionId] = useState(safeFilters.id_estacion_servicio ?? '');
    const [categoria, setCategoria] = useState(safeFilters.categoria ?? '');
    const [hasPedidos, setHasPedidos] = useState(safeFilters.has_pedidos ?? '');
    const [multiPedido, setMultiPedido] = useState(safeFilters.multi_pedido ?? '');
    const [pedidoImporte, setPedidoImporte] = useState(safeFilters.pedido_importe ?? '');
    const [facturado, setFacturado] = useState(safeFilters.facturado ?? '');
    const [solicitado, setSolicitado] = useState(safeFilters.solicitado ?? '');
    const [sortField, setSortField] = useState(safeFilters.sort ?? '');
    const [sortDirection, setSortDirection] = useState(safeFilters.direction ?? '');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(() => Boolean(
        safeFilters.pedido_numero
        || safeFilters.id_tarifario
        || safeFilters.id_contrato
        || safeFilters.id_estacion_servicio
        || safeFilters.categoria
        || safeFilters.has_pedidos
        || safeFilters.multi_pedido
        || safeFilters.pedido_importe
        || safeFilters.facturado
        || safeFilters.solicitado
    ));
    const [newRow, setNewRow] = useState(null);
    const [newRowErrors, setNewRowErrors] = useState({});
    const [newRowMessage, setNewRowMessage] = useState('');
    const [isCreating, setIsCreating] = useState(false);

    useEffect(() => {
        setRows(safeTrabajos);
    }, [safeTrabajos]);

    useEffect(() => {
        setSortField(safeFilters.sort ?? '');
        setSortDirection(safeFilters.direction ?? '');
    }, [safeFilters.sort, safeFilters.direction]);

    useEffect(() => {
        const timer = window.setTimeout(() => {
            if (search !== (safeFilters.search ?? '')) {
                doFilter({ search });
            }
        }, 400);

        return () => window.clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    const hasServerSort = Boolean(sortField && sortDirection);

    const orderedRows = useMemo(() => {
        if (hasServerSort) return rows;

        return [...rows].sort((a, b) => {
            if (a.estado === 'cancelado' && b.estado !== 'cancelado') return 1;
            if (a.estado !== 'cancelado' && b.estado === 'cancelado') return -1;
            return (ESTADO_ORDER[a.estado] ?? 99) - (ESTADO_ORDER[b.estado] ?? 99);
        });
    }, [hasServerSort, rows]);

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
            pedido_numero: overrides.pedido_numero !== undefined ? overrides.pedido_numero : pedidoNumero,
            id_tarifario: overrides.id_tarifario !== undefined ? overrides.id_tarifario : tarifarioId,
            id_contrato: overrides.id_contrato !== undefined ? overrides.id_contrato : contratoId,
            id_estacion_servicio: overrides.id_estacion_servicio !== undefined ? overrides.id_estacion_servicio : estacionId,
            categoria: overrides.categoria !== undefined ? overrides.categoria : categoria,
            has_pedidos: overrides.has_pedidos !== undefined ? overrides.has_pedidos : hasPedidos,
            multi_pedido: overrides.multi_pedido !== undefined ? overrides.multi_pedido : multiPedido,
            pedido_importe: overrides.pedido_importe !== undefined ? overrides.pedido_importe : pedidoImporte,
            facturado: overrides.facturado !== undefined ? overrides.facturado : facturado,
            solicitado: overrides.solicitado !== undefined ? overrides.solicitado : solicitado,
            sort: overrides.sort !== undefined ? overrides.sort : sortField,
            direction: overrides.direction !== undefined ? overrides.direction : sortDirection,
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
        setPedidoNumero('');
        setTarifarioId('');
        setContratoId('');
        setEstacionId('');
        setCategoria('');
        setHasPedidos('');
        setMultiPedido('');
        setPedidoImporte('');
        setFacturado('');
        setSolicitado('');
        aplicarFiltros({
            search: '',
            estado: '',
            fecha_desde: '',
            fecha_hasta: '',
            municipio: '',
            provincia: '',
            codigo_estacion: '',
            id_responsable_ciete: '',
            pedido_numero: '',
            id_tarifario: '',
            id_contrato: '',
            id_estacion_servicio: '',
            categoria: '',
            has_pedidos: '',
            multi_pedido: '',
            pedido_importe: '',
            facturado: '',
            solicitado: '',
            sort: sortField,
            direction: sortDirection,
        });
    }

    function toggleSort(nextField) {
        let nextSort = nextField;
        let nextDirection = 'asc';

        if (sortField === nextField) {
            if (sortDirection === 'asc') {
                nextDirection = 'desc';
            } else if (sortDirection === 'desc') {
                nextSort = '';
                nextDirection = '';
            }
        }

        setSortField(nextSort);
        setSortDirection(nextDirection);
        doFilter({
            sort: nextSort,
            direction: nextDirection,
            page: 1,
        });
    }

    function clearSingleFilter(key) {
        switch (key) {
        case 'search':
            setSearch('');
            doFilter({ search: '' });
            break;
        case 'estado':
            setEstado('');
            doFilter({ estado: '' });
            break;
        case 'fecha_desde':
            setFechaDesde('');
            doFilter({ fecha_desde: '' });
            break;
        case 'fecha_hasta':
            setFechaHasta('');
            doFilter({ fecha_hasta: '' });
            break;
        case 'municipio':
            setMunicipio('');
            doFilter({ municipio: '' });
            break;
        case 'provincia':
            setProvincia('');
            doFilter({ provincia: '' });
            break;
        case 'codigo_estacion':
            setCodigoEstacion('');
            doFilter({ codigo_estacion: '' });
            break;
        case 'id_responsable_ciete':
            setResponsableId('');
            doFilter({ id_responsable_ciete: '' });
            break;
        case 'pedido_numero':
            setPedidoNumero('');
            doFilter({ pedido_numero: '' });
            break;
        case 'id_tarifario':
            setTarifarioId('');
            doFilter({ id_tarifario: '' });
            break;
        case 'id_contrato':
            setContratoId('');
            doFilter({ id_contrato: '' });
            break;
        case 'id_estacion_servicio':
            setEstacionId('');
            doFilter({ id_estacion_servicio: '' });
            break;
        case 'categoria':
            setCategoria('');
            doFilter({ categoria: '' });
            break;
        case 'has_pedidos':
            setHasPedidos('');
            doFilter({ has_pedidos: '' });
            break;
        case 'multi_pedido':
            setMultiPedido('');
            doFilter({ multi_pedido: '' });
            break;
        case 'pedido_importe':
            setPedidoImporte('');
            doFilter({ pedido_importe: '' });
            break;
        case 'facturado':
            setFacturado('');
            doFilter({ facturado: '' });
            break;
        case 'solicitado':
            setSolicitado('');
            doFilter({ solicitado: '' });
            break;
        default:
            break;
        }
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

    function validateNewRow({ requireTarifario = false } = {}) {
        const errors = {};
        const allowedStates = new Set(ESTADO_OPTIONS.map((option) => option.value));

        if (!String(newRow?.id_estacion_servicio ?? '').trim()) errors.id_estacion_servicio = 'La estación es obligatoria.';
        if (!String(newRow?.descripcion_trabajo ?? '').trim()) errors.descripcion_trabajo = 'La descripción es obligatoria.';
        if (!String(newRow?.fecha_encargo ?? '').trim()) errors.fecha_encargo = 'La fecha de encargo es obligatoria.';
        if (newRow?.estado && !allowedStates.has(newRow.estado)) errors.estado = 'Estado no válido.';
        if (isMoeveContext(activeContext) && !String(newRow?.id_tarifario ?? '').trim()) errors.id_tarifario = 'Selecciona tarifario para MOEVE.';
        if (requireTarifario && !String(newRow?.id_tarifario ?? '').trim()) errors.id_tarifario = 'Selecciona tarifario antes de crear el pedido.';
        if (isRepsolContext(activeContext) && !String(newRow?.id_tipo_documento ?? '').trim()) errors.id_tipo_documento = 'El tipo documental es obligatorio para REPSOL.';
        if (isRepsolContext(activeContext) && !String(newRow?.id_tipo_trabajo ?? '').trim()) errors.id_tipo_trabajo = 'El tipo de trabajo es obligatorio para REPSOL.';

        return errors;
    }

    function buildNewRowPayload() {
        return {
            id_contexto: activeContext?.id_contexto ? Number(activeContext.id_contexto) : undefined,
            numero_trabajo: String(newRow.numero_trabajo ?? '').trim() !== '' ? Number(newRow.numero_trabajo) : undefined,
            numero_trabajo_operativo: String(newRow.numero_trabajo_operativo ?? '').trim() || undefined,
            id_estacion_servicio: Number(newRow.id_estacion_servicio),
            descripcion_trabajo: String(newRow.descripcion_trabajo).trim(),
            estado: newRow.estado,
            fecha_encargo: newRow.fecha_encargo,
            fecha_terminacion: newRow.fecha_terminacion || null,
            observaciones: newRow.observaciones || null,
            id_responsable_ciete: newRow.id_responsable_ciete ? Number(newRow.id_responsable_ciete) : null,
            id_contrato: newRow.id_contrato ? Number(newRow.id_contrato) : null,
            id_tarifario: newRow.id_tarifario ? Number(newRow.id_tarifario) : null,
            categoria: !isRepsolContext(activeContext) && newRow.categoria ? String(newRow.categoria).trim() : null,
            id_tipo_documento: isRepsolContext(activeContext) && newRow.id_tipo_documento ? Number(newRow.id_tipo_documento) : null,
            id_tipo_trabajo: isRepsolContext(activeContext) && newRow.id_tipo_trabajo ? Number(newRow.id_tipo_trabajo) : null,
        };
    }

    async function saveNewRow({ openPedidoAfterSave = false } = {}) {
        if (!newRow || isCreating) return;

        const errors = validateNewRow({ requireTarifario: openPedidoAfterSave });
        if (Object.keys(errors ?? {}).length > 0) {
            setNewRowErrors(errors);
            setNewRowMessage(
                openPedidoAfterSave
                    ? 'Completa los campos obligatorios y selecciona tarifario antes de crear el pedido.'
                    : 'Completa los campos obligatorios antes de guardar.'
            );
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
            if (openPedidoAfterSave && createdTrabajo?.id_trabajo) {
                setNewRowMessage('Trabajo creado correctamente. Abriendo pedido...');
                router.visit(pedidoCreateFromTrabajoUrl(createdTrabajo));
                return createdTrabajo;
            }

            setNewRowMessage('Trabajo creado correctamente.');
            return createdTrabajo;
        } catch (error) {
            const data = error.response?.data ?? {};
            setNewRowErrors(data.errors ?? {});
            setNewRowMessage(
                data.message
                    ?? Object.values(data.errors ?? {})?.flat()?.[0]
                    ?? 'No se pudo crear el trabajo.'
            );
            return null;
        } finally {
            setIsCreating(false);
        }
    }

    function cancelNewRow() {
        if (!newRow) return;

        const hasDraft = Object.entries(newRow ?? {}).some(([key, value]) => (
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

    const hasFilters = Boolean(
        search
        || estado
        || fechaDesde
        || fechaHasta
        || municipio
        || provincia
        || codigoEstacion
        || responsableId
        || pedidoNumero
        || tarifarioId
        || contratoId
        || estacionId
        || categoria
        || hasPedidos
        || multiPedido
        || pedidoImporte
        || facturado
        || solicitado
    );
    const selectClassName = 'h-9 min-w-[11rem] rounded-md border border-border bg-surface px-3 pr-9 text-sm text-text-main outline-none transition focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)';
    const compactInputClassName = 'h-9 rounded-md border border-border bg-surface px-3 text-sm text-text-main outline-none transition placeholder:text-text-hint focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)';
    const activeFilterChips = [
        search ? { key: 'search', label: `Buscar: ${search}` } : null,
        estado ? { key: 'estado', label: `Estado: ${ESTADO_LABEL[estado] ?? estado}` } : null,
        responsableId ? {
            key: 'id_responsable_ciete',
            label: `Responsable: ${responsableName(responsables, responsableId) ?? responsableId}`,
        } : null,
        fechaDesde ? { key: 'fecha_desde', label: `Desde: ${fechaDesde}` } : null,
        fechaHasta ? { key: 'fecha_hasta', label: `Hasta: ${fechaHasta}` } : null,
        codigoEstacion ? { key: 'codigo_estacion', label: `Nº estación: ${codigoEstacion}` } : null,
        municipio ? { key: 'municipio', label: `Municipio: ${municipio}` } : null,
        provincia ? { key: 'provincia', label: `Provincia: ${provincia}` } : null,
        pedidoNumero ? { key: 'pedido_numero', label: `Pedido: ${pedidoNumero}` } : null,
        contratoId ? {
            key: 'id_contrato',
            label: `Contrato: ${contractOptionLabel(contratos.find((item) => String(item.id) === String(contratoId))) ?? contratoId}`,
        } : null,
        tarifarioId ? {
            key: 'id_tarifario',
            label: `Tarifario: ${tarifarioOptionLabel(tarifarios.find((item) => String(item.id) === String(tarifarioId))) ?? tarifarioId}`,
        } : null,
        estacionId ? {
            key: 'id_estacion_servicio',
            label: `Estación: ${stationLabel(estaciones.find((item) => String(item.id) === String(estacionId))) ?? estacionId}`,
        } : null,
        categoria ? { key: 'categoria', label: `Categoría de trabajo: ${categoria}` } : null,
        hasPedidos === '1' ? { key: 'has_pedidos', label: 'Con pedidos' } : null,
        hasPedidos === '0' ? { key: 'has_pedidos', label: 'Sin pedidos' } : null,
        multiPedido === '1' ? { key: 'multi_pedido', label: 'Con varios pedidos' } : null,
        multiPedido === '0' ? { key: 'multi_pedido', label: 'Sin varios pedidos' } : null,
        pedidoImporte === '1' ? { key: 'pedido_importe', label: 'Con importe pedido' } : null,
        pedidoImporte === '0' ? { key: 'pedido_importe', label: 'Sin importe pedido' } : null,
        facturado === '1' ? { key: 'facturado', label: 'Facturado' } : null,
        facturado === '0' ? { key: 'facturado', label: 'No facturado' } : null,
        solicitado === '1' ? { key: 'solicitado', label: 'Solicitado' } : null,
        solicitado === '0' ? { key: 'solicitado', label: 'No solicitado' } : null,
    ].filter(Boolean);

    return (
        <div className="space-y-3">
            <div className="space-y-3 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="mr-2 flex min-h-9 items-center rounded-lg border border-border bg-surface-2 px-3">
                        <WorkspaceContextIndicator compact />
                        {activeContext?.is_all && (
                            <span className="ml-2 text-[11px] font-medium text-text-hint">vista global operativa</span>
                        )}
                    </div>

                    <div className="min-w-[18rem] flex-1">
                        <input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            onKeyDown={(event) => event.key === 'Enter' && doFilter({ search })}
                            placeholder="Buscar nº trabajo, estación, pedido, contrato, tarifario, responsable o importe"
                            className={`${compactInputClassName} w-full`}
                        />
                    </div>

                    <select
                        value={estado}
                        onChange={(event) => {
                            setEstado(event.target.value);
                            doFilter({ estado: event.target.value });
                        }}
                        className={selectClassName}
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
                            className={`${selectClassName} max-w-64`}
                        >
                            <option value="">Todos los responsables</option>
                            {responsables.map((responsable) => (
                                <option key={responsable.id} value={responsable.id}>{responsable.nombre}</option>
                            ))}
                        </select>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <input
                        type="date"
                        value={fechaDesde}
                        onChange={(event) => {
                            setFechaDesde(event.target.value);
                            doFilter({ fecha_desde: event.target.value });
                        }}
                        title="Fecha desde"
                        className={compactInputClassName}
                    />
                    <input
                        type="date"
                        value={fechaHasta}
                        onChange={(event) => {
                            setFechaHasta(event.target.value);
                            doFilter({ fecha_hasta: event.target.value });
                        }}
                        title="Fecha hasta"
                        className={compactInputClassName}
                    />
                    <input
                        type="search"
                        value={codigoEstacion}
                        onChange={(event) => setCodigoEstacion(event.target.value)}
                        onKeyDown={(event) => event.key === 'Enter' && doFilter({ codigo_estacion: codigoEstacion })}
                        placeholder="Nº estación"
                        className={`${compactInputClassName} w-36`}
                    />
                    <input
                        type="search"
                        value={municipio}
                        onChange={(event) => setMunicipio(event.target.value)}
                        onKeyDown={(event) => event.key === 'Enter' && doFilter({ municipio })}
                        placeholder="Municipio"
                        className={`${compactInputClassName} w-36`}
                    />
                    <input
                        type="search"
                        value={provincia}
                        onChange={(event) => setProvincia(event.target.value)}
                        onKeyDown={(event) => event.key === 'Enter' && doFilter({ provincia })}
                        placeholder="Provincia"
                        className={`${compactInputClassName} w-36`}
                    />

                    <button
                        type="button"
                        onClick={() => setShowAdvancedFilters((current) => !current)}
                        className="h-9 rounded-md border border-border px-3 text-xs font-semibold text-text-muted transition hover:bg-surface-2 hover:text-text-main"
                    >
                        {showAdvancedFilters ? 'Ocultar filtros avanzados' : 'Filtros avanzados'}
                    </button>

                    {hasFilters && (
                        <button
                            type="button"
                            onClick={clearFilters}
                            className="h-9 rounded-md border border-border px-3 text-xs font-medium text-text-muted transition hover:bg-surface-2 hover:text-text-main"
                        >
                            Limpiar filtros
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
                            className={`h-9 rounded-md px-3 text-xs font-semibold transition ${
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

                {showAdvancedFilters && (
                    <div className="grid gap-2 border-t border-border pt-3 sm:grid-cols-2 xl:grid-cols-4">
                        {contratos.length > 0 && (
                            <select
                                value={contratoId}
                                onChange={(event) => {
                                    setContratoId(event.target.value);
                                    doFilter({ id_contrato: event.target.value });
                                }}
                                className={selectClassName}
                            >
                                <option value="">Contrato</option>
                                {contratos.map((contrato) => (
                                    <option key={contrato.id} value={contrato.id}>
                                        {contractOptionLabel(contrato)}
                                    </option>
                                ))}
                            </select>
                        )}

                        {tarifarios.length > 0 && (
                            <select
                                value={tarifarioId}
                                onChange={(event) => {
                                    setTarifarioId(event.target.value);
                                    doFilter({ id_tarifario: event.target.value });
                                }}
                                className={selectClassName}
                            >
                                <option value="">Tarifario</option>
                                {tarifarios.map((tarifario) => (
                                    <option key={tarifario.id} value={tarifario.id}>
                                        {tarifarioOptionLabel(tarifario)}
                                    </option>
                                ))}
                            </select>
                        )}

                        <input
                            type="search"
                            value={pedidoNumero}
                            onChange={(event) => setPedidoNumero(event.target.value)}
                            onKeyDown={(event) => event.key === 'Enter' && doFilter({ pedido_numero: pedidoNumero })}
                            placeholder="Número de pedido"
                            className={compactInputClassName}
                        />

                        <select
                            value={hasPedidos}
                            onChange={(event) => {
                                setHasPedidos(event.target.value);
                                doFilter({ has_pedidos: event.target.value });
                            }}
                            className={selectClassName}
                        >
                            <option value="">Pedidos: todos</option>
                            <option value="1">Con pedidos</option>
                            <option value="0">Sin pedidos</option>
                        </select>

                        <select
                            value={multiPedido}
                            onChange={(event) => {
                                setMultiPedido(event.target.value);
                                doFilter({ multi_pedido: event.target.value });
                            }}
                            className={selectClassName}
                        >
                            <option value="">Multipedido: todos</option>
                            <option value="1">Con varios pedidos</option>
                            <option value="0">Sin varios pedidos</option>
                        </select>

                        <select
                            value={pedidoImporte}
                            onChange={(event) => {
                                setPedidoImporte(event.target.value);
                                doFilter({ pedido_importe: event.target.value });
                            }}
                            className={selectClassName}
                        >
                            <option value="">Importe pedido: todos</option>
                            <option value="1">Con importe pedido</option>
                            <option value="0">Sin importe pedido</option>
                        </select>

                        <select
                            value={facturado}
                            onChange={(event) => {
                                setFacturado(event.target.value);
                                doFilter({ facturado: event.target.value });
                            }}
                            className={selectClassName}
                        >
                            <option value="">Facturación: todos</option>
                            <option value="1">Facturado</option>
                            <option value="0">No facturado</option>
                        </select>

                        <select
                            value={solicitado}
                            onChange={(event) => {
                                setSolicitado(event.target.value);
                                doFilter({ solicitado: event.target.value });
                            }}
                            className={selectClassName}
                        >
                            <option value="">Solicitud: todos</option>
                            <option value="1">Solicitado</option>
                            <option value="0">No solicitado</option>
                        </select>

                        {estaciones.length > 0 && (
                            <select
                                value={estacionId}
                                onChange={(event) => {
                                    setEstacionId(event.target.value);
                                    doFilter({ id_estacion_servicio: event.target.value });
                                }}
                                className={selectClassName}
                            >
                                <option value="">Estación</option>
                                {estaciones.map((estacion) => (
                                    <option key={estacion.id} value={estacion.id}>
                                        {stationLabel(estacion)}
                                    </option>
                                ))}
                            </select>
                        )}

                        <input
                            type="search"
                            value={categoria}
                            onChange={(event) => setCategoria(event.target.value)}
                            onKeyDown={(event) => event.key === 'Enter' && doFilter({ categoria })}
                            placeholder="Categoría de trabajo"
                            className={compactInputClassName}
                        />
                    </div>
                )}

                {activeFilterChips.length > 0 && (
                    <div className="flex flex-wrap gap-2 border-t border-border pt-3">
                        {activeFilterChips.map((chip) => (
                            <button
                                key={chip.key}
                                type="button"
                                onClick={() => clearSingleFilter(chip.key)}
                                className="inline-flex items-center gap-2 rounded-full border border-border bg-surface-2 px-3 py-1 text-[11px] font-medium text-text-main transition hover:border-(--ciete-red) hover:text-(--ciete-red)"
                                title="Quitar filtro"
                            >
                                <span>{chip.label}</span>
                                <span className="text-text-hint">×</span>
                            </button>
                        ))}
                    </div>
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
                <table className="ciete-excel-table w-full min-w-[2400px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {TABLE_COLUMNS.map((column) => (
                                <th
                                    key={column.id}
                                    className={`${column.width} whitespace-nowrap border-b border-border/70 px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {column.sortKey ? (
                                        <button
                                            type="button"
                                            onClick={() => toggleSort(column.sortKey)}
                                            className="inline-flex w-full cursor-pointer items-center gap-1 whitespace-nowrap text-left transition hover:text-text-main"
                                        >
                                            <span>{column.label}</span>
                                            {sortField === column.sortKey && sortDirection === 'asc' && (
                                                <span aria-hidden="true" className="text-[10px] leading-none text-text-main">↑</span>
                                            )}
                                            {sortField === column.sortKey && sortDirection === 'desc' && (
                                                <span aria-hidden="true" className="text-[10px] leading-none text-text-main">↓</span>
                                            )}
                                        </button>
                                    ) : (
                                        column.label
                                    )}
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
                                onSave={() => saveNewRow()}
                                onSaveAndCreatePedido={() => saveNewRow({ openPedidoAfterSave: true })}
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
                            const internalWorkLabel = internalWorkReference(trabajo);

                            return (
                                <tr
                                    key={trabajo.id_trabajo}
                                    className={`transition hover:bg-surface-2/65 ${isCancelled ? 'bg-surface-2/35 opacity-70' : ''}`}
                                >
                                    <td className="whitespace-nowrap px-3 py-2.5 align-top font-mono font-semibold text-(--ciete-red)">
                                        <div className="grid gap-1">
                                            <EditableTextCell
                                                trabajo={trabajo}
                                                fieldName="numero_trabajo_operativo"
                                                onPatched={onPatched}
                                                canEdit={canEditRow}
                                                fallbackValue={formatWorkNumber(trabajo.numero_trabajo)}
                                                maxWidthClass="max-w-[170px]"
                                            />
                                            {internalWorkLabel && (
                                                <span
                                                    className="block max-w-[170px] truncate text-[10px] font-medium text-text-hint"
                                                    title={internalWorkLabel}
                                                >
                                                    {internalWorkLabel}
                                                </span>
                                            )}
                                        </div>
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
                                    <TrabajoPedidoFlowCells
                                            trabajo={trabajo}
                                            tarifarios={tarifarios}
                                            onPatched={onPatched}
                                            canEdit={canEditRow}
                                            canCreatePedidos={canCreatePedidos}
                                            canEditPedidos={canEditPedidos}
                                        />
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
                                                className="whitespace-nowrap rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-muted transition hover:bg-surface-2 hover:text-(--ciete-red)"
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
