import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import MobilePreferencesDrawer from '@/Components/MobilePreferencesDrawer';
import MobileSidebarDrawer from '@/Components/MobileSidebarDrawer';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildSidebarSections, buildTopNavbarItems } from '@/navigation/sidebar';
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
            <h2 className="truncate py-1 text-[14px] font-black uppercase tracking-tight text-text-main">{header}</h2>
        ) : (
            header
        );

    return (
        <>
            <header className="sticky top-0 z-40 flex min-h-13 w-full items-center border-b border-border bg-surface px-3 shadow-sm md:min-h-16 md:px-8">
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
                            <span className="inline-flex flex-col gap-0.75">
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                                <span className="h-0.5 w-4 rounded-sm bg-text-main" />
                            </span>
                        </button>

                        <div className="hidden items-center gap-4 md:flex">
                            <nav className="flex items-center text-[10px] font-bold uppercase tracking-widest sm:text-[11px]">
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
