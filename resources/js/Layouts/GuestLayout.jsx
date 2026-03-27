import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { useI18n } from '@/i18n';

export default function GuestLayout({ children }) {
    const { t } = useI18n();

    return (
        <div className="ciete-shell">
            {/* En modo invitado no exponemos navbar operativo. */}
            <CieteMark className="ciete-watermark" aria-hidden />
            <div className="relative z-10 mx-auto flex w-full max-w-6xl justify-end px-6 pt-4">
                <GlobalPreferenceSelectors compact />
            </div>

            <main className="relative z-10 mx-auto flex min-h-[calc(100vh-6rem)] w-full max-w-6xl items-center justify-center px-6 py-10">
                <div className="w-full max-w-md overflow-hidden rounded-xl border border-border bg-surface px-6 py-6 shadow-sm">
                    {children}
                </div>
            </main>

            <footer className="ciete-footer">
                {t('common.footer.projectVersion')}
            </footer>
        </div>
    );
}
