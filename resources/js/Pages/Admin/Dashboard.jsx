import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, usePage, router } from '@inertiajs/react';
import { CheckCircle2, CircleOff, Pencil, Plus, Save, Star, StarOff, X } from 'lucide-react';
import { useEffect, useState } from 'react';

const EMPTY_PAGINATION_META = {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    from: 0,
    to: 0,
    total: 0,
    has_previous_page: false,
    has_next_page: false,
};

function DashboardPagination({ meta, onPrevious, onNext, t }) {
    if (!meta?.total) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2 border-t border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-xs text-text-hint">
                {t('adminDashboard.pagination.showing', {
                    from: meta.from,
                    to: meta.to,
                    total: meta.total,
                })}
            </p>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={onPrevious}
                    disabled={!meta.has_previous_page}
                    className="rounded-md border border-border px-3 py-1.5 text-[11px] font-semibold text-text-main transition hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {t('adminDashboard.pagination.previous')}
                </button>
                <span className="text-[11px] font-semibold text-text-muted">
                    {t('adminDashboard.pagination.page', {
                        page: meta.current_page,
                        lastPage: meta.last_page,
                    })}
                </span>
                <button
                    type="button"
                    onClick={onNext}
                    disabled={!meta.has_next_page}
                    className="rounded-md border border-border px-3 py-1.5 text-[11px] font-semibold text-text-main transition hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {t('adminDashboard.pagination.next')}
                </button>
            </div>
        </div>
    );
}

function makeDraftNotice(category = 'notices') {
    return {
        id: null,
        category,
        title_es: '',
        body_es: '',
        title_en: '',
        body_en: '',
        is_active: true,
        is_featured: false,
        starts_at: '',
        ends_at: '',
    };
}

const NOTICE_CATEGORIES = ['notices', 'updates', 'companyNews'];

function toDateTimeLocal(value) {
    if (!value) return '';

    return String(value).slice(0, 16);
}

function fromDateTimeLocal(value) {
    return value ? value : null;
}

function normalizeNotice(item = {}, fallbackCategory = 'notices') {
    return {
        id: item.id ?? null,
        category: item.category ?? fallbackCategory,
        category_value: item.category_value ?? null,
        title_es: item.title_es ?? '',
        body_es: item.body_es ?? item.es ?? '',
        title_en: item.title_en ?? '',
        body_en: item.body_en ?? item.en ?? '',
        is_active: Boolean(item.is_active ?? true),
        is_featured: Boolean(item.is_featured ?? item.featured ?? false),
        starts_at: toDateTimeLocal(item.starts_at),
        ends_at: toDateTimeLocal(item.ends_at),
    };
}

function normalizeNoticeCatalog(homeNotices = {}) {
    const base = {};

    NOTICE_CATEGORIES.forEach((category) => {
        base[category] = (homeNotices[category] || []).map((item) => normalizeNotice(item, category));
    });

    return base;
}

export default function AdminDashboard({
    stats = {},
    users = { data: [], meta: EMPTY_PAGINATION_META },
    activity = { data: [], meta: EMPTY_PAGINATION_META },
    userContexts = [],
    homeNotices = {},
    featuredNotice = null,
}) {
    const user = usePage().props.auth.user;
    const maintenanceActive = usePage().props.maintenance?.active ?? false;
    const { t } = useI18n();
    const canManageUsers = Boolean(user?.can_manage_users);
    const canManageSupport = Boolean(user?.can_manage_support);
    const canManageMaintenance = Boolean(user?.can_manage_maintenance);
    const canManageNotices = Boolean(user?.can_manage_notices);
    const canViewAudit = Boolean(user?.can_view_audit);
    const canManageImports = Boolean(user?.can_manage_imports);
    const canViewWorks = Boolean(user?.permission_slugs?.includes('trabajos.ver'));
    const canViewOrders = Boolean(user?.permission_slugs?.includes('pedidos.ver'));
    const canViewInvoices = Boolean(user?.permission_slugs?.includes('facturas.ver'));
    const canViewClients = Boolean(user?.permission_slugs?.includes('clientes.ver'));
    const canViewStations = Boolean(user?.permission_slugs?.includes('estaciones.ver'));
    const isOperationalReadOnly = Boolean(user?.is_technical_admin && !user?.can_mutate_operational_data);
    const usersItems = users?.data ?? [];
    const usersMeta = users?.meta ?? EMPTY_PAGINATION_META;
    const activityItems = activity?.data ?? [];
    const activityMeta = activity?.meta ?? EMPTY_PAGINATION_META;

    const toggleMaintenance = () => {
        if (!canManageMaintenance) {
            return;
        }

        router.post(route('admin.maintenance.toggle'), {}, {
            preserveScroll: true,
        });
    };

    const changeDashboardPage = (target, page) => {
        router.get(route('admin.dashboard'), {
            users_page: target === 'users' ? page : usersMeta.current_page || 1,
            activity_page: target === 'activity' ? page : activityMeta.current_page || 1,
        }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const [editingNoticeId, setEditingNoticeId] = useState(null);
    const [draftNotice, setDraftNotice] = useState(null);
    const [savingNotices, setSavingNotices] = useState(false);
    const [noticesData, setNoticesData] = useState(() => normalizeNoticeCatalog(homeNotices));

    useEffect(() => {
        setNoticesData(normalizeNoticeCatalog(homeNotices));
    }, [homeNotices]);

    const activeFeaturedNotice =
        NOTICE_CATEGORIES.flatMap((category) =>
            (noticesData[category] || []).map((item) => ({ ...item, category })),
        ).find((item) => item.is_featured && item.is_active)
        ?? (featuredNotice ? normalizeNotice(featuredNotice, featuredNotice.category ?? 'notices') : null);

    const updateDraftNotice = (field, value) => {
        setDraftNotice((current) => (current ? { ...current, [field]: value } : current));
    };

    const startCreateNotice = (category) => {
        setEditingNoticeId('new');
        setDraftNotice(makeDraftNotice(category));
    };

    const startEditNotice = (notice) => {
        setEditingNoticeId(notice.id);
        setDraftNotice({ ...notice });
    };

    const cancelNoticeEdit = () => {
        setEditingNoticeId(null);
        setDraftNotice(null);
    };

    const submitNotice = () => {
        if (!draftNotice) {
            return;
        }

        const payload = {
            category: draftNotice.category,
            title_es: draftNotice.title_es,
            body_es: draftNotice.body_es,
            title_en: draftNotice.title_en,
            body_en: draftNotice.body_en,
            is_active: draftNotice.is_active,
            is_featured: draftNotice.is_featured,
            starts_at: fromDateTimeLocal(draftNotice.starts_at),
            ends_at: fromDateTimeLocal(draftNotice.ends_at),
        };

        const options = {
            preserveScroll: true,
            onStart: () => setSavingNotices(true),
            onFinish: () => setSavingNotices(false),
            onSuccess: cancelNoticeEdit,
        };

        if (draftNotice.id) {
            router.put(route('admin.notices.update', draftNotice.id), payload, options);
            return;
        }

        router.post(route('admin.notices.store'), payload, options);
    };

    const toggleNoticeActive = (notice) => {
        router.patch(route('admin.notices.toggle', notice.id), {
            is_active: !notice.is_active,
        }, {
            preserveScroll: true,
        });
    };

    const toggleNoticeFeatured = (notice) => {
        router.patch(route('admin.notices.feature', notice.id), {
            is_featured: !notice.is_featured,
        }, {
            preserveScroll: true,
        });
    };

    const moduleGroups = [
        {
            key: 'system',
            title: t('adminDashboard.groups.system.title'),
            description: t('adminDashboard.groups.system.description'),
            items: [
                {
                    key: 'status',
                    label: t('adminDashboard.modules.status'),
                    description: t('adminDashboard.moduleDescriptions.status'),
                    href: route('status'),
                },
                canViewAudit
                    ? {
                        key: 'technical-audit',
                        label: t('nav.technicalAudit'),
                        description: t('adminDashboard.moduleDescriptions.technicalAudit'),
                        href: route('admin.audit'),
                        badge: `${stats?.audit_today ?? 0} ${t('adminDashboard.metrics.today')}`,
                    }
                    : null,
                canManageMaintenance
                    ? {
                        key: 'maintenance',
                        label: t('adminDashboard.modules.maintenance'),
                        description: t('adminDashboard.moduleDescriptions.maintenance'),
                        href: `${route('admin.dashboard')}#maintenance`,
                        badge: maintenanceActive ? t('statusPage.degraded') : t('statusPage.operational'),
                    }
                    : null,
            ].filter(Boolean),
        },
        {
            key: 'users-access',
            title: t('adminDashboard.groups.usersAccess.title'),
            description: t('adminDashboard.groups.usersAccess.description'),
            items: [
                canManageUsers
                    ? {
                        key: 'users',
                        label: t('adminDashboard.modules.users'),
                        description: t('adminDashboard.moduleDescriptions.users'),
                        href: route('admin.users.index'),
                        badge: `${stats?.active_users ?? 0} ${t('adminDashboard.metrics.registered')}`,
                    }
                    : null,
            ].filter(Boolean),
        },
        {
            key: 'support',
            title: t('adminDashboard.groups.support.title'),
            description: t('adminDashboard.groups.support.description'),
            items: [
                canManageSupport
                    ? {
                        key: 'support',
                        label: t('adminDashboard.modules.support'),
                        description: t('adminDashboard.moduleDescriptions.support'),
                        href: route('admin.support.index'),
                        badge: `${stats?.open_tickets ?? 0} ${t('adminDashboard.metrics.pending')}`,
                    }
                    : null,
            ].filter(Boolean),
        },
        {
            key: 'communication',
            title: t('adminDashboard.groups.communication.title'),
            description: t('adminDashboard.groups.communication.description'),
            items: [
                canManageNotices
                    ? {
                        key: 'notices',
                        label: t('adminDashboard.modules.notices'),
                        description: t('adminDashboard.moduleDescriptions.notices'),
                        href: `${route('admin.dashboard')}#notices`,
                        badge: `${stats?.notices ?? 0} ${t('adminDashboard.metrics.configured')}`,
                    }
                    : null,
            ].filter(Boolean),
        },
        {
            key: 'imports',
            title: t('adminDashboard.groups.imports.title'),
            description: t('adminDashboard.groups.imports.description'),
            items: [
                canManageImports
                    ? {
                        key: 'imports',
                        label: t('adminDashboard.modules.imports'),
                        description: t('adminDashboard.moduleDescriptions.imports'),
                        href: route('importaciones.index'),
                        badge: `${stats?.imports ?? 0} ${t('adminDashboard.metrics.processed')}`,
                    }
                    : null,
            ].filter(Boolean),
        },
        {
            key: 'operational-read',
            title: t('adminDashboard.groups.operational.title'),
            description: t('adminDashboard.groups.operational.description'),
            note: isOperationalReadOnly ? t('adminDashboard.groups.operational.readOnlyNote') : null,
            items: [
                canViewWorks
                    ? {
                        key: 'works',
                        label: t('adminDashboard.modules.works'),
                        description: t('adminDashboard.moduleDescriptions.works'),
                        href: route('trabajos.index'),
                    }
                    : null,
                canViewOrders
                    ? {
                        key: 'orders',
                        label: t('adminDashboard.modules.orders'),
                        description: t('adminDashboard.moduleDescriptions.orders'),
                        href: route('pedidos.index'),
                    }
                    : null,
                canViewInvoices
                    ? {
                        key: 'billing',
                        label: t('adminDashboard.modules.billing'),
                        description: t('adminDashboard.moduleDescriptions.billing'),
                        href: route('facturas.index'),
                    }
                    : null,
                canViewClients
                    ? {
                        key: 'clients',
                        label: t('adminDashboard.modules.clients'),
                        description: t('adminDashboard.moduleDescriptions.clients'),
                        href: route('clientes.index'),
                    }
                    : null,
                canViewStations
                    ? {
                        key: 'stations',
                        label: t('adminDashboard.modules.stations'),
                        description: t('adminDashboard.moduleDescriptions.stations'),
                        href: route('estaciones.index'),
                    }
                    : null,
                canViewAudit
                    ? {
                        key: 'activity-log',
                        label: t('nav.activityLog'),
                        description: t('adminDashboard.moduleDescriptions.activityLog'),
                        href: route('registro.actividad.index'),
                    }
                    : null,
            ].filter(Boolean),
        },
    ].filter((group) => group.items.length > 0);

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

            <div className="ciete-page ciete-page-wide">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminDashboard.panelLabel')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('adminDashboard.overview')}</h2>
                    </div>
                    <p className="text-[11px] text-text-hint">{t('adminDashboard.lastAccessToday')}</p>
                </div>

                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7">
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-primary bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.openTickets')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.open_tickets ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.pending')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-progress-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.activeUsers')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.active_users ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.registered')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-pending-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.imports')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.imports ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.processed')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.auditToday')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.audit_today ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.today')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.notices')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.notices ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.configured')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-accent bg-surface p-3">
                        <p className="mb-1 text-[9px] font-bold uppercase leading-tight tracking-widest text-text-muted">
                            {t('adminDashboard.metrics.contexts')}
                        </p>
                        <p className="text-2xl font-medium leading-none text-text-main">{stats?.contexts ?? userContexts?.length ?? 0}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('adminDashboard.metrics.assigned')}</p>
                    </div>
                </div>

                <section id="notices" className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <div className="flex flex-col gap-3 border-b border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 className="text-sm font-medium text-text-main">
                            {t('adminDashboard.notices.title')}
                        </h3>
                        {canManageNotices ? (
                            <button
                                type="button"
                                onClick={() => startCreateNotice('notices')}
                                className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                            >
                                <Plus className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                {t('adminDashboard.notices.create')}
                            </button>
                        ) : null}
                    </div>

                    <div className="border-b border-border bg-surface-2/70 px-4 py-3">
                        <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminDashboard.notices.featuredTitle')}
                        </p>
                        {activeFeaturedNotice ? (
                            <div className="mt-2 rounded-xl border border-primary/15 bg-primary/6 px-3 py-3">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="inline-flex items-center gap-1 rounded-full border border-primary/20 bg-surface px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest text-primary">
                                        <Star className="h-3 w-3" strokeWidth={2} aria-hidden />
                                        {t('adminDashboard.notices.featuredBadge')}
                                    </span>
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                        {t(`adminDashboard.notices.categories.${activeFeaturedNotice.category}`)}
                                    </span>
                                </div>
                                <p className="mt-2 text-sm font-medium text-text-main">{activeFeaturedNotice.title_es}</p>
                                <p className="mt-1 text-xs leading-relaxed text-text-muted">{activeFeaturedNotice.body_es}</p>
                                <p className="mt-2 text-xs font-medium text-text-main">{activeFeaturedNotice.title_en}</p>
                                <p className="mt-1 text-xs leading-relaxed text-text-hint">{activeFeaturedNotice.body_en}</p>
                                {canManageNotices && activeFeaturedNotice.id ? (
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            onClick={() => startEditNotice(activeFeaturedNotice)}
                                            className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                                        >
                                            <Pencil className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                            {t('common.actions.edit')}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => toggleNoticeFeatured(activeFeaturedNotice)}
                                            className="inline-flex items-center gap-1.5 rounded-md border border-primary/20 bg-surface px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-primary transition hover:bg-primary/10"
                                        >
                                            <StarOff className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                            {t('adminDashboard.notices.unmarkFeatured')}
                                        </button>
                                    </div>
                                ) : null}
                            </div>
                        ) : (
                            <p className="mt-2 text-xs text-text-hint">{t('adminDashboard.notices.featuredEmpty')}</p>
                        )}
                    </div>

                    {draftNotice ? (
                        <div className="border-b border-border bg-surface px-4 py-4">
                            <p className="mb-3 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {editingNoticeId === 'new'
                                    ? t('adminDashboard.notices.formCreate')
                                    : t('adminDashboard.notices.formEdit')}
                            </p>
                            <div className="grid gap-3 lg:grid-cols-2">
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.category')}
                                    <select
                                        value={draftNotice.category}
                                        onChange={(event) => updateDraftNotice('category', event.target.value)}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main focus:border-primary focus:outline-none"
                                    >
                                        {NOTICE_CATEGORIES.map((category) => (
                                            <option key={category} value={category}>
                                                {t(`adminDashboard.notices.categories.${category}`)}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <div className="flex flex-wrap items-end gap-3">
                                    <label className="inline-flex items-center gap-2 text-xs font-medium text-text-muted">
                                        <input
                                            type="checkbox"
                                            checked={draftNotice.is_active}
                                            onChange={(event) => {
                                                const checked = event.target.checked;
                                                setDraftNotice((current) => current ? {
                                                    ...current,
                                                    is_active: checked,
                                                    is_featured: checked ? current.is_featured : false,
                                                } : current);
                                            }}
                                            className="rounded border-border text-primary focus:ring-primary"
                                        />
                                        {t('adminDashboard.notices.fields.active')}
                                    </label>
                                    <label className="inline-flex items-center gap-2 text-xs font-medium text-text-muted">
                                        <input
                                            type="checkbox"
                                            checked={draftNotice.is_featured}
                                            onChange={(event) => {
                                                const checked = event.target.checked;
                                                setDraftNotice((current) => current ? {
                                                    ...current,
                                                    is_active: checked ? true : current.is_active,
                                                    is_featured: checked,
                                                } : current);
                                            }}
                                            className="rounded border-border text-primary focus:ring-primary"
                                        />
                                        {t('adminDashboard.notices.fields.featured')}
                                    </label>
                                </div>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.titleEs')}
                                    <input
                                        type="text"
                                        value={draftNotice.title_es}
                                        onChange={(event) => updateDraftNotice('title_es', event.target.value)}
                                        maxLength={180}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                                        placeholder={t('adminDashboard.notices.placeholders.titleEs')}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.titleEn')}
                                    <input
                                        type="text"
                                        value={draftNotice.title_en}
                                        onChange={(event) => updateDraftNotice('title_en', event.target.value)}
                                        maxLength={180}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                                        placeholder={t('adminDashboard.notices.placeholders.titleEn')}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.bodyEs')}
                                    <textarea
                                        value={draftNotice.body_es}
                                        onChange={(event) => updateDraftNotice('body_es', event.target.value)}
                                        rows={4}
                                        maxLength={1200}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                                        placeholder={t('adminDashboard.notices.placeholders.bodyEs')}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.bodyEn')}
                                    <textarea
                                        value={draftNotice.body_en}
                                        onChange={(event) => updateDraftNotice('body_en', event.target.value)}
                                        rows={4}
                                        maxLength={1200}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                                        placeholder={t('adminDashboard.notices.placeholders.bodyEn')}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.startsAt')}
                                    <input
                                        type="datetime-local"
                                        value={draftNotice.starts_at}
                                        onChange={(event) => updateDraftNotice('starts_at', event.target.value)}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main focus:border-primary focus:outline-none"
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('adminDashboard.notices.fields.endsAt')}
                                    <input
                                        type="datetime-local"
                                        value={draftNotice.ends_at}
                                        onChange={(event) => updateDraftNotice('ends_at', event.target.value)}
                                        className="rounded-md border border-border bg-surface-2 px-2 py-2 text-xs font-normal normal-case tracking-normal text-text-main focus:border-primary focus:outline-none"
                                    />
                                </label>
                            </div>
                            <div className="mt-3 flex flex-wrap justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={cancelNoticeEdit}
                                    className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                                >
                                    <X className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                    {t('common.actions.cancel')}
                                </button>
                                <button
                                    type="button"
                                    onClick={submitNotice}
                                    disabled={savingNotices}
                                    className="inline-flex items-center gap-1.5 rounded-md bg-primary px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white transition hover:opacity-90 disabled:opacity-50"
                                >
                                    <Save className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                    {savingNotices ? '...' : t('common.actions.save')}
                                </button>
                            </div>
                        </div>
                    ) : null}

                    <div className="divide-y divide-border">
                        {NOTICE_CATEGORIES.map((category) => {
                            const categoryItems = (noticesData[category] || []).filter((item) => !item.is_featured);

                            return (
                                <div key={category} className="px-4 py-3">
                                    <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                                        <p className="text-[10px] font-bold uppercase tracking-widest text-(--ciete-red)">
                                            {t(`adminDashboard.notices.categories.${category}`)}
                                        </p>
                                        {canManageNotices ? (
                                            <button
                                                type="button"
                                                onClick={() => startCreateNotice(category)}
                                                className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                                            >
                                                <Plus className="h-3 w-3" strokeWidth={2} aria-hidden />
                                                {t('adminDashboard.notices.addItem')}
                                            </button>
                                        ) : null}
                                    </div>

                                    <ul className="space-y-2">
                                        {categoryItems.map((item) => (
                                            <li key={item.id} className="rounded-lg border border-border bg-surface-2 px-3 py-3">
                                                <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                    <div className="min-w-0">
                                                        <div className="flex flex-wrap items-center gap-2">
                                                            <span className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${
                                                                item.is_active
                                                                    ? 'bg-state-done-bg text-state-done-text'
                                                                    : 'bg-state-blocked-bg text-state-blocked-text'
                                                            }`}>
                                                                {item.is_active ? (
                                                                    <CheckCircle2 className="h-3 w-3" strokeWidth={2} aria-hidden />
                                                                ) : (
                                                                    <CircleOff className="h-3 w-3" strokeWidth={2} aria-hidden />
                                                                )}
                                                                {item.is_active
                                                                    ? t('adminDashboard.notices.active')
                                                                    : t('adminDashboard.notices.inactive')}
                                                            </span>
                                                        </div>
                                                        <p className="mt-2 text-sm font-medium text-text-main">{item.title_es}</p>
                                                        <p className="mt-1 text-xs leading-relaxed text-text-muted">{item.body_es}</p>
                                                        <p className="mt-2 text-xs font-medium text-text-main">{item.title_en}</p>
                                                        <p className="mt-1 text-xs leading-relaxed text-text-hint">{item.body_en}</p>
                                                    </div>
                                                    {canManageNotices ? (
                                                        <div className="flex shrink-0 flex-wrap gap-2">
                                                            <button
                                                                type="button"
                                                                onClick={() => startEditNotice(item)}
                                                                className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                                                            >
                                                                <Pencil className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                                                {t('common.actions.edit')}
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => toggleNoticeActive(item)}
                                                                className="inline-flex items-center gap-1.5 rounded-md border border-border bg-surface px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-muted transition hover:bg-surface-2"
                                                            >
                                                                {item.is_active ? (
                                                                    <CircleOff className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                                                ) : (
                                                                    <CheckCircle2 className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                                                )}
                                                                {item.is_active
                                                                    ? t('adminDashboard.notices.deactivate')
                                                                    : t('adminDashboard.notices.activate')}
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => toggleNoticeFeatured(item)}
                                                                className="inline-flex items-center gap-1.5 rounded-md border border-primary/20 bg-surface px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest text-primary transition hover:bg-primary/10"
                                                            >
                                                                <Star className="h-3.5 w-3.5" strokeWidth={2} aria-hidden />
                                                                {t('adminDashboard.notices.markFeatured')}
                                                            </button>
                                                        </div>
                                                    ) : null}
                                                </div>
                                            </li>
                                        ))}
                                        {categoryItems.length === 0 && (
                                            <li className="text-xs text-text-hint">{t('adminDashboard.notices.empty')}</li>
                                        )}
                                    </ul>
                                </div>
                            );
                        })}
                    </div>
                </section>

                <section id="maintenance" className="rounded-[12px] border border-border bg-surface p-4 shadow-sm">
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
                                {canManageMaintenance && !maintenanceActive ? (
                                    <button
                                        type="button"
                                        onClick={toggleMaintenance}
                                        className="inline-flex items-center rounded-md bg-(--ciete-red) px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white transition hover:opacity-90"
                                    >
                                        {t('adminDashboard.maintenance.enable')}
                                    </button>
                                ) : null}
                                {canManageMaintenance && maintenanceActive ? (
                                    <button
                                        type="button"
                                        onClick={toggleMaintenance}
                                        className="inline-flex items-center rounded-md border border-border bg-surface px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-surface-2"
                                    >
                                        {t('adminDashboard.maintenance.disable')}
                                    </button>
                                ) : null}
                                {!canManageMaintenance && (
                                    <p className="text-xs text-text-hint">{t('adminDashboard.maintenance.readOnly')}</p>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    {moduleGroups.map((group) => (
                        <section key={group.key} className="overflow-hidden rounded-[12px] border border-border bg-surface">
                            <div className="border-b border-border px-4 py-3">
                                <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {group.title}
                                </p>
                                <p className="mt-1 text-sm text-text-muted">{group.description}</p>
                            </div>

                            {group.note && (
                                <div className="border-b border-border bg-sky-50/80 px-4 py-3 text-xs text-sky-900">
                                    {group.note}
                                </div>
                            )}

                            <div className="divide-y divide-border">
                                {group.items.map((module) => (
                                    <Link
                                        key={module.key}
                                        href={module.href}
                                        className="flex items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-surface-2"
                                    >
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-text-main">{module.label}</p>
                                            <p className="mt-1 text-xs text-text-hint">{module.description}</p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {module.badge && (
                                                <span className="inline-flex rounded-full border border-border bg-surface-2 px-2 py-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">
                                                    {module.badge}
                                                </span>
                                            )}
                                            <span className="text-xs text-text-hint" aria-hidden>→</span>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-5">
                    <div className="overflow-hidden rounded-[12px] border border-border bg-surface lg:col-span-3">
                        <div className="border-b border-border px-4 py-3">
                            <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.activity.title')}</h3>
                        </div>
                        {activityItems.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('adminDashboard.activity.empty')}</div>
                        ) : (
                            <>
                                <div className="divide-y divide-border">
                                    {activityItems.map((item) => (
                                        <div key={item.id} className="flex gap-3 px-4 py-3">
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
                                <DashboardPagination
                                    meta={activityMeta}
                                    onPrevious={() => changeDashboardPage('activity', Math.max(1, activityMeta.current_page - 1))}
                                    onNext={() => changeDashboardPage('activity', Math.min(activityMeta.last_page, activityMeta.current_page + 1))}
                                    t={t}
                                />
                            </>
                        )}
                    </div>

                    <div className="overflow-hidden rounded-[12px] border border-border bg-surface lg:col-span-2">
                        <div className="border-b border-border px-4 py-3">
                            <h3 className="text-sm font-medium text-text-main">{t('adminDashboard.activeUsers.title')}</h3>
                        </div>
                        {usersItems.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('adminDashboard.activeUsers.empty')}</div>
                        ) : (
                            <>
                                <div className="divide-y divide-border">
                                    {usersItems.map((member) => (
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
                                <DashboardPagination
                                    meta={usersMeta}
                                    onPrevious={() => changeDashboardPage('users', Math.max(1, usersMeta.current_page - 1))}
                                    onNext={() => changeDashboardPage('users', Math.min(usersMeta.last_page, usersMeta.current_page + 1))}
                                    t={t}
                                />
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
