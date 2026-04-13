const KNOWN_CLIENTS = {
    repsol: 'Repsol',
    moeve: 'Moeve',
    bp: 'BP',
    galp: 'Galp',
};

function resolveClient(value) {
    const normalized = String(value || '').trim().toLowerCase();

    for (const client of Object.keys(KNOWN_CLIENTS)) {
        if (normalized.includes(client)) {
            return client;
        }
    }

    return null;
}

export default function BadgeCliente({ cliente, label }) {
    const resolvedClient = resolveClient(cliente);
    const text = label || (resolvedClient ? KNOWN_CLIENTS[resolvedClient] : cliente || 'N/A');

    if (!resolvedClient) {
        return (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-surface-2 px-2.5 py-0.5 text-xs font-semibold text-text-muted">
                <span className="h-1.5 w-1.5 rounded-full bg-border-heavy" />
                {text}
            </span>
        );
    }

    const color = `var(--color-client-${resolvedClient})`;

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
            style={{
                backgroundColor: `color-mix(in srgb, ${color} 14%, transparent)`,
                color,
            }}
        >
            <span className="h-1.5 w-1.5 rounded-full" style={{ backgroundColor: color }} />
            {text}
        </span>
    );
}
