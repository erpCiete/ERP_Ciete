const ESTADOS = {
    borrador: {
        label: 'Borrador',
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    },
    en_curso: {
        label: 'En curso',
        bg: 'var(--color-state-progress-bg)',
        text: 'var(--color-state-progress-text)',
        dot: 'var(--color-state-progress-dot)',
    },
    terminado: {
        label: 'Terminado',
        bg: 'var(--color-state-done-bg)',
        text: 'var(--color-state-done-text)',
        dot: 'var(--color-state-done-dot)',
    },
    cerrado: {
        label: 'Cerrado',
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    },
    cancelado: {
        label: 'Cancelado',
        bg: 'var(--color-state-blocked-bg)',
        text: 'var(--color-state-blocked-text)',
        dot: 'var(--color-state-blocked-dot)',
    },
};

export default function BadgeTrabajo({ estado }) {
    const config = ESTADOS[estado?.toLowerCase()] ?? {
        label: estado ?? '—',
        bg: 'var(--color-state-closed-bg)',
        text: 'var(--color-state-closed-text)',
        dot: 'var(--color-state-closed-dot)',
    };

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
            style={{ backgroundColor: config.bg, color: config.text }}
        >
            <span className="h-1.5 w-1.5 shrink-0 rounded-full" style={{ backgroundColor: config.dot }} />
            {config.label}
        </span>
    );
}
