import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import { Activity, CheckCircle, AlertTriangle, XCircle, RefreshCw, Database, HardDrive, Mail, Server, Wrench } from 'lucide-react';

function StatusIcon({ status }) {
    if (status === 'ok') return <CheckCircle size={16} className="text-state-done-dot" />;
    if (status === 'warning') return <AlertTriangle size={16} className="text-state-pending-dot" />;
    return <XCircle size={16} className="text-state-blocked-dot" />;
}

function StatusBadge({ status, t }) {
    const map = {
        ok: 'bg-state-done-bg text-state-done-text',
        warning: 'bg-state-pending-bg text-state-pending-text',
        error: 'bg-state-blocked-bg text-state-blocked-text',
    };
    const labelMap = {
        ok: t('statusPage.operational'),
        warning: t('statusPage.degraded'),
        error: t('statusPage.down'),
    };

    return (
        <span className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${map[status] ?? map.error}`}>
            <StatusIcon status={status} />
            {labelMap[status] ?? status}
        </span>
    );
}

function StatusCard({ icon: Icon, title, status, details, t }) {
    return (
        <div className="rounded-xl border border-border bg-surface p-4">
            <div className="flex items-start justify-between">
                <div className="flex items-center gap-2.5">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-2">
                        <Icon size={16} className="text-text-muted" />
                    </div>
                    <span className="text-sm font-medium text-text-main">{title}</span>
                </div>
                <StatusBadge status={status} t={t} />
            </div>
            {details && (
                <div className="mt-3 space-y-1">
                    {Object.entries(details).map(([key, value]) =>
                        key !== 'status' ? (
                            <div key={key} className="flex items-center justify-between text-xs">
                                <span className="text-text-hint">{key}</span>
                                <span className="font-medium text-text-muted">{String(value)}</span>
                            </div>
                        ) : null,
                    )}
                </div>
            )}
        </div>
    );
}

export default function Status({ checks = {}, timestamp, isAdmin = false }) {
    const { t } = useI18n();

    const allOk = Object.values(checks).every((c) => c.status === 'ok');
    const hasWarning = Object.values(checks).some((c) => c.status === 'warning');

    const overallStatus = allOk ? 'ok' : hasWarning ? 'warning' : 'error';

    const overallLabel = {
        ok: t('statusPage.allOperational'),
        warning: t('statusPage.partialIssues'),
        error: t('statusPage.systemIssues'),
    };

    const overallColor = {
        ok: 'border-state-done-dot bg-state-done-bg text-state-done-text',
        warning: 'border-state-pending-dot bg-state-pending-bg text-state-pending-text',
        error: 'border-state-blocked-dot bg-state-blocked-bg text-state-blocked-text',
    };

    return (
        <AuthenticatedLayout header={t('statusPage.header')}>
            <Head title={t('statusPage.headTitle')} />

            <div className="mx-auto max-w-3xl space-y-5 pb-10">
                <div>
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                        {t('statusPage.eyebrow')}
                    </p>
                    <h2 className="text-xl font-semibold text-text-main">{t('statusPage.title')}</h2>
                </div>

                <div className={`flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 ${overallColor[overallStatus]}`}>
                    <Activity size={20} />
                    <div>
                        <p className="text-sm font-semibold">{overallLabel[overallStatus]}</p>
                        {timestamp && (
                            <p className="mt-0.5 text-[10px] opacity-70">
                                {t('statusPage.lastCheck')}: {new Date(timestamp).toLocaleString()}
                            </p>
                        )}
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    {checks.database && (
                        <StatusCard
                            icon={Database}
                            title={t('statusPage.services.database')}
                            status={checks.database.status}
                            details={{ driver: checks.database.driver, database: checks.database.name }}
                            t={t}
                        />
                    )}
                    {checks.storage && (
                        <StatusCard
                            icon={HardDrive}
                            title={t('statusPage.services.storage')}
                            status={checks.storage.status}
                            details={{
                                [t('statusPage.freeMb')]: `${checks.storage.free_mb} MB`,
                                [t('statusPage.totalMb')]: `${checks.storage.total_mb} MB`,
                            }}
                            t={t}
                        />
                    )}
                    {checks.mail && (
                        <StatusCard
                            icon={Mail}
                            title={t('statusPage.services.mail')}
                            status={checks.mail.status}
                            details={checks.mail.driver ? { driver: checks.mail.driver } : null}
                            t={t}
                        />
                    )}
                    {checks.queue && (
                        <StatusCard
                            icon={RefreshCw}
                            title={t('statusPage.services.queue')}
                            status={checks.queue.status}
                            details={{ driver: checks.queue.driver }}
                            t={t}
                        />
                    )}
                    {checks.app && (
                        <StatusCard
                            icon={Server}
                            title={t('statusPage.services.application')}
                            status={checks.app.status}
                            details={isAdmin ? {
                                version: checks.app.version,
                                PHP: checks.app.php,
                                Laravel: checks.app.laravel,
                                [t('statusPage.environment')]: checks.app.environment,
                                timezone: checks.app.timezone,
                            } : {
                                version: checks.app.version,
                            }}
                            t={t}
                        />
                    )}
                    {checks.maintenance && (
                        <StatusCard
                            icon={Wrench}
                            title={t('statusPage.services.maintenance')}
                            status={checks.maintenance.status}
                            details={{
                                [t('statusPage.maintenanceActive')]: checks.maintenance.active
                                    ? t('statusPage.yes')
                                    : t('statusPage.no'),
                            }}
                            t={t}
                        />
                    )}
                </div>

                <p className="text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('statusPage.footer')}
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
