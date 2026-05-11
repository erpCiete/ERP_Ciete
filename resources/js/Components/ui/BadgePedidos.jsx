import { useI18n } from '@/i18n';

const ESTADOS = {
    pendiente: { labelKey: 'pedidos.status.pendiente', bg: '#fff7ed', text: '#9a3412', dot: '#f97316' },
    solicitado: { labelKey: 'pedidos.status.solicitado', bg: '#eff2ff', text: '#1e3a8a', dot: '#0033FF' },
    recibido: { labelKey: 'pedidos.status.recibido', bg: '#f0fdf4', text: '#14532d', dot: '#22c55e' },
    en_ejecucion: { labelKey: 'pedidos.status.enEjecucion', bg: '#ecfeff', text: '#155e75', dot: '#06b6d4' },
    facturado: { labelKey: 'pedidos.status.facturado', bg: '#f5f3ff', text: '#4c1d95', dot: '#8b5cf6' },
    cancelado: { labelKey: 'pedidos.status.cancelado', bg: '#fff0f0', text: '#7f1d1d', dot: '#E8000D' },
    anulado: { labelKey: 'pedidos.status.anulado', bg: '#fff0f0', text: '#7f1d1d', dot: '#E8000D' },
};

export default function BadgePedido({ estado, className = '' }) {
    const { t } = useI18n();
    const estadoNormalizado = estado?.toLowerCase();
    const config = ESTADOS[estadoNormalizado] || {
        label: estado || 'Desconocido',
        bg: '#f3f4f6',
        text: '#4b5563',
        dot: '#9ca3af',
    };
    const label = config.labelKey ? t(config.labelKey) : config.label;

    return (
        <div
            className={`inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium border border-black/5 ${className}`}
            style={{
                backgroundColor: config.bg,
                color: config.text,
            }}
        >
            <span
                className="h-1.5 w-1.5 rounded-full"
                style={{ backgroundColor: config.dot }}
                aria-hidden="true"
            />
            <span>{label}</span>
        </div>
    );
}
