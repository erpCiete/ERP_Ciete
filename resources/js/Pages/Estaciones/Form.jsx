import InputError from '@/Components/InputError';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import { useClientes } from '@/Hooks/useClientes';
import { useEstaciones } from '@/Hooks/useEstaciones';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import {
    exceedsMaxLength,
    hasValidSpanishPostalCode,
    isBlank,
    normalizePostalCode,
} from '@/validation/formRules';

const EMPTY_FORM = {
    id_empresa_cliente: '',
    nombre: '',
    codigo_estacion: '',
    estado: '',
    direccion: '',
    codigo_postal: '',
    poblacion: '',
    provincia: '',
    pais: 'Espana',
    observaciones: '',
    activo: true,
    fecha_baja: '',
};

const ESTACION_FIELDS = [
    'id_empresa_cliente',
    'nombre',
    'codigo_estacion',
    'estado',
    'direccion',
    'codigo_postal',
    'poblacion',
    'provincia',
    'pais',
    'observaciones',
    'activo',
];

function validateEstacionForm(form, t) {
    const nextErrors = {};

    if (isBlank(form.id_empresa_cliente)) {
        nextErrors.id_empresa_cliente = t('common.validation.required');
    }

    if (isBlank(form.nombre)) {
        nextErrors.nombre = t('common.validation.required');
    } else if (exceedsMaxLength(form.nombre, 180)) {
        nextErrors.nombre = t('common.validation.maxLength', { max: 180 });
    }

    if (isBlank(form.codigo_estacion)) {
        nextErrors.codigo_estacion = t('common.validation.required');
    } else if (exceedsMaxLength(form.codigo_estacion, 80)) {
        nextErrors.codigo_estacion = t('common.validation.maxLength', { max: 80 });
    }

    if (exceedsMaxLength(form.estado, 50)) {
        nextErrors.estado = t('common.validation.maxLength', { max: 50 });
    }

    if (exceedsMaxLength(form.direccion, 255)) {
        nextErrors.direccion = t('common.validation.maxLength', { max: 255 });
    }

    if (exceedsMaxLength(form.codigo_postal, 5)) {
        nextErrors.codigo_postal = t('common.validation.maxLength', { max: 5 });
    } else if (!hasValidSpanishPostalCode(form.codigo_postal)) {
        nextErrors.codigo_postal = t('common.validation.postalCode');
    }

    if (exceedsMaxLength(form.poblacion, 120)) {
        nextErrors.poblacion = t('common.validation.maxLength', { max: 120 });
    }

    if (exceedsMaxLength(form.provincia, 120)) {
        nextErrors.provincia = t('common.validation.maxLength', { max: 120 });
    }

    if (exceedsMaxLength(form.pais, 120)) {
        nextErrors.pais = t('common.validation.maxLength', { max: 120 });
    }

    return nextErrors;
}

function normalizeEstacion(estacion) {
    return {
        id_empresa_cliente: estacion?.id_empresa_cliente ? String(estacion.id_empresa_cliente) : '',
        nombre: estacion?.nombre ?? '',
        codigo_estacion: estacion?.codigo_estacion ?? '',
        estado: estacion?.estado ?? '',
        direccion: estacion?.direccion ?? '',
        codigo_postal: estacion?.codigo_postal ?? '',
        poblacion: estacion?.municipio ?? estacion?.poblacion ?? '',
        provincia: estacion?.provincia ?? '',
        pais: estacion?.pais ?? 'Espana',
        observaciones: estacion?.observaciones ?? '',
        activo: estacion?.activo ?? true,
        fecha_baja: estacion?.fecha_baja ?? '',
    };
}

function FormField({ label, error, children, help = '' }) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-text-main">{label}</span>
            {children}
            {help && <p className="mt-2 text-xs text-text-muted">{help}</p>}
            <InputError message={error} className="mt-2" />
        </label>
    );
}

export default function EstacionesForm({ estacionId = null }) {
    const { t } = useI18n();
    const { getClientes, clearFieldError: clearClienteFieldError } = useClientes();
    const { getEstacion, saveEstacion, loading, errors, clearErrors, clearFieldError } = useEstaciones();
    const mastersBack = useMastersBackLink();

    const [clientes, setClientes] = useState([]);
    const [form, setForm] = useState(EMPTY_FORM);
    const [originalSensitive, setOriginalSensitive] = useState({ codigo_estacion: '', nombre: '' });
    const [sensitiveConfirmed, setSensitiveConfirmed] = useState(false);
    const [status, setStatus] = useState(estacionId ? 'loading' : 'idle');
    const [submitError, setSubmitError] = useState('');
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);

    const isEditing = estacionId !== null;
    const localErrors = useMemo(() => validateEstacionForm(form, t), [form, t]);

    useEffect(() => {
        let active = true;

        getClientes({ per_page: 100 })
            .then((result) => {
                if (active) {
                    setClientes(result?.data ?? []);
                }
            })
            .catch(() => {
                if (active) {
                    setClientes([]);
                }
            });

        return () => {
            active = false;
        };
    }, [getClientes]);

    useEffect(() => {
        if (!estacionId) {
            return;
        }

        let active = true;

        getEstacion(estacionId)
            .then((response) => {
                if (!active) {
                    return;
                }

                const normalized = normalizeEstacion(response?.data);
                setForm(normalized);
                setOriginalSensitive({
                    codigo_estacion: normalized.codigo_estacion,
                    nombre: normalized.nombre,
                });
                setStatus('ready');
            })
            .catch(() => {
                if (!active) {
                    return;
                }

                setStatus('error');
            });

        return () => {
            active = false;
        };
    }, [estacionId, getEstacion]);

    const pageTitle = useMemo(
        () => (isEditing ? t('estaciones.edit') : t('estaciones.create')),
        [isEditing, t],
    );

    const markFieldTouched = (field) => {
        setTouched((currentTouched) => (currentTouched[field] ? currentTouched : { ...currentTouched, [field]: true }));
    };

    const getFieldError = (field) => {
        if (!submitAttempted && !touched[field] && !errors[field]) {
            return '';
        }

        return localErrors[field] ?? errors[field]?.[0] ?? '';
    };

    const updateField = (field, value) => {
        markFieldTouched(field);
        clearFieldError(field);

        if (field === 'id_empresa_cliente') {
            clearClienteFieldError(field);
        }

        setSubmitError('');
        setForm((current) => ({ ...current, [field]: value }));
    };

    const sensitiveChanged = isEditing && (
        form.codigo_estacion !== originalSensitive.codigo_estacion ||
        form.nombre !== originalSensitive.nombre
    );

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitAttempted(true);
        setTouched(Object.fromEntries(ESTACION_FIELDS.map((field) => [field, true])));
        setSubmitError('');

        if (Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm'));
            return;
        }

        if (sensitiveChanged && !sensitiveConfirmed) {
            setSubmitError('Debes confirmar que has leído el aviso sobre el impacto de cambiar código o nombre de estación.');
            return;
        }

        clearErrors();

        try {
            await saveEstacion(
                {
                    ...form,
                    id_empresa_cliente: Number(form.id_empresa_cliente),
                },
                estacionId,
            );

            router.visit(route('estaciones.index'));
        } catch (error) {
            if (error?.response?.status === 422) {
                setSubmitError(t('common.validation.reviewForm'));
                return;
            }

            setSubmitError(t('estaciones.saveError'));
        }
    };

    if (status === 'loading') {
        return (
            <AuthenticatedLayout
                header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
            >
                <Head title={pageTitle} />
                <div className="rounded-2xl border border-border bg-surface p-6 text-text-muted shadow-sm">
                    {t('estaciones.loading')}
                </div>
            </AuthenticatedLayout>
        );
    }

    if (status === 'error') {
        return (
            <AuthenticatedLayout
                header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
            >
                <Head title={pageTitle} />
                <div className="rounded-2xl border border-border bg-surface p-6 text-text-muted shadow-sm">
                    <p>{t('estaciones.loadError')}</p>
                    <button
                        type="button"
                        onClick={() => mastersBack.href && router.visit(mastersBack.href)}
                        className="mt-4 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                    >
                        {mastersBack.label}
                    </button>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
        >
            <Head title={pageTitle} />

            <div className="ciete-page max-w-4xl">
                <ContextualPageHeader
                    eyebrow={t('nav.groups.masters')}
                    title={pageTitle}
                    description={isEditing ? t('estaciones.editDescription') : t('estaciones.createDescription')}
                    backHref={route('maestros.index')}
                />

                <form onSubmit={handleSubmit} noValidate className="space-y-6 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    {isEditing && (
                        <div className="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3">
                            <p className="text-sm font-semibold text-amber-800">⚠ Campos con impacto en el histórico</p>
                            <p className="mt-1 text-sm text-amber-700">
                                El <strong>código de estación</strong> y el <strong>nombre</strong> se usan en trabajos, pedidos y facturas históricas.
                                Cambiarlos afectará a cómo se muestran registros anteriores.
                                Solo modifícalos si es estrictamente necesario y tras confirmar el impacto.
                            </p>
                        </div>
                    )}
                    <section className="space-y-5">
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wide text-text-hint">
                                {t('estaciones.sections.primary')}
                            </h3>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="md:col-span-2">
                                <FormField
                                    label={t('estaciones.fields.client')}
                                    error={getFieldError('id_empresa_cliente')}
                                >
                                    <select
                                        value={form.id_empresa_cliente}
                                        required
                                        onBlur={() => markFieldTouched('id_empresa_cliente')}
                                        onChange={(event) => updateField('id_empresa_cliente', event.target.value)}
                                        aria-invalid={Boolean(getFieldError('id_empresa_cliente'))}
                                        className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                    >
                                        <option value="">{t('estaciones.clientPlaceholder')}</option>
                                        {clientes.map((cliente) => (
                                            <option key={cliente.id} value={cliente.id}>
                                                {cliente.razon_social || cliente.nombre}
                                            </option>
                                        ))}
                                    </select>
                                </FormField>
                            </div>

                            <FormField
                                label={t('estaciones.fields.stationCode')}
                                help={t('estaciones.fields.stationCodeHelp')}
                                error={getFieldError('codigo_estacion')}
                            >
                                <input
                                    type="text"
                                    value={form.codigo_estacion}
                                    required
                                    maxLength={80}
                                    onBlur={() => markFieldTouched('codigo_estacion')}
                                    onChange={(event) => updateField('codigo_estacion', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('codigo_estacion'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm font-mono text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <FormField
                                label={t('estaciones.fields.name')}
                                error={getFieldError('nombre')}
                            >
                                <input
                                    type="text"
                                    value={form.nombre}
                                    required
                                    maxLength={180}
                                    onBlur={() => markFieldTouched('nombre')}
                                    onChange={(event) => updateField('nombre', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('nombre'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <FormField
                                label={t('estaciones.fields.city')}
                                error={getFieldError('poblacion')}
                            >
                                <input
                                    type="text"
                                    value={form.poblacion}
                                    maxLength={120}
                                    onBlur={() => markFieldTouched('poblacion')}
                                    onChange={(event) => updateField('poblacion', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('poblacion'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <FormField
                                label={t('estaciones.fields.province')}
                                error={getFieldError('provincia')}
                            >
                                <input
                                    type="text"
                                    value={form.provincia}
                                    maxLength={120}
                                    onBlur={() => markFieldTouched('provincia')}
                                    onChange={(event) => updateField('provincia', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('provincia'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>
                        </div>
                    </section>

                    <section className="space-y-5 border-t border-border pt-6">
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wide text-text-hint">
                                {t('estaciones.sections.secondary')}
                            </h3>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="md:col-span-2">
                                <FormField
                                    label={t('estaciones.fields.address')}
                                    help={t('estaciones.fields.addressHelp')}
                                    error={getFieldError('direccion')}
                                >
                                    <input
                                        type="text"
                                        value={form.direccion}
                                        maxLength={255}
                                        onBlur={() => markFieldTouched('direccion')}
                                        onChange={(event) => updateField('direccion', event.target.value)}
                                        aria-invalid={Boolean(getFieldError('direccion'))}
                                        className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                    />
                                </FormField>
                            </div>

                            <FormField
                                label={t('estaciones.fields.postalCode')}
                                error={getFieldError('codigo_postal')}
                            >
                                <input
                                    type="text"
                                    value={form.codigo_postal}
                                    inputMode="numeric"
                                    maxLength={5}
                                    onBlur={() => markFieldTouched('codigo_postal')}
                                    onChange={(event) => updateField('codigo_postal', normalizePostalCode(event.target.value))}
                                    aria-invalid={Boolean(getFieldError('codigo_postal'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <FormField
                                label={t('estaciones.fields.country')}
                                error={getFieldError('pais')}
                            >
                                <input
                                    type="text"
                                    value={form.pais}
                                    maxLength={120}
                                    onBlur={() => markFieldTouched('pais')}
                                    onChange={(event) => updateField('pais', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('pais'))}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <FormField
                                label={t('estaciones.fields.status')}
                                error={getFieldError('estado')}
                            >
                                <input
                                    type="text"
                                    value={form.estado}
                                    maxLength={50}
                                    onBlur={() => markFieldTouched('estado')}
                                    onChange={(event) => updateField('estado', event.target.value)}
                                    aria-invalid={Boolean(getFieldError('estado'))}
                                    placeholder={t('estaciones.fields.statusPlaceholder')}
                                    className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                />
                            </FormField>

                            <div className="md:col-span-2">
                                <FormField
                                    label={t('estaciones.fields.notes')}
                                    error={getFieldError('observaciones')}
                                >
                                    <textarea
                                        value={form.observaciones}
                                        onBlur={() => markFieldTouched('observaciones')}
                                        onChange={(event) => updateField('observaciones', event.target.value)}
                                        aria-invalid={Boolean(getFieldError('observaciones'))}
                                        rows={4}
                                        className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                                    />
                                </FormField>
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4 border-t border-border pt-6">
                        <div>
                            <h3 className="text-sm font-semibold uppercase tracking-wide text-text-hint">
                                {t('estaciones.sections.lifecycle')}
                            </h3>
                        </div>

                        <label className="flex items-start gap-3">
                            <input
                                type="checkbox"
                                checked={form.activo}
                                onBlur={() => markFieldTouched('activo')}
                                onChange={(event) => updateField('activo', event.target.checked)}
                                className="mt-0.5 rounded-sm border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <div>
                                <span className="text-sm font-medium text-text-main">{t('estaciones.fields.active')}</span>
                                <p className="mt-1 text-xs text-text-muted">{t('estaciones.fields.activeHelp')}</p>
                                {!form.activo && form.fecha_baja && (
                                    <p className="mt-2 text-xs font-medium text-text-muted">
                                        {t('estaciones.fields.deactivatedOn', { date: form.fecha_baja })}
                                    </p>
                                )}
                            </div>
                        </label>
                    </section>

                    {sensitiveChanged && (
                        <div className="rounded-lg border border-red-300 bg-red-50 px-4 py-3">
                            <p className="text-sm font-semibold text-red-800">⚠ Has modificado campos con impacto en el histórico</p>
                            <p className="mt-1 text-sm text-red-700">
                                Has cambiado el <strong>código</strong> o el <strong>nombre</strong> de esta estación.
                                Todos los trabajos, pedidos y facturas que ya usan estos datos mostrarán los nuevos valores.
                                Asegúrate de que el cambio es correcto y deliberado.
                            </p>
                            <label className="mt-3 flex items-start gap-2">
                                <input
                                    type="checkbox"
                                    checked={sensitiveConfirmed}
                                    onChange={(e) => setSensitiveConfirmed(e.target.checked)}
                                    className="mt-0.5 rounded-sm border-red-400 text-red-600"
                                />
                                <span className="text-sm font-medium text-red-800">
                                    Confirmo que entiendo el impacto en el histórico y quiero guardar este cambio.
                                </span>
                            </label>
                        </div>
                    )}

                    {submitError && <p className="text-sm text-primary">{submitError}</p>}

                    <div className="ciete-form-actions border-0 border-t bg-transparent px-0 py-4 shadow-none sm:justify-end">
                        <button
                            type="button"
                            onClick={() => mastersBack.href && router.visit(mastersBack.href)}
                            className="inline-flex w-full items-center justify-center text-sm font-medium text-text-muted transition hover:text-text-main sm:w-auto"
                        >
                            {t('common.actions.cancel')}
                        </button>

                        <button
                            type="submit"
                            disabled={loading}
                            className="inline-flex w-full items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60 sm:w-auto"
                        >
                            {loading ? t('estaciones.saving') : t('common.actions.save')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
