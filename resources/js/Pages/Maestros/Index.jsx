import ContextualPageHeader from '@/Components/ContextualPageHeader';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { AlertTriangle, Building2, MapPin, Tags, Users } from 'lucide-react';
import { useState } from 'react';

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

const baseRows = [
    {
        key: 'usuarios',
        title: 'Usuarios',
        subtitle: 'Roles y acceso',
        routeName: 'admin.users.index',
        permissionKey: 'usuarios',
        icon: Users,
    },
    {
        key: 'empresas',
        title: 'Empresas / clientes',
        subtitle: 'Base de empresa',
        routeName: 'clientes.index',
        permissionKey: 'clientes',
        icon: Building2,
    },
    {
        key: 'estaciones',
        title: 'Estaciones',
        subtitle: 'Codigo y localidad',
        routeName: 'estaciones.index',
        permissionKey: 'estaciones',
        icon: MapPin,
    },
];

const pricingRows = [
    {
        key: 'contratos',
        title: 'Contratos',
        routeName: 'maestros.contratos.index',
        permissionKey: 'contratos',
    },
    {
        key: 'sociedades',
        title: 'Sociedades / CIF',
        routeName: 'maestros.sociedades.index',
        permissionKey: 'sociedades',
    },
    {
        key: 'tarifarios',
        title: 'Tarifarios',
        routeName: 'maestros.tarifarios.index',
        permissionKey: 'tarifarios',
    },
    {
        key: 'lineas',
        title: 'Lineas de tarifa',
        routeName: 'maestros.tarifario-lineas.index',
        permissionKey: 'lineas',
    },
];

function CompactLink({ href, children }) {
    return (
        <Link
            href={href}
            className="inline-flex items-center rounded border border-border px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-text-main transition hover:bg-surface-2"
        >
            {children}
        </Link>
    );
}

function SectionRow({ icon: Icon, title, subtitle, count, href, createHref }) {
    return (
        <div className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 border-t border-border px-3 py-2 first:border-t-0">
            <div className="min-w-0">
                <div className="flex items-center gap-2 text-sm font-medium text-text-main">
                    <Icon className="size-4 text-(--ciete-red)" />
                    <span>{title}</span>
                    <span className="text-xs font-semibold text-text-hint">{count ?? 0}</span>
                </div>
                <div className="mt-0.5 text-xs text-text-muted">{subtitle}</div>
            </div>

            <div className="flex flex-wrap items-center justify-end gap-2">
                {createHref && <CompactLink href={createHref}>Nuevo</CompactLink>}
                {href && <CompactLink href={href}>Abrir</CompactLink>}
            </div>
        </div>
    );
}

function DiagnosticRow({ diagnostic, can }) {
    const canView = diagnostic.permissionKey ? Boolean(can[diagnostic.permissionKey]?.view) : true;
    const href = diagnostic.routeName && canView && hasRoute(diagnostic.routeName)
        ? route(diagnostic.routeName)
        : null;
    const tone = diagnostic.severity === 'critical'
        ? 'border-red-500/40 text-red-200'
        : 'border-amber-500/40 text-amber-200';

    return (
        <div className={`grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 border-t border-border px-3 py-2 first:border-t-0 ${tone}`}>
            <span className="rounded border border-current/50 px-2 py-0.5 text-[11px] font-semibold">{diagnostic.count}</span>
            <div className="min-w-0">
                <div className="truncate text-sm font-medium">{diagnostic.title}</div>
                <div className="truncate text-xs text-text-muted">{diagnostic.description}</div>
            </div>
            {href ? <CompactLink href={href}>{diagnostic.actionLabel}</CompactLink> : <span />}
        </div>
    );
}

export default function MaestrosIndex({ summary = {}, diagnostics = [], can = {} }) {
    const { auth } = usePage().props;
    const [showDiagnostics, setShowDiagnostics] = useState(false);

    const user = auth?.user;
    const activeContext = user?.active_context;
    const canSeeSystemCatalogs = Boolean(
        user?.is_admin
        || user?.is_technical_admin
        || user?.can_manage_support
        || user?.can_manage_maintenance
        || user?.can_access_admin_panel,
    );

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">Maestros</h2>}
        >
            <Head title="Maestros" />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Datos maestros"
                    title="Maestros"
                    description="Base operativa y arbol de contratos y tarifas."
                />

                {activeContext?.is_all && (
                    <div className="mb-4 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
                        Selecciona un contexto concreto para crear o mantener maestros.
                    </div>
                )}

                {diagnostics.length > 0 && (
                    <section className="mb-4 overflow-hidden rounded-lg border border-border bg-surface">
                        <div className="flex items-center justify-between gap-3 px-3 py-2">
                            <div className="flex min-w-0 items-center gap-2 text-sm font-medium text-text-main">
                                <AlertTriangle className="size-4 text-amber-300" />
                                <span>{diagnostics.length} alerta{diagnostics.length === 1 ? '' : 's'} operativa{diagnostics.length === 1 ? '' : 's'}</span>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowDiagnostics((value) => !value)}
                                className="rounded border border-border px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-text-main transition hover:bg-surface-2"
                            >
                                {showDiagnostics ? 'Ocultar' : 'Ver'}
                            </button>
                        </div>

                        {showDiagnostics && (
                            <div className="border-t border-border">
                                {diagnostics.map((diagnostic) => (
                                    <DiagnosticRow key={diagnostic.key} diagnostic={diagnostic} can={can} />
                                ))}
                            </div>
                        )}
                    </section>
                )}

                <div className="space-y-4">
                    <section className="overflow-hidden rounded-lg border border-border bg-surface">
                        <div className="flex items-center justify-between gap-3 border-b border-border px-3 py-2">
                            <div>
                                <h3 className="text-sm font-semibold text-text-main">Base operativa</h3>
                                <p className="text-xs text-text-muted">Usuarios, empresas y estaciones.</p>
                            </div>
                        </div>

                        <div>
                            {baseRows.map((item) => {
                                const permissions = item.permissionKey ? can[item.permissionKey] : null;
                                const canView = item.permissionKey ? Boolean(permissions?.view) : true;

                                if (!canView) {
                                    return null;
                                }

                                const href = item.routeName && hasRoute(item.routeName) ? route(item.routeName) : null;
                                const createRouteName = item.key === 'empresas'
                                    ? 'clientes.create'
                                    : item.key === 'estaciones'
                                        ? 'estaciones.create'
                                        : null;
                                const createHref = can.create_contextual && createRouteName && hasRoute(createRouteName)
                                    ? route(createRouteName)
                                    : null;

                                return (
                                    <SectionRow
                                        key={item.key}
                                        icon={item.icon}
                                        title={item.title}
                                        subtitle={item.subtitle}
                                        count={summary[item.key] ?? 0}
                                        href={href}
                                        createHref={createHref}
                                    />
                                );
                            })}
                        </div>
                    </section>

                    <section className="overflow-hidden rounded-lg border border-border bg-surface">
                        <div className="flex items-center justify-between gap-3 border-b border-border px-3 py-2">
                            <div>
                                <h3 className="text-sm font-semibold text-text-main">Arbol contratos y tarifas</h3>
                                <p className="text-xs text-text-muted">Empresa - sociedades/CIF - contratos - tarifarios - lineas.</p>
                            </div>
                            {hasRoute('maestros.contratos-tarifas') && (
                                <CompactLink href={route('maestros.contratos-tarifas')}>Abrir</CompactLink>
                            )}
                        </div>

                        <div className="border-b border-border px-3 py-2 text-xs text-text-muted">
                            {summary.contratos ?? 0} contratos · {summary.sociedades ?? 0} sociedades/CIF · {summary.tarifarios ?? 0} tarifarios · {summary.lineas_tarifario ?? 0} lineas
                        </div>

                        <div className="grid gap-2 px-3 py-3 lg:grid-cols-[minmax(0,1fr)_auto]">
                            <div className="min-w-0">
                                <div className="text-sm font-medium text-text-main">Entrada principal</div>
                                <div className="mt-1 text-xs text-text-muted">Vista unica en arbol para trabajo operativo y revision rapida.</div>
                            </div>
                            <div className="flex flex-wrap items-center justify-end gap-2">
                                {pricingRows.map((item) => {
                                    const permissions = item.permissionKey ? can[item.permissionKey] : null;
                                    const canView = item.permissionKey ? Boolean(permissions?.view) : true;
                                    const href = item.routeName && canView && hasRoute(item.routeName) ? route(item.routeName) : null;

                                    if (!href) {
                                        return null;
                                    }

                                    return (
                                        <CompactLink key={item.key} href={href}>
                                            {item.title}
                                        </CompactLink>
                                    );
                                })}
                            </div>
                        </div>
                    </section>

                    {canSeeSystemCatalogs && (
                        <section className="overflow-hidden rounded-lg border border-border bg-surface">
                            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 px-3 py-2">
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2 text-sm font-medium text-text-main">
                                        <Tags className="size-4 text-(--ciete-red)" />
                                        <span>Catalogos de sistema</span>
                                        <span className="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-hint">Solo tecnico</span>
                                    </div>
                                    <div className="mt-0.5 text-xs text-text-muted">
                                        Unidades, tipos de documento y tipos de trabajo de apoyo interno.
                                    </div>
                                </div>
                            </div>
                        </section>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
