import BadgeFactura from '@/Components/ui/BadgeFactura';
import PaginationControls from '@/Components/ui/PaginationControls';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useMemo, useState } from 'react';

const ESTADO_OPTIONS = ['pendiente', 'solicitada', 'emitida', 'enviada', 'anulada'];

const ESTADO_LABEL = {
    pendiente: 'Pendiente',
    solicitada: 'Solicitada',
    emitida:   'Emitida',
    enviada:   'Enviada',
    anulada:   'Anulada',
};

function fmt(val) {
    if (val === null || val === undefined) return '—';
    return val;
}

function fmtMoney(val) {
    if (val === null || val === undefined) return '—';
    const n = Number(val);
    if (isNaN(n)) return '—';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(n);
}

function fmtDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-ES');
}

function todayIso() {
    return new Date().toISOString().split('T')[0];
}

function emptyNewFactura(contextId = '') {
    return {
        id_contexto: contextId ? String(contextId) : '',
        id_contrato: '',
        id_trabajo: '',
        id_pedido: '',
        numero_factura: '',
        fecha_emision: todayIso(),
        estado: 'pendiente',
        total: '',
        total_manual: false,
        items: [],
        id_empresa_facturadora: '',
        factura_ccp: '',
        sociedad: '',
        orden_factura: '',
        autofactura: false,
    };
}

function includesText(query, ...values) {
    const normalizedQuery = String(query ?? '').trim().toLowerCase();

    if (!normalizedQuery) return true;

    return values
        .filter((value) => value !== null && value !== undefined && value !== '')
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery);
}

function formatContractLabel(contract) {
    const code = String(contract?.codigo_contrato ?? '').trim();
    const name = String(contract?.nombre_contrato ?? '').trim();

    return [code || `Contrato ${contract?.id_contrato ?? '—'}`, name].filter(Boolean).join(' · ');
}

function formatTrabajoLabel(trabajo) {
    return [
        `Trabajo ${formatWorkNumber(trabajo?.numero_trabajo_visible)}`,
        trabajo?.descripcion_trabajo,
        trabajo?.codigo_estacion,
    ].filter(Boolean).join(' · ');
}

function formatPedidoLabel(pedido) {
    return [
        `Pedido ${pedido?.numero_pedido ?? '-'}`,
        `Trabajo ${formatWorkNumber(pedido?.numero_trabajo_visible)}`,
    ].filter(Boolean).join(' · ');
}

function SearchableSelectField({
    label,
    searchValue,
    onSearchChange,
    searchPlaceholder,
    value,
    onChange,
    selectPlaceholder,
    options = [],
    disabled = false,
    error = '',
    helper = '',
    countLabel = '',
}) {
    return (
        <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
            <span>{label}</span>
            <input
                type="search"
                value={searchValue}
                onChange={(event) => onSearchChange(event.target.value)}
                placeholder={searchPlaceholder}
                disabled={disabled}
                className="mt-1 h-9 w-full rounded border border-border bg-surface px-2 text-xs font-normal normal-case tracking-normal text-text-main disabled:opacity-60"
            />
            <select
                value={value}
                onChange={(event) => onChange(event.target.value)}
                disabled={disabled}
                className="mt-2 h-10 w-full rounded border border-border bg-surface px-2 text-xs font-normal normal-case tracking-normal text-text-main disabled:opacity-60"
            >
                <option value="">{selectPlaceholder}</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {(helper || countLabel) && (
                <span className="mt-1 block text-[11px] font-normal normal-case tracking-normal text-text-muted">
                    {[helper, countLabel].filter(Boolean).join(' · ')}
                </span>
            )}
            {error && <span className="mt-1 block normal-case tracking-normal text-red-600">{error}</span>}
        </label>
    );
}

function toNumber(value, fallback = 0) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function formatDecimal(value, decimals = 2) {
    const number = toNumber(value);

    return number.toFixed(decimals);
}

function formatWorkNumber(value) {
    if (value === null || value === undefined || value === '') return '-';
    const text = String(value);

    return /^\d+$/.test(text) ? text.padStart(4, '0') : text;
}

function contractIdsForEmpresa(empresa) {
    const ids = Array.isArray(empresa?.id_contratos)
        ? empresa.id_contratos
        : empresa?.id_contrato
          ? [empresa.id_contrato]
          : [];

    return ids.map((id) => Number(id)).filter((id) => Number.isFinite(id));
}

function pedidoItemLabel(item) {
    return [
        `Pedido ${item.numero_pedido ?? '-'}`,
        `Trabajo ${item.numero_trabajo_visible ?? item.numero_trabajo_operativo ?? item.numero_trabajo ?? '-'}`,
        item.codigo_servicio || item.descripcion_servicio || 'Item',
        fmtMoney(item.importe_pendiente),
    ].join(' · ');
}

function availableDisplay(item) {
    return {
        id_contexto: item.id_contexto,
        id_pedido: item.id_pedido,
        numero_pedido: item.numero_pedido ?? '-',
        id_trabajo: item.id_trabajo,
        numero_trabajo: item.numero_trabajo ?? '-',
        numero_trabajo_operativo: item.numero_trabajo_operativo ?? null,
        numero_trabajo_visible: item.numero_trabajo_visible ?? item.numero_trabajo_operativo ?? item.numero_trabajo ?? '-',
        descripcion_trabajo: item.descripcion_trabajo ?? '',
        codigo_estacion: item.codigo_estacion ?? '',
        id_contrato: item.id_contrato ?? null,
        id_tarifario: item.id_tarifario ?? null,
        codigo_servicio: item.codigo_servicio ?? '',
        numero_tarifa: item.numero_tarifa ?? '',
        descripcion_servicio: item.descripcion_servicio ?? '',
        cantidad: toNumber(item.cantidad),
        total_linea: toNumber(item.total_linea),
        unidades_pendientes: toNumber(item.unidades_pendientes, toNumber(item.cantidad)),
        importe_pendiente: toNumber(item.importe_pendiente, toNumber(item.total_linea)),
    };
}

function makeLineFromAvailable(item) {
    const display = availableDisplay(item);

    return {
        id_pedido_item: Number(item.id_pedido_item),
        unidades_facturadas: display.unidades_pendientes > 0 ? String(display.unidades_pendientes) : '',
        importe_facturado: String(display.importe_pendiente),
        observaciones: '',
        display,
    };
}

function itemsAmount(lines) {
    return lines.reduce((sum, line) => sum + toNumber(line.importe_facturado), 0);
}

function flattenApiErrors(error) {
    const errors = error.response?.data?.errors ?? {};
    const firstError = Object.values(errors).flat()?.[0];

    return firstError ?? error.response?.data?.message ?? 'No se pudo guardar el campo.';
}

function responseFactura(response, fallback, payload = {}) {
    const data = response.data?.data?.data ?? response.data?.data;

    return data && typeof data === 'object'
        ? data
        : { ...fallback, ...payload };
}

async function patchFactura(factura, payload, onPatched) {
    const response = await axios.patch(`/api/v1/facturas/${factura.id_factura}`, payload);
    const updated = responseFactura(response, factura, payload);
    onPatched?.(factura.id_factura, updated);

    return updated;
}

function facturaContractId(factura) {
    return factura.id_contrato
        ?? factura.contrato?.id_contrato
        ?? factura.items?.find((item) => item?.tarifario?.id_contrato)?.tarifario?.id_contrato
        ?? factura.trabajo?.id_contrato
        ?? null;
}

function sociedadesForFactura(factura, empresasFacturadoras) {
    const empresas = empresasFacturadoras[String(factura.id_contexto)] ?? [];
    const contractId = Number(facturaContractId(factura));

    if (!Number.isFinite(contractId)) return empresas;

    return empresas.filter((empresa) => contractIdsForEmpresa(empresa).includes(contractId));
}

function EditableTextCell({
    factura,
    fieldName,
    canEdit,
    onPatched,
    displayValue = null,
    inputType = 'text',
    numberStep = undefined,
    numberMin = undefined,
    className = '',
    payloadBuilder = null,
    formatDisplay = fmt,
}) {
    const rawValue = factura[fieldName] ?? '';
    const [editing, setEditing] = useState(false);
    const [value, setValue] = useState(rawValue);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        setValue(rawValue);
        setError('');
    }, [factura.id_factura, fieldName, rawValue]);

    async function commit() {
        const normalizedValue = value === '' ? null : value;

        if (String(rawValue ?? '') === String(normalizedValue ?? '')) {
            setEditing(false);
            return;
        }

        setSaving(true);
        setError('');

        try {
            const payload = payloadBuilder ? payloadBuilder(normalizedValue) : { [fieldName]: normalizedValue };
            await patchFactura(factura, payload, onPatched);
            setEditing(false);
        } catch (err) {
            setError(flattenApiErrors(err));
        } finally {
            setSaving(false);
        }
    }

    function rollback() {
        setValue(rawValue);
        setError('');
        setEditing(false);
    }

    if (!canEdit) {
        const renderedValue = formatDisplay(displayValue ?? rawValue);
        const titleValue = String(displayValue ?? rawValue ?? '');

        return (
            <span className={`block max-w-full truncate overflow-hidden whitespace-nowrap ${className}`.trim()} title={titleValue}>
                {renderedValue}
            </span>
        );
    }

    return editing ? (
        <span className="block min-w-[120px]">
            <input
                autoFocus
                type={inputType}
                value={value ?? ''}
                step={inputType === 'number' ? numberStep : undefined}
                min={inputType === 'number' ? numberMin : undefined}
                onChange={(event) => setValue(event.target.value)}
                onBlur={commit}
                onKeyDown={(event) => {
                    if (event.key === 'Enter') commit();
                    if (event.key === 'Escape') rollback();
                }}
                className="h-8 w-full rounded border border-(--ciete-red) bg-surface px-2 text-xs text-text-main outline-none ring-1 ring-(--ciete-red)"
                disabled={saving}
            />
            {error && <span className="mt-1 block max-w-[220px] whitespace-normal text-[10px] font-semibold text-red-600">{error}</span>}
        </span>
    ) : (
        <button
            type="button"
            onClick={() => setEditing(true)}
            disabled={saving}
            className="inline-flex w-full max-w-full items-center rounded px-1 py-0.5 text-left transition hover:bg-surface-2 hover:text-text-main disabled:cursor-wait"
            title={error || String(displayValue ?? rawValue ?? 'Editar')}
        >
            <span className={`block max-w-full truncate overflow-hidden whitespace-nowrap ${className}`.trim()}>
                {formatDisplay(displayValue ?? rawValue)}
            </span>
            {error && <span className="ml-1 text-red-600">!</span>}
        </button>
    );
}

function EditableEstadoCell({ factura, canEdit, onPatched }) {
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');

    if (!canEdit) {
        return <BadgeFactura estado={factura.estado} />;
    }

    return (
        <span className="block">
            <select
                value={factura.estado ?? 'pendiente'}
                disabled={saving}
                onChange={async (event) => {
                    setSaving(true);
                    setError('');

                    try {
                        await patchFactura(factura, { estado: event.target.value }, onPatched);
                    } catch (err) {
                        setError(flattenApiErrors(err));
                    } finally {
                        setSaving(false);
                    }
                }}
                className="h-8 rounded border border-border bg-surface px-2 text-xs text-text-main"
            >
                {ESTADO_OPTIONS.map((option) => (
                    <option key={option} value={option}>{ESTADO_LABEL[option] ?? option}</option>
                ))}
            </select>
            {error && <span className="mt-1 block max-w-[180px] whitespace-normal text-[10px] font-semibold text-red-600">{error}</span>}
        </span>
    );
}

function EditableSociedadCell({ factura, canEdit, empresasFacturadoras, onPatched }) {
    const empresas = sociedadesForFactura(factura, empresasFacturadoras);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');

    if (!canEdit || empresas.length === 0) {
        const sociedadFacturadora = factura.empresa_facturadora ?? factura.empresa ?? null;

        return (
            <span className="block truncate font-semibold text-text-main" title={sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre ?? ''}>
                {sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre ?? '—'}
            </span>
        );
    }

    return (
        <span className="block min-w-0 max-w-full">
            <select
                value={factura.id_empresa_facturadora ?? ''}
                disabled={saving}
                onChange={async (event) => {
                    setSaving(true);
                    setError('');

                    try {
                        await patchFactura(factura, { id_empresa_facturadora: event.target.value || null }, onPatched);
                    } catch (err) {
                        setError(flattenApiErrors(err));
                    } finally {
                        setSaving(false);
                    }
                }}
                className="h-8 w-full max-w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
            >
                <option value="">Seleccionar sociedad</option>
                {empresas.map((empresa) => (
                    <option key={empresa.id_empresa} value={empresa.id_empresa}>
                        {empresa.nombre_comercial ?? empresa.nombre ?? 'Sociedad'}
                    </option>
                ))}
            </select>
            {error && <span className="mt-1 block max-w-[220px] whitespace-normal text-[10px] font-semibold text-red-600">{error}</span>}
        </span>
    );
}

function EditableAutofacturaCell({ factura, canEdit, onPatched }) {
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');

    if (!canEdit) {
        return factura.autofactura ? <span className="font-bold text-green-600">✓</span> : '—';
    }

    return (
        <span className="inline-flex flex-col items-center">
            <input
                type="checkbox"
                checked={Boolean(factura.autofactura)}
                disabled={saving}
                onChange={async (event) => {
                    setSaving(true);
                    setError('');

                    try {
                        await patchFactura(factura, { autofactura: event.target.checked }, onPatched);
                    } catch (err) {
                        setError(flattenApiErrors(err));
                    } finally {
                        setSaving(false);
                    }
                }}
                className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
            />
            {error && <span className="mt-1 text-[10px] font-semibold text-red-600">!</span>}
        </span>
    );
}

export default function FacturasExcelView({
    facturas = [],
    filters = {},
    aplicarFiltros,
    canCreate = true,
    canEdit = true,
    canExport = false,
    selectedIds = [],
    onToggleSelected,
    onToggleAllVisible,
    onExportList,
    onExportSelected,
    onExportDetail,
    pagination = null,
    isMoeve = false,
    isRepsol = false,
    activeContext = null,
    pedidoItemsFacturables = [],
    empresasFacturadoras = {},
    facturacionCatalogos = {},
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [estado, setEstado] = useState(filters.estado ?? '');
    const [newRow, setNewRow] = useState(null);
    const [newErrors, setNewErrors] = useState({});
    const [savingNew, setSavingNew] = useState(false);
    const [createSearches, setCreateSearches] = useState({
        contrato: '',
        empresa: '',
        trabajo: '',
        pedido: '',
        item: '',
    });
    const [itemToAdd, setItemToAdd] = useState('');
    const [patchedFacturas, setPatchedFacturas] = useState({});
    const contextCatalog = facturacionCatalogos?.contextos ?? [];
    const activeContextId = activeContext?.is_all
        ? ''
        : String(activeContext?.id_contexto ?? contextCatalog[0]?.id_contexto ?? '');
    const selectedContextId = newRow?.id_contexto || activeContextId;
    const selectedInvoiceItems = newRow?.items ?? [];
    const selectedPedidoItemIds = useMemo(
        () => new Set(selectedInvoiceItems.map((item) => Number(item.id_pedido_item))),
        [selectedInvoiceItems],
    );
    const assignedAmount = useMemo(
        () => itemsAmount(selectedInvoiceItems),
        [selectedInvoiceItems],
    );
    const invoiceTotal = newRow?.total !== '' ? toNumber(newRow?.total) : assignedAmount;
    const invoiceDifference = invoiceTotal - assignedAmount;
    const visibleFacturas = useMemo(
        () => facturas.map((factura) => patchedFacturas[factura.id_factura] ?? factura),
        [facturas, patchedFacturas],
    );
    const selectedContextMeta = useMemo(
        () => contextCatalog.find((contexto) => String(contexto.id_contexto) === String(selectedContextId)) ?? null,
        [contextCatalog, selectedContextId],
    );
    const availableContracts = useMemo(
        () => (facturacionCatalogos?.contratos ?? [])
            .filter((contract) => String(contract.id_contexto) === String(selectedContextId))
            .filter((contract) => includesText(
                createSearches.contrato,
                formatContractLabel(contract),
                contract.codigo_contrato,
                contract.nombre_contrato,
                contract.id_contrato,
            )),
        [createSearches.contrato, facturacionCatalogos, selectedContextId],
    );
    const contractOptions = useMemo(
        () => availableContracts.map((contract) => ({
            value: String(contract.id_contrato),
            label: formatContractLabel(contract),
        })),
        [availableContracts],
    );
    const availableEmpresasRaw = useMemo(() => {
        if (!selectedContextId || !newRow?.id_contrato) return [];

        return (empresasFacturadoras[String(selectedContextId)] ?? [])
            .filter((empresa) => contractIdsForEmpresa(empresa).includes(Number(newRow.id_contrato)))
            .filter((empresa) => includesText(
                createSearches.empresa,
                empresa.cif_label,
                empresa.nombre,
                empresa.nombre_comercial,
                empresa.razon_social,
                empresa.cif,
            ));
    }, [createSearches.empresa, empresasFacturadoras, newRow?.id_contrato, selectedContextId]);
    const empresaOptions = useMemo(
        () => availableEmpresasRaw.map((empresa) => ({
            value: String(empresa.id_empresa),
            label: empresa.cif_label ?? `${empresa.nombre ?? 'Sociedad'} - ${empresa.cif ?? ''}`,
        })),
        [availableEmpresasRaw],
    );
    const availableTrabajosRaw = useMemo(() => {
        if (!selectedContextId || !newRow?.id_contrato || !newRow?.id_empresa_facturadora) return [];

        return (facturacionCatalogos?.trabajos ?? [])
            .filter((trabajo) => String(trabajo.id_contexto) === String(selectedContextId))
            .filter((trabajo) => String(trabajo.id_contrato) === String(newRow.id_contrato))
            .filter((trabajo) => includesText(
                createSearches.trabajo,
                formatTrabajoLabel(trabajo),
                trabajo.numero_trabajo_visible,
                trabajo.descripcion_trabajo,
                trabajo.codigo_estacion,
            ));
    }, [createSearches.trabajo, facturacionCatalogos, newRow?.id_contrato, newRow?.id_empresa_facturadora, selectedContextId]);
    const trabajoOptions = useMemo(
        () => availableTrabajosRaw.map((trabajo) => ({
            value: String(trabajo.id_trabajo),
            label: formatTrabajoLabel(trabajo),
        })),
        [availableTrabajosRaw],
    );
    const availablePedidosRaw = useMemo(() => {
        if (!selectedContextId || !newRow?.id_contrato || !newRow?.id_trabajo) return [];

        return (facturacionCatalogos?.pedidos ?? [])
            .filter((pedido) => String(pedido.id_contexto) === String(selectedContextId))
            .filter((pedido) => String(pedido.id_contrato) === String(newRow.id_contrato))
            .filter((pedido) => String(pedido.id_trabajo) === String(newRow.id_trabajo))
            .filter((pedido) => includesText(
                createSearches.pedido,
                formatPedidoLabel(pedido),
                pedido.numero_pedido,
                pedido.numero_trabajo_visible,
            ));
    }, [createSearches.pedido, facturacionCatalogos, newRow?.id_contrato, newRow?.id_trabajo, selectedContextId]);
    const pedidoOptions = useMemo(
        () => availablePedidosRaw.map((pedido) => ({
            value: String(pedido.id_pedido),
            label: formatPedidoLabel(pedido),
        })),
        [availablePedidosRaw],
    );
    const filteredPedidoItems = useMemo(() => {
        if (!selectedContextId || !newRow?.id_contrato || !newRow?.id_trabajo || !newRow?.id_pedido) {
            return [];
        }

        return pedidoItemsFacturables
            .filter((item) => !selectedPedidoItemIds.has(Number(item.id_pedido_item)))
            .filter((item) => String(item.id_contexto) === String(selectedContextId))
            .filter((item) => String(item.id_contrato) === String(newRow.id_contrato))
            .filter((item) => String(item.id_trabajo) === String(newRow.id_trabajo))
            .filter((item) => String(item.id_pedido) === String(newRow.id_pedido))
            .filter((item) => includesText(
                createSearches.item,
                pedidoItemLabel(item),
                item.numero_pedido,
                item.numero_trabajo_visible,
                item.numero_trabajo_operativo,
                item.numero_trabajo,
                item.descripcion_servicio,
                item.codigo_servicio,
                item.descripcion_trabajo,
                item.codigo_estacion,
                item.numero_tarifa,
            ))
            .slice(0, 80);
    }, [createSearches.item, newRow?.id_contrato, newRow?.id_pedido, newRow?.id_trabajo, pedidoItemsFacturables, selectedContextId, selectedPedidoItemIds]);
    const itemOptions = useMemo(
        () => filteredPedidoItems.map((item) => ({
            value: String(item.id_pedido_item),
            label: pedidoItemLabel(item),
        })),
        [filteredPedidoItems],
    );
    const selectedContractMeta = useMemo(
        () => (facturacionCatalogos?.contratos ?? []).find(
            (contract) => String(contract.id_contexto) === String(selectedContextId)
                && String(contract.id_contrato) === String(newRow?.id_contrato),
        ) ?? null,
        [facturacionCatalogos, newRow?.id_contrato, selectedContextId],
    );
    const selectedEmpresaMeta = useMemo(
        () => availableEmpresasRaw.find(
            (empresa) => String(empresa.id_empresa) === String(newRow?.id_empresa_facturadora),
        ) ?? null,
        [availableEmpresasRaw, newRow?.id_empresa_facturadora],
    );

    useEffect(() => {
        if (!newRow || !newRow.id_contrato) {
            return;
        }

        const selectedEmpresaStillAllowed = availableEmpresasRaw.some(
            (empresa) => String(empresa.id_empresa) === String(newRow.id_empresa_facturadora),
        );

        if (selectedEmpresaStillAllowed) {
            return;
        }

        if (availableEmpresasRaw.length === 1) {
            const nextEmpresaId = String(availableEmpresasRaw[0].id_empresa);

            if (String(newRow.id_empresa_facturadora) === nextEmpresaId) {
                return;
            }

            setNewRow((current) => (current ? { ...current, id_empresa_facturadora: nextEmpresaId } : current));
            return;
        }

        if (!newRow.id_empresa_facturadora) {
            return;
        }

        setNewRow((current) => (current ? {
            ...current,
            id_empresa_facturadora: '',
            id_trabajo: '',
            id_pedido: '',
            items: [],
            total: '',
            total_manual: false,
        } : current));
    }, [availableEmpresasRaw, newRow?.id_contrato, newRow?.id_empresa_facturadora]);

    function handleFacturaPatched(idFactura, updatedFactura) {
        setPatchedFacturas((current) => ({
            ...current,
            [idFactura]: {
                ...(current[idFactura] ?? {}),
                ...updatedFactura,
            },
        }));
    }

    function clearNewErrors(fields = []) {
        if (!Array.isArray(fields) || fields.length === 0) {
            setNewErrors({});
            return;
        }

        setNewErrors((current) => {
            const next = { ...current };

            fields.forEach((field) => {
                delete next[field];
            });

            return next;
        });
    }

    function updateCreateSearch(field, value) {
        setCreateSearches((current) => ({
            ...current,
            [field]: value,
        }));
    }

    function updateNewRow(field, value) {
        clearNewErrors([field, 'form']);
        setNewRow((current) => (current ? { ...current, [field]: value } : current));
    }

    function startCreate() {
        setCreateSearches({
            contrato: '',
            empresa: '',
            trabajo: '',
            pedido: '',
            item: '',
        });
        setItemToAdd('');
        setNewErrors({});
        setNewRow(emptyNewFactura(activeContextId));
    }

    function handleContractChange(value) {
        clearNewErrors(['id_contrato', 'id_empresa_facturadora', 'id_trabajo', 'id_pedido', 'items', 'form']);
        setCreateSearches((current) => ({
            ...current,
            empresa: '',
            trabajo: '',
            pedido: '',
            item: '',
        }));
        setItemToAdd('');
        setNewRow((current) => (current ? {
            ...current,
            id_contrato: value,
            id_empresa_facturadora: '',
            id_trabajo: '',
            id_pedido: '',
            items: [],
            total: '',
            total_manual: false,
        } : current));
    }

    function handleEmpresaChange(value) {
        clearNewErrors(['id_empresa_facturadora', 'id_trabajo', 'id_pedido', 'items', 'form']);
        setCreateSearches((current) => ({
            ...current,
            trabajo: '',
            pedido: '',
            item: '',
        }));
        setItemToAdd('');
        setNewRow((current) => (current ? {
            ...current,
            id_empresa_facturadora: value,
            id_trabajo: '',
            id_pedido: '',
            items: [],
            total: '',
            total_manual: false,
        } : current));
    }

    function handleTrabajoChange(value) {
        clearNewErrors(['id_trabajo', 'id_pedido', 'items', 'form']);
        setCreateSearches((current) => ({
            ...current,
            pedido: '',
            item: '',
        }));
        setItemToAdd('');
        setNewRow((current) => (current ? {
            ...current,
            id_trabajo: value,
            id_pedido: '',
            items: [],
            total: '',
            total_manual: false,
        } : current));
    }

    function handlePedidoChange(value) {
        clearNewErrors(['id_pedido', 'items', 'form']);
        updateCreateSearch('item', '');
        setItemToAdd('');
        setNewRow((current) => (current ? {
            ...current,
            id_pedido: value,
            items: [],
            total: '',
            total_manual: false,
        } : current));
    }

    function addPedidoItem(idPedidoItem) {
        const normalizedId = String(idPedidoItem ?? '').trim();

        if (!normalizedId) {
            return;
        }

        const selectedItem = filteredPedidoItems.find(
            (item) => String(item.id_pedido_item) === normalizedId,
        );

        if (!selectedItem) {
            return;
        }

        clearNewErrors(['items', 'form']);
        setItemToAdd('');
        updateCreateSearch('item', '');
        setNewRow((current) => {
            if (!current) {
                return current;
            }

            return {
                ...current,
                items: [...current.items, makeLineFromAvailable(selectedItem)],
            };
        });
    }

    function updateInvoiceLine(index, field, value) {
        clearNewErrors([`items.${index}.${field}`, 'items', 'form']);
        setNewRow((current) => {
            if (!current) {
                return current;
            }

            return {
                ...current,
                items: current.items.map((line, lineIndex) => (
                    lineIndex === index ? { ...line, [field]: value } : line
                )),
            };
        });
    }

    function removeInvoiceLine(index) {
        clearNewErrors(['items', 'form']);
        setNewRow((current) => {
            if (!current) {
                return current;
            }

            return {
                ...current,
                items: current.items.filter((_, lineIndex) => lineIndex !== index),
            };
        });
    }

    function doFilter(overrides = {}) {
        aplicarFiltros({
            search: overrides.search !== undefined ? overrides.search : search,
            estado: overrides.estado !== undefined ? overrides.estado : estado,
            ...(overrides.page !== undefined ? { page: overrides.page } : {}),
        });
    }

    async function saveNewFactura(event) {
        event.preventDefault();

        if (!newRow) {
            return;
        }

        const payload = {
            id_contrato: newRow.id_contrato ? Number(newRow.id_contrato) : null,
            id_trabajo: newRow.id_trabajo ? Number(newRow.id_trabajo) : null,
            id_empresa_facturadora: newRow.id_empresa_facturadora ? Number(newRow.id_empresa_facturadora) : null,
            numero_factura: newRow.numero_factura.trim() || null,
            fecha_emision: newRow.fecha_emision || null,
            estado: newRow.estado || 'pendiente',
            base_imponible: newRow.total !== '' ? toNumber(newRow.total) : assignedAmount,
            importe: newRow.total !== '' ? toNumber(newRow.total) : assignedAmount,
            total: newRow.total !== '' ? toNumber(newRow.total) : assignedAmount,
            autofactura: Boolean(newRow.autofactura),
            items: selectedInvoiceItems.map((line) => ({
                id_pedido_item: Number(line.id_pedido_item),
                unidades_facturadas: line.unidades_facturadas !== '' ? toNumber(line.unidades_facturadas) : null,
                importe_facturado: toNumber(line.importe_facturado),
                observaciones: line.observaciones?.trim() || null,
            })),
        };

        if (isMoeve) {
            payload.factura_ccp = newRow.factura_ccp.trim();
            payload.sociedad = newRow.sociedad.trim() || null;
        }

        if (isRepsol) {
            payload.orden_factura = newRow.orden_factura ? Number(newRow.orden_factura) : null;
            payload.autofactura = Boolean(newRow.autofactura);
        }

        setSavingNew(true);
        setNewErrors({});

        try {
            await axios.post('/api/v1/facturas', payload);
            setCreateSearches({
                contrato: '',
                empresa: '',
                trabajo: '',
                pedido: '',
                item: '',
            });
            setItemToAdd('');
            setNewRow(null);
            router.reload({ preserveScroll: true });
        } catch (error) {
            if (error.response?.status === 422) {
                const responseErrors = error.response.data?.errors ?? {};
                setNewErrors(
                    Object.fromEntries(
                        Object.entries(responseErrors).map(([field, messages]) => [
                            field,
                            Array.isArray(messages) ? messages[0] : messages,
                        ]),
                    ),
                );
            } else {
                setNewErrors({ form: 'No se pudo guardar la factura.' });
            }
        } finally {
            setSavingNew(false);
        }
    }

    // ── Columnas: orden operativo CIETE ───────────────────────────────────────
    // 1. Estado  2. Nº Factura  3. Fecha  4. Sociedad  5. CIF
    // 6. Contrato/tarifa  7. Importe  8. [MOEVE: CCP, Sociedad M]
    // 9. [REPSOL: Orden, Auto.]  10. Acciones
    const columns = [
        { label: 'Estado',        key: 'estado',           width: 'w-24' },
        { label: 'Nº Factura',    key: 'numero_factura',   width: 'w-44' },
        { label: 'Fecha',         key: 'fecha_emision',    width: 'w-28' },
        { label: 'Sociedad',      key: 'sociedad_nombre',  width: 'w-56' },
        { label: 'CIF',           key: 'cif',              width: 'w-36' },
        { label: 'Contrato',      key: 'contrato',         width: 'w-36' },
        { label: 'Importe',       key: 'total',            width: 'w-28' },
        { label: 'Asignado',      key: 'importe_asignado', width: 'w-28' },
        { label: 'Difer.',        key: 'diferencia',       width: 'w-24' },
        { label: 'Cuadre',        key: 'estado_cuadre',    width: 'w-24' },
        ...(isMoeve ? [
            { label: 'Nº CCP',    key: 'factura_ccp',      width: 'w-24', badge: 'M' },
            { label: 'Sociedad M',key: 'sociedad',         width: 'w-28', badge: 'M' },
        ] : []),
        ...(isRepsol ? [
            { label: 'Orden',     key: 'orden_factura',    width: 'w-16', badge: 'R' },
            { label: 'Auto.',     key: 'autofactura',      width: 'w-14', badge: 'R' },
        ] : []),
        { label: 'Acciones',      key: '_acciones',        width: 'w-20' },
    ];
    const visibleIds = visibleFacturas.map((factura) => factura.id_factura).filter(Boolean);
    const allVisibleSelected = visibleIds.length > 0 && visibleIds.every((id) => selectedIds.includes(id));

    return (
        <div className="space-y-3">
            {/* Barra de filtros compacta */}
            <div className="flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-3 shadow-sm">
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && doFilter({ search })}
                    placeholder="Buscar factura, sociedad, CIF…"
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />
                <select
                    value={estado}
                    onChange={(e) => { setEstado(e.target.value); doFilter({ estado: e.target.value }); }}
                    className="rounded border border-border bg-surface px-2 py-1 text-xs text-text-main"
                >
                    <option value="">Todos los estados</option>
                    {ESTADO_OPTIONS.map((op) => (
                        <option key={op} value={op}>{ESTADO_LABEL[op] ?? op}</option>
                    ))}
                </select>
                {canExport && (
                    <button
                        type="button"
                        onClick={onExportList}
                        className="rounded border border-border px-3 py-1 text-xs font-semibold text-text-main hover:bg-surface-2"
                    >
                        Exportar listado
                    </button>
                )}
                {canExport && selectedIds.length > 0 && (
                    <button
                        type="button"
                        onClick={onExportSelected}
                        className="rounded border border-(--ciete-red) px-3 py-1 text-xs font-semibold text-(--ciete-red) hover:bg-red-50"
                    >
                        Exportar seleccionadas ({selectedIds.length})
                    </button>
                )}
                {canCreate && (
                    <button
                        type="button"
                        onClick={startCreate}
                        disabled={Boolean(newRow)}
                        className="ml-auto rounded bg-(--ciete-red) px-3 py-1 text-xs font-semibold text-white hover:bg-(--ciete-red-dark)"
                    >
                        + Nueva factura
                    </button>
                )}
            </div>

            {newRow && (
                <form onSubmit={saveNewFactura} className="rounded-xl border border-(--ciete-red)/30 bg-surface p-3 shadow-sm">
                    <div className="space-y-4">
                        <div className="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
                            <div className="space-y-4">
                                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                                    <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                        Contexto activo
                                        <input
                                            value={selectedContextMeta?.nombre ?? activeContext?.nombre ?? activeContext?.label ?? '—'}
                                            disabled
                                            className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs font-normal normal-case tracking-normal text-text-main disabled:opacity-80"
                                        />
                                        {selectedContextMeta?.codigo && (
                                            <span className="mt-1 block text-[11px] font-normal normal-case tracking-normal text-text-muted">
                                                {selectedContextMeta.codigo}
                                            </span>
                                        )}
                                        {newErrors.id_contexto && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.id_contexto}</span>}
                                    </label>

                                    <SearchableSelectField
                                        label="Contrato"
                                        searchValue={createSearches.contrato}
                                        onSearchChange={(value) => updateCreateSearch('contrato', value)}
                                        searchPlaceholder="Buscar contrato..."
                                        value={newRow.id_contrato}
                                        onChange={handleContractChange}
                                        selectPlaceholder="Seleccionar contrato"
                                        options={contractOptions}
                                        disabled={!selectedContextId}
                                        error={newErrors.id_contrato}
                                        helper={!selectedContextId ? 'Selecciona antes un contexto activo valido.' : ''}
                                        countLabel={selectedContextId ? `${contractOptions.length} contratos visibles` : ''}
                                    />

                                    <SearchableSelectField
                                        label="Sociedad/CIF"
                                        searchValue={createSearches.empresa}
                                        onSearchChange={(value) => updateCreateSearch('empresa', value)}
                                        searchPlaceholder="Buscar sociedad o CIF..."
                                        value={newRow.id_empresa_facturadora}
                                        onChange={handleEmpresaChange}
                                        selectPlaceholder="Seleccionar sociedad"
                                        options={empresaOptions}
                                        disabled={!newRow.id_contrato}
                                        error={newErrors.id_empresa_facturadora}
                                        helper={!newRow.id_contrato ? 'Selecciona antes un contrato.' : (availableEmpresasRaw.length === 0 ? 'No hay sociedades permitidas para este contrato.' : '')}
                                        countLabel={newRow.id_contrato ? `${empresaOptions.length} sociedades visibles` : ''}
                                    />

                                    {newRow.id_contrato && availableEmpresasRaw.length === 0 && (
                                        <div className="md:col-span-2 xl:col-span-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                            <p className="font-semibold">
                                                No hay sociedades disponibles para {selectedContractMeta ? formatContractLabel(selectedContractMeta) : 'el contrato seleccionado'}.
                                            </p>
                                            <p className="mt-1 text-amber-700">
                                                Este selector solo muestra relaciones activas contrato-sociedad con empresa activa y CIF informado dentro del contexto actual.
                                            </p>
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('maestros.sociedades.index'))}
                                                    className="rounded border border-amber-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 transition hover:bg-amber-100"
                                                >
                                                    Abrir sociedades facturadoras
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => router.visit(route('clientes.index'))}
                                                    className="rounded border border-amber-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 transition hover:bg-amber-100"
                                                >
                                                    Revisar empresas / CIF
                                                </button>
                                            </div>
                                        </div>
                                    )}

                                    <SearchableSelectField
                                        label="Trabajo"
                                        searchValue={createSearches.trabajo}
                                        onSearchChange={(value) => updateCreateSearch('trabajo', value)}
                                        searchPlaceholder="Buscar trabajo o estacion..."
                                        value={newRow.id_trabajo}
                                        onChange={handleTrabajoChange}
                                        selectPlaceholder="Seleccionar trabajo"
                                        options={trabajoOptions}
                                        disabled={!newRow.id_empresa_facturadora}
                                        error={newErrors.id_trabajo}
                                        helper={!newRow.id_empresa_facturadora ? 'Selecciona antes una sociedad/CIF.' : (availableTrabajosRaw.length === 0 ? 'No hay trabajos facturables para este contrato.' : '')}
                                        countLabel={newRow.id_empresa_facturadora ? `${trabajoOptions.length} trabajos visibles` : ''}
                                    />

                                    <SearchableSelectField
                                        label="Pedido"
                                        searchValue={createSearches.pedido}
                                        onSearchChange={(value) => updateCreateSearch('pedido', value)}
                                        searchPlaceholder="Buscar pedido..."
                                        value={newRow.id_pedido}
                                        onChange={handlePedidoChange}
                                        selectPlaceholder="Seleccionar pedido"
                                        options={pedidoOptions}
                                        disabled={!newRow.id_trabajo}
                                        error={newErrors.id_pedido}
                                        helper={!newRow.id_trabajo ? 'Selecciona antes un trabajo.' : (availablePedidosRaw.length === 0 ? 'No hay pedidos facturables para este trabajo.' : '')}
                                        countLabel={newRow.id_trabajo ? `${pedidoOptions.length} pedidos visibles` : ''}
                                    />
                                </div>

                                <div className="rounded-lg border border-border bg-surface p-3">
                                    <div className="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                                        <SearchableSelectField
                                            label="Items facturables"
                                            searchValue={createSearches.item}
                                            onSearchChange={(value) => updateCreateSearch('item', value)}
                                            searchPlaceholder="Buscar item, servicio o importe..."
                                            value={itemToAdd}
                                            onChange={setItemToAdd}
                                            selectPlaceholder="Seleccionar item"
                                            options={itemOptions}
                                            disabled={!newRow.id_pedido}
                                            error={newErrors.items}
                                            helper={
                                                !newRow.id_contrato
                                                    ? 'Selecciona antes un contrato.'
                                                    : !newRow.id_empresa_facturadora
                                                      ? 'Selecciona antes una sociedad/CIF.'
                                                      : !newRow.id_trabajo
                                                        ? 'Selecciona antes un trabajo.'
                                                        : !newRow.id_pedido
                                                          ? 'Selecciona antes un pedido.'
                                                          : (itemOptions.length === 0 ? 'No hay items facturables disponibles para este pedido.' : '')
                                            }
                                            countLabel={newRow.id_pedido ? `${itemOptions.length} items visibles` : ''}
                                        />

                                        <button
                                            type="button"
                                            onClick={() => addPedidoItem(itemToAdd)}
                                            disabled={!itemToAdd}
                                            className="h-10 rounded bg-(--ciete-red) px-4 text-xs font-semibold text-white hover:bg-(--ciete-red-dark) disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            Anadir item
                                        </button>
                                    </div>
                                </div>

                                <div className="min-w-0">
                                    <div className="mb-1 flex items-center justify-between gap-2">
                                        <span className="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Items seleccionados</span>
                                        <span className="text-xs normal-case tracking-normal text-text-muted">
                                            {selectedInvoiceItems.length} items · {fmtMoney(assignedAmount)}
                                        </span>
                                    </div>
                                    <div className="overflow-x-auto rounded border border-border bg-surface">
                                        <table className="w-full min-w-[780px] divide-y divide-border text-xs normal-case tracking-normal">
                                            <thead className="bg-surface-2 text-[10px] font-semibold uppercase tracking-wide text-text-hint">
                                                <tr>
                                                    <th className="px-2 py-2 text-left">Pedido / Trabajo</th>
                                                    <th className="px-2 py-2 text-left">Servicio</th>
                                                    <th className="px-2 py-2 text-right">Ud. pend.</th>
                                                    <th className="px-2 py-2 text-right">Ud. fact.</th>
                                                    <th className="px-2 py-2 text-right">Imp. pend.</th>
                                                    <th className="px-2 py-2 text-right">Imp. fact.</th>
                                                    <th className="px-2 py-2 text-left">Obs.</th>
                                                    <th className="px-2 py-2 text-right">Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-border">
                                                {selectedInvoiceItems.length === 0 && (
                                                    <tr>
                                                        <td colSpan={8} className="px-3 py-8 text-center text-xs text-text-muted">
                                                            No hay items seleccionados.
                                                        </td>
                                                    </tr>
                                                )}
                                                {selectedInvoiceItems.map((line, index) => (
                                                    <tr key={line.id_pedido_item} className="align-top text-text-main">
                                                        <td className="px-2 py-2">
                                                            <p className="ciete-dark-table-accent font-mono font-semibold">
                                                                {line.display.numero_pedido}
                                                            </p>
                                                            <p className="mt-1 text-text-muted">
                                                                Trabajo {formatWorkNumber(line.display.numero_trabajo_visible)}
                                                            </p>
                                                        </td>
                                                        <td className="px-2 py-2">
                                                            <p className="font-medium">
                                                                {line.display.descripcion_servicio || line.display.codigo_servicio || '-'}
                                                            </p>
                                                            <p className="mt-1 text-text-hint">
                                                                {line.display.numero_tarifa || line.display.codigo_estacion || ''}
                                                            </p>
                                                        </td>
                                                        <td className="whitespace-nowrap px-2 py-2 text-right text-text-muted">
                                                            {line.display.unidades_pendientes}
                                                        </td>
                                                        <td className="px-2 py-2">
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                step="0.001"
                                                                value={line.unidades_facturadas}
                                                                onChange={(event) => updateInvoiceLine(index, 'unidades_facturadas', event.target.value)}
                                                                className="h-8 w-24 rounded border border-border bg-surface px-2 text-right text-xs text-text-main"
                                                            />
                                                            {newErrors[`items.${index}.unidades_facturadas`] && (
                                                                <span className="mt-1 block max-w-[140px] text-[10px] font-semibold text-red-600">
                                                                    {newErrors[`items.${index}.unidades_facturadas`]}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="whitespace-nowrap px-2 py-2 text-right text-text-muted">
                                                            {fmtMoney(line.display.importe_pendiente)}
                                                        </td>
                                                        <td className="px-2 py-2">
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                step="0.01"
                                                                value={line.importe_facturado}
                                                                onChange={(event) => updateInvoiceLine(index, 'importe_facturado', event.target.value)}
                                                                className="h-8 w-28 rounded border border-border bg-surface px-2 text-right text-xs text-text-main"
                                                            />
                                                            {newErrors[`items.${index}.importe_facturado`] && (
                                                                <span className="mt-1 block max-w-[150px] text-[10px] font-semibold text-red-600">
                                                                    {newErrors[`items.${index}.importe_facturado`]}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-2 py-2">
                                                            <input
                                                                value={line.observaciones}
                                                                onChange={(event) => updateInvoiceLine(index, 'observaciones', event.target.value)}
                                                                className="h-8 w-36 rounded border border-border bg-surface px-2 text-xs text-text-main"
                                                            />
                                                        </td>
                                                        <td className="px-2 py-2 text-right">
                                                            <button
                                                                type="button"
                                                                onClick={() => removeInvoiceLine(index)}
                                                                className="ciete-dark-table-accent text-xs font-semibold hover:underline"
                                                            >
                                                                Quitar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4 rounded-lg border border-border bg-surface-2 p-4">
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <div className="rounded border border-border bg-surface px-3 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Contrato</p>
                                        <p className="mt-1 text-sm font-semibold text-text-main">
                                            {selectedContractMeta ? formatContractLabel(selectedContractMeta) : '—'}
                                        </p>
                                    </div>
                                    <div className="rounded border border-border bg-surface px-3 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Sociedad/CIF</p>
                                        <p className="mt-1 text-sm font-semibold text-text-main">
                                            {selectedEmpresaMeta?.cif_label ?? '—'}
                                        </p>
                                    </div>
                                </div>

                                <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                    Fecha
                                    <input
                                        type="date"
                                        value={newRow.fecha_emision}
                                        onChange={(event) => updateNewRow('fecha_emision', event.target.value)}
                                        className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                    />
                                    {newErrors.fecha_emision && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.fecha_emision}</span>}
                                </label>

                                <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                    Estado
                                    <select
                                        value={newRow.estado}
                                        onChange={(event) => updateNewRow('estado', event.target.value)}
                                        className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                    >
                                        {ESTADO_OPTIONS.map((op) => (
                                            <option key={op} value={op}>{ESTADO_LABEL[op] ?? op}</option>
                                        ))}
                                    </select>
                                </label>

                                <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                    Nº factura
                                    <input
                                        value={newRow.numero_factura}
                                        onChange={(event) => updateNewRow('numero_factura', event.target.value)}
                                        className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                    />
                                    {newErrors.numero_factura && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.numero_factura}</span>}
                                </label>

                                <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                    Total factura
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={newRow.total}
                                        onChange={(event) => {
                                            clearNewErrors(['total']);
                                            setNewRow((current) => ({
                                                ...current,
                                                total: event.target.value,
                                                total_manual: true,
                                            }));
                                        }}
                                        placeholder={formatDecimal(assignedAmount)}
                                        className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-right text-xs text-text-main"
                                    />
                                    {newErrors.total && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.total}</span>}
                                </label>

                                {isMoeve && (
                                    <>
                                        <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                            Nº CCP
                                            <input
                                                value={newRow.factura_ccp}
                                                onChange={(event) => updateNewRow('factura_ccp', event.target.value)}
                                                className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                            />
                                            {newErrors.factura_ccp && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.factura_ccp}</span>}
                                        </label>

                                        <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                            Sociedad Moeve
                                            <input
                                                value={newRow.sociedad}
                                                onChange={(event) => updateNewRow('sociedad', event.target.value)}
                                                className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                            />
                                        </label>
                                    </>
                                )}

                                {isRepsol && (
                                    <>
                                        <label className="min-w-0 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                            Orden
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                value={newRow.orden_factura}
                                                onChange={(event) => updateNewRow('orden_factura', event.target.value)}
                                                className="mt-1 h-10 w-full rounded border border-border bg-surface px-2 text-xs text-text-main"
                                            />
                                            {newErrors.orden_factura && <span className="mt-1 block normal-case tracking-normal text-red-600">{newErrors.orden_factura}</span>}
                                        </label>

                                        <label className="flex items-end gap-2 text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                            <input
                                                type="checkbox"
                                                checked={newRow.autofactura}
                                                onChange={(event) => updateNewRow('autofactura', event.target.checked)}
                                                className="mb-3 rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                            />
                                            <span className="pb-3">Autofactura</span>
                                        </label>
                                    </>
                                )}

                                <div className="grid gap-3 sm:grid-cols-3">
                                    <div className="rounded border border-border bg-surface px-3 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Suma items</p>
                                        <p className="mt-1 text-sm font-semibold text-text-main">{fmtMoney(assignedAmount)}</p>
                                    </div>
                                    <div className="rounded border border-border bg-surface px-3 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Total factura</p>
                                        <p className="mt-1 text-sm font-semibold text-text-main">{fmtMoney(invoiceTotal)}</p>
                                    </div>
                                    <div className="rounded border border-border bg-surface px-3 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-wide text-text-hint">Diferencia</p>
                                        <p className={`mt-1 text-sm font-semibold ${Math.abs(invoiceDifference) < 0.01 ? 'text-green-700' : 'text-(--ciete-red)'}`}>
                                            {fmtMoney(invoiceDifference)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {newErrors.form && (
                        <p className="mt-3 text-xs font-semibold text-red-600">{newErrors.form}</p>
                    )}

                    <div className="mt-3 flex justify-end gap-2">
                        <button
                            type="button"
                            onClick={() => {
                                setCreateSearches({
                                    contrato: '',
                                    empresa: '',
                                    trabajo: '',
                                    pedido: '',
                                    item: '',
                                });
                                setItemToAdd('');
                                setNewRow(null);
                                setNewErrors({});
                            }}
                            className="rounded border border-border px-3 py-2 text-xs font-semibold text-text-main hover:bg-surface-2"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={savingNew}
                            className="rounded bg-(--ciete-red) px-3 py-2 text-xs font-semibold text-white hover:bg-(--ciete-red-dark) disabled:opacity-60"
                        >
                            {savingNew ? 'Guardando...' : 'Guardar factura'}
                        </button>
                    </div>
                </form>
            )}

            {/* Tabla densa */}
            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="ciete-excel-table w-full min-w-[800px] divide-y divide-border text-xs">
                    <thead className="bg-surface-2">
                        <tr>
                            {canExport && (
                                <th className="w-10 whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint">
                                    <input
                                        type="checkbox"
                                        checked={allVisibleSelected}
                                        onChange={onToggleAllVisible}
                                        aria-label="Exportar seleccionadas"
                                        className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                    />
                                </th>
                            )}
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    className={`${col.width} whitespace-nowrap px-2 py-2 text-left font-semibold uppercase tracking-wide text-text-hint`}
                                >
                                    {col.label}
                                    {col.badge === 'M' && (
                                        <span className="ml-1 rounded bg-blue-100 px-1 text-[9px] font-bold text-blue-700">M</span>
                                    )}
                                    {col.badge === 'R' && (
                                        <span className="ml-1 rounded bg-red-100 px-1 text-[9px] font-bold text-red-700">R</span>
                                    )}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {visibleFacturas.length === 0 && (
                            <tr>
                                <td colSpan={columns.length + (canExport ? 1 : 0)} className="px-3 py-10 text-center text-text-hint">
                                    No hay facturas con los filtros aplicados.
                                </td>
                            </tr>
                        )}
                        {visibleFacturas.map((factura) => {
                            const isCancelled = factura.estado === 'anulada';
                            const sociedadFacturadora = factura.empresa_facturadora ?? factura.empresa ?? null;

                            return (
                                <tr
                                    key={factura.id_factura}
                                    className={`hover:bg-surface-2/50 ${isCancelled ? 'opacity-50' : ''}`}
                                >
                                    {canExport && (
                                        <td className="px-2 py-1.5">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.includes(factura.id_factura)}
                                                onChange={() => onToggleSelected?.(factura.id_factura)}
                                                aria-label={`Exportar factura ${factura.numero_factura ?? factura.id_factura}`}
                                                className="rounded border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                                            />
                                        </td>
                                    )}
                                    {/* Estado */}
                                    <td className="px-2 py-1.5">
                                        <EditableEstadoCell
                                            factura={factura}
                                            canEdit={canEdit}
                                            onPatched={handleFacturaPatched}
                                        />
                                    </td>

                                    {/* Nº factura — protagonista */}
                                    <td className="ciete-dark-table-accent w-[11rem] max-w-[11rem] whitespace-nowrap px-2 py-1.5 font-mono font-semibold">
                                        <EditableTextCell
                                            factura={factura}
                                            fieldName="numero_factura"
                                            canEdit={canEdit}
                                            onPatched={handleFacturaPatched}
                                            className="ciete-dark-table-accent max-w-[10rem] font-mono font-semibold"
                                        />
                                    </td>

                                    {/* Fecha emisión */}
                                    <td className="w-[7rem] whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        <EditableTextCell
                                            factura={factura}
                                            fieldName="fecha_emision"
                                            canEdit={canEdit}
                                            onPatched={handleFacturaPatched}
                                            inputType="date"
                                            className="text-text-muted"
                                            formatDisplay={fmtDate}
                                        />
                                    </td>

                                    {/* Sociedad — nombre de la empresa cliente (prominente) */}
                                    <td className="max-w-[240px] overflow-hidden px-2 py-1.5" title={sociedadFacturadora?.nombre_comercial ?? sociedadFacturadora?.nombre ?? ''}>
                                        <EditableSociedadCell
                                            factura={factura}
                                            canEdit={canEdit}
                                            empresasFacturadoras={empresasFacturadoras}
                                            onPatched={handleFacturaPatched}
                                        />
                                    </td>

                                    {/* CIF — prominente, en mono rojo */}
                                    <td className="whitespace-nowrap px-2 py-1.5">
                                        <span className="ciete-dark-table-accent font-mono font-semibold">
                                            {sociedadFacturadora?.cif ?? '—'}
                                        </span>
                                    </td>

                                    {/* Contrato/tarifa */}
                                    <td className="max-w-[144px] truncate px-2 py-1.5 text-text-muted" title={factura.contrato?.nombre ?? factura.contrato?.codigo_contrato ?? ''}>
                                        {factura.contrato?.codigo_contrato ?? '—'}
                                    </td>

                                    {/* Importe */}
                                    <td className="whitespace-nowrap px-2 py-1.5 text-right font-medium text-text-main">
                                        <EditableTextCell
                                            factura={factura}
                                            fieldName="total"
                                            canEdit={canEdit}
                                            onPatched={handleFacturaPatched}
                                            inputType="number"
                                            numberStep="0.01"
                                            numberMin="0"
                                            className="font-medium text-text-main"
                                            formatDisplay={fmtMoney}
                                            payloadBuilder={(value) => ({
                                                total: value !== null ? Number(value) : 0,
                                                base_imponible: value !== null ? Number(value) : 0,
                                                importe: value !== null ? Number(value) : 0,
                                            })}
                                        />
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-right text-text-muted">
                                        {fmtMoney(factura.importe_asignado)}
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-right font-medium text-text-main">
                                        {fmtMoney(factura.diferencia)}
                                    </td>

                                    <td className="whitespace-nowrap px-2 py-1.5 text-text-muted">
                                        {fmt(factura.estado_cuadre)}
                                    </td>

                                    {/* MOEVE: CCP + sociedad facturadora */}
                                    {isMoeve && (
                                        <>
                                            <td className="whitespace-nowrap px-2 py-1.5 font-mono text-text-muted">
                                                <EditableTextCell
                                                    factura={factura}
                                                    fieldName="numero_factura_ccp"
                                                    canEdit={canEdit}
                                                    onPatched={handleFacturaPatched}
                                                    className="max-w-[130px] font-mono text-text-muted"
                                                />
                                            </td>
                                            <td className="max-w-[112px] truncate px-2 py-1.5 text-text-muted" title={factura.sociedad ?? ''}>
                                                <EditableTextCell
                                                    factura={factura}
                                                    fieldName="sociedad"
                                                    canEdit={canEdit}
                                                    onPatched={handleFacturaPatched}
                                                    className="max-w-[130px] text-text-muted"
                                                />
                                            </td>
                                        </>
                                    )}

                                    {/* REPSOL: orden + autofactura */}
                                    {isRepsol && (
                                        <>
                                            <td className="px-2 py-1.5 text-center">
                                                <EditableTextCell
                                                    factura={factura}
                                                    fieldName="orden_factura"
                                                    canEdit={canEdit}
                                                    onPatched={handleFacturaPatched}
                                                    inputType="number"
                                                    numberStep="1"
                                                    numberMin="1"
                                                    className="text-center font-bold text-red-700"
                                                    payloadBuilder={(value) => ({
                                                        orden_factura: value !== null ? Number(value) : null,
                                                    })}
                                                />
                                            </td>
                                            <td className="px-2 py-1.5 text-center">
                                                <EditableAutofacturaCell
                                                    factura={factura}
                                                    canEdit={canEdit}
                                                    onPatched={handleFacturaPatched}
                                                />
                                            </td>
                                        </>
                                    )}

                                    {/* Acciones */}
                                    <td className="px-2 py-1.5">
                                        <div className="flex flex-wrap gap-2">
                                        {canExport && (
                                            <button
                                                type="button"
                                                onClick={() => onExportDetail?.(factura.id_factura)}
                                                className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                            >
                                                Exportar
                                            </button>
                                        )}
                                        {canEdit && (
                                            <button
                                                type="button"
                                                onClick={() => router.visit(route('facturas.edit', factura.id_factura))}
                                                className="text-xs font-medium text-text-muted hover:text-(--ciete-red)"
                                            >
                                                Ver ficha
                                            </button>
                                        )}
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            <PaginationControls
                pagination={pagination}
                onPageChange={(p) => doFilter({ page: p })}
                entityLabel="facturas"
            />
        </div>
    );
}
