import InputError from '@/Components/InputError';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import BadgeCliente from '@/Components/ui/BadgeCliente';
import ItemsTable, { EMPTY_ITEM } from '@/Components/ui/ItemsTable';
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
    id_trabajo: '',
    numero_pedido: '',
    fecha_solicitud: new Date().toISOString().split('T')[0],
    fecha_recepcion: '',
    estado: 'pendiente',
    importe_solicitado: '',
    unidades_solicitadas: '',
};

const DEFAULT_PEDIDO_STATUS_OPTIONS = ['pendiente', 'solicitado', 'recibido', 'facturado_parcial', 'facturado', 'cancelado'];

function pedidoStatusLabelKey(status) {
    return status === 'en_ejecucion' ? 'pedidos.status.enEjecucion' : `pedidos.status.${status}`;
}

function formatWorkNumber(trabajo) {
    const value = trabajo?.numero_trabajo_visible ?? trabajo?.numero_trabajo_operativo ?? trabajo?.numero_trabajo ?? '';
    const asString = String(value);

    return /^\d+$/.test(asString) ? asString.padStart(4, '0') : asString;
}

function normalizePedido(pedido) {
    if (!pedido) return EMPTY_FORM;

    const item = pedido.data || pedido;

    return {
        id_contexto: item.id_contexto ? String(item.id_contexto) : '',
        id_trabajo: item.id_trabajo ? String(item.id_trabajo) : '',
        numero_pedido: item.numero_pedido ?? '',
        fecha_solicitud: item.fecha_solicitud ?? new Date().toISOString().split('T')[0],
        fecha_recepcion: item.fecha_recepcion ?? '',
        estado: item.estado ?? 'pendiente',
        importe_solicitado: item.importe_solicitado ?? '',
        unidades_solicitadas: item.unidades_solicitadas ?? '',
    };
}

function normalizeItems(items) {
    if (!items || items.length === 0) return [{ ...EMPTY_ITEM }];

    return items.map((item) => ({
        id_pedido_item: item.id_pedido_item ?? item.id ?? null,
        id_tarifario_linea: item.id_tarifario_linea ?? null,
        codigo_servicio: item.codigo_servicio ?? '',
        numero_tarifa: item.numero_tarifa ?? '',
        descripcion_servicio: item.descripcion_servicio ?? item.concepto_libre ?? '',
        cantidad: item.cantidad ?? 1,
        precio_unitario: item.precio_unitario ?? 0,
        total_linea: item.total_linea ?? 0,
        factura_items_count: Number(item.factura_items_count ?? 0),
        esta_facturado: Boolean(item.esta_facturado ?? Number(item.factura_items_count ?? 0) > 0),
    }));
}

function hasMeaningfulItem(item) {
    return Boolean(
        item?.id_pedido_item
            || item?.id_tarifario_linea
            || String(item?.codigo_servicio ?? '').trim()
            || String(item?.numero_tarifa ?? '').trim()
            || String(item?.descripcion_servicio ?? '').trim()
            || Number(item?.precio_unitario) > 0
            || Number(item?.total_linea) > 0
    );
}

function resolveClientKey(context) {
    const value = String(context?.codigo || context?.nombre || '').trim().toLowerCase();

    if (value.includes('moeve')) return 'moeve';
    if (value.includes('repsol')) return 'repsol';
    if (value.includes('otro')) return 'otros';

    return null;
}

function selectedTheme(clientKey) {
    return CLIENT_THEME[clientKey] ?? null;
}

function validarForm(form, items, trabajos, tarifarioLineas, allowClientSelection, isRepsol, t) {
    const errs = {};
    const reqMsg = t('common.validation.required');
    const selectedTrabajo = trabajos.find(
        (item) => String(item.id_trabajo ?? item.id) === String(form.id_trabajo),
    );

    if (allowClientSelection && !form.id_contexto) {
        errs.id_contexto = reqMsg;
    }

    if (!form.numero_pedido?.trim()) {
        errs.numero_pedido = reqMsg;
    }

    if (!form.id_trabajo) {
        errs.id_trabajo = reqMsg;
    } else if (
        selectedTrabajo &&
        form.id_contexto &&
        String(selectedTrabajo.id_contexto) !== String(form.id_contexto)
    ) {
        errs.id_trabajo = t('trabajos.clientSelector.workMismatch');
    } else if (trabajos.length === 0) {
        errs.id_trabajo = 'No hay trabajos disponibles en este contexto. Crea primero el trabajo.';
    }

    if (!form.fecha_solicitud) {
        errs.fecha_solicitud = reqMsg;
    }

    if (!form.estado) {
        errs.estado = reqMsg;
    }

    if (isRepsol) {
        if (form.importe_solicitado === '' || form.importe_solicitado === null) {
            errs.importe_solicitado = reqMsg;
        }
        if (form.unidades_solicitadas === '' || form.unidades_solicitadas === null) {
            errs.unidades_solicitadas = reqMsg;
        } else {
            const requestedUnits = Number(form.unidades_solicitadas);

            if (!Number.isFinite(requestedUnits) || requestedUnits < 0) {
                errs.unidades_solicitadas = 'Las unidades solicitadas no son válidas.';
            }
        }
    }

    const meaningfulItems = items.filter(hasMeaningfulItem);

    if (meaningfulItems.length === 0 && tarifarioLineas.length > 0) {
        errs.items = 'Añade al menos una línea al pedido.';
    } else if (meaningfulItems.length > 0) {
        items.forEach((item, i) => {
            if (!hasMeaningfulItem(item)) {
                return;
            }

            const quantity = Number(item.cantidad);

            if (tarifarioLineas.length > 0 && !item.id_tarifario_linea) {
                errs[`items.${i}.id_tarifario_linea`] = 'Selecciona una linea de tarifa del trabajo.';
            }
            if (!Number.isFinite(quantity) || quantity <= 0) {
                errs[`items.${i}.cantidad`] = 'La cantidad debe ser mayor que 0.';
            }
            if (Number(item.precio_unitario) < 0) {
                errs[`items.${i}.precio_unitario`] = 'El precio no puede ser negativo.';
            }
        });
    }

    return errs;
}

export default function PedidosForm({
    pedido = null,
    trabajos = [],
    tarifarioLineas = [],
    contextoIds = [],
    clientContexts = [],
    sourceTrabajo = null,
}) {
    const { t } = useI18n();
    const { props } = usePage();
    const isAllContext = props.auth?.user?.active_context?.is_all ?? false;

    const isEditing = pedido !== null;
    const pageTitle = isEditing ? t('pedidos.edit') : t('pedidos.create');
    const normalizedPedido = useMemo(() => normalizePedido(pedido), [pedido]);
    const normalizedItems = useMemo(() => normalizeItems((pedido?.data || pedido)?.items), [pedido]);
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
    const sourceTrabajoId = sourceTrabajo?.id_trabajo ? String(sourceTrabajo.id_trabajo) : '';
    const isPedidoFromTrabajo = !isEditing && Boolean(sourceTrabajoId);
    const initialPedido = useMemo(() => {
        if (!isPedidoFromTrabajo) return normalizedPedido;

        return {
            ...normalizedPedido,
            id_trabajo: sourceTrabajoId,
            id_contexto: sourceTrabajo?.id_contexto ? String(sourceTrabajo.id_contexto) : normalizedPedido.id_contexto,
        };
    }, [isPedidoFromTrabajo, normalizedPedido, sourceTrabajo, sourceTrabajoId]);

    const [form, setForm] = useState(() => ({
        ...initialPedido,
        id_contexto: initialPedido.id_contexto || (allowClientSelection ? '' : defaultContextId),
    }));
    const [items, setItems] = useState(() => (isPedidoFromTrabajo ? [{ ...EMPTY_ITEM }] : normalizedItems));
    const [serverErrors, setServerErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError, setSubmitError] = useState('');
    const [listaTrab, setListaTrab] = useState(trabajos);
    const [cargandoTrab, setCargandoTrab] = useState(false);

    useEffect(() => {
        setForm({
            ...initialPedido,
            id_contexto: initialPedido.id_contexto || (allowClientSelection ? '' : defaultContextId),
        });
    }, [initialPedido, allowClientSelection, defaultContextId]);

    useEffect(() => {
        setItems(isPedidoFromTrabajo ? [{ ...EMPTY_ITEM }] : normalizedItems);
    }, [isPedidoFromTrabajo, normalizedItems]);

    useEffect(() => {
        setListaTrab(trabajos);
    }, [trabajos]);

    useEffect(() => {
        if (listaTrab.length > 0) return;

        setCargandoTrab(true);
        axios
            .get('/api/v1/trabajos', { params: { per_page: 200 } })
            .then((res) => {
                const data = res.data?.data ?? res.data ?? [];
                setListaTrab(Array.isArray(data) ? data : []);
            })
            .catch(() => setListaTrab([]))
            .finally(() => setCargandoTrab(false));
    }, [listaTrab.length]);

    const selectedContext = useMemo(
        () => availableClientContexts.find((context) => String(context.id_contexto) === String(form.id_contexto)) ?? null,
        [availableClientContexts, form.id_contexto],
    );
    const selectedClientKey = selectedContext?.clientKey ?? null;
    const activeTheme = selectedTheme(selectedClientKey);
    const hasOperationalClientAccess = availableClientContexts.length > 0;
    const filteredTrabajos = useMemo(() => {
        if (!selectedContext) {
            return allowClientSelection ? [] : listaTrab || [];
        }

        return (listaTrab || []).filter(
            (item) => String(item.id_contexto) === String(selectedContext.id_contexto),
        );
    }, [allowClientSelection, listaTrab, selectedContext]);
    const selectedTrabajo = useMemo(
        () => filteredTrabajos.find((item) => String(item.id_trabajo ?? item.id) === String(form.id_trabajo)) ?? null,
        [filteredTrabajos, form.id_trabajo],
    );
    const filteredTarifarioLineas = useMemo(() => {
        if (!selectedContext || !selectedTrabajo) {
            return [];
        }

        return (tarifarioLineas || []).filter((linea) => {
            if (String(linea.id_contexto) !== String(selectedContext.id_contexto)) {
                return false;
            }

            if (selectedTrabajo.id_tarifario) {
                return String(linea.id_tarifario) === String(selectedTrabajo.id_tarifario);
            }

            if (selectedTrabajo.id_contrato) {
                return String(linea.id_contrato) === String(selectedTrabajo.id_contrato);
            }

            return true;
        });
    }, [selectedContext, selectedTrabajo, tarifarioLineas]);
    const isRepsol = selectedClientKey === 'repsol';
    const localErrors = useMemo(
        () => validarForm(form, items, filteredTrabajos, filteredTarifarioLineas, allowClientSelection, isRepsol, t),
        [form, items, filteredTrabajos, filteredTarifarioLineas, allowClientSelection, isRepsol, t],
    );
    const totalPedido = items.reduce((sum, item) => sum + (Number(item.total_linea) || 0), 0);
    const exportableItems = useMemo(() => items.filter(hasMeaningfulItem), [items]);
    // La exportación MOEVE (PDF, CSV, ARIBA) solo aplica a pedidos del contexto MOEVE.
    const canExportMoeve = isEditing && exportableItems.length > 0 && selectedClientKey === 'moeve';
    const pedidoId = (pedido?.data || pedido)?.id_pedido ?? null;
    const [showCorreoModal, setShowCorreoModal] = useState(false);
    const pedidoData = pedido?.data || pedido;
    const pedidoStatusOptions = useMemo(() => {
        if (!form.estado || DEFAULT_PEDIDO_STATUS_OPTIONS.includes(form.estado)) {
            return DEFAULT_PEDIDO_STATUS_OPTIONS;
        }

        return [...DEFAULT_PEDIDO_STATUS_OPTIONS, form.estado];
    }, [form.estado]);

    const updateField = (field, value) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
        setForm((prev) => ({ ...prev, [field]: value }));
    };

    const updateTrabajo = (value) => {
        if (isPedidoFromTrabajo) return;

        setTouched((prev) => ({ ...prev, id_trabajo: true, items: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_trabajo;
            delete next.items;
            return next;
        });
        setForm((prev) => ({ ...prev, id_trabajo: value }));
        setItems([{ ...EMPTY_ITEM }]);
    };

    const selectClient = (contextId) => {
        if (isPedidoFromTrabajo) return;

        setTouched((prev) => ({ ...prev, id_contexto: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_contexto;
            delete next.id_trabajo;
            delete next.importe_solicitado;
            delete next.unidades_solicitadas;
            return next;
        });
        setForm((prev) => ({
            ...prev,
            id_contexto: String(contextId),
            id_trabajo: '',
            importe_solicitado: '',
            unidades_solicitadas: '',
        }));
        setItems([{ ...EMPTY_ITEM }]);
    };

    const getError = (field) => {
        if (!submitAttempted && !touched[field] && !serverErrors[field]) return '';
        const serverMessage = Array.isArray(serverErrors[field]) ? serverErrors[field][0] : serverErrors[field];
        return localErrors[field] ?? serverMessage ?? '';
    };

    const inputClass = (field) =>
        `w-full rounded-md border px-3 py-2 text-sm bg-surface text-text-main
         focus:outline-none focus:ring-1
         ${
             getError(field)
                 ? 'border-red-500 focus:border-red-500 focus:ring-red-300'
                 : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'
         }`;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitAttempted(true);
        setSubmitError('');

        const allTouched = Object.keys(EMPTY_FORM).reduce((acc, key) => ({ ...acc, [key]: true }), {});
        setTouched(allTouched);

        if (!hasOperationalClientAccess || Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            const firstError = Object.keys(localErrors)[0];
            if (firstError) {
                document.getElementById(firstError)?.focus();
            }
            return;
        }

        setLoading(true);

        try {
            const itemsToSubmit = items.filter(hasMeaningfulItem);
            const payloadItems = itemsToSubmit.map((item) => {
                const itemId = Number(item.id_pedido_item);

                return {
                    ...(Number.isFinite(itemId) && itemId > 0 ? { id_pedido_item: itemId } : {}),
                    id_tarifario_linea: item.id_tarifario_linea ? Number(item.id_tarifario_linea) : null,
                    codigo_servicio: item.codigo_servicio,
                    numero_tarifa: item.numero_tarifa || null,
                    descripcion_servicio: item.descripcion_servicio,
                    cantidad: Number(item.cantidad),
                    precio_unitario: Number(item.precio_unitario),
                    total_linea: Number(item.total_linea),
                };
            });

            const payload = {
                id_trabajo: Number(form.id_trabajo),
                numero_pedido: form.numero_pedido,
                fecha_solicitud: form.fecha_solicitud,
                fecha_recepcion: form.fecha_recepcion || null,
                estado: form.estado,
            };

            if (!isPedidoFromTrabajo || payloadItems.length > 0 || isEditing) {
                payload.items = payloadItems;
            }

            const selectedLine = payloadItems.find((item) => item.id_tarifario_linea);
            if (selectedLine) {
                const line = tarifarioLineas.find(
                    (tarifa) => Number(tarifa.id_tarifario_linea) === Number(selectedLine.id_tarifario_linea),
                );
                if (line?.id_tarifario) {
                    payload.id_tarifario = Number(line.id_tarifario);
                }
            }
            if (!payload.id_tarifario && selectedTrabajo?.id_tarifario) {
                payload.id_tarifario = Number(selectedTrabajo.id_tarifario);
            }

            if (isRepsol) {
                payload.importe_solicitado = Number(form.importe_solicitado) || 0;
                payload.unidades_solicitadas = Number(form.unidades_solicitadas) || 0;
            }

            const response = isEditing
                ? await axios.put(`/api/v1/pedidos/${(pedido.data || pedido).id_pedido}`, payload)
                : await axios.post('/api/v1/pedidos', payload);

            const blockedItems = response.data?.meta?.items_bloqueados ?? [];
            if (blockedItems.length > 0) {
                setItems(normalizeItems(response.data?.data?.items ?? []));
                setSubmitError(response.data?.message ?? 'No se eliminaron las lineas ya vinculadas a factura.');
                return;
            }

            router.visit(isPedidoFromTrabajo ? route('trabajos.index') : route('pedidos.index'));
        } catch (err) {
            if (err.response?.status === 422) {
                setServerErrors(err.response.data?.errors ?? {});
                setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            } else {
                setSubmitError('Error al guardar el pedido. Inténtalo de nuevo.');
            }
        } finally {
            setLoading(false);
        }
    };

    const openExportWindow = (routeName) => {
        if (!pedidoId) return;
        window.open(route(routeName, pedidoId), '_blank', 'noopener,noreferrer');
    };

    const downloadExport = (routeName) => {
        if (!pedidoId) return;
        window.location.assign(route(routeName, pedidoId));
    };

    return (
        <>
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
        >
            <Head title={pageTitle} />

            <div className="ciete-page ciete-page-form">
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={pageTitle}
                    description={
                        isPedidoFromTrabajo
                            ? 'Nuevo pedido iniciado desde Trabajos. El trabajo queda preseleccionado y aquí ya puedes completar las líneas económicas.'
                            : selectedClientKey
                              ? t('trabajos.clientSelector.contextReady')
                              : t('trabajos.clientSelector.intro')
                    }
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
                                    <div className="grid gap-3 md:grid-cols-2">
                                        {availableClientContexts.map((context) => {
                                            const theme = selectedTheme(context.clientKey);
                                            const isSelected = String(form.id_contexto) === String(context.id_contexto);

                                            return (
                                                <button
                                                    key={context.id_contexto}
                                                    type="button"
                                                    onClick={() => selectClient(context.id_contexto)}
                                                    disabled={isPedidoFromTrabajo}
                                                    className={`rounded-2xl border px-4 py-4 text-left transition ${
                                                        isSelected
                                                            ? 'border-transparent bg-surface-2 shadow-sm'
                                                            : 'border-border bg-surface hover:bg-surface-2'
                                                    }`}
                                                    style={
                                                        isSelected
                                                            ? {
                                                                  boxShadow: `inset 0 0 0 1px ${theme?.tone}`,
                                                              }
                                                            : undefined
                                                    }
                                                >
                                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                        <div>
                                                            <p className="text-sm font-semibold text-text-main">
                                                                {theme?.label ?? context.nombre}
                                                            </p>
                                                            <p className="mt-1 text-xs text-text-muted">
                                                                {t(`trabajos.clientSelector.${context.clientKey}.summary`)}
                                                            </p>
                                                        </div>
                                                        {isSelected && (
                                                            <span className="inline-flex rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-primary">
                                                                {t('trabajos.clientSelector.selected')}
                                                            </span>
                                                        )}
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
                                {isPedidoFromTrabajo && (
                                    <section className="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm">
                                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                            Nuevo pedido desde trabajo
                                        </p>
                                        <p className="mt-1 text-sm font-semibold text-emerald-900">
                                            Trabajo {formatWorkNumber(sourceTrabajo)}
                                            {sourceTrabajo?.descripcion_trabajo ? ` — ${sourceTrabajo.descripcion_trabajo}` : ''}
                                        </p>
                                        {(sourceTrabajo?.codigo_estacion || sourceTrabajo?.nombre_estacion) && (
                                            <p className="mt-1 text-xs text-emerald-800">
                                                Estación: {[sourceTrabajo.codigo_estacion, sourceTrabajo.nombre_estacion].filter(Boolean).join(' · ')}
                                            </p>
                                        )}
                                        <p className="mt-2 text-xs text-emerald-800">
                                            El trabajo queda bloqueado para evitar asociar el pedido a otro registro por error.
                                        </p>
                                    </section>
                                )}

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
                                            <label
                                                htmlFor="id_trabajo"
                                                className="mb-1.5 block text-sm font-medium text-text-main"
                                            >
                                                {t('pedidos.fields.trabajo')} <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="id_trabajo"
                                                value={form.id_trabajo}
                                                onChange={(e) => updateTrabajo(e.target.value)}
                                                disabled={cargandoTrab || !selectedClientKey || isPedidoFromTrabajo}
                                                className={inputClass('id_trabajo')}
                                            >
                                                <option value="">
                                                    {cargandoTrab
                                                        ? t('trabajos.clientSelector.loadingWorks')
                                                        : t('trabajos.clientSelector.workPlaceholder')}
                                                </option>
                                                {filteredTrabajos.map((tr) => {
                                                    const id = tr.id_trabajo ?? tr.id;
                                                    const numero = formatWorkNumber(tr);
                                                    const descripcion = tr.descripcion_trabajo ?? tr.descripcion ?? '';

                                                    return (
                                                        <option key={id} value={id}>
                                                            {numero}
                                                            {descripcion ? ` — ${descripcion}` : ''}
                                                        </option>
                                                    );
                                                })}
                                            </select>
                                            {filteredTrabajos.length === 0 && !cargandoTrab && (
                                                <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                    <p className="text-xs font-medium text-amber-800">
                                                        No hay trabajos disponibles para este contexto.
                                                    </p>
                                                    <p className="mt-1 text-xs text-amber-700">
                                                        Primero crea el trabajo en MOEVE, REPSOL u OTROS CLIENTES. Desde TODOS no se crean pedidos.
                                                    </p>
                                                </div>
                                            )}
                                            <InputError message={getError('id_trabajo')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="numero_pedido"
                                                className="mb-1.5 block text-sm font-medium text-text-main"
                                            >
                                                {t('pedidos.fields.numero')} <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="numero_pedido"
                                                type="text"
                                                value={form.numero_pedido}
                                                onChange={(e) => updateField('numero_pedido', e.target.value)}
                                                className={inputClass('numero_pedido')}
                                                placeholder="Ej: PED-M-001"
                                            />
                                            <InputError message={getError('numero_pedido')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="estado"
                                                className="mb-1.5 block text-sm font-medium text-text-main"
                                            >
                                                {t('pedidos.fields.estado')} <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="estado"
                                                value={form.estado}
                                                onChange={(e) => updateField('estado', e.target.value)}
                                                className={inputClass('estado')}
                                            >
                                                {pedidoStatusOptions.map((statusOption) => (
                                                    <option key={statusOption} value={statusOption}>
                                                        {t(pedidoStatusLabelKey(statusOption))}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={getError('estado')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="fecha_solicitud"
                                                className="mb-1.5 block text-sm font-medium text-text-main"
                                            >
                                                {t('pedidos.fields.fechaSolicitud')} <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="fecha_solicitud"
                                                type="date"
                                                value={form.fecha_solicitud}
                                                onChange={(e) => updateField('fecha_solicitud', e.target.value)}
                                                className={inputClass('fecha_solicitud')}
                                            />
                                            <InputError message={getError('fecha_solicitud')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="fecha_recepcion"
                                                className="mb-1.5 block text-sm font-medium text-text-main"
                                            >
                                                {t('pedidos.fields.fechaRecepcion')}
                                                <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                            </label>
                                            <input
                                                id="fecha_recepcion"
                                                type="date"
                                                value={form.fecha_recepcion}
                                                onChange={(e) => updateField('fecha_recepcion', e.target.value)}
                                                className={inputClass('fecha_recepcion')}
                                            />
                                        </div>
                                    </div>
                                </fieldset>

                                {isRepsol && (
                                    <fieldset className="space-y-5 rounded-2xl border border-red-200 bg-red-50/20 p-6 shadow-sm">
                                        <legend className="flex items-center gap-2 px-1 text-sm font-semibold text-text-main">
                                            Datos REPSOL
                                            <span className="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">
                                                R
                                            </span>
                                        </legend>
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label
                                                    htmlFor="importe_solicitado"
                                                    className="mb-1.5 block text-sm font-medium text-text-main"
                                                >
                                                    {t('pedidos.fields.importeSolicitado')}
                                                    <span className="text-red-500"> *</span>
                                                </label>
                                                <div className="relative">
                                                    <input
                                                        id="importe_solicitado"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        value={form.importe_solicitado}
                                                        onChange={(e) => updateField('importe_solicitado', e.target.value)}
                                                        className={`${inputClass('importe_solicitado')} pr-7`}
                                                        placeholder="0.00"
                                                    />
                                                    <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-text-hint">
                                                        €
                                                    </span>
                                                </div>
                                                <InputError message={getError('importe_solicitado')} className="mt-1.5" />
                                            </div>

                                            <div>
                                                <label
                                                    htmlFor="unidades_solicitadas"
                                                    className="mb-1.5 block text-sm font-medium text-text-main"
                                                >
                                                    {t('pedidos.fields.unidadesSolicitadas')}
                                                    <span className="text-red-500"> *</span>
                                                </label>
                                                <input
                                                    id="unidades_solicitadas"
                                                    type="number"
                                                    min="0"
                                                    step="0.001"
                                                    value={form.unidades_solicitadas}
                                                    onChange={(e) => updateField('unidades_solicitadas', e.target.value)}
                                                    className={inputClass('unidades_solicitadas')}
                                                    placeholder="0"
                                                />
                                                <InputError message={getError('unidades_solicitadas')} className="mt-1.5" />
                                            </div>
                                        </div>
                                    </fieldset>
                                )}

                                <fieldset className="space-y-4 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                    <legend className="px-1 text-sm font-semibold text-text-main">
                                        {t('pedidos.items.title')}
                                    </legend>
                                    {isPedidoFromTrabajo && (
                                        <div className="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                                            <p className="text-sm font-medium text-blue-900">
                                                El pedido ya nace vinculado al trabajo y al tarifario heredado.
                                            </p>
                                            <p className="mt-1 text-sm text-blue-800">
                                                Añade ahora las líneas tarifarias para dejar el pedido operativo completo.
                                            </p>
                                        </div>
                                    )}
                                    {form.id_trabajo && filteredTarifarioLineas.length === 0 && (
                                        <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                                            <p className="text-sm font-medium text-amber-800">
                                                Este trabajo no tiene lineas de tarifa disponibles para su contrato/tarifario.
                                            </p>
                                            <p className="mt-1 text-sm text-amber-700">
                                                Revisa Maestros &gt; Tarifarios y Lineas antes de crear items economicos.
                                            </p>
                                        </div>
                                    )}
                                    <ItemsTable
                                        items={items}
                                        onChange={setItems}
                                        errors={{ ...serverErrors, ...localErrors }}
                                        disabled={false}
                                        tarifarioLineas={filteredTarifarioLineas}
                                    />
                                    {submitAttempted && localErrors.items && (
                                        <p className="text-xs font-medium text-red-600">{localErrors.items}</p>
                                    )}
                                </fieldset>

                                {exportableItems.length > 0 && (
                                    <div className="rounded-2xl border border-border bg-surface-2 px-4 py-4 sm:px-6">
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <span className="text-sm font-semibold text-text-main">
                                                {t('pedidos.items.total')}
                                            </span>
                                            <span className="text-xl font-bold text-(--ciete-red)">
                                                {totalPedido.toLocaleString('es-ES', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })}{' '}
                                                €
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs text-text-hint">
                                            {items.length} {items.length === 1 ? 'línea' : 'líneas'} · Calculado automáticamente
                                        </p>
                                    </div>
                                )}

                                {isEditing && (
                                    <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p className="text-sm font-semibold text-text-main">
                                                    Exportación Moeve
                                                </p>
                                                <p className="mt-1 text-sm text-text-muted">
                                                    El CSV se descarga directamente. El PDF se sirve como HTML imprimible hasta cerrar un PDF binario definitivo.
                                                </p>
                                            </div>
                                            {canExportMoeve && (
                                                <div className="flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => openExportWindow('pedidos.export.moeve.pdf')}
                                                        className="inline-flex items-center justify-center rounded-md border border-border px-4 py-2 text-sm font-medium text-text-main transition hover:bg-surface-2"
                                                    >
                                                        PDF Moeve
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => downloadExport('pedidos.export.moeve.csv')}
                                                        className="inline-flex items-center justify-center rounded-md border border-border px-4 py-2 text-sm font-medium text-text-main transition hover:bg-surface-2"
                                                    >
                                                        CSV Moeve
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => openExportWindow('pedidos.export.moeve.ariba')}
                                                        className="inline-flex items-center justify-center rounded-md border border-border px-4 py-2 text-sm font-medium text-text-main transition hover:bg-surface-2"
                                                    >
                                                        Cuadro ARIBA
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowCorreoModal(true)}
                                                        className="inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark)"
                                                    >
                                                        ✉ Preparar correo Moeve
                                                    </button>
                                                </div>
                                            )}
                                        </div>

                                        {!canExportMoeve && (
                                            <div className="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                                                <p className="text-sm font-medium text-amber-800">
                                                    Este pedido todavía no tiene líneas exportables.
                                                </p>
                                                <p className="mt-1 text-sm text-amber-700">
                                                    Añade y guarda al menos una línea válida antes de generar PDF, CSV o cuadro ARIBA.
                                                </p>
                                            </div>
                                        )}

                                        {canExportMoeve && (
                                            <div className="mt-4 space-y-1">
                                                <p className="text-xs text-text-hint">
                                                    La exportación usa el pedido guardado en servidor. Si acabas de modificar líneas o importes, guarda primero antes de exportar.
                                                </p>
                                                <p className="text-xs text-amber-700">
                                                    La exportación contiene campos pendientes de parametrizar.
                                                </p>
                                            </div>
                                        )}
                                    </section>
                                )}

                                {submitError && (
                                    <div
                                        role="alert"
                                        className="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3"
                                    >
                                        <span className="mt-0.5 text-red-500" aria-hidden>
                                            ⚠
                                        </span>
                                        <p className="text-sm font-medium text-red-700">{submitError}</p>
                                    </div>
                                )}

                                <div className="ciete-form-actions">
                                    <button
                                        type="button"
                                        onClick={() => router.visit(isPedidoFromTrabajo ? route('trabajos.index') : route('pedidos.index'))}
                                        className="inline-flex w-full items-center justify-center text-sm font-medium text-text-muted transition hover:text-text-main sm:w-auto"
                                    >
                                        {t('common.actions.cancel')}
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={loading || (!isEditing && isAllContext)}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-(--ciete-red) px-5 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60 sm:w-auto"
                                    >
                                        {loading && (
                                            <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle
                                                    className="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    strokeWidth="4"
                                                />
                                                <path
                                                    className="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                                />
                                            </svg>
                                        )}
                                        {loading
                                            ? (t('common.actions.saving') ?? 'Guardando...')
                                            : isEditing
                                              ? t('common.actions.save')
                                              : t('pedidos.create')}
                                    </button>
                                </div>
                            </>
                        )}
                    </form>
                )}
            </div>
        </AuthenticatedLayout>

        {showCorreoModal && canExportMoeve && (
            <CorreoMoeveModal
                pedidoId={pedidoId}
                numeroPedido={form.numero_pedido || pedidoData?.numero_pedido || ''}
                nombreEstacion={pedidoData?.trabajo?.nombre_estacion ?? pedidoData?.trabajo?.estacion?.nombre ?? ''}
                codigoEstacion={pedidoData?.trabajo?.codigo_estacion ?? pedidoData?.trabajo?.estacion?.codigo_estacion ?? ''}
                onClose={() => setShowCorreoModal(false)}
                onOpenPdf={() => openExportWindow('pedidos.export.moeve.pdf')}
                onDownloadCsv={() => downloadExport('pedidos.export.moeve.csv')}
                onOpenAriba={() => openExportWindow('pedidos.export.moeve.ariba')}
            />
        )}
        </>
    );
}

function CorreoMoeveModal({ pedidoId, numeroPedido, nombreEstacion, codigoEstacion, onClose, onOpenPdf, onDownloadCsv, onOpenAriba }) {
    const [copied, setCopied] = useState(null);

    const estacionLabel = [codigoEstacion, nombreEstacion].filter(Boolean).join(' - ') || 'estación';
    const asunto = `Solicitud de pedido Moeve - ${estacionLabel} - ${numeroPedido}`;
    const cuerpo = `Estimados/as,

Adjuntamos la solicitud de pedido correspondiente al trabajo en la estación ${estacionLabel} (Pedido: ${numeroPedido}).

Se adjuntan a este correo:
  • Oferta Precios Acuerdo (PDF)
  • Fichero de carga CSV

A continuación encontrará también el cuadro ARIBA para la tramitación del pedido.

Quedamos a su disposición para cualquier consulta.

Un saludo,
Ciete Ingenieros S.A.`;

    function copy(text, key) {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(key);
            setTimeout(() => setCopied(null), 2000);
        });
    }

    const btnBase = 'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition';
    const btnOutline = `${btnBase} border border-border bg-surface text-text-main hover:bg-surface-2`;
    const btnRed = `${btnBase} bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)`;
    const btnGreen = `${btnBase} bg-green-600 text-white hover:bg-green-700`;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}>
            <div className="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-surface shadow-2xl border border-border">
                <div className="flex items-center justify-between border-b border-border px-6 py-4">
                    <h2 className="text-lg font-semibold text-text-main">✉ Preparar correo Moeve</h2>
                    <button onClick={onClose} className="text-text-hint hover:text-text-main text-xl leading-none">&times;</button>
                </div>

                <div className="px-6 py-5 space-y-5">
                    {/* Asunto */}
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-text-hint mb-1">Asunto del correo</p>
                        <div className="flex gap-2 items-start">
                            <code className="flex-1 rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm text-text-main break-all">{asunto}</code>
                            <button className={copied === 'asunto' ? btnGreen : btnOutline} onClick={() => copy(asunto, 'asunto')}>
                                {copied === 'asunto' ? '✓ Copiado' : 'Copiar'}
                            </button>
                        </div>
                    </div>

                    {/* Cuerpo */}
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-text-hint mb-1">Cuerpo del correo</p>
                        <textarea readOnly value={cuerpo} rows={10} className="w-full rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm text-text-main resize-none font-mono" />
                        <button className={`mt-2 ${copied === 'cuerpo' ? btnGreen : btnOutline}`} onClick={() => copy(cuerpo, 'cuerpo')}>
                            {copied === 'cuerpo' ? '✓ Cuerpo copiado' : 'Copiar cuerpo'}
                        </button>
                    </div>

                    {/* Acciones de descarga */}
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-text-hint mb-2">Adjuntos y documentos</p>
                        <div className="flex flex-wrap gap-2">
                            <button className={btnRed} onClick={onOpenPdf}>📄 Abrir PDF Moeve</button>
                            <button className={btnOutline} onClick={onDownloadCsv}>⬇ Descargar CSV</button>
                            <button className={btnOutline} onClick={onOpenAriba}>📋 Abrir cuadro ARIBA</button>
                        </div>
                        <p className="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            ⚠ Imprime o guarda el PDF como archivo y adjúntalo manualmente al correo junto con el CSV descargado.
                        </p>
                    </div>

                    {/* Checklist */}
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-text-hint mb-2">Checklist antes de enviar</p>
                        <ul className="space-y-1 text-sm text-text-muted">
                            {['Abrir PDF Moeve, imprimirlo o guardarlo como PDF y adjuntarlo al correo', 'Descargar el CSV y adjuntarlo al correo', 'Copiar el cuerpo del correo y pegarlo en el cliente de email', 'Añadir el asunto copiado', 'Revisar destinatario Moeve antes de enviar'].map((item, i) => (
                                <li key={i} className="flex items-start gap-2">
                                    <span className="mt-0.5 text-green-600">☐</span>
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <div className="flex justify-end border-t border-border px-6 py-4">
                    <button className={btnOutline} onClick={onClose}>Cerrar</button>
                </div>
            </div>
        </div>
    );
}
