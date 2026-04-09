import CieteParticleLogoCanvas from '@/Components/WelcomeHome/CieteParticleLogoCanvas';
import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { motion } from 'framer-motion';
import { useId } from 'react';

export default function InstitutionalHero({ shouldReduceMotion = false }) {
    const { t } = useI18n();
    const { theme } = useTheme();
    const patternId = `ciete-tech-grid-${useId().replace(/:/g, '')}`;
    const badges = [
        t('welcome.home.hero.badgeErp'),
        t('welcome.home.hero.badgeVersion'),
    ];

    return (
        <article className="group relative mx-auto w-full overflow-hidden rounded-2xl border border-border bg-surface px-6 py-8 shadow-sm sm:px-8 sm:py-10">
            <div className="pointer-events-none absolute inset-0 opacity-[0.35]">
                <svg
                    aria-hidden
                    className="h-full w-full"
                    viewBox="0 0 1200 520"
                    preserveAspectRatio="none"
                >
                    <defs>
                        <pattern id={patternId} width="72" height="72" patternUnits="userSpaceOnUse">
                            <path
                                d="M72 0H0V72"
                                fill="none"
                                stroke="var(--prueba-tech-line)"
                                strokeWidth="1.1"
                            />
                            <path
                                d="M36 0V20M36 52V72M0 36H20M52 36H72"
                                fill="none"
                                stroke="var(--prueba-tech-line-soft)"
                                strokeWidth="0.9"
                            />
                            <circle cx="36" cy="36" r="1.4" fill="var(--prueba-tech-node)" />
                        </pattern>
                    </defs>
                    <rect width="1200" height="520" fill={`url(#${patternId})`} />
                    <path
                        d="M140 116H460L525 182H860"
                        fill="none"
                        stroke="var(--prueba-tech-accent)"
                        strokeWidth="1.8"
                    />
                    <path
                        d="M210 340H420L460 302H690L735 350H995"
                        fill="none"
                        stroke="var(--prueba-tech-neutral)"
                        strokeWidth="1.6"
                    />
                </svg>
            </div>

            {!shouldReduceMotion && (
                <motion.div
                    aria-hidden
                    className="pointer-events-none absolute -left-[36%] top-0 h-full w-[28%] opacity-30"
                    style={{
                        background:
                            'linear-gradient(90deg, transparent 0%, var(--prueba-hero-shine) 48%, transparent 100%)',
                    }}
                    animate={{ x: ['0%', '420%'] }}
                    transition={{
                        repeat: Infinity,
                        repeatDelay: 8.5,
                        duration: 2.3,
                        ease: 'easeInOut',
                    }}
                />
            )}

            <div className="relative z-10 grid items-center gap-8 lg:grid-cols-[1.08fr_0.92fr] lg:gap-10">
                <div>
                    <div className="mb-4 flex flex-wrap gap-2">
                        {badges.map((badge) => (
                            <span
                                key={badge}
                                className="rounded-full border border-border bg-surface-2 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-text-muted"
                            >
                                {badge}
                            </span>
                        ))}
                    </div>

                    <h1 className="text-3xl font-semibold tracking-tight text-(--ciete-slate) sm:text-4xl">
                        {t('welcome.home.hero.title')}
                    </h1>
                    <p className="mt-3 text-base font-medium text-text-main sm:text-lg">
                        {t('welcome.home.hero.subtitle')}
                    </p>
                    <p className="mt-4 max-w-xl text-sm leading-relaxed text-text-muted sm:text-[15px]">
                        {t('welcome.home.hero.description')}
                    </p>
                </div>

                <div
                    className="mx-auto w-full max-w-[380px] rounded-2xl border p-4 shadow-sm sm:p-5"
                    style={{
                        borderColor: 'var(--prueba-logo-shell-border)',
                        background:
                            'linear-gradient(180deg, var(--prueba-logo-shell-start) 0%, var(--prueba-logo-shell-end) 100%)',
                    }}
                >
                    <CieteParticleLogoCanvas
                        className="h-[260px] w-full sm:h-[300px]"
                        shouldReduceMotion={shouldReduceMotion}
                        themeKey={theme}
                    />
                </div>
            </div>
        </article>
    );
}
