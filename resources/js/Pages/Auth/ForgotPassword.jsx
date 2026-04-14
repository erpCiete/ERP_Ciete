import { Head, Link, useForm } from '@inertiajs/react';
import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { useI18n } from '@/i18n';

export default function ForgotPassword({ status }) {
    const { t } = useI18n();

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <>
            <Head title={t('auth.forgotPassword.headTitle')} />

            <div className="relative min-h-screen bg-surface font-sans text-text-main">
                <div className="absolute right-4 top-4 z-20">
                    <GlobalPreferenceSelectors compact />
                </div>

                <div className="flex min-h-screen flex-col lg:flex-row">
                    <section className="relative hidden w-full lg:flex lg:w-[41%] lg:min-w-[380px] flex-col justify-between bg-secondary px-10 py-10 xl:px-16 xl:py-12">
                        <div />

                        <div className="mx-auto flex w-full max-w-[320px] flex-col items-center">
                            <CieteMark className="mb-8 w-16" />

                            <p className="text-[32px] font-light leading-none text-white tracking-widest">
                                {t('auth.login.leftBrand')}
                            </p>
                            <p className="mt-2 text-[10px] tracking-[0.3em] text-text-hint uppercase">
                                {t('auth.login.leftCompany')}
                            </p>
                        </div>

                        <div className="border-t border-white/10 pt-6 text-[10px] tracking-widest text-text-hint uppercase">
                            {t('auth.login.leftFooter')}
                        </div>
                    </section>

                    <section className="flex flex-1 items-center justify-center px-6 py-10 sm:px-10 bg-surface">
                        <div className="w-full max-w-[400px]">
                            <h1 className="text-2xl font-light text-text-main">
                                {t('auth.forgotPassword.title')}
                            </h1>
                            <p className="mt-2 text-[15px] text-text-muted">
                                {t('auth.forgotPassword.description')}
                            </p>

                            {status && (
                                <div className="mt-6 rounded-md border border-state-done-dot bg-state-done-bg px-3 py-2 text-sm text-state-done-text">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} className="mt-10 space-y-6">
                                <div>
                                    <label
                                        htmlFor="email"
                                        className="block text-[11px] font-medium uppercase tracking-[0.05em] text-text-muted mb-1.5"
                                    >
                                        {t('auth.forgotPassword.emailLabel')}
                                    </label>

                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        autoComplete="email"
                                        autoFocus
                                        onChange={(e) => setData('email', e.target.value)}
                                        className={`w-full rounded-lg border bg-surface px-3 py-2 text-[14px] outline-hidden transition-all ${
                                            errors.email
                                                ? 'border-primary shadow-focus'
                                                : 'border-border hover:border-border-heavy focus:border-primary focus:shadow-focus'
                                        }`}
                                        placeholder="email@ciete.es"
                                    />

                                    {errors.email && (
                                        <p className="mt-2 text-[12px] text-state-blocked-text">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full flex items-center justify-center gap-2 rounded-lg bg-primary-strong px-4 py-2.5 text-[13px] font-medium text-white transition hover:bg-primary focus:outline-hidden focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {t('auth.forgotPassword.submit')}
                                </button>

                                <div className="text-right">
                                    <Link
                                        href={route('login')}
                                        className="text-[12px] text-text-muted transition hover:text-primary"
                                    >
                                        {t('auth.forgotPassword.backToLogin')}
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
