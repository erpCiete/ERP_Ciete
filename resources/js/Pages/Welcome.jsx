import InstitutionalHero from '@/Components/WelcomeHome/InstitutionalHero';
import InstitutionalInfoBlocks from '@/Components/WelcomeHome/InstitutionalInfoBlocks';
import SupportDock from '@/Components/WelcomeHome/SupportDock';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import { motion, useReducedMotion } from 'framer-motion';

const SECTION_EASE = [0.22, 1, 0.36, 1];

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
                    <InstitutionalInfoBlocks
                        notices={internalNotices}
                        updates={systemUpdates}
                        companyNews={companyNews}
                        shouldReduceMotion={shouldReduceMotion}
                    />
                </motion.section>

                <motion.section className="w-full" {...getSectionMotion(0.22, shouldReduceMotion)}>
                    <SupportDock shouldReduceMotion={shouldReduceMotion} />
                </motion.section>
            </div>
        </AuthenticatedLayout>
    );
}
