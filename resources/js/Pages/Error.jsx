import CieteMark from '@/Components/CieteMark';
import ErrorShellLayout from '@/Layouts/ErrorShellLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';

function resolveErrorContent(t, status) {
    const key = String(status);
    const availableKeys = ['401', '403', '404', '419', '500', '503'];
    const suffix = availableKeys.includes(key) ? key : 'generic';

    return {
        title: t(`errors.statuses.${suffix}.title`),
        message: t(`errors.statuses.${suffix}.message`),
    };
}

export default function ErrorPage({ status = 500 }) {
    const { t } = useI18n();
    const { title, message } = resolveErrorContent(t, status);
    const pageTitle = `${t('errors.pageTitle')} ${status}`;

    return (
        <ErrorShellLayout header={pageTitle}>
            <Head title={pageTitle} />

            <section className="relative overflow-hidden rounded-[28px] border border-border bg-gradient-to-br from-surface via-surface to-surface-2 p-8 shadow-sm md:p-12">
                <div className="pointer-events-none absolute -left-12 top-6 h-28 w-28 rounded-full bg-primary/8 blur-3xl" />
                <div className="pointer-events-none absolute -right-12 bottom-6 h-32 w-32 rounded-full bg-accent/10 blur-3xl" />
                <div className="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />

                <div className="relative flex flex-col gap-10 lg:flex-row lg:items-center">
                    <div className="flex justify-center lg:w-[28%] lg:justify-start">
                        <div className="relative rounded-[26px] border border-border bg-surface/90 p-5 shadow-sm">
                            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(254,0,0,0.10),transparent_55%),radial-gradient(circle_at_bottom_left,rgba(0,62,255,0.10),transparent_55%)]" />
                            <CieteMark className="relative z-10 w-28 sm:w-32" />
                        </div>
                    </div>

                    <div className="max-w-3xl text-center lg:text-left">
                        <p className="text-[10px] font-bold uppercase tracking-[0.24em] text-text-hint">
                            {t('errors.label')}
                        </p>
                        <p className="mt-3 text-sm font-semibold uppercase tracking-[0.18em] text-(--ciete-red)">
                            {t('errors.codeLabel', { status })}
                        </p>
                        <h1 className="mt-4 text-3xl font-semibold tracking-tight text-text-main sm:text-4xl">
                            {title}
                        </h1>
                        <p className="mt-4 max-w-2xl text-base leading-relaxed text-text-muted sm:text-lg">
                            {message}
                        </p>
                    </div>
                </div>
            </section>
        </ErrorShellLayout>
    );
}
