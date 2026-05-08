const STATE_STYLES = {
    activo: {
        container: 'bg-state-done-bg text-state-done-text',
        dot: 'bg-state-done-dot',
    },
    inactivo: {
        container: 'bg-state-closed-bg text-state-closed-text',
        dot: 'bg-state-closed-dot',
    },
    pendiente: {
        container: 'bg-state-pending-bg text-state-pending-text',
        dot: 'bg-state-pending-dot',
    },
    en_curso: {
        container: 'bg-state-progress-bg text-state-progress-text',
        dot: 'bg-state-progress-dot',
    },
    terminado: {
        container: 'bg-state-done-bg text-state-done-text',
        dot: 'bg-state-done-dot',
    },
    facturado: {
        container: 'bg-state-billed-bg text-state-billed-text',
        dot: 'bg-state-billed-dot',
    },
    bloqueado: {
        container: 'bg-state-blocked-bg text-state-blocked-text',
        dot: 'bg-state-blocked-dot',
    },
    finalizado: {
        container: 'bg-state-closed-bg text-state-closed-text',
        dot: 'bg-state-closed-dot',
    },
};

export default function BadgeEstado({ estado, label }) {
    const normalizedState = String(estado || '').trim().toLowerCase();
    const style = STATE_STYLES[normalizedState] ?? {
        container: 'bg-state-progress-bg text-state-progress-text',
        dot: 'bg-state-progress-dot',
    };

    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ${style.container}`}>
            <span className={`h-1.5 w-1.5 rounded-full ${style.dot}`} />
            {label || estado || 'N/A'}
        </span>
    );
}
