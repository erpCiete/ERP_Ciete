import InputError from '@/Components/InputError';
import { useEstaciones } from '@/hooks/useEstaciones'; // Asegúrate de que 'hooks' esté en minúscula si así se llama la carpeta
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { isBlank, exceedsMaxLength } from '@/validation/formRules';

function validarForm(form, listaEstaciones, t) {
    const errs = {};
    // Mensaje de respaldo si la traducción falla
    const reqMsg = t('common.validation.required') || 'Este campo es obligatorio';

    if (isBlank(form.numero_trabajo)) errs.numero_trabajo = reqMsg;
    if (isBlank(form.id_estacion_servicio)) errs.id_estacion_servicio = reqMsg;
    if (isBlank(form.descripcion_trabajo)) errs.descripcion_trabajo = reqMsg;

    const estacion = listaEstaciones.find(e => String(e.id ?? e.id_estacion_servicio) === String(form.id_estacion_servicio));
    const contextoActivo = estacion?.id_contexto;

    if (contextoActivo === 1) { // MOEVE
        if (isBlank(form.id_contrato)) errs.id_contrato = reqMsg;
    }
    if (contextoActivo === 2) { // REPSOL
        if (isBlank(form.id_tipo_documento)) errs.id_tipo_documento = reqMsg;
        if (isBlank(form.id_tipo_trabajo)) errs.id_tipo_trabajo = reqMsg;
    }
    return errs;
}

const EMPTY_FORM = {
    numero_trabajo:       '',
    descripcion_trabajo:  '',
    id_estacion_servicio: '',
    fecha_encargo:        new Date().toISOString().split('T')[0],
    fecha_terminado:      '',
    estado:               'borrador',
    observaciones:        '',
    id_contrato:          '',
    categoria:            '',
    id_tipo_documento:    '',
    id_tipo_trabajo:      '',
    numero_aviso:         '',
};

function normalizeTrabajo(trabajo) {
    if (!trabajo) return EMPTY_FORM;
    // Extraemos los datos dependiendo de si viene envuelto en 'data' por el Resource o no
    const t = trabajo.data || trabajo;
    
    return {
        numero_trabajo:       t.numero_trabajo       ?? '',
        descripcion_trabajo:  t.descripcion_trabajo  ?? '',
        id_estacion_servicio: t.id_estacion_servicio ? String(t.id_estacion_servicio) : '',
        fecha_encargo:        t.fecha_encargo        ?? new Date().toISOString().split('T')[0],
        fecha_terminado:      t.fecha_terminado      ?? '',
        estado:               t.estado               ?? 'borrador',
        observaciones:        t.observaciones        ?? '',
        id_contrato:          t.id_contrato          ? String(t.id_contrato) : '',
        categoria:            t.categoria            ?? '',
        id_tipo_documento:    t.id_tipo_documento    ? String(t.id_tipo_documento) : '',
        id_tipo_trabajo:      t.id_tipo_trabajo      ? String(t.id_tipo_trabajo) : '',
        numero_aviso:         t.numero_aviso         ?? '',
    };
}


export default function TrabajosForm({
    trabajo        = null,
    contextoIds    = [],
    contratos      = [],
    tiposDocumento = [],
    tiposTrabajo   = []
}) {
    const { t } = useI18n();
    
    // Extraemos las estaciones de forma segura
    const { estaciones } = useEstaciones();
    const listaEstaciones = estaciones || []; 

    const isEditing = trabajo !== null;
    const isMoeve   = contextoIds.includes(1);
    const isRepsol  = contextoIds.includes(2);

    const [form,            setForm]            = useState(() => normalizeTrabajo(trabajo));
    const [serverErrors,    setServerErrors]    = useState({});
    const [loading,         setLoading]         = useState(false);
    const [touched,         setTouched]         = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError,     setSubmitError]     = useState('');

    const localErrors = useMemo(() => 
        validarForm(form, listaEstaciones, t), 
        [form, listaEstaciones, t]
    );
    const pageTitle = isEditing ? t('trabajos.edit') : t('trabajos.create');

    const updateField = (field, value) => {
        setTouched(prev => ({ ...prev, [field]: true }));
        setServerErrors(prev => { const n = { ...prev }; delete n[field]; return n; });
        setForm(prev => ({ ...prev, [field]: value }));
    };

    const getError = (field) => {
        if (!submitAttempted && !touched[field] && !serverErrors[field]) return '';
        return localErrors[field] ?? serverErrors[field]?.[0] ?? '';
    };

    const inputClass = (field) =>
        `w-full rounded-md border px-3 py-2 text-sm bg-surface text-text-main focus:outline-none focus:ring-1 ${
            getError(field) ? 'border-red-500 focus:border-red-500 focus:ring-red-300' : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'
        }`;

        const handleSubmit = (e) => {
            e.preventDefault();
            setSubmitAttempted(true);
            
            // Si hay errores locales de React, no enviamos nada
            if (Object.keys(localErrors).length > 0) return;
        
            setLoading(true);
        
            const payload = {
                numero_trabajo:       form.numero_trabajo,
                descripcion_trabajo:  form.descripcion_trabajo, // Laravel busca este nombre
                id_estacion_servicio: Number(form.id_estacion_servicio),
                fecha_encargo:        form.fecha_encargo,
                fecha_terminado:      form.fecha_terminado || null,
                estado:               form.estado,
                observaciones:        form.observaciones || null,
                // Campos específicos convertidos a número si tienen valor
                id_contrato:          form.id_contrato ? Number(form.id_contrato) : null,
                id_tipo_documento:    form.id_tipo_documento ? Number(form.id_tipo_documento) : null,
                id_tipo_trabajo:      form.id_tipo_trabajo ? Number(form.id_tipo_trabajo) : null,
                numero_aviso:         form.numero_aviso || null,
                categoria:            form.categoria || null,
            };
        
            if (isEditing) {
                const idTrabajo = trabajo.data ? trabajo.data.id_trabajo : trabajo.id_trabajo;
                router.put(route('trabajos.update', idTrabajo), payload, {
                    onError: (errs) => { setServerErrors(errs); setLoading(false); }
                });
            } else {
                router.post(route('trabajos.store'), payload, {
                    onError: (errs) => { setServerErrors(errs); setLoading(false); }
                });
            }
        };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}>
            <Head title={pageTitle} />

            <div className="mx-auto max-w-4xl space-y-6">
                <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">{t('nav.groups.operations')}</p>
                    <h1 className="mt-2 text-2xl font-semibold text-text-main">{pageTitle}</h1>
                    <div className="mt-2 flex gap-2">
                        {isMoeve  && <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-blue-700">MOEVE</span>}
                        {isRepsol && <span className="rounded-full bg-red-50  px-2 py-0.5 text-[10px] font-semibold uppercase text-red-700">REPSOL</span>}
                    </div>
                </section>

                <form onSubmit={handleSubmit} noValidate className="space-y-6">
                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                        <legend className="text-sm font-semibold text-text-main px-1">Datos principales</legend>
                        <div className="grid gap-5 md:grid-cols-2">
                            <div>
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Nº Trabajo <span className="text-red-500">*</span></label>
                                <input type="text" value={form.numero_trabajo} onChange={e => updateField('numero_trabajo', e.target.value)} className={inputClass('numero_trabajo')} />
                                <InputError message={getError('numero_trabajo')} className="mt-1.5" />
                            </div>

                            <div>
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Estado <span className="text-red-500">*</span></label>
                                <select value={form.estado} onChange={e => updateField('estado', e.target.value)} className={inputClass('estado')}>
                                    <option value="borrador">Borrador</option>
                                    <option value="en_curso">En Curso</option>
                                    <option value="terminado">Terminado</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <InputError message={getError('estado')} className="mt-1.5" />
                            </div>

                            <div className="md:col-span-2">
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Estación de servicio <span className="text-red-500">*</span></label>
                                <select value={form.id_estacion_servicio} onChange={e => updateField('id_estacion_servicio', e.target.value)} className={inputClass('id_estacion_servicio')}>
                                    <option value="">Selecciona una estación...</option>
                                    {listaEstaciones.map(est => (
                                        <option key={est.id ?? est.id_estacion_servicio} value={est.id ?? est.id_estacion_servicio}>
                                            {est.nombre} {est.codigo_estacion ? `(${est.codigo_estacion})` : ''}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={getError('id_estacion_servicio')} className="mt-1.5" />
                            </div>

                            <div className="md:col-span-2">
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Descripción <span className="text-red-500">*</span></label>
                                <textarea value={form.descripcion_trabajo} rows={3} onChange={e => updateField('descripcion_trabajo', e.target.value)} className={inputClass('descripcion_trabajo')} />
                                <InputError message={getError('descripcion_trabajo')} className="mt-1.5" />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                        <legend className="text-sm font-semibold text-text-main px-1">Fechas</legend>
                        <div className="grid gap-5 md:grid-cols-2">
                            <div>
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Fecha encargo <span className="text-red-500">*</span></label>
                                <input type="date" value={form.fecha_encargo} onChange={e => updateField('fecha_encargo', e.target.value)} className={inputClass('fecha_encargo')} />
                                <InputError message={getError('fecha_encargo')} className="mt-1.5" />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-sm font-medium text-text-main">Fecha terminación</label>
                                <input type="date" value={form.fecha_terminado} onChange={e => updateField('fecha_terminado', e.target.value)} className={inputClass('fecha_terminado')} />
                            </div>
                        </div>
                    </fieldset>

                    {isMoeve && (
                        <fieldset className="rounded-2xl border border-blue-200 bg-blue-50/30 p-6 shadow-sm space-y-5">
                            <legend className="text-sm font-semibold text-text-main px-1">Datos MOEVE</legend>
                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">Contrato <span className="text-red-500">*</span></label>
                                    {contratos.length > 0 ? (
                                        <select value={form.id_contrato} onChange={e => updateField('id_contrato', e.target.value)} className={inputClass('id_contrato')}>
                                            <option value="">Selecciona...</option>
                                            {contratos.map(c => <option key={c.id} value={c.id}>{c.nombre ?? c.codigo}</option>)}
                                        </select>
                                    ) : (
                                        <input type="text" value={form.id_contrato} onChange={e => updateField('id_contrato', e.target.value)} className={inputClass('id_contrato')} />
                                    )}
                                    <InputError message={getError('id_contrato')} className="mt-1.5" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">Categoría</label>
                                    <input type="text" value={form.categoria} onChange={e => updateField('categoria', e.target.value)} className={inputClass('categoria')} />
                                </div>
                            </div>
                        </fieldset>
                    )}

                    {isRepsol && (
                        <fieldset className="rounded-2xl border border-red-200 bg-red-50/20 p-6 shadow-sm space-y-5">
                            <legend className="text-sm font-semibold text-text-main px-1">Datos REPSOL</legend>
                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">Tipo de documento <span className="text-red-500">*</span></label>
                                    {tiposDocumento.length > 0 ? (
                                        <select value={form.id_tipo_documento} onChange={e => updateField('id_tipo_documento', e.target.value)} className={inputClass('id_tipo_documento')}>
                                            <option value="">Selecciona...</option>
                                            {tiposDocumento.map(td => <option key={td.id} value={td.id}>{td.nombre}</option>)}
                                        </select>
                                    ) : (
                                        <input type="text" value={form.id_tipo_documento} onChange={e => updateField('id_tipo_documento', e.target.value)} className={inputClass('id_tipo_documento')} />
                                    )}
                                    <InputError message={getError('id_tipo_documento')} className="mt-1.5" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">Tipo de trabajo <span className="text-red-500">*</span></label>
                                    {tiposTrabajo.length > 0 ? (
                                        <select value={form.id_tipo_trabajo} onChange={e => updateField('id_tipo_trabajo', e.target.value)} className={inputClass('id_tipo_trabajo')}>
                                            <option value="">Selecciona...</option>
                                            {tiposTrabajo.map(tt => <option key={tt.id} value={tt.id}>{tt.nombre}</option>)}
                                        </select>
                                    ) : (
                                        <input type="text" value={form.id_tipo_trabajo} onChange={e => updateField('id_tipo_trabajo', e.target.value)} className={inputClass('id_tipo_trabajo')} />
                                    )}
                                    <InputError message={getError('id_tipo_trabajo')} className="mt-1.5" />
                                </div>
                                <div>
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">Nº de aviso</label>
                                    <input type="text" value={form.numero_aviso} onChange={e => updateField('numero_aviso', e.target.value)} className={inputClass('numero_aviso')} />
                                </div>
                            </div>
                        </fieldset>
                    )}

                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                        <legend className="text-sm font-semibold text-text-main px-1">Observaciones</legend>
                        <div className="mt-3">
                            <textarea value={form.observaciones} rows={3} onChange={e => updateField('observaciones', e.target.value)} className={inputClass('observaciones')} />
                        </div>
                    </fieldset>

                    {submitError && (
                        <div className="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                            <p className="text-sm text-red-700 font-medium">{submitError}</p>
                        </div>
                    )}

                    <div className="flex items-center justify-between rounded-2xl border border-border bg-surface px-6 py-4 shadow-sm">
                        <button type="button" onClick={() => router.visit(route('trabajos.index'))} className="text-sm font-medium text-text-muted hover:text-text-main">
                            {t('common.actions.cancel')}
                        </button>
                        <button type="submit" disabled={loading} className="inline-flex items-center gap-2 rounded-md bg-(--ciete-red) px-5 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60">
                            {loading ? 'Guardando...' : (isEditing ? 'Guardar Cambios' : 'Crear Obra')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}