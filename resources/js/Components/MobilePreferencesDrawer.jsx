import LanguageSelector from '@/Components/LanguageSelector';
import ThemeSelector from '@/Components/ThemeSelector';
import { useI18n } from '@/i18n';

export default function MobilePreferencesDrawer({ open, drawerId, drawerRef, onClose, user }) {
    const { t } = useI18n();

    return (
        <>
            {open && (
                <button
                    type="button"
                    aria-label={t('common.aria.closePreferencesPanel')}
                    className="fixed inset-0 z-70 bg-black/45 xl:hidden"
                    onClick={onClose}
                />
            )}

            <aside
                id={drawerId}
                ref={drawerRef}
                role="dialog"
                aria-modal="true"
                aria-label={t('common.aria.preferencesPanel')}
                tabIndex={-1}
                className={`fixed inset-y-0 right-0 z-80 flex w-[min(20rem,90vw)] max-w-full flex-col bg-surface shadow-2xl transition-transform duration-200 ease-out xl:hidden ${
                    open ? 'translate-x-0' : 'translate-x-full'
                }`}
            >
                <div className="flex items-center justify-between border-b border-border px-4 py-3">
                    <div>
                        <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-text-hint">{t('common.panel')}</p>
                        <h3 className="text-sm font-semibold text-text-main">{t('common.preferences')}</h3>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md border border-border px-2.5 py-1 text-xs font-bold uppercase tracking-widest text-text-muted"
                    >
                        {t('common.actions.close')}
                    </button>
                </div>

                <div className="space-y-4 overflow-y-auto px-4 py-4">
                    <div className="rounded-xl border border-border bg-surface-2 p-3">
                        <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-text-hint">
                            {t('common.theme')}
                        </p>
                        <div className="mt-2">
                            <ThemeSelector compact />
                        </div>

                        <p className="mt-4 text-[10px] font-bold uppercase tracking-[0.14em] text-text-hint">
                            {t('common.language')}
                        </p>
                        <div className="mt-2">
                            <LanguageSelector compact />
                        </div>
                    </div>
                </div>
            </aside>
        </>
    );
}
