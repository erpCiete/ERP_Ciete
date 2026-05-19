import ContextualPageHeader from '@/Components/ContextualPageHeader';
import InputError from '@/Components/InputError';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import PaginationControls from '@/Components/ui/PaginationControls';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';

const deactivateMessage =
    'Vas a desactivar este registro maestro. No se eliminará el histórico relacionado, pero dejará de estar disponible para nuevas operaciones. ¿Quieres continuar?';

const hasPermission = (user, permission) => Boolean(user?.permission_slugs?.includes(permission));

function Badge({ active, children }) {
    return (
        <span
            className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${
                active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'
            }`}
        >
            {children}
        </span>
    );
}

export default function SociedadesFacturadorasIndex({
    relaciones,
    filters = {},
    contratos = [],
    empresas = [],
    canCreate = false,
}) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;
    const [search, setSearch] = useState(filters.search ?? '');
    const [activo, setActivo] = useState(filters.activo ?? '');
    const [deactivateTarget, setDeactivateTarget] = useState(null);
    const [editTarget, setEditTarget] = useState(null);
    const currentPage = relaciones?.current_page ?? 1;
    const lastPage = relaciones?.last_page ?? 1;
    const total = relaciones?.total ?? (relaciones?.data?.length ?? 0);
    const from = relaciones?.from ?? (total === 0 ? 0 : 1);
    const to = relaciones?.to ?? (total === 0 ? 0 : (relaciones?.data?.length ?? 0));

    const canEdit = auth?.user?.is_director || hasPermission(auth?.user, 'sociedades_facturadoras.editar');
    const canDelete = auth?.user?.is_director || hasPermission(auth?.user, 'sociedades_facturadoras.eliminar');

    const form = useForm({
        id_contrato: '',
        id_empresa: '',
        activo: true,
        observaciones: '',
    });

    const editForm = useForm({
        id_contrato: '',
        id_empresa: '',
        activo: true,
        observaciones: '',
    });

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('maestros.sociedades.index'), { search, activo, page: 1 }, { preserveState: true });
    };

    const submit = (event) => {
        event.preventDefault();
        form.post(route('maestros.sociedades.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const openEdit = (relacion) => {
        setEditTarget(relacion);
        editForm.setData({
            id_contrato: relacion.id_contrato,
            id_empresa: relacion.id_empresa,
            activo: Boolean(relacion.activo),
            observaciones: relacion.observaciones ?? '',
        });
    };

    const update = (event) => {
        event.preventDefault();
        if (!editTarget) {
            return;
        }

        editForm.put(route('maestros.sociedades.update', editTarget.id), {
            preserveScroll: true,
            onSuccess: () => setEditTarget(null),
        });
    };

    const deactivate = () => {
        if (!deactivateTarget) {
            return;
        }

        router.delete(route('maestros.sociedades.destroy', deactivateTarget.id), {
            preserveScroll: true,
            onSuccess: () => setDeactivateTarget(null),
        });
    };

    const goToPage = (page) => {
        if (page < 1 || page > lastPage || page === currentPage) {
            return;
        }

        router.get(route('maestros.sociedades.index'), { search, activo, page }, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Sociedades facturadoras</h2>}
        >
            <Head title="Sociedades facturadoras permitidas" />

            <ModalConfirmacion
                isOpen={Boolean(deactivateTarget)}
                title="Desactivar sociedad permitida"
                message={deactivateMessage}
                confirmLabel="Confirmar desactivacion"
                onClose={() => setDeactivateTarget(null)}
                onConfirm={deactivate}
            />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title="Sociedades facturadoras permitidas"
                    description="Relacion entre contrato y empresa facturadora. Esta relacion sigue validando sociedad/CIF en facturas."
                    backHref={route('maestros.index')}
                />

                {activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Selecciona MOEVE, REPSOL u OTROS CLIENTES para crear datos maestros.
                    </div>
                )}

                {canCreate && (
                    <form onSubmit={submit} className="mb-5 rounded-lg border border-border bg-surface p-4">
                        <h3 className="text-sm font-semibold text-text-main">Asignar sociedad a contrato</h3>
                        <div className="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <label className="block">
                                <span className="text-xs font-semibold uppercase text-text-muted">Contrato</span>
                                <select
                                    value={form.data.id_contrato}
                                    onChange={(event) => form.setData('id_contrato', event.target.value)}
                                    className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                                >
                                    <option value="">Selecciona contrato</option>
                                    {contratos.map((contrato) => (
                                        <option key={contrato.id_contrato} value={contrato.id_contrato}>
                                            {contrato.codigo_contrato} - {contrato.nombre || 'Sin nombre'}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={form.errors.id_contrato} className="mt-1" />
                            </label>
                            <label className="block">
                                <span className="text-xs font-semibold uppercase text-text-muted">Empresa con CIF</span>
                                <select
                                    value={form.data.id_empresa}
                                    onChange={(event) => form.setData('id_empresa', event.target.value)}
                                    className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                                >
                                    <option value="">Selecciona empresa</option>
                                    {empresas.map((empresa) => (
                                        <option key={empresa.id_empresa} value={empresa.id_empresa}>
                                            {empresa.nombre} ({empresa.cif})
                                        </option>
                                    ))}
                                </select>
                                <InputError message={form.errors.id_empresa} className="mt-1" />
                            </label>
                            <label className="block xl:col-span-2">
                                <span className="text-xs font-semibold uppercase text-text-muted">Observaciones</span>
                                <input
                                    value={form.data.observaciones}
                                    onChange={(event) => form.setData('observaciones', event.target.value)}
                                    className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                                />
                            </label>
                        </div>
                        <div className="mt-4 flex justify-end">
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60"
                            >
                                Asignar sociedad
                            </button>
                        </div>
                    </form>
                )}

                {editTarget && (
                    <form onSubmit={update} className="mb-5 rounded-lg border border-border bg-surface p-4">
                        <h3 className="text-sm font-semibold text-text-main">Editar sociedad permitida</h3>
                        <div className="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <select
                                value={editForm.data.id_contrato}
                                onChange={(event) => editForm.setData('id_contrato', event.target.value)}
                                className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            >
                                {contratos.map((contrato) => (
                                    <option key={contrato.id_contrato} value={contrato.id_contrato}>
                                        {contrato.codigo_contrato} - {contrato.nombre || 'Sin nombre'}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={editForm.data.id_empresa}
                                onChange={(event) => editForm.setData('id_empresa', event.target.value)}
                                className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            >
                                {empresas.map((empresa) => (
                                    <option key={empresa.id_empresa} value={empresa.id_empresa}>
                                        {empresa.nombre} ({empresa.cif})
                                    </option>
                                ))}
                            </select>
                            <label className="flex items-center gap-2 text-sm font-semibold text-text-main">
                                <input
                                    type="checkbox"
                                    checked={Boolean(editForm.data.activo)}
                                    onChange={(event) => editForm.setData('activo', event.target.checked)}
                                    className="rounded border-border text-(--ciete-red)"
                                />
                                Activa
                            </label>
                            <input
                                value={editForm.data.observaciones}
                                onChange={(event) => editForm.setData('observaciones', event.target.value)}
                                className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
                                placeholder="Observaciones"
                            />
                        </div>
                        <div className="mt-4 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => setEditTarget(null)}
                                className="rounded-md border border-border px-4 py-2 text-sm font-semibold text-text-main hover:bg-surface-2"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                disabled={editForm.processing}
                                className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                            >
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                )}

                <form onSubmit={applyFilters} className="ciete-filter-bar">
                    <div className="ciete-filter-row">
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute left-3 top-2.5 size-4 text-text-hint" />
                            <input
                                type="search"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Buscar por contrato, sociedad o CIF"
                                className="w-full rounded-md border border-border bg-surface py-2 pl-9 pr-3 text-sm"
                            />
                        </div>
                        <select
                            value={activo}
                            onChange={(event) => setActivo(event.target.value)}
                            className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
                        >
                            <option value="">Activo e inactivo</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <button className="rounded-md bg-surface-2 px-4 py-2 text-sm font-semibold text-text-main">
                            Filtrar
                        </button>
                    </div>
                </form>

                <section className="overflow-hidden rounded-lg border border-border bg-surface">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-border text-sm">
                            <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">
                                <tr>
                                    <th className="px-4 py-3">Contrato</th>
                                    <th className="px-4 py-3">Sociedad</th>
                                    <th className="px-4 py-3">CIF</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {(relaciones.data ?? []).map((relacion) => (
                                    <tr key={relacion.id}>
                                        <td className="px-4 py-3 text-text-main">
                                            {relacion.contrato?.codigo_contrato} - {relacion.contrato?.nombre || 'Sin nombre'}
                                        </td>
                                        <td className="px-4 py-3 text-text-main">{relacion.empresa?.nombre || '-'}</td>
                                        <td className="px-4 py-3 text-text-muted">{relacion.empresa?.cif || '-'}</td>
                                        <td className="px-4 py-3">
                                            <Badge active={relacion.activo}>{relacion.activo ? 'Activa' : 'Inactiva'}</Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="inline-flex gap-2">
                                                {canEdit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => openEdit(relacion)}
                                                        className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-main hover:bg-surface-2"
                                                    >
                                                        Editar
                                                    </button>
                                                )}
                                                {canDelete && relacion.activo && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeactivateTarget(relacion)}
                                                        className="rounded-md border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                                                    >
                                                        Desactivar
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

                <PaginationControls
                    pagination={{ current_page: currentPage, last_page: lastPage, total, from, to }}
                    onPageChange={goToPage}
                />
            </div>
        </AuthenticatedLayout>
    );
}
