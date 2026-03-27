import CieteMark from '@/Components/CieteMark';
import TopNavbar from '@/Components/TopNavbar';
import { useI18n } from '@/i18n';
import { Link, usePage } from '@inertiajs/react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();

    const navLinkClass = (active) =>
        `flex items-center p-2 text-sm font-medium rounded-lg transition-colors ${
            active
                ? 'bg-[var(--ciete-red)] text-white hover:bg-[var(--ciete-red-dark)]'
                : 'text-white/70 hover:bg-white/10 hover:text-white'
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
                                    {t('nav.dashboard')}
                                </Link>
                            </div>
                        </div>

                        <div>
                            <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                {t('nav.groups.masters')}
                            </p>
                            <div className="space-y-1 text-white/70">
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.clients')}
                                </Link>
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.stations')}
                                </Link>
                            </div>
                        </div>

                        <div>
                            <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                {t('nav.groups.operations')}
                            </p>
                            <div className="space-y-1 text-white/70">
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.works')}
                                </Link>
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.orders')}
                                </Link>
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.legalizations')}
                                </Link>
                            </div>
                        </div>

                        <div>
                            <p className="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.2em] text-text-hint/40">
                                {t('nav.groups.reports')}
                            </p>
                            <div className="space-y-1 text-white/70">
                                <Link href="#" className={navLinkClass(false)}>
                                    {t('nav.reports')}
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
                        className="mb-1 block px-2 text-left text-[10px] text-white/50 transition-colors hover:text-white"
                    >
                        {t('nav.configuration')}
                    </Link>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="w-full px-2 text-left text-[10px] font-bold uppercase tracking-widest text-text-hint transition-colors hover:text-primary"
                    >
                        {t('common.actions.logOut')}
                    </Link>
                </div>
            </aside>

            <div className="flex flex-1 flex-col md:ml-[240px]">
                <TopNavbar header={header} />

                <main className="p-4 md:p-8">
                    <div className="mx-auto max-w-[1400px]">{children}</div>
                </main>

                <footer className="mt-auto border-t border-border p-6 text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('common.footer.projectVersion')}
                </footer>
            </div>
        </div>
    );
}
