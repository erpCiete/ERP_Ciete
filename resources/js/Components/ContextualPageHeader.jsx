import ContextualTitle from '@/Components/ContextualTitle';
import { Link } from '@inertiajs/react';

export default function ContextualPageHeader({
    eyebrow = null,
    title,
    description = null,
    backHref = null,
    backLabel = 'Volver a maestros',
    actions = null,
    className = '',
    children = null,
}) {
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

            {(backHref || actions) && (
                <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-start sm:justify-end">
                    {backHref && (
                        <Link
                            href={backHref}
                            className="rounded-md border border-border px-4 py-2 text-center text-sm font-semibold text-text-main hover:bg-surface-2"
                        >
                            {backLabel}
                        </Link>
                    )}
                    {actions && <div className="w-full sm:w-auto">{actions}</div>}
                </div>
            )}
        </section>
    );
}
