import { useI18n } from '@/i18n';

export default function LanguageSelector({ compact = false, className = '' }) {
    const { locale, setLocale, supportedLocales, t } = useI18n();

    return (
        <div className={`flex items-center gap-2 ${className}`.trim()}>
            {!compact && (
                <span className="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                    {t('common.language')}
                </span>
            )}

            <div className="flex items-center rounded-md border border-border bg-surface p-0.5">
                {supportedLocales.map((code) => {
                    const isActive = locale === code;

                    return (
                        <button
                            key={code}
                            type="button"
                            onClick={() => setLocale(code)}
                            className={`rounded px-2 py-1 text-[11px] font-semibold uppercase tracking-wide transition ${
                                isActive
                                    ? 'bg-[var(--ciete-red)] text-white'
                                    : 'text-text-muted hover:bg-surface-2'
                            }`}
                            aria-pressed={isActive}
                            aria-label={`${t('common.language')}: ${t(`languages.${code}`)}`}
                        >
                            {t(`languages.${code}`)}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
