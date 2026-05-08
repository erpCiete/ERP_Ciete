import BadgeFactura from '@/Components/ui/BadgeFactura';
import { router } from '@inertiajs/react';
import { useState } from 'react';

const ESTADO_OPTIONS = ['pendiente', 'solicitada', 'emitida', 'enviada', 'anulada'];

const ESTADO_LABEL = {
    pendiente: 'Pendiente',
    solicitada: 'Solicitada',
    emitida:   'Emitida',
    enviada:   'Enviada',
    anulada:   'Anulada',
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

export default function FacturasExcelView({
    facturas = [],
    filters = {},
    aplicarFiltros,
    canCreate = true,
    canEdit = true,
    canExport = false,
    selectedIds = [],
    onToggleSelected,
    onToggleAllVisible,
    onExportList,
    onExportSelected,
    onExportDetail,
    pagination = null,
    isMoeve = false,
    isRepsol = false,
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [estado, setEstado] = useState(filters.estado ?? '');

    function doFilter(overrides = {}) {
        aplicarFiltros({
            search: overrides.search !== undefined ? overrides.search : search,
            estado: overrides.estado !== undefined ? overrides.estado : estado,
            ...(overrides.page !== undefined ? { page: overrides.page } : {}),
        });
    }

    // ── Columnas: orden operativo CIETE ───────────────────────────────────────
    // 1. Estado  2. Nº Factura  3. Fecha  4. Sociedad  5. CIF
    // 6. Contrato/tarifa  7. Importe  8. [MOEVE: CCP, Sociedad M]
    // 9. [REPSOL: Orden, Auto.]  10. Acciones
    const columns = [
        { label: 'Estado',        key: 'estado',           width: 'w-24' },
        { label: 'Nº Factura',    key: 'numero_factura',   width: 'w-32' },
        { label: 'Fecha',         key: 'fecha_emision',    width: 'w-24' },
        { label: 'Sociedad',      key: 'sociedad_nombre',  width: 'w-40' },
        { label: 'CIF',           key: 'cif',              width: 'w-28' },
        { label: 'Contrato',      key: 'contrato',         width: 'w-36' },
        { label: 'Importe',       key: 'total',            width: 'w-28' },
        { label: 'Asignado',      key: 'importe_asignado', width: 'w-28' },
        { label: 'Difer.',        key: 'diferencia',       width: 'w-24' },
        { label: 'Cuadre',        key: 'estado_cuadre',    width: 'w-24' },
        ...(isMoeve ? [
            { label: 'Nº CCP',    key: 'factura_ccp',      width: 'w-24', badge: 'M' },
            { label: 'Sociedad M',key: 'sociedad',         width: 'w-28', badge: 'M' },
        ] : []),
        ...(isRepsol ? [
            { label: 'Orden',     key: 'orden_factura',    width: 'w-16', badge: 'R' },
            { label: 'Auto.',     key: 'autofactura',      width: 'w-14', badge: 'R' },
        ] : []),
        { label: 'Acciones',      key: '_acciones',        width: 'w-20' },
    ];
    const visibleIds = facturas.map((factura) => factura.id_factura).filter(Boolean);
    const allVisibleSelected = visibleIds.length > 0 && visibleIds.every((id) => selectedIds.includes(id));

    return (
        <div className="space-y-3">
            {/* Barra de filtros compacta */}
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && doFilter({ search })}
                    placeholder="Buscar factura, sociedad, CIF…"
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
                {canExport && (
                    <button
                        type="button"
                        onClick={onExportList}
                        className="rounded border border-border px-3 py-1 text-xs font-semibold text-text-main hover:bg-surface-2"
                    >
                        Exportar listado
                    </button>
                )}
                {canExport && selectedIds.length > 0 && (
                    <button
                        type="button"
                        onClick={onExportSelected}
                        className="rounded border border-(--ciete-red) px-3 py-1 text-xs font-semibold text-(--ciete-red) hover:bg-red-50"
                    >
                        Exportar seleccionadas ({selectedIds.length})
                    </button>
                )}
                {canCreate && (
                    <button
                        type="button"
                        onClick={() => router.visit(route('facturas.create'))}
                        className="ml-auto rounded bg-(--ciete-red) px-3 py-1 text-xs font-semibold text-white hover:bg-(--ciete-red-dark)"
                    >
                        + Nueva factura
                    </button>
                )}
            </div>

            {/* Tabla densa */}
            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="w-full min-w-[800px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {canExport && (
                                <th className="w-10 whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint">
                                    <input
                                        type="checkbox"
                                        checked={allVisibleSelected}
                                        onChange={onToggleAllVisible}
                                        aria-label="Exportar seleccionadas"
                                        className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                    />
                                </th>
                            )}
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    className={`${col.width} whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {col.label}
                                    {col.badge === 'M' && (
                                        <span className="ml-1 rounded bg-blue-100 px-1 text-[9px] font-bold text-blue-700">M</span>
                                    )}
                                    {col.badge === 'R' && (
                                        <span className="ml-1 rounded bg-red-100 px-1 text-[9px] font-bold text-red-700">R</span>
                                    )}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {facturas.length === 0 && (
                            <tr>
                                <td colSpan={columns.length + (canExport ? 1 : 0)} className="px-3 py-10 text-center text-text-hint">
                                    No hay facturas con los filtros aplicados.
                                </td>
                            </tr>
                        )}
                        {facturas.map((factura) => {
                            const isCancelled = factura.estado === 'anulada';
                            const sociedadFacturadora = factura.empresa_facturadora ?? factura.empresa ?? null;

                            return (
                                <tr
                                    key={factura.id_factura}
                                    className={`hover:bg-surface-2/50 ${isCancelled ? 'opacity-50' : ''}`}
                                >
                                    {canExport && (
                                        <td className="px-2 py-1.5">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.includes(factura.id_factura)}
                                                onChange={() => onToggleSelected?.(factura.id_factura)}
                                                aria-label={`Exportar factura ${factura.numero_factura ?? factura.id_factura}`}
                                                className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                            />
                                        </td>
                                    )}
                                    {/* Estado */}
                                    <td className="px-2 py-1.5">
                                        <BadgeFactura estado={factura.estado} />
                                    </td>

                                    {/* Nº factura — protagonista */}
                                    <td className="whitespace-nowrap px-2 py-1.5 font-mono font-semibold text-(--ciete-red)">
                                        {fmt(factura.numero_factura)}
                                    </td>

                                    {/* Fecha emisión */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        {fmtDate(factura.fecha_emision)}
                                    </td>

                                    {/* Sociedad — nombre de la empresa cliente (prominente) */}
                                    <td className="max-w-[160px] px-2 py-1.5" title={sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre ?? ''}>
                                        <span className="block truncate font-semibold text-text-main">
                                            {sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre ?? '—'}
                                        </span>
                                    </td>

                                    {/* CIF — prominente, en mono rojo */}
                                    <td className="whitespace-nowrap px-2 py-1.5">
                                        <span className="font-mono font-semibold text-(--ciete-red)">
                                            {sociedadFacturadora?.cif ?? '—'}
                                        </span>
                                    </td>

                                    {/* Contrato/tarifa */}
                                    <td className="max-w-[144px] truncate px-2 py-1.5 text-text-muted" title={factura.contrato?.nombre ?? factura.contrato?.codigo_contrato ?? ''}>
                                        {factura.contrato?.codigo_contrato ?? '—'}
                                    </td>

                                    {/* Importe */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-right font-medium text-text-main">
                                        {fmtMoney(factura.total)}
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-right text-text-muted">
                                        {fmtMoney(factura.importe_asignado)}
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-right font-medium text-text-main">
                                        {fmtMoney(factura.diferencia)}
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        {fmt(factura.estado_cuadre)}
                                    </td>

                                    {/* MOEVE: CCP + sociedad facturadora */}
                                    {isMoeve && (
                                        <>
                                            <td className="whitespace-nowrap px-2 py-1.5 font-mono text-text-muted">
                                                {fmt(factura.numero_factura_ccp)}
                                            </td>
                                            <td className="max-w-[112px] truncate px-2 py-1.5 text-text-muted" title={factura.sociedad ?? ''}>
                                                {fmt(factura.sociedad)}
                                            </td>
                                        </>
                                    )}

                                    {/* REPSOL: orden + autofactura */}
                                    {isRepsol && (
                                        <>
                                            <td className="px-2 py-1.5 text-center">
                                                {factura.orden_factura != null ? (
                                                    <span className="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-50 text-[10px] font-bold text-red-700">
                                                        {factura.orden_factura}
                                                    </span>
                                                ) : '—'}
                                            </td>
                                            <td className="px-2 py-1.5 text-center">
                                                {factura.autofactura ? (
                                                    <span className="font-bold text-green-600">✓</span>
                                                ) : '—'}
                                            </td>
                                        </>
                                    )}

                                    {/* Acciones */}
                                    <td className="px-2 py-1.5">
                                        <div className="flex flex-wrap gap-2">
                                        {canExport && (
                                            <button
                                                type="button"
                                                onClick={() => onExportDetail?.(factura.id_factura)}
                                                className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                            >
                                                Exportar
                                            </button>
                                        )}
                                        {canEdit && (
                                            <button
                                                type="button"
                                                onClick={() => router.visit(route('facturas.edit', factura.id_factura))}
                                                className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                            >
                                                Ver ficha
                                            </button>
                                        )}
                                        </div>
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
