import { useEffect, useState } from 'react';
import { useI18n } from '@/i18n';

export const EMPTY_ITEM = {
    id_pedido_item: null,
    id_tarifario_linea: null,
    codigo_servicio: '',
    numero_tarifa: '',
    descripcion_servicio: '',
    cantidad: 1,
    precio_unitario: 0,
    total_linea: 0,
};

export function formatEur(value) {
    return Number(value ?? 0).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' €';
}

function rowKeyFor(item, index) {
    return String(item.id_pedido_item ?? `new-${index}`);
}

function buildTarifaOptionLabel(linea) {
    const descripcion = linea.actuacion || linea.descripcion || 'Sin descripción';
    const unidad = linea.unidad_abreviatura || linea.unidad_nombre || 'sin unidad';
    const precio = linea.tarifa_aplicada ?? linea.tarifa_base ?? 0;

    return `${linea.codigo_tarifa} · ${descripcion} · ${unidad} · ${formatEur(precio)}`;
}

function matchesTarifaSearch(linea, query) {
    const needle = query.trim().toLowerCase();

    if (!needle) {
        return true;
    }

    return [
        linea.codigo_tarifa,
        linea.actuacion,
        linea.descripcion,
        linea.unidad_nombre,
        linea.unidad_abreviatura,
    ]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(needle));
}

export default function ItemsTable({ items = [], onChange, errors = {}, disabled = false, tarifarioLineas = [] }) {
    const { t } = useI18n();
    const hasTarifarioLineas = tarifarioLineas.length > 0;
    const [searchByRow, setSearchByRow] = useState({});
    const [openRowKey, setOpenRowKey] = useState(null);
    const selectionSignature = items
        .map((item, index) => `${rowKeyFor(item, index)}:${item.id_tarifario_linea ?? ''}`)
        .join('|');

    useEffect(() => {
        setSearchByRow((prev) => {
            const next = {};

            items.forEach((item, index) => {
                const rowKey = rowKeyFor(item, index);
                const selected = tarifarioLineas.find((linea) => String(linea.id_tarifario_linea) === String(item.id_tarifario_linea));

                next[rowKey] = selected ? buildTarifaOptionLabel(selected) : '';
            });

            return JSON.stringify(prev) === JSON.stringify(next) ? prev : next;
        });
    }, [items, selectionSignature, tarifarioLineas]);

    const actualizarLinea = (index, campo, valor) => {
        onChange((prev) => prev.map((item, i) => {
            if (i !== index) return item;

            const actualizado = { ...item, [campo]: valor };
            actualizado.total_linea = Number(actualizado.cantidad) * Number(actualizado.precio_unitario);
            return actualizado;
        }));
    };

    const seleccionarTarifa = (index, idTarifarioLinea) => {
        const selected = tarifarioLineas.find((linea) => String(linea.id_tarifario_linea) === String(idTarifarioLinea));
        const rowKey = rowKeyFor(items[index] ?? {}, index);

        onChange((prev) => prev.map((item, i) => {
            if (i !== index) return item;

            if (!selected) {
                return {
                    ...item,
                    id_tarifario_linea: null,
                    codigo_servicio: '',
                    numero_tarifa: '',
                    descripcion_servicio: '',
                    precio_unitario: 0,
                    total_linea: 0,
                };
            }

            const cantidad = Number(item.cantidad) || 1;
            const precio = Number(selected.tarifa_aplicada ?? selected.tarifa_base ?? 0);

            return {
                ...item,
                id_tarifario_linea: selected.id_tarifario_linea,
                codigo_servicio: selected.codigo_tarifa ?? '',
                numero_tarifa: selected.codigo_tarifa ?? '',
                descripcion_servicio: selected.actuacion ?? selected.descripcion ?? '',
                precio_unitario: precio,
                total_linea: cantidad * precio,
            };
        }));

        setSearchByRow((prev) => ({
            ...prev,
            [rowKey]: selected ? buildTarifaOptionLabel(selected) : '',
        }));
        setOpenRowKey(null);
    };

    const anadirLinea = () => {
        onChange((prev) => [...prev, { ...EMPTY_ITEM }]);
    };

    const eliminarLinea = (index) => {
        onChange((prev) => prev.filter((_, i) => i !== index));
    };

    const totalPedido = items.reduce((sum, item) => sum + (Number(item.total_linea) || 0), 0);
    const errorGeneral = errors.items ?? errors.lineas ?? '';

    return (
        <div className="space-y-3">
            <p className="text-xs text-text-hint">
                {t('help.sections.mobile.tablesNote') ?? 'En pantallas pequeñas esta tabla se desplaza horizontalmente dentro del bloque.'}
            </p>

            <div className="overflow-x-auto rounded-xl border border-border overscroll-x-contain">
                <table className="min-w-[820px] w-full divide-y divide-border text-sm">
                    <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                        <tr>
                            <th className="px-3 py-2 w-72">Tarifa / código</th>
                            <th className="px-3 py-2">{t('pedidos.fields.descripcionServicio') ?? 'Descripción'}</th>
                            <th className="px-3 py-2 w-28 text-right">{t('pedidos.fields.cantidad') ?? 'Cantidad'}</th>
                            <th className="px-3 py-2 w-32 text-right">{t('pedidos.fields.precioUnitario') ?? 'Precio unit.'}</th>
                            <th className="px-3 py-2 w-32 text-right">{t('pedidos.fields.totalLinea') ?? 'Total línea'}</th>
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
                                <td colSpan={disabled ? 5 : 6} className="px-3 py-6 text-center text-sm text-text-hint">
                                    {t('pedidos.items.empty') ?? 'No hay líneas añadidas.'}
                                </td>
                            </tr>
                        )}

                        {items.map((item, index) => {
                            const errLinea = errors[`items.${index}`] ?? {};
                            const fieldError = (field) => errLinea[field] ?? errors[`items.${index}.${field}`];
                            const isFacturado = Boolean(item.esta_facturado || Number(item.factura_items_count ?? 0) > 0);
                            const selectedTarifa = hasTarifarioLineas
                                ? tarifarioLineas.find((linea) => String(linea.id_tarifario_linea) === String(item.id_tarifario_linea))
                                : null;
                            const rowKey = rowKeyFor(item, index);
                            const searchValue = searchByRow[rowKey] ?? '';
                            const filteredOptions = hasTarifarioLineas
                                ? tarifarioLineas.filter((linea) => matchesTarifaSearch(linea, searchValue)).slice(0, 8)
                                : [];

                            return (
                                <tr key={item.id_pedido_item ?? index} className="group">
                                    <td className="px-3 py-2 align-top">
                                        {hasTarifarioLineas ? (
                                            <div className="relative">
                                                <input
                                                    type="search"
                                                    value={searchValue}
                                                    disabled={disabled || isFacturado}
                                                    onFocus={() => setOpenRowKey(rowKey)}
                                                    onBlur={() => {
                                                        window.setTimeout(() => {
                                                            setOpenRowKey((current) => (current === rowKey ? null : current));
                                                        }, 120);
                                                    }}
                                                    onChange={(e) => {
                                                        const value = e.target.value;
                                                        setSearchByRow((prev) => ({ ...prev, [rowKey]: value }));
                                                        setOpenRowKey(rowKey);

                                                        if (value.trim() === '') {
                                                            seleccionarTarifa(index, '');
                                                        }
                                                    }}
                                                    placeholder="Busca por código o descripción"
                                                    className={`w-full rounded-md border px-2 py-1.5 text-sm bg-surface text-text-main
                                                        focus:outline-none focus:ring-1 disabled:bg-surface-2 disabled:text-text-hint
                                                        ${fieldError('id_tarifario_linea') || fieldError('codigo_servicio')
                                                            ? 'border-red-400 focus:ring-red-300'
                                                            : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                                />

                                                {openRowKey === rowKey && !disabled && !isFacturado && (
                                                    <div className="absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-xl border border-border bg-surface shadow-lg">
                                                        {filteredOptions.length === 0 ? (
                                                            <div className="px-3 py-2 text-xs text-text-hint">
                                                                No hay líneas que coincidan con la búsqueda.
                                                            </div>
                                                        ) : (
                                                            filteredOptions.map((linea) => {
                                                                const unidad = linea.unidad_abreviatura || linea.unidad_nombre || 'sin unidad';
                                                                const precio = linea.tarifa_aplicada ?? linea.tarifa_base ?? 0;

                                                                return (
                                                                    <button
                                                                        key={linea.id_tarifario_linea}
                                                                        type="button"
                                                                        onMouseDown={(e) => {
                                                                            e.preventDefault();
                                                                            seleccionarTarifa(index, linea.id_tarifario_linea);
                                                                        }}
                                                                        className="flex w-full flex-col gap-1 border-b border-border px-3 py-2 text-left last:border-b-0 hover:bg-surface-2"
                                                                    >
                                                                        <span className="text-xs font-semibold text-text-main">
                                                                            {linea.codigo_tarifa} · {linea.actuacion || linea.descripcion}
                                                                        </span>
                                                                        <span className="text-[11px] text-text-muted">
                                                                            {unidad} · {formatEur(precio)}
                                                                        </span>
                                                                    </button>
                                                                );
                                                            })
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        ) : (
                                            <input
                                                type="text"
                                                value={item.codigo_servicio}
                                                disabled={disabled}
                                                onChange={(e) => actualizarLinea(index, 'codigo_servicio', e.target.value)}
                                                placeholder="G-1"
                                                className={`w-full rounded-md border px-2 py-1.5 text-sm bg-surface text-text-main
                                                    focus:outline-none focus:ring-1 disabled:bg-surface-2 disabled:text-text-hint
                                                    ${fieldError('codigo_servicio')
                                                        ? 'border-red-400 focus:ring-red-300'
                                                        : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                            />
                                        )}

                                        {fieldError('id_tarifario_linea') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('id_tarifario_linea')}</p>
                                        )}
                                        {fieldError('codigo_servicio') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('codigo_servicio')}</p>
                                        )}
                                        {hasTarifarioLineas && selectedTarifa && (
                                            <p className="mt-0.5 text-[10px] text-text-hint">
                                                {selectedTarifa.unidad_abreviatura || selectedTarifa.unidad_nombre || 'Sin unidad'} · {formatEur(selectedTarifa.tarifa_aplicada ?? selectedTarifa.tarifa_base ?? 0)}
                                            </p>
                                        )}
                                        {hasTarifarioLineas && !selectedTarifa && !fieldError('id_tarifario_linea') && (
                                            <p className="mt-0.5 text-[10px] text-text-hint">
                                                Busca por código o descripción para cargar concepto, unidad y precio.
                                            </p>
                                        )}
                                    </td>

                                    <td className="px-3 py-2 align-top">
                                        <input
                                            type="text"
                                            value={item.descripcion_servicio}
                                            disabled={disabled || hasTarifarioLineas}
                                            onChange={(e) => actualizarLinea(index, 'descripcion_servicio', e.target.value)}
                                            placeholder="Descripción del servicio..."
                                            className={`w-full rounded-md border px-2 py-1.5 text-sm bg-surface text-text-main
                                                focus:outline-none focus:ring-1 disabled:bg-surface-2 disabled:text-text-hint
                                                ${fieldError('descripcion_servicio')
                                                    ? 'border-red-400 focus:ring-red-300'
                                                    : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                        />
                                        {fieldError('descripcion_servicio') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('descripcion_servicio')}</p>
                                        )}
                                    </td>

                                    <td className="px-3 py-2 align-top">
                                        <input
                                            type="number"
                                            min="0.001"
                                            step="0.001"
                                            value={item.cantidad}
                                            disabled={disabled}
                                            onChange={(e) => actualizarLinea(index, 'cantidad', e.target.value)}
                                            className={`w-full rounded-md border px-2 py-1.5 text-right text-sm bg-surface text-text-main
                                                focus:outline-none focus:ring-1 disabled:bg-surface-2 disabled:text-text-hint
                                                ${fieldError('cantidad')
                                                    ? 'border-red-400 focus:ring-red-300'
                                                    : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                        />
                                        {fieldError('cantidad') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('cantidad')}</p>
                                        )}
                                    </td>

                                    <td className="px-3 py-2 align-top">
                                        <div className="relative">
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={item.precio_unitario}
                                                disabled={disabled || hasTarifarioLineas}
                                                onChange={(e) => actualizarLinea(index, 'precio_unitario', e.target.value)}
                                                className={`w-full rounded-md border px-2 py-1.5 pr-6 text-right text-sm bg-surface text-text-main
                                                    focus:outline-none focus:ring-1 disabled:bg-surface-2 disabled:text-text-hint
                                                    ${fieldError('precio_unitario')
                                                        ? 'border-red-400 focus:ring-red-300'
                                                        : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`}
                                            />
                                            <span className="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-xs text-text-hint">
                                                €
                                            </span>
                                        </div>
                                        {fieldError('precio_unitario') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('precio_unitario')}</p>
                                        )}
                                    </td>

                                    <td className="px-3 py-2 align-top text-right">
                                        <span className="block rounded-md border border-transparent bg-surface-2 px-2 py-1.5 text-sm font-medium text-text-main">
                                            {formatEur(item.total_linea)}
                                        </span>
                                        {fieldError('total_linea') && (
                                            <p className="mt-0.5 text-[10px] text-red-600">{fieldError('total_linea')}</p>
                                        )}
                                    </td>

                                    {!disabled && (
                                        <td className="px-3 py-2 align-top text-center">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    if (!isFacturado) eliminarLinea(index);
                                                }}
                                                disabled={isFacturado}
                                                title={isFacturado
                                                    ? 'Esta línea ya está vinculada a factura y no se puede eliminar.'
                                                    : (t('pedidos.items.removeLine') ?? 'Eliminar línea')}
                                                className="rounded p-1 text-text-hint transition hover:bg-red-50 hover:text-(--ciete-red) disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-text-hint"
                                            >
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

                    {items.length > 0 && (
                        <tfoot className="border-t border-border bg-surface-2">
                            <tr>
                                <td colSpan={4} className="px-3 py-2 text-right text-sm font-semibold text-text-main">
                                    {t('pedidos.items.total') ?? 'Total pedido'}
                                </td>
                                <td className="px-3 py-2 text-right">
                                    <span className="text-sm font-bold text-(--ciete-red)">{formatEur(totalPedido)}</span>
                                </td>
                                {!disabled && <td />}
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            {errorGeneral && (
                <p className="text-xs font-medium text-red-600">{errorGeneral}</p>
            )}

            {!disabled && (
                <button
                    type="button"
                    onClick={anadirLinea}
                    className="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-dashed border-border
                               px-4 py-2 text-sm font-medium text-text-hint transition
                               hover:border-(--ciete-red) hover:text-(--ciete-red) sm:w-auto"
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
