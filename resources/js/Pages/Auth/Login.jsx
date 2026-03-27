import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import CieteMark from '@/Components/CieteMark';
import GlobalPreferenceSelectors from '@/Components/GlobalPreferenceSelectors';
import { useI18n } from '@/i18n';

export default function Login({ status }) {
    const [showPassword, setShowPassword] = useState(false);
    const { t } = useI18n();
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title={t('auth.login.headTitle')} />

            <div className="relative min-h-screen bg-surface font-sans text-text-main">
                <div className="absolute right-4 top-4 z-20">
                    <GlobalPreferenceSelectors compact />
                </div>

                <div className="flex min-h-screen flex-col lg:flex-row">
                    {/* Left Panel */}
                    <section className="relative hidden w-full lg:flex lg:w-[41%] lg:min-w-[380px] flex-col justify-between bg-secondary px-10 py-10 xl:px-16 xl:py-12">
                        <div />

                        <div className="mx-auto flex w-full max-w-[320px] flex-col items-center">
                            {/* Fuente unica del logo: no duplicar bloques manuales. */}
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

                    {/* Right Panel */}
                    <section className="flex flex-1 items-center justify-center px-6 py-10 sm:px-10 bg-surface">
                        <div className="w-full max-w-[400px]">
                            <h1 className="text-2xl font-light text-text-main">
                                {t('auth.login.title')}
                            </h1>
                            <p className="mt-2 text-[15px] text-text-muted">
                                {t('auth.login.subtitle')}
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
                                        {t('auth.login.emailLabel')}
                                    </label>

                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        autoComplete="email"
                                        autoFocus
                                        onChange={(e) => setData('email', e.target.value)}
                                        className={`w-full rounded-lg border bg-surface px-3 py-2 text-[14px] outline-none transition-all ${
                                            errors.email
                                                ? 'border-primary shadow-focus'
                                                : 'border-border hover:border-border-heavy focus:border-primary focus:shadow-focus'
                                        }`}
                                        placeholder={t('auth.login.emailPlaceholder')}
                                    />

                                    {errors.email && (
                                        <p className="mt-2 flex items-center gap-1 text-[12px] text-state-blocked-text">
                                            <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label
                                        htmlFor="password"
                                        className="block text-[11px] font-medium uppercase tracking-[0.05em] text-text-muted mb-1.5"
                                    >
                                        {t('auth.login.passwordLabel')}
                                    </label>

                                    <div className="relative">
                                        <input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            name="password"
                                            value={data.password}
                                            autoComplete="current-password"
                                            onChange={(e) => setData('password', e.target.value)}
                                            className={`w-full rounded-lg border bg-page px-3 py-2 pr-10 text-[14px] outline-none transition-all ${
                                                errors.password
                                                    ? 'border-primary shadow-focus'
                                                    : 'border-border hover:border-border-heavy focus:border-primary focus:shadow-focus'
                                            }`}
                                            placeholder="••••••••"
                                        />

                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((current) => !current)}
                                            className="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-hint transition hover:text-text-main"
                                            aria-label={
                                                showPassword
                                                    ? t('auth.login.hidePassword')
                                                    : t('auth.login.showPassword')
                                            }
                                        >
                                            <svg
                                                className="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                strokeWidth="2"
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                            >
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                    </div>

                                    {errors.password && (
                                        <p className="mt-2 flex items-center gap-1 text-[12px] text-state-blocked-text">
                                            {errors.password}
                                        </p>
                                    )}

                                    <div className="mt-3 text-right">
                                        <Link
                                            href={route('password.request')}
                                            className="text-[12px] text-text-muted transition hover:text-primary"
                                        >
                                            {t('auth.login.forgotPassword')}
                                        </Link>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full flex items-center justify-center gap-2 rounded-lg bg-primary-strong px-4 py-2.5 text-[13px] font-medium text-white transition hover:bg-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {t('auth.login.submit')}
                                    <svg
                                        className="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
