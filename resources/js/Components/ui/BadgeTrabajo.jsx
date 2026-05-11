import { useI18n } from '@/i18n';

const ESTADOS = {
    en_curso: {
        labelKey: 'trabajos.status.enCurso',
        bg: 'var(--color-state-progress-bg)',
        text: 'var(--color-state-progress-text)',
        dot: 'var(--color-state-progress-dot)',
    },
    terminado: {
        labelKey: 'trabajos.status.terminado',
        bg: 'var(--color-state-done-bg)',
        text: 'var(--color-state-done-text)',
        dot: 'var(--color-state-done-dot)',
    },
    pendiente_facturar: {
        labelKey: 'trabajos.status.pendienteFacturar',
        bg: 'var(--color-state-pending-bg)',
        text: 'var(--color-state-pending-text)',
        dot: 'var(--color-state-pending-dot)',
    },
    facturado: {
        labelKey: 'trabajos.status.facturado',
        bg: 'var(--color-state-billed-bg)',
        text: 'var(--color-state-billed-text)',
        dot: 'var(--color-state-billed-dot)',
    },
    finalizado: {
        labelKey: 'trabajos.status.finalizado',
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    },
    cancelado: {
        labelKey: 'trabajos.status.cancelado',
        bg: 'var(--color-state-blocked-bg)',
        text: 'var(--color-state-blocked-text)',
        dot: 'var(--color-state-blocked-dot)',
    },
};

export default function BadgeTrabajo({ estado }) {
    const { t } = useI18n();
    const config = ESTADOS[estado?.toLowerCase()] ?? {
        label: estado ?? '—',
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    };
    const label = config.labelKey ? t(config.labelKey) : config.label;

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
            style={{ backgroundColor: config.bg, color: config.text }}
        >
            <span className="h-1.5 w-1.5 shrink-0 rounded-full" style={{ backgroundColor: config.dot }} />
            {label}
        </span>
    );
}
