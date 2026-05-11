import { router } from '@inertiajs/react';
import { useI18n } from '@/i18n';

function formatValue(value) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return value;
}

export default function EstacionesExcelView({
    rows = [],
    status = 'ready',
    canManage = false,
    canCreate = canManage,
    canEdit = canManage,
    canDelete = canManage,
    clientes = [],
    onDelete,
    onReload,
    search,
    setSearch,
    clienteId,
    setClienteId,
    municipio,
    setMunicipio,
    provincia,
    setProvincia,
    codigo,
    setCodigo,
    activoFilter,
    setActivoFilter,
    hasFilters,
    onClearFilters,
    total = 0,
}) {
    const { t } = useI18n();

    const columns = [
        { label: t('estaciones.columns.code'), key: 'codigo_estacion', width: 'w-28' },
        { label: t('estaciones.columns.name'), key: 'nombre', width: 'w-56' },
        { label: t('estaciones.columns.municipality'), key: 'municipio', width: 'w-32' },
        { label: t('estaciones.columns.province'), key: 'provincia', width: 'w-32' },
        { label: t('estaciones.columns.clientContext'), key: 'cliente_contexto', width: 'w-48' },
        { label: t('estaciones.columns.status'), key: 'estado', width: 'w-24' },
        ...(canEdit || canDelete ? [{ label: t('estaciones.columns.actions'), key: '_acciones', width: 'w-24' }] : []),
    ];

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <input
                    type="search"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder={t('estaciones.searchPlaceholder')}
                    className="min-w-56 rounded border border-border bg-surface px-2 py-1 text-xs text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />

                <input
                    type="search"
                    value={codigo}
                    onChange={(event) => setCodigo(event.target.value)}
                    placeholder={t('estaciones.filters.code')}
                    className="w-28 rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                />

                <select
                    value={clienteId}
                    onChange={(event) => setClienteId(event.target.value)}
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
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
                    onChange={(event) => setMunicipio(event.target.value)}
                    placeholder={t('estaciones.filters.municipality')}
                    className="w-32 rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                />

                <input
                    type="search"
                    value={provincia}
                    onChange={(event) => setProvincia(event.target.value)}
                    placeholder={t('estaciones.filters.province')}
                    className="w-32 rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                />

                <select
                    value={activoFilter}
                    onChange={(event) => setActivoFilter(event.target.value)}
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                >
                    <option value="">{t('estaciones.allStatuses')}</option>
                    <option value="1">{t('estaciones.filters.active')}</option>
                    <option value="0">{t('estaciones.filters.inactive')}</option>
                </select>

                {hasFilters && (
                    <button
                        type="button"
                        onClick={onClearFilters}
                        className="text-xs font-medium text-text-muted hover:text-text-main"
                    >
                        {t('estaciones.clearFilters')}
                    </button>
                )}

                <span className="ml-auto text-xs text-text-hint">{t('estaciones.total', { count: total })}</span>

                {canCreate && (
                    <button
                        type="button"
                        onClick={() => router.visit(route('estaciones.create'))}
                        className="rounded bg-(--ciete-red) px-3 py-1 text-xs font-semibold text-white hover:bg-(--ciete-red-dark)"
                    >
                        + {t('estaciones.create')}
                    </button>
                )}
            </div>

            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="w-full min-w-[920px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    className={`${column.width} whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-border bg-surface">
                        {status === 'loading' && (
                            Array.from({ length: 4 }, (_, index) => (
                                <tr key={index} className="animate-pulse">
                                    {columns.map((column) => (
                                        <td key={column.key} className="px-2 py-2">
                                            <div className="h-3 w-full max-w-24 rounded bg-surface-2" />
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}

                        {status === 'error' && (
                            <tr>
                                <td colSpan={columns.length} className="px-3 py-10 text-center text-text-muted">
                                    {t('estaciones.loadError')}{' '}
                                    <button type="button" onClick={onReload} className="text-xs text-(--ciete-red)">
                                        {t('common.actions.retry')}
                                    </button>
                                </td>
                            </tr>
                        )}

                        {status === 'empty' && (
                            <tr>
                                <td colSpan={columns.length} className="px-3 py-10 text-center text-text-hint">
                                    {t('estaciones.empty')}
                                </td>
                            </tr>
                        )}

                        {status === 'ready' && rows.map((estacion) => (
                            <tr key={estacion.id} className="hover:bg-surface-2/50">
                                <td className="whitespace-nowrap px-2 py-1.5 font-mono font-semibold text-(--ciete-red)">
                                    {formatValue(estacion.codigo_estacion)}
                                </td>

                                <td className="max-w-[224px] px-2 py-1.5" title={estacion.nombre}>
                                    <span className="block truncate font-medium text-text-main">{formatValue(estacion.nombre)}</span>
                                </td>

                                <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                    {formatValue(estacion.municipio || estacion.poblacion)}
                                </td>

                                <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                    {formatValue(estacion.provincia)}
                                </td>

                                <td className="max-w-[192px] px-2 py-1.5">
                                    <span className="block truncate text-text-main" title={estacion.empresa?.nombre}>
                                        {formatValue(estacion.empresa?.nombre)}
                                    </span>
                                    <span className="block truncate text-[11px] text-text-hint" title={estacion.contexto?.nombre}>
                                        {formatValue(estacion.contexto?.nombre)}
                                    </span>
                                </td>

                                <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                    {!estacion.activo ? (
                                        <span className="inline-block rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 ring-1 ring-gray-300">
                                            {t('estaciones.states.inactive')}
                                        </span>
                                    ) : (
                                        '—'
                                    )}
                                </td>

                                {(canEdit || canDelete) && (
                                    <td className="px-2 py-1.5">
                                        <div className="flex gap-2">
                                            {canEdit && (
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('estaciones.edit', estacion.id))}
                                                    className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                                >
                                                    {t('common.actions.edit')}
                                                </button>
                                            )}

                                            {canDelete && (
                                                <button
                                                    type="button"
                                                    onClick={() => onDelete(estacion)}
                                                    className="text-xs font-medium text-(--ciete-red) hover:text-(--ciete-red-dark)"
                                                >
                                                    {t('estaciones.deactivateAction')}
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
