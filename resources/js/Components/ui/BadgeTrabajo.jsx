// resources/js/Components/ui/BadgeTrabajo.jsx
// Uso: <BadgeTrabajo estado="en_curso" />
// Estados: borrador · en_curso · terminado · cerrado · cancelado

const ESTADOS = {
    borrador:  { label: 'Borrador',  bg: '#f9fafb', text: '#374151', dot: '#9ca3af' },
    en_curso:  { label: 'En curso',  bg: '#eff2ff', text: '#1e3a8a', dot: '#0033FF' },
    terminado: { label: 'Terminado', bg: '#f0fdf4', text: '#14532d', dot: '#22c55e' },
    cerrado:   { label: 'Cerrado',   bg: '#f9fafb', text: '#374151', dot: '#6b7280' },
    cancelado: { label: 'Cancelado', bg: '#fff0f0', text: '#7f1d1d', dot: '#E8000D' },
};

export default function BadgeTrabajo({ estado }) {
    const config = ESTADOS[estado?.toLowerCase()] ?? {
        label: estado ?? '—',
        bg: '#f9fafb', text: '#374151', dot: '#6b7280',
    };

    return (
        <span
            className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
            style={{ backgroundColor: config.bg, color: config.text }}
        >
            <span
                className="w-1.5 h-1.5 rounded-full flex-shrink-0"
                style={{ backgroundColor: config.dot }}
            />
            {config.label}
        </span>
    );
}
