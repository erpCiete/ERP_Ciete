import BadgePedido from '@/Components/ui/BadgePedidos';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

const ESTADO_OPTIONS = ['solicitado', 'recibido', 'facturado', 'cancelado'];

const ESTADO_LABEL = {
    solicitado: 'Solicitado',
    recibido:   'Recibido',
    facturado:  'Facturado',
    cancelado:  'Cancelado',
};

function fmt(val) {
    if (val === null || val === undefined) return '—';
    return val;
}

function fmtMoney(val) {
    if (val === null || val === undefined) return '—';
    const n = Number(val);
    if (isNaN(n)) return '—';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(n);
}

function fmtDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-ES');
}

function emptyNewPedido() {
    return {
        numero_pedido: '',
        id_trabajo: '',
        estado: 'solicitado',
        fecha_solicitud: new Date().toISOString().slice(0, 10),
        importe_pedido: '',
        importe_solicitado: '',
        unidades_solicitadas: '',
    };
}

function fieldError(errors, field) {
    const value = errors?.[field];

    return Array.isArray(value) ? value[0] : value;
}

function NewPedidoRow({
    row,
    trabajos,
    errors = {},
    isSaving = false,
    isRepsol = false,
    onChange,
    onSave,
    onCancel,
}) {
    const inputClass = (field) =>
        `h-7 w-full rounded border bg-surface px-2 text-xs text-text-main outline-none ${
            fieldError(errors, field)
                ? 'border-red-300 ring-1 ring-red-200'
                : 'border-border focus:border-(--ciete-red) focus:ring-1 focus:ring-(--ciete-red)'
        }`;

    return (
        <tr className="bg-(--ciete-red)/[0.035]">
            <td className="whitespace-nowrap px-2 py-1.5 align-middle">
                <input
                    value={row.numero_pedido}
                    onChange={(event) => onChange('numero_pedido', event.target.value)}
                    placeholder="N pedido"
                    className={`${inputClass('numero_pedido')} font-mono font-semibold text-(--ciete-red)`}
                    title={fieldError(errors, 'numero_pedido')}
                />
            </td>
            <td className="whitespace-nowrap px-2 py-1.5 align-middle" colSpan={2}>
                <select
                    value={row.id_trabajo}
                    onChange={(event) => onChange('id_trabajo', event.target.value)}
                    className={inputClass('id_trabajo')}
                    title={fieldError(errors, 'id_trabajo')}
                >
                    <option value="">Selecciona trabajo</option>
                    {trabajos.map((trabajo) => (
                        <option key={trabajo.id_trabajo} value={trabajo.id_trabajo}>
                            {String(trabajo.numero_trabajo ?? '').padStart(4, '0')} - {trabajo.descripcion_trabajo ?? 'Sin descripcion'}
                        </option>
                    ))}
                </select>
            </td>
            <td className="px-2 py-1.5 align-middle">
                <select
                    value={row.estado}
                    onChange={(event) => onChange('estado', event.target.value)}
                    className={inputClass('estado')}
                    title={fieldError(errors, 'estado')}
                >
                    {ESTADO_OPTIONS.map((option) => (
                        <option key={option} value={option}>{ESTADO_LABEL[option] ?? option}</option>
                    ))}
                </select>
            </td>
            <td className="whitespace-nowrap px-2 py-1.5 align-middle">
                <input
                    type="date"
                    value={row.fecha_solicitud}
                    onChange={(event) => onChange('fecha_solicitud', event.target.value)}
                    className={inputClass('fecha_solicitud')}
                    title={fieldError(errors, 'fecha_solicitud')}
                />
            </td>
            <td className="whitespace-nowrap px-2 py-1.5 align-middle">
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    value={row.importe_pedido}
                    onChange={(event) => onChange('importe_pedido', event.target.value)}
                    placeholder="0.00"
                    className={`${inputClass('importe_pedido')} text-right`}
                    title={fieldError(errors, 'importe_pedido')}
                />
            </td>
            {isRepsol && (
                <>
                    <td className="whitespace-nowrap px-2 py-1.5 align-middle">
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            value={row.importe_solicitado}
                            onChange={(event) => onChange('importe_solicitado', event.target.value)}
                            placeholder="0.00"
                            className={`${inputClass('importe_solicitado')} text-right`}
                            title={fieldError(errors, 'importe_solicitado')}
                        />
                    </td>
                    <td className="whitespace-nowrap px-2 py-1.5 align-middle">
                        <input
                            type="number"
                            min="0"
                            step="1"
                            value={row.unidades_solicitadas}
                            onChange={(event) => onChange('unidades_solicitadas', event.target.value)}
                            placeholder="0"
                            className={`${inputClass('unidades_solicitadas')} text-right`}
                            title={fieldError(errors, 'unidades_solicitadas')}
                        />
                    </td>
                </>
            )}
            <td className="px-2 py-1.5 align-middle">
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={onSave}
                        disabled={isSaving}
                        className="rounded bg-(--ciete-red) px-2 py-1 text-xs font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-50"
                    >
                        {isSaving ? 'Guardando' : 'Guardar'}
                    </button>
                    <button
                        type="button"
                        onClick={onCancel}
                        disabled={isSaving}
                        className="rounded border border-border px-2 py-1 text-xs font-medium text-text-muted transition hover:bg-surface-2 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                </div>
            </td>
        </tr>
    );
}

export default function PedidosExcelView({
    pedidos = [],
    filters = {},
    aplicarFiltros,
    canCreate = true,
    canEdit = true,
    canDelete = true,
    pagination = null,
    isRepsol = false,
    trabajos: initialTrabajos = [],
}) {
    const [search,     setSearch]     = useState(filters.search      ?? '');
    const [estado,     setEstado]     = useState(filters.estado       ?? '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde  ?? '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta  ?? '');
    const [trabajos, setTrabajos] = useState(initialTrabajos);
    const [newRow, setNewRow] = useState(null);
    const [newRowErrors, setNewRowErrors] = useState({});
    const [isCreating, setIsCreating] = useState(false);
    const [newRowMessage, setNewRowMessage] = useState('');

    useEffect(() => {
        setTrabajos(initialTrabajos);
    }, [initialTrabajos]);

    function doFilter(overrides = {}) {
        aplicarFiltros({
            search:      overrides.search      !== undefined ? overrides.search      : search,
            estado:      overrides.estado      !== undefined ? overrides.estado      : estado,
            fecha_desde: overrides.fecha_desde !== undefined ? overrides.fecha_desde : fechaDesde,
            fecha_hasta: overrides.fecha_hasta !== undefined ? overrides.fecha_hasta : fechaHasta,
            ...(overrides.page !== undefined ? { page: overrides.page } : {}),
        });
    }

    const hasDraft = useMemo(() => {
        if (!newRow) return false;

        return Object.values(newRow).some((value) => String(value ?? '').trim() !== '');
    }, [newRow]);

    function handleCreate() {
        setNewRow(emptyNewPedido());
        setNewRowErrors({});
        setNewRowMessage('');
    }

    function updateNewRow(field, value) {
        setNewRow((current) => ({ ...current, [field]: value }));
        setNewRowErrors((current) => {
            if (!current[field]) return current;
            const next = { ...current };
            delete next[field];
            return next;
        });
    }

    function validateNewRow() {
        const errors = {};

        if (!String(newRow?.numero_pedido ?? '').trim()) errors.numero_pedido = 'El numero de pedido es obligatorio.';
        if (!String(newRow?.id_trabajo ?? '').trim()) errors.id_trabajo = 'El trabajo es obligatorio.';

        return errors;
    }

    function buildNewRowPayload() {
        return {
            id_trabajo: Number(newRow.id_trabajo),
            numero_pedido: String(newRow.numero_pedido).trim(),
            estado: newRow.estado || 'solicitado',
            fecha_solicitud: newRow.fecha_solicitud || null,
            importe_pedido: Number(newRow.importe_pedido || 0),
            importe_solicitado: Number(newRow.importe_solicitado || 0),
            unidades_solicitadas: Number(newRow.unidades_solicitadas || 0),
        };
    }

    async function saveNewRow() {
        if (!newRow || isCreating) return;

        const errors = validateNewRow();
        if (Object.keys(errors).length > 0) {
            setNewRowErrors(errors);
            return;
        }

        setIsCreating(true);
        setNewRowErrors({});
        setNewRowMessage('');

        try {
            await axios.post('/api/v1/pedidos', buildNewRowPayload(), {
                headers: { Accept: 'application/json' },
            });
            setNewRow(null);
            setNewRowMessage('Pedido creado correctamente.');
            doFilter({ page: 1 });
        } catch (error) {
            setNewRowErrors(error.response?.data?.errors ?? {});
            setNewRowMessage(error.response?.data?.message ?? 'No se pudo crear el pedido.');
        } finally {
            setIsCreating(false);
        }
    }

    function cancelNewRow() {
        if (hasDraft && !window.confirm('Descartar el pedido nuevo sin guardar?')) return;

        setNewRow(null);
        setNewRowErrors({});
        setNewRowMessage('');
    }

    const columns = [
        { label: 'Nº Pedido',     key: 'numero_pedido',     width: 'w-28' },
        { label: 'Trabajo',       key: 'trabajo',            width: 'w-20' },
        { label: 'Cód. estación', key: 'codigo_estacion',   width: 'w-24' },
        { label: 'Estado',        key: 'estado',             width: 'w-32' },
        { label: 'F. solicitud',  key: 'fecha_solicitud',    width: 'w-24' },
        { label: 'Importe',       key: 'total',              width: 'w-28' },
        ...(isRepsol ? [
            { label: 'Imp. solit.', key: 'importe_solicitado', width: 'w-28' },
            { label: 'Uds.',        key: 'unidades_solicitadas', width: 'w-16' },
        ] : []),
        { label: 'Acciones',      key: '_acciones',          width: 'w-20' },
    ];

    return (
        <div className="space-y-3">
            {/* Barra de filtros compacta */}
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && doFilter({ search })}
                    placeholder="Buscar pedido…"
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />
                <select
                    value={estado}
                    onChange={(e) => { setEstado(e.target.value); doFilter({ estado: e.target.value }); }}
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                >
                    <option value="">Todos los estados</option>
                    {ESTADO_OPTIONS.map((op) => (
                        <option key={op} value={op}>{ESTADO_LABEL[op] ?? op}</option>
                    ))}
                </select>
                <input
                    type="date"
                    value={fechaDesde}
                    onChange={(e) => { setFechaDesde(e.target.value); doFilter({ fecha_desde: e.target.value }); }}
                    title="Desde"
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                />
                <input
                    type="date"
                    value={fechaHasta}
                    onChange={(e) => { setFechaHasta(e.target.value); doFilter({ fecha_hasta: e.target.value }); }}
                    title="Hasta"
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                />
                {canCreate && (
                    <button
                        type="button"
                        onClick={handleCreate}
                        disabled={Boolean(newRow)}
                        className={`ml-auto rounded px-3 py-1 text-xs font-semibold transition ${
                            newRow
                                ? 'border border-border bg-surface-2 text-text-hint'
                                : 'bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)'
                        } disabled:cursor-not-allowed`}
                    >
                        {newRow ? 'Pedido nuevo en edicion' : '+ Nuevo pedido'}
                    </button>
                )}
            </div>

            {newRowMessage && (
                <div className={`rounded-lg border px-3 py-2 text-sm ${
                    newRowMessage === 'Pedido creado correctamente.'
                        ? 'border-green-200 bg-green-50 text-green-800'
                        : 'border-amber-200 bg-amber-50 text-amber-800'
                }`}>
                    {newRowMessage}
                </div>
            )}

            {/* Tabla densa */}
            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="w-full min-w-[700px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    className={`${col.width} whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {col.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {newRow && (
                            <NewPedidoRow
                                row={newRow}
                                trabajos={trabajos}
                                errors={newRowErrors}
                                isSaving={isCreating}
                                isRepsol={isRepsol}
                                onChange={updateNewRow}
                                onSave={saveNewRow}
                                onCancel={cancelNewRow}
                            />
                        )}
                        {pedidos.length === 0 && !newRow && (
                            <tr>
                                <td colSpan={columns.length} className="px-3 py-10 text-center text-text-hint">
                                    No hay pedidos con los filtros aplicados.
                                </td>
                            </tr>
                        )}
                        {pedidos.map((pedido) => {
                            const isCancelled = pedido.estado === 'cancelado';
                            const fechaSol = pedido.fecha_solicitud_pedido || pedido.fecha_solicitud;
                            return (
                                <tr
                                    key={pedido.id_pedido}
                                    className={`hover:bg-surface-2/50 ${isCancelled ? 'opacity-50' : ''}`}
                                >
                                    {/* Nº pedido */}
                                    <td className="whitespace-nowrap px-2 py-1.5 font-mono font-semibold text-(--ciete-red)">
                                        {fmt(pedido.numero_pedido)}
                                    </td>

                                    {/* Trabajo vinculado */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        {pedido.trabajo ? (
                                            <span className="font-mono font-semibold text-(--ciete-red)">
                                                {String(pedido.trabajo.numero_trabajo).padStart(4, '0')}
                                            </span>
                                        ) : '—'}
                                    </td>

                                    {/* Cód. estación */}
                                    <td className="whitespace-nowrap px-2 py-1.5 font-mono font-semibold text-(--ciete-red)">
                                        {pedido.trabajo?.codigo_estacion
                                            ? fmt(pedido.trabajo.codigo_estacion)
                                            : '—'}
                                    </td>

                                    {/* Estado */}
                                    <td className="px-2 py-1.5">
                                        <BadgePedido estado={pedido.estado} />
                                    </td>

                                    {/* Fecha solicitud */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        {fmtDate(fechaSol)}
                                    </td>

                                    {/* Importe total */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-right font-medium text-text-main">
                                        {fmtMoney(pedido.total ?? pedido.importe_pedido)}
                                    </td>

                                    {/* REPSOL: importe solicitado */}
                                    {isRepsol && (
                                        <>
                                            <td className="whitespace-nowrap px-2 py-1.5 text-right text-text-muted">
                                                {fmtMoney(pedido.importe_solicitado)}
                                            </td>
                                            <td className="whitespace-nowrap px-2 py-1.5 text-right text-text-muted">
                                                {fmt(pedido.unidades_solicitadas ?? 0)}
                                            </td>
                                        </>
                                    )}

                                    {/* Acciones */}
                                    <td className="px-2 py-1.5">
                                        {canEdit && (
                                            <button
                                                type="button"
                                                onClick={() => router.visit(route('pedidos.edit', pedido.id_pedido))}
                                                className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                            >
                                                Ver ficha
                                            </button>
                                        )}
                                        {canDelete && (
                                            <span className="text-[10px] text-text-hint">Cancelar desde ficha</span>
                                        )}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {/* Paginación */}
            {pagination && pagination.last_page > 1 && (
                <div className="flex items-center justify-between text-xs text-text-muted">
                    <span>Pág. {pagination.current_page} / {pagination.last_page}</span>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            disabled={pagination.current_page <= 1}
                            onClick={() => doFilter({ page: pagination.current_page - 1 })}
                            className="rounded border border-border px-3 py-1 hover:bg-surface-2 disabled:opacity-40"
                        >
                            Anterior
                        </button>
                        <button
                            type="button"
                            disabled={pagination.current_page >= pagination.last_page}
                            onClick={() => doFilter({ page: pagination.current_page + 1 })}
                            className="rounded border border-border px-3 py-1 hover:bg-surface-2 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
