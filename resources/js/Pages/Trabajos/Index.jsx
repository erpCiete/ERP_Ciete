import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeTrabajo from '@/Components/ui/BadgeTrabajo';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import TrabajosColumnas from '@/Components/ui/TrabajosColumnas';
import { useTrabajos } from '@/Hooks/useTrabajos';

// ─── Opciones de estado para el filtro ───────────────────────────────────────
const ESTADO_OPTIONS = ['borrador', 'en_curso', 'terminado', 'cerrado', 'cancelado'];

// ─── Componente principal ─────────────────────────────────────────────────────
// Props que llegan desde TrabajoController@index via Inertia:
//   trabajos    → { data: [...], meta: { pagination: { total, current_page, last_page } } }
//   filters     → { search, estado, fecha_desde, fecha_hasta } (filtros activos en el servidor)
//   contextoIds → [1] = MOEVE · [2] = REPSOL · [1,2] = ambos
//   canCreate   → boolean — permiso trabajos.crear del usuario autenticado
export default function TrabajosIndex({ trabajos, filters = {}, contextoIds = [], canCreate = true }) {
    const { t } = useI18n();
    const { irACrear, irAEditar, eliminarTrabajo } = useTrabajos();

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

    // ── Eliminar trabajo ──────────────────────────────────────────────────────
    const handleDelete = () => {
        if (!deleteTarget) return;
    
        // Usamos la función del hook en lugar de 'router.delete' manual
        eliminarTrabajo(deleteTarget.id_trabajo, () => {
            setDeleteTarget(null);
        });
    };

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('trabajos.list')}
                </h2>
            }
        >
            <Head title={t('trabajos.title')} />

            {/* Modal confirmación eliminar */}
            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('trabajos.confirmDelete')}
                message={`Nº ${deleteTarget?.numero_trabajo} — ${deleteTarget?.descripcion_trabajo}`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('common.actions.delete')}
            />

            <div className="space-y-6">

                {/* ── Cabecera ──────────────────────────────────────────────── */}
                <section className="flex flex-col gap-4 rounded-2xl border border-border bg-surface p-6 shadow-sm md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">
                            {t('nav.groups.operations')}
                        </p>
                        <h1 className="mt-2 text-2xl font-semibold text-text-main">
                            {t('trabajos.title')}
                        </h1>
                        <div className="mt-2 flex items-center gap-2">
                            <p className="text-sm text-text-muted">{t('trabajos.list')}</p>
                            {/* Indicadores de contexto activo */}
                            {isMoeve  && (
                                <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700">
                                    MOEVE
                                </span>
                            )}
                            {isRepsol && (
                                <span className="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-700">
                                    REPSOL
                                </span>
                            )}
                        </div>
                    </div>

                    {canCreate && (
                        <button
                            type="button"
                            onClick={() => router.visit(route('trabajos.create'))}
                            className="inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                        >
                            + {t('trabajos.create')}
                        </button>
                    )}
                </section>

                {/* ── Filtros ───────────────────────────────────────────────── */}
                <section className="rounded-2xl border border-border bg-surface p-4 shadow-sm">
                    <div className="flex flex-wrap gap-3 lg:items-center">

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
                        <div className="flex items-center gap-2">
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
                        <div className="flex items-center gap-2">
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
                        <span className="text-sm text-text-hint lg:ml-auto">
                            {total} {total === 1 ? 'trabajo' : 'trabajos'}
                        </span>
                    </div>
                </section>

                {/* ── Tabla ─────────────────────────────────────────────────── */}
                <section className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
                    <div className="overflow-x-auto xl:overflow-visible">
                        <table className="min-w-full table-fixed divide-y divide-border text-sm">
                            <colgroup>
                                <col className="w-[88px]" />
                                <col className="w-[24%]" />
                                <col className="w-[120px]" />
                                <col className="w-[150px]" />
                                <col className="w-[18%]" />
                                <col className="w-[118px]" />
                                <col className="w-[22%]" />
                                <col className="w-[140px]" />
                            </colgroup>
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    <th className="px-3 py-3 whitespace-nowrap">Nº</th>
                                    <th className="px-3 py-3">Descripción</th>
                                    <th className="px-3 py-3">Estado</th>
                                    <th className="px-3 py-3">Empresa</th>
                                    <th className="px-3 py-3">Estación</th>
                                    <th className="px-3 py-3 whitespace-nowrap">F. encargo</th>
                                    <th className="px-3 py-3">Detalle cliente</th>
                                    <th className="px-3 py-3 text-right">Acciones</th>
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
                                            <p>No se pudo cargar la lista de trabajos.</p>
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
                                            {canCreate && (
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('trabajos.create'))}
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
                                                {String(trabajo.numero_trabajo).padStart(4, '0')}
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
                                            <p className="truncate">{trabajo.estacion?.nombre ?? '—'}</p>
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
                                            <div className="flex justify-end gap-3">
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
                                                    {t('common.actions.delete')}
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
                        <div className="flex items-center justify-between border-t border-border px-3 py-3">
                            <p className="text-xs text-text-hint">
                                Página {trabajos.meta.pagination.current_page} de {trabajos.meta.pagination.last_page}
                                {' · '}{total} resultados
                            </p>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={trabajos.meta.pagination.current_page <= 1}
                                    onClick={() => aplicarFiltros({ page: trabajos.meta.pagination.current_page - 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    Anterior
                                </button>
                                <button
                                    type="button"
                                    disabled={trabajos.meta.pagination.current_page >= trabajos.meta.pagination.last_page}
                                    onClick={() => aplicarFiltros({ page: trabajos.meta.pagination.current_page + 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    Siguiente
                                </button>
                            </div>
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
