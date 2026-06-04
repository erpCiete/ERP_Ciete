function hasPermission(user, permission) {
    return user?.permission_slugs?.includes(permission) ?? false;
}

function hasRoute(name, params = {}) {
    try {
        route(name, params);
        return true;
    } catch {
        return false;
    }
}

function hrefFor(name, params = {}) {
    return hasRoute(name, params) ? route(name, params) : null;
}

function isActive(patterns = []) {
    return patterns.some((pattern) => route().current(pattern));
}

function makeItem({ id, key, label, routeName, routeParams = {}, activePatterns, method, href: customHref }) {
    const href = customHref ?? (routeName ? hrefFor(routeName, routeParams) : null);

    if (!href) {
        return null;
    }

    return {
        id,
        key,
        label,
        href,
        method,
        active: isActive(activePatterns ?? [routeName]),
    };
}

function filterItems(items) {
    return items.filter(Boolean);
}

function section(id, label, items) {
    const filteredItems = filterItems(items);

    if (filteredItems.length === 0) {
        return null;
    }

    return { id, label, items: filteredItems };
}

export function getNavigationContextBadge(user, t) {
    if (user?.is_execution_moeve) {
        return t('nav.contextMoeve');
    }

    if (user?.is_execution_repsol) {
        return t('nav.contextRepsol');
    }

    return null;
}

export function buildTopNavbarItems(t, user) {
    if (!user) {
        return [];
    }

    return [];
}

export function buildSidebarSections(t, user) {
    if (!user) {
        return [];
    }

    const canViewWorks = hasPermission(user, 'trabajos.ver');
    const canCreateWorks = hasPermission(user, 'trabajos.crear');
    const canViewOrders = hasPermission(user, 'pedidos.ver');
    const canViewInvoices = hasPermission(user, 'facturas.ver');
    const canViewClients = hasPermission(user, 'clientes.ver');
    const canViewStations = hasPermission(user, 'estaciones.ver');
    const canViewMasters = hasPermission(user, 'maestros.ver');
    const canAccessAdminPanel = Boolean(user.can_access_admin_panel);
    const canManageUsers = Boolean(user.can_manage_users);
    const canViewSystemStatus = Boolean(user.can_view_system_status);
    const canManageMaintenance = Boolean(user.can_manage_maintenance);
    const canManageNotices = Boolean(user.can_manage_notices);
    const canManageImports = Boolean(user.can_manage_imports);
    const canViewAudit = Boolean(user.can_view_audit);
    const isTechnicalAdmin = canAccessAdminPanel;
    const isExcelMode = user.interface_mode === 'ciete_excel';
    const worksLabel = user.is_execution_moeve
        ? t('nav.worksMoeve')
        : user.is_execution_repsol
            ? t('nav.worksRepsol')
            : t('nav.works');

    const createWorkLabel = user.is_execution_moeve
        ? t('nav.createWorkMoeve')
        : user.is_execution_repsol
            ? t('nav.createWorkRepsol')
            : t('nav.createWork');

    const ordersLabel = user.is_execution_moeve
        ? t('nav.ordersMoeve')
        : user.is_execution_repsol
            ? t('nav.ordersRepsol')
            : t('nav.orders');

    const sections = [
        section('general', t('nav.groups.general'), [
            makeItem({
                id: 'home',
                key: 'nav.home',
                label: t('nav.home'),
                routeName: 'index',
                activePatterns: ['index'],
            }),
        ]),
    ];

    if (isTechnicalAdmin) {
        sections.push(
            section('administration', t('nav.groups.administration'), [
                makeItem({
                    id: 'admin-dashboard',
                    key: 'nav.adminPanel',
                    label: t('nav.adminDashboard'),
                    routeName: 'admin.dashboard',
                    activePatterns: ['admin.dashboard', 'admin.users.*', 'admin.support.*', 'admin.audit'],
                }),
                canViewSystemStatus
                    ? makeItem({
                        id: 'system-status',
                        key: 'nav.systemStatus',
                        label: t('nav.systemStatus'),
                        routeName: 'status',
                        activePatterns: ['status'],
                    })
                    : null,
            ])
        );
    } else if (user.can_access_direction_panel) {
        sections.push(
            section('direction', t('nav.groups.direction'), [
                canManageUsers
                    ? makeItem({
                        id: 'admin-users',
                        key: 'nav.adminUsers',
                        label: t('nav.adminUsers'),
                        routeName: 'admin.users.index',
                        activePatterns: ['admin.users.*'],
                    })
                    : null,
                canViewAudit
                    ? makeItem({
                        id: 'activity-log',
                        key: 'nav.activityLog',
                        label: t('nav.activityLog'),
                        routeName: 'registro.actividad.index',
                        activePatterns: ['registro.actividad.*'],
                    })
                    : null,
                canViewMasters
                    ? makeItem({
                        id: 'master-data',
                        key: 'nav.masterData',
                        label: t('nav.masterData'),
                        routeName: 'maestros.index',
                        activePatterns: ['maestros.*'],
                    })
                    : null,
            ])
        );

        sections.push(
            section('consultation', t('nav.groups.consultation'), [
                canViewWorks
                    ? makeItem({
                        id: 'works',
                        key: 'nav.works',
                        label: t('nav.works'),
                        routeName: 'trabajos.index',
                        activePatterns: ['trabajos.*'],
                    })
                    : null,
                canViewOrders
                    ? makeItem({
                        id: 'orders',
                        key: 'nav.orders',
                        label: t('nav.orders'),
                        routeName: 'pedidos.index',
                        activePatterns: ['pedidos.*'],
                    })
                    : null,
                canViewInvoices
                    ? makeItem({
                        id: 'invoices',
                        key: 'nav.invoices',
                        label: t('nav.invoices'),
                        routeName: 'facturas.index',
                        activePatterns: ['facturas.*'],
                    })
                    : null,
            ])
        );
    } else if (user.is_execution || user.is_execution_moeve || user.is_execution_repsol) {
        const executionGroupKey = user.is_execution_moeve
            ? 'nav.groups.executionMoeve'
            : user.is_execution_repsol
                ? 'nav.groups.executionRepsol'
                : 'nav.groups.execution';

        sections.push(
            section('execution', t(executionGroupKey), [
                canViewWorks
                    ? makeItem({
                        id: 'works',
                        key: 'nav.works',
                        label: worksLabel,
                        routeName: 'trabajos.index',
                        activePatterns: ['trabajos.index', 'trabajos.edit'],
                    })
                    : null,
                canCreateWorks && !isExcelMode
                    ? makeItem({
                        id: 'create-work',
                        key: 'nav.createWork',
                        label: createWorkLabel,
                        routeName: 'trabajos.create',
                        activePatterns: ['trabajos.create'],
                    })
                    : null,
                canViewStations
                    ? makeItem({
                        id: 'stations',
                        key: 'nav.stations',
                        label: t('nav.stations'),
                        routeName: 'estaciones.index',
                        activePatterns: ['estaciones.*'],
                    })
                    : null,
                canViewOrders
                    ? makeItem({
                        id: 'orders',
                        key: 'nav.orders',
                        label: ordersLabel,
                        routeName: 'pedidos.index',
                        activePatterns: ['pedidos.*'],
                    })
                    : null,
                canViewInvoices
                    ? makeItem({
                        id: 'invoices',
                        key: 'nav.invoices',
                        label: t('nav.invoices'),
                        routeName: 'facturas.index',
                        activePatterns: ['facturas.*'],
                    })
                    : null,
            ])
        );
    } else if (user.is_accounting) {
        sections.push(
            section('accounting', t('nav.groups.accounting'), [
                canViewOrders
                    ? makeItem({
                        id: 'orders',
                        key: 'nav.orders',
                        label: t('nav.orders'),
                        routeName: 'pedidos.index',
                        activePatterns: ['pedidos.*'],
                    })
                    : null,
                canViewInvoices
                    ? makeItem({
                        id: 'invoices',
                        key: 'nav.invoices',
                        label: t('nav.invoices'),
                        routeName: 'facturas.index',
                        activePatterns: ['facturas.*'],
                    })
                    : null,
                canViewAudit
                    ? makeItem({
                        id: 'activity-log',
                        key: 'nav.activityLog',
                        label: t('nav.activityLog'),
                        routeName: 'registro.actividad.index',
                        activePatterns: ['registro.actividad.*'],
                    })
                    : null,
            ])
        );
    }

    if (!canAccessAdminPanel && !user.can_access_direction_panel && !user.is_accounting && canViewAudit) {
        const generalSection = sections.find((sectionItem) => sectionItem?.id === 'general');

        if (generalSection) {
            generalSection.items.push(
                makeItem({
                    id: 'activity-log',
                    key: 'nav.activityLog',
                    label: t('nav.activityLog'),
                    routeName: 'registro.actividad.index',
                    activePatterns: ['registro.actividad.*'],
                }),
            );
        }
    }

    return sections.filter(Boolean);
}
