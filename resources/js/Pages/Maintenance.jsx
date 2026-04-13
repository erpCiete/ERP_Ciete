import { Head, Link } from '@inertiajs/react';
import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { useI18n } from '@/i18n';

export default function Maintenance() {
    const { t } = useI18n();

    return (
        <>
            <Head title={t('maintenance.headTitle')} />

            <div className="relative flex min-h-screen flex-col items-center justify-center bg-surface font-sans text-text-main">
                <div className="absolute right-4 top-4 z-20">
                    <GlobalPreferenceSelectors compact />
                </div>

                <div className="mx-auto flex w-full max-w-lg flex-col items-center px-6 text-center">
                    <CieteMark className="mb-8 w-16" />

                    <div className="mb-6 inline-flex items-center gap-2 rounded-full bg-state-blocked-bg px-4 py-1.5">
                        <span className="h-2.5 w-2.5 animate-pulse rounded-full bg-state-blocked-dot" />
                        <span className="text-[10px] font-bold uppercase tracking-widest text-state-blocked-text">
                            {t('maintenance.badge')}
                        </span>
                    </div>

                    <h1 className="text-2xl font-light text-text-main">
                        {t('maintenance.title')}
                    </h1>

                    <p className="mt-4 text-sm leading-relaxed text-text-muted">
                        {t('maintenance.description')}
                    </p>

                    <div className="mt-8 rounded-xl border border-border bg-surface-2 px-6 py-5">
                        <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('maintenance.infoTitle')}
                        </p>
                        <p className="mt-2 text-xs text-text-muted">
                            {t('maintenance.infoDescription')}
                        </p>
                    </div>

                    <div className="mt-8 flex gap-3">
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="inline-flex items-center rounded-lg border border-border bg-surface px-4 py-2 text-[12px] font-medium text-text-main transition hover:bg-surface-2"
                        >
                            {t('maintenance.logout')}
                        </Link>
                    </div>

                    <p className="mt-10 text-[10px] uppercase tracking-widest text-text-hint">
                        {t('maintenance.footer')}
                    </p>
                </div>
            </div>
        </>
    );
}
