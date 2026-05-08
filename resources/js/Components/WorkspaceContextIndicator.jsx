import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { usePage } from '@inertiajs/react';

const CONTEXT_STYLES = {
    moeve: {
        dotClass: 'bg-client-moeve',
    },
    repsol: {
        dotClass: 'bg-client-repsol',
    },
    otros: {
        dotClass: 'bg-client-others',
    },
    todos: {
        dotClass: 'bg-secondary',
    },
};

export default function WorkspaceContextIndicator({ compact = false, className = '' }) {
    const { t } = useI18n();
    const { auth } = usePage().props;
    const { workspaceContext } = useTheme();
    const resolvedContext = auth?.user?.active_context?.workspace_key ?? workspaceContext;
    const style = CONTEXT_STYLES[resolvedContext] ?? CONTEXT_STYLES.otros;

    return (
        <span
            className={`inline-flex min-w-0 items-center gap-1.5 whitespace-nowrap ${className}`.trim()}
            aria-label={`${t('common.context')}: ${t(`workspaceContexts.${resolvedContext}`)}`}
        >
            <span
                className={`shrink-0 rounded-full ${compact ? 'h-1.5 w-1.5' : 'h-2 w-2'} ${style.dotClass}`}
                aria-hidden
            />
            <span
                className={`truncate font-semibold text-text-main/72 ${
                    compact ? 'text-[10px] tracking-[0.04em]' : 'text-[11px] tracking-[0.06em]'
                }`}
            >
                {t(`workspaceContexts.${resolvedContext}`)}
            </span>
        </span>
    );
}
