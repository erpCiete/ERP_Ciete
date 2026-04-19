import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, usePage, router } from '@inertiajs/react';

export default function AdminDashboard({ stats = {}, users = [], activity = [] }) {
    const user = usePage().props.auth.user;
    const maintenanceActive = usePage().props.maintenance?.active ?? false;
    const { t } = useI18n();

    const toggleMaintenance = () => {
        router.post(route('admin.maintenance.toggle'), {}, {
            preserveScroll: true,
        });
    };

    const adminModules = [
        { key: 'users', color: 'border-l-state-progress-dot', href: route('admin.users.index') },
        { key: 'orders', color: 'border-l-state-pending-dot', href: '#' },
        { key: 'billing', color: 'border-l-state-done-dot', href: '#' },
        { key: 'legalizations', color: 'border-l-state-blocked-dot', href: '#' },
        { key: 'clients', color: 'border-l-accent', href: '#' },
        { key: 'stations', color: 'border-l-border-heavy', href: '#' },
        { key: 'works', color: 'border-l-primary', href: route('trabajos.index') },
        { key: 'audit', color: 'border-l-text-hint', href: route('admin.audit') },
    ];

    const translateActivityAction = (action) => {
        if (!action) return '';

        const normalized = String(action)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        const aliases = {
            creo: 'created',
            created: 'created',
            actualizo: 'updated',
            updated: 'updated',
            elimino: 'deleted',
            deleted: 'deleted',
            asigno: 'assigned',
            assigned: 'assigned',
            valido: 'validated',
            validated: 'validated',
            cerro: 'closed',
            closed: 'closed',
            abrio: 'opened',
            opened: 'opened',
        };

        const key = aliases[normalized];
        if (!key) return action;

        const candidate = t(`adminDashboard.activity.actions.${key}`);
        return candidate.startsWith('adminDashboard.activity.actions.') ? action : candidate;
    };

    const maintenanceStateBadgeClass =
        maintenanceActive
            ? 'bg-state-blocked-bg text-state-blocked-text'
            : 'bg-state-done-bg text-state-done-text';

    const maintenanceStateLabel =
        maintenanceActive
            ? t('adminDashboard.maintenance.statusMaintenance')
            : t('adminDashboard.maintenance.statusNormal');

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('adminDashboard.header')}
                </h2>
            }
        >
            <Head title={t('adminDashboard.headTitle')} />

            <div className="mx-auto max-w-7xl space-y-5 px-6 py-8">
                <div className="flex items-start justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminDashboard.panelLabel')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('adminDashboard.overview')}</h2>
                    </div>
                    <p className="text-[11px] text-text-hint">{t('adminDashboard.lastAccessToday')}</p>
                </div>

                <div className="grid grid-cols-4 gap-2 md:grid-cols-7">
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-primary bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.activeWorks')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.active_works ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.active')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-progress-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.users')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{users?.length ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.registered')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-pending-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.orders')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.pending_orders ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.pending')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.billing')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.billing ?? '-'}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.thisMonth')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.legalizations')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.legalizaciones ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.pending')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-accent bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.clients')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.clientes ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.registered')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-border-heavy bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.stations')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.estaciones ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.registeredFem')}</p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div className="rounded-2xl border border-border p-4">
                        <p className="text-xs uppercase tracking-[0.16em] text-text-muted">{t('adminDashboard.cards.user')}</p>
                        <p className="mt-2 text-base font-semibold text-text-main">{user?.nombre_usuario ?? '-'}</p>
                        <p className="text-sm text-text-muted">{user?.email ?? '-'}</p>
                    </div>
                    <div className="rounded-2xl border border-border p-4">
                        <p className="text-xs uppercase tracking-[0.16em] text-text-muted">{t('adminDashboard.cards.context')}</p>
                        <p className="mt-2 text-base font-semibold text-text-main">{user?.contexto?.nombre ?? t('adminDashboard.cards.noContext')}</p>
                        <p className="text-sm text-text-muted">{user?.contexto?.codigo ?? '-'}</p>
                    </div>
                </div>

                <section className="rounded-[12px] border border-border bg-surface p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.maintenance.title')}</h3>
                            <p className="mt-1 text-xs text-text-hint">{t('adminDashboard.maintenance.description')}</p>
                        </div>
                        <span className={`inline-flex h-fit items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest ${maintenanceStateBadgeClass}`}>
                            {maintenanceStateLabel}
                        </span>
                    </div>

                    {/* Por ahora es solo visual para no tocar el estado real del entorno en este sprint. */}
                    <div className="mt-3">
                        <div className="rounded-[10px] border border-border bg-surface-2 p-3">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('adminDashboard.maintenance.actionsTitle')}
                            </p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                {!maintenanceActive ? (
                                    <button
                                        type="button"
                                        onClick={toggleMaintenance}
                                        className="inline-flex items-center rounded-md bg-(--ciete-red) px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white transition hover:opacity-90"
                                    >
                                        {t('adminDashboard.maintenance.enable')}
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={toggleMaintenance}
                                        className="inline-flex items-center rounded-md border border-border bg-surface px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-surface-2"
                                    >
                                        {t('adminDashboard.maintenance.disable')}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <div className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <div className="border-b border-border px-4 py-3">
                        <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.modules.title')}</h3>
                    </div>
                    <div className="grid grid-cols-2 gap-2 p-3 md:grid-cols-4">
                        {adminModules.map((module) => (
                            <Link
                                key={module.key}
                                href={module.href}
                                className={`flex items-center justify-between rounded-[8px] border-l-[3px] bg-surface-2 px-3 py-2 transition-colors hover:bg-border ${module.color}`}
                            >
                                <span className="text-[10px] font-bold uppercase tracking-widest text-text-main">
                                    {t(`adminDashboard.modules.${module.key}`)}
                                </span>
                                <span className="text-xs text-text-hint" aria-hidden>
                                    →
                                </span>
                            </Link>
                        ))}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-5">
                    <div className="overflow-hidden rounded-[12px] border border-border bg-surface lg:col-span-3">
                        <div className="border-b border-border px-4 py-3">
                            <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.activity.title')}</h3>
                        </div>
                        {activity.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('adminDashboard.activity.empty')}</div>
                        ) : (
                            <div className="divide-y divide-border">
                                {activity.map((item, index) => (
                                    <div key={index} className="flex gap-3 px-4 py-3">
                                        <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent/10 text-[9px] font-bold text-accent">
                                            {item.initials}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <p className="text-xs text-text-main">
                                                <span className="font-medium">{item.name}</span>{' '}
                                                {translateActivityAction(item.action)}{' '}
                                                {item.target && <span className="font-medium">{item.target}</span>}
                                            </p>
                                            <p className="mt-0.5 text-[9px] text-text-hint">{item.time}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="overflow-hidden rounded-[12px] border border-border bg-surface lg:col-span-2">
                        <div className="border-b border-border px-4 py-3">
                            <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.activeUsers.title')}</h3>
                        </div>
                        {users.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('adminDashboard.activeUsers.empty')}</div>
                        ) : (
                            <div className="divide-y divide-border">
                                {users.map((member) => (
                                    <div key={member.id_usuario} className="flex items-center justify-between px-4 py-2.5">
                                        <div className="flex items-center gap-2">
                                            <div className="relative">
                                                <div className="flex h-7 w-7 items-center justify-center rounded-full bg-surface-2 text-[9px] font-bold text-text-muted">
                                                    {member.nombre?.charAt(0)}
                                                    {member.apellidos?.charAt(0) ?? ''}
                                                </div>
                                                <div className="absolute bottom-0 right-0 h-2 w-2 rounded-full border-2 border-surface bg-state-done-dot" />
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-text-main">{member.nombre} {member.apellidos}</p>
                                                <p className="text-[9px] text-text-hint">{member.email}</p>
                                            </div>
                                        </div>
                                        <span className="inline-flex items-center rounded-full bg-state-done-bg px-2 py-0.5 text-[9px] font-bold text-state-done-text">
                                            {t('status.active')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
