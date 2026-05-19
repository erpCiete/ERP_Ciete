import InputError from '@/Components/InputError';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import BadgeCliente from '@/Components/ui/BadgeCliente';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useMemo, useState } from 'react';

const CLIENT_THEME = {
    moeve: {
        key: 'moeve',
        label: 'Moeve',
        tone: 'var(--color-client-moeve)',
    },
    repsol: {
        key: 'repsol',
        label: 'Repsol',
        tone: 'var(--color-client-repsol)',
    },
    otros: {
        key: 'otros',
        label: 'OTROS CLIENTES',
        tone: 'var(--color-client-others)',
    },
};

const EMPTY_FORM = {
    id_contexto: '',
    numero_factura: '',
    id_trabajo: '',
    id_empresa_facturadora: '',
    fecha_emision: new Date().toISOString().split('T')[0],
    estado: 'pendiente',
    total: '',
    base_imponible: '',
    iva: '',
    factura_ccp: '',
    sociedad: '',
    orden_factura: '',
    autofactura: false,
};

const FACTURA_STATUS_OPTIONS = ['pendiente', 'solicitada', 'emitida', 'enviada', 'anulada'];

function toNumber(value, fallback = 0) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function fmtMoney(value) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(toNumber(value));
}

function contractIdsForEmpresa(empresa) {
    const ids = Array.isArray(empresa?.id_contratos)
        ? empresa.id_contratos
        : empresa?.id_contrato
          ? [empresa.id_contrato]
          : [];

    return ids.map((id) => Number(id)).filter((id) => Number.isFinite(id));
}

function formatWorkNumber(value) {
    if (value === null || value === undefined || value === '') return '-';
    const text = String(value);
    return /^\d+$/.test(text) ? text.padStart(4, '0') : text;
}

function workNumberForDisplay(item) {
    return item?.numero_trabajo_visible ?? item?.numero_trabajo_operativo ?? item?.numero_trabajo ?? '-';
}

function normalizeFactura(factura) {
    if (!factura) return EMPTY_FORM;

    const item = factura.data || factura;

    return {
        id_contexto: item.id_contexto ? String(item.id_contexto) : '',
        numero_factura: item.numero_factura ?? '',
        id_trabajo: item.id_trabajo ? String(item.id_trabajo) : '',
        id_empresa_facturadora: item.id_empresa_facturadora ? String(item.id_empresa_facturadora) : '',
        fecha_emision: item.fecha_emision ?? new Date().toISOString().split('T')[0],
        estado: item.estado ?? 'pendiente',
        total: item.total ?? '',
        base_imponible: item.base_imponible ?? '',
        iva: item.iva ?? '',
        factura_ccp: item.factura_ccp ?? item.numero_factura_ccp ?? '',
        sociedad: item.sociedad ?? '',
        orden_factura: item.orden_factura != null ? String(item.orden_factura) : '',
        autofactura: item.autofactura ?? false,
    };
}

function resolveClientKey(context) {
    const value = String(context?.codigo || context?.nombre || '').trim().toLowerCase();

    if (value.includes('moeve')) return 'moeve';
    if (value.includes('repsol')) return 'repsol';
    if (value.includes('otro') || value.includes('otros')) return 'otros';

    return null;
}

function selectedTheme(clientKey) {
    return CLIENT_THEME[clientKey] ?? null;
}

function availableDisplay(item) {
    return {
        id_contexto: item.id_contexto,
        id_pedido: item.id_pedido,
        numero_pedido: item.numero_pedido ?? '-',
        id_trabajo: item.id_trabajo,
        numero_trabajo: item.numero_trabajo ?? '-',
        numero_trabajo_operativo: item.numero_trabajo_operativo ?? null,
        numero_trabajo_visible: workNumberForDisplay(item),
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
        id_factura_item: null,
        id_pedido_item: item.id_pedido_item,
        unidades_facturadas: display.unidades_pendientes > 0 ? String(display.unidades_pendientes) : '',
        importe_facturado: String(display.importe_pendiente),
        observaciones: '',
        display,
    };
}

function normalizeFacturaItems(factura, availableItems) {
    if (!factura) return [];

    const item = factura.data || factura;
    const availableMap = new Map((availableItems || []).map((available) => [Number(available.id_pedido_item), available]));

    return (item.items || []).map((line) => {
        const pedidoItemId = Number(line.id_pedido_item);
        const available = availableMap.get(pedidoItemId);
        const pedidoItem = line.pedido_item || {};
        const pedido = line.pedido || {};
        const trabajo = line.trabajo || {};
        const display = available
            ? availableDisplay(available)
            : {
                  id_contexto: pedidoItem.id_contexto ?? item.id_contexto,
                  id_pedido: pedido.id_pedido ?? pedidoItem.id_pedido,
                  numero_pedido: pedido.numero_pedido ?? '-',
                  id_trabajo: trabajo.id_trabajo ?? pedido.id_trabajo,
                  numero_trabajo: trabajo.numero_trabajo ?? '-',
                  numero_trabajo_operativo: trabajo.numero_trabajo_operativo ?? null,
                  numero_trabajo_visible: workNumberForDisplay(trabajo),
                  descripcion_trabajo: trabajo.descripcion_trabajo ?? '',
                  codigo_estacion: trabajo.codigo_estacion ?? '',
                  id_contrato: trabajo.id_contrato ?? line.tarifario?.id_contrato ?? item.id_contrato ?? null,
                  id_tarifario: line.tarifario?.id_tarifario ?? pedido.id_tarifario ?? item.id_tarifario ?? null,
                  codigo_servicio: pedidoItem.codigo_servicio ?? '',
                  numero_tarifa: pedidoItem.numero_tarifa ?? '',
                  descripcion_servicio: pedidoItem.descripcion_servicio ?? '',
                  cantidad: toNumber(pedidoItem.cantidad),
                  total_linea: toNumber(pedidoItem.total_linea),
                  unidades_pendientes: toNumber(line.unidades_facturadas),
                  importe_pendiente: toNumber(line.importe_facturado),
              };

        return {
            id_factura_item: line.id_factura_item ?? null,
            id_pedido_item: pedidoItemId,
            unidades_facturadas: line.unidades_facturadas != null ? String(line.unidades_facturadas) : '',
            importe_facturado: line.importe_facturado != null ? String(line.importe_facturado) : '0',
            observaciones: line.observaciones ?? '',
            display,
        };
    });
}

function validarForm({
    form,
    invoiceItems,
    trabajos,
    allowClientSelection,
    isMoeve,
    isRepsol,
    isEditing,
    availableEmpresasFacturadoras,
    t,
}) {
    const errs = {};
    const reqMsg = t('common.validation.required');
    const selectedTrabajo = trabajos.find((item) => String(item.id_trabajo) === String(form.id_trabajo));

    if (allowClientSelection && !form.id_contexto) {
        errs.id_contexto = reqMsg;
    }

    if (['emitida', 'enviada'].includes(form.estado) && !form.numero_factura?.trim()) {
        errs.numero_factura = reqMsg;
    }

    if (
        selectedTrabajo &&
        form.id_contexto &&
        String(selectedTrabajo.id_contexto) !== String(form.id_contexto)
    ) {
        errs.id_trabajo = t('trabajos.clientSelector.workMismatch');
    }

    if (!form.fecha_emision) {
        errs.fecha_emision = reqMsg;
    }

    if (!form.estado) {
        errs.estado = reqMsg;
    }

    if (!isEditing && invoiceItems.length === 0) {
        errs.items = 'Selecciona al menos un item de pedido.';
    }

    if (invoiceItems.length > 0 && availableEmpresasFacturadoras.length === 0) {
        errs.id_empresa_facturadora =
            'No hay sociedades facturadoras permitidas con CIF registrado para el contrato o tarifa de los items seleccionados.';
    }

    if (form.id_empresa_facturadora && availableEmpresasFacturadoras.length > 0) {
        const selectedEmpresa = availableEmpresasFacturadoras.find(
            (e) => String(e.id_empresa) === String(form.id_empresa_facturadora),
        );

        if (selectedEmpresa && !selectedEmpresa.cif) {
            errs.id_empresa_facturadora = 'La sociedad seleccionada no tiene CIF registrado.';
        }
    }

    // Validar empresa facturadora si hay items y hay opciones disponibles
    if (invoiceItems.length > 0 && availableEmpresasFacturadoras.length > 0 && !form.id_empresa_facturadora) {
        errs.id_empresa_facturadora = 'Selecciona la sociedad/CIF que emite la factura.';
    }

    if (
        form.id_empresa_facturadora &&
        availableEmpresasFacturadoras.length > 0 &&
        !availableEmpresasFacturadoras.some((e) => String(e.id_empresa) === String(form.id_empresa_facturadora))
    ) {
        errs.id_empresa_facturadora =
            'La sociedad/CIF seleccionada no está permitida para el contrato o tarifa de los ítems facturados.';
    }

    invoiceItems.forEach((line, index) => {
        const amount = toNumber(line.importe_facturado, NaN);
        const units = line.unidades_facturadas === '' ? null : toNumber(line.unidades_facturadas, NaN);

        if (!Number.isFinite(amount) || amount < 0) {
            errs[`items.${index}.importe_facturado`] = 'Importe no valido.';
        } else if (amount > toNumber(line.display?.importe_pendiente) + 0.01) {
            errs[`items.${index}.importe_facturado`] = `Pendiente maximo: ${fmtMoney(line.display?.importe_pendiente)}.`;
        }

        if (units !== null && (!Number.isFinite(units) || units < 0)) {
            errs[`items.${index}.unidades_facturadas`] = 'Unidades no validas.';
        } else if (units !== null && units > toNumber(line.display?.unidades_pendientes) + 0.001) {
            errs[`items.${index}.unidades_facturadas`] = `Pendiente maximo: ${line.display?.unidades_pendientes}.`;
        }
    });

    if (isMoeve && !form.factura_ccp?.trim()) {
        errs.factura_ccp = reqMsg;
    }

    if (isRepsol && !form.orden_factura) {
        errs.orden_factura = reqMsg;
    }

    return errs;
}

export default function FacturasForm({
    factura = null,
    trabajos = [],
    contextoIds = [],
    clientContexts = [],
    ordenesUsadas = {},
    pedidoItemsFacturables = [],
    empresasFacturadoras = {},
}) {
    const { t } = useI18n();
    const { props } = usePage();
    const isAllContext = props.auth?.user?.active_context?.is_all ?? false;

    const isEditing = factura !== null;
    const pageTitle = isEditing ? t('facturas.edit') : t('facturas.create');
    const normalizedFactura = useMemo(() => normalizeFactura(factura), [factura]);
    const normalizedExistingItems = useMemo(
        () => normalizeFacturaItems(factura, pedidoItemsFacturables),
        [factura, pedidoItemsFacturables],
    );
    const originalItemsCount = normalizedExistingItems.length;

    const availableClientContexts = useMemo(
        () =>
            (clientContexts || [])
                .map((context) => {
                    const key = resolveClientKey(context);
                    return key ? { ...context, clientKey: key } : null;
                })
                .filter(Boolean),
        [clientContexts],
    );
    const allowClientSelection = availableClientContexts.length > 1;
    const defaultContextId = availableClientContexts[0]?.id_contexto
        ? String(availableClientContexts[0].id_contexto)
        : '';

    const [form, setForm] = useState(() => ({
        ...normalizedFactura,
        id_contexto: normalizedFactura.id_contexto || (allowClientSelection ? '' : defaultContextId),
    }));
    const [invoiceItems, setInvoiceItems] = useState(normalizedExistingItems);
    const [itemSearch, setItemSearch] = useState('');
    const [serverErrors, setServerErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError, setSubmitError] = useState('');

    useEffect(() => {
        setForm({
            ...normalizedFactura,
            id_contexto: normalizedFactura.id_contexto || (allowClientSelection ? '' : defaultContextId),
        });
        setInvoiceItems(normalizedExistingItems);
    }, [normalizedFactura, normalizedExistingItems, allowClientSelection, defaultContextId]);

    const selectedContext = useMemo(
        () => availableClientContexts.find((context) => String(context.id_contexto) === String(form.id_contexto)) ?? null,
        [availableClientContexts, form.id_contexto],
    );
    const selectedClientKey = selectedContext?.clientKey ?? null;
    const activeTheme = selectedTheme(selectedClientKey);
    const hasOperationalClientAccess = availableClientContexts.length > 0;
    const filteredTrabajos = useMemo(() => {
        if (!selectedContext) {
            return allowClientSelection ? [] : trabajos || [];
        }

        return (trabajos || []).filter(
            (item) => String(item.id_contexto) === String(selectedContext.id_contexto),
        );
    }, [allowClientSelection, trabajos, selectedContext]);
    const isMoeve = selectedClientKey === 'moeve';
    const isRepsol = selectedClientKey === 'repsol';
    const contextEmpresasFacturadoras = useMemo(
        () => (empresasFacturadoras[String(form.id_contexto)] ?? []),
        [empresasFacturadoras, form.id_contexto],
    );
    const selectedContractIds = useMemo(
        () =>
            Array.from(
                new Set(
                    invoiceItems
                        .map((line) => Number(line.display?.id_contrato))
                        .filter((id) => Number.isFinite(id) && id > 0),
                ),
            ),
        [invoiceItems],
    );
    const availableEmpresasFacturadoras = useMemo(() => {
        if (selectedContractIds.length === 0) {
            return invoiceItems.length > 0 ? [] : contextEmpresasFacturadoras;
        }

        return contextEmpresasFacturadoras.filter((empresa) => {
            const contractIds = contractIdsForEmpresa(empresa);

            return selectedContractIds.every((id) => contractIds.includes(id));
        });
    }, [contextEmpresasFacturadoras, invoiceItems.length, selectedContractIds]);
    useEffect(() => {
        if (!form.id_empresa_facturadora || invoiceItems.length === 0) {
            return;
        }

        const stillAllowed = availableEmpresasFacturadoras.some(
            (empresa) => String(empresa.id_empresa) === String(form.id_empresa_facturadora),
        );

        if (!stillAllowed) {
            setForm((prev) => ({ ...prev, id_empresa_facturadora: '' }));
        }
    }, [availableEmpresasFacturadoras, form.id_empresa_facturadora, invoiceItems.length]);
    const selectedPedidoItemIds = useMemo(
        () => new Set(invoiceItems.map((item) => Number(item.id_pedido_item))),
        [invoiceItems],
    );
    const selectedEmpresaFacturadora = useMemo(
        () =>
            contextEmpresasFacturadoras.find(
                (empresa) => String(empresa.id_empresa) === String(form.id_empresa_facturadora),
            ) ?? null,
        [contextEmpresasFacturadoras, form.id_empresa_facturadora],
    );
    const availablePedidoItems = useMemo(() => {
        const query = itemSearch.trim().toLowerCase();
        const empresaContractIds = contractIdsForEmpresa(selectedEmpresaFacturadora);

        return (pedidoItemsFacturables || [])
            .filter((item) => !selectedContext || String(item.id_contexto) === String(selectedContext.id_contexto))
            .filter((item) => !form.id_trabajo || String(item.id_trabajo) === String(form.id_trabajo))
            .filter((item) => !selectedPedidoItemIds.has(Number(item.id_pedido_item)))
            .filter((item) => {
                if (!selectedEmpresaFacturadora) return true;

                const itemContractId = Number(item.id_contrato);

                return Number.isFinite(itemContractId) && empresaContractIds.includes(itemContractId);
            })
            .filter((item) => {
                if (!query) return true;

                return [
                    item.numero_pedido,
                    item.numero_trabajo,
                    item.numero_trabajo_operativo,
                    item.numero_trabajo_visible,
                    item.descripcion_trabajo,
                    item.codigo_servicio,
                    item.numero_tarifa,
                    item.descripcion_servicio,
                    item.codigo_estacion,
                ]
                    .join(' ')
                    .toLowerCase()
                    .includes(query);
            })
            .slice(0, 25);
    }, [form.id_trabajo, itemSearch, pedidoItemsFacturables, selectedContext, selectedPedidoItemIds, selectedEmpresaFacturadora]);
    const contextPedidoItemsCount = useMemo(
        () =>
            (pedidoItemsFacturables || []).filter(
                (item) => !selectedContext || String(item.id_contexto) === String(selectedContext.id_contexto),
            ).length,
        [pedidoItemsFacturables, selectedContext],
    );
    const assignedAmount = useMemo(
        () => invoiceItems.reduce((sum, line) => sum + toNumber(line.importe_facturado), 0),
        [invoiceItems],
    );
    const invoiceTotal = form.total !== '' ? toNumber(form.total) : assignedAmount;
    const difference = invoiceTotal - assignedAmount;
    const localErrors = useMemo(
        () =>
            validarForm({
                form,
                invoiceItems,
                trabajos: filteredTrabajos,
                allowClientSelection,
                isMoeve,
                isRepsol,
                isEditing,
                availableEmpresasFacturadoras,
                t,
            }),
        [form, invoiceItems, filteredTrabajos, allowClientSelection, isMoeve, isRepsol, isEditing, availableEmpresasFacturadoras, t],
    );

    const ordenesDelTrabajo = form.id_trabajo
        ? (ordenesUsadas[form.id_trabajo] ?? [])
              .map((orden) => Number(orden))
              .filter((orden) => Number.isInteger(orden) && orden > 0)
              .filter((orden) =>
                  isEditing ? String(orden) !== String((factura.data || factura)?.orden_factura) : true,
              )
              .sort((a, b) => a - b)
        : [];
    const siguienteOrdenSugerida = ordenesDelTrabajo.length > 0
        ? Math.max(...ordenesDelTrabajo) + 1
        : 1;

    const updateField = (field, value) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
        setForm((prev) => ({ ...prev, [field]: value }));
    };

    const selectClient = (contextId) => {
        setTouched((prev) => ({ ...prev, id_contexto: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_contexto;
            delete next.id_trabajo;
            delete next.factura_ccp;
            delete next.orden_factura;
            delete next.items;
            delete next.id_empresa_facturadora;
            return next;
        });
        setForm((prev) => ({
            ...prev,
            id_contexto: String(contextId),
            id_trabajo: '',
            factura_ccp: '',
            sociedad: '',
            orden_factura: '',
            autofactura: false,
            id_empresa_facturadora: '',
        }));
        setInvoiceItems([]);
        setItemSearch('');
    };

    const handleTrabajoChange = (value) => {
        setTouched((prev) => ({ ...prev, id_trabajo: true, orden_factura: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_trabajo;
            delete next.orden_factura;
            delete next.items;
            delete next.id_empresa_facturadora;
            return next;
        });
        setForm((prev) => ({
            ...prev,
            id_trabajo: value,
            orden_factura: '',
            id_empresa_facturadora: '',
        }));
        setInvoiceItems([]);
        setItemSearch('');
    };

    const addInvoiceItem = (item) => {
        const line = makeLineFromAvailable(item);
        setInvoiceItems((prev) => [...prev, line]);
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.items;
            return next;
        });

        if (!form.id_trabajo && item.id_trabajo) {
            setForm((prev) => ({ ...prev, id_trabajo: String(item.id_trabajo) }));
        }
    };

    const updateInvoiceLine = (index, field, value) => {
        setInvoiceItems((prev) =>
            prev.map((line, lineIndex) => (lineIndex === index ? { ...line, [field]: value } : line)),
        );
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next[`items.${index}.${field}`];
            delete next.items;
            return next;
        });
    };

    const removeInvoiceLine = (index) => {
        setInvoiceItems((prev) => prev.filter((_, lineIndex) => lineIndex !== index));
    };

    const getError = (field) => {
        if (!submitAttempted && !touched[field] && !serverErrors[field]) return '';
        const serverMessage = Array.isArray(serverErrors[field]) ? serverErrors[field][0] : serverErrors[field];
        return localErrors[field] ?? serverMessage ?? '';
    };

    const inputClass = (field) =>
        `w-full rounded-md border px-3 py-2 text-sm bg-surface text-text-main focus:outline-none focus:ring-1 ${
            getError(field)
                ? 'border-red-500 focus:border-red-500 focus:ring-red-300'
                : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'
        }`;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitAttempted(true);
        setSubmitError('');

        const allTouched = Object.keys(EMPTY_FORM).reduce((acc, key) => ({ ...acc, [key]: true }), {});
        setTouched({ ...allTouched, items: true });

        if (!hasOperationalClientAccess || Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            const firstError = Object.keys(localErrors)[0];
            if (firstError) {
                document.getElementById(firstError.replaceAll('.', '-'))?.focus();
            }
            return;
        }

        setLoading(true);

        try {
            const totalValue = form.total !== '' ? toNumber(form.total) : assignedAmount;
            const baseValue = form.base_imponible !== '' ? toNumber(form.base_imponible) : totalValue;
            const payload = {
                numero_factura: form.numero_factura || null,
                id_empresa_facturadora: form.id_empresa_facturadora ? Number(form.id_empresa_facturadora) : null,
                fecha_emision: form.fecha_emision,
                estado: form.estado,
                total: totalValue,
                base_imponible: baseValue,
                iva: form.iva !== '' ? toNumber(form.iva) : 0,
            };

            if (invoiceItems.length > 0 || originalItemsCount > 0 || !isEditing) {
                payload.items = invoiceItems.map((line) => ({
                    ...(line.id_factura_item ? { id_factura_item: Number(line.id_factura_item) } : {}),
                    id_pedido_item: Number(line.id_pedido_item),
                    unidades_facturadas: line.unidades_facturadas !== '' ? toNumber(line.unidades_facturadas) : null,
                    importe_facturado: toNumber(line.importe_facturado),
                    observaciones: line.observaciones || null,
                }));
            }

            if (isMoeve) {
                payload.factura_ccp = form.factura_ccp;
                payload.sociedad = form.sociedad;
            }

            if (isRepsol) {
                payload.orden_factura = Number(form.orden_factura);
                payload.autofactura = Boolean(form.autofactura);
            }

            if (isEditing) {
                await axios.put(`/api/v1/facturas/${(factura.data || factura).id_factura}`, payload);
            } else {
                await axios.post('/api/v1/facturas', payload);
            }

            router.visit(route('facturas.index'));
        } catch (err) {
            if (err.response?.status === 422) {
                setServerErrors(err.response.data?.errors ?? {});
                setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            } else {
                setSubmitError('Error al guardar la factura. Intentalo de nuevo.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}
        >
            <Head title={pageTitle} />

            <div className="ciete-page ciete-page-form">
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={pageTitle}
                    description={selectedClientKey ? t('trabajos.clientSelector.contextReady') : t('trabajos.clientSelector.intro')}
                />

                {!hasOperationalClientAccess && (
                    <section className="rounded-2xl border border-state-pending-dot/20 bg-state-pending-bg p-5 shadow-sm">
                        <p className="text-sm font-semibold text-state-pending-text">
                            {t('trabajos.clientSelector.noAccessTitle')}
                        </p>
                        <p className="mt-2 text-sm text-state-pending-text/85">
                            {t('trabajos.clientSelector.noAccessDescription')}
                        </p>
                    </section>
                )}

                {hasOperationalClientAccess && (
                    <form onSubmit={handleSubmit} noValidate className="space-y-6">
                        {!isEditing && isAllContext && (
                            <div className="rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <p className="text-sm font-medium text-amber-800">
                                    No es posible crear registros mientras el contexto activo es Todos. Selecciona un contexto específico en la barra superior.
                                </p>
                            </div>
                        )}

                        <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                            <div className="flex flex-col gap-4">
                                <div>
                                    <p className="text-sm font-semibold text-text-main">
                                        {t('trabajos.clientSelector.title')}
                                    </p>
                                    <p className="mt-1 text-sm text-text-muted">
                                        {t('trabajos.clientSelector.description')}
                                    </p>
                                </div>

                                {allowClientSelection ? (
                                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        {availableClientContexts.map((context) => {
                                            const theme = selectedTheme(context.clientKey);
                                            const isSelected = String(form.id_contexto) === String(context.id_contexto);

                                            return (
                                                <button
                                                    key={context.id_contexto}
                                                    type="button"
                                                    onClick={() => selectClient(context.id_contexto)}
                                                    className={`rounded-2xl border px-4 py-4 text-left transition ${
                                                        isSelected
                                                            ? 'border-transparent bg-surface-2 shadow-sm'
                                                            : 'border-border bg-surface hover:bg-surface-2'
                                                    }`}
                                                    style={
                                                        isSelected
                                                            ? { boxShadow: `inset 0 0 0 1px ${theme?.tone}` }
                                                            : undefined
                                                    }
                                                >
                                                    <div className="flex flex-col gap-3">
                                                        <p className="text-sm font-semibold text-text-main">
                                                            {theme?.label ?? context.nombre}
                                                        </p>
                                                        <p className="text-xs text-text-muted">
                                                            {t(`trabajos.clientSelector.${context.clientKey}.summary`)}
                                                        </p>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-border bg-surface-2 px-4 py-4">
                                        <div className="flex flex-wrap items-center gap-3">
                                            <BadgeCliente cliente={selectedClientKey} label={activeTheme?.label} />
                                            <p className="text-sm text-text-muted">
                                                {t(`trabajos.clientSelector.${selectedClientKey}.auto`)}
                                            </p>
                                        </div>
                                    </div>
                                )}

                                <InputError message={getError('id_contexto')} className="mt-1" />
                            </div>
                        </section>

                        {!selectedClientKey && allowClientSelection && (
                            <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                <p className="text-sm text-text-muted">{t('trabajos.clientSelector.pendingSelection')}</p>
                            </section>
                        )}

                        {selectedClientKey && (
                            <>
                                <fieldset className="space-y-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                    <legend className="px-1 text-sm font-semibold text-text-main">
                                        Datos principales
                                    </legend>

                                    <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border bg-surface-2 px-4 py-3">
                                        <div>
                                            <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-text-hint">
                                                {t('trabajos.clientSelector.contextLabel')}
                                            </p>
                                            <p className="mt-1 text-sm font-semibold text-text-main">
                                                {t(`trabajos.clientSelector.${selectedClientKey}.title`)}
                                            </p>
                                        </div>
                                        <BadgeCliente cliente={selectedClientKey} label={activeTheme?.label} />
                                    </div>

                                    <div className="grid gap-5 md:grid-cols-2">
                                        <div className="md:col-span-2">
                                            <label htmlFor="id_trabajo" className="mb-1.5 block text-sm font-medium text-text-main">
                                                Trabajo principal
                                                <span className="ml-1 text-xs font-normal text-text-hint">(legacy)</span>
                                            </label>
                                            <select
                                                id="id_trabajo"
                                                value={form.id_trabajo}
                                                onChange={(e) => handleTrabajoChange(e.target.value)}
                                                className={inputClass('id_trabajo')}
                                            >
                                                <option value="">Se inferira desde los items seleccionados</option>
                                                {filteredTrabajos.map((tr) => (
                                                    <option key={tr.id_trabajo} value={tr.id_trabajo}>
                                                        {formatWorkNumber(workNumberForDisplay(tr))} - {tr.descripcion_trabajo}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={getError('id_trabajo')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label htmlFor="numero_factura" className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('facturas.fields.numero')}
                                                {['emitida', 'enviada'].includes(form.estado) && <span className="text-red-500"> *</span>}
                                            </label>
                                            <input
                                                id="numero_factura"
                                                type="text"
                                                value={form.numero_factura}
                                                onChange={(e) => updateField('numero_factura', e.target.value)}
                                                className={inputClass('numero_factura')}
                                                placeholder="Ej: FAC-001"
                                            />
                                            <InputError message={getError('numero_factura')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label htmlFor="estado" className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('facturas.fields.estado')} <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="estado"
                                                value={form.estado}
                                                onChange={(e) => updateField('estado', e.target.value)}
                                                className={inputClass('estado')}
                                            >
                                                {FACTURA_STATUS_OPTIONS.map((status) => (
                                                    <option key={status} value={status}>
                                                        {t(`facturas.status.${status}`)}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={getError('estado')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label htmlFor="fecha_emision" className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('facturas.fields.fechaEmision')} <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="fecha_emision"
                                                type="date"
                                                value={form.fecha_emision}
                                                onChange={(e) => updateField('fecha_emision', e.target.value)}
                                                className={inputClass('fecha_emision')}
                                            />
                                            <InputError message={getError('fecha_emision')} className="mt-1.5" />
                                        </div>

                                        {invoiceItems.length > 0 && availableEmpresasFacturadoras.length === 0 && (
                                            <div className="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                                                <p className="text-sm font-medium text-amber-800">
                                                    No hay sociedades facturadoras permitidas con CIF registrado para el contrato o tarifa de los items seleccionados.
                                                </p>
                                                <p className="mt-1 text-sm text-amber-700">
                                                    Revisa en Maestros la relacion contrato-sociedad permitida y comprueba que la empresa tenga CIF informado y este activa en el contexto actual.
                                                </p>
                                                <div className="mt-3 flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('maestros.sociedades.index'))}
                                                        className="rounded-md border border-amber-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 transition hover:bg-amber-100"
                                                    >
                                                        Abrir sociedades facturadoras
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('clientes.index'))}
                                                        className="rounded-md border border-amber-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 transition hover:bg-amber-100"
                                                    >
                                                        Revisar empresas / CIF
                                                    </button>
                                                </div>
                                                <InputError message={getError('id_empresa_facturadora')} className="mt-1.5" />
                                            </div>
                                        )}

                                        {availableEmpresasFacturadoras.length > 0 && (
                                            <div className="md:col-span-2">
                                                <label htmlFor="id_empresa_facturadora" className="mb-1.5 block text-sm font-medium text-text-main">
                                                    Sociedad / CIF facturadora
                                                    {invoiceItems.length > 0 && <span className="ml-1 text-red-500">*</span>}
                                                </label>
                                                <select
                                                    id="id_empresa_facturadora"
                                                    value={form.id_empresa_facturadora}
                                                    onChange={(e) => updateField('id_empresa_facturadora', e.target.value)}
                                                    className={inputClass('id_empresa_facturadora')}
                                                >
                                                    <option value="">— Selecciona sociedad —</option>
                                                    {availableEmpresasFacturadoras.map((emp) => (
                                                        <option key={emp.id_empresa} value={String(emp.id_empresa)}>
                                                            {emp.cif_label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <InputError message={getError('id_empresa_facturadora')} className="mt-1.5" />
                                                {form.id_empresa_facturadora && (() => {
                                                    const selected = availableEmpresasFacturadoras.find(
                                                        (e) => String(e.id_empresa) === String(form.id_empresa_facturadora)
                                                    );
                                                    return selected?.cif ? (
                                                        <p className="mt-1 text-xs text-text-hint">
                                                            CIF: <span className="font-mono font-semibold">{selected.cif}</span>
                                                            {selected.razon_social ? ` · ${selected.razon_social}` : ''}
                                                        </p>
                                                    ) : null;
                                                })()}
                                            </div>
                                        )}
                                    </div>
                                </fieldset>

                                <fieldset className="space-y-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                    <legend className="px-1 text-sm font-semibold text-text-main">Importes</legend>
                                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                        <div>
                                            <label htmlFor="base_imponible" className="mb-1.5 block text-sm font-medium text-text-main">
                                                Base imponible
                                            </label>
                                            <input
                                                id="base_imponible"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={form.base_imponible}
                                                onChange={(e) => updateField('base_imponible', e.target.value)}
                                                className={inputClass('base_imponible')}
                                                placeholder="0.00"
                                            />
                                        </div>

                                        <div>
                                            <label htmlFor="iva" className="mb-1.5 block text-sm font-medium text-text-main">
                                                IVA
                                            </label>
                                            <input
                                                id="iva"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={form.iva}
                                                onChange={(e) => updateField('iva', e.target.value)}
                                                className={inputClass('iva')}
                                                placeholder="0.00"
                                            />
                                        </div>

                                        <div>
                                            <label htmlFor="total" className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('facturas.fields.total')}
                                            </label>
                                            <input
                                                id="total"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={form.total}
                                                onChange={(e) => updateField('total', e.target.value)}
                                                className={inputClass('total')}
                                                placeholder={assignedAmount > 0 ? String(assignedAmount.toFixed(2)) : '0.00'}
                                            />
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset className="space-y-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                    <legend className="px-1 text-sm font-semibold text-text-main">
                                        Items facturables
                                    </legend>

                                    <div className="grid gap-3 md:grid-cols-3">
                                        <div className="rounded-xl border border-border bg-surface-2 px-4 py-3">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-text-hint">Asignado</p>
                                            <p className="mt-1 text-lg font-semibold text-text-main">{fmtMoney(assignedAmount)}</p>
                                        </div>
                                        <div className="rounded-xl border border-border bg-surface-2 px-4 py-3">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-text-hint">Total factura</p>
                                            <p className="mt-1 text-lg font-semibold text-text-main">{fmtMoney(invoiceTotal)}</p>
                                        </div>
                                        <div className="rounded-xl border border-border bg-surface-2 px-4 py-3">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-text-hint">Diferencia</p>
                                            <p className={`mt-1 text-lg font-semibold ${Math.abs(difference) <= 0.01 ? 'text-green-700' : 'text-amber-700'}`}>
                                                {fmtMoney(difference)}
                                            </p>
                                        </div>
                                    </div>

                                    <InputError message={getError('items')} className="mt-1" />

                                    <div className="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(280px,380px)] 2xl:grid-cols-[minmax(0,1fr)_minmax(320px,420px)]">
                                        <div className="overflow-x-auto rounded-xl border border-border">
                                            <table className="min-w-[760px] w-full divide-y divide-border text-sm">
                                                <thead className="bg-surface-2 text-left text-xs font-semibold uppercase tracking-wide text-text-hint">
                                                    <tr>
                                                        <th className="px-3 py-2">Pedido / Trabajo</th>
                                                        <th className="px-3 py-2">Servicio</th>
                                                        <th className="px-3 py-2 text-right">Unidades</th>
                                                        <th className="px-3 py-2 text-right">Importe</th>
                                                        <th className="px-3 py-2">Obs.</th>
                                                        <th className="px-3 py-2 text-right">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-border">
                                                    {invoiceItems.length === 0 && (
                                                        <tr>
                                                            <td colSpan={6} className="px-3 py-8 text-center text-sm text-text-muted">
                                                                No hay items seleccionados.
                                                            </td>
                                                        </tr>
                                                    )}
                                                    {invoiceItems.map((line, index) => (
                                                        <tr key={`${line.id_factura_item ?? 'new'}-${line.id_pedido_item}`} className="align-top">
                                                            <td className="px-3 py-3">
                                                                <p className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                                    {line.display.numero_pedido}
                                                                </p>
                                                                <p className="mt-1 text-xs text-text-muted">
                                                                    Trabajo {formatWorkNumber(line.display.numero_trabajo_visible)}
                                                                </p>
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <p className="font-medium text-text-main">
                                                                    {line.display.descripcion_servicio || line.display.codigo_servicio || '-'}
                                                                </p>
                                                                <p className="mt-1 text-xs text-text-hint">
                                                                    Pendiente {fmtMoney(line.display.importe_pendiente)}
                                                                </p>
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <input
                                                                    id={`items-${index}-unidades_facturadas`}
                                                                    type="number"
                                                                    min="0"
                                                                    step="0.001"
                                                                    value={line.unidades_facturadas}
                                                                    onChange={(e) => updateInvoiceLine(index, 'unidades_facturadas', e.target.value)}
                                                                    className={`${inputClass(`items.${index}.unidades_facturadas`)} text-right`}
                                                                />
                                                                <InputError message={getError(`items.${index}.unidades_facturadas`)} className="mt-1" />
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <input
                                                                    id={`items-${index}-importe_facturado`}
                                                                    type="number"
                                                                    min="0"
                                                                    step="0.01"
                                                                    value={line.importe_facturado}
                                                                    onChange={(e) => updateInvoiceLine(index, 'importe_facturado', e.target.value)}
                                                                    className={`${inputClass(`items.${index}.importe_facturado`)} text-right`}
                                                                />
                                                                <InputError message={getError(`items.${index}.importe_facturado`)} className="mt-1" />
                                                            </td>
                                                            <td className="px-3 py-3">
                                                                <input
                                                                    type="text"
                                                                    value={line.observaciones}
                                                                    onChange={(e) => updateInvoiceLine(index, 'observaciones', e.target.value)}
                                                                    className={inputClass(`items.${index}.observaciones`)}
                                                                    placeholder="-"
                                                                />
                                                            </td>
                                                            <td className="px-3 py-3 text-right">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => removeInvoiceLine(index)}
                                                                    className="text-sm font-medium text-(--ciete-red) hover:text-(--ciete-red-dark)"
                                                                >
                                                                    Quitar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>

                                        <div className="space-y-3 rounded-xl border border-border bg-surface-2 p-4">
                                            <label htmlFor="itemSearch" className="block text-sm font-medium text-text-main">
                                                Buscar items pendientes
                                            </label>
                                            <input
                                                id="itemSearch"
                                                type="search"
                                                value={itemSearch}
                                                onChange={(e) => setItemSearch(e.target.value)}
                                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:outline-none focus:ring-1 focus:ring-(--ciete-red)/30"
                                                placeholder="Pedido, trabajo, servicio..."
                                            />

                                            <div className="max-h-[420px] space-y-2 overflow-y-auto pr-1">
                                                {availablePedidoItems.length === 0 && (
                                                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-4">
                                                        <p className="text-sm font-medium text-amber-800">
                                                            No hay items pendientes para facturar.
                                                        </p>
                                                        <p className="mt-1 text-sm text-amber-700">
                                                            {contextPedidoItemsCount === 0
                                                                ? 'Primero crea un pedido con items para este contexto y trabajo.'
                                                                : form.id_empresa_facturadora
                                                                  ? 'La sociedad seleccionada no permite los contratos de los items disponibles.'
                                                                  : 'Revisa el trabajo seleccionado, la busqueda o los items ya añadidos.'}
                                                        </p>
                                                    </div>
                                                )}
                                                {availablePedidoItems.map((item) => (
                                                    <button
                                                        key={item.id_pedido_item}
                                                        type="button"
                                                        onClick={() => addInvoiceItem(item)}
                                                        className="w-full rounded-lg border border-border bg-surface px-3 py-3 text-left transition hover:border-(--ciete-red)"
                                                    >
                                                        <div className="flex min-w-0 items-start justify-between gap-3">
                                                            <div className="min-w-0">
                                                                <p className="font-mono text-xs font-semibold text-(--ciete-red)">
                                                                    {item.numero_pedido ?? '-'}
                                                                </p>
                                                                <p className="mt-1 break-words text-sm font-medium text-text-main">
                                                                    {item.descripcion_servicio || item.codigo_servicio || '-'}
                                                                </p>
                                                                <p className="mt-1 break-words text-xs text-text-muted">
                                                                    Trabajo {formatWorkNumber(workNumberForDisplay(item))} - {item.descripcion_trabajo}
                                                                </p>
                                                            </div>
                                                            <span className="shrink-0 whitespace-nowrap text-xs font-semibold text-text-main">
                                                                {fmtMoney(item.importe_pendiente)}
                                                            </span>
                                                        </div>
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                {isMoeve && (
                                    <fieldset className="space-y-5 rounded-2xl border border-blue-200 bg-blue-50/20 p-6 shadow-sm">
                                        <legend className="px-1 text-sm font-semibold text-text-main">Datos MOEVE</legend>
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label htmlFor="factura_ccp" className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('facturas.fields.facturaCcp')} <span className="text-red-500">*</span>
                                                </label>
                                                <input
                                                    id="factura_ccp"
                                                    type="text"
                                                    value={form.factura_ccp}
                                                    onChange={(e) => updateField('factura_ccp', e.target.value)}
                                                    className={inputClass('factura_ccp')}
                                                    placeholder="Ej: CCP-00001"
                                                />
                                                <InputError message={getError('factura_ccp')} className="mt-1.5" />
                                            </div>

                                            <div>
                                                <label htmlFor="sociedad" className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('facturas.fields.sociedad')}
                                                </label>
                                                <input
                                                    id="sociedad"
                                                    type="text"
                                                    value={form.sociedad}
                                                    onChange={(e) => updateField('sociedad', e.target.value)}
                                                    className={inputClass('sociedad')}
                                                    placeholder="Ej: MOEVE ES"
                                                />
                                            </div>
                                        </div>
                                    </fieldset>
                                )}

                                {isRepsol && (
                                    <fieldset className="space-y-5 rounded-2xl border border-red-200 bg-red-50/20 p-6 shadow-sm">
                                        <legend className="px-1 text-sm font-semibold text-text-main">Datos REPSOL</legend>
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label htmlFor="orden_factura" className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('facturas.fields.ordenFactura')} <span className="text-red-500">*</span>
                                                </label>
                                                <input
                                                    id="orden_factura"
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                    value={form.orden_factura}
                                                    onChange={(e) => updateField('orden_factura', e.target.value)}
                                                    className={inputClass('orden_factura')}
                                                    placeholder={`Ej: ${siguienteOrdenSugerida}`}
                                                />
                                                <InputError message={getError('orden_factura')} className="mt-1.5" />
                                            </div>

                                            <div className="flex flex-col justify-center">
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('facturas.fields.autofactura')}
                                                </label>
                                                <label className="inline-flex cursor-pointer items-center gap-3">
                                                    <input
                                                        id="autofactura"
                                                        type="checkbox"
                                                        checked={form.autofactura}
                                                        onChange={(e) => updateField('autofactura', e.target.checked)}
                                                        className="h-4 w-4 rounded border-border accent-(--ciete-red)"
                                                    />
                                                    <span className="text-sm text-text-muted">Autofactura</span>
                                                </label>
                                            </div>
                                        </div>
                                    </fieldset>
                                )}

                                {submitError && (
                                    <div role="alert" className="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                                        <p className="text-sm font-medium text-red-700">{submitError}</p>
                                    </div>
                                )}

                                <div className="ciete-form-actions">
                                    <button
                                        type="button"
                                        onClick={() => router.visit(route('facturas.index'))}
                                        className="inline-flex w-full items-center justify-center text-sm font-medium text-text-muted transition hover:text-text-main sm:w-auto"
                                    >
                                        {t('common.actions.cancel')}
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={loading || (!isEditing && isAllContext)}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-(--ciete-red) px-5 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60 sm:w-auto"
                                    >
                                        {loading
                                            ? (t('common.actions.saving') ?? 'Guardando...')
                                            : isEditing
                                              ? t('common.actions.save')
                                              : t('facturas.create')}
                                    </button>
                                </div>
                            </>
                        )}
                    </form>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
