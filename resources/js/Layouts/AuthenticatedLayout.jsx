import CieteMark from '@/Components/CieteMark';
import TopNavbar from '@/Components/TopNavbar';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { Link, usePage } from '@inertiajs/react';

export default function AuthenticatedLayout({ header, children, contentWidthClass = 'max-w-[1400px]' }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();
    const hasClosureRole = user?.role_slugs?.includes('control_cierre');
    const canManageClientes = user?.permission_slugs?.includes('empresas_contactos.gestionar');
    const canViewEstaciones = user?.permission_slugs?.some((permission) =>
        ['estaciones.ver', 'estaciones.gestionar'].includes(permission)
    );
    const DashboardIcon = getNavigationIcon('nav.dashboard');
    const ClosureIcon = getNavigationIcon('nav.closurePanel');
    const ClientsIcon = getNavigationIcon('nav.clients');
    const StationsIcon = getNavigationIcon('nav.stations');
    const WorksIcon = getNavigationIcon('nav.works');
    const OrdersIcon = getNavigationIcon('nav.orders');
    const LegalizationsIcon = getNavigationIcon('nav.legalizations');
    const ReportsIcon = getNavigationIcon('nav.reports');
    const SettingsIcon = getNavigationIcon('nav.configuration');
    const LogOutIcon = getNavigationIcon('common.actions.logOut');

    const navLinkClass = (active) =>
        `group block rounded-lg px-2.5 py-2 text-sm font-medium transition-colors duration-200 focus-visible:outline-hidden ${
            active
                ? 'text-(--ciete-red)'
                : 'text-white/75 hover:text-(--ciete-red) focus-visible:text-(--ciete-red)'
        }`;

    const navLinkLabelClass = (active) =>
        `relative inline-block after:absolute after:bottom-[-2px] after:left-0 after:h-px after:w-full after:origin-left after:bg-(--ciete-red) after:content-[''] after:transition-transform after:duration-200 ${
            active ? 'after:scale-x-0' : 'after:scale-x-0 group-hover:after:scale-x-100'
        }`;

    return (
        <div className="flex min-h-screen bg-surface font-sans antialiased text-text-main">
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-[240px] flex-col bg-secondary text-white shadow-xl md:flex">
                <div className="p-6">
                    <div className="mb-10 flex items-center gap-3">
                        <CieteMark className="h-8 w-8 text-primary" />
                        <span className="text-lg font-bold tracking-tight">{t('nav.brandShort')}</span>
                    </div>

                    <nav className="space-y-6">
                        <div>
                            <p className="mb-4 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/50">
                                {t('nav.groups.general')}
                            </p>
                            <div className="space-y-1">
                                <Link href={route('dashboard')} className={navLinkClass(route().current('dashboard'))}>
                                    <span className="inline-flex items-center gap-2">
                                        {DashboardIcon && <DashboardIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span className={navLinkLabelClass(route().current('dashboard'))}>{t('nav.dashboard')}</span>
                                    </span>
                                </Link>
                                {hasClosureRole && (
                                    <Link href={route('cierre.dashboard')} className={navLinkClass(route().current('cierre.dashboard'))}>
                                        <span className="inline-flex items-center gap-2">
                                            {ClosureIcon && <ClosureIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                            <span className={navLinkLabelClass(route().current('cierre.dashboard'))}>{t('nav.closurePanel')}</span>
                                        </span>
                                    </Link>
                                )}
                            </div>
                        </div>

                        {(canManageClientes || canViewEstaciones) && (
                            <div>
                                <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                    {t('nav.groups.masters')}
                                </p>
                                <div className="space-y-1 text-white/70">
                                    {canManageClientes && (
                                        <Link href={route('clientes.index')} className={navLinkClass(route().current('clientes.*'))}>
                                            <span className="inline-flex items-center gap-2">
                                                {ClientsIcon && <ClientsIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                                <span className={navLinkLabelClass(route().current('clientes.*'))}>{t('nav.clients')}</span>
                                            </span>
                                        </Link>
                                    )}
                                    {canViewEstaciones && (
                                        <Link href={route('estaciones.index')} className={navLinkClass(route().current('estaciones.*'))}>
                                            <span className="inline-flex items-center gap-2">
                                                {StationsIcon && <StationsIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                                <span className={navLinkLabelClass(route().current('estaciones.*'))}>{t('nav.stations')}</span>
                                            </span>
                                        </Link>
                                    )}
                                </div>
                            </div>
                        )}

                        <div>
                            <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                {t('nav.groups.operations')}
                            </p>
                            <div className="space-y-1 text-white/70">
                                <Link href="#" className={navLinkClass(false)}>
                                    <span className="inline-flex items-center gap-2">
                                        {WorksIcon && <WorksIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span className={navLinkLabelClass(false)}>{t('nav.works')}</span>
                                    </span>
                                </Link>
                                <Link href="#" className={navLinkClass(false)}>
                                    <span className="inline-flex items-center gap-2">
                                        {OrdersIcon && <OrdersIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span className={navLinkLabelClass(false)}>{t('nav.orders')}</span>
                                    </span>
                                </Link>
                                <Link href="#" className={navLinkClass(false)}>
                                    <span className="inline-flex items-center gap-2">
                                        {LegalizationsIcon && <LegalizationsIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span className={navLinkLabelClass(false)}>{t('nav.legalizations')}</span>
                                    </span>
                                </Link>
                            </div>
                        </div>

                        <div>
                            <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                {t('nav.groups.reports')}
                            </p>
                            <div className="space-y-1 text-white/70">
                                <Link href="#" className={navLinkClass(false)}>
                                    <span className="inline-flex items-center gap-2">
                                        {ReportsIcon && <ReportsIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span className={navLinkLabelClass(false)}>{t('nav.reports')}</span>
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </nav>
                </div>

                <div className="mt-auto border-t border-white/10 bg-black/10 p-4">
                    <div className="mb-3 flex items-center gap-3 px-2">
                        {user.avatar_url && (
                            <img
                                src={user.avatar_url}
                                alt="Avatar"
                                className="h-10 w-10 rounded-lg border border-white/20 bg-white/5 p-1"
                            />
                        )}
                        <div>
                            <p className="truncate text-xs font-bold text-white">{user.nombre ?? user.nombre_usuario ?? '-'}</p>
                            <p className="truncate text-[10px] text-text-hint">{user.email}</p>
                        </div>
                    </div>
                    <Link
                        href={route('profile.edit')}
                        className="mb-1 inline-flex items-center gap-1.5 px-2 text-left text-[10px] text-white/50 transition-colors hover:text-white"
                    >
                        {SettingsIcon && <SettingsIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                        {t('nav.configuration')}
                    </Link>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="inline-flex w-full items-center gap-1.5 px-2 text-left text-[10px] font-bold uppercase tracking-widest text-text-hint transition-colors hover:text-primary"
                    >
                        {LogOutIcon && <LogOutIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                        {t('common.actions.logOut')}
                    </Link>
                </div>
            </aside>

            <div className="flex flex-1 flex-col md:ml-[240px]">
                <TopNavbar header={header} />

                <main className="p-4 md:p-8">
                    <div className={`mx-auto w-full ${contentWidthClass}`}>{children}</div>
                </main>

                <footer className="mt-auto border-t border-border p-6 text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('common.footer.projectVersion')}
                </footer>
            </div>
        </div>
    );
}
