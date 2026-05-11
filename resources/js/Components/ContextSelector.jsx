import { useI18n } from '@/i18n';
import { useTheme } from '@/theme';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

const ACTIVE_CLASS_BY_CONTEXT = {
    moeve: 'bg-client-moeve text-white hover:bg-client-moeve',
    repsol: 'bg-client-repsol text-white hover:bg-client-repsol',
    otros: 'bg-client-others text-white hover:bg-client-others',
    todos: 'bg-secondary text-white hover:bg-secondary',
};

export default function ContextSelector({ compact = false, className = '' }) {
    const { t } = useI18n();
    const { workspaceContext, setWorkspaceContext } = useTheme();
    const { auth } = usePage().props;
    const [processingValue, setProcessingValue] = useState(null);

    const activeContext = auth?.user?.active_context ?? null;
    const availableContexts = useMemo(
        () => (Array.isArray(auth?.user?.available_contexts) ? auth.user.available_contexts : []),
        [auth?.user?.available_contexts]
    );

    const resolvedActiveValue = String(activeContext?.value ?? '');
    const canSwitch = availableContexts.length > 1;

    useEffect(() => {
        if (activeContext?.workspace_key) {
            setWorkspaceContext(activeContext.workspace_key);
        }
    }, [activeContext?.workspace_key, setWorkspaceContext]);

    const resolveContextLabel = (option) => {
        const workspaceKey = option?.workspace_key;
        if (workspaceKey) {
            return t(`workspaceContexts.${workspaceKey}`);
        }

        return option?.nombre ?? '';
    };

    const switchContext = (option) => {
        const nextValue = option?.value;
        if (!nextValue || String(nextValue) === resolvedActiveValue || processingValue !== null) {
            return;
        }

        setProcessingValue(String(nextValue));

        router.post(
            route('contexto.activo.update'),
            { contexto: nextValue },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (option?.workspace_key) {
                        setWorkspaceContext(option.workspace_key);
                    }
                },
                onFinish: () => {
                    setProcessingValue(null);
                },
            }
        );
    };

    return (
        <div className={`flex min-w-0 flex-wrap items-center gap-2 ${className}`.trim()}>
            {!compact && (
                <span className="text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                    {t('common.context')}
                </span>
            )}

            {/* Usuario de contexto único: indicador fijo, sin selector */}
            {!canSwitch ? (
                <div className="flex max-w-full flex-wrap items-center rounded-md border border-border bg-surface p-0.5">
                    {availableContexts.map((option) => {
                        const code = option.workspace_key ?? workspaceContext;
                        return (
                            <span
                                key={String(option.value)}
                                className={`max-w-full rounded-sm px-2 py-1 text-[11px] font-semibold uppercase tracking-wide ${
                                    ACTIVE_CLASS_BY_CONTEXT[code] ?? ACTIVE_CLASS_BY_CONTEXT.otros
                                }`}
                                aria-label={`${t('common.context')}: ${resolveContextLabel(option)}`}
                            >
                                {resolveContextLabel(option)}
                            </span>
                        );
                    })}
                </div>
            ) : (
                /* Usuario multicontexto: pill con botones */
                <div className="flex max-w-full flex-wrap items-center rounded-md border border-border bg-surface p-0.5">
                    {availableContexts.map((option) => {
                        const optionValue = String(option.value);
                        const code = option.workspace_key ?? workspaceContext;
                        const isActive = optionValue === resolvedActiveValue;

                        return (
                            <button
                                key={optionValue}
                                type="button"
                                onClick={() => switchContext(option)}
                                disabled={processingValue !== null}
                                className={`max-w-full rounded-sm px-2 py-1 text-[11px] font-semibold uppercase tracking-wide transition ${
                                    isActive
                                        ? (ACTIVE_CLASS_BY_CONTEXT[code] ?? ACTIVE_CLASS_BY_CONTEXT.otros)
                                        : 'text-text-muted hover:bg-surface-2'
                                } ${processingValue !== null ? 'cursor-not-allowed opacity-80' : ''}`}
                                aria-pressed={isActive}
                                aria-label={`${t('common.context')}: ${resolveContextLabel(option)}`}
                            >
                                {resolveContextLabel(option)}
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
