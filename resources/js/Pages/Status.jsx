import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import { Activity, CheckCircle, AlertTriangle, XCircle, RefreshCw, Database, HardDrive, Mail, Server, ShieldAlert, Wrench } from 'lucide-react';

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

function formatDateTime(value) {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return date.toLocaleString();
}

function SummaryCard({ title, value, caption, status = 'ok' }) {
    const tones = {
        ok: 'border-state-done-dot bg-state-done-bg/70 text-state-done-text',
        warning: 'border-state-pending-dot bg-state-pending-bg/70 text-state-pending-text',
        error: 'border-state-blocked-dot bg-state-blocked-bg/70 text-state-blocked-text',
        neutral: 'border-border bg-surface text-text-main',
    };

    return (
        <div className={`rounded-xl border-l-4 border border-border px-4 py-3 ${tones[status] ?? tones.neutral}`}>
            <p className="text-[10px] font-bold uppercase tracking-widest opacity-80">{title}</p>
            <p className="mt-2 text-2xl font-semibold leading-none">{value}</p>
            {caption && <p className="mt-2 text-xs opacity-80">{caption}</p>}
        </div>
    );
}

function StatusCard({ icon: Icon, title, status, description, details, t }) {
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
            {description && <p className="mt-3 text-xs text-text-hint">{description}</p>}
            {details?.length > 0 && (
                <div className="mt-3 space-y-1">
                    {details.map((detail) => (
                        <div key={detail.label} className="flex items-center justify-between gap-3 text-xs">
                            <span className="text-text-hint">{detail.label}</span>
                            <span className="text-right font-medium text-text-muted">{String(detail.value ?? '—')}</span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default function Status({ checks = {}, timestamp, canViewTechnicalDetails = false, summary = {} }) {
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

    const serviceCards = [
        checks.database && {
            key: 'database',
            icon: Database,
            title: t('statusPage.services.database'),
            status: checks.database.status,
            description: canViewTechnicalDetails
                ? t('statusPage.serviceDescriptions.databaseTechnical')
                : t('statusPage.serviceDescriptions.databaseLimited'),
            details: canViewTechnicalDetails
                ? [
                    { label: t('statusPage.details.driver'), value: checks.database.driver },
                    { label: t('statusPage.details.databaseName'), value: checks.database.name },
                    { label: t('statusPage.details.host'), value: checks.database.host },
                ]
                : [],
        },
        checks.storage && {
            key: 'storage',
            icon: HardDrive,
            title: t('statusPage.services.storage'),
            status: checks.storage.status,
            description: canViewTechnicalDetails
                ? t('statusPage.serviceDescriptions.storageTechnical')
                : t('statusPage.serviceDescriptions.storageLimited'),
            details: canViewTechnicalDetails
                ? [
                    { label: t('statusPage.freeMb'), value: checks.storage.free_mb !== null && checks.storage.free_mb !== undefined ? `${checks.storage.free_mb} MB` : '—' },
                    { label: t('statusPage.totalMb'), value: checks.storage.total_mb !== null && checks.storage.total_mb !== undefined ? `${checks.storage.total_mb} MB` : '—' },
                    { label: t('statusPage.details.storageUsage'), value: checks.storage.used_percent !== null && checks.storage.used_percent !== undefined ? `${checks.storage.used_percent}%` : '—' },
                    { label: t('statusPage.details.writable'), value: checks.storage.writable ? t('statusPage.yes') : t('statusPage.no') },
                ]
                : [
                    { label: t('statusPage.details.writable'), value: checks.storage.writable ? t('statusPage.yes') : t('statusPage.no') },
                ],
        },
        checks.mail && {
            key: 'mail',
            icon: Mail,
            title: t('statusPage.services.mail'),
            status: checks.mail.status,
            description: canViewTechnicalDetails
                ? t('statusPage.serviceDescriptions.mailTechnical')
                : t('statusPage.serviceDescriptions.mailLimited'),
            details: canViewTechnicalDetails
                ? [
                    { label: t('statusPage.details.driver'), value: checks.mail.driver },
                    { label: t('statusPage.details.host'), value: checks.mail.host },
                    { label: t('statusPage.details.fromAddress'), value: checks.mail.from },
                ].filter((detail) => detail.value)
                : [],
        },
        checks.queue && {
            key: 'queue',
            icon: RefreshCw,
            title: t('statusPage.services.queue'),
            status: checks.queue.status,
            description: t('statusPage.serviceDescriptions.queueTechnical'),
            details: [
                { label: t('statusPage.details.driver'), value: checks.queue.driver },
            ],
        },
        checks.app && {
            key: 'app',
            icon: Server,
            title: t('statusPage.services.application'),
            status: checks.app.status,
            description: canViewTechnicalDetails
                ? t('statusPage.serviceDescriptions.appTechnical')
                : t('statusPage.serviceDescriptions.appLimited'),
            details: canViewTechnicalDetails
                ? [
                    { label: t('statusPage.details.version'), value: checks.app.version },
                    { label: t('statusPage.details.phpVersion'), value: checks.app.php },
                    { label: t('statusPage.details.laravelVersion'), value: checks.app.laravel },
                    { label: t('statusPage.environment'), value: checks.app.environment },
                    { label: t('statusPage.details.language'), value: checks.app.locale },
                    { label: t('statusPage.details.timezone'), value: checks.app.timezone },
                    { label: t('statusPage.details.debug'), value: checks.app.debug ? t('statusPage.yes') : t('statusPage.no') },
                ]
                : [
                    { label: t('statusPage.details.version'), value: checks.app.version },
                    { label: t('statusPage.details.language'), value: checks.app.locale },
                ],
        },
        checks.maintenance && {
            key: 'maintenance',
            icon: Wrench,
            title: t('statusPage.services.maintenance'),
            status: checks.maintenance.status,
            description: canViewTechnicalDetails
                ? t('statusPage.serviceDescriptions.maintenanceTechnical')
                : t('statusPage.serviceDescriptions.maintenanceLimited'),
            details: canViewTechnicalDetails
                ? [
                    { label: t('statusPage.maintenanceActive'), value: checks.maintenance.active ? t('statusPage.yes') : t('statusPage.no') },
                    { label: t('statusPage.details.maintenanceFile'), value: checks.maintenance.file_present ? t('statusPage.yes') : t('statusPage.no') },
                ]
                : [
                    { label: t('statusPage.maintenanceActive'), value: checks.maintenance.active ? t('statusPage.yes') : t('statusPage.no') },
                ],
        },
    ].filter(Boolean);

    const summaryCards = canViewTechnicalDetails
        ? [
            {
                key: 'healthy',
                title: t('statusPage.summary.healthyServices'),
                value: summary.ok_count ?? 0,
                caption: t('statusPage.summary.healthyCaption'),
                status: 'ok',
            },
            {
                key: 'warnings',
                title: t('statusPage.summary.warnings'),
                value: summary.warning_count ?? 0,
                caption: t('statusPage.summary.warningsCaption'),
                status: (summary.warning_count ?? 0) > 0 ? 'warning' : 'neutral',
            },
            {
                key: 'support',
                title: t('statusPage.summary.openSupport'),
                value: summary.open_support_tickets ?? 0,
                caption: t('statusPage.summary.openSupportCaption'),
                status: (summary.open_support_tickets ?? 0) > 0 ? 'warning' : 'neutral',
            },
            {
                key: 'imports',
                title: t('statusPage.summary.totalImports'),
                value: summary.imports_total ?? 0,
                caption: summary.latest_import_at
                    ? `${t('statusPage.summary.lastImport')}: ${formatDateTime(summary.latest_import_at)}`
                    : t('statusPage.summary.noImportsYet'),
                status: 'neutral',
            },
        ]
        : [
            {
                key: 'context',
                title: t('statusPage.summary.currentContext'),
                value: summary.active_context_name ?? t('statusPage.noContext'),
                caption: t('statusPage.summary.currentContextCaption'),
                status: 'neutral',
            },
            {
                key: 'contexts',
                title: t('statusPage.summary.availableContexts'),
                value: summary.accessible_contexts ?? 0,
                caption: t('statusPage.summary.availableContextsCaption'),
                status: 'neutral',
            },
            {
                key: 'maintenance',
                title: t('statusPage.summary.maintenance'),
                value: summary.maintenance_active ? t('statusPage.yes') : t('statusPage.no'),
                caption: t('statusPage.summary.maintenanceCaption'),
                status: summary.maintenance_active ? 'warning' : 'ok',
            },
            {
                key: 'support',
                title: t('statusPage.summary.openSupport'),
                value: summary.open_support_tickets ?? 0,
                caption: t('statusPage.summary.openSupportCaption'),
                status: (summary.open_support_tickets ?? 0) > 0 ? 'warning' : 'neutral',
            },
        ];

    const runtimeDetails = canViewTechnicalDetails
        ? [
            { label: t('statusPage.details.version'), value: checks.app?.version },
            { label: t('statusPage.details.phpVersion'), value: checks.app?.php },
            { label: t('statusPage.details.laravelVersion'), value: checks.app?.laravel },
            { label: t('statusPage.environment'), value: checks.app?.environment },
            { label: t('statusPage.details.language'), value: checks.app?.locale },
            { label: t('statusPage.details.timezone'), value: checks.app?.timezone },
            { label: t('statusPage.details.debug'), value: checks.app?.debug ? t('statusPage.yes') : t('statusPage.no') },
        ]
        : [];

    const operationalDetails = canViewTechnicalDetails
        ? [
            { label: t('statusPage.details.activeContext'), value: summary.active_context_name ?? t('statusPage.noContext') },
            { label: t('statusPage.summary.availableContexts'), value: summary.accessible_contexts ?? 0 },
            { label: t('statusPage.summary.openSupport'), value: summary.open_support_tickets ?? 0 },
            { label: t('statusPage.summary.totalImports'), value: summary.imports_total ?? 0 },
            { label: t('statusPage.summary.lastImport'), value: summary.latest_import_at ? formatDateTime(summary.latest_import_at) : t('statusPage.summary.noImportsYet') },
        ]
        : [];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('statusPage.header')}
                </h2>
            }
        >
            <Head title={t('statusPage.headTitle')} />

            <div className="ciete-page ciete-page-wide space-y-5 pb-10">
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

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map((card) => (
                        <SummaryCard
                            key={card.key}
                            title={card.title}
                            value={card.value}
                            caption={card.caption}
                            status={card.status}
                        />
                    ))}
                </div>

                {!canViewTechnicalDetails && (
                    <div className="rounded-xl border border-border bg-surface px-4 py-4">
                        <div className="flex items-start gap-3">
                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-text-muted">
                                <ShieldAlert size={18} />
                            </div>
                            <div>
                                <h3 className="text-sm font-semibold text-text-main">{t('statusPage.limited.title')}</h3>
                                <p className="mt-1 text-sm text-text-muted">{t('statusPage.limited.description')}</p>
                            </div>
                        </div>
                    </div>
                )}

                <section className="space-y-3">
                    <div>
                        <h3 className="text-sm font-semibold text-text-main">{t('statusPage.sections.services')}</h3>
                        <p className="mt-1 text-sm text-text-hint">{t('statusPage.sections.servicesDescription')}</p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {serviceCards.map((card) => (
                            <StatusCard
                                key={card.key}
                                icon={card.icon}
                                title={card.title}
                                status={card.status}
                                description={card.description}
                                details={card.details}
                                t={t}
                            />
                        ))}
                    </div>
                </section>

                {canViewTechnicalDetails && (
                    <section className="space-y-3">
                        <div>
                            <h3 className="text-sm font-semibold text-text-main">{t('statusPage.sections.technical')}</h3>
                            <p className="mt-1 text-sm text-text-hint">{t('statusPage.sections.technicalDescription')}</p>
                        </div>

                        <div className="grid gap-3 lg:grid-cols-2">
                            <StatusCard
                                icon={Server}
                                title={t('statusPage.technical.runtime')}
                                status={checks.app?.status ?? 'ok'}
                                description={t('statusPage.technical.runtimeDescription')}
                                details={runtimeDetails}
                                t={t}
                            />
                            <StatusCard
                                icon={Activity}
                                title={t('statusPage.technical.operations')}
                                status={overallStatus}
                                description={t('statusPage.technical.operationsDescription')}
                                details={operationalDetails}
                                t={t}
                            />
                        </div>
                    </section>
                )}

                <p className="text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('statusPage.footer')}
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
