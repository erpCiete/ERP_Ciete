import CieteMark from '@/Components/CieteMark';
import FloatingContextHelp from '@/Components/FloatingContextHelp';
import TopNavbar from '@/Components/TopNavbar';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildSidebarSections, getNavigationContextBadge } from '@/navigation/sidebar';
import { Link, usePage } from '@inertiajs/react';

function resolveRoleLabel(user) {
    return user?.primary_role_name ?? user?.roles?.[0]?.nombre ?? user?.primary_role_slug ?? '-';
}

export default function AuthenticatedLayout({ header, children, contentWidthClass = 'max-w-[1400px]' }) {
    const page = usePage();
    const user = page.props.auth.user;
    const { t } = useI18n();
    const sidebarSections = buildSidebarSections(t, user);
    const contextBadge = getNavigationContextBadge(user, t);
    const roleLabel = resolveRoleLabel(user);
    const shouldShowFloatingHelp = page.component !== 'Welcome';
    const isProfileActive = route().current('profile.*');
    const SettingsIcon = getNavigationIcon('nav.myProfile');
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

                <div className="mt-auto border-t border-white/10 bg-black/10 p-4">
                    <div className="mb-3 flex items-center gap-3 px-2">
                        {user.avatar_url && (
                            <img
                                src={user.avatar_url}
                                alt="Avatar"
                                className="h-10 w-10 rounded-lg border border-white/20 bg-white/5 p-1"
                            />
                        )}
                        <div className="min-w-0">
                            <p className="truncate text-xs font-bold text-white">{user.nombre ?? user.nombre_usuario ?? '-'}</p>
                            <p className="truncate text-[10px] text-text-hint">{user.email}</p>
                            <p className="truncate pt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white/45">
                                {roleLabel}
                            </p>
                            {contextBadge && (
                                <p className="mt-1 inline-flex rounded-full border border-white/15 px-2 py-0.5 text-[10px] font-semibold text-white/70">
                                    {contextBadge}
                                </p>
                            )}
                        </div>
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

            {shouldShowFloatingHelp && <FloatingContextHelp />}
        </div>
    );
}
