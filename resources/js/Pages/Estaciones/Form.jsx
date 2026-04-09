import InputError from '@/Components/InputError';
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
    codigo_estacion_interno: '',
    cod_repsol: '',
    cod_cepsa: '',
    tipo: '',
    direccion: '',
    codigo_postal: '',
    poblacion: '',
    provincia: '',
    pais: 'Espana',
    observaciones: '',
    activo: true,
};

const ESTACION_FIELDS = [
    'id_empresa_cliente',
    'nombre',
    'codigo_estacion_interno',
    'cod_repsol',
    'cod_cepsa',
    'tipo',
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

    if (exceedsMaxLength(form.codigo_estacion_interno, 32)) {
        nextErrors.codigo_estacion_interno = t('common.validation.maxLength', { max: 32 });
    }

    if (exceedsMaxLength(form.cod_repsol, 13)) {
        nextErrors.cod_repsol = t('common.validation.maxLength', { max: 13 });
    }

    if (exceedsMaxLength(form.cod_cepsa, 12)) {
        nextErrors.cod_cepsa = t('common.validation.maxLength', { max: 12 });
    }

    if (exceedsMaxLength(form.tipo, 120)) {
        nextErrors.tipo = t('common.validation.maxLength', { max: 120 });
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
        codigo_estacion_interno: estacion?.codigo_estacion_interno ?? '',
        cod_repsol: estacion?.cod_repsol ?? '',
        cod_cepsa: estacion?.cod_cepsa ?? '',
        tipo: estacion?.tipo ?? '',
        direccion: estacion?.direccion ?? '',
        codigo_postal: estacion?.codigo_postal ?? '',
        poblacion: estacion?.poblacion ?? '',
        provincia: estacion?.provincia ?? '',
        pais: estacion?.pais ?? 'Espana',
        observaciones: estacion?.observaciones ?? '',
        activo: estacion?.activo ?? true,
    };
}

export default function EstacionesForm({ estacionId = null }) {
    const { t } = useI18n();
    const { getClientes, clearFieldError: clearClienteFieldError } = useClientes();
    const { getEstacion, saveEstacion, loading, errors, clearErrors, clearFieldError } = useEstaciones();

    const [clientes, setClientes] = useState([]);
    const [form, setForm] = useState(EMPTY_FORM);
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

                setForm(normalizeEstacion(response?.data));
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

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitAttempted(true);
        setTouched(Object.fromEntries(ESTACION_FIELDS.map((field) => [field, true])));
        setSubmitError('');

        if (Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm'));
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
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm text-text-muted">
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
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm text-text-muted">
                    <p>{t('estaciones.loadError')}</p>
                    <button
                        type="button"
                        onClick={() => router.visit(route('estaciones.index'))}
                        className="mt-4 text-sm font-medium text-(--ciete-red) transition hover:text-(--ciete-red-dark)"
                    >
                        {t('common.actions.back')}
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

            <div className="mx-auto max-w-4xl space-y-6">
                <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">{t('nav.groups.masters')}</p>
                    <h1 className="mt-2 text-2xl font-semibold text-text-main">{pageTitle}</h1>
                    <p className="mt-2 text-sm text-text-muted">
                        {isEditing ? t('estaciones.editDescription') : t('estaciones.createDescription')}
                    </p>
                </section>

                <form onSubmit={handleSubmit} noValidate className="space-y-6 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <div className="grid gap-5 md:grid-cols-2">
                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.client')}</span>
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
                            <InputError message={getFieldError('id_empresa_cliente')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.name')}</span>
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
                            <InputError message={getFieldError('nombre')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.internalCode')}</span>
                            <input
                                type="text"
                                value={form.codigo_estacion_interno}
                                maxLength={32}
                                onBlur={() => markFieldTouched('codigo_estacion_interno')}
                                onChange={(event) => updateField('codigo_estacion_interno', event.target.value)}
                                aria-invalid={Boolean(getFieldError('codigo_estacion_interno'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('codigo_estacion_interno')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.type')}</span>
                            <input
                                type="text"
                                value={form.tipo}
                                maxLength={120}
                                onBlur={() => markFieldTouched('tipo')}
                                onChange={(event) => updateField('tipo', event.target.value)}
                                aria-invalid={Boolean(getFieldError('tipo'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('tipo')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.repsolCode')}</span>
                            <input
                                type="text"
                                value={form.cod_repsol}
                                maxLength={13}
                                onBlur={() => markFieldTouched('cod_repsol')}
                                onChange={(event) => updateField('cod_repsol', event.target.value)}
                                aria-invalid={Boolean(getFieldError('cod_repsol'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('cod_repsol')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.cepsaCode')}</span>
                            <input
                                type="text"
                                value={form.cod_cepsa}
                                maxLength={12}
                                onBlur={() => markFieldTouched('cod_cepsa')}
                                onChange={(event) => updateField('cod_cepsa', event.target.value)}
                                aria-invalid={Boolean(getFieldError('cod_cepsa'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('cod_cepsa')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.address')}</span>
                            <input
                                type="text"
                                value={form.direccion}
                                maxLength={255}
                                onBlur={() => markFieldTouched('direccion')}
                                onChange={(event) => updateField('direccion', event.target.value)}
                                aria-invalid={Boolean(getFieldError('direccion'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('direccion')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.postalCode')}</span>
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
                            <InputError message={getFieldError('codigo_postal')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.city')}</span>
                            <input
                                type="text"
                                value={form.poblacion}
                                maxLength={120}
                                onBlur={() => markFieldTouched('poblacion')}
                                onChange={(event) => updateField('poblacion', event.target.value)}
                                aria-invalid={Boolean(getFieldError('poblacion'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('poblacion')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.province')}</span>
                            <input
                                type="text"
                                value={form.provincia}
                                maxLength={120}
                                onBlur={() => markFieldTouched('provincia')}
                                onChange={(event) => updateField('provincia', event.target.value)}
                                aria-invalid={Boolean(getFieldError('provincia'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('provincia')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.country')}</span>
                            <input
                                type="text"
                                value={form.pais}
                                maxLength={120}
                                onBlur={() => markFieldTouched('pais')}
                                onChange={(event) => updateField('pais', event.target.value)}
                                aria-invalid={Boolean(getFieldError('pais'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('pais')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('estaciones.fields.notes')}</span>
                            <textarea
                                value={form.observaciones}
                                onBlur={() => markFieldTouched('observaciones')}
                                onChange={(event) => updateField('observaciones', event.target.value)}
                                aria-invalid={Boolean(getFieldError('observaciones'))}
                                rows={4}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('observaciones')} className="mt-2" />
                        </label>

                        <label className="flex items-center gap-3 md:col-span-2">
                            <input
                                type="checkbox"
                                checked={form.activo}
                                onBlur={() => markFieldTouched('activo')}
                                onChange={(event) => updateField('activo', event.target.checked)}
                                className="rounded-sm border-border text-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <span className="text-sm font-medium text-text-main">{t('estaciones.fields.active')}</span>
                        </label>
                    </div>

                    {submitError && <p className="text-sm text-primary">{submitError}</p>}

                    <div className="flex items-center justify-between border-t border-border pt-4">
                        <button
                            type="button"
                            onClick={() => router.visit(route('estaciones.index'))}
                            className="text-sm font-medium text-text-muted transition hover:text-text-main"
                        >
                            {t('common.actions.cancel')}
                        </button>

                        <button
                            type="submit"
                            disabled={loading}
                            className="inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60"
                        >
                            {loading ? t('estaciones.saving') : t('common.actions.save')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
