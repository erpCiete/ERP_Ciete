import ContextualPageHeader from '@/Components/ContextualPageHeader';
import ModalConfirmacion from '@/Components/ui/ModalConfirmacion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';

const deactivateMessage =
    'Vas a desactivar este registro maestro. No se eliminará el histórico relacionado, pero dejará de estar disponible para nuevas operaciones. ¿Quieres continuar?';

const hasPermission = (user, permission) => Boolean(user?.permission_slugs?.includes(permission));

function StatusBadge({ active, children }) {
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

function Pagination({ links = [] }) {
    if (!links?.length) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-2">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url || '#'}
                    preserveScroll
                    className={`rounded-md border px-3 py-2 text-sm ${
                        link.active
                            ? 'border-(--ciete-red) bg-(--ciete-red) text-white'
                            : 'border-border text-text-muted hover:bg-surface-2'
                    } ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
}

export default function ContratosIndex({ contratos, filters = {}, canCreate = false }) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;
    const [search, setSearch] = useState(filters.search ?? '');
    const [estado, setEstado] = useState(filters.estado ?? '');
    const [activo, setActivo] = useState(filters.activo ?? '');
    const [deactivateTarget, setDeactivateTarget] = useState(null);

    const canEdit = auth?.user?.is_director || hasPermission(auth?.user, 'contratos.editar');
    const canDelete = auth?.user?.is_director || hasPermission(auth?.user, 'contratos.eliminar');

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('maestros.contratos.index'), { search, estado, activo }, { preserveState: true });
    };

    const deactivate = () => {
        if (!deactivateTarget) {
            return;
        }

        router.delete(route('maestros.contratos.destroy', deactivateTarget.id_contrato), {
            preserveScroll: true,
            onSuccess: () => setDeactivateTarget(null),
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Contratos</h2>}>
            <Head title="Contratos maestros" />

            <ModalConfirmacion
                isOpen={Boolean(deactivateTarget)}
                title="Desactivar contrato"
                message={deactivateMessage}
                confirmLabel="Confirmar desactivacion"
                onClose={() => setDeactivateTarget(null)}
                onConfirm={deactivate}
            />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title="Contratos"
                    description="Contratos base asociados a empresas cliente y contexto. No crean trabajos ni facturas."
                    backHref={route('maestros.index')}
                    actions={
                        canCreate ? (
                            <Link
                                href={route('maestros.contratos.create')}
                                className="inline-flex items-center gap-2 rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                            >
                                <Plus className="size-4" />
                                Nuevo contrato
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
                                placeholder="Buscar por código, nombre o empresa"
                                className="w-full rounded-md border border-border bg-surface py-2 pl-9 pr-3 text-sm text-text-main"
                            />
                        </div>
                        <select
                            value={estado}
                            onChange={(event) => setEstado(event.target.value)}
                            className="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main"
                        >
                            <option value="">Todos los estados</option>
                            <option value="vigente">Vigente</option>
                            <option value="expirado">Expirado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        <select
                            value={activo}
                            onChange={(event) => setActivo(event.target.value)}
                            className="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main"
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
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Empresa</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3">Uso</th>
                                    <th className="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {(contratos.data ?? []).map((contrato) => (
                                    <tr key={contrato.id_contrato}>
                                        <td className="px-4 py-3 font-semibold text-text-main">{contrato.codigo_contrato}</td>
                                        <td className="px-4 py-3 text-text-main">{contrato.nombre || '-'}</td>
                                        <td className="px-4 py-3 text-text-muted">{contrato.empresa?.nombre || '-'}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap gap-2">
                                                <StatusBadge active={contrato.activo}>
                                                    {contrato.activo ? 'Activo' : 'Inactivo'}
                                                </StatusBadge>
                                                <StatusBadge active={contrato.estado === 'vigente'}>
                                                    {contrato.estado}
                                                </StatusBadge>
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-text-muted">
                                            {contrato.tarifarios_count} tarifarios · {contrato.sociedades_facturadoras_count} sociedades
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="inline-flex gap-2">
                                                {canEdit && (
                                                    <Link
                                                        href={route('maestros.contratos.edit', contrato.id_contrato)}
                                                        className="rounded-md border border-border px-3 py-1.5 text-xs font-semibold text-text-main hover:bg-surface-2"
                                                    >
                                                        Editar
                                                    </Link>
                                                )}
                                                {canDelete && contrato.activo && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setDeactivateTarget(contrato)}
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

                <div className="mt-4">
                    <Pagination links={contratos.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
