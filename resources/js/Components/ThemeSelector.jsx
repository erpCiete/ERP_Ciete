import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';

export default function ThemeSelector({ compact = false, className = '' }) {
    const { theme, setTheme, supportedThemes } = useTheme();
    const { t } = useI18n();

    return (
        <div className={`flex items-center gap-2 ${className}`.trim()}>
            {!compact && (
                <span className="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                    {t('common.theme')}
                </span>
            )}

            <div className="flex items-center rounded-md border border-border bg-surface p-0.5">
                {supportedThemes.map((code) => {
                    const isActive = theme === code;

                    return (
                        <button
                            key={code}
                            type="button"
                            onClick={() => setTheme(code)}
                            className={`rounded px-2 py-1 text-[11px] font-semibold uppercase tracking-wide transition ${
                                isActive
                                    ? 'bg-[var(--ciete-red)] text-white'
                                    : 'text-text-muted hover:bg-surface-2'
                            }`}
                            aria-pressed={isActive}
                            aria-label={`${t('common.theme')}: ${t(`themes.${code}`)}`}
                        >
                            {t(`themes.${code}`)}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
