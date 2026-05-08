import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
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

export default function TarifariosIndex({ tarifarios, filters = {}, canCreate = false }) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;
    const [search, setSearch] = useState(filters.search ?? '');
    const [activo, setActivo] = useState(filters.activo ?? '');
    const [deactivateTarget, setDeactivateTarget] = useState(null);
    const canEdit = auth?.user?.is_director || hasPermission(auth?.user, 'tarifarios.editar');
    const canDelete = auth?.user?.is_director || hasPermission(auth?.user, 'tarifarios.eliminar');

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('maestros.tarifarios.index'), { search, activo }, { preserveState: true });
    };

    const deactivate = () => {
        if (!deactivateTarget) {
            return;
        }

        router.delete(route('maestros.tarifarios.destroy', deactivateTarget.id_tarifario), {
            preserveScroll: true,
            onSuccess: () => setDeactivateTarget(null),
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Tarifarios</h2>}>
            <Head title="Tarifarios maestros" />

            <ModalConfirmacion
                isOpen={Boolean(deactivateTarget)}
                title="Desactivar tarifario"
                message={deactivateMessage}
                confirmLabel="Confirmar desactivacion"
                onClose={() => setDeactivateTarget(null)}
                onConfirm={deactivate}
            />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title="Tarifarios"
                    description="Versiones de tarifa por contrato. Las lineas se mantienen separadas para preservar su trazabilidad."
                    backHref={route('maestros.index')}
                    actions={
                        canCreate ? (
                            <Link
                                href={route('maestros.tarifarios.create')}
                                className="inline-flex items-center gap-2 rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                            >
                                <Plus className="size-4" />
                                Nuevo tarifario
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
                                placeholder="Buscar por nombre, version o contrato"
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
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Version</th>
                                    <th className="px-4 py-3">Contrato</th>
                                    <th className="px-4 py-3">Vigencia</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3">Lineas</th>
                                    <th className="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {(tarifarios.data ?? []).map((tarifario) => (
                                    <tr key={tarifario.id_tarifario}>
                                        <td className="px-4 py-3 font-semibold text-text-main">{tarifario.nombre}</td>
                                        <td className="px-4 py-3 text-text-muted">{tarifario.version || '-'}</td>
                                        <td className="px-4 py-3 text-text-muted">
                                            {tarifario.contrato?.codigo_contrato || '-'}
                                        </td>
                                        <td className="px-4 py-3 text-text-muted">
                                            {tarifario.fecha_inicio_vigencia || '-'} / {tarifario.fecha_fin_vigencia || '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge active={tarifario.activo}>{tarifario.activo ? 'Activo' : 'Inactivo'}</Badge>
                                        </td>
                                        <td className="px-4 py-3 text-text-muted">{tarifario.lineas_count}</td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="inline-flex gap-2">
                                                <Link
                                                    href={route('maestros.tarifario-lineas.index', {
                                                        tarifario: tarifario.id_tarifario,
                                                    })}
                                                    className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-main hover:bg-surface-2"
                                                >
                                                    Lineas
                                                </Link>
                                                {canEdit && (
                                                    <Link
                                                        href={route('maestros.tarifarios.edit', tarifario.id_tarifario)}
                                                        className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-main hover:bg-surface-2"
                                                    >
                                                        Editar
                                                    </Link>
                                                )}
                                                {canDelete && tarifario.activo && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeactivateTarget(tarifario)}
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
            </div>
        </AuthenticatedLayout>
    );
}
