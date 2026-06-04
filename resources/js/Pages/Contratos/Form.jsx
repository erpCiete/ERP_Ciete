import ContextualPageHeader from '@/Components/ContextualPageHeader';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ContratoForm({ contrato = null, empresas = [] }) {
    const mastersBack = useMastersBackLink();
    const isEditing = Boolean(contrato);
    const { data, setData, post, put, processing, errors } = useForm({
        id_empresa_cliente: contrato?.id_empresa_cliente ?? '',
        codigo_contrato: contrato?.codigo_contrato ?? '',
        nombre: contrato?.nombre ?? '',
        tipo: contrato?.tipo ?? 'marco',
        fecha_inicio: contrato?.fecha_inicio ?? '',
        fecha_fin: contrato?.fecha_fin ?? '',
        estado: contrato?.estado ?? 'vigente',
        activo: contrato?.activo ?? true,
        observaciones: contrato?.observaciones ?? '',
        ariba_cta_mayor: contrato?.ariba_cta_mayor ?? '',
        ariba_propuesta_opex: contrato?.ariba_propuesta_opex ?? '',
        ariba_accion_gasto: contrato?.ariba_accion_gasto ?? '',
        ariba_nombre_proveedor: contrato?.ariba_nombre_proveedor ?? '',
        ariba_sociedad: contrato?.ariba_sociedad ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEditing) {
            put(route('maestros.contratos.update', contrato.id_contrato));
            return;
        }

        post(route('maestros.contratos.store'));
    };

    const inputClass = 'mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm';

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Contrato</h2>}>
            <Head title={isEditing ? 'Editar contrato' : 'Nuevo contrato'} />

            <div className="ciete-page ciete-page-narrow">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title={isEditing ? 'Editar contrato' : 'Nuevo contrato'}
                    backHref={route('maestros.index')}
                />

                <form onSubmit={submit} className="space-y-6">
                    {/* Datos generales del contrato */}
                    <div className="rounded-lg border border-border bg-surface p-5">
                        <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-text-hint">Datos del contrato</h3>
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Empresa cliente</span>
                                <select
                                    value={data.id_empresa_cliente}
                                    onChange={(event) => setData('id_empresa_cliente', event.target.value)}
                                    className={inputClass}
                                >
                                    <option value="">Selecciona empresa</option>
                                    {empresas.map((empresa) => (
                                        <option key={empresa.id_empresa} value={empresa.id_empresa}>
                                            {empresa.nombre} {empresa.cif ? `(${empresa.cif})` : ''}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.id_empresa_cliente} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Código de contrato</span>
                                <input
                                    value={data.codigo_contrato}
                                    onChange={(event) => setData('codigo_contrato', event.target.value)}
                                    className={inputClass}
                                />
                                <InputError message={errors.codigo_contrato} className="mt-1" />
                            </label>

                            <label className="block md:col-span-2">
                                <span className="text-sm font-semibold text-text-main">Nombre</span>
                                <input
                                    value={data.nombre}
                                    onChange={(event) => setData('nombre', event.target.value)}
                                    className={inputClass}
                                />
                                <InputError message={errors.nombre} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Tipo</span>
                                <select
                                    value={data.tipo}
                                    onChange={(event) => setData('tipo', event.target.value)}
                                    className={inputClass}
                                >
                                    <option value="marco">Marco</option>
                                    <option value="directo">Directo</option>
                                    <option value="otro">Otro</option>
                                </select>
                                <InputError message={errors.tipo} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Estado</span>
                                <select
                                    value={data.estado}
                                    onChange={(event) => setData('estado', event.target.value)}
                                    className={inputClass}
                                >
                                    <option value="vigente">Vigente</option>
                                    <option value="expirado">Expirado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <InputError message={errors.estado} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Fecha inicio</span>
                                <input
                                    type="date"
                                    value={data.fecha_inicio}
                                    onChange={(event) => setData('fecha_inicio', event.target.value)}
                                    className={inputClass}
                                />
                                <InputError message={errors.fecha_inicio} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Fecha fin</span>
                                <input
                                    type="date"
                                    value={data.fecha_fin}
                                    onChange={(event) => setData('fecha_fin', event.target.value)}
                                    className={inputClass}
                                />
                                <InputError message={errors.fecha_fin} className="mt-1" />
                            </label>

                            <label className="flex items-center gap-2 md:col-span-2">
                                <input
                                    type="checkbox"
                                    checked={Boolean(data.activo)}
                                    onChange={(event) => setData('activo', event.target.checked)}
                                    className="rounded border-border text-(--ciete-red)"
                                />
                                <span className="text-sm font-semibold text-text-main">Activo para nuevas operaciones</span>
                            </label>

                            <label className="block md:col-span-2">
                                <span className="text-sm font-semibold text-text-main">Observaciones</span>
                                <textarea
                                    value={data.observaciones}
                                    onChange={(event) => setData('observaciones', event.target.value)}
                                    rows={3}
                                    className={inputClass}
                                />
                                <InputError message={errors.observaciones} className="mt-1" />
                            </label>
                        </div>
                    </div>

                    {/* Datos ARIBA / Solicitud Moeve */}
                    <div className="rounded-lg border border-amber-200 bg-amber-50 p-5">
                        <h3 className="mb-1 text-sm font-semibold uppercase tracking-wide text-amber-800">
                            Datos ARIBA / Solicitud Moeve
                        </h3>
                        <p className="mb-4 text-xs text-amber-700">
                            Estos campos se usan en las exportaciones PDF, CSV y cuadro ARIBA de los pedidos Moeve.
                            Si se dejan vacíos aparecerán como «Pendiente de parametrizar» en las exportaciones.
                        </p>
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Cta. de Mayor</span>
                                <input
                                    value={data.ariba_cta_mayor}
                                    onChange={(event) => setData('ariba_cta_mayor', event.target.value)}
                                    placeholder="Ej: 630000"
                                    className={inputClass}
                                />
                                <InputError message={errors.ariba_cta_mayor} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Propuesta de Inversión / Opex acción gasto</span>
                                <input
                                    value={data.ariba_propuesta_opex}
                                    onChange={(event) => setData('ariba_propuesta_opex', event.target.value)}
                                    placeholder="Ej: CAPEX-2026-001"
                                    className={inputClass}
                                />
                                <InputError message={errors.ariba_propuesta_opex} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Acción de gasto AC</span>
                                <input
                                    value={data.ariba_accion_gasto}
                                    onChange={(event) => setData('ariba_accion_gasto', event.target.value)}
                                    placeholder="Ej: AC-123456"
                                    className={inputClass}
                                />
                                <InputError message={errors.ariba_accion_gasto} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Sociedad (CIF / filial MOEVE)</span>
                                <input
                                    value={data.ariba_sociedad}
                                    onChange={(event) => setData('ariba_sociedad', event.target.value)}
                                    placeholder="Ej: MOEVE ES o A28003119"
                                    className={inputClass}
                                />
                                <p className="mt-1 text-xs text-text-hint">
                                    Fallback si la estación no tiene sociedad configurada. Se usa en la columna «Sociedad» de ARIBA y CSV.
                                </p>
                                <InputError message={errors.ariba_sociedad} className="mt-1" />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-text-main">Nombre proveedor / Contrato</span>
                                <input
                                    value={data.ariba_nombre_proveedor}
                                    onChange={(event) => setData('ariba_nombre_proveedor', event.target.value)}
                                    placeholder="Ej: CIETE INGENIEROS S.A."
                                    className={inputClass}
                                />
                                <p className="mt-1 text-xs text-text-hint">
                                    Se mostrará como «{data.codigo_contrato || '772'} / {data.ariba_nombre_proveedor || 'Pendiente de parametrizar'}» en las exportaciones.
                                </p>
                                <InputError message={errors.ariba_nombre_proveedor} className="mt-1" />
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <Link
                            href={mastersBack.href ?? route('maestros.index')}
                            className="rounded-md border border-border px-4 py-2 text-sm font-semibold text-text-main hover:bg-surface-2"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white transition hover:bg-(--ciete-red-dark) disabled:opacity-60"
                        >
                            Guardar contrato
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
