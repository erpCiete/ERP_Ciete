import { Activity, BookOpen, LifeBuoy, MessageSquare, X } from 'lucide-react';
import { Link, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useI18n } from '@/i18n';

function QuickLink({ href, label, icon: Icon, onClick }) {
    const sharedClass =
        'flex items-center gap-2 rounded-xl border border-border bg-surface-2 px-3 py-2 text-left text-sm font-medium text-text-main transition hover:border-primary/30 hover:bg-primary/6';

    if (href) {
        return (
            <Link href={href} className={sharedClass}>
                <Icon className="h-4 w-4 text-primary" strokeWidth={1.9} aria-hidden />
                <span>{label}</span>
            </Link>
        );
    }

    return (
        <button type="button" onClick={onClick} className={sharedClass}>
            <Icon className="h-4 w-4 text-primary" strokeWidth={1.9} aria-hidden />
            <span>{label}</span>
        </button>
    );
}

export default function FloatingContextHelp() {
    const { t } = useI18n();
    const [open, setOpen] = useState(false);
    const rootRef = useRef(null);

    const actions = useMemo(
        () => [
            { id: 'messages', label: t('welcome.home.dock.messages'), icon: MessageSquare, href: route('messages.index') },
            { id: 'manual', label: t('welcome.home.dock.manual'), icon: BookOpen, href: route('help') },
            { id: 'support', label: t('welcome.home.dock.support'), icon: LifeBuoy, href: route('support') },
            { id: 'status', label: t('welcome.home.dock.status'), icon: Activity, href: route('status') },
        ],
        [t],
    );

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const handlePointerDown = (event) => {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                setOpen(false);
            }
        };

        const handleKeyDown = (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [open]);

    return (
        <div ref={rootRef} className="fixed right-4 bottom-4 z-50 md:right-6 md:bottom-6">
            {open && (
                <div className="mb-3 w-[min(92vw,360px)] rounded-3xl border border-border bg-surface/96 p-4 shadow-[0_24px_60px_-28px_rgba(15,23,42,0.45)] backdrop-blur">
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-text-hint">
                                {t('floatingHelp.eyebrow')}
                            </p>
                            <h3 className="mt-1 text-base font-semibold text-text-main">
                                {t('floatingHelp.title')}
                            </h3>
                        </div>
                        <button
                            type="button"
                            onClick={() => setOpen(false)}
                            className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-border bg-surface-2 text-text-hint transition hover:text-text-main"
                            aria-label={t('common.actions.close')}
                        >
                            <X className="h-4 w-4" strokeWidth={2} />
                        </button>
                    </div>

                    <div className="mt-4 grid gap-2 sm:grid-cols-2">
                        {actions.map((action) => (
                            <QuickLink key={action.id} href={action.href} label={action.label} icon={action.icon} />
                        ))}
                    </div>

                    <button
                        type="button"
                        onClick={() => router.visit(route('help'))}
                        className="mt-4 inline-flex items-center text-sm font-semibold text-primary transition hover:opacity-80"
                    >
                        {t('floatingHelp.more')}
                    </button>
                </div>
            )}

            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className={`group inline-flex h-14 items-center justify-center gap-2 rounded-full border px-4 text-sm font-semibold shadow-lg transition ${
                    open
                        ? 'border-primary/25 bg-surface text-text-main'
                        : 'border-primary/15 bg-primary text-white hover:bg-primary-hover'
                }`}
                aria-expanded={open}
                aria-label={t('floatingHelp.toggle')}
            >
                <LifeBuoy className="h-5 w-5" strokeWidth={2} aria-hidden />
                <span className="hidden sm:inline">{t('floatingHelp.trigger')}</span>
            </button>
        </div>
    );
}
