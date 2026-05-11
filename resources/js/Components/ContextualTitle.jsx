import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { usePage } from '@inertiajs/react';

const CONTEXT_DOT_STYLES = {
    moeve: 'bg-client-moeve',
    repsol: 'bg-client-repsol',
    otros: 'bg-client-others',
    todos: 'bg-secondary',
};

export default function ContextualTitle({
    title,
    as: Tag = 'h1',
    className = '',
    titleClassName = '',
    contextClassName = '',
    compact = false,
}) {
    const { t } = useI18n();
    const { auth } = usePage().props;
    const { workspaceContext } = useTheme();
    const resolvedContext = auth?.user?.active_context?.workspace_key ?? workspaceContext;
    const dotClass = CONTEXT_DOT_STYLES[resolvedContext] ?? CONTEXT_DOT_STYLES.otros;

    return (
        <Tag className={`flex min-w-0 flex-wrap items-baseline gap-x-2 gap-y-1 ${className}`.trim()}>
            <span className={`min-w-0 truncate ${titleClassName}`.trim()}>{title}</span>
            <span
                className={`shrink-0 rounded-full ${compact ? 'mt-[1px] h-2.5 w-2.5' : 'mt-[1px] h-2 w-2'} ${dotClass}`}
                aria-hidden
            />
            <span
                className={`truncate font-semibold text-text-main/72 ${
                    compact ? 'text-[11px] tracking-[0.04em]' : 'text-sm tracking-[0.02em]'
                } ${contextClassName}`.trim()}
            >
                {t(`workspaceContexts.${resolvedContext}`)}
            </span>
        </Tag>
    );
}
