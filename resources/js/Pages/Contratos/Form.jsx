import ContextualPageHeader from '@/Components/ContextualPageHeader';
import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ContratoForm({ contrato = null, empresas = [] }) {
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
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEditing) {
            put(route('maestros.contratos.update', contrato.id_contrato));
            return;
        }

        post(route('maestros.contratos.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Contrato</h2>}>
            <Head title={isEditing ? 'Editar contrato' : 'Nuevo contrato'} />

            <div className="ciete-page ciete-page-narrow">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title={isEditing ? 'Editar contrato' : 'Nuevo contrato'}
                    description="El contrato queda disponible como dato base. No se crean trabajos, pedidos ni facturas desde aqui."
                    backHref={route('maestros.index')}
                />

                <form onSubmit={submit} className="rounded-lg border border-border bg-surface p-5">
                    <div className="grid gap-4 md:grid-cols-2">
                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Empresa cliente</span>
                            <select
                                value={data.id_empresa_cliente}
                                onChange={(event) => setData('id_empresa_cliente', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
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
                            <span className="text-sm font-semibold text-text-main">Codigo de contrato</span>
                            <input
                                value={data.codigo_contrato}
                                onChange={(event) => setData('codigo_contrato', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.codigo_contrato} className="mt-1" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Nombre</span>
                            <input
                                value={data.nombre}
                                onChange={(event) => setData('nombre', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.nombre} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Tipo</span>
                            <select
                                value={data.tipo}
                                onChange={(event) => setData('tipo', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
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
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
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
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.fecha_inicio} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Fecha fin</span>
                            <input
                                type="date"
                                value={data.fecha_fin}
                                onChange={(event) => setData('fecha_fin', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
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
                                rows={4}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.observaciones} className="mt-1" />
                        </label>
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <Link
                            href={route('maestros.index')}
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
