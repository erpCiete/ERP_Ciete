import EstacionesExcelView from '@/Components/ui/EstacionesExcelView';
import PaginationControls from '@/Components/ui/PaginationControls';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import OperationalReadOnlyNotice from '@/Components/OperationalReadOnlyNotice';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import { useClientes } from '@/Hooks/useClientes';
import { useEstaciones } from '@/Hooks/useEstaciones';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useDeferredValue, useEffect, useState } from 'react';

const hasPermission = (user, permission, aliases = []) =>
    Boolean(user?.permission_slugs?.some((slug) => slug === permission || aliases.includes(slug)));

function StationRow({ estacion, canEdit, canDelete, onDelete, t, loading }) {
    const municipality = estacion.municipio || estacion.poblacion;
    const province = estacion.provincia;
    const address = estacion.direccion;
    const deactivatedAt = estacion.fecha_baja;

    return (
        <article className="grid gap-4 px-5 py-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(13rem,0.9fr)_auto] xl:items-start">
            <div className="min-w-0">
                <div className="flex flex-wrap items-center gap-3">
                    <p className="font-mono text-sm font-semibold text-(--ciete-red)">
                        {estacion.codigo_estacion || '—'}
                    </p>
                    {!estacion.activo && (
                        <span className="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold text-gray-500 ring-1 ring-gray-300">
                            {t('estaciones.states.inactive')}
                        </span>
                    )}
                </div>

                <h3 className="mt-2 text-base font-semibold text-text-main">{estacion.nombre || '—'}</h3>

                <div className="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-text-muted">
                    <span>{municipality || t('estaciones.modern.noMunicipality')}</span>
                    <span>{province || t('estaciones.modern.noProvince')}</span>
                </div>
            </div>

            <div className="min-w-0 space-y-3">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-wide text-text-hint">
                        {t('estaciones.modern.clientContext')}
                    </p>
                    <p className="mt-1 truncate text-sm text-text-main" title={estacion.empresa?.nombre}>
                        {estacion.empresa?.nombre || t('estaciones.modern.noClient')}
                    </p>
                    <p className="truncate text-xs text-text-muted" title={estacion.contexto?.nombre}>
                        {estacion.contexto?.nombre || '—'}
                    </p>
                </div>

                {(address || deactivatedAt) && (
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-text-hint">
                            {t('estaciones.modern.secondaryDetail')}
                        </p>
                        <div className="mt-1 space-y-1 text-xs text-text-muted">
                            <p>
                                {t('estaciones.modern.address')}: {address || t('estaciones.modern.noAddress')}
                            </p>
                            {deactivatedAt && (
                                <p>
                                    {t('estaciones.modern.deactivatedOn')}: {deactivatedAt}
                                </p>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {(canEdit || canDelete) && (
                <div className="flex flex-wrap justify-start gap-3 xl:justify-end">
                    {canEdit && (
                        <button
                            type="button"
                            onClick={() => router.visit(route('estaciones.edit', estacion.id))}
                            className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                        >
                            {t('common.actions.edit')}
                        </button>
                    )}

                    {canDelete && (
                        <button
                            type="button"
                            onClick={() => onDelete(estacion)}
                            disabled={loading}
                            className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark) disabled:opacity-50"
                        >
                            {t('estaciones.deactivateAction')}
                        </button>
                    )}
                </div>
            )}
        </article>
    );
}

export default function EstacionesIndex({ canCreate = true }) {
    const { t } = useI18n();
    const { visualStyle } = useTheme();
    const { auth } = usePage().props;
    const { getClientes } = useClientes();
    const { getEstaciones, deleteEstacion, loading } = useEstaciones();

    const canCreateEstaciones = hasPermission(auth.user, 'estaciones.crear') && canCreate;
    const canEditEstaciones = hasPermission(auth.user, 'estaciones.editar');
    const canDeleteEstaciones = hasPermission(auth.user, 'estaciones.eliminar');
    const canManageEstaciones = canEditEstaciones || canDeleteEstaciones;
    const canRequestCreateEstaciones = hasPermission(auth.user, 'estaciones.crear');
    const activeContext = auth?.user?.active_context;

    const isCieteExcel = visualStyle === 'ciete_excel';

    const [clientes, setClientes] = useState([]);
    const [response, setResponse] = useState({ data: [], meta: {} });
    const [status, setStatus] = useState('loading');
    const [search, setSearch] = useState('');
    const [clienteId, setClienteId] = useState('');
    const [municipio, setMunicipio] = useState('');
    const [provincia, setProvincia] = useState('');
    const [codigo, setCodigo] = useState('');
    const [activoFilter, setActivoFilter] = useState('');
    const [page, setPage] = useState(1);
    const [deleteTarget, setDeleteTarget] = useState(null);

    const deferredSearch = useDeferredValue(search);
    const deferredMunicipio = useDeferredValue(municipio);
    const deferredProvincia = useDeferredValue(provincia);
    const deferredCodigo = useDeferredValue(codigo);
    const deferredActivoFilter = useDeferredValue(activoFilter);

    useEffect(() => {
        let active = true;

        getClientes({ per_page: 100 })
            .then((result) => {
                if (!active) {
                    return;
                }

                setClientes(result?.data ?? []);
            })
            .catch(() => {
                if (active) {
                    setClientes([]);
                }
            });

        return () => {
            active = false;
        };
    }, [getClientes]);

    const loadEstaciones = useCallback(async () => {
        setStatus('loading');

        try {
            const result = await getEstaciones({
                search: deferredSearch || undefined,
                cliente_id: clienteId || undefined,
                municipio: deferredMunicipio || undefined,
                provincia: deferredProvincia || undefined,
                codigo: deferredCodigo || undefined,
                activo: deferredActivoFilter || undefined,
                page,
                per_page: 10,
            });

            const nextResponse = result ?? { data: [], meta: {} };
            setResponse(nextResponse);
            setStatus((nextResponse.data?.length ?? 0) > 0 ? 'ready' : 'empty');
        } catch {
            setStatus('error');
        }
    }, [clienteId, deferredActivoFilter, deferredCodigo, deferredMunicipio, deferredProvincia, deferredSearch, getEstaciones, page]);

    useEffect(() => {
        loadEstaciones();
    }, [loadEstaciones]);

    const rows = response.data ?? [];
    const pagination = response.meta?.pagination ?? {};
    const total = pagination.total ?? rows.length;
    const currentPage = pagination.current_page ?? page;
    const lastPage = pagination.last_page ?? 1;
    const perPage = pagination.per_page ?? 10;
    const from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const to = total === 0 ? 0 : Math.min(currentPage * perPage, total);
    const hasFilters = search !== ''
        || clienteId !== ''
        || municipio !== ''
        || provincia !== ''
        || codigo !== ''
        || activoFilter !== '';

    const handleDelete = async () => {
        if (!deleteTarget) {
            return;
        }

        try {
            await deleteEstacion(deleteTarget.id);
            setDeleteTarget(null);
            await loadEstaciones();
        } catch {
            setStatus('error');
        }
    };

    const clearFilters = () => {
        setSearch('');
        setClienteId('');
        setMunicipio('');
        setProvincia('');
        setCodigo('');
        setActivoFilter('');
        setPage(1);
    };

    const goToPage = (nextPage) => {
        if (nextPage < 1 || nextPage > lastPage || nextPage === currentPage) {
            return;
        }

        setPage(nextPage);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{t('estaciones.header')}</h2>}
        >
            <Head title={t('estaciones.headTitle')} />

            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('estaciones.deleteTitle')}
                message={t('estaciones.deleteMessage')}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('estaciones.deactivateAction')}
            />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow={t('nav.groups.masters')}
                    title={t('estaciones.title')}
                    description={t('estaciones.description')}
                    backHref={route('maestros.index')}
                    actions={canCreateEstaciones ? (
                        <button
                            type="button"
                            onClick={() => router.visit(route('estaciones.create'))}
                            className="inline-flex w-full items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) sm:w-auto"
                        >
                            + {t('estaciones.create')}
                        </button>
                    ) : null}
                />

                <OperationalReadOnlyNotice />

                {canRequestCreateEstaciones && activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        {t('estaciones.activeContextWarning')}
                    </div>
                )}

                {isCieteExcel ? (
                    <EstacionesExcelView
                        rows={rows}
                        status={status}
                        canManage={canManageEstaciones}
                        canCreate={canCreateEstaciones}
                        canEdit={canEditEstaciones}
                        canDelete={canDeleteEstaciones}
                        clientes={clientes}
                        onDelete={setDeleteTarget}
                        onReload={loadEstaciones}
                        search={search}
                        setSearch={(value) => {
                            setSearch(value);
                            setPage(1);
                        }}
                        clienteId={clienteId}
                        setClienteId={(value) => {
                            setClienteId(value);
                            setPage(1);
                        }}
                        municipio={municipio}
                        setMunicipio={(value) => {
                            setMunicipio(value);
                            setPage(1);
                        }}
                        provincia={provincia}
                        setProvincia={(value) => {
                            setProvincia(value);
                            setPage(1);
                        }}
                        codigo={codigo}
                        setCodigo={(value) => {
                            setCodigo(value);
                            setPage(1);
                        }}
                        activoFilter={activoFilter}
                        setActivoFilter={(value) => {
                            setActivoFilter(value);
                            setPage(1);
                        }}
                        hasFilters={hasFilters}
                        onClearFilters={clearFilters}
                        total={total}
                    />
                ) : (
                    <>
                        <section className="ciete-filter-bar">
                            <div className="ciete-filter-row">
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(event) => {
                                        setSearch(event.target.value);
                                        setPage(1);
                                    }}
                                    placeholder={t('estaciones.searchPlaceholder')}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:flex-1"
                                />

                                <input
                                    type="search"
                                    value={codigo}
                                    onChange={(event) => {
                                        setCodigo(event.target.value);
                                        setPage(1);
                                    }}
                                    placeholder={t('estaciones.filters.code')}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-40"
                                />

                                <select
                                    value={clienteId}
                                    onChange={(event) => {
                                        setClienteId(event.target.value);
                                        setPage(1);
                                    }}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-72"
                                >
                                    <option value="">{t('estaciones.allClients')}</option>
                                    {clientes.map((cliente) => (
                                        <option key={cliente.id} value={cliente.id}>
                                            {cliente.razon_social || cliente.nombre}
                                        </option>
                                    ))}
                                </select>

                                <input
                                    type="search"
                                    value={municipio}
                                    onChange={(event) => {
                                        setMunicipio(event.target.value);
                                        setPage(1);
                                    }}
                                    placeholder={t('estaciones.filters.municipality')}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-44"
                                />

                                <input
                                    type="search"
                                    value={provincia}
                                    onChange={(event) => {
                                        setProvincia(event.target.value);
                                        setPage(1);
                                    }}
                                    placeholder={t('estaciones.filters.province')}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-44"
                                />

                                <select
                                    value={activoFilter}
                                    onChange={(event) => {
                                        setActivoFilter(event.target.value);
                                        setPage(1);
                                    }}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-40"
                                >
                                    <option value="">{t('estaciones.allStatuses')}</option>
                                    <option value="1">{t('estaciones.filters.active')}</option>
                                    <option value="0">{t('estaciones.filters.inactive')}</option>
                                </select>

                                {hasFilters && (
                                    <button
                                        type="button"
                                        onClick={clearFilters}
                                        className="text-sm font-medium text-text-muted transition hover:text-text-main"
                                    >
                                        {t('estaciones.clearFilters')}
                                    </button>
                                )}

                                <span className="text-sm text-text-hint lg:ml-auto">
                                    {t('estaciones.total', { count: total })}
                                </span>
                            </div>
                        </section>

                        <section className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
                            {status === 'loading' && (
                                <div className="divide-y divide-border">
                                    {Array.from({ length: 4 }, (_, index) => (
                                        <div key={index} className="grid gap-4 px-5 py-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(13rem,0.9fr)_auto]">
                                            <div className="space-y-3">
                                                <div className="h-4 w-24 rounded bg-surface-2" />
                                                <div className="h-5 w-48 rounded bg-surface-2" />
                                                <div className="h-4 w-56 rounded bg-surface-2" />
                                            </div>
                                            <div className="space-y-3">
                                                <div className="h-4 w-28 rounded bg-surface-2" />
                                                <div className="h-4 w-40 rounded bg-surface-2" />
                                            </div>
                                            <div className="h-4 w-24 rounded bg-surface-2" />
                                        </div>
                                    ))}
                                </div>
                            )}

                            {status === 'error' && (
                                <div className="px-5 py-10 text-center text-text-muted">
                                    <p>{t('estaciones.loadError')}</p>
                                    <button
                                        type="button"
                                        onClick={loadEstaciones}
                                        className="mt-3 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                    >
                                        {t('common.actions.retry')}
                                    </button>
                                </div>
                            )}

                            {status === 'empty' && (
                                <div className="px-5 py-10 text-center text-text-muted">
                                    {t('estaciones.empty')}
                                </div>
                            )}

                            {status === 'ready' && (
                                <div className="divide-y divide-border">
                                    {rows.map((estacion) => (
                                        <StationRow
                                            key={estacion.id}
                                            estacion={estacion}
                                            canEdit={canEditEstaciones}
                                            canDelete={canDeleteEstaciones}
                                            onDelete={setDeleteTarget}
                                            t={t}
                                            loading={loading}
                                        />
                                    ))}
                                </div>
                            )}
                        </section>
                    </>
                )}

                <PaginationControls
                    pagination={{ current_page: currentPage, last_page: lastPage, total, from, to }}
                    onPageChange={goToPage}
                />
            </div>
        </AuthenticatedLayout>
    );
}
