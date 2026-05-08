import { useI18n } from '@/i18n';

const ESTADOS = {
    pendiente: { labelKey: 'facturas.status.pendiente', bg: '#fff7ed', text: '#9a3412', dot: '#f97316' },
    solicitada: { labelKey: 'facturas.status.solicitada', bg: '#f8fafc', text: '#475569', dot: '#64748b' },
    emitida: { labelKey: 'facturas.status.emitida', bg: '#eff2ff', text: '#1e3a8a', dot: '#0033FF' },
    enviada: { labelKey: 'facturas.status.enviada', bg: '#f0fdf4', text: '#14532d', dot: '#22c55e' },
    anulada: { labelKey: 'facturas.status.anulada', bg: '#fff0f0', text: '#7f1d1d', dot: '#E8000D' },
};

export default function BadgeFactura({ estado, className = '' }) {
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
