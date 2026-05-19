import { motion } from 'framer-motion';
import { useI18n } from '@/i18n';

function InstitutionalCard({ title, items, shouldReduceMotion = false }) {
    return (
        <motion.article
            whileHover={
                shouldReduceMotion
                    ? undefined
                    : {
                          y: -3,
                          boxShadow: '0 16px 36px -30px rgba(15, 23, 42, 0.45)',
                      }
            }
            transition={{ duration: 0.2, ease: 'easeOut' }}
            className="mx-auto w-full rounded-xl border border-border bg-surface p-6 shadow-sm"
        >
            <h3 className="text-sm font-semibold uppercase tracking-[0.12em] text-(--ciete-red)">
                {title}
            </h3>
            <ul className="mt-4 space-y-3">
                {items.slice(0, 3).map((item) => (
                    <li key={item.id ?? `${item.title}-${item.body}`} className="flex items-start gap-3">
                        <span
                            aria-hidden
                            className="mt-2 h-1.5 w-1.5 flex-none rounded-full bg-(--ciete-red)"
                        />
                        <span className="min-w-0 text-sm leading-relaxed text-text-muted">
                            {item.title ? (
                                <span className="block font-medium text-text-main">{item.title}</span>
                            ) : null}
                            {item.body ? <span className="block">{item.body}</span> : null}
                        </span>
                    </li>
                ))}
            </ul>
        </motion.article>
    );
}

export default function InstitutionalInfoBlocks({
    notices = [],
    updates = [],
    companyNews = [],
    shouldReduceMotion = false,
}) {
    const { t } = useI18n();

    return (
        <div className="grid w-full gap-5 md:grid-cols-2 xl:grid-cols-3">
            <InstitutionalCard
                title={t('welcome.home.notices.title')}
                items={notices}
                shouldReduceMotion={shouldReduceMotion}
            />
            <InstitutionalCard
                title={t('welcome.home.updates.title')}
                items={updates}
                shouldReduceMotion={shouldReduceMotion}
            />
            <InstitutionalCard
                title={t('welcome.home.companyNews.title')}
                items={companyNews}
                shouldReduceMotion={shouldReduceMotion}
            />
        </div>
    );
}
