import InputError from '@/Components/InputError';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import BadgeCliente from '@/Components/ui/BadgeCliente';
import { useEstaciones } from '@/Hooks/useEstaciones';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { isBlank } from '@/validation/formRules';
import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

const EMPTY_FORM = {
    id_contexto: '',
    numero_trabajo: '',
    numero_trabajo_operativo: '',
    descripcion_trabajo: '',
    id_estacion_servicio: '',
    fecha_encargo: new Date().toISOString().split('T')[0],
    fecha_terminado: '',
    estado: 'en_curso',
    observaciones: '',
    id_contrato: '',
    categoria: '',
    id_tipo_documento: '',
    id_tipo_trabajo: '',
    numero_aviso: '',
};

const MANUAL_STATUS_OPTIONS = [
    { value: 'en_curso', labelKey: 'trabajos.status.enCurso' },
    { value: 'terminado', labelKey: 'trabajos.status.terminado' },
    { value: 'cancelado', labelKey: 'trabajos.status.cancelado' },
];

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
        tone: 'var(--ciete-slate)',
    },
};

function normalizeTrabajo(trabajo) {
    if (!trabajo) return EMPTY_FORM;

    const item = trabajo.data || trabajo;

    return {
        id_contexto: item.id_contexto ? String(item.id_contexto) : '',
        numero_trabajo: item.numero_trabajo ?? '',
        numero_trabajo_operativo: item.numero_trabajo_operativo ?? '',
        descripcion_trabajo: item.descripcion_trabajo ?? '',
        id_estacion_servicio: item.id_estacion_servicio ? String(item.id_estacion_servicio) : '',
        fecha_encargo: item.fecha_encargo ?? new Date().toISOString().split('T')[0],
        fecha_terminado: item.fecha_terminado ?? '',
        estado: item.estado ?? 'en_curso',
        observaciones: item.observaciones ?? '',
        id_contrato: item.id_contrato ? String(item.id_contrato) : '',
        categoria: item.categoria ?? '',
        id_tipo_documento: item.id_tipo_documento ? String(item.id_tipo_documento) : '',
        id_tipo_trabajo: item.id_tipo_trabajo ? String(item.id_tipo_trabajo) : '',
        numero_aviso: item.numero_aviso ?? '',
        updated_at: item.updated_at ?? null,
    };
}

function resolveClientKey(context) {
    const value = String(context?.codigo || context?.nombre || '').trim().toLowerCase();

    if (value.includes('moeve')) return 'moeve';
    if (value.includes('repsol')) return 'repsol';
    if (context) return 'otros';

    return null;
}

function selectedTheme(clientKey) {
    return CLIENT_THEME[clientKey] ?? null;
}

function isManualStatus(value) {
    return MANUAL_STATUS_OPTIONS.some((option) => option.value === value);
}

function statusLabelKey(value) {
    return {
        en_curso: 'trabajos.status.enCurso',
        terminado: 'trabajos.status.terminado',
        pendiente_facturar: 'trabajos.status.pendienteFacturar',
        facturado: 'trabajos.status.facturado',
        finalizado: 'trabajos.status.finalizado',
        cancelado: 'trabajos.status.cancelado',
    }[value] ?? null;
}

function validateForm(form, estaciones, t, selectedClientKey, allowClientSelection) {
    const errors = {};
    const reqMsg = t('common.validation.required') || 'Este campo es obligatorio';
    const selectedStation = estaciones.find((item) => String(item.id) === String(form.id_estacion_servicio));

    if (allowClientSelection && isBlank(form.id_contexto)) {
        errors.id_contexto = reqMsg;
    }

    if (isBlank(form.numero_trabajo)) errors.numero_trabajo = reqMsg;
    if (isBlank(form.id_estacion_servicio)) errors.id_estacion_servicio = reqMsg;
    if (isBlank(form.descripcion_trabajo)) errors.descripcion_trabajo = reqMsg;

    if (selectedStation && form.id_contexto && String(selectedStation.id_contexto) !== String(form.id_contexto)) {
        errors.id_estacion_servicio = t('trabajos.clientSelector.stationMismatch');
    }

    if (selectedClientKey === 'moeve' && isBlank(form.id_contrato)) {
        errors.id_contrato = reqMsg;
    }

    if (selectedClientKey === 'repsol') {
        if (isBlank(form.id_tipo_documento)) errors.id_tipo_documento = reqMsg;
        if (isBlank(form.id_tipo_trabajo)) errors.id_tipo_trabajo = reqMsg;
    }

    return errors;
}

export default function TrabajosForm({
    trabajo = null,
    contextoIds = [],
    clientContexts = [],
    contratos = [],
    tiposDocumento = [],
    tiposTrabajo = [],
}) {
    const { t } = useI18n();
    const { estaciones } = useEstaciones();
    const { props } = usePage();
    const isAllContext = props.auth?.user?.active_context?.is_all ?? false;
    const conflictWarning = props.flash?.conflict_warning ?? null;

    const isEditing = trabajo !== null;
    const pageTitle = isEditing ? t('trabajos.edit') : t('trabajos.create');
    const normalizedTrabajo = useMemo(() => normalizeTrabajo(trabajo), [trabajo]);
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
    const defaultContextId = availableClientContexts[0]?.id_contexto ? String(availableClientContexts[0].id_contexto) : '';
    const [form, setForm] = useState(() => ({
        ...normalizedTrabajo,
        id_contexto: normalizedTrabajo.id_contexto || (allowClientSelection ? '' : defaultContextId),
    }));
    const [serverErrors, setServerErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const estadoEsDerivado = isEditing && !isManualStatus(form.estado);

    useEffect(() => {
        setForm({
            ...normalizedTrabajo,
            id_contexto: normalizedTrabajo.id_contexto || (allowClientSelection ? '' : defaultContextId),
        });
    }, [normalizedTrabajo, allowClientSelection, defaultContextId]);

    const selectedContext = useMemo(
        () => availableClientContexts.find((context) => String(context.id_contexto) === String(form.id_contexto)) ?? null,
        [availableClientContexts, form.id_contexto],
    );
    const selectedClientKey = selectedContext?.clientKey ?? null;
    const activeTheme = selectedTheme(selectedClientKey);
    const filteredEstaciones = useMemo(() => {
        if (!selectedContext) {
            return allowClientSelection ? [] : estaciones || [];
        }

        return (estaciones || []).filter((item) => String(item.id_contexto) === String(selectedContext.id_contexto));
    }, [allowClientSelection, estaciones, selectedContext]);
    const filteredContratos = useMemo(() => {
        if (!selectedContext) {
            return [];
        }

        return (contratos || []).filter((item) => String(item.id_contexto) === String(selectedContext.id_contexto));
    }, [contratos, selectedContext]);
    const filteredTiposDocumento = useMemo(() => {
        if (!selectedContext) {
            return [];
        }

        return (tiposDocumento || []).filter((item) => String(item.id_contexto) === String(selectedContext.id_contexto));
    }, [tiposDocumento, selectedContext]);
    const filteredTiposTrabajo = useMemo(() => {
        if (!selectedContext) {
            return [];
        }

        return (tiposTrabajo || []).filter((item) => {
            if (String(item.id_contexto) !== String(selectedContext.id_contexto)) {
                return false;
            }

            if (!form.id_tipo_documento) {
                return true;
            }

            return String(item.id_tipo_documento) === String(form.id_tipo_documento);
        });
    }, [form.id_tipo_documento, selectedContext, tiposTrabajo]);
    const localErrors = useMemo(
        () => validateForm(form, filteredEstaciones, t, selectedClientKey, allowClientSelection),
        [form, filteredEstaciones, t, selectedClientKey, allowClientSelection],
    );
    const hasOperationalClientAccess = availableClientContexts.length > 0;

    useEffect(() => {
        if (form.id_contrato && !filteredContratos.some((item) => String(item.id) === String(form.id_contrato))) {
            setForm((prev) => ({ ...prev, id_contrato: '' }));
        }
    }, [filteredContratos, form.id_contrato]);

    useEffect(() => {
        if (
            form.id_tipo_documento &&
            !filteredTiposDocumento.some((item) => String(item.id) === String(form.id_tipo_documento))
        ) {
            setForm((prev) => ({ ...prev, id_tipo_documento: '', id_tipo_trabajo: '' }));
        }
    }, [filteredTiposDocumento, form.id_tipo_documento]);

    useEffect(() => {
        if (form.id_tipo_trabajo && !filteredTiposTrabajo.some((item) => String(item.id) === String(form.id_tipo_trabajo))) {
            setForm((prev) => ({ ...prev, id_tipo_trabajo: '' }));
        }
    }, [filteredTiposTrabajo, form.id_tipo_trabajo]);

    const updateField = (field, value) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
        setForm((prev) => ({ ...prev, [field]: value }));
    };

    const handleTipoDocumentoChange = (value) => {
        setTouched((prev) => ({ ...prev, id_tipo_documento: true, id_tipo_trabajo: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_tipo_documento;
            delete next.id_tipo_trabajo;
            return next;
        });
        setForm((prev) => ({
            ...prev,
            id_tipo_documento: value,
            id_tipo_trabajo: '',
        }));
    };

    const selectClient = (contextId) => {
        setTouched((prev) => ({ ...prev, id_contexto: true }));
        setServerErrors((prev) => {
            const next = { ...prev };
            delete next.id_contexto;
            delete next.id_estacion_servicio;
            return next;
        });
        setForm((prev) => ({
            ...prev,
            id_contexto: String(contextId),
            id_estacion_servicio: '',
            id_contrato: '',
            categoria: '',
            id_tipo_documento: '',
            id_tipo_trabajo: '',
            numero_aviso: '',
        }));
    };

    const getError = (field) => {
        if (!submitAttempted && !touched[field] && !serverErrors[field]) return '';
        const serverMessage = Array.isArray(serverErrors[field]) ? serverErrors[field][0] : serverErrors[field];
        return localErrors[field] ?? serverMessage ?? '';
    };

    const inputClass = (field) =>
        `w-full rounded-md border px-3 py-2 text-sm bg-surface text-text-main focus:outline-none focus:ring-1 ${
            getError(field)
                ? 'border-state-blocked-dot focus:border-state-blocked-dot focus:ring-state-blocked-dot/25'
                : 'border-border focus:border-primary focus:ring-primary/20'
        }`;

    const handleSubmit = (event) => {
        event.preventDefault();
        setSubmitAttempted(true);

        if (!hasOperationalClientAccess || Object.keys(localErrors).length > 0) {
            return;
        }

        setLoading(true);

        const payload = {
            id_contexto: Number(form.id_contexto),
            numero_trabajo: Number(form.numero_trabajo),
            numero_trabajo_operativo: form.numero_trabajo_operativo || null,
            descripcion_trabajo: form.descripcion_trabajo,
            id_estacion_servicio: Number(form.id_estacion_servicio),
            fecha_encargo: form.fecha_encargo,
            fecha_terminado: form.fecha_terminado || null,
            observaciones: form.observaciones || null,
            id_contrato: form.id_contrato ? Number(form.id_contrato) : null,
            id_tipo_documento: form.id_tipo_documento ? Number(form.id_tipo_documento) : null,
            id_tipo_trabajo: form.id_tipo_trabajo ? Number(form.id_tipo_trabajo) : null,
            numero_aviso: form.numero_aviso || null,
            categoria: form.categoria || null,
        };

        if (!isEditing || isManualStatus(form.estado)) {
            payload.estado = form.estado;
        }

        const inertiaOptions = {
            onError: (errors) => {
                setServerErrors(errors);
                setLoading(false);
            },
        };

        if (isEditing) {
            const idTrabajo = trabajo.data ? trabajo.data.id_trabajo : trabajo.id_trabajo;
            router.put(route('trabajos.update', idTrabajo), payload, inertiaOptions);
            return;
        }

        router.post(route('trabajos.store'), payload, inertiaOptions);
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}>
            <Head title={pageTitle} />

            <div className="ciete-page ciete-page-form">
                <ContextualPageHeader
                    eyebrow={t('nav.groups.operations')}
                    title={pageTitle}
                    description={selectedClientKey ? t('trabajos.clientSelector.contextReady') : t('trabajos.clientSelector.intro')}
                />

                {!hasOperationalClientAccess && (
                    <section className="rounded-2xl border border-state-pending-dot/20 bg-state-pending-bg p-5 shadow-sm">
                        <p className="text-sm font-semibold text-state-pending-text">{t('trabajos.clientSelector.noAccessTitle')}</p>
                        <p className="mt-2 text-sm text-state-pending-text/85">{t('trabajos.clientSelector.noAccessDescription')}</p>
                    </section>
                )}

                {hasOperationalClientAccess && (
                    <form onSubmit={handleSubmit} noValidate className="space-y-6">
                        {conflictWarning && (
                            <div className="rounded-lg border border-red-300 bg-red-50 p-4">
                                <p className="text-sm font-semibold text-red-800">⚠ Conflicto de edición detectado</p>
                                <p className="mt-1 text-sm text-red-700">{conflictWarning}</p>
                            </div>
                        )}
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
                                    <p className="text-sm font-semibold text-text-main">{t('trabajos.clientSelector.title')}</p>
                                    <p className="mt-1 text-sm text-text-muted">{t('trabajos.clientSelector.description')}</p>
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
                                                            <p className="text-sm font-semibold text-text-main">{theme?.label ?? context.nombre}</p>
                                                            <p className="mt-1 text-xs text-text-muted">{t(`trabajos.clientSelector.${context.clientKey}.summary`)}</p>
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
                                <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border pb-4">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">
                                                {t('trabajos.clientSelector.contextLabel')}
                                            </p>
                                            <h2 className="mt-1 text-lg font-semibold text-text-main">
                                                {t(`trabajos.clientSelector.${selectedClientKey}.title`)}
                                            </h2>
                                        </div>
                                        <BadgeCliente cliente={selectedClientKey} label={activeTheme?.label} />
                                    </div>

                                    <div className="mt-5 grid gap-5 md:grid-cols-2">
                                        <div>
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.numero')} interno <span className="text-state-blocked-dot">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                min="0"
                                                step="1"
                                                value={form.numero_trabajo}
                                                onChange={(event) => updateField('numero_trabajo', event.target.value)}
                                                className={inputClass('numero_trabajo')}
                                            />
                                            <InputError message={getError('numero_trabajo')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                Numero operativo CIETE
                                            </label>
                                            <input
                                                type="text"
                                                value={form.numero_trabajo_operativo}
                                                onChange={(event) => updateField('numero_trabajo_operativo', event.target.value)}
                                                className={inputClass('numero_trabajo_operativo')}
                                                placeholder="Código real del Excel"
                                            />
                                            <InputError message={getError('numero_trabajo_operativo')} className="mt-1.5" />
                                        </div>

                                        <div>
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.estado')} <span className="text-state-blocked-dot">*</span>
                                            </label>
                                            {estadoEsDerivado && (
                                                <p className="mb-2 text-xs text-text-muted">
                                                    El estado actual se calcula automáticamente por pedidos, facturación o cierre. Solo puedes cambiarlo a un estado manual.
                                                </p>
                                            )}
                                            <select
                                                value={form.estado}
                                                onChange={(event) => updateField('estado', event.target.value)}
                                                className={inputClass('estado')}
                                            >
                                                {estadoEsDerivado && (
                                                    <option value={form.estado}>
                                                        {t(statusLabelKey(form.estado) ?? 'trabajos.fields.estado')}
                                                    </option>
                                                )}
                                                {MANUAL_STATUS_OPTIONS.map((option) => (
                                                    <option key={option.value} value={option.value}>
                                                        {t(option.labelKey)}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={getError('estado')} className="mt-1.5" />
                                        </div>

                                        <div className="md:col-span-2">
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.estacion')} <span className="text-state-blocked-dot">*</span>
                                            </label>
                                            <select
                                                value={form.id_estacion_servicio}
                                                onChange={(event) => updateField('id_estacion_servicio', event.target.value)}
                                                className={inputClass('id_estacion_servicio')}
                                            >
                                                <option value="">{t('trabajos.clientSelector.stationPlaceholder')}</option>
                                                {filteredEstaciones.map((estacion) => (
                                                    <option key={estacion.id} value={estacion.id}>
                                                        {estacion.nombre} {estacion.codigo_estacion ? `(${estacion.codigo_estacion})` : ''}
                                                    </option>
                                                ))}
                                            </select>
                                            {filteredEstaciones.length === 0 && (
                                                <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                    <p className="text-xs font-medium text-amber-800">
                                                        {t('trabajos.clientSelector.noStationsAvailable')}
                                                    </p>
                                                    <p className="mt-1 text-xs text-amber-700">
                                                        Revisa Maestros &gt; Estaciones para este contexto antes de crear el trabajo.
                                                    </p>
                                                    <button
                                                        type="button"
                                                        onClick={() => router.visit(route('estaciones.index'))}
                                                        className="mt-2 rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-100"
                                                    >
                                                        Abrir estaciones
                                                    </button>
                                                </div>
                                            )}
                                            <InputError message={getError('id_estacion_servicio')} className="mt-1.5" />
                                        </div>

                                        <div className="md:col-span-2">
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.descripcion')} <span className="text-state-blocked-dot">*</span>
                                            </label>
                                            <textarea
                                                value={form.descripcion_trabajo}
                                                rows={3}
                                                onChange={(event) => updateField('descripcion_trabajo', event.target.value)}
                                                className={inputClass('descripcion_trabajo')}
                                            />
                                            <InputError message={getError('descripcion_trabajo')} className="mt-1.5" />
                                        </div>
                                    </div>
                                </section>

                                <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                                    <legend className="px-1 text-sm font-semibold text-text-main">{t('trabajos.sections.dates')}</legend>
                                    <div className="grid gap-5 md:grid-cols-2">
                                        <div>
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.fechaEncargo')} <span className="text-state-blocked-dot">*</span>
                                            </label>
                                            <input
                                                type="date"
                                                value={form.fecha_encargo}
                                                onChange={(event) => updateField('fecha_encargo', event.target.value)}
                                                className={inputClass('fecha_encargo')}
                                            />
                                            <InputError message={getError('fecha_encargo')} className="mt-1.5" />
                                        </div>
                                        <div>
                                            <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                {t('trabajos.fields.fechaTerminacion')}
                                            </label>
                                            <input
                                                type="date"
                                                value={form.fecha_terminado}
                                                onChange={(event) => updateField('fecha_terminado', event.target.value)}
                                                className={inputClass('fecha_terminado')}
                                            />
                                        </div>
                                    </div>
                                </fieldset>

                                {selectedClientKey === 'moeve' && (
                                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                                        <legend className="px-1 text-sm font-semibold text-text-main">{t('trabajos.clientSelector.moeve.section')}</legend>
                                        <p className="text-sm text-text-muted">{t('trabajos.clientSelector.moeve.helper')}</p>
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('trabajos.fields.contrato')} <span className="text-state-blocked-dot">*</span>
                                                </label>
                                                {filteredContratos.length > 0 ? (
                                                    <select
                                                        value={form.id_contrato}
                                                        onChange={(event) => updateField('id_contrato', event.target.value)}
                                                        className={inputClass('id_contrato')}
                                                    >
                                                        <option value="">{t('trabajos.clientSelector.moeve.contractPlaceholder')}</option>
                                                        {filteredContratos.map((contrato) => (
                                                            <option key={contrato.id} value={contrato.id}>
                                                                {contrato.nombre ?? contrato.codigo}
                                                            </option>
                                                        ))}
                                                    </select>
                                                ) : (
                                                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                        <p className="text-xs font-medium text-amber-800">
                                                            {t('trabajos.clientSelector.moeve.noContractsAvailable')}
                                                        </p>
                                                        <p className="mt-1 text-xs text-amber-700">
                                                            MOEVE necesita contrato activo para crear trabajos defendibles.
                                                        </p>
                                                        <button
                                                            type="button"
                                                            onClick={() => router.visit(route('maestros.contratos.index'))}
                                                            className="mt-2 rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-100"
                                                        >
                                                            Abrir contratos
                                                        </button>
                                                    </div>
                                                )}
                                                <InputError message={getError('id_contrato')} className="mt-1.5" />
                                            </div>

                                            <div>
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('trabajos.fields.categoria')}
                                                </label>
                                                <input
                                                    type="text"
                                                    value={form.categoria}
                                                    onChange={(event) => updateField('categoria', event.target.value)}
                                                    className={inputClass('categoria')}
                                                />
                                            </div>
                                        </div>
                                    </fieldset>
                                )}

                                {selectedClientKey === 'repsol' && (
                                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                                        <legend className="px-1 text-sm font-semibold text-text-main">{t('trabajos.clientSelector.repsol.section')}</legend>
                                        <p className="text-sm text-text-muted">{t('trabajos.clientSelector.repsol.helper')}</p>
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div>
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('trabajos.fields.tipoDocumento')} <span className="text-state-blocked-dot">*</span>
                                                </label>
                                                {filteredTiposDocumento.length > 0 ? (
                                                    <select
                                                        value={form.id_tipo_documento}
                                                        onChange={(event) => handleTipoDocumentoChange(event.target.value)}
                                                        className={inputClass('id_tipo_documento')}
                                                    >
                                                        <option value="">{t('trabajos.clientSelector.repsol.documentPlaceholder')}</option>
                                                        {filteredTiposDocumento.map((tipo) => (
                                                            <option key={tipo.id} value={tipo.id}>
                                                                {tipo.nombre}
                                                            </option>
                                                        ))}
                                                    </select>
                                                ) : (
                                                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                        <p className="text-xs font-medium text-amber-800">
                                                            {t('trabajos.clientSelector.repsol.noDocumentTypesAvailable')}
                                                        </p>
                                                        <p className="mt-1 text-xs text-amber-700">
                                                            REPSOL necesita tipos de documento activos para clasificar el trabajo.
                                                        </p>
                                                    </div>
                                                )}
                                                <InputError message={getError('id_tipo_documento')} className="mt-1.5" />
                                            </div>

                                            <div>
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('trabajos.fields.tipoTrabajo')} <span className="text-state-blocked-dot">*</span>
                                                </label>
                                                {filteredTiposTrabajo.length > 0 ? (
                                                    <select
                                                        value={form.id_tipo_trabajo}
                                                        onChange={(event) => updateField('id_tipo_trabajo', event.target.value)}
                                                        className={inputClass('id_tipo_trabajo')}
                                                    >
                                                        <option value="">{t('trabajos.clientSelector.repsol.workTypePlaceholder')}</option>
                                                        {filteredTiposTrabajo.map((tipo) => (
                                                            <option key={tipo.id} value={tipo.id}>
                                                                {tipo.nombre}
                                                            </option>
                                                        ))}
                                                    </select>
                                                ) : (
                                                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                        <p className="text-xs font-medium text-amber-800">
                                                            {t('trabajos.clientSelector.repsol.noWorkTypesAvailable')}
                                                        </p>
                                                        <p className="mt-1 text-xs text-amber-700">
                                                            Selecciona un tipo de documento con tipos de trabajo activos o revisa los catalogos maestros.
                                                        </p>
                                                    </div>
                                                )}
                                                <InputError message={getError('id_tipo_trabajo')} className="mt-1.5" />
                                            </div>

                                            <div>
                                                <label className="mb-1.5 block text-sm font-medium text-text-main">
                                                    {t('trabajos.fields.numeroAviso')}
                                                </label>
                                                <input
                                                    type="text"
                                                    value={form.numero_aviso}
                                                    onChange={(event) => updateField('numero_aviso', event.target.value)}
                                                    className={inputClass('numero_aviso')}
                                                />
                                            </div>
                                        </div>
                                    </fieldset>
                                )}

                                <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                                    <legend className="px-1 text-sm font-semibold text-text-main">{t('trabajos.fields.observaciones')}</legend>
                                    <textarea
                                        value={form.observaciones}
                                        rows={4}
                                        onChange={(event) => updateField('observaciones', event.target.value)}
                                        className={inputClass('observaciones')}
                                    />
                                </fieldset>

                                <div className="ciete-form-actions border-0 bg-transparent px-0 py-0 shadow-none sm:justify-end">
                                    <button
                                        type="button"
                                        onClick={() => router.visit(route('trabajos.index'))}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-md border border-border px-5 py-2 text-sm font-semibold text-text-main transition hover:bg-surface-2 sm:w-auto"
                                    >
                                        {t('common.actions.cancel')}
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={loading || (!isEditing && isAllContext)}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-(--ciete-red) px-5 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60 sm:w-auto"
                                    >
                                        {loading ? '...' : t('common.actions.save')}
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
