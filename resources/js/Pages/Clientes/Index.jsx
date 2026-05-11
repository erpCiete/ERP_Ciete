import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeEstado from '@/Components/ui/BadgeEstado';
import ClientesExcelView from '@/Components/ui/ClientesExcelView';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import { useClientes } from '@/Hooks/useClientes';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useDeferredValue, useEffect, useState } from 'react';

const CONTEXT_OPTIONS = ['repsol', 'moeve', 'bp', 'galp', 'otros'];
const hasPermission = (user, permission, aliases = []) =>
    Boolean(user?.permission_slugs?.some((slug) => slug === permission || aliases.includes(slug)));

export default function ClientesIndex({ canCreate = true }) {
    const { t } = useI18n();
    const { visualStyle } = useTheme();
    const { getClientes, deleteCliente, loading } = useClientes();
    const { auth } = usePage().props;

    const isCieteExcel  = visualStyle === 'ciete_excel';
    const activeContext = auth?.user?.active_context;
    const canEditClientes = hasPermission(auth?.user, 'clientes.editar');
    const canDeleteClientes = hasPermission(auth?.user, 'clientes.eliminar');

    const [search, setSearch] = useState('');
    const [contextFilter, setContextFilter] = useState('');
    const [response, setResponse] = useState({ data: [], meta: {} });
    const [status, setStatus] = useState('loading');
    const [deleteTarget, setDeleteTarget] = useState(null);

    const deferredSearch = useDeferredValue(search);

    const loadClientes = useCallback(async () => {
        setStatus('loading');

        try {
            const result = await getClientes({
                search: deferredSearch || undefined,
                contexto: contextFilter || undefined,
                per_page: 50,
            });

            const nextResponse = result ?? { data: [], meta: {} };
            setResponse(nextResponse);
            setStatus((nextResponse.data?.length ?? 0) > 0 ? 'ready' : 'empty');
        } catch {
            setStatus('error');
        }
    }, [contextFilter, deferredSearch, getClientes]);

    useEffect(() => {
        loadClientes();
    }, [loadClientes]);

    const rows = response.data ?? [];
    const total = response.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || contextFilter !== '';

    const handleDelete = async () => {
        if (!deleteTarget) {
            return;
        }

        try {
            await deleteCliente(deleteTarget.id);
            setDeleteTarget(null);
            await loadClientes();
        } catch {
            setStatus('error');
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{t('clientes.header')}</h2>}
        >
            <Head title={t('clientes.headTitle')} />

            <ModalConfirmacion
                isOpen={Boolean(deleteTarget)}
                title={t('clientes.deleteTitle')}
                message={t('clientes.deleteMessage')}
                onClose={() => setDeleteTarget(null)}
                onConfirm={handleDelete}
                confirmLabel={t('clientes.deactivateAction')}
            />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow={t('nav.groups.masters')}
                    title={t('clientes.title')}
                    description={t('clientes.description')}
                    backHref={route('maestros.index')}
                    actions={canCreate ? <button
                        type="button"
                        onClick={() => router.visit(route('clientes.create'))}
                        className="inline-flex w-full items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) sm:w-auto"
                    >
                        + {t('clientes.create')}
                    </button> : null}
                />

                {activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        No puedes crear registros desde TODOS. Selecciona primero un contexto real: MOEVE, REPSOL u OTROS CLIENTES.
                    </div>
                )}

                {/* ── Vista Excel ──────────────────────────────────────────── */}
                {isCieteExcel ? (
                    <ClientesExcelView
                        rows={rows}
                        status={status}
                        loading={loading}
                        search={search} setSearch={setSearch}
                        contextFilter={contextFilter} setContextFilter={setContextFilter}
                        hasFilters={hasFilters}
                        onClearFilters={() => { setSearch(''); setContextFilter(''); }}
                        onDelete={setDeleteTarget}
                        onReload={loadClientes}
                        total={total}
                        canCreate={canCreate}
                        canEdit={canEditClientes}
                        canDelete={canDeleteClientes}
                    />
                ) : (
                <>

                <section className="ciete-filter-bar">
                    <div className="ciete-filter-row">
                        <input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t('clientes.searchPlaceholder')}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:flex-1"
                        />

                        <select
                            value={contextFilter}
                            onChange={(event) => setContextFilter(event.target.value)}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-56"
                        >
                            <option value="">{t('clientes.allContexts')}</option>
                            {CONTEXT_OPTIONS.map((option) => (
                                <option key={option} value={option}>
                                    {t(`clientes.operators.${option}`)}
                                </option>
                            ))}
                        </select>

                        {hasFilters && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSearch('');
                                    setContextFilter('');
                                }}
                                className="text-sm font-medium text-text-muted transition hover:text-text-main"
                            >
                                {t('clientes.clearFilters')}
                            </button>
                        )}

                        <span className="text-sm text-text-hint lg:ml-auto">{t('clientes.total', { count: total })}</span>
                    </div>
                </section>

                <section className="ciete-table-card">
                    <p className="ciete-table-hint">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="ciete-table-scroll">
                        <table className="min-w-[980px] w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    <th className="px-5 py-3">{t('clientes.columns.name')}</th>
                                    <th className="px-5 py-3">{t('clientes.columns.context')}</th>
                                    <th className="px-5 py-3">{t('clientes.columns.status')}</th>
                                    <th className="px-5 py-3">{t('clientes.columns.cif')}</th>
                                    <th className="px-5 py-3">{t('clientes.columns.website')}</th>
                                    <th className="px-5 py-3 text-right">{t('clientes.columns.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border text-text-main">
                                {status === 'loading' && (
                                    Array.from({ length: 4 }, (_, index) => (
                                        <tr key={index} className="animate-pulse">
                                            <td className="px-5 py-4"><div className="h-4 w-40 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-24 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-20 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-24 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-32 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="ml-auto h-4 w-20 rounded bg-surface-2" /></td>
                                        </tr>
                                    ))
                                )}

                                {status === 'error' && (
                                    <tr>
                                        <td colSpan={6} className="px-5 py-10 text-center text-text-muted">
                                            <p>{t('clientes.loadError')}</p>
                                            <button
                                                type="button"
                                                onClick={loadClientes}
                                                className="mt-3 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                            >
                                                {t('common.actions.retry')}
                                            </button>
                                        </td>
                                    </tr>
                                )}

                                {status === 'empty' && (
                                    <tr>
                                        <td colSpan={6} className="px-5 py-10 text-center text-text-muted">
                                            {t('clientes.empty')}
                                        </td>
                                    </tr>
                                )}

                                {status === 'ready' && rows.map((cliente) => (
                                    <tr key={cliente.id} className="hover:bg-surface-2/60">
                                        <td className="px-5 py-4 align-top">
                                            <div className="min-w-0">
                                                <p className="font-semibold text-text-main">{cliente.razon_social || cliente.nombre}</p>
                                                <p className="truncate text-xs text-text-muted">{cliente.nombre}</p>
                                            </div>
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <BadgeCliente cliente={cliente.operador || cliente.nombre_comercial} />
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <BadgeEstado
                                                estado={cliente.activo ? 'activo' : 'inactivo'}
                                                label={cliente.activo ? t('clientes.states.active') : t('clientes.states.inactive')}
                                            />
                                        </td>
                                        <td className="px-5 py-4 align-top text-text-muted">{cliente.cif || 'N/A'}</td>
                                        <td className="px-5 py-4 align-top text-text-muted break-all">{cliente.web || 'N/A'}</td>
                                        <td className="px-5 py-4 align-top">
                                            <div className="flex flex-wrap justify-end gap-3">
                                                {canEditClientes && (
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('clientes.edit', cliente.id))}
                                                        className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                    >
                                                        {t('common.actions.edit')}
                                                    </button>
                                                )}
                                                {canDeleteClientes && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeleteTarget(cliente)}
                                                        disabled={loading}
                                                        className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark) disabled:opacity-50"
                                                    >
                                                        {t('clientes.deactivateAction')}
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </>
            )}
            </div>
        </AuthenticatedLayout>
    );
}
