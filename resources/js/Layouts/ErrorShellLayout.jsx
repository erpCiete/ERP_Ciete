import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildSidebarSections, buildTopNavbarItems, getNavigationContextBadge } from '@/navigation/sidebar';
import { Link, usePage } from '@inertiajs/react';

function resolveRoleLabel(user) {
    return user?.primary_role_name ?? user?.roles?.[0]?.nombre ?? user?.primary_role_slug ?? '-';
}

export default function ErrorShellLayout({ header, children }) {
    const { t } = useI18n();
    const { auth = {} } = usePage().props;
    const user = auth.user ?? null;
    const sidebarSections = user ? buildSidebarSections(t, user) : [];
    const topItems = user ? buildTopNavbarItems(t, user) : [];
    const contextBadge = user ? getNavigationContextBadge(user, t) : null;
    const roleLabel = user ? resolveRoleLabel(user) : null;
    const SettingsIcon = getNavigationIcon('nav.myProfile');
    const LogOutIcon = getNavigationIcon('common.actions.logOut');

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
                                                        {item.label}
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
                        <div className="mb-3 px-2">
                            <p className="truncate text-xs font-bold text-white">
                                {user.nombre ?? user.nombre_usuario ?? '-'}
                            </p>
                            <p className="mt-1 truncate text-[10px] text-white/55">{user.email}</p>
                            <p className="mt-1 truncate text-[10px] font-bold uppercase tracking-[0.14em] text-white/45">
                                {roleLabel}
                            </p>
                            {contextBadge && (
                                <p className="mt-2 inline-flex rounded-full border border-white/15 px-2 py-0.5 text-[10px] font-semibold text-white/70">
                                    {contextBadge}
                                </p>
                            )}
                        </div>

                        <Link
                            href={route('profile.edit')}
                            className="mb-1 inline-flex items-center gap-1.5 px-2 text-left text-[10px] text-white/50 transition-colors hover:text-white"
                        >
                            {SettingsIcon && <SettingsIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                            {t('nav.myProfile')}
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
                                                    {item.label}
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
