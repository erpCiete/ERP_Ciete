// resources/js/Components/ui/ItemsTable.jsx
// Componente reutilizable de líneas dinámicas.
// Uso: <ItemsTable items={items} onChange={setItems} errors={lineErrors} disabled={false} />

import { useI18n } from '@/i18n';

// ─── Línea vacía por defecto ──────────────────────────────────────────────────
export const EMPTY_ITEM = {
    codigo_servicio:     '',
    descripcion_servicio:'',
    cantidad:            1,
    precio_unitario:     0,
    total_linea:         0,
};

// ─── Helper: formato € europeo ────────────────────────────────────────────────
export function formatEur(value) {
    return Number(value ?? 0).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' €';
}

// ─── Componente ───────────────────────────────────────────────────────────────
export default function ItemsTable({ items = [], onChange, errors = {}, disabled = false }) {
    const { t } = useI18n();

    // ── Actualizar un campo de una línea y recalcular total ───────────────────
    const actualizarLinea = (index, campo, valor) => {
        onChange(prev => prev.map((item, i) => {
            if (i !== index) return item;
            const actualizado = { ...item, [campo]: valor };
            // Recalcular total al cambiar cantidad o precio
            actualizado.total_linea =
                Number(actualizado.cantidad) * Number(actualizado.precio_unitario);
            return actualizado;
        }));
    };

    // ── Añadir línea vacía ────────────────────────────────────────────────────
    const añadirLinea = () => {
        onChange(prev => [...prev, { ...EMPTY_ITEM }]);
    };

    // ── Eliminar línea ────────────────────────────────────────────────────────
    const eliminarLinea = (index) => {
        onChange(prev => prev.filter((_, i) => i !== index));
    };

    // ── Total general ─────────────────────────────────────────────────────────
    const totalPedido = items.reduce((sum, item) => sum + (Number(item.total_linea) || 0), 0);

    // ── Error general de items (ej: "Al menos 1 línea") ───────────────────────
    const errorGeneral = errors['items'] ?? errors['lineas'] ?? '';

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <div className="space-y-3">

            {/* ── Tabla de líneas ───────────────────────────────────────────── */}
            <div className="overflow-x-auto rounded-xl border border-border">
                <table className="min-w-full divide-y divide-border text-sm">
                    <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                        <tr>
                            <th className="px-3 py-2 w-32">
                                {t('pedidos.fields.codigoServicio') ?? 'Código'}
                            </th>
                            <th className="px-3 py-2">
                                {t('pedidos.fields.descripcionServicio') ?? 'Descripción'}
                            </th>
                            <th className="px-3 py-2 w-24 text-right">
                                {t('pedidos.fields.cantidad') ?? 'Cantidad'}
                            </th>
                            <th className="px-3 py-2 w-32 text-right">
                                {t('pedidos.fields.precioUnitario') ?? 'Precio unit.'}
                            </th>
                            <th className="px-3 py-2 w-32 text-right">
                                {t('pedidos.fields.totalLinea') ?? 'Total línea'}
                            </th>
                            {!disabled && (
                                <th className="px-3 py-2 w-12">
                                    <span className="sr-only">Acciones</span>
                                </th>
                            )}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-border bg-surface">
                        {items.length === 0 && (
                            <tr>
                                <td
                                    colSpan={disabled ? 5 : 6}
                                    className="px-3 py-6 text-center text-text-hint text-sm"
                                >
                                    {t('pedidos.items.empty') ?? 'No hay líneas añadidas.'}
                                </td>
                            </tr>
                        )}

                        {items.map((item, index) => {
                            const errLinea = errors[`items.${index}`] ?? {};

                            return (
                                <tr key={index} className="group">
                                    {/* Código de servicio */}
                                    <td className="px-3 py-2 align-top">
                                        <input
                                            type="text"
                                            value={item.codigo_servicio}
                                            disabled={disabled}
                                            onChange={e => actualizarLinea(index, 'codigo_servicio', e.target.value)}
                                            placeholder="G-1"
                                            className={`w-full rounded-md border px-2 py-1.5 text-sm bg-surface text-text-main
                                                focus:outline-none focus:ring-1
                                                disabled:bg-surface-2 disabled:text-text-hint
                                                ${errLinea.codigo_servicio
                                                    ? 'border-red-400 focus:ring-red-300'
                                                    : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                        />
                                        {errLinea.codigo_servicio && (
                                            <p className="mt-0.5 text-[10px] text-red-600">
                                                {errLinea.codigo_servicio}
                                            </p>
                                        )}
                                    </td>

                                    {/* Descripción */}
                                    <td className="px-3 py-2 align-top">
                                        <input
                                            type="text"
                                            value={item.descripcion_servicio}
                                            disabled={disabled}
                                            onChange={e => actualizarLinea(index, 'descripcion_servicio', e.target.value)}
                                            placeholder="Descripción del servicio..."
                                            className={`w-full rounded-md border px-2 py-1.5 text-sm bg-surface text-text-main
                                                focus:outline-none focus:ring-1
                                                disabled:bg-surface-2 disabled:text-text-hint
                                                ${errLinea.descripcion_servicio
                                                    ? 'border-red-400 focus:ring-red-300'
                                                    : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                        />
                                        {errLinea.descripcion_servicio && (
                                            <p className="mt-0.5 text-[10px] text-red-600">
                                                {errLinea.descripcion_servicio}
                                            </p>
                                        )}
                                    </td>

                                    {/* Cantidad */}
                                    <td className="px-3 py-2 align-top">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={item.cantidad}
                                            disabled={disabled}
                                            onChange={e => actualizarLinea(index, 'cantidad', e.target.value)}
                                            className={`w-full rounded-md border px-2 py-1.5 text-sm text-right bg-surface text-text-main
                                                focus:outline-none focus:ring-1
                                                disabled:bg-surface-2 disabled:text-text-hint
                                                ${errLinea.cantidad
                                                    ? 'border-red-400 focus:ring-red-300'
                                                    : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                        />
                                        {errLinea.cantidad && (
                                            <p className="mt-0.5 text-[10px] text-red-600">
                                                {errLinea.cantidad}
                                            </p>
                                        )}
                                    </td>

                                    {/* Precio unitario */}
                                    <td className="px-3 py-2 align-top">
                                        <div className="relative">
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={item.precio_unitario}
                                                disabled={disabled}
                                                onChange={e => actualizarLinea(index, 'precio_unitario', e.target.value)}
                                                className={`w-full rounded-md border px-2 py-1.5 pr-6 text-sm text-right bg-surface text-text-main
                                                    focus:outline-none focus:ring-1
                                                    disabled:bg-surface-2 disabled:text-text-hint
                                                    ${errLinea.precio_unitario
                                                        ? 'border-red-400 focus:ring-red-300'
                                                        : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                            />
                                            <span className="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-xs text-text-hint">
                                                €
                                            </span>
                                        </div>
                                        {errLinea.precio_unitario && (
                                            <p className="mt-0.5 text-[10px] text-red-600">
                                                {errLinea.precio_unitario}
                                            </p>
                                        )}
                                    </td>

                                    {/* Total línea (calculado, readonly) */}
                                    <td className="px-3 py-2 align-top text-right">
                                        <span className="block rounded-md border border-transparent bg-surface-2 px-2 py-1.5 text-sm font-medium text-text-main">
                                            {formatEur(item.total_linea)}
                                        </span>
                                    </td>

                                    {/* Eliminar línea */}
                                    {!disabled && (
                                        <td className="px-3 py-2 align-top text-center">
                                            <button
                                                type="button"
                                                onClick={() => eliminarLinea(index)}
                                                title={t('pedidos.items.removeLine') ?? 'Eliminar línea'}
                                                className="rounded p-1 text-text-hint transition hover:bg-red-50 hover:text-(--ciete-red)"
                                            >
                                                {/* Icono X */}
                                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    )}
                                </tr>
                            );
                        })}
                    </tbody>

                    {/* ── Fila de total ─────────────────────────────────────── */}
                    {items.length > 0 && (
                        <tfoot className="border-t border-border bg-surface-2">
                            <tr>
                                <td colSpan={disabled ? 4 : 4} className="px-3 py-2 text-right text-sm font-semibold text-text-main">
                                    {t('pedidos.items.total') ?? 'Total pedido'}
                                </td>
                                <td className="px-3 py-2 text-right">
                                    <span className="text-sm font-bold text-(--ciete-red)">
                                        {formatEur(totalPedido)}
                                    </span>
                                </td>
                                {!disabled && <td />}
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            {/* ── Error general de items ────────────────────────────────────── */}
            {errorGeneral && (
                <p className="text-xs text-red-600 font-medium">{errorGeneral}</p>
            )}

            {/* ── Botón añadir línea ────────────────────────────────────────── */}
            {!disabled && (
                <button
                    type="button"
                    onClick={añadirLinea}
                    className="inline-flex items-center gap-1.5 rounded-md border border-dashed border-border
                               px-4 py-2 text-sm font-medium text-text-hint transition
                               hover:border-(--ciete-red) hover:text-(--ciete-red)"
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {t('pedidos.items.addLine') ?? 'Añadir línea'}
                </button>
            )}
        </div>
    );
}
