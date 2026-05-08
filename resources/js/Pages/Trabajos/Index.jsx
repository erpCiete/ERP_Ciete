import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeTrabajo from '@/Components/ui/BadgeTrabajo';
import TrabajosExcelView from '@/Components/ui/TrabajosExcelView';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import TrabajosColumnas from '@/Components/ui/TrabajosColumnas';
import { useTrabajos } from '@/Hooks/useTrabajos';

// ─── Opciones de estado para el filtro ───────────────────────────────────────
const ESTADO_OPTIONS = ['en_curso', 'terminado', 'pendiente_facturar', 'facturado', 'finalizado', 'cancelado'];

function formatWorkNumber(trabajo) {
    const value = trabajo?.numero_trabajo_visible ?? trabajo?.numero_trabajo_operativo ?? trabajo?.numero_trabajo;
    if (value === null || value === undefined || value === '') return '—';
    const text = String(value);
    return /^\d+$/.test(text) ? text.padStart(4, '0') : text;
}

// ─── Componente principal ─────────────────────────────────────────────────────
// Props que llegan desde TrabajoController@index via Inertia:
//   trabajos    → { data: [...], meta: { pagination: { total, current_page, last_page } } }
//   filters     → { search, estado, fecha_desde, fecha_hasta } (filtros activos en el servidor)
//   contextoIds → [1] = MOEVE · [2] = REPSOL · [1,2] = ambos
//   canCreate   → boolean — permiso trabajos.crear del usuario autenticado
export default function TrabajosIndex({ trabajos, filters = {}, contextoIds = [], canCreate = true, responsables = [], creationCatalogs = {} }) {
    const { t } = useI18n();
    const { irACrear, irAEditar, eliminarTrabajo } = useTrabajos();
    const { visualStyle } = useTheme();
    const { auth } = usePage().props;

    const activeContext = auth?.user?.active_context;
    const isCieteExcel = visualStyle === 'ciete_excel';
    const canCreateByPermission = Boolean(canCreate);
    const canCreateInContext = canCreateByPermission && !activeContext?.is_all;

    // Contexto activo del usuario
    const isMoeve  = contextoIds.includes(1);
    const isRepsol = contextoIds.includes(2);

    // Estado local de filtros — se sincronizan con los props que vienen del servidor
    const [search,     setSearch]     = useState(filters.search      ?? '');
    const [estado,     setEstado]     = useState(filters.estado       ?? '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde  ?? '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta  ?? '');
    const [status,     setStatus]     = useState('ready');
    const [deleteTarget, setDeleteTarget] = useState(null);

    const rows  = trabajos?.data  ?? [];
    const total = trabajos?.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || estado !== '' || fechaDesde !== '' || fechaHasta !== '';

    // Número de columnas total — para colSpan dinámico
    const totalCols = 8;

    // ── Aplicar filtros en el servidor via Inertia ────────────────────────────
    // Los filtros NO son locales — cada cambio dispara un router.get al servidor.
    // El TrabajoController@index filtra y devuelve los datos paginados.
    const aplicarFiltros = useCallback((overrides = {}) => {
        const params = {
            search:      overrides.search      !== undefined ? overrides.search      : search,
            estado:      overrides.estado      !== undefined ? overrides.estado      : estado,
            fecha_desde: overrides.fecha_desde !== undefined ? overrides.fecha_desde : fechaDesde,
            fecha_hasta: overrides.fecha_hasta !== undefined ? overrides.fecha_hasta : fechaHasta,
            municipio:   overrides.municipio,
            provincia:   overrides.provincia,
            codigo_estacion: overrides.codigo_estacion,
            id_responsable_ciete: overrides.id_responsable_ciete,
            id_estacion_servicio: overrides.id_estacion_servicio,
            page:        overrides.page        !== undefined ? overrides.page        : undefined,
        };

        // Limpiar valores vacíos para no ensuciar la URL
        Object.keys(params).forEach((k) => { if (!params[k]) delete params[k]; });

        router.get(route('trabajos.index'), params, {
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
        router.get(route('trabajos.index'), {}, { replace: true });
    };

    // ── Cancelar trabajo ──────────────────────────────────────────────────────
    const handleDelete = () => {
        if (!deleteTarget) return;
    
        // Usamos la función del hook en lugar de 'router.delete' manual
        eliminarTrabajo(deleteTarget.id_trabajo, () => {
            setDeleteTarget(null);
        });
    };

    // ─────────────────────────────────────────────────────────────────────────
    const handleCreate = () => {
        if (activeContext?.is_all) {
            window.alert('Selecciona un contexto concreto para crear trabajos.');
            return;
        }

        irACrear();
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('trabajos.list')}
                </h2>
            }
        >
            <Head title={t('trabajos.title')} />

            {/* Modal confirmación cancelar */}
            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('trabajos.confirmDelete')}
                message={`Nº ${formatWorkNumber(deleteTarget)} — ${deleteTarget?.descripcion_trabajo}`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('trabajos.cancelAction')}
            />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={t('trabajos.title')}
                    description={t('trabajos.list')}
                    actions={!isCieteExcel && canCreateByPermission ? (
                        <button
                            type="button"
                            onClick={handleCreate}
                            className={`inline-flex w-full items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition sm:w-auto ${
                                canCreateInContext
                                    ? 'bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)'
                                    : 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100'
                            }`}
                        >
                            + {t('trabajos.create')}
                        </button>
                    ) : null}
                />

                {!isCieteExcel && canCreateByPermission && activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Selecciona un contexto concreto para crear trabajos.
                    </div>
                )}

                {/* ── Filtros ───────────────────────────────────────────────── */}
                {isCieteExcel ? (
                    <TrabajosExcelView
                        trabajos={rows}
                        filters={filters}
                        aplicarFiltros={aplicarFiltros}
                        canCreate={canCreateByPermission}
                        pagination={trabajos?.meta?.pagination}
                        responsables={responsables}
                        creationCatalogs={creationCatalogs}
                    />
                ) : (
                <>
                <section className="ciete-filter-bar">
                    <div className="ciete-filter-row">

                        {/* Búsqueda libre */}
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={t('trabajos.filters.searchPlaceholder')}
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
                            <option value="">{t('trabajos.filters.allStatuses')}</option>
                            {ESTADO_OPTIONS.map((op) => (
                                <option key={op} value={op}>
                                    {t(`trabajos.status.${op === 'en_curso' ? 'enCurso' : op}`)}
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
                            {total === 1 ? t('trabajos.countOne', { count: total }) : t('trabajos.countOther', { count: total })}
                        </span>
                    </div>
                </section>

                {/* ── Tabla ─────────────────────────────────────────────────── */}
                <section className="ciete-table-card">
                    <p className="ciete-table-hint">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="ciete-table-scroll">
                        <table className="min-w-[1220px] w-full table-fixed divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    <th className="w-22 px-3 py-3 whitespace-nowrap">{t('trabajos.columns.number')}</th>
                                    <th className="px-3 py-3">{t('trabajos.columns.description')}</th>
                                    <th className="w-28 px-3 py-3">{t('trabajos.columns.status')}</th>
                                    <th className="w-32 px-3 py-3">{t('trabajos.columns.company')}</th>
                                    <th className="px-3 py-3">{t('trabajos.columns.station')}</th>
                                    <th className="w-28 px-3 py-3 whitespace-nowrap">{t('trabajos.columns.assignedAt')}</th>
                                    <th className="px-3 py-3">{t('trabajos.columns.clientDetail')}</th>
                                    <th className="w-32 px-3 py-3 text-right">{t('trabajos.columns.actions')}</th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-border text-text-main">

                                {/* Skeleton cargando */}
                                {status === 'loading' && (
                                    Array.from({ length: 4 }, (_, i) => (
                                        <tr key={i} className="animate-pulse">
                                            {Array.from({ length: totalCols }, (_, j) => (
                                                <td key={j} className="px-3 py-4">
                                                    <div className="h-4 rounded bg-surface-2 w-full max-w-28" />
                                                </td>
                                            ))}
                                        </tr>
                                    ))
                                )}

                                {/* Error */}
                                {status === 'error' && (
                                    <tr>
                                        <td colSpan={totalCols} className="px-3 py-10 text-center text-text-muted">
                                            <p>{t('trabajos.loadError')}</p>
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
                                        <td colSpan={totalCols} className="px-3 py-12 text-center text-text-muted">
                                            <p className="mb-3">{t('trabajos.empty')}</p>
                                            {canCreateInContext && (
                                                <button
                                                    type="button"
                                                    onClick={handleCreate}
                                                    className="inline-flex items-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark)"
                                                >
                                                    + {t('trabajos.create')}
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                )}

                                {/* Filas de datos */}
                                {status === 'ready' && rows.map((trabajo) => (
                                    <tr key={trabajo.id_trabajo} className="hover:bg-surface-2/60">

                                        {/* Nº trabajo — en mono rojo igual que referencias OBR/PED */}
                                        <td className="px-3 py-4 align-top">
                                            <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                {formatWorkNumber(trabajo)}
                                            </span>
                                        </td>

                                        {/* Descripción */}
                                        <td className="px-3 py-4 align-top">
                                            <p className="font-medium text-text-main line-clamp-2">
                                                {trabajo.descripcion_trabajo}
                                            </p>
                                        </td>

                                        {/* Estado */}
                                        <td className="px-3 py-4 align-top">
                                            <BadgeTrabajo estado={trabajo.estado} />
                                        </td>

                                        {/* Empresa — badge de cliente */}
                                        <td className="px-3 py-4 align-top">
                                            <BadgeCliente cliente={trabajo.empresa?.nombre_comercial} />
                                        </td>

                                        {/* Estación */}
                                        <td className="px-3 py-4 align-top text-text-muted">
                                            <p className="break-words">{trabajo.estacion?.nombre ?? '—'}</p>
                                        </td>

                                        {/* Fecha encargo */}
                                        <td className="px-3 py-4 align-top whitespace-nowrap text-text-muted">
                                            {trabajo.fecha_encargo
                                                ? new Date(trabajo.fecha_encargo).toLocaleDateString('es-ES')
                                                : '—'}
                                        </td>

                                        {/* ── Columnas Dinámicas MOEVE/REPSOL ─────────── */}
                                        <TrabajosColumnas isMoeve={isMoeve} isRepsol={isRepsol} trabajo={trabajo} />

                                        {/* Acciones */}
                                        <td className="px-3 py-4 align-top">
                                            <div className="flex flex-wrap justify-end gap-3">
                                                <button
                                                    type="button"
                                                    // CAMBIO: Antes usabas router.visit, ahora usas la función del hook
                                                    onClick={() => irAEditar(trabajo.id_trabajo)}
                                                    className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                >
                                                    {t('common.actions.edit')}
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    onClick={() => setDeleteTarget(trabajo)}
                                                    className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                                >
                                                    {t('trabajos.cancelAction')}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* ── Paginación ─────────────────────────────────────────── */}
                    {trabajos?.meta?.pagination?.last_page > 1 && (
                        <div className="flex flex-col gap-3 border-t border-border px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-xs text-text-hint">
                                {t('trabajos.pagination.summary', {
                                    page: trabajos.meta.pagination.current_page,
                                    lastPage: trabajos.meta.pagination.last_page,
                                    total,
                                })}
                            </p>
                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    disabled={trabajos.meta.pagination.current_page <= 1}
                                    onClick={() => aplicarFiltros({ page: trabajos.meta.pagination.current_page - 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('trabajos.pagination.previous')}
                                </button>
                                <button
                                    type="button"
                                    disabled={trabajos.meta.pagination.current_page >= trabajos.meta.pagination.last_page}
                                    onClick={() => aplicarFiltros({ page: trabajos.meta.pagination.current_page + 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    {t('trabajos.pagination.next')}
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
