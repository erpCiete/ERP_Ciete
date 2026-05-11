import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function StatusBadge({ status, t }) {
    const map = {
        pending: 'bg-state-pending-bg text-state-pending-text',
        in_review: 'bg-primary/10 text-primary',
        resolved: 'bg-state-done-bg text-state-done-text',
        archived: 'bg-surface-2 text-text-hint',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest ${map[status] ?? map.pending}`}>
            {t(`supportPage.status.${status}`)}
        </span>
    );
}

function PriorityBadge({ priority, t }) {
    const map = {
        baja: 'bg-surface-2 text-text-hint',
        normal: 'bg-surface-2 text-text-hint',
        alta: 'bg-state-pending-bg text-state-pending-text',
        urgente: 'bg-state-blocked-bg text-state-blocked-text',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${map[priority] ?? map.normal}`}>
            {t(`supportPage.priority.${priority}`)}
        </span>
    );
}

export default function AdminSupportIndex({ tickets, filters = {}, supportReady = true, supportSchemaWarning = null }) {
    const { t } = useI18n();
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [priority, setPriority] = useState(filters.priority ?? '');

    const applyFilters = (overrides = {}) => {
        const next = {
            search: overrides.search !== undefined ? overrides.search : search,
            status: overrides.status !== undefined ? overrides.status : status,
            priority: overrides.priority !== undefined ? overrides.priority : priority,
        };

        Object.keys(next).forEach((key) => {
            if (!next[key]) {
                delete next[key];
            }
        });

        router.get(route('admin.support.index'), next, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters.search ?? '')) {
                applyFilters({ search });
            }
        }, 400);

        return () => clearTimeout(timer);
    }, [search]);

    return (
        <AuthenticatedLayout header={t('supportAdmin.header')}>
            <Head title={t('supportAdmin.headTitle')} />

            <div className="ciete-page ciete-page-wide">
                <div>
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                        {t('supportAdmin.eyebrow')}
                    </p>
                    <h2 className="text-xl font-semibold text-text-main">{t('supportAdmin.title')}</h2>
                </div>

                {!supportReady && (
                    <div className="rounded-xl border border-state-pending-text/20 bg-state-pending-bg px-4 py-3 text-sm text-state-pending-text">
                        {supportSchemaWarning ?? t('supportPage.schemaWarning')}
                    </div>
                )}

                <div className="grid gap-3 rounded-xl border border-border bg-surface p-4 xl:grid-cols-[minmax(0,1fr)_180px_180px]">
                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('supportAdmin.search')}
                        </label>
                        <input
                            type="text"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t('supportAdmin.searchPlaceholder')}
                            disabled={!supportReady}
                            className="w-full rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-hidden"
                        />
                    </div>
                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('supportAdmin.filterStatus')}
                        </label>
                        <select
                            value={status}
                            onChange={(event) => {
                                setStatus(event.target.value);
                                applyFilters({ status: event.target.value });
                            }}
                            disabled={!supportReady}
                            className="w-full rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden"
                        >
                            <option value="">{t('supportAdmin.allStatuses')}</option>
                            <option value="pending">{t('supportPage.status.pending')}</option>
                            <option value="in_review">{t('supportPage.status.in_review')}</option>
                            <option value="resolved">{t('supportPage.status.resolved')}</option>
                            <option value="archived">{t('supportPage.status.archived')}</option>
                        </select>
                    </div>
                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('supportAdmin.filterPriority')}
                        </label>
                        <select
                            value={priority}
                            onChange={(event) => {
                                setPriority(event.target.value);
                                applyFilters({ priority: event.target.value });
                            }}
                            disabled={!supportReady}
                            className="w-full rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden"
                        >
                            <option value="">{t('supportAdmin.allPriorities')}</option>
                            <option value="baja">{t('supportPage.priority.baja')}</option>
                            <option value="normal">{t('supportPage.priority.normal')}</option>
                            <option value="alta">{t('supportPage.priority.alta')}</option>
                            <option value="urgente">{t('supportPage.priority.urgente')}</option>
                        </select>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-border bg-surface">
                    <p className="ciete-table-hint">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="ciete-table-scroll">
                    <table className="min-w-[1080px] w-full">
                        <thead className="border-b border-border">
                            <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                <th className="px-4 py-3">{t('supportAdmin.cols.ticket')}</th>
                                <th className="px-4 py-3">{t('supportAdmin.cols.requester')}</th>
                                <th className="px-4 py-3">{t('supportAdmin.cols.subject')}</th>
                                <th className="px-4 py-3">{t('supportAdmin.cols.status')}</th>
                                <th className="px-4 py-3">{t('supportAdmin.cols.priority')}</th>
                                <th className="px-4 py-3">{t('supportAdmin.cols.updated')}</th>
                                <th className="px-4 py-3 text-right">{t('supportAdmin.cols.actions')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {tickets.data?.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-sm text-text-hint">
                                        {t('supportAdmin.empty')}
                                    </td>
                                </tr>
                            ) : (
                                tickets.data.map((ticket) => (
                                    <tr key={ticket.id_solicitud_soporte} className="transition hover:bg-surface-2">
                                        <td className="px-4 py-3 text-sm font-medium text-text-main">
                                            #{ticket.id_solicitud_soporte}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-text-muted">
                                            {ticket.solicitante ? `${ticket.solicitante.nombre} ${ticket.solicitante.apellidos ?? ''}`.trim() : '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <p className="text-sm font-medium text-text-main">{ticket.asunto}</p>
                                            <p className="text-xs text-text-hint">{ticket.ultimo_comentario?.mensaje ?? t(`supportPage.topics.${ticket.tema}`)}</p>
                                        </td>
                                        <td className="px-4 py-3"><StatusBadge status={ticket.estado} t={t} /></td>
                                        <td className="px-4 py-3"><PriorityBadge priority={ticket.prioridad} t={t} /></td>
                                        <td className="px-4 py-3 text-sm text-text-muted">
                                            {ticket.ultimo_mensaje_at ? new Date(ticket.ultimo_mensaje_at).toLocaleString() : '—'}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={route('admin.support.show', ticket.id_solicitud_soporte)}
                                                className="text-xs font-bold uppercase tracking-widest text-primary hover:underline"
                                            >
                                                {t('supportAdmin.open')}
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
