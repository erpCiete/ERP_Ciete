// resources/js/Pages/Facturas/Index.jsx
import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeFactura from '@/Components/ui/BadgeFactura';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { useFacturas } from '@/Hooks/useFacturas';

// ─── Opciones de estado para el filtro ───────────────────────────────────────
const ESTADO_OPTIONS = ['borrador', 'emitida', 'cobrada', 'cancelada'];

// ─── Componente principal ─────────────────────────────────────────────────────
// Props desde FacturaController@index via Inertia:
//   facturas    → { data: [...], meta: { pagination: { total, current_page, last_page } } }
//   filters     → { search, estado } (filtros activos en el servidor)
//   contextoIds → [1] MOEVE · [2] REPSOL · [1,2] ambos
//   canCreate   → boolean — permiso facturas.gestionar del usuario
export default function FacturasIndex({ facturas, filters = {}, contextoIds = [], canCreate = true }) {
    const { t } = useI18n();
    const { eliminarFactura } = useFacturas();

    const isMoeve  = contextoIds.includes(1);
    const isRepsol = contextoIds.includes(2);

    const [search,   setSearch]   = useState(filters.search ?? '');
    const [estado,   setEstado]   = useState(filters.estado ?? '');
    const [status,   setStatus]   = useState('ready');
    const [deleteTarget, setDeleteTarget] = useState(null);

    const rows  = facturas?.data  ?? [];
    const total = facturas?.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || estado !== '';

    // Columnas fijas (7) + MOEVE (2) + REPSOL (2) + acciones (1)
    const totalCols = 7 + (isMoeve ? 2 : 0) + (isRepsol ? 2 : 0) + 1;

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
    const limpiarFiltros = () => {
        setSearch('');
        setEstado('');
        router.get(route('facturas.index'), {}, { replace: true });
    };

    // ── Eliminar factura ──────────────────────────────────────────────────────
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

            {/* Modal confirmación eliminar */}
            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('facturas.confirmDelete')}
                message={`${deleteTarget?.numero_factura ?? '—'}`}
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
                            {t('facturas.title')}
                        </h1>
                        <div className="mt-2 flex items-center gap-2">
                            <p className="text-sm text-text-muted">{t('facturas.list')}</p>
                            {isMoeve && (
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
                            onClick={() => router.visit(route('facturas.create'))}
                            className="inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                        >
                            + {t('facturas.create')}
                        </button>
                    )}
                </section>

                {/* ── Filtros ───────────────────────────────────────────────── */}
                <section className="rounded-2xl border border-border bg-surface p-4 shadow-sm">
                    <div className="flex flex-wrap gap-3 lg:items-center">

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
                        <span className="text-sm text-text-hint lg:ml-auto">
                            {total} {total === 1 ? 'factura' : 'facturas'}
                        </span>
                    </div>
                </section>

                {/* ── Tabla ─────────────────────────────────────────────────── */}
                <section className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    {/* Columnas siempre visibles */}
                                    <th className="px-5 py-3 whitespace-nowrap">Nº factura</th>
                                    <th className="px-5 py-3">Trabajo</th>
                                    <th className="px-5 py-3">Empresa</th>
                                    <th className="px-5 py-3 whitespace-nowrap">F. emisión</th>
                                    <th className="px-5 py-3 text-right whitespace-nowrap">Total</th>
                                    <th className="px-5 py-3">Estado</th>

                                    {/* Columnas MOEVE — badge azul M */}
                                    {isMoeve && (
                                        <>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    Nº CCP
                                                    <span className="rounded bg-blue-100 px-1 py-0.5 text-[9px] font-bold text-blue-700">M</span>
                                                </span>
                                            </th>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    Sociedad
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
                                                    Orden
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                            <th className="px-5 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1">
                                                    Autofactura
                                                    <span className="rounded bg-red-100 px-1 py-0.5 text-[9px] font-bold text-red-700">R</span>
                                                </span>
                                            </th>
                                        </>
                                    )}

                                    <th className="px-5 py-3 text-right">Acciones</th>
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
                                            <p>No se pudo cargar la lista de facturas.</p>
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
                                            {canCreate && (
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('facturas.create'))}
                                                    className="inline-flex items-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark)"
                                                >
                                                    + {t('facturas.create')}
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                )}

                                {/* Filas de datos */}
                                {status === 'ready' && rows.map(factura => (
                                    <tr key={factura.id_factura} className="hover:bg-surface-2/60">

                                        {/* Nº factura — mono rojo */}
                                        <td className="px-5 py-4 align-top">
                                            <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                {factura.numero_factura ?? '—'}
                                            </span>
                                        </td>

                                        {/* Trabajo relacionado */}
                                        <td className="px-5 py-4 align-top">
                                            {factura.trabajo ? (
                                                <span className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                    {String(factura.trabajo.numero_trabajo).padStart(4, '0')}
                                                </span>
                                            ) : (
                                                <span className="text-text-hint">—</span>
                                            )}
                                        </td>

                                        {/* Empresa — BadgeCliente */}
                                        <td className="px-5 py-4 align-top">
                                            <BadgeCliente cliente={factura.empresa?.nombre_comercial} />
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
                                            <div className="flex justify-end gap-3">
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('facturas.edit', factura.id_factura))}
                                                    className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                >
                                                    {t('common.actions.edit')}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setDeleteTarget(factura)}
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
                    {facturas?.meta?.pagination?.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-border px-5 py-3">
                            <p className="text-xs text-text-hint">
                                Página {facturas.meta.pagination.current_page} de {facturas.meta.pagination.last_page}
                                {' · '}{total} resultados
                            </p>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={facturas.meta.pagination.current_page <= 1}
                                    onClick={() => aplicarFiltros({ page: facturas.meta.pagination.current_page - 1 })}
                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 disabled:opacity-40"
                                >
                                    Anterior
                                </button>
                                <button
                                    type="button"
                                    disabled={facturas.meta.pagination.current_page >= facturas.meta.pagination.last_page}
                                    onClick={() => aplicarFiltros({ page: facturas.meta.pagination.current_page + 1 })}
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
