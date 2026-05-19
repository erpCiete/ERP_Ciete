import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import PaginationControls from '@/Components/ui/PaginationControls';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
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

export default function TarifarioLineas({ lineas, filters = {}, tarifarios = [], canCreate = false }) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;
    const [search, setSearch] = useState(filters.search ?? '');
    const [tarifario, setTarifario] = useState(filters.tarifario ?? '');
    const [activo, setActivo] = useState(filters.activo ?? '');
    const [deactivateTarget, setDeactivateTarget] = useState(null);
    const currentPage = lineas?.current_page ?? 1;
    const lastPage = lineas?.last_page ?? 1;
    const total = lineas?.total ?? (lineas?.data?.length ?? 0);
    const from = lineas?.from ?? (total === 0 ? 0 : 1);
    const to = lineas?.to ?? (total === 0 ? 0 : (lineas?.data?.length ?? 0));
    const canEdit = auth?.user?.is_director || hasPermission(auth?.user, 'tarifario_lineas.editar');
    const canDelete = auth?.user?.is_director || hasPermission(auth?.user, 'tarifario_lineas.eliminar');

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('maestros.tarifario-lineas.index'), { search, tarifario, activo, page: 1 }, { preserveState: true });
    };

    const deactivate = () => {
        if (!deactivateTarget) {
            return;
        }

        router.delete(route('maestros.tarifario-lineas.destroy', deactivateTarget.id_tarifario_linea), {
            preserveScroll: true,
            onSuccess: () => setDeactivateTarget(null),
        });
    };

    const goToPage = (page) => {
        if (page < 1 || page > lastPage || page === currentPage) {
            return;
        }

        router.get(route('maestros.tarifario-lineas.index'), { search, tarifario, activo, page }, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Lineas de tarifario</h2>}
        >
            <Head title="Lineas de tarifario" />

            <ModalConfirmacion
                isOpen={Boolean(deactivateTarget)}
                title="Desactivar linea"
                message={deactivateMessage}
                confirmLabel="Confirmar desactivacion"
                onClose={() => setDeactivateTarget(null)}
                onConfirm={deactivate}
            />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title="Lineas de tarifario"
                    description="Catalogo economico facturable. Los pedido_items pueden enlazar estas lineas, por eso se desactivan sin borrado fisico."
                    backHref={route('maestros.index')}
                    actions={
                        canCreate ? (
                            <Link
                                href={route('maestros.tarifario-lineas.create')}
                                className="inline-flex items-center gap-2 rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                            >
                                <Plus className="size-4" />
                                Nueva linea
                            </Link>
                        ) : null
                    }
                />

                {activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Selecciona MOEVE, REPSOL u OTROS CLIENTES para crear datos maestros.
                    </div>
                )}

                <form onSubmit={applyFilters} className="ciete-filter-bar">
                    <div className="ciete-filter-row">
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute left-3 top-2.5 size-4 text-text-hint" />
                            <input
                                type="search"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Buscar por código, grupo, actuación o descripción"
                                className="w-full rounded-md border border-border bg-surface py-2 pl-9 pr-3 text-sm"
                            />
                        </div>
                        <select
                            value={tarifario}
                            onChange={(event) => setTarifario(event.target.value)}
                            className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
                        >
                            <option value="">Todos los tarifarios</option>
                            {tarifarios.map((option) => (
                                <option key={option.id_tarifario} value={option.id_tarifario}>
                                    {option.nombre} {option.version ? `(${option.version})` : ''}
                                </option>
                            ))}
                        </select>
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
                                    <th className="px-4 py-3">Codigo</th>
                                    <th className="px-4 py-3">Actuacion</th>
                                    <th className="px-4 py-3">Tarifario</th>
                                    <th className="px-4 py-3">Tarifa base</th>
                                    <th className="px-4 py-3">Tarifa aplicada</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {(lineas.data ?? []).map((linea) => (
                                    <tr key={linea.id_tarifario_linea}>
                                        <td className="px-4 py-3 font-semibold text-text-main">{linea.codigo_tarifa}</td>
                                        <td className="px-4 py-3 text-text-main">
                                            <div>{linea.actuacion}</div>
                                            <div className="text-xs text-text-muted">{linea.grupo || '-'}</div>
                                        </td>
                                        <td className="px-4 py-3 text-text-muted">
                                            {linea.tarifario?.nombre || '-'} {linea.tarifario?.version ? `(${linea.tarifario.version})` : ''}
                                        </td>
                                        <td className="px-4 py-3 text-text-muted">{linea.tarifa_base}</td>
                                        <td className="px-4 py-3 text-text-muted">{linea.tarifa_aplicada}</td>
                                        <td className="px-4 py-3">
                                            <Badge active={linea.activo}>{linea.activo ? 'Activa' : 'Inactiva'}</Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="inline-flex gap-2">
                                                {canEdit && (
                                                    <Link
                                                        href={route(
                                                            'maestros.tarifario-lineas.edit',
                                                            linea.id_tarifario_linea,
                                                        )}
                                                        className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-main hover:bg-surface-2"
                                                    >
                                                        Editar
                                                    </Link>
                                                )}
                                                {canDelete && linea.activo && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeactivateTarget(linea)}
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
