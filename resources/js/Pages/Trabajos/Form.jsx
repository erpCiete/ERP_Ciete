import InputError from '@/Components/InputError';
import { useTrabajos } from '@/Hooks/useTrabajos';
import { useEstaciones } from '@/Hooks/useEstaciones';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { isBlank, exceedsMaxLength } from '@/validation/formRules';

const EMPTY_FORM = {
    numero_trabajo: '',
    descripcion_trabajo: '',
    id_estacion_servicio: '',
    fecha_encargo: new Date().toISOString().split('T')[0],
    observaciones: '',
    activo: true,
};

const TRABAJO_FIELDS = [
    'numero_trabajo',
    'descripcion_trabajo',
    'id_estacion_servicio',
    'fecha_encargo',
];

function validateTrabajoForm(form, t) {
    const nextErrors = {};

    if (isBlank(form.numero_trabajo)) {
        nextErrors.numero_trabajo = t('common.validation.required');
    }

    if (isBlank(form.descripcion_trabajo)) {
        nextErrors.descripcion_trabajo = t('common.validation.required');
    } else if (exceedsMaxLength(form.descripcion_trabajo, 255)) {
        nextErrors.descripcion_trabajo = t('common.validation.maxLength', { max: 255 });
    }

    if (isBlank(form.id_estacion_servicio)) {
        nextErrors.id_estacion_servicio = t('common.validation.required');
    }

    if (isBlank(form.fecha_encargo)) {
        nextErrors.fecha_encargo = t('common.validation.required');
    }

    return nextErrors;
}

function normalizeTrabajo(trabajo) {
    return {
        numero_trabajo: trabajo?.numero_trabajo ?? '',
        descripcion_trabajo: trabajo?.descripcion_trabajo ?? '',
        id_estacion_servicio: trabajo?.id_estacion_servicio ? String(trabajo.id_estacion_servicio) : '',
        fecha_encargo: trabajo?.fecha_encargo ?? new Date().toISOString().split('T')[0],
        observaciones: trabajo?.observaciones ?? '',
        activo: trabajo?.activo ?? true,
    };
}

export default function TrabajosForm({ trabajoId = null }) {
    const { t } = useI18n();
    const { getTrabajo, saveTrabajo, loading, errors, clearErrors, clearFieldError } = useTrabajos();
// Renombramos los errores de estaciones a 'errorsEst' para que no choquen
    const { estaciones: listaEstaciones, errors: errorsEst } = useEstaciones();

    const [form, setForm] = useState(EMPTY_FORM);
    const [status, setStatus] = useState(trabajoId ? 'loading' : 'idle');
    const [submitError, setSubmitError] = useState('');
    const [touched, setTouched] = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);

    const isEditing = trabajoId !== null;
    const localErrors = useMemo(() => validateTrabajoForm(form, t), [form, t]);


    useEffect(() => {
        if (!trabajoId) return;
        getTrabajo(trabajoId)
            .then(res => {
                setForm(normalizeTrabajo(res?.data));
                setStatus('ready');
            })
            .catch(() => setStatus('error'));
    }, [trabajoId, getTrabajo]);

    const pageTitle = useMemo(() => (isEditing ? t('trabajos.edit') : t('trabajos.create')), [isEditing, t]);

    const markFieldTouched = (field) => {
        setTouched(prev => ({ ...prev, [field]: true }));
    };

    const getFieldError = (field) => {
        // Si submitAttempted es false y no hemos tocado el campo, solo mostramos si hay error del servidor
        // Usamos el operador ?. (optional chaining) para que no explote si errors es undefined
        if (!submitAttempted && !touched[field] && !errors?.[field]) return '';
        
        // Prioridad: 1. Error local (validación JS) | 2. Error del servidor (Laravel)
        return localErrors[field] ?? errors?.[field]?.[0] ?? '';
    };

    const updateField = (field, value) => {
        markFieldTouched(field);
        clearFieldError(field);
        setForm(prev => ({ ...prev, [field]: value }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitAttempted(true);
        setTouched(Object.fromEntries(TRABAJO_FIELDS.map(f => [f, true])));

        if (Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm'));
            return;
        }

        try {
            await saveTrabajo({ ...form, id_estacion_servicio: Number(form.id_estacion_servicio) }, trabajoId);
            router.visit(route('trabajos.index'));
        } catch (error) {
            setSubmitError(t('trabajos.saveError'));
        }
    };

    if (status === 'loading') {
        return (
            <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}>
                <div className="p-6">{t('trabajos.loading')}</div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}>
            <Head title={pageTitle} />
            <div className="mx-auto max-w-4xl space-y-6">
                <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">{t('nav.groups.operations')}</p>
                    <h1 className="mt-2 text-2xl font-semibold text-text-main">{pageTitle}</h1>
                </section>

                <form onSubmit={handleSubmit} noValidate className="space-y-6 rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <div className="grid gap-5 md:grid-cols-2">

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">Nº de Trabajo</span>
                            <input
                                type="text"
                                value={form.numero_trabajo}
                                required
                                onChange={e => updateField('numero_trabajo', e.target.value)}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('numero_trabajo')} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">Fecha de Encargo</span>
                            <input
                                type="date"
                                value={form.fecha_encargo}
                                required
                                onChange={e => updateField('fecha_encargo', e.target.value)}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('fecha_encargo')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">Estación de Servicio</span>
                            <select
                                value={form.id_estacion_servicio}
                                required
                                onChange={e => updateField('id_estacion_servicio', e.target.value)}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            >
                                <option value="">Selecciona una estación</option>
                                {listaEstaciones.map(est => (
                                    <option key={est.id} value={est.id}>{est.nombre} ({est.codigo_estacion})</option>
                                ))}
                            </select>
                            <InputError message={getFieldError('id_estacion_servicio')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">Descripción del Trabajo</span>
                            <textarea
                                value={form.descripcion_trabajo}
                                required
                                rows={2}
                                onChange={e => updateField('descripcion_trabajo', e.target.value)}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                            <InputError message={getFieldError('descripcion_trabajo')} className="mt-2" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="mb-1.5 block text-sm font-medium text-text-main">Observaciones internas</span>
                            <textarea
                                value={form.observaciones}
                                rows={3}
                                onChange={e => updateField('observaciones', e.target.value)}
                                className="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-(--ciete-red) focus:ring-(--ciete-red)"
                            />
                        </label>
                    </div>

                    <div className="flex items-center justify-between border-t border-border pt-4">
                        <button type="button" onClick={() => router.visit(route('trabajos.index'))} className="text-sm text-text-muted hover:text-text-main">
                            {t('common.actions.cancel')}
                        </button>
                        <button type="submit" disabled={loading} className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark) disabled:opacity-60">
                            {loading ? t('common.actions.saving') : t('common.actions.save')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
