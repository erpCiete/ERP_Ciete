import { Activity, BookOpen, Globe, LifeBuoy, MessageSquare } from 'lucide-react';
import { AnimatePresence, motion } from 'framer-motion';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useI18n } from '@/i18n';

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

export default function SupportDock({ shouldReduceMotion = false }) {
    const { t } = useI18n();
    const cieteWebsiteUrl = import.meta.env.VITE_CIETE_WEBSITE_URL || 'https://www.ciete.es';
    const dockRef = useRef(null);
    const itemRefs = useRef([]);

    const [hoveredItem, setHoveredItem] = useState(null);
    const [mouseX, setMouseX] = useState(null);
    const [itemCenters, setItemCenters] = useState([]);
    const dockItems = useMemo(
        () => [
            { id: 'web', label: t('welcome.home.dock.web'), icon: Globe },
            { id: 'messages', label: t('welcome.home.dock.messages'), icon: MessageSquare },
            { id: 'manual', label: t('welcome.home.dock.manual'), icon: BookOpen },
            { id: 'support', label: t('welcome.home.dock.support'), icon: LifeBuoy },
            { id: 'status', label: t('welcome.home.dock.status'), icon: Activity },
        ],
        [t],
    );

    useEffect(() => {
        const updateCenters = () => {
            const dockEl = dockRef.current;
            if (!dockEl) {
                return;
            }

            const dockRect = dockEl.getBoundingClientRect();
            const centers = dockItems.map((_, index) => {
                const itemEl = itemRefs.current[index];
                if (!itemEl) {
                    return 0;
                }

                const itemRect = itemEl.getBoundingClientRect();
                return itemRect.left - dockRect.left + itemRect.width / 2;
            });

            setItemCenters(centers);
        };

        updateCenters();
        window.addEventListener('resize', updateCenters);

        return () => {
            window.removeEventListener('resize', updateCenters);
        };
    }, [dockItems]);

    const getScaleAt = (index, itemId) => {
        if (shouldReduceMotion || mouseX === null) {
            return 1;
        }

        const center = itemCenters[index] ?? 0;
        const distance = Math.abs(mouseX - center);
        const influence = 132;
        const ratio = clamp(1 - distance / influence, 0, 1);
        const scaleFromDistance = 1 + ratio * 0.34;
        const hoverBoost = hoveredItem === itemId ? 1.58 : scaleFromDistance;

        return Math.max(scaleFromDistance, hoverBoost);
    };

    return (
        <div className="flex w-full flex-col items-center pb-2">
            <div
                ref={dockRef}
                onMouseMove={(event) => {
                    const rect = event.currentTarget.getBoundingClientRect();
                    setMouseX(event.clientX - rect.left);
                }}
                onMouseLeave={() => {
                    setMouseX(null);
                    setHoveredItem(null);
                }}
                className="flex items-end gap-2 rounded-2xl border border-border bg-surface px-3 py-2.5 shadow-sm"
            >
                {dockItems.map((item, index) => {
                    const Icon = item.icon;

                    return (
                        <div
                            key={item.id}
                            className={`relative ${
                                hoveredItem === item.id ? 'z-30' : 'z-10'
                            }`}
                        >
                            <AnimatePresence>
                                {hoveredItem === item.id && (
                                    <div className="pointer-events-none absolute left-1/2 top-[calc(100%+8px)] -translate-x-1/2">
                                        <motion.div
                                            initial={{ opacity: 0, y: -4 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            exit={{ opacity: 0, y: -4 }}
                                            transition={{ duration: 0.14, ease: 'easeOut' }}
                                            className="w-max rounded-md border border-border bg-surface px-2 py-1 text-center text-[11px] font-medium text-text-muted shadow-sm"
                                        >
                                            {item.label}
                                        </motion.div>
                                    </div>
                                )}
                            </AnimatePresence>

                            <motion.button
                                type="button"
                                ref={(element) => {
                                    itemRefs.current[index] = element;
                                }}
                                onMouseEnter={() => setHoveredItem(item.id)}
                                onFocus={() => setHoveredItem(item.id)}
                                onBlur={() => setHoveredItem(null)}
                                onClick={() => {
                                    if (item.id === 'web') {
                                        window.location.assign(cieteWebsiteUrl);
                                    }
                                }}
                                animate={{
                                    scale: getScaleAt(index, item.id),
                                    y: hoveredItem === item.id && !shouldReduceMotion ? -4 : 0,
                                }}
                                transition={{
                                    type: 'spring',
                                    stiffness: 320,
                                    damping: 22,
                                    mass: 0.36,
                                }}
                                className="flex h-12 w-12 items-center justify-center rounded-xl border border-border bg-surface-2 text-text-main transition-colors hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] focus-visible:ring-offset-2"
                                aria-label={item.label}
                            >
                                <Icon size={20} strokeWidth={1.9} />
                            </motion.button>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
