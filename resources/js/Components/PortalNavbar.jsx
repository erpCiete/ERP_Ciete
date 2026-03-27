import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';

export default function PortalNavbar({ user = null, canLogin = true }) {
    const { t } = useI18n();

    const navItems = user
        ? [
              { key: 'nav.home', href: route('index') },
              { key: 'nav.dashboard', href: route('dashboard') },
              ...(user.is_admin
                  ? [{ key: 'nav.adminPanel', href: route('admin.dashboard') }]
                  : []),
          ]
        : [
              ...(canLogin ? [{ key: 'nav.login', href: route('login') }] : []),
          ];

    return (
        <header className="ciete-nav">
            <nav className="mx-auto flex h-12 w-full max-w-6xl items-center justify-between px-6">
                <Link href={route('index')} className="flex items-center gap-2.5">
                    {/* Logo compartido: evitar variantes distintas en navbar. */}
                    <CieteMark className="w-8" />
                    <span className="text-sm font-semibold tracking-wide text-[var(--ciete-slate)]">
                        {t('nav.brand')}
                    </span>
                </Link>

                <div className="flex items-center gap-4 text-sm">
                    {navItems.map((item, index) => (
                        <div key={item.key} className="flex items-center gap-2">
                            {index > 0 && <span className="text-text-hint">|</span>}
                            <Link href={item.href} className="ciete-link">
                                {t(item.key)}
                            </Link>
                        </div>
                    ))}
                    <GlobalPreferenceSelectors compact />
                </div>
            </nav>
        </header>
    );
}
