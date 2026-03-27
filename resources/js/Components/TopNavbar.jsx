import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import MobilePreferencesDrawer from '@/Components/MobilePreferencesDrawer';
import MobileSidebarDrawer from '@/Components/MobileSidebarDrawer';
import { useI18n } from '@/i18n';
import { Link, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

function getFocusable(container) {
    if (!container) return [];

    return Array.from(
        container.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )
    ).filter((el) => !el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
}

export default function TopNavbar({ header }) {
    const { t } = useI18n();
    const { auth } = usePage().props;
    const user = auth.user;

    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [isPrefsOpen, setIsPrefsOpen] = useState(false);

    const sidebarTriggerRef = useRef(null);
    const prefsTriggerRef = useRef(null);
    const sidebarDrawerRef = useRef(null);
    const prefsDrawerRef = useRef(null);

    const sidebarDrawerId = 'mobile-sidebar-drawer';
    const prefsDrawerId = 'mobile-prefs-drawer';

    const navItems = [
        { key: 'nav.home', href: route('index'), active: route().current('index') },
        ...(user?.is_admin
            ? [{ key: 'nav.adminPanel', href: route('admin.dashboard'), active: route().current('admin.dashboard') }]
            : []),
    ];

    const topNavLinkClass = (active) =>
        `relative inline-flex items-center whitespace-nowrap pb-[3px] transition-colors duration-200 focus-visible:outline-none after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:origin-left after:bg-[var(--ciete-red)] after:content-[''] after:transition-transform after:duration-200 ${
            active
                ? 'text-[var(--ciete-red)] after:scale-x-0'
                : 'text-text-main/60 hover:text-[var(--ciete-red)] after:scale-x-0 hover:after:scale-x-100 focus-visible:text-[var(--ciete-red)] focus-visible:after:scale-x-100'
        }`;

    const sidebarSections = useMemo(() => {
        const generalItems = [
            {
                id: 'home',
                label: t('nav.home'),
                href: route('index'),
                active: route().current('index'),
            },
            {
                id: 'dashboard',
                label: t('nav.dashboard'),
                href: route('dashboard'),
                active: route().current('dashboard'),
            },
        ];

        if (user?.permission_slugs?.includes('proyectos.ver')) {
            generalItems.push({
                id: 'projects',
                label: t('nav.projects'),
                href: route('proyectos.index'),
                active: route().current('proyectos.index'),
            });
        }

        if (user?.is_admin) {
            generalItems.push({
                id: 'admin',
                label: t('nav.adminPanel'),
                href: route('admin.dashboard'),
                active: route().current('admin.dashboard'),
            });
        }

        return [
            {
                id: 'general',
                label: t('nav.groups.general'),
                items: generalItems,
            },
            {
                id: 'masters',
                label: t('nav.groups.masters'),
                items: [
                    { id: 'clients', label: t('nav.clients') },
                    { id: 'stations', label: t('nav.stations') },
                ],
            },
            {
                id: 'operations',
                label: t('nav.groups.operations'),
                items: [
                    { id: 'works', label: t('nav.works') },
                    { id: 'orders', label: t('nav.orders') },
                    { id: 'legalizations', label: t('nav.legalizations') },
                ],
            },
            {
                id: 'reports',
                label: t('nav.groups.reports'),
                items: [{ id: 'reports', label: t('nav.reports') }],
            },
        ];
    }, [t, user]);

    const closeSidebar = useCallback((restoreFocus = true) => {
        setIsSidebarOpen(false);

        if (restoreFocus) {
            requestAnimationFrame(() => sidebarTriggerRef.current?.focus());
        }
    }, []);

    const closePrefs = useCallback((restoreFocus = true) => {
        setIsPrefsOpen(false);

        if (restoreFocus) {
            requestAnimationFrame(() => prefsTriggerRef.current?.focus());
        }
    }, []);

    const openSidebar = useCallback(() => {
        closePrefs(false);
        setIsSidebarOpen(true);
    }, [closePrefs]);

    const openPrefs = useCallback(() => {
        closeSidebar(false);
        setIsPrefsOpen(true);
    }, [closeSidebar]);

    const toggleSidebar = useCallback(() => {
        if (isSidebarOpen) {
            closeSidebar(true);
            return;
        }

        openSidebar();
    }, [isSidebarOpen, closeSidebar, openSidebar]);

    const togglePrefs = useCallback(() => {
        if (isPrefsOpen) {
            closePrefs(true);
            return;
        }

        openPrefs();
    }, [isPrefsOpen, closePrefs, openPrefs]);

    const handleSidebarNavigate = useCallback(() => {
        closeSidebar(false);
    }, [closeSidebar]);

    useEffect(() => {
        if (!isSidebarOpen && !isPrefsOpen) return;

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        return () => {
            document.body.style.overflow = previousOverflow;
        };
    }, [isSidebarOpen, isPrefsOpen]);

    useEffect(() => {
        const panel = isSidebarOpen
            ? sidebarDrawerRef.current
            : isPrefsOpen
              ? prefsDrawerRef.current
              : null;

        if (!panel) return undefined;

        const focusable = getFocusable(panel);
        (focusable[0] ?? panel).focus();

        const handleKeyDown = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();

                if (isPrefsOpen) {
                    closePrefs(true);
                } else {
                    closeSidebar(true);
                }
                return;
            }

            if (event.key !== 'Tab') return;

            const nodes = getFocusable(panel);
            if (nodes.length === 0) {
                event.preventDefault();
                panel.focus();
                return;
            }

            const first = nodes[0];
            const last = nodes[nodes.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [isSidebarOpen, isPrefsOpen, closeSidebar, closePrefs]);

    const headerNode =
        typeof header === 'string' ? (
            <h2 className="truncate text-[14px] font-black uppercase tracking-tight text-text-main py-1">{header}</h2>
        ) : (
            header
        );

    return (
        <>
            <header className="sticky top-0 z-40 flex min-h-[52px] w-full items-center border-b border-border bg-surface px-3 md:min-h-[64px] md:px-8 shadow-sm">
                <div className="w-full">
                    <div className="flex items-center gap-2">
                        <button
                            ref={sidebarTriggerRef}
                            type="button"
                            onClick={toggleSidebar}
                            aria-label={
                                isSidebarOpen
                                    ? t('common.aria.closeNavigation')
                                    : t('common.aria.openNavigation')
                            }
                            aria-expanded={isSidebarOpen}
                            aria-controls={sidebarDrawerId}
                            className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-surface-2 md:hidden"
                        >
                            <CieteMark className="h-5 w-5" />
                        </button>

                        <div className="min-w-0 flex-1">
                            <div className="hidden md:block">{headerNode}</div>
                        </div>

                        <button
                            ref={prefsTriggerRef}
                            type="button"
                            onClick={togglePrefs}
                            aria-label={
                                isPrefsOpen
                                    ? t('common.aria.closePreferences')
                                    : t('common.aria.openPreferences')
                            }
                            aria-expanded={isPrefsOpen}
                            aria-controls={prefsDrawerId}
                            className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-surface-2 md:hidden"
                        >
                            <span className="inline-flex flex-col gap-[3px]">
                                <span className="h-[2px] w-4 rounded bg-text-main" />
                                <span className="h-[2px] w-4 rounded bg-text-main" />
                                <span className="h-[2px] w-4 rounded bg-text-main" />
                            </span>
                        </button>

                        <div className="hidden items-center gap-4 md:flex">
                            <nav className="flex items-center text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">
                                {navItems.map((item, index) => (
                                    <div key={item.key} className="flex items-center">
                                        {index > 0 && <span className="text-border/40 font-light select-none mx-[6px]">|</span>}
                                        <Link href={item.href} className={topNavLinkClass(item.active)}>
                                            {t(item.key)}
                                        </Link>
                                    </div>
                                ))}
                            </nav>

                            <GlobalPreferenceSelectors compact />
                        </div>
                    </div>

                </div>
            </header>

            <MobileSidebarDrawer
                open={isSidebarOpen}
                drawerId={sidebarDrawerId}
                drawerRef={sidebarDrawerRef}
                onClose={() => closeSidebar(true)}
                onNavigate={handleSidebarNavigate}
                sections={sidebarSections}
                user={user}
                t={t}
            />

            <MobilePreferencesDrawer
                open={isPrefsOpen}
                drawerId={prefsDrawerId}
                drawerRef={prefsDrawerRef}
                onClose={() => closePrefs(true)}
                user={user}
            />
        </>
    );
}
