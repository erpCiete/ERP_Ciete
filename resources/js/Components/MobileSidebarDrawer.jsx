import CieteMark from '@/Components/CieteMark';
import { Link } from '@inertiajs/react';

export default function MobileSidebarDrawer({
    open,
    drawerId,
    drawerRef,
    onClose,
    onNavigate,
    sections,
    user,
    t,
}) {
    return (
        <>
            {open && (
                <button
                    type="button"
                    aria-label={t('common.aria.closeNavigationPanel')}
                    className="fixed inset-0 z-[70] bg-black/45 md:hidden"
                    onClick={onClose}
                />
            )}

            <aside
                id={drawerId}
                ref={drawerRef}
                role="dialog"
                aria-modal="true"
                aria-label={t('common.aria.navigationMenu')}
                tabIndex={-1}
                className={`fixed inset-y-0 left-0 z-[80] flex w-[min(22rem,90vw)] flex-col bg-secondary text-white shadow-2xl transition-transform duration-200 ease-out md:hidden ${
                    open ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex items-center justify-between border-b border-white/10 px-4 py-3">
                    <div className="flex items-center gap-2.5">
                        <CieteMark className="h-7 w-7 text-primary" />
                        <span className="text-sm font-bold tracking-tight">{t('nav.brandShort')}</span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md border border-white/20 px-2.5 py-1 text-xs font-bold uppercase tracking-widest text-white/80"
                    >
                        {t('common.actions.close')}
                    </button>
                </div>

                <div className="flex-1 overflow-y-auto px-4 py-4">
                    <nav className="space-y-6">
                        {sections.map((section) => (
                            <div key={section.id}>
                                <p className="mb-2 px-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white/45">
                                    {section.label}
                                </p>
                                <div className="space-y-1">
                                    {section.items.map((item) => {
                                        const baseClass = `flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                                            item.active
                                                ? 'bg-[var(--ciete-red)] text-white hover:bg-[var(--ciete-red-dark)]'
                                                : 'text-white/80 hover:bg-white/10 hover:text-white'
                                        }`;

                                        if (!item.href) {
                                            return (
                                                <span key={item.id} className={`${baseClass} opacity-60`}>
                                                    {item.label}
                                                </span>
                                            );
                                        }

                                        return (
                                            <Link
                                                key={item.id}
                                                href={item.href}
                                                className={baseClass}
                                                onClick={onNavigate}
                                            >
                                                {item.label}
                                            </Link>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </nav>
                </div>

                <div className="border-t border-white/10 bg-black/10 p-4">
                    <div className="mb-3 flex items-center gap-3 px-1">
                        {user.avatar_url && (
                            <img
                                src={user.avatar_url}
                                alt="Avatar"
                                className="h-10 w-10 rounded-lg border border-white/20 bg-white/5 p-1"
                            />
                        )}
                        <div>
                            <p className="truncate text-xs font-bold text-white">{user.nombre ?? user.nombre_usuario ?? '-'}</p>
                            <p className="truncate text-[10px] text-white/55">{user.email}</p>
                        </div>
                    </div>

                    <Link
                        href={route('profile.edit')}
                        className="mb-1 block px-1 text-[10px] text-white/60 transition-colors hover:text-white"
                        onClick={onNavigate}
                    >
                        {t('nav.configuration')}
                    </Link>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="w-full px-1 text-left text-[10px] font-bold uppercase tracking-widest text-white/70 transition-colors hover:text-primary"
                        onClick={onNavigate}
                    >
                        {t('common.actions.logOut')}
                    </Link>
                </div>
            </aside>
        </>
    );
}
