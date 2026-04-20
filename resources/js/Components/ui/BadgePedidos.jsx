import React from 'react';

const ESTADOS = {
    borrador:   { label: 'Borrador',   bg: '#f9fafb', text: '#374151', dot: '#9ca3af' },
    solicitado: { label: 'Solicitado', bg: '#eff2ff', text: '#1e3a8a', dot: '#0033FF' },
    recibido:   { label: 'Recibido',   bg: '#f0fdf4', text: '#14532d', dot: '#22c55e' },
    facturado:  { label: 'Facturado',  bg: '#f5f3ff', text: '#4c1d95', dot: '#8b5cf6' },
    cancelado:  { label: 'Cancelado',  bg: '#fff0f0', text: '#7f1d1d', dot: '#E8000D' },
};

export default function BadgePedido({ estado, className = '' }) {
    const estadoNormalizado = estado?.toLowerCase();
    const config = ESTADOS[estadoNormalizado] || {
        label: estado || 'Desconocido',
        bg: '#f3f4f6',
        text: '#4b5563',
        dot: '#9ca3af'
    };

    return (
        <div
            className={`inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium border border-black/5 ${className}`}
            style={{
                backgroundColor: config.bg,
                color: config.text
            }}
        >
            <span
                className="h-1.5 w-1.5 rounded-full"
                style={{ backgroundColor: config.dot }}
                aria-hidden="true"
            />
            <span>{config.label}</span>
        </div>
    );
}