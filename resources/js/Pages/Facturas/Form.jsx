// resources/js/Pages/Facturas/Form.jsx
import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import axios from 'axios';

// ─── Estado inicial del formulario ────────────────────────────────────────────
const EMPTY_FORM = {
    numero_factura:  '',
    id_trabajo:      '',
    fecha_emision:   new Date().toISOString().split('T')[0],
    estado:          'borrador',
    // Importes
    total:           '',
    base_imponible:  '',
    iva:             '',
    // Campos MOEVE
    factura_ccp:     '',
    sociedad:        '',
    // Campos REPSOL
    orden_factura:   '',
    autofactura:     false,
};

// ─── Normalizar factura existente para rellenar el form ───────────────────────
function normalizeFactura(factura) {
    if (!factura) return EMPTY_FORM;
    return {
        numero_factura:  factura.numero_factura  ?? '',
        id_trabajo:      factura.id_trabajo      ? String(factura.id_trabajo) : '',
        fecha_emision:   factura.fecha_emision   ?? new Date().toISOString().split('T')[0],
        estado:          factura.estado          ?? 'borrador',
        total:           factura.total           ?? '',
        base_imponible:  factura.base_imponible  ?? '',
        iva:             factura.iva             ?? '',
        factura_ccp:     factura.factura_ccp     ?? '',
        sociedad:        factura.sociedad        ?? '',
        orden_factura:   factura.orden_factura   != null ? String(factura.orden_factura) : '',
        autofactura:     factura.autofactura     ?? false,
    };
}

// ─── Validaciones en cliente ──────────────────────────────────────────────────
function validarForm(form, isMoeve, isRepsol, t) {
    const errs = {};

    if (!form.numero_factura?.trim())
        errs.numero_factura = t('common.validation.required');

    if (!form.id_trabajo)
        errs.id_trabajo = t('common.validation.required');

    if (!form.fecha_emision)
        errs.fecha_emision = t('common.validation.required');

    if (!form.estado)
        errs.estado = t('common.validation.required');

    if (isMoeve && !form.factura_ccp?.trim())
        errs.factura_ccp = t('common.validation.required');

    if (isRepsol && !form.orden_factura)
        errs.orden_factura = t('common.validation.required');

    return errs;
}

// ─── Componente principal ─────────────────────────────────────────────────────
// Props desde FacturaController@create / @edit via Inertia:
//   factura     → null (crear) | objeto FacturaResource (editar)
//   trabajos    → array de trabajos disponibles para el select
//   contextoIds → [1] MOEVE · [2] REPSOL · [1,2] ambos
//   ordenesUsadas → { [id_trabajo]: [1, 2] } — órdenes ya existentes por trabajo (REPSOL)
export default function FacturasForm({
    factura      = null,
    trabajos     = [],
    contextoIds  = [],
    ordenesUsadas = {},
}) {
    const { t } = useI18n();

    const isEditing = factura !== null;
    const isMoeve   = contextoIds.includes(1);
    const isRepsol  = contextoIds.includes(2);

    const [form,            setForm]            = useState(() => normalizeFactura(factura));
    const [serverErrors,    setServerErrors]    = useState({});
    const [loading,         setLoading]         = useState(false);
    const [touched,         setTouched]         = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError,     setSubmitError]     = useState('');

    const localErrors = useMemo(
        () => validarForm(form, isMoeve, isRepsol, t),
        [form, isMoeve, isRepsol, t]
    );

    const pageTitle = isEditing ? t('facturas.edit') : t('facturas.create');

    // ── Aviso REPSOL: orden ya ocupada ────────────────────────────────────────
    // ordenesUsadas[id_trabajo] = [1] | [2] | [1,2]
    // En edición, la orden actual de la factura no cuenta como "ocupada"
    const ordenesDelTrabajo = form.id_trabajo
        ? (ordenesUsadas[form.id_trabajo] ?? []).filter(o =>
            // Si estamos editando, excluir la propia orden actual
            isEditing ? String(o) !== String(factura?.orden_factura) : true
          )
        : [];
    const ordenSeleccionada = Number(form.orden_factura);
    const ordenYaOcupada    = isRepsol
        && form.orden_factura !== ''
        && ordenesDelTrabajo.includes(ordenSeleccionada);
    const ambasOrdenesOcupadas = isRepsol
        && form.id_trabajo
        && ordenesDelTrabajo.length >= 2;

    // ── Helpers ───────────────────────────────────────────────────────────────
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
        `w-full rounded-md border px-3 py-2 text-sm bg-surface text-text-main
         focus:outline-none focus:ring-1
         ${getError(field)
            ? 'border-red-500 focus:border-red-500 focus:ring-red-300'
            : 'border-border focus:border-(--ciete-red) focus:ring-(--ciete-red)/30'}`;

    // ── Submit ────────────────────────────────────────────────────────────────
    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitAttempted(true);
        setSubmitError('');

        const allTouched = Object.keys(EMPTY_FORM).reduce((a, k) => ({ ...a, [k]: true }), {});
        setTouched(allTouched);

        if (Object.keys(localErrors).length > 0) {
            setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            const primerError = Object.keys(localErrors)[0];
            document.getElementById(primerError)?.focus();
            return;
        }

        setLoading(true);

        try {
            const payload = {
                numero_factura: form.numero_factura,
                id_trabajo:     Number(form.id_trabajo),
                fecha_emision:  form.fecha_emision,
                estado:         form.estado,
                total:          form.total !== '' ? Number(form.total) : null,
                base_imponible: form.base_imponible !== '' ? Number(form.base_imponible) : null,
                iva:            form.iva !== '' ? Number(form.iva) : null,
            };

            if (isMoeve) {
                payload.factura_ccp = form.factura_ccp;
                payload.sociedad    = form.sociedad;
            }

            if (isRepsol) {
                payload.orden_factura = Number(form.orden_factura);
                payload.autofactura   = Boolean(form.autofactura);
            }

            if (isEditing) {
                await axios.put(`/api/v1/facturas/${factura.id_factura}`, payload);
            } else {
                await axios.post('/api/v1/facturas', payload);
            }

            router.visit(route('facturas.index'));

        } catch (err) {
            if (err.response?.status === 422) {
                setServerErrors(err.response.data?.errors ?? {});
                setSubmitError(t('common.validation.reviewForm') ?? 'Revisa los errores del formulario.');
            } else {
                setSubmitError('Error al guardar la factura. Inténtalo de nuevo.');
            }
        } finally {
            setLoading(false);
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{pageTitle}</h2>}
        >
            <Head title={pageTitle} />

            <div className="mx-auto max-w-5xl space-y-6">

                {/* ── Cabecera ─────────────────────────────────────────────── */}
                <section className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">
                        {t('nav.groups.operations')}
                    </p>
                    <h1 className="mt-2 text-2xl font-semibold text-text-main">{pageTitle}</h1>
                    <div className="mt-2 flex gap-2">
                        {isMoeve  && <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-blue-700">MOEVE</span>}
                        {isRepsol && <span className="rounded-full bg-red-50  px-2 py-0.5 text-[10px] font-semibold uppercase text-red-700">REPSOL</span>}
                    </div>
                </section>

                <form onSubmit={handleSubmit} noValidate className="space-y-6">

                    {/* ── SECCIÓN 1: Datos principales ─────────────────────── */}
                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                        <legend className="text-sm font-semibold text-text-main px-1">
                            Datos principales
                        </legend>
                        <div className="grid gap-5 md:grid-cols-2">

                            {/* Trabajo */}
                            <div className="md:col-span-2">
                                <label htmlFor="id_trabajo" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('facturas.fields.trabajo')} <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="id_trabajo"
                                    value={form.id_trabajo}
                                    onChange={e => updateField('id_trabajo', e.target.value)}
                                    className={inputClass('id_trabajo')}
                                >
                                    <option value="">Selecciona un trabajo...</option>
                                    {trabajos.map(tr => (
                                        <option key={tr.id_trabajo} value={tr.id_trabajo}>
                                            {String(tr.numero_trabajo).padStart(4, '0')} — {tr.descripcion_trabajo}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={getError('id_trabajo')} className="mt-1.5" />
                            </div>

                            {/* Nº factura */}
                            <div>
                                <label htmlFor="numero_factura" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('facturas.fields.numero')} <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="numero_factura"
                                    type="text"
                                    value={form.numero_factura}
                                    onChange={e => updateField('numero_factura', e.target.value)}
                                    className={inputClass('numero_factura')}
                                    placeholder="Ej: FAC-M-001"
                                />
                                <InputError message={getError('numero_factura')} className="mt-1.5" />
                            </div>

                            {/* Estado */}
                            <div>
                                <label htmlFor="estado" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('facturas.fields.estado')} <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="estado"
                                    value={form.estado}
                                    onChange={e => updateField('estado', e.target.value)}
                                    className={inputClass('estado')}
                                >
                                    <option value="borrador">{t('facturas.status.borrador')}</option>
                                    <option value="emitida">{t('facturas.status.emitida')}</option>
                                    <option value="cobrada">{t('facturas.status.cobrada')}</option>
                                    <option value="cancelada">{t('facturas.status.cancelada')}</option>
                                </select>
                                <InputError message={getError('estado')} className="mt-1.5" />
                            </div>

                            {/* Fecha emisión */}
                            <div>
                                <label htmlFor="fecha_emision" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('facturas.fields.fechaEmision')} <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="fecha_emision"
                                    type="date"
                                    value={form.fecha_emision}
                                    onChange={e => updateField('fecha_emision', e.target.value)}
                                    className={inputClass('fecha_emision')}
                                />
                                <InputError message={getError('fecha_emision')} className="mt-1.5" />
                            </div>
                        </div>
                    </fieldset>

                    {/* ── SECCIÓN 2: Importes ───────────────────────────────── */}
                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-5">
                        <legend className="text-sm font-semibold text-text-main px-1">
                            Importes
                        </legend>
                        <div className="grid gap-5 md:grid-cols-3">

                            {/* Base imponible */}
                            <div>
                                <label htmlFor="base_imponible" className="mb-1.5 block text-sm font-medium text-text-main">
                                    Base imponible
                                    <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                </label>
                                <div className="relative">
                                    <input
                                        id="base_imponible"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={form.base_imponible}
                                        onChange={e => updateField('base_imponible', e.target.value)}
                                        className={`${inputClass('base_imponible')} pr-7`}
                                        placeholder="0.00"
                                    />
                                    <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-text-hint">€</span>
                                </div>
                            </div>

                            {/* IVA */}
                            <div>
                                <label htmlFor="iva" className="mb-1.5 block text-sm font-medium text-text-main">
                                    IVA
                                    <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                </label>
                                <div className="relative">
                                    <input
                                        id="iva"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={form.iva}
                                        onChange={e => updateField('iva', e.target.value)}
                                        className={`${inputClass('iva')} pr-7`}
                                        placeholder="0.00"
                                    />
                                    <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-text-hint">€</span>
                                </div>
                            </div>

                            {/* Total factura */}
                            <div>
                                <label htmlFor="total" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('facturas.fields.total')}
                                    <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                </label>
                                <div className="relative">
                                    <input
                                        id="total"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={form.total}
                                        onChange={e => updateField('total', e.target.value)}
                                        className={`${inputClass('total')} pr-7`}
                                        placeholder="0.00"
                                    />
                                    <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-text-hint">€</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {/* ── SECCIÓN 3: Campos MOEVE ───────────────────────────── */}
                    {isMoeve && (
                        <fieldset className="rounded-2xl border border-blue-200 bg-blue-50/20 p-6 shadow-sm space-y-5">
                            <legend className="flex items-center gap-2 text-sm font-semibold text-text-main px-1">
                                Datos MOEVE
                                <span className="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold text-blue-700">M</span>
                            </legend>
                            <div className="grid gap-5 md:grid-cols-2">

                                {/* Nº Factura CCP */}
                                <div>
                                    <label htmlFor="factura_ccp" className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('facturas.fields.facturaCcp')} <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="factura_ccp"
                                        type="text"
                                        value={form.factura_ccp}
                                        onChange={e => updateField('factura_ccp', e.target.value)}
                                        className={inputClass('factura_ccp')}
                                        placeholder="Ej: CCP-00001"
                                    />
                                    <InputError message={getError('factura_ccp')} className="mt-1.5" />
                                </div>

                                {/* Sociedad */}
                                <div>
                                    <label htmlFor="sociedad" className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('facturas.fields.sociedad')}
                                        <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                    </label>
                                    <input
                                        id="sociedad"
                                        type="text"
                                        value={form.sociedad}
                                        onChange={e => updateField('sociedad', e.target.value)}
                                        className={inputClass('sociedad')}
                                        placeholder="Ej: MOEVE ES"
                                    />
                                </div>
                            </div>
                        </fieldset>
                    )}

                    {/* ── SECCIÓN 4: Campos REPSOL ──────────────────────────── */}
                    {isRepsol && (
                        <fieldset className="rounded-2xl border border-red-200 bg-red-50/20 p-6 shadow-sm space-y-5">
                            <legend className="flex items-center gap-2 text-sm font-semibold text-text-main px-1">
                                Datos REPSOL
                                <span className="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">R</span>
                            </legend>

                            {/* Aviso: ambas órdenes ya ocupadas para este trabajo */}
                            {ambasOrdenesOcupadas && !isEditing && (
                                <div role="alert" className="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                                    <span className="mt-0.5 text-red-500" aria-hidden>⚠</span>
                                    <div>
                                        <p className="text-sm font-semibold text-red-700">
                                            Límite alcanzado para este trabajo
                                        </p>
                                        <p className="mt-0.5 text-xs text-red-600">
                                            Este trabajo ya tiene las dos facturas REPSOL permitidas (orden 1 y orden 2). No es posible crear una tercera.
                                        </p>
                                    </div>
                                </div>
                            )}

                            <div className="grid gap-5 md:grid-cols-2">

                                {/* Orden factura */}
                                <div>
                                    <label htmlFor="orden_factura" className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('facturas.fields.ordenFactura')} <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="orden_factura"
                                        value={form.orden_factura}
                                        onChange={e => updateField('orden_factura', e.target.value)}
                                        className={inputClass('orden_factura')}
                                        disabled={ambasOrdenesOcupadas && !isEditing}
                                    >
                                        <option value="">Selecciona orden...</option>
                                        <option
                                            value="1"
                                            disabled={ordenesDelTrabajo.includes(1) && String(factura?.orden_factura) !== '1'}
                                        >
                                            Orden 1{ordenesDelTrabajo.includes(1) && String(factura?.orden_factura) !== '1' ? ' — ocupada' : ''}
                                        </option>
                                        <option
                                            value="2"
                                            disabled={ordenesDelTrabajo.includes(2) && String(factura?.orden_factura) !== '2'}
                                        >
                                            Orden 2{ordenesDelTrabajo.includes(2) && String(factura?.orden_factura) !== '2' ? ' — ocupada' : ''}
                                        </option>
                                    </select>
                                    <InputError message={getError('orden_factura')} className="mt-1.5" />

                                    {/* Aviso inline: orden seleccionada ya existe */}
                                    {ordenYaOcupada && (
                                        <p className="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-amber-600">
                                            <span aria-hidden>⚠</span>
                                            Ya existe una factura con esta orden para el trabajo seleccionado.
                                        </p>
                                    )}

                                    {/* Indicador visual: qué órdenes están disponibles */}
                                    {form.id_trabajo && (
                                        <div className="mt-2 flex items-center gap-2">
                                            <span className="text-[10px] text-text-hint uppercase tracking-wide">Órdenes:</span>
                                            {[1, 2].map(n => {
                                                const ocupada = ordenesDelTrabajo.includes(n)
                                                    && !(isEditing && String(factura?.orden_factura) === String(n));
                                                return (
                                                    <span
                                                        key={n}
                                                        className={`inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold
                                                            ${ocupada
                                                                ? 'bg-red-100 text-red-500 line-through'
                                                                : 'bg-green-100 text-green-700'
                                                            }`}
                                                        title={ocupada ? `Orden ${n}: ocupada` : `Orden ${n}: disponible`}
                                                    >
                                                        {n}
                                                    </span>
                                                );
                                            })}
                                            <span className="text-[10px] text-text-hint">
                                                {ordenesDelTrabajo.length === 0 && 'Ambas disponibles'}
                                                {ordenesDelTrabajo.length === 1 && '1 disponible'}
                                                {ordenesDelTrabajo.length >= 2 && 'Sin órdenes disponibles'}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                {/* Autofactura */}
                                <div className="flex flex-col justify-center">
                                    <label className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('facturas.fields.autofactura')}
                                    </label>
                                    <label className="inline-flex cursor-pointer items-center gap-3">
                                        <input
                                            id="autofactura"
                                            type="checkbox"
                                            checked={form.autofactura}
                                            onChange={e => updateField('autofactura', e.target.checked)}
                                            className="h-4 w-4 rounded border-border accent-(--ciete-red)"
                                        />
                                        <span className="text-sm text-text-muted">
                                            Esta factura es una autofactura
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    )}

                    {/* ── Banner de error global ────────────────────────────── */}
                    {submitError && (
                        <div role="alert" className="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                            <span className="text-red-500 mt-0.5" aria-hidden>⚠</span>
                            <p className="text-sm font-medium text-red-700">{submitError}</p>
                        </div>
                    )}

                    {/* ── Footer ───────────────────────────────────────────── */}
                    <div className="flex items-center justify-between rounded-2xl border border-border bg-surface px-6 py-4 shadow-sm">
                        <button
                            type="button"
                            onClick={() => router.visit(route('facturas.index'))}
                            className="text-sm font-medium text-text-muted transition hover:text-text-main"
                        >
                            {t('common.actions.cancel')}
                        </button>
                        <button
                            type="submit"
                            disabled={loading || (isRepsol && ambasOrdenesOcupadas && !isEditing)}
                            className="inline-flex items-center gap-2 rounded-md bg-(--ciete-red) px-5 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60"
                        >
                            {loading && (
                                <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                            )}
                            {loading
                                ? (t('common.actions.saving') ?? 'Guardando...')
                                : isEditing ? t('common.actions.save') : t('facturas.create')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
