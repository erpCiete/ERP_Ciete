import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useMemo, useState } from 'react';

const formatAuditValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
};

export default function AuditIndex({
    logs,
    filtros = {},
    returnRoute = 'admin.dashboard',
    usuariosFiltro = [],
    modulosFiltro = [],
    accionesFiltro = [],
    contextosFiltro = [],
}) {
    const { t } = useI18n();

    const [search, setSearch] = useState(filtros.search ?? '');
    const [idUsuario, setIdUsuario] = useState(filtros.id_usuario ? String(filtros.id_usuario) : '');
    const [modulo, setModulo] = useState(filtros.modulo ?? '');
    const [accion, setAccion] = useState(filtros.accion ?? '');
    const [idContexto, setIdContexto] = useState(filtros.id_contexto ? String(filtros.id_contexto) : '');
    const [fechaDesde, setFechaDesde] = useState(filtros.fecha_desde ?? '');
    const [fechaHasta, setFechaHasta] = useState(filtros.fecha_hasta ?? '');

    const rows = logs?.data ?? [];
    const pagination = logs?.meta ?? logs;

    const buildParams = useCallback(
        (overrides = {}) => ({
            search: overrides.search !== undefined ? overrides.search : search,
            id_usuario: overrides.id_usuario !== undefined ? overrides.id_usuario : idUsuario,
            modulo: overrides.modulo !== undefined ? overrides.modulo : modulo,
            accion: overrides.accion !== undefined ? overrides.accion : accion,
            id_contexto: overrides.id_contexto !== undefined ? overrides.id_contexto : idContexto,
            fecha_desde: overrides.fecha_desde !== undefined ? overrides.fecha_desde : fechaDesde,
            fecha_hasta: overrides.fecha_hasta !== undefined ? overrides.fecha_hasta : fechaHasta,
            page: overrides.page,
        }),
        [search, idUsuario, modulo, accion, idContexto, fechaDesde, fechaHasta],
    );

    const aplicarFiltros = useCallback(
        (overrides = {}) => {
            const params = buildParams(overrides);
            Object.keys(params).forEach((key) => {
                if (!params[key]) {
                    delete params[key];
                }
            });

            router.get(route('admin.audit'), params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [buildParams],
    );

    const limpiarFiltros = () => {
        setSearch('');
        setIdUsuario('');
        setModulo('');
        setAccion('');
        setIdContexto('');
        setFechaDesde('');
        setFechaHasta('');

        router.get(route('admin.audit'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const moduloOptions = useMemo(() => (Array.isArray(modulosFiltro) ? modulosFiltro : []), [modulosFiltro]);
    const accionOptions = useMemo(() => (Array.isArray(accionesFiltro) ? accionesFiltro : []), [accionesFiltro]);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('adminAudit.header')}
                </h2>
            }
        >
            <Head title={t('adminAudit.headTitle')} />

            <div className="ciete-page ciete-page-wide">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminAudit.panelLabel')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('adminAudit.title')}</h2>
                        <p className="mt-1 text-sm text-text-muted">{t('adminAudit.subtitle')}</p>
                    </div>
                </div>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        aplicarFiltros({ page: 1 });
                    }}
                    className="grid gap-2 rounded-[12px] border border-border bg-surface p-3 md:grid-cols-3 xl:grid-cols-7"
                >
                    <input
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('adminAudit.filters.searchPlaceholder')}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                    />

                    <select
                        value={idUsuario}
                        onChange={(event) => setIdUsuario(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    >
                        <option value="">{t('adminAudit.filters.allUsers')}</option>
                        {usuariosFiltro.map((user) => (
                            <option key={user.id_usuario} value={user.id_usuario}>
                                {`${user.nombre ?? ''} ${user.apellidos ?? ''}`.trim()}
                            </option>
                        ))}
                    </select>

                    <select
                        value={modulo}
                        onChange={(event) => setModulo(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    >
                        <option value="">{t('adminAudit.filters.allModules')}</option>
                        {moduloOptions.map((item) => (
                            <option key={item} value={item}>
                                {item}
                            </option>
                        ))}
                    </select>

                    <select
                        value={accion}
                        onChange={(event) => setAccion(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    >
                        <option value="">{t('adminAudit.filters.allActions')}</option>
                        {accionOptions.map((item) => (
                            <option key={item} value={item}>
                                {item}
                            </option>
                        ))}
                    </select>

                    <select
                        value={idContexto}
                        onChange={(event) => setIdContexto(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    >
                        <option value="">{t('adminAudit.filters.allContexts')}</option>
                        {contextosFiltro.map((contexto) => (
                            <option key={contexto.id_contexto} value={contexto.id_contexto}>
                                {contexto.nombre}
                            </option>
                        ))}
                    </select>

                    <input
                        type="date"
                        value={fechaDesde}
                        onChange={(event) => setFechaDesde(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    />

                    <input
                        type="date"
                        value={fechaHasta}
                        onChange={(event) => setFechaHasta(event.target.value)}
                        className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                    />

                    <div className="flex gap-2 md:col-span-3 xl:col-span-7">
                        <button
                            type="submit"
                            className="rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90"
                        >
                            {t('adminAudit.filters.apply')}
                        </button>
                        <button
                            type="button"
                            onClick={limpiarFiltros}
                            className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-xs font-semibold text-text-muted transition hover:bg-surface"
                        >
                            {t('adminAudit.filters.clear')}
                        </button>
                    </div>
                </form>

                <div className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <div className="ciete-table-scroll">
                        <table className="min-w-[1400px] w-full">
                            <thead className="border-b border-border">
                                <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    <th className="px-4 py-2">{t('adminAudit.cols.date')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.user')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.module')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.action')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.record')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.field')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.previousValue')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.newValue')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.context')}</th>
                                    <th className="px-4 py-2">{t('adminAudit.cols.description')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={10} className="px-4 py-8 text-center text-sm text-text-hint">
                                            {t('adminAudit.noLogs')}
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((log) => {
                                        const entityType = log.entity_type_resuelto || '—';
                                        const entityId = log.entity_id_resuelto ? `#${log.entity_id_resuelto}` : '';
                                        const affectedRecord = `${entityType} ${entityId}`.trim();

                                        return (
                                            <tr key={log.id_audit} className="transition hover:bg-surface-2">
                                                <td className="px-4 py-3 text-xs text-text-muted">
                                                    {log.created_at ? new Date(log.created_at).toLocaleString('es-ES') : '—'}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-text-main">
                                                    {log.usuario
                                                        ? `${log.usuario.nombre} ${log.usuario.apellidos ?? ''}`.trim()
                                                        : '—'}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-text-muted">
                                                    {log.modulo_resuelto ?? log.modulo ?? log.tabla ?? '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-[9px] font-bold text-accent">
                                                        {log.accion}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-xs text-text-muted">{affectedRecord}</td>
                                                <td className="px-4 py-3 text-xs text-text-muted">{log.campo ?? '—'}</td>
                                                <td className="max-w-[220px] px-4 py-3 text-xs text-text-hint">
                                                    <div className="line-clamp-3 break-words">{formatAuditValue(log.valor_anterior_resuelto)}</div>
                                                </td>
                                                <td className="max-w-[220px] px-4 py-3 text-xs text-text-hint">
                                                    <div className="line-clamp-3 break-words">{formatAuditValue(log.valor_nuevo_resuelto)}</div>
                                                </td>
                                                <td className="px-4 py-3 text-xs text-text-muted">
                                                    {log.contexto?.nombre ?? (log.id_contexto ? `#${log.id_contexto}` : '—')}
                                                </td>
                                                <td className="max-w-[260px] px-4 py-3 text-xs text-text-hint">
                                                    <div className="line-clamp-3 break-words">{log.descripcion ?? '—'}</div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {pagination?.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 pt-2">
                        {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => aplicarFiltros({ page })}
                                className={`rounded-md px-3 py-1 text-xs font-medium transition ${
                                    page === pagination.current_page
                                        ? 'bg-primary text-white'
                                        : 'border border-border bg-surface text-text-muted hover:bg-surface-2'
                                }`}
                            >
                                {page}
                            </button>
                        ))}
                    </div>
                )}

                <div className="pt-2">
                    <Link href={route(returnRoute)} className="text-xs text-text-hint hover:text-text-muted">
                        ← {t('adminAudit.backToPanel')}
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
