import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { getNavigationIcon } from '@/Components/navigationIcons';
import { useI18n } from '@/i18n';
import { buildTopNavbarItems } from '@/navigation/sidebar';
import { Link } from '@inertiajs/react';

export default function PortalNavbar({ user = null, canLogin = true }) {
    const { t } = useI18n();

    const navItems = user
        ? buildTopNavbarItems(t, user)
        : [
              ...(canLogin ? [{ key: 'nav.login', href: route('login') }] : []),
          ];

    return (
        <header className="ciete-nav">
            <nav className="mx-auto flex h-12 w-full max-w-6xl items-center justify-between px-6">
                <Link href={route('index')} className="flex items-center gap-2.5">
                    {/* Logo compartido: evitar variantes distintas en navbar. */}
                    <CieteMark className="w-8" />
                    <span className="text-sm font-semibold tracking-wide text-(--ciete-slate)">
                        {t('nav.brand')}
                    </span>
                </Link>

                <div className="flex items-center gap-4 text-sm">
                    {navItems.map((item, index) => {
                        const ItemIcon = getNavigationIcon(item.key);

                        return (
                            <div key={item.id ?? item.key} className="flex items-center gap-2">
                                {index > 0 && <span className="text-text-hint">|</span>}
                                <Link href={item.href} className="ciete-link">
                                    <span className="inline-flex items-center gap-1.5">
                                        {ItemIcon && <ItemIcon className="h-4 w-4 shrink-0" strokeWidth={1.9} aria-hidden />}
                                        <span>{item.label ?? t(item.key)}</span>
                                    </span>
                                </Link>
                            </div>
                        );
                    })}
                    <GlobalPreferenceSelectors compact />
                </div>
            </nav>
        </header>
    );
}
