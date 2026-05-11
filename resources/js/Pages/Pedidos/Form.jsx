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

function validarForm(form, items, trabajos, allowClientSelection, isRepsol, t) {
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
        }
    }

    if (items.length === 0) {
        errs.items = 'Añade al menos una línea al pedido.';
    } else {
        items.forEach((item, i) => {
            if (Number(item.cantidad) <= 0) {
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
    contextoIds = [],
    clientContexts = [],
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

    const [form, setForm] = useState(() => ({
        ...normalizedPedido,
        id_contexto: normalizedPedido.id_contexto || (allowClientSelection ? '' : defaultContextId),
    }));
    const [items, setItems] = useState(normalizedItems);
    const [serverErrors, setServerErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError, setSubmitError] = useState('');
    const [listaTrab, setListaTrab] = useState(trabajos);
    const [cargandoTrab, setCargandoTrab] = useState(false);

    useEffect(() => {
        setForm({
            ...normalizedPedido,
            id_contexto: normalizedPedido.id_contexto || (allowClientSelection ? '' : defaultContextId),
        });
    }, [normalizedPedido, allowClientSelection, defaultContextId]);

    useEffect(() => {
        setItems(normalizedItems);
    }, [normalizedItems]);

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
    const isRepsol = selectedClientKey === 'repsol';
    const localErrors = useMemo(
        () => validarForm(form, items, filteredTrabajos, allowClientSelection, isRepsol, t),
        [form, items, filteredTrabajos, allowClientSelection, isRepsol, t],
    );
    const totalPedido = items.reduce((sum, item) => sum + (Number(item.total_linea) || 0), 0);
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

    const selectClient = (contextId) => {
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
            const payloadItems = items.map((item) => {
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
                items: payloadItems,
            };

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

            router.visit(route('pedidos.index'));
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

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
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
                                    <div className="grid gap-3 md:grid-cols-2">
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
                                                onChange={(e) => updateField('id_trabajo', e.target.value)}
                                                disabled={cargandoTrab || !selectedClientKey}
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
                                                <p className="mt-1 text-xs text-amber-600">
                                                    {t('trabajos.clientSelector.noWorksAvailable')}
                                                </p>
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
                                                    {t('pedidos.fields.importeSolicitado')} <span className="text-red-500">*</span>
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
                                                    {t('pedidos.fields.unidadesSolicitadas')} <span className="text-red-500">*</span>
                                                </label>
                                                <input
                                                    id="unidades_solicitadas"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
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
                                    <ItemsTable items={items} onChange={setItems} errors={serverErrors} disabled={false} />
                                    {submitAttempted && localErrors.items && (
                                        <p className="text-xs font-medium text-red-600">{localErrors.items}</p>
                                    )}
                                </fieldset>

                                {items.length > 0 && (
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
                                        onClick={() => router.visit(route('pedidos.index'))}
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
    );
}
