import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { Link, usePage } from '@inertiajs/react';

function buildSidebarSections(t, user) {
    const canManageClientes = user?.permission_slugs?.includes('empresas_contactos.gestionar');
    const canViewEstaciones = user?.permission_slugs?.some((permission) =>
        ['estaciones.ver', 'estaciones.gestionar'].includes(permission),
    );

    const generalItems = [
        { id: 'home', key: 'nav.home', href: route('index') },
        ...(user ? [{ id: 'dashboard', key: 'nav.dashboard', href: route('dashboard') }] : []),
        ...(user?.is_admin ? [{ id: 'admin', key: 'nav.adminPanel', href: route('admin.dashboard') }] : []),
    ];

    const masterItems = [
        ...(canManageClientes ? [{ id: 'clients', key: 'nav.clients', href: route('clientes.index') }] : []),
        ...(canViewEstaciones ? [{ id: 'stations', key: 'nav.stations', href: route('estaciones.index') }] : []),
    ];

    const accessItems = [
        { id: 'profile', key: 'nav.configuration', href: route('profile.edit') },
        { id: 'logout', key: 'common.actions.logOut', href: route('logout'), method: 'post' },
    ];

    return [
        {
            id: 'general',
            label: t('nav.groups.general'),
            items: generalItems,
        },
        ...(masterItems.length > 0
            ? [
                  {
                      id: 'masters',
                      label: t('nav.groups.masters'),
                      items: masterItems,
                  },
              ]
            : []),
        {
            id: 'access',
            label: t('errors.navigationSection'),
            items: accessItems,
        },
    ];
}

function buildTopItems(t, user) {
    return [
        { id: 'home', key: 'nav.home', href: route('index') },
        { id: 'dashboard', key: 'nav.dashboard', href: route('dashboard') },
        ...(user?.is_admin ? [{ id: 'admin', key: 'nav.adminPanel', href: route('admin.dashboard') }] : []),
    ];
}

export default function ErrorShellLayout({ header, children }) {
    const { t } = useI18n();
    const { auth = {} } = usePage().props;
    const user = auth.user ?? null;
    const sidebarSections = user ? buildSidebarSections(t, user) : [];
    const topItems = user ? buildTopItems(t, user) : [];

    const sidebarLinkClass =
        'group flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-white/78 transition-colors duration-200 hover:bg-white/10 hover:text-white focus-visible:outline-hidden focus-visible:bg-white/10 focus-visible:text-white';

    return (
        <div className="flex min-h-screen bg-surface font-sans text-text-main antialiased">
            {user && (
                <aside className="fixed inset-y-0 left-0 z-30 hidden w-[240px] flex-col bg-secondary text-white shadow-xl md:flex">
                    <div className="p-6">
                        <div className="mb-10 flex items-center gap-3">
                            <CieteMark className="h-8 w-8" />
                            <div>
                                <p className="text-lg font-bold tracking-tight">{t('nav.brandShort')}</p>
                                <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-white/45">
                                    {t('errors.sidebarLabel')}
                                </p>
                            </div>
                        </div>

                        <nav className="space-y-6">
                            {sidebarSections.map((section) => (
                                <div key={section.id}>
                                    <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">
                                        {section.label}
                                    </p>
                                    <div className="space-y-1">
                                        {section.items.map((item) => {
                                            const ItemIcon = getNavigationIcon(item.key);

                                            return (
                                                <Link
                                                    key={item.id}
                                                    href={item.href}
                                                    method={item.method}
                                                    as={item.method ? 'button' : undefined}
                                                    className={sidebarLinkClass}
                                                >
                                                    {ItemIcon && (
                                                        <ItemIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />
                                                    )}
                                                    <span className="relative inline-block after:absolute after:bottom-[-2px] after:left-0 after:h-px after:w-full after:origin-left after:bg-(--ciete-red) after:scale-x-0 after:content-[''] after:transition-transform after:duration-200 group-hover:after:scale-x-100">
                                                        {t(item.key)}
                                                    </span>
                                                </Link>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </nav>
                    </div>

                    <div className="mt-auto border-t border-white/10 bg-black/10 p-4">
                        <div className="px-2">
                            <p className="truncate text-xs font-bold text-white">
                                {user.nombre ?? user.nombre_usuario ?? '-'}
                            </p>
                            <p className="mt-1 truncate text-[10px] text-white/55">{user.email}</p>
                        </div>
                    </div>
                </aside>
            )}

            <div className={`flex flex-1 flex-col ${user ? 'md:ml-[240px]' : ''}`}>
                <header className="sticky top-0 z-40 border-b border-border bg-surface shadow-sm">
                    <div className="flex min-h-13 items-center gap-4 px-4 md:min-h-16 md:px-8">
                        <div className="flex min-w-0 flex-1 items-center gap-3">
                            <div className="flex items-center gap-2 md:hidden">
                                <CieteMark className="h-6 w-6" />
                                <span className="text-sm font-bold tracking-tight text-text-main">{t('nav.brandShort')}</span>
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-[10px] font-bold uppercase tracking-[0.18em] text-text-hint">
                                    {t('errors.headerEyebrow')}
                                </p>
                                <h2 className="truncate text-sm font-semibold text-text-main md:text-base">{header}</h2>
                            </div>
                        </div>

                        {topItems.length > 0 && (
                            <nav className="hidden items-center text-[10px] font-bold uppercase tracking-widest md:flex">
                                {topItems.map((item, index) => {
                                    const ItemIcon = getNavigationIcon(item.key);

                                    return (
                                        <div key={item.id} className="flex items-center">
                                            {index > 0 && (
                                                <span className="mx-1.5 select-none font-light text-border/40">|</span>
                                            )}
                                            <Link
                                                href={item.href}
                                                method={item.method}
                                                as={item.method ? 'button' : undefined}
                                                className="group inline-flex items-center gap-1.5 pb-[3px] text-text-main/60 transition-colors duration-200 hover:text-(--ciete-red) focus-visible:outline-hidden focus-visible:text-(--ciete-red)"
                                            >
                                                {ItemIcon && <ItemIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                                                <span className="relative inline-block after:absolute after:-bottom-px after:left-0 after:h-px after:w-full after:origin-left after:bg-(--ciete-red) after:scale-x-0 after:content-[''] after:transition-transform after:duration-200 group-hover:after:scale-x-100">
                                                    {t(item.key)}
                                                </span>
                                            </Link>
                                        </div>
                                    );
                                })}
                            </nav>
                        )}

                        <GlobalPreferenceSelectors compact />
                    </div>
                </header>

                <main className="flex-1 p-4 md:p-8">
                    <div className="mx-auto w-full max-w-[1400px]">{children}</div>
                </main>

                <footer className="mt-auto border-t border-border p-6 text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('common.footer.projectVersion')}
                </footer>
            </div>
        </div>
    );
}
