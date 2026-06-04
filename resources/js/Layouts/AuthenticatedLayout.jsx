import CieteMark from '@/Components/CieteMark';
import FloatingContextHelp from '@/Components/FloatingContextHelp';
import TopNavbar from '@/Components/TopNavbar';
import UserAccountAvatar from '@/Components/UserAccountAvatar';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildSidebarSections } from '@/navigation/sidebar';
import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function AuthenticatedLayout({
    header,
    children,
    contentWidthClass = 'max-w-[1400px]',
    desktopSidebarInitiallyHidden = false,
    showDesktopSidebarToggle = false,
}) {
    const page = usePage();
    const user = page.props.auth.user;
    const { t } = useI18n();
    const sidebarSections = buildSidebarSections(t, user);
    const shouldShowFloatingHelp = page.component !== 'Welcome';
    const isProfileActive = route().current('profile.*');
    const SettingsIcon = getNavigationIcon('nav.myProfile');
    const LogOutIcon = getNavigationIcon('common.actions.logOut');
    const desktopSidebarWidthClass = 'xl:w-[240px] 2xl:w-[252px]';
    const desktopContentOffsetClass = 'xl:ml-[240px] 2xl:ml-[252px]';
    const [isDesktopSidebarOpen, setIsDesktopSidebarOpen] = useState(!desktopSidebarInitiallyHidden);

    useEffect(() => {
        setIsDesktopSidebarOpen(!desktopSidebarInitiallyHidden);
    }, [desktopSidebarInitiallyHidden]);

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
        <div className="flex min-h-dvh overflow-x-clip bg-surface font-sans antialiased text-text-main">
            <aside
                className={`fixed inset-y-0 left-0 z-30 hidden ${desktopSidebarWidthClass} flex-col overflow-hidden bg-secondary text-white shadow-xl transition-transform duration-200 xl:flex ${
                    isDesktopSidebarOpen ? 'xl:translate-x-0' : 'xl:-translate-x-full'
                }`}
            >
                <div className="flex h-full min-h-0 flex-1 flex-col">
                    <div className="flex h-[calc(var(--app-shell-header-height)+1px)] items-center gap-3 border-b border-white/10 px-5 xl:px-6">
                        <CieteMark className="h-8 w-8 shrink-0 text-primary" />
                        <span className="truncate text-lg font-bold tracking-tight">{t('nav.brandShort')}</span>
                    </div>

                    <div className="min-h-0 flex-1 overflow-y-auto px-4 py-5 xl:px-5">
                        <nav className="space-y-5 pb-4">
                            {sidebarSections.map((section) => (
                                <div key={section.id}>
                                    <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                        {section.label}
                                    </p>
                                    <div className="space-y-1 text-white/70">
                                        {section.items.map((item) => {
                                            const ItemIcon = getNavigationIcon(item.key);

                                            return (
                                                <Link
                                                    key={item.id}
                                                    href={item.href}
                                                    method={item.method}
                                                    as={item.method ? 'button' : undefined}
                                                    className={navLinkClass(item.active)}
                                                >
                                                    <span className="inline-flex items-center gap-2">
                                                        {ItemIcon && <ItemIcon className="h-5 w-5 shrink-0" strokeWidth={1.9} aria-hidden />}
                                                        <span className={navLinkLabelClass(item.active)}>{item.label}</span>
                                                    </span>
                                                </Link>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </nav>
                    </div>

                    <div className="border-t border-white/10 bg-black/10 p-4">
                        <div className="mb-3 flex items-center gap-2.5 px-2">
                            <UserAccountAvatar user={user} className="h-8 w-8" />
                            <p className="truncate text-[10px] font-semibold text-text-hint">{user.email}</p>
                        </div>
                        <Link
                            href={route('profile.edit')}
                            className={`${navLinkClass(isProfileActive)} mb-1 text-left text-[10px]`}
                        >
                            <span className="inline-flex items-center gap-1.5">
                                {SettingsIcon && <SettingsIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                                <span className={navLinkLabelClass(isProfileActive)}>{t('nav.myProfile')}</span>
                            </span>
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
                </div>
            </aside>

            <div className={`flex min-w-0 flex-1 flex-col ${isDesktopSidebarOpen ? desktopContentOffsetClass : ''}`}>
                <TopNavbar
                    header={header}
                    showDesktopSidebarToggle={showDesktopSidebarToggle}
                    isDesktopSidebarOpen={isDesktopSidebarOpen}
                    onDesktopSidebarToggle={() => setIsDesktopSidebarOpen((current) => !current)}
                />

                <main className="min-w-0 flex-1 overflow-x-clip px-4 py-4 pb-24 sm:px-5 sm:pb-8 lg:px-6 xl:px-8 xl:py-6">
                    <div className={`mx-auto w-full min-w-0 ${contentWidthClass}`}>{children}</div>
                </main>

                <footer className="mt-auto border-t border-border px-4 py-4 text-center text-[10px] uppercase tracking-widest text-text-hint sm:px-5 lg:px-6 xl:px-8">
                    {t('common.footer.projectVersion')}
                </footer>
            </div>

            {shouldShowFloatingHelp && <FloatingContextHelp />}
        </div>
    );
}
