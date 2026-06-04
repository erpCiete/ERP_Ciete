import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import MobilePreferencesDrawer from '@/Components/MobilePreferencesDrawer';
import MobileSidebarDrawer from '@/Components/MobileSidebarDrawer';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildSidebarSections, buildTopNavbarItems } from '@/navigation/sidebar';
import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

function getFocusable(container) {
    if (!container) return [];

    return Array.from(
        container.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )
    ).filter((el) => !el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
}

export default function TopNavbar({
    header,
    showDesktopSidebarToggle = false,
    isDesktopSidebarOpen = true,
    onDesktopSidebarToggle = null,
}) {
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

    const navItems = useMemo(() => buildTopNavbarItems(t, user), [t, user]);
    const sidebarSections = useMemo(() => buildSidebarSections(t, user), [t, user]);

    const topNavLinkClass = (active) =>
        `inline-flex items-center whitespace-nowrap pb-[3px] transition-colors duration-200 focus-visible:outline-hidden ${
            active
                ? 'text-(--ciete-red)'
                : 'text-text-main/60 hover:text-(--ciete-red) focus-visible:text-(--ciete-red)'
        }`;

    const topNavLinkLabelClass = (active) =>
        `relative inline-block after:absolute after:-bottom-px after:left-0 after:h-px after:w-full after:origin-left after:bg-(--ciete-red) after:content-[''] after:transition-transform after:duration-200 ${
            active ? 'after:scale-x-0' : 'after:scale-x-0 group-hover:after:scale-x-100'
        }`;

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
            <h2 className="truncate py-0.5 text-[15px] font-black uppercase tracking-[0.12em] text-text-main lg:text-[16px]">
                {header}
            </h2>
        ) : (
            header
        );

    return (
        <>
            <header className="sticky top-0 z-40 border-b bg-surface shadow-sm" style={{ borderBottomColor: 'var(--workspace-context-accent-line)' }}>
                <div className="flex min-h-13 w-full flex-wrap items-center gap-3 px-3 py-2 sm:px-4 xl:px-6 xl:py-2 2xl:h-[var(--app-shell-header-height)] 2xl:flex-nowrap 2xl:py-0 2xl:px-8">
                    <div className="flex min-w-0 flex-1 items-center gap-2">
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
                            className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-surface-2 xl:hidden"
                        >
                            <CieteMark className="h-5 w-5" />
                        </button>

                        {showDesktopSidebarToggle && (
                            <button
                                type="button"
                                onClick={onDesktopSidebarToggle}
                                aria-label={
                                    isDesktopSidebarOpen
                                        ? t('common.aria.closeNavigation')
                                        : t('common.aria.openNavigation')
                                }
                                aria-expanded={isDesktopSidebarOpen}
                                className="hidden h-9 items-center gap-2 rounded-md border border-border bg-surface-2 px-3 text-xs font-bold uppercase tracking-widest text-text-main transition hover:bg-surface xl:inline-flex"
                            >
                                <Menu className="h-4 w-4" />
                                {t('common.actions.menu')}
                            </button>
                        )}

                        <div className="min-w-0 flex-1">
                            <p className="truncate pb-0.5 text-[11px] font-black uppercase tracking-[0.14em] text-text-main/78 sm:text-[12px]">
                                {t('nav.brand')}
                            </p>
                            <div className="block">{headerNode}</div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2 xl:hidden">
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
                            className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-surface-2"
                        >
                            <span className="inline-flex flex-col gap-0.75">
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                            </span>
                        </button>
                    </div>

                    <div className="hidden min-w-0 basis-full items-start justify-between gap-3 xl:flex 2xl:min-w-0 2xl:flex-1 2xl:basis-auto 2xl:items-center 2xl:justify-end">
                        <div className="min-w-0 flex-1 overflow-x-auto overscroll-x-contain">
                            <nav className="flex min-w-max items-center justify-start text-[10px] font-bold uppercase tracking-widest sm:text-[11px] 2xl:justify-end">
                                {navItems.map((item, index) => {
                                    const ItemIcon = getNavigationIcon(item.key);

                                    return (
                                        <div key={item.id} className="flex items-center">
                                            {index > 0 && <span className="mx-1.5 select-none font-light text-border/40">|</span>}
                                            <Link href={item.href} className={`group ${topNavLinkClass(item.active)}`}>
                                                <span className="inline-flex items-center gap-1.5">
                                                    {ItemIcon && <ItemIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                                                    <span className={topNavLinkLabelClass(item.active)}>{item.label}</span>
                                                </span>
                                            </Link>
                                        </div>
                                    );
                                })}
                            </nav>
                        </div>

                        <GlobalPreferenceSelectors compact className="w-full justify-start xl:w-auto xl:justify-end" />
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
