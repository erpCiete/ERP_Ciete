import { router } from '@inertiajs/react';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';

export default function VisualStyleSelector({ compact = false, className = '' }) {
    const { visualStyle, setVisualStyle, supportedVisualStyles } = useTheme();
    const { t } = useI18n();

    const handleStyleChange = (code) => {
        // Actualización local inmediata (optimistic UI)
        setVisualStyle(code);
        // Persistir en backend
        router.patch(route('profile.preferences'), { interface_mode: code }, { preserveScroll: true });
    };

    // ── Modo compacto: pill con dos botones igual que ThemeSelector ───────────
    if (compact) {
        return (
            <div className={`flex min-w-0 flex-wrap items-center gap-2 ${className}`.trim()}>
                <div className="flex max-w-full flex-wrap items-center rounded-md border border-border bg-surface p-0.5">
                    {supportedVisualStyles.map((code) => {
                        const isActive = visualStyle === code;
                        return (
                            <button
                                key={code}
                                type="button"
                                onClick={() => handleStyleChange(code)}
                                className={`rounded-sm px-2 py-1 text-[11px] font-semibold uppercase tracking-wide transition ${
                                    isActive
                                        ? 'bg-(--ciete-red) text-white'
                                        : 'text-text-muted hover:bg-surface-2'
                                }`}
                                aria-pressed={isActive}
                                aria-label={`${t('common.visualStyle')}: ${t(`visualStyles.${code}.shortName`)}`}
                            >
                                {t(`visualStyles.${code}.shortName`)}
                            </button>
                        );
                    })}
                </div>
            </div>
        );
    }

    // ── Modo completo: cards de perfil ────────────────────────────────────────
    return (
        <section className={`space-y-4 ${className}`.trim()} aria-labelledby="visual-style-title">
            <div>
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-text-hint">
                    {t('profile.appearance.eyebrow')}
                </p>
                <h3 id="visual-style-title" className="mt-1 text-lg font-semibold text-text-main">
                    {t('profile.appearance.title')}
                </h3>
                <p className="mt-1 max-w-2xl text-sm text-text-muted">
                    {t('profile.appearance.description')}
                </p>
            </div>

            <div className="grid gap-3 md:grid-cols-2" role="group" aria-label={t('common.visualStyle')}>
                {supportedVisualStyles.map((code) => {
                    const isActive = visualStyle === code;

                    return (
                        <button
                            key={code}
                            type="button"
                            onClick={() => handleStyleChange(code)}
                            className={`group min-h-28 rounded-lg border p-4 text-left transition ${
                                isActive
                                    ? 'border-primary bg-primary-light shadow-sm'
                                    : 'border-border bg-surface hover:border-primary/35 hover:bg-surface-2'
                            }`}
                            aria-pressed={isActive}
                        >
                            <span>
                                <span className="block text-sm font-bold text-text-main">
                                    {t(`visualStyles.${code}.name`)}
                                </span>
                                <span className="mt-1 block text-xs leading-5 text-text-muted">
                                    {t(`visualStyles.${code}.description`)}
                                </span>
                            </span>
                        </button>
                    );
                })}
            </div>
        </section>
    );
}
