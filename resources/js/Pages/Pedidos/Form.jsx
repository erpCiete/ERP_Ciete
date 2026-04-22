// resources/js/Pages/Pedidos/Form.jsx
import InputError from '@/Components/InputError';
import ItemsTable, { EMPTY_ITEM } from '@/Components/ui/ItemsTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import axios from 'axios';

// ─── Estado inicial del formulario ────────────────────────────────────────────
const EMPTY_FORM = {
    id_trabajo:           '',
    numero_pedido:        '',
    fecha_solicitud:      new Date().toISOString().split('T')[0],
    fecha_recepcion:      '',
    estado:               'borrador',
    importe_solicitado:   '',
    unidades_solicitadas: '',
};

function normalizePedido(pedido) {
    if (!pedido) return EMPTY_FORM;
    return {
        id_trabajo:           pedido.id_trabajo            ? String(pedido.id_trabajo)  : '',
        numero_pedido:        pedido.numero_pedido          ?? '',
        fecha_solicitud:      pedido.fecha_solicitud        ?? new Date().toISOString().split('T')[0],
        fecha_recepcion:      pedido.fecha_recepcion        ?? '',
        estado:               pedido.estado                 ?? 'borrador',
        importe_solicitado:   pedido.importe_solicitado     ?? '',
        unidades_solicitadas: pedido.unidades_solicitadas   ?? '',
    };
}

function normalizeItems(items) {
    if (!items || items.length === 0) return [{ ...EMPTY_ITEM }];
    return items.map(item => ({
        codigo_servicio:      item.codigo_servicio      ?? '',
        descripcion_servicio: item.descripcion_servicio ?? '',
        cantidad:             item.cantidad             ?? 1,
        precio_unitario:      item.precio_unitario      ?? 0,
        total_linea:          item.total_linea          ?? 0,
    }));
}

function validarForm(form, items, isRepsol, t) {
    const errs = {};

    if (!form.numero_pedido?.trim())
        errs.numero_pedido = t('common.validation.required');
    if (!form.id_trabajo)
        errs.id_trabajo = t('common.validation.required');
    if (!form.fecha_solicitud)
        errs.fecha_solicitud = t('common.validation.required');
    if (!form.estado)
        errs.estado = t('common.validation.required');

    if (isRepsol) {
        if (form.importe_solicitado === '' || form.importe_solicitado === null)
            errs.importe_solicitado = t('common.validation.required');
        if (form.unidades_solicitadas === '' || form.unidades_solicitadas === null)
            errs.unidades_solicitadas = t('common.validation.required');
    }

    if (items.length === 0) {
        errs.items = 'Añade al menos una línea al pedido.';
    } else {
        items.forEach((item, i) => {
            if (Number(item.cantidad) <= 0)
                errs[`items.${i}.cantidad`] = 'La cantidad debe ser mayor que 0.';
            if (Number(item.precio_unitario) < 0)
                errs[`items.${i}.precio_unitario`] = 'El precio no puede ser negativo.';
        });
    }

    return errs;
}

// ─── Componente principal ─────────────────────────────────────────────────────
// Props desde el controller via Inertia:
//   pedido      → null (crear) | objeto PedidoResource (editar)
//   trabajos    → array del controller (puede llegar vacío si el web controller no lo pasa)
//   contextoIds → [1] MOEVE · [2] REPSOL
export default function PedidosForm({
    pedido      = null,
    trabajos    = [],   // puede llegar [] si el controller web no existe aún
    contextoIds = [],
}) {
    const { t } = useI18n();

    const isEditing = pedido !== null;
    const isRepsol  = contextoIds.includes(2);
    const isMoeve   = contextoIds.includes(1);

    const [form,            setForm]            = useState(() => normalizePedido(pedido));
    const [items,           setItems]           = useState(() => normalizeItems(pedido?.items));
    const [serverErrors,    setServerErrors]    = useState({});
    const [loading,         setLoading]         = useState(false);
    const [touched,         setTouched]         = useState({});
    const [submitAttempted, setSubmitAttempted] = useState(false);
    const [submitError,     setSubmitError]     = useState('');

    // ── Lista de trabajos: usa el prop si viene, si no carga desde la API ────
    // Esto soluciona que el controller web no pase el catálogo de trabajos
    const [listaTrab,       setListaTrab]       = useState(trabajos);
    const [cargandoTrab,    setCargandoTrab]    = useState(false);

    useEffect(() => {
        // Solo cargar desde API si el prop llegó vacío
        if (listaTrab.length > 0) return;

        setCargandoTrab(true);
        axios.get('/api/v1/trabajos', { params: { per_page: 200 } })
            .then(res => {
                // La API devuelve { data: { data: [...] } } o { data: [...] }
                const data = res.data?.data ?? res.data ?? [];
                setListaTrab(Array.isArray(data) ? data : []);
            })
            .catch(() => setListaTrab([]))
            .finally(() => setCargandoTrab(false));
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    const localErrors = useMemo(
        () => validarForm(form, items, isRepsol, t),
        [form, items, isRepsol, t]
    );

    const pageTitle    = isEditing ? t('pedidos.edit') : t('pedidos.create');
    const totalPedido  = items.reduce((sum, item) => sum + (Number(item.total_linea) || 0), 0);

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
            const primerError = Object.keys(localErrors)[0].replace(/\.\d+\..+/, '');
            document.getElementById(primerError)?.focus();
            return;
        }

        setLoading(true);
        try {
            const payload = {
                ...form,
                id_trabajo: Number(form.id_trabajo),
                items: items.map(item => ({
                    codigo_servicio:      item.codigo_servicio,
                    descripcion_servicio: item.descripcion_servicio,
                    cantidad:             Number(item.cantidad),
                    precio_unitario:      Number(item.precio_unitario),
                    total_linea:          Number(item.total_linea),
                })),
            };

            if (isRepsol) {
                payload.importe_solicitado   = Number(form.importe_solicitado)   || 0;
                payload.unidades_solicitadas = Number(form.unidades_solicitadas) || 0;
            }

            if (isEditing) {
                await axios.put(`/api/v1/pedidos/${pedido.id_pedido}`, payload);
            } else {
                await axios.post('/api/v1/pedidos', payload);
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

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{pageTitle}</h2>}
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

                            {/* Trabajo — se carga desde API si el prop llega vacío */}
                            <div className="md:col-span-2">
                                <label htmlFor="id_trabajo" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('pedidos.fields.trabajo')} <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="id_trabajo"
                                    value={form.id_trabajo}
                                    onChange={e => updateField('id_trabajo', e.target.value)}
                                    disabled={cargandoTrab}
                                    className={inputClass('id_trabajo')}
                                >
                                    <option value="">
                                        {cargandoTrab ? 'Cargando trabajos...' : 'Selecciona un trabajo...'}
                                    </option>
                                    {listaTrab.map(tr => {
                                        // El recurso puede venir con id_trabajo o con id según el Resource
                                        const id  = tr.id_trabajo ?? tr.id;
                                        const num = String(tr.numero_trabajo ?? '').padStart(4, '0');
                                        const desc = tr.descripcion_trabajo ?? tr.descripcion ?? '';
                                        return (
                                            <option key={id} value={id}>
                                                {num}{desc ? ` — ${desc}` : ''}
                                            </option>
                                        );
                                    })}
                                </select>
                                {listaTrab.length === 0 && !cargandoTrab && (
                                    <p className="mt-1 text-xs text-amber-600">
                                        No hay trabajos disponibles. Crea un trabajo primero.
                                    </p>
                                )}
                                <InputError message={getError('id_trabajo')} className="mt-1.5" />
                            </div>

                            {/* Nº pedido */}
                            <div>
                                <label htmlFor="numero_pedido" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('pedidos.fields.numero')} <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="numero_pedido"
                                    type="text"
                                    value={form.numero_pedido}
                                    onChange={e => updateField('numero_pedido', e.target.value)}
                                    className={inputClass('numero_pedido')}
                                    placeholder="Ej: PED-M-001"
                                />
                                <InputError message={getError('numero_pedido')} className="mt-1.5" />
                            </div>

                            {/* Estado */}
                            <div>
                                <label htmlFor="estado" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('pedidos.fields.estado')} <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="estado"
                                    value={form.estado}
                                    onChange={e => updateField('estado', e.target.value)}
                                    className={inputClass('estado')}
                                >
                                    <option value="borrador">{t('pedidos.status.borrador')}</option>
                                    <option value="solicitado">{t('pedidos.status.solicitado')}</option>
                                    <option value="recibido">{t('pedidos.status.recibido')}</option>
                                    <option value="facturado">{t('pedidos.status.facturado')}</option>
                                    <option value="cancelado">{t('pedidos.status.cancelado')}</option>
                                </select>
                                <InputError message={getError('estado')} className="mt-1.5" />
                            </div>

                            {/* Fecha solicitud */}
                            <div>
                                <label htmlFor="fecha_solicitud" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('pedidos.fields.fechaSolicitud')} <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="fecha_solicitud"
                                    type="date"
                                    value={form.fecha_solicitud}
                                    onChange={e => updateField('fecha_solicitud', e.target.value)}
                                    className={inputClass('fecha_solicitud')}
                                />
                                <InputError message={getError('fecha_solicitud')} className="mt-1.5" />
                            </div>

                            {/* Fecha recepción */}
                            <div>
                                <label htmlFor="fecha_recepcion" className="mb-1.5 block text-sm font-medium text-text-main">
                                    {t('pedidos.fields.fechaRecepcion')}
                                    <span className="ml-1 text-xs font-normal text-text-hint">(opcional)</span>
                                </label>
                                <input
                                    id="fecha_recepcion"
                                    type="date"
                                    value={form.fecha_recepcion}
                                    onChange={e => updateField('fecha_recepcion', e.target.value)}
                                    className={inputClass('fecha_recepcion')}
                                />
                            </div>
                        </div>
                    </fieldset>

                    {/* ── SECCIÓN 2: Campos REPSOL ─────────────────────────── */}
                    {isRepsol && (
                        <fieldset className="rounded-2xl border border-red-200 bg-red-50/20 p-6 shadow-sm space-y-5">
                            <legend className="flex items-center gap-2 text-sm font-semibold text-text-main px-1">
                                Datos REPSOL
                                <span className="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">R</span>
                            </legend>
                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label htmlFor="importe_solicitado" className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('pedidos.fields.importeSolicitado')} <span className="text-red-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <input
                                            id="importe_solicitado"
                                            type="number" min="0" step="0.01"
                                            value={form.importe_solicitado}
                                            onChange={e => updateField('importe_solicitado', e.target.value)}
                                            className={`${inputClass('importe_solicitado')} pr-7`}
                                            placeholder="0.00"
                                        />
                                        <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-text-hint">€</span>
                                    </div>
                                    <InputError message={getError('importe_solicitado')} className="mt-1.5" />
                                </div>
                                <div>
                                    <label htmlFor="unidades_solicitadas" className="mb-1.5 block text-sm font-medium text-text-main">
                                        {t('pedidos.fields.unidadesSolicitadas')} <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="unidades_solicitadas"
                                        type="number" min="0" step="0.01"
                                        value={form.unidades_solicitadas}
                                        onChange={e => updateField('unidades_solicitadas', e.target.value)}
                                        className={inputClass('unidades_solicitadas')}
                                        placeholder="0"
                                    />
                                    <InputError message={getError('unidades_solicitadas')} className="mt-1.5" />
                                </div>
                            </div>
                        </fieldset>
                    )}

                    {/* ── SECCIÓN 3: Líneas del pedido ─────────────────────── */}
                    <fieldset className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-4">
                        <legend className="text-sm font-semibold text-text-main px-1">
                            {t('pedidos.items.title')}
                        </legend>
                        <ItemsTable
                            items={items}
                            onChange={setItems}
                            errors={serverErrors}
                            disabled={false}
                        />
                        {submitAttempted && localErrors.items && (
                            <p className="text-xs font-medium text-red-600">{localErrors.items}</p>
                        )}
                    </fieldset>

                    {/* ── SECCIÓN 4: Resumen total ──────────────────────────── */}
                    {items.length > 0 && (
                        <div className="rounded-2xl border border-border bg-surface-2 px-6 py-4">
                            <div className="flex items-center justify-between">
                                <span className="text-sm font-semibold text-text-main">
                                    {t('pedidos.items.total')}
                                </span>
                                <span className="text-xl font-bold text-(--ciete-red)">
                                    {totalPedido.toLocaleString('es-ES', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    })} €
                                </span>
                            </div>
                            <p className="mt-1 text-xs text-text-hint">
                                {items.length} {items.length === 1 ? 'línea' : 'líneas'} · Calculado automáticamente
                            </p>
                        </div>
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
                            onClick={() => router.visit(route('pedidos.index'))}
                            className="text-sm font-medium text-text-muted transition hover:text-text-main"
                        >
                            {t('common.actions.cancel')}
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
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
                                : isEditing ? t('common.actions.save') : t('pedidos.create')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
