import BadgeCliente from '@/Components/ui/BadgeCliente';
import BadgeEstado from '@/Components/ui/BadgeEstado';
import { router } from '@inertiajs/react';

const CONTEXT_OPTIONS = ['repsol', 'moeve', 'bp', 'galp', 'otros'];

const CONTEXT_LABEL = {
    repsol: 'Repsol',
    moeve:  'Moeve',
    bp:     'BP',
    galp:   'Galp',
    otros:  'OTROS CLIENTES',
};

function fmt(val) {
    if (val === null || val === undefined || val === '') return '—';
    return val;
}

export default function ClientesExcelView({
    rows = [],
    status = 'ready',
    loading = false,
    // filter state from parent
    search, setSearch,
    contextFilter, setContextFilter,
    hasFilters,
    onClearFilters,
    onDelete,
    onReload,
    total = 0,
    canCreate = true,
    canEdit = true,
    canDelete = true,
}) {
    const columns = [
        { label: 'Razón social',  key: 'razon_social',    width: 'w-44' },
        { label: 'Nombre com.',   key: 'nombre',           width: 'w-36' },
        { label: 'CIF',           key: 'cif',              width: 'w-28' },
        { label: 'Contexto',      key: 'operador',         width: 'w-24' },
        { label: 'Estado',        key: 'activo',           width: 'w-20' },
        { label: 'Web',           key: 'web',              width: 'w-36' },
        ...(canEdit || canDelete ? [{ label: 'Acciones', key: '_acciones', width: 'w-24' }] : []),
    ];

    return (
        <div className="space-y-3">
            {/* Barra de filtros compacta */}
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Buscar cliente…"
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />
                <select
                    value={contextFilter}
                    onChange={(e) => setContextFilter(e.target.value)}
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                >
                    <option value="">Todos los contextos</option>
                    {CONTEXT_OPTIONS.map((op) => (
                        <option key={op} value={op}>{CONTEXT_LABEL[op] ?? op}</option>
                    ))}
                </select>
                {hasFilters && (
                    <button
                        type="button"
                        onClick={onClearFilters}
                        className="text-xs font-medium text-text-muted hover:text-text-main"
                    >
                        Limpiar
                    </button>
                )}
                <span className="ml-auto text-xs text-text-hint">{total} clientes</span>
                {canCreate && (
                    <button
                        type="button"
                        onClick={() => router.visit(route('clientes.create'))}
                        className="rounded bg-(--ciete-red) px-3 py-1 text-xs font-semibold text-white hover:bg-(--ciete-red-dark)"
                    >
                        + Nuevo
                    </button>
                )}
            </div>

            {/* Tabla densa */}
            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="w-full min-w-[800px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    className={`${col.width} whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {col.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {/* Skeleton */}
                        {status === 'loading' && (
                            Array.from({ length: 4 }, (_, i) => (
                                <tr key={i} className="animate-pulse">
                                    {columns.map((col) => (
                                        <td key={col.key} className="px-2 py-2">
                                            <div className="h-3 rounded bg-surface-2 w-full max-w-24" />
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}

                        {/* Error */}
                        {status === 'error' && (
                            <tr>
                                <td colSpan={columns.length} className="px-3 py-10 text-center text-text-muted">
                                    Error cargando clientes.{' '}
                                    <button type="button" onClick={onReload} className="text-xs text-(--ciete-red)">
                                        Reintentar
                                    </button>
                                </td>
                            </tr>
                        )}

                        {/* Vacío */}
                        {status === 'empty' && (
                            <tr>
                                <td colSpan={columns.length} className="px-3 py-10 text-center text-text-hint">
                                    No hay clientes con los filtros aplicados.
                                </td>
                            </tr>
                        )}

                        {/* Filas */}
                        {status === 'ready' && rows.map((cliente) => (
                            <tr key={cliente.id} className="hover:bg-surface-2/50">
                                {/* Razón social */}
                                <td className="max-w-[176px] truncate px-2 py-1.5 font-medium text-text-main" title={cliente.razon_social}>
                                    {fmt(cliente.razon_social)}
                                </td>

                                {/* Nombre comercial */}
                                <td className="max-w-[144px] truncate px-2 py-1.5 text-text-muted" title={cliente.nombre}>
                                    {fmt(cliente.nombre)}
                                </td>

                                {/* CIF */}
                                <td className="whitespace-nowrap px-2 py-1.5 font-mono text-text-muted">
                                    {fmt(cliente.cif)}
                                </td>

                                {/* Contexto/operador badge */}
                                <td className="px-2 py-1.5">
                                    <BadgeCliente cliente={cliente.operador || cliente.nombre_comercial} />
                                </td>

                                {/* Estado */}
                                <td className="px-2 py-1.5">
                                    <BadgeEstado
                                        estado={cliente.activo ? 'activo' : 'inactivo'}
                                        label={cliente.activo ? 'Activo' : 'Inactivo'}
                                    />
                                </td>

                                {/* Web */}
                                <td className="max-w-[144px] truncate px-2 py-1.5 text-text-muted" title={cliente.web}>
                                    {cliente.web ? (
                                        <a
                                            href={cliente.web}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="underline decoration-dashed hover:text-text-main"
                                        >
                                            {cliente.web}
                                        </a>
                                    ) : '—'}
                                </td>

                                {(canEdit || canDelete) && (
                                    <td className="px-2 py-1.5">
                                        <div className="flex gap-2">
                                            {canEdit && (
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('clientes.edit', cliente.id))}
                                                    className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                                >
                                                    Editar
                                                </button>
                                            )}
                                            {canDelete && (
                                                <button
                                                    type="button"
                                                    onClick={() => onDelete(cliente)}
                                                    disabled={loading}
                                                    className="text-xs font-medium text-(--ciete-red) hover:text-(--ciete-red-dark) disabled:opacity-50"
                                                >
                                                    Desactivar
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
