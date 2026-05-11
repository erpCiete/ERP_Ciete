// resources/js/Pages/Facturas/Index.jsx
import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeFactura from '@/Components/ui/BadgeFactura';
import FacturasExcelView from '@/Components/ui/FacturasExcelView';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { useFacturas } from '@/Hooks/useFacturas';

// ─── Opciones de estado para el filtro ───────────────────────────────────────
const ESTADO_OPTIONS = ['pendiente', 'solicitada', 'emitida', 'enviada', 'anulada'];
const hasPermission = (user, permission, aliases = []) =>
    Boolean(user?.permission_slugs?.some((slug) => slug === permission || aliases.includes(slug)));

function fmtMoney(value) {
    if (value === null || value === undefined) return '-';
    const parsed = Number(value);
    if (Number.isNaN(parsed)) return '-';

    return `${parsed.toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })} EUR`;
}

function primaryTrabajo(factura) {
    return factura.trabajos?.[0] ?? factura.trabajo ?? null;
}

function formatWorkNumber(trabajo) {
    const value = trabajo?.numero_trabajo_visible ?? trabajo?.numero_trabajo_operativo ?? trabajo?.numero_trabajo;
    if (value === null || value === undefined || value === '') return '—';
    const text = String(value);
    return /^\d+$/.test(text) ? text.padStart(4, '0') : text;
}

// ─── Componente principal ─────────────────────────────────────────────────────
// Props desde FacturaController@index via Inertia:
//   facturas    → { data: [...], meta: { pagination: { total, current_page, last_page } } }
//   filters     → { search, estado } (filtros activos en el servidor)
//   contextoIds → [1] MOEVE · [2] REPSOL · [1,2] ambos
//   canCreate   → boolean — permiso facturas.crear del usuario
export default function FacturasIndex({ facturas, filters = {}, contextoIds = [], canCreate = true, canExport = false }) {
    const { t } = useI18n();
    const { eliminarFactura } = useFacturas();
    const { visualStyle } = useTheme();
    const { auth } = usePage().props;

    const isCieteExcel  = visualStyle === 'ciete_excel';
    const activeContext = auth?.user?.active_context;
    const canCreateFacturas = hasPermission(auth?.user, 'facturas.crear');
    const canEditFacturas = hasPermission(auth?.user, 'facturas.editar');
    const canDeleteFacturas = hasPermission(auth?.user, 'facturas.eliminar');
    const canExportFacturas = canExport || hasPermission(auth?.user, 'facturas.exportar');

    const isMoeve  = contextoIds.includes(1);
    const isRepsol = contextoIds.includes(2);

    const [search,   setSearch]   = useState(filters.search ?? '');
    const [estado,   setEstado]   = useState(filters.estado ?? '');
    const [status,   setStatus]   = useState('ready');
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [selectedIds, setSelectedIds] = useState([]);

    const rows  = facturas?.data  ?? [];
    const total = facturas?.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || estado !== '';
    const selectedCount = selectedIds.length;
    const visibleIds = rows.map((factura) => factura.id_factura).filter(Boolean);
    const allVisibleSelected = visibleIds.length > 0 && visibleIds.every((id) => selectedIds.includes(id));

    // Columnas fijas (9) + MOEVE (2) + REPSOL (2) + acciones (1)
    const totalCols = 9 + (isMoeve ? 2 : 0) + (isRepsol ? 2 : 0) + 1 + (canExportFacturas ? 1 : 0);

    // ── Aplicar filtros en el servidor via Inertia ────────────────────────────
    const aplicarFiltros = useCallback((overrides = {}) => {
        const params = {
            search: overrides.search !== undefined ? overrides.search : search,
            estado: overrides.estado !== undefined ? overrides.estado : estado,
            page:   overrides.page   !== undefined ? overrides.page   : undefined,
        };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });

        router.get(route('facturas.index'), params, {
            preserveState:  true,
            preserveScroll: true,
            replace:        true,
            onStart:  () => setStatus('loading'),
            onFinish: () => setStatus('ready'),
            onError:  () => setStatus('error'),
        });
    }, [search, estado]);

    // Debounce 400ms en la búsqueda
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters.search ?? '')) aplicarFiltros({ search });
        }, 400);
        return () => clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    // ── Limpiar filtros ───────────────────────────────────────────────────────
    const buildExportUrl = useCallback((ids = []) => {
        const params = new URLSearchParams();

        if (ids.length > 0) {
            params.set('ids', ids.join(','));
        } else {
            if (search) params.set('search', search);
            if (estado) params.set('estado', estado);
        }

        const queryString = params.toString();
        return `/api/v1/facturas/export${queryString ? `?${queryString}` : ''}`;
    }, [search, estado]);

    const exportList = useCallback(() => {
        if (!canExportFacturas) return;
        window.location.assign(buildExportUrl());
    }, [buildExportUrl, canExportFacturas]);

    const exportSelected = useCallback(() => {
        if (!canExportFacturas || selectedIds.length === 0) return;
        window.location.assign(buildExportUrl(selectedIds));
    }, [buildExportUrl, canExportFacturas, selectedIds]);

    const exportDetail = useCallback((id) => {
        if (!canExportFacturas || !id) return;
        window.location.assign(`/api/v1/facturas/${id}/export`);
    }, [canExportFacturas]);

    const toggleSelected = useCallback((id) => {
        setSelectedIds((current) => (
            current.includes(id)
                ? current.filter((currentId) => currentId !== id)
                : [...current, id]
        ));
    }, []);

    const toggleAllVisible = useCallback(() => {
        setSelectedIds((current) => {
            if (visibleIds.length === 0) return current;

            if (visibleIds.every((id) => current.includes(id))) {
                return current.filter((id) => !visibleIds.includes(id));
            }

            return Array.from(new Set([...current, ...visibleIds]));
        });
    }, [visibleIds]);

    const limpiarFiltros = () => {
        setSearch('');
        setEstado('');
        router.get(route('facturas.index'), {}, { replace: true });
    };

    // ── Anular factura ────────────────────────────────────────────────────────
    const handleDelete = () => {
        if (!deleteTarget) return;
        eliminarFactura(deleteTarget.id_factura, () => setDeleteTarget(null));
    };

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('facturas.list')}
                </h2>
            }
        >
            <Head title={t('facturas.title')} />

            {/* Modal confirmación anular */}
            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('facturas.confirmDelete')}
                message={`${deleteTarget?.numero_factura ?? '—'}`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('facturas.voidAction')}
            />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={t('facturas.title')}
                    description={t('facturas.list')}
                    actions={(
                        <div className="flex w-full flex-wrap gap-2 sm:w-auto sm:justify-end">
                            {canExportFacturas && (
                                <>
                                    <button
                                        type="button"
                                        onClick={exportList}
                                        className="inline-flex w-full items-center justify-center rounded-md border border-border px-4 py-2 text-sm font-semibold text-text-main transition hover:bg-surface-2 sm:w-auto"
                                    >
                                        {t('facturas.exportList')}
                                    </button>
                                    {selectedCount > 0 && (
                                        <button
                                            type="button"
                                            onClick={exportSelected}
                                            className="inline-flex w-full items-center justify-center rounded-md border border-(--ciete-red) px-4 py-2 text-sm font-semibold text-(--ciete-red) transition hover:bg-red-50 sm:w-auto"
                                        >
                                            {t('facturas.exportSelected')} ({selectedCount})
                                        </button>
                                    )}
                                </>
                            )}
                            {canCreate && (
                                <button
                                    type="button"
                                    onClick={() => router.visit(route('facturas.create'))}
                                    className="inline-flex w-full items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) sm:w-auto"
                                >
                                    + {t('facturas.create')}
                                </button>
                            )}
                        </div>
                    )}
                />

                {canCreateFacturas && activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        No puedes crear registros desde TODOS. Selecciona primero un contexto real: MOEVE, REPSOL u OTROS CLIENTES.
                    </div>
                )}

                {/* ── Vista Excel ──────────────────────────────────────────── */}
                {isCieteExcel ? (
                    <FacturasExcelView
                        facturas={rows}
                        filters={filters}
                        aplicarFiltros={aplicarFiltros}
                        canCreate={canCreate}
                        canEdit={canEditFacturas}
                        canExport={canExportFacturas}
                        selectedIds={selectedIds}
                        onToggleSelected={toggleSelected}
                        onToggleAllVisible={toggleAllVisible}
                        onExportList={exportList}
                        onExportSelected={exportSelected}
                        onExportDetail={exportDetail}
                        pagination={facturas?.meta?.pagination}
                        isMoeve={isMoeve}
                        isRepsol={isRepsol}
                    />
                ) : (
                <>

                {/* ── Filtros ───────────────────────────────────────────────── */}
                <section className="ciete-filter-bar">
                    <div className="ciete-filter-row">

                        {/* Búsqueda */}
                        <input
                            type="search"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder={t('facturas.filters.searchPlaceholder')}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm
                                       text-text-main placeholder:text-text-hint/70
                                       focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:flex-1"
                        />

                        {/* Estado */}
                        <select
                            value={estado}
                            onChange={e => {
                                setEstado(e.target.value);
                                aplicarFiltros({ estado: e.target.value });
                            }}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm
                                       text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-48"
                        >
                            <option value="">{t('facturas.filters.allStatuses')}</option>
                            {ESTADO_OPTIONS.map(op => (
                                <option key={op} value={op}>
                                    {t(`facturas.status.${op}`)}
                                </option>
                            ))}
                        </select>

                        {/* Limpiar filtros */}
                        {hasFilters && (
                            <button
                                type="button"
                                onClick={limpiarFiltros}
                                className="text-sm font-medium text-text-muted transition hover:text-text-main"
                            >
                                {t('clientes.clearFilters') ?? 'Limpiar filtros'}
                            </button>
                        )}

                        {/* Contador */}
                        <span className="text-sm text-text-hint xl:ml-auto">
                            {total === 1 ? t('facturas.countOne', { count: total }) : t('facturas.countOther', { count: total })}
                        </span>
                        {canExportFacturas && (
                            <div className="flex w-full flex-wrap gap-2 xl:w-auto xl:justify-end">
                                <button
                                    type="button"
                                    onClick={exportList}
                                    className="rounded-md border border-border px-3 py-2 text-sm font-semibold text-text-main transition hover:bg-surface-2"
                                >
                                    {t('facturas.exportList')}
                                </button>
                                {selectedCount > 0 && (
                                    <button
                                        type="button"
                                        onClick={exportSelected}
                                        className="rounded-md border border-(--ciete-red) px-3 py-2 text-sm font-semibold text-(--ciete-red) transition hover:bg-red-50"
                                    >
                                        {t('facturas.exportSelected')} ({selectedCount})
                                    </button>
                                )}
                            </div>
                        )}
                    </div>
                </section>

                {/* ── Tabla ─────────────────────────────────────────────────── */}
                <section className="ciete-table-card">
                    <p className="ciete-table-hint">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="ciete-table-scroll">
                        <table className="min-w-[1180px] w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    {canExportFacturas && (
                                        <th className="px-5 py-3 whitespace-nowrap">
                                            <input
                                                type="checkbox"
                                                checked={allVisibleSelected}
                                                onChange={toggleAllVisible}
                                                aria-label={t('facturas.exportSelected')}
                                                className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                            />
                                        </th>
                                    )}
                                    {/* Columnas siempre visibles */}
                                    <th className="px-5 py-3 whitespace-nowrap">{t('facturas.columns.number')}</th>
                                    <th className="px-5 py-3">{t('facturas.columns.work')}</th>
                                    <th className="px-5 py-3">{t('facturas.columns.company')}</th>
                                    <th className="px-5 py-3 whitespace-nowrap">{t('facturas.columns.issuedAt')}</th>
                                    <th className="px-5 py-3 text-right whitespace-nowrap">{t('facturas.columns.total')}</th>
                                    <th className="px-5 py-3 text-right whitespace-nowrap">Asignado</th>
                                    <th className="px-5 py-3 text-right whitespace-nowrap">Diferencia</th>
                                    <th className="px-5 py-3 whitespace-nowrap">Cuadre</th>
                                    <th className="px-5 py-3">{t('facturas.columns.status')}</th>

                                    {/* Columnas MOEVE — badge azul M */}
                                    {isMoeve && (
                                        <>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    {t('facturas.columns.ccpNumber')}
                                                    <span className="rounded bg-blue-100 px-1 py-0.5 text-[9px] font-bold text-blue-700">M</span>
                                                </span>
                                            </th>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    {t('facturas.columns.society')}
                                                    <span className="rounded bg-blue-100 px-1 py-0.5 text-[9px] font-bold text-blue-700">M</span>
                                                </span>
                                            </th>
                                        </>
                                    )}

                                    {/* Columnas REPSOL — badge rojo R */}
                                    {isRepsol && (
                                        <>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    {t('facturas.columns.order')}
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    {t('facturas.columns.selfInvoice')}
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                        </>
                                    )}

                                    <th className="px-5 py-3 text-right">{t('facturas.columns.actions')}</th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-border text-text-main">

                                {/* Skeleton cargando */}
                                {status === 'loading' && (
                                    Array.from({ length: 4 }, (_, i) => (
                                        <tr key={i} className="animate-pulse">
                                            {Array.from({ length: totalCols }, (_, j) => (
                                                <td key={j} className="px-5 py-4">
                                                    <div className="h-4 rounded bg-surface-2 w-full max-w-28" />
                                                </td>
                                            ))}
                                        </tr>
                                    ))
                                )}

                                {/* Error */}
                                {status === 'error' && (
                                    <tr>
                                        <td colSpan={totalCols} className="px-5 py-10 text-center text-text-muted">
                                            <p>{t('facturas.loadError')}</p>
                                            <button
                                                type="button"
                                                onClick={() => aplicarFiltros()}
                                                className="mt-3 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                            >
                                                {t('common.actions.retry')}
                                            </button>
                                        </td>
                                    </tr>
                                )}

                                {/* Vacío */}
                                {status === 'ready' && rows.length === 0 && (
                                    <tr>
                                        <td colSpan={totalCols} className="px-5 py-12 text-center text-text-muted">
                                            <p className="mb-3">{t('facturas.empty')}</p>
                                        </td>
                                    </tr>
                                )}

                                {/* Filas de datos */}
                                {status === 'ready' && rows.map(factura => {
                                    const trabajo = primaryTrabajo(factura);
                                    const sociedadFacturadora = factura.empresa_facturadora ?? factura.empresa ?? null;

                                    return (
                                    <tr key={factura.id_factura} className="hover:bg-surface-2/60">
                                        {canExportFacturas && (
                                            <td className="px-5 py-4 align-top">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedIds.includes(factura.id_factura)}
                                                    onChange={() => toggleSelected(factura.id_factura)}
                                                    aria-label={`${t('facturas.exportInvoice')} ${factura.numero_factura ?? factura.id_factura}`}
                                                    className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                                />
                                            </td>
                                        )}

                                        {/* Nº factura — mono rojo */}
                                        <td className="px-5 py-4 align-top">
                                            <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                {factura.numero_factura ?? '—'}
                                            </span>
                                        </td>

                                        {/* Trabajo relacionado */}
                                        <td className="px-5 py-4 align-top">
                                            {trabajo ? (
                                                <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                    {formatWorkNumber(trabajo)}
                                                    {factura.trabajos?.length > 1 ? ` +${factura.trabajos.length - 1}` : ''}
                                                </span>
                                            ) : (
                                                <span className="text-text-hint">—</span>
                                            )}
                                        </td>

                                        {/* Empresa — BadgeCliente */}
                                        <td className="px-5 py-4 align-top">
                                            <div className="space-y-1">
                                                <BadgeCliente cliente={sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre} />
                                                <p className="font-mono text-[11px] text-text-hint">
                                                    {sociedadFacturadora?.cif ?? 'Sin CIF'}
                                                </p>
                                            </div>
                                        </td>

                                        {/* Fecha emisión */}
                                        <td className="px-5 py-4 align-top whitespace-nowrap text-text-muted">
                                            {factura.fecha_emision
                                                ? new Date(factura.fecha_emision).toLocaleDateString('es-ES')
                                                : '—'}
                                        </td>

                                        {/* Total — € europeo, derecha, negrita */}
                                        <td className="px-5 py-4 align-top text-right font-semibold text-text-main whitespace-nowrap">
                                            {factura.total != null
                                                ? Number(factura.total).toLocaleString('es-ES', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                }) + ' €'
                                                : '—'}
                                        </td>

                                        <td className="px-5 py-4 align-top text-right text-text-muted whitespace-nowrap">
                                            {fmtMoney(factura.importe_asignado)}
                                        </td>

                                        <td className="px-5 py-4 align-top text-right font-medium text-text-main whitespace-nowrap">
                                            {fmtMoney(factura.diferencia)}
                                        </td>

                                        <td className="px-5 py-4 align-top whitespace-nowrap">
                                            <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                                                factura.estado_cuadre === 'cuadrada'
                                                    ? 'bg-green-50 text-green-700'
                                                    : factura.estado_cuadre === 'sin_items'
                                                      ? 'bg-gray-100 text-gray-600'
                                                      : 'bg-amber-50 text-amber-700'
                                            }`}>
                                                {factura.estado_cuadre ?? 'sin_items'}
                                            </span>
                                        </td>

                                        {/* Estado */}
                                        <td className="px-5 py-4 align-top">
                                            <BadgeFactura estado={factura.estado} />
                                        </td>

                                        {/* ── Columnas MOEVE ─────────────────────────── */}
                                        {isMoeve && (
                                            <>
                                                <td className="px-5 py-4 align-top text-text-muted font-mono text-xs">
                                                    {factura.factura_ccp ?? '—'}
                                                </td>
                                                <td className="px-5 py-4 align-top text-text-muted">
                                                    {factura.sociedad ?? '—'}
                                                </td>
                                            </>
                                        )}

                                        {/* ── Columnas REPSOL ────────────────────────── */}
                                        {isRepsol && (
                                            <>
                                                {/* Orden factura: 1 o 2, con pill visual */}
                                                <td className="px-5 py-4 align-top">
                                                    {factura.orden_factura != null ? (
                                                        <span className="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-50 text-[10px] font-bold text-red-700">
                                                            {factura.orden_factura}
                                                        </span>
                                                    ) : (
                                                        <span className="text-text-hint">—</span>
                                                    )}
                                                </td>
                                                {/* Autofactura: check o guión */}
                                                <td className="px-5 py-4 align-top text-center">
                                                    {factura.autofactura ? (
                                                        <span className="text-green-600 font-bold" title="Autofactura">✓</span>
                                                    ) : (
                                                        <span className="text-text-hint">—</span>
                                                    )}
                                                </td>
                                            </>
                                        )}

                                        {/* Acciones */}
                                        <td className="px-5 py-4 align-top">
                                            <div className="flex flex-wrap justify-end gap-3">
                                                {canExportFacturas && (
                                                    <button
                                                        type="button"
                                                        onClick={() => exportDetail(factura.id_factura)}
                                                        className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                    >
                                                        {t('facturas.exportInvoice')}
                                                    </button>
                                                )}
                                                {canEditFacturas && (
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('facturas.edit', factura.id_factura))}
                                                        className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                    >
                                                        {t('common.actions.edit')}
                                                    </button>
                                                )}
                                                {canDeleteFacturas && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeleteTarget(factura)}
                                                        className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                                    >
                                                        {t('facturas.voidAction')}
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

                    {/* ── Paginación ─────────────────────────────────────────── */}
                    {facturas?.meta?.pagination?.last_page > 1 && (
                        <div className="flex flex-col gap-3 border-t border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-xs text-text-hint">
                                {t('facturas.pagination.summary', {
                                    page: facturas.meta.pagination.current_page,
                                    lastPage: facturas.meta.pagination.last_page,
                                    total,
                                })}
                            </p>
                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    disabled={facturas.meta.pagination.current_page <= 1}
                                    onClick={() => aplicarFiltros({ page: facturas.meta.pagination.current_page - 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('facturas.pagination.previous')}
                                </button>
                                <button
                                    type="button"
                                    disabled={facturas.meta.pagination.current_page >= facturas.meta.pagination.last_page}
                                    onClick={() => aplicarFiltros({ page: facturas.meta.pagination.current_page + 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('facturas.pagination.next')}
                                </button>
                            </div>
                        </div>
                    )}
                </section>
            </>
            )}
            </div>
        </AuthenticatedLayout>
    );
}
