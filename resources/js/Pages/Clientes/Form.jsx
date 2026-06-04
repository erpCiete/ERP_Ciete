import InputError from '@/Components/InputError';
import ContextualPageHeader from '@/Components/ContextualPageHeader';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import { useClientes } from '@/Hooks/useClientes';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { exceedsMaxLength, hasValidSpanishTaxId, hasValidUrlFormat, isBlank, normalizeTaxId } from '@/validation/formRules';

const EMPTY_FORM = {
    nombre: '',
    nombre_comercial: '',
    razon_social: '',
    cif: '',
    web: '',
    observaciones: '',
    activo: true,
};

const CONTEXT_OPTIONS = ['repsol', 'moeve', 'bp', 'galp', 'otros'];
const CLIENTE_FIELDS = ['nombre', 'nombre_comercial', 'razon_social', 'cif', 'web', 'observaciones', 'activo'];

function validateClienteForm(form, t) {
    const nextErrors = {};

    if (isBlank(form.nombre)) {
        nextErrors.nombre = t('common.validation.required');
    } else if (exceedsMaxLength(form.nombre, 180)) {
        nextErrors.nombre = t('common.validation.maxLength', { max: 180 });
    }

    if (exceedsMaxLength(form.nombre_comercial, 180)) {
        nextErrors.nombre_comercial = t('common.validation.maxLength', { max: 180 });
    }

    if (exceedsMaxLength(form.razon_social, 220)) {
        nextErrors.razon_social = t('common.validation.maxLength', { max: 220 });
    }

    if (!isBlank(form.cif) && !hasValidSpanishTaxId(form.cif)) {
        nextErrors.cif = t('common.validation.taxId');
    }

    if (exceedsMaxLength(form.web, 255)) {
        nextErrors.web = t('common.validation.maxLength', { max: 255 });
    } else if (!hasValidUrlFormat(form.web)) {
        nextErrors.web = t('common.validation.url');
    }

    return nextErrors;
}

function normalizeCliente(cliente) {
    return {
        nombre: cliente?.nombre ?? '',
        nombre_comercial: cliente?.nombre_comercial ?? cliente?.operador ?? '',
        razon_social: cliente?.razon_social ?? '',
        cif: cliente?.cif ?? '',
        web: cliente?.web ?? '',
        observaciones: cliente?.observaciones ?? '',
        activo: cliente?.activo ?? true,
    };
}

export default function ClientesForm({ clienteId = null }) {
    const { t } = useI18n();
    const { getCliente, saveCliente, loading, errors, clearErrors, clearFieldError } = useClientes();
    const mastersBack = useMastersBackLink();

    const [form, setForm] = useState(EMPTY_FORM);
    const [status, setStatus] = useState(clienteId ? 'loading' : 'idle');
    const [submitError, setSubmitError] = useState('');
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);

    const isEditing = clienteId !== null;
    const localErrors = useMemo(() => validateClienteForm(form, t), [form, t]);

    useEffect(() => {
        if (!clienteId) {
            return;
        }

        let active = true;

        getCliente(clienteId)
            .then((response) => {
                if (!active) {
                    return;
                }

                setForm(normalizeCliente(response?.data));
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
    }, [clienteId, getCliente]);

    const pageTitle = useMemo(
        () => (isEditing ? t('clientes.edit') : t('clientes.create')),
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
        setSubmitError('');
        setForm((current) => ({ ...current, [field]: value }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitAttempted(true);
        setTouched(Object.fromEntries(CLIENTE_FIELDS.map((field) => [field, true])));
        setSubmitError('');

        if (Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm'));
            return;
        }

        clearErrors();

        try {
            await saveCliente(
                {
                    ...form,
                    tipo_empresa: 'cliente',
                },
                clienteId,
            );

            router.visit(route('clientes.index'));
        } catch (error) {
            if (error?.response?.status === 422) {
                setSubmitError(t('common.validation.reviewForm'));
                return;
            }

            setSubmitError(t('clientes.saveError'));
        }
    };

    if (status === 'loading') {
        return (
            <AuthenticatedLayout
                header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
            >
                <Head title={pageTitle} />
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm text-text-muted">
                    {t('clientes.loading')}
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
                    <p>{t('clientes.loadError')}</p>
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

            <div className="ciete-page max-w-3xl">
                <ContextualPageHeader
                    eyebrow={t('nav.groups.masters')}
                    title={pageTitle}
                    description={isEditing ? t('clientes.editDescription') : t('clientes.createDescription')}
                    backHref={route('maestros.index')}
                />

                <form onSubmit={handleSubmit} noValidate className="space-y-6 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <div className="grid gap-5 md:grid-cols-2">
                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.name')}</span>
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
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.context')}</span>
                            <select
                                value={form.nombre_comercial}
                                onBlur={() => markFieldTouched('nombre_comercial')}
                                onChange={(event) => updateField('nombre_comercial', event.target.value)}
                                aria-invalid={Boolean(getFieldError('nombre_comercial'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            >
                                <option value="">{t('clientes.contextPlaceholder')}</option>
                                {CONTEXT_OPTIONS.map((option) => (
                                    <option key={option} value={option}>
                                        {t(`clientes.operators.${option}`)}
                                    </option>
                                ))}
                            </select>
                            <InputError message={getFieldError('nombre_comercial')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.businessName')}</span>
                            <input
                                type="text"
                                value={form.razon_social}
                                maxLength={220}
                                onBlur={() => markFieldTouched('razon_social')}
                                onChange={(event) => updateField('razon_social', event.target.value)}
                                aria-invalid={Boolean(getFieldError('razon_social'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('razon_social')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.cif')}</span>
                            <input
                                type="text"
                                value={form.cif}
                                maxLength={9}
                                onBlur={() => markFieldTouched('cif')}
                                onChange={(event) => updateField('cif', normalizeTaxId(event.target.value))}
                                aria-invalid={Boolean(getFieldError('cif'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('cif')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.website')}</span>
                            <input
                                type="url"
                                value={form.web}
                                maxLength={255}
                                onBlur={() => markFieldTouched('web')}
                                onChange={(event) => updateField('web', event.target.value)}
                                aria-invalid={Boolean(getFieldError('web'))}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint/70 focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('web')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">{t('clientes.fields.notes')}</span>
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
                            <span className="text-sm font-medium text-text-main">{t('clientes.fields.active')}</span>
                        </label>
                    </div>

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
                            {loading ? t('clientes.saving') : t('common.actions.save')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
