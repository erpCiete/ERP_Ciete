import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeEstado from '@/Components/ui/BadgeEstado';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import { useClientes } from '@/Hooks/useClientes';
import { useEstaciones } from '@/Hooks/useEstaciones';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useDeferredValue, useEffect, useState } from 'react';

export default function EstacionesIndex() {
    const { t } = useI18n();
    const { auth } = usePage().props;
    const { getClientes } = useClientes();
    const { getEstaciones, deleteEstacion, loading } = useEstaciones();
    const canManageEstaciones = auth.user?.permission_slugs?.includes('estaciones.gestionar');

    const [clientes, setClientes] = useState([]);
    const [response, setResponse] = useState({ data: [], meta: {} });
    const [status, setStatus] = useState('loading');
    const [search, setSearch] = useState('');
    const [clienteId, setClienteId] = useState('');
    const [deleteTarget, setDeleteTarget] = useState(null);

    const deferredSearch = useDeferredValue(search);

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
                per_page: 50,
            });

            const nextResponse = result ?? { data: [], meta: {} };
            setResponse(nextResponse);
            setStatus((nextResponse.data?.length ?? 0) > 0 ? 'ready' : 'empty');
        } catch {
            setStatus('error');
        }
    }, [clienteId, deferredSearch, getEstaciones]);

    useEffect(() => {
        loadEstaciones();
    }, [loadEstaciones]);

    const rows = response.data ?? [];
    const total = response.meta?.pagination?.total ?? rows.length;
    const hasFilters = search !== '' || clienteId !== '';
    const totalColumns = canManageEstaciones ? 6 : 5;

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
                confirmLabel={t('common.actions.delete')}
            />

            <div className="space-y-6">
                <section className="flex flex-col gap-4 rounded-2xl border border-border bg-surface p-6 shadow-sm md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">{t('nav.groups.masters')}</p>
                        <h1 className="mt-2 text-2xl font-semibold text-text-main">{t('estaciones.title')}</h1>
                        <p className="mt-2 max-w-2xl text-sm text-text-muted">{t('estaciones.description')}</p>
                    </div>

                    {canManageEstaciones && (
                        <button
                            type="button"
                            onClick={() => router.visit(route('estaciones.create'))}
                            className="inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                        >
                            {t('estaciones.create')}
                        </button>
                    )}
                </section>

                <section className="rounded-2xl border border-border bg-surface p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
                        <input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t('estaciones.searchPlaceholder')}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:flex-1"
                        />

                        <select
                            value={clienteId}
                            onChange={(event) => setClienteId(event.target.value)}
                            className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red) lg:w-72"
                        >
                            <option value="">{t('estaciones.allClients')}</option>
                            {clientes.map((cliente) => (
                                <option key={cliente.id} value={cliente.id}>
                                    {cliente.razon_social || cliente.nombre}
                                </option>
                            ))}
                        </select>

                        {hasFilters && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSearch('');
                                    setClienteId('');
                                }}
                                className="text-sm font-medium text-text-muted transition hover:text-text-main"
                            >
                                {t('estaciones.clearFilters')}
                            </button>
                        )}

                        <span className="text-sm text-text-hint lg:ml-auto">{t('estaciones.total', { count: total })}</span>
                    </div>
                </section>

                <section className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                <tr>
                                    <th className="px-5 py-3">{t('estaciones.columns.station')}</th>
                                    <th className="px-5 py-3">{t('estaciones.columns.client')}</th>
                                    <th className="px-5 py-3">{t('estaciones.columns.code')}</th>
                                    <th className="px-5 py-3">{t('estaciones.columns.location')}</th>
                                    <th className="px-5 py-3">{t('estaciones.columns.status')}</th>
                                    {canManageEstaciones && <th className="px-5 py-3 text-right">{t('estaciones.columns.actions')}</th>}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border text-text-main">
                                {status === 'loading' && (
                                    Array.from({ length: 4 }, (_, index) => (
                                        <tr key={index} className="animate-pulse">
                                            <td className="px-5 py-4"><div className="h-4 w-40 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-28 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-32 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-32 rounded bg-surface-2" /></td>
                                            <td className="px-5 py-4"><div className="h-4 w-20 rounded bg-surface-2" /></td>
                                            {canManageEstaciones && <td className="px-5 py-4"><div className="ml-auto h-4 w-20 rounded bg-surface-2" /></td>}
                                        </tr>
                                    ))
                                )}

                                {status === 'error' && (
                                    <tr>
                                        <td colSpan={totalColumns} className="px-5 py-10 text-center text-text-muted">
                                            <p>{t('estaciones.loadError')}</p>
                                            <button
                                                type="button"
                                                onClick={loadEstaciones}
                                                className="mt-3 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                                            >
                                                {t('common.actions.retry')}
                                            </button>
                                        </td>
                                    </tr>
                                )}

                                {status === 'empty' && (
                                    <tr>
                                        <td colSpan={totalColumns} className="px-5 py-10 text-center text-text-muted">
                                            {t('estaciones.empty')}
                                        </td>
                                    </tr>
                                )}

                                {status === 'ready' && rows.map((estacion) => (
                                    <tr key={estacion.id} className="hover:bg-surface-2/60">
                                        <td className="px-5 py-4 align-top">
                                            <div>
                                                <p className="font-semibold text-text-main">{estacion.nombre}</p>
                                                <p className="text-xs text-text-muted">{estacion.estado || 'N/A'}</p>
                                            </div>
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <div className="space-y-2">
                                                <p className="text-sm text-text-main">{estacion.empresa?.nombre || 'N/A'}</p>
                                                <BadgeCliente cliente={estacion.operador || estacion.empresa?.nombre_comercial} />
                                            </div>
                                        </td>
                                        <td className="px-5 py-4 align-top text-text-muted">
                                            <p className="text-sm font-medium">{estacion.codigo_estacion || 'N/A'}</p>
                                        </td>
                                        <td className="px-5 py-4 align-top text-text-muted">
                                            {[estacion.direccion, estacion.poblacion, estacion.provincia].filter(Boolean).join(', ') || 'N/A'}
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <BadgeEstado
                                                estado={estacion.activo ? 'activo' : 'inactivo'}
                                                label={estacion.activo ? t('estaciones.states.active') : t('estaciones.states.inactive')}
                                            />
                                        </td>
                                        {canManageEstaciones && (
                                            <td className="px-5 py-4 align-top">
                                                <div className="flex justify-end gap-3">
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('estaciones.edit', estacion.id))}
                                                        className="text-sm font-medium text-text-main transition hover:text-(--ciete-red)"
                                                    >
                                                        {t('common.actions.edit')}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeleteTarget(estacion)}
                                                        disabled={loading}
                                                        className="text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark) disabled:opacity-50"
                                                    >
                                                        {t('common.actions.delete')}
                                                    </button>
                                                </div>
                                            </td>
                                        )}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
