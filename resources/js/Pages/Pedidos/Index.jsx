import PedidosExcelView from '@/Components/ui/PedidosExcelView';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { usePedidos } from '@/Hooks/usePedidos';
import BadgePedido from '@/Components/ui/BadgePedidos'; // Asegúrate de que la ruta coincide con tu archivo

// ─── Opciones de estado para el filtro ───────────────────────────────────────
const ESTADO_OPTIONS = ['pendiente', 'solicitado', 'recibido', 'facturado_parcial', 'facturado', 'cancelado'];
const hasPermission = (user, permission, aliases = []) =>
    Boolean(user?.permission_slugs?.some((slug) => slug === permission || aliases.includes(slug)));

// ─── Componente principal ─────────────────────────────────────────────────────
export default function PedidosIndex({ pedidos, filters = {}, contextoIds = [], canCreate = true, trabajos = [] }) {
    const { t } = useI18n();
    const { irACrear, irAEditar, eliminarPedido } = usePedidos();
    const { visualStyle } = useTheme();
    const { auth } = usePage().props;

    const isCieteExcel  = visualStyle === 'ciete_excel';
    const activeContext = auth?.user?.active_context;
    const canCreatePedidos = hasPermission(auth?.user, 'pedidos.crear');
    const canEditPedidos = hasPermission(auth?.user, 'pedidos.editar');
    const canDeletePedidos = hasPermission(auth?.user, 'pedidos.eliminar');

    // Contexto activo del usuario
    const isMoeve  = contextoIds.includes(1);
    const isRepsol = contextoIds.includes(2);

    // Estado local de filtros — se sincronizan con los props que vienen del servidor
    const [search,     setSearch]     = useState(filters.search      ?? '');
    const [estado,     setEstado]     = useState(filters.estado      ?? '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde ?? '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta ?? '');
    const [status,     setStatus]     = useState('ready');
    const [deleteTarget, setDeleteTarget] = useState(null);

    const rows  = pedidos?.data  ?? [];
    const total = pedidos?.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || estado !== '' || fechaDesde !== '' || fechaHasta !== '';

    // Número de columnas total — para colSpan dinámico
    const totalCols = 5 + (isRepsol ? 2 : 0) + 1;

    // ── Aplicar filtros en el servidor via Inertia ────────────────────────────
    const aplicarFiltros = useCallback((overrides = {}) => {
        const params = {
            search:      overrides.search      !== undefined ? overrides.search      : search,
            estado:      overrides.estado      !== undefined ? overrides.estado      : estado,
            fecha_desde: overrides.fecha_desde !== undefined ? overrides.fecha_desde : fechaDesde,
            fecha_hasta: overrides.fecha_hasta !== undefined ? overrides.fecha_hasta : fechaHasta,
            page:        overrides.page        !== undefined ? overrides.page        : undefined,
        };

        // Limpiar valores vacíos para no ensuciar la URL
        Object.keys(params).forEach((k) => { if (!params[k]) delete params[k]; });

        router.get(route('pedidos.index'), params, {
            preserveState:  true,
            preserveScroll: true,
            replace:        true,
            onStart:  () => setStatus('loading'),
            onFinish: () => setStatus('ready'),
            onError:  () => setStatus('error'),
        });
    }, [search, estado, fechaDesde, fechaHasta]);

    // Debounce del input de búsqueda — espera 400ms antes de disparar el request
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters.search ?? '')) {
                aplicarFiltros({ search });
            }
        }, 400);
        return () => clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    // ── Limpiar todos los filtros ─────────────────────────────────────────────
    const limpiarFiltros = () => {
        setSearch('');
        setEstado('');
        setFechaDesde('');
        setFechaHasta('');
        router.get(route('pedidos.index'), {}, { replace: true });
    };

    // ── Cancelar pedido ───────────────────────────────────────────────────────
    const handleDelete = () => {
        if (!deleteTarget) return;
    
        eliminarPedido(deleteTarget.id_pedido, () => {
            setDeleteTarget(null);
        });
    };

    // ── Formatear moneda ──────────────────────────────────────────────────────
    const formatEur = (value) => {
        return Number(value ?? 0).toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + ' €';
    };

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('pedidos.list')}
                </h2>
            }
        >
            <Head title={t('pedidos.title')} />

            {/* Modal confirmación cancelar */}
            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('pedidos.confirmDelete')}
                message={`Nº ${deleteTarget?.numero_pedido}`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('pedidos.cancelAction')}
            />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={t('pedidos.title')}
                    description={t('pedidos.list')}
                    actions={!isCieteExcel && canCreate ? (
                        <button
                            type="button"
                            onClick={() => irACrear()}
                            className="inline-flex w-full items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) sm:w-auto"
                        >
                            + {t('pedidos.create')}
                        </button>
                    ) : null}
                />

                {canCreatePedidos && activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        No puedes crear registros desde TODOS. Selecciona primero un contexto real: MOEVE, REPSOL u OTROS CLIENTES.
                    </div>
                )}

                {/* ── Vista Excel ──────────────────────────────────────────── */}
                {isCieteExcel ? (
                    <PedidosExcelView
                        pedidos={rows}
                        filters={filters}
                        aplicarFiltros={aplicarFiltros}
                        canCreate={canCreate}
                        canEdit={canEditPedidos}
                        canDelete={canDeletePedidos}
                        pagination={pedidos?.meta?.pagination}
                        isRepsol={isRepsol}
                        trabajos={trabajos}
                    />
                ) : (
                <>

                {/* ── Filtros ───────────────────────────────────────────────── */}
                <section className="ciete-filter-bar">
                    <div className="ciete-filter-row">

                        {/* Búsqueda libre */}
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('pedidos.filters.searchPlaceholder') ?? 'Buscar por nº de pedido...'}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm
                                       text-text-main placeholder:text-text-hint/70
                                       focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:flex-1"
                        />

                        {/* Estado */}
                        <select
                            value={estado}
                            onChange={(e) => {
                                setEstado(e.target.value);
                                aplicarFiltros({ estado: e.target.value });
                            }}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm
                                       text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-48"
                        >
                            <option value="">{t('pedidos.filters.allStatuses') ?? 'Todos los estados'}</option>
                            {ESTADO_OPTIONS.map((op) => (
                                <option key={op} value={op}>
                                    {t(`pedidos.status.${op}`)}
                                </option>
                            ))}
                        </select>

                        {/* Fecha desde */}
                        <div className="ciete-filter-group">
                            <label className="whitespace-nowrap text-xs text-text-hint">
                                {t('trabajos.filters.dateFrom')}
                            </label>
                            <input
                                type="date"
                                value={fechaDesde}
                                onChange={(e) => {
                                    setFechaDesde(e.target.value);
                                    aplicarFiltros({ fecha_desde: e.target.value });
                                }}
                                className="rounded-md border border-border bg-surface px-3 py-2 text-sm
                                           text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                        </div>

                        {/* Fecha hasta */}
                        <div className="ciete-filter-group">
                            <label className="whitespace-nowrap text-xs text-text-hint">
                                {t('trabajos.filters.dateTo')}
                            </label>
                            <input
                                type="date"
                                value={fechaHasta}
                                onChange={(e) => {
                                    setFechaHasta(e.target.value);
                                    aplicarFiltros({ fecha_hasta: e.target.value });
                                }}
                                className="rounded-md border border-border bg-surface px-3 py-2 text-sm
                                           text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                        </div>

                        {/* Limpiar filtros */}
                        {hasFilters && (
                            <button
                                type="button"
                                onClick={limpiarFiltros}
                                className="text-sm font-medium text-text-muted transition hover:text-text-main"
                            >
                                {t('clientes.clearFilters')}
                            </button>
                        )}

                        {/* Total */}
                        <span className="text-sm text-text-hint xl:ml-auto">
                            {total === 1 ? t('pedidos.countOne', { count: total }) : t('pedidos.countOther', { count: total })}
                        </span>
                    </div>
                </section>

                {/* ── Tabla ─────────────────────────────────────────────────── */}
                <section className="ciete-table-card">
                    <p className="ciete-table-hint">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="ciete-table-scroll">
                        <table className="min-w-[900px] w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    {/* Columnas siempre visibles */}
                                    <th className="px-5 py-3 whitespace-nowrap">{t('pedidos.columns.number')}</th>
                                    <th className="px-5 py-3">{t('pedidos.columns.status')}</th>
                                    <th className="px-5 py-3">{t('pedidos.columns.requestedAt')}</th>
                                    <th className="px-5 py-3 text-right">{t('pedidos.columns.totalAmount')}</th>

                                    {/* Columnas REPSOL — indicadas con badge rojo */}
                                    {isRepsol && (
                                        <>
                                            <th className="px-5 py-3 text-right whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1 justify-end w-full">
                                                    {t('pedidos.columns.requestedAmount')}
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                            <th className="px-5 py-3 text-right whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1 justify-end w-full">
                                                    {t('pedidos.columns.requestedUnits')}
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                        </>
                                    )}

                                    <th className="px-5 py-3 text-right">{t('pedidos.columns.actions')}</th>
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
                                            <p>{t('pedidos.loadError')}</p>
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
                                            <p className="mb-3">{t('pedidos.empty') ?? 'No hay pedidos registrados.'}</p>
                                        </td>
                                    </tr>
                                )}

                                {/* Filas de datos */}
                                {status === 'ready' && rows.map((pedido) => (
                                    <tr key={pedido.id_pedido} className="hover:bg-surface-2/60">

                                        {/* Nº pedido */}
                                        <td className="px-5 py-4 align-top">
                                            <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                {pedido.numero_pedido || '—'}
                                            </span>
                                        </td>

                                        {/* Estado */}
                                        <td className="px-5 py-4 align-top">
                                            <BadgePedido estado={pedido.estado} />
                                        </td>

                                        {/* Fecha Solicitud */}
                                        <td className="px-5 py-4 align-top whitespace-nowrap text-text-muted">
                                            {pedido.fecha_solicitud_pedido || pedido.fecha_solicitud
                                                ? new Date(pedido.fecha_solicitud_pedido || pedido.fecha_solicitud).toLocaleDateString('es-ES')
                                                : '—'}
                                        </td>

                                        {/* Importe Total */}
                                        <td className="px-5 py-4 align-top text-right font-medium">
                                            {formatEur(pedido.total ?? 0)}
                                        </td>

                                        {/* ── Columnas Dinámicas REPSOL ─────────── */}
                                        {isRepsol && (
                                            <>
                                                <td className="px-5 py-4 align-top text-right text-text-muted">
                                                    {formatEur(pedido.importe_solicitado)}
                                                </td>
                                                <td className="px-5 py-4 align-top text-right text-text-muted">
                                                    {pedido.unidades_solicitadas ?? '0'}
                                                </td>
                                            </>
                                        )}

                                        {/* Acciones */}
                                        <td className="px-5 py-4 align-top">
                                            <div className="flex flex-wrap justify-end gap-3">
                                                {canEditPedidos && (
                                                    <button
                                                        type="button"
                                                        onClick={() => irAEditar(pedido.id_pedido)}
                                                        className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                    >
                                                        {t('common.actions.edit')}
                                                    </button>
                                                )}
                                                
                                                {canDeletePedidos && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeleteTarget(pedido)}
                                                        className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                                    >
                                                        {t('pedidos.cancelAction')}
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* ── Paginación ─────────────────────────────────────────── */}
                    {pedidos?.meta?.pagination?.last_page > 1 && (
                        <div className="flex flex-col gap-3 border-t border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-xs text-text-hint">
                                {t('pedidos.pagination.summary', {
                                    page: pedidos.meta.pagination.current_page,
                                    lastPage: pedidos.meta.pagination.last_page,
                                    total,
                                })}
                            </p>
                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    disabled={pedidos.meta.pagination.current_page <= 1}
                                    onClick={() => aplicarFiltros({ page: pedidos.meta.pagination.current_page - 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('pedidos.pagination.previous')}
                                </button>
                                <button
                                    type="button"
                                    disabled={pedidos.meta.pagination.current_page >= pedidos.meta.pagination.last_page}
                                    onClick={() => aplicarFiltros({ page: pedidos.meta.pagination.current_page + 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('pedidos.pagination.next')}
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
