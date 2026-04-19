import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export default function AuditIndex({ logs, filtros = {} }) {
    const { t } = useI18n();
    const [search, setSearch] = useState(filtros.search ?? '');

    const rows = logs?.data ?? [];
    const pagination = logs?.meta ?? logs;

    const aplicarFiltros = useCallback(
        (overrides = {}) => {
            const params = {
                search: overrides.search !== undefined ? overrides.search : search,
                page: overrides.page,
            };
            Object.keys(params).forEach((k) => {
                if (!params[k]) delete params[k];
            });

            router.get(route('admin.audit'), params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [search],
    );

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filtros.search ?? '')) {
                aplicarFiltros({ search });
            }
        }, 400);
        return () => clearTimeout(timer);
    }, [search]);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('adminAudit.header')}
                </h2>
            }
        >
            <Head title={t('adminAudit.headTitle')} />

            <div className="mx-auto max-w-7xl space-y-5 px-6 py-8">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminAudit.panelLabel')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('adminAudit.title')}</h2>
                    </div>
                </div>

                <div className="rounded-[12px] border border-border bg-surface p-4">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder={t('adminAudit.searchPlaceholder')}
                        className="w-full rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                    />
                </div>

                <div className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <table className="w-full">
                        <thead className="border-b border-border">
                            <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                <th className="px-4 py-2">{t('adminAudit.cols.date')}</th>
                                <th className="px-4 py-2">{t('adminAudit.cols.user')}</th>
                                <th className="px-4 py-2">{t('adminAudit.cols.action')}</th>
                                <th className="px-4 py-2">{t('adminAudit.cols.table')}</th>
                                <th className="px-4 py-2">{t('adminAudit.cols.record')}</th>
                                <th className="px-4 py-2">{t('adminAudit.cols.ip')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {rows.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-text-hint">
                                        {t('adminAudit.noLogs')}
                                    </td>
                                </tr>
                            ) : (
                                rows.map((log) => (
                                    <tr key={log.id_audit} className="transition hover:bg-surface-2">
                                        <td className="px-4 py-3 text-xs text-text-muted">
                                            {log.created_at ? new Date(log.created_at).toLocaleString('es-ES') : '—'}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-text-main">
                                            {log.usuario ? `${log.usuario.nombre} ${log.usuario.apellidos ?? ''}`.trim() : '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-[9px] font-bold text-accent">
                                                {log.accion}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-text-muted">{log.tabla}</td>
                                        <td className="px-4 py-3 text-sm text-text-muted">#{log.registro_id ?? '—'}</td>
                                        <td className="px-4 py-3 text-xs text-text-hint">{log.ip ?? '—'}</td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
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
                    <Link href={route('admin.dashboard')} className="text-xs text-text-hint hover:text-text-muted">
                        ← {t('adminAudit.backToAdmin')}
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
