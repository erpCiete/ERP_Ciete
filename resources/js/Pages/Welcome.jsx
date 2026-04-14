import InstitutionalHero from '@/Components/WelcomeHome/InstitutionalHero';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';
import { lazy, startTransition, Suspense, useEffect, useState } from 'react';

const InstitutionalInfoBlocks = lazy(() => import('@/Components/WelcomeHome/InstitutionalInfoBlocks'));
const SupportDock = lazy(() => import('@/Components/WelcomeHome/SupportDock'));

const SECTION_EASE = [0.22, 1, 0.36, 1];

function WelcomeCardsPlaceholder() {
    return (
        <div className="grid w-full gap-5 md:grid-cols-2 xl:grid-cols-3">
            {Array.from({ length: 3 }, (_, index) => (
                <div
                    key={index}
                    className="mx-auto min-h-46 w-full rounded-xl border border-border bg-surface p-6 shadow-sm"
                >
                    <div className="h-3 w-28 rounded-full bg-surface-2" />
                    <div className="mt-5 space-y-3">
                        <div className="h-3 w-full rounded-full bg-surface-2" />
                        <div className="h-3 w-[92%] rounded-full bg-surface-2" />
                        <div className="h-3 w-[84%] rounded-full bg-surface-2" />
                    </div>
                </div>
            ))}
        </div>
    );
}

function SupportDockPlaceholder() {
    return (
        <div className="flex w-full flex-col items-center pb-2">
            <div className="flex items-end gap-2 rounded-2xl border border-border bg-surface px-3 py-2.5 shadow-sm">
                {Array.from({ length: 5 }, (_, index) => (
                    <div
                        key={index}
                        className="h-12 w-12 rounded-xl border border-border bg-surface-2"
                    />
                ))}
            </div>
        </div>
    );
}

function useDeferredLandingSections() {
    const [showDeferredSections, setShowDeferredSections] = useState(false);

    useEffect(() => {
        let timeoutId = 0;
        let frameId = 0;
        let idleId = 0;

        const revealDeferredSections = () => {
            startTransition(() => {
                setShowDeferredSections(true);
            });
        };

        if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
            idleId = window.requestIdleCallback(revealDeferredSections, { timeout: 600 });

            return () => {
                window.cancelIdleCallback(idleId);
            };
        }

        frameId = window.requestAnimationFrame(() => {
            timeoutId = window.setTimeout(revealDeferredSections, 0);
        });

        return () => {
            window.cancelAnimationFrame(frameId);
            window.clearTimeout(timeoutId);
        };
    }, []);

    return showDeferredSections;
}

function getSectionMotion(delay, shouldReduceMotion) {
    if (shouldReduceMotion) {
        return {
            initial: false,
            animate: { opacity: 1, y: 0 },
        };
    }

    return {
        initial: { opacity: 0, y: 18 },
        animate: { opacity: 1, y: 0 },
        transition: {
            duration: 0.58,
            delay,
            ease: SECTION_EASE,
        },
    };
}

export default function Welcome() {
    const shouldReduceMotion = useReducedMotion();
    const { t } = useI18n();
    const showDeferredSections = useDeferredLandingSections();

    const internalNotices = [
        t('welcome.home.notices.item1'),
        t('welcome.home.notices.item2'),
        t('welcome.home.notices.item3'),
    ];

    const systemUpdates = [
        t('welcome.home.updates.item1'),
        t('welcome.home.updates.item2'),
        t('welcome.home.updates.item3'),
    ];

    const companyNews = [
        t('welcome.home.companyNews.item1'),
        t('welcome.home.companyNews.item2'),
        t('welcome.home.companyNews.item3'),
    ];

    return (
        <AuthenticatedLayout header={t('welcome.brand')}>
            <Head title={t('welcome.home.headTitle')} />

            <div className="mx-auto flex w-full max-w-5xl flex-col items-center gap-10 pb-5 sm:gap-12">
                <motion.section className="w-full" {...getSectionMotion(0, shouldReduceMotion)}>
                    <InstitutionalHero shouldReduceMotion={shouldReduceMotion} />
                </motion.section>

                <motion.section className="w-full" {...getSectionMotion(0.12, shouldReduceMotion)}>
                    <Suspense fallback={<WelcomeCardsPlaceholder />}>
                        {showDeferredSections ? (
                            <InstitutionalInfoBlocks
                                notices={internalNotices}
                                updates={systemUpdates}
                                companyNews={companyNews}
                                shouldReduceMotion={shouldReduceMotion}
                            />
                        ) : (
                            <WelcomeCardsPlaceholder />
                        )}
                    </Suspense>
                </motion.section>

                <motion.section className="w-full" {...getSectionMotion(0.22, shouldReduceMotion)}>
                    <Suspense fallback={<SupportDockPlaceholder />}>
                        {showDeferredSections ? (
                            <SupportDock shouldReduceMotion={shouldReduceMotion} />
                        ) : (
                            <SupportDockPlaceholder />
                        )}
                    </Suspense>
                </motion.section>
            </div>
        </AuthenticatedLayout>
    );
}
