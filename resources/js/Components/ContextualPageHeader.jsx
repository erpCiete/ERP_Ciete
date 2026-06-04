import ContextualTitle from '@/Components/ContextualTitle';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import { Link } from '@inertiajs/react';

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

export default function ContextualPageHeader({
    eyebrow = null,
    title,
    description = null,
    backHref = null,
    backLabel = null,
    actions = null,
    className = '',
    children = null,
}) {
    const mastersBack = useMastersBackLink();
    const maestrosHref = hasRoute('maestros.index') ? route('maestros.index') : null;
    const shouldResolveMastersBack = Boolean(backHref && maestrosHref && backHref === maestrosHref);
    const resolvedBackHref = shouldResolveMastersBack ? mastersBack.href : backHref;
    const resolvedBackLabel = backLabel ?? (shouldResolveMastersBack ? mastersBack.label : 'Volver');

    return (
        <section className={`ciete-page-header ${className}`.trim()}>
            <div className="min-w-0">
                {eyebrow && (
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">{eyebrow}</p>
                )}
                <ContextualTitle
                    title={title}
                    className="mt-2"
                    titleClassName="text-2xl font-semibold text-text-main"
                    contextClassName="text-sm text-text-muted"
                />
                {description && <p className="mt-2 max-w-2xl text-sm text-text-muted">{description}</p>}
                {children}
            </div>

            {(resolvedBackHref || actions) && (
                <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-start sm:justify-end">
                    {resolvedBackHref && (
                        <Link
                            href={resolvedBackHref}
                            className="rounded-md border border-border px-4 py-2 text-center text-sm font-semibold text-text-main hover:bg-surface-2"
                        >
                            {resolvedBackLabel}
                        </Link>
                    )}
                    {actions && <div className="w-full sm:w-auto">{actions}</div>}
                </div>
            )}
        </section>
    );
}
