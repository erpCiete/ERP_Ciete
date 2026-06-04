import ContextualPageHeader from '@/Components/ContextualPageHeader';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function TarifarioLineaForm({ linea = null, tarifarios = [], unidades = [] }) {
    const mastersBack = useMastersBackLink();
    const isEditing = Boolean(linea);
    const { data, setData, post, put, processing, errors } = useForm({
        id_tarifario: linea?.id_tarifario ?? '',
        codigo_tarifa: linea?.codigo_tarifa ?? '',
        grupo: linea?.grupo ?? '',
        actuacion: linea?.actuacion ?? '',
        descripcion: linea?.descripcion ?? '',
        tarifa_anterior: linea?.tarifa_anterior ?? '',
        tarifa_base: linea?.tarifa_base ?? '',
        tarifa_aplicada: linea?.tarifa_aplicada ?? '',
        id_unidad: linea?.id_unidad ?? '',
        activo: linea?.activo ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEditing) {
            put(route('maestros.tarifario-lineas.update', linea.id_tarifario_linea));
            return;
        }

        post(route('maestros.tarifario-lineas.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Linea de tarifario</h2>}
        >
            <Head title={isEditing ? 'Editar linea de tarifario' : 'Nueva linea de tarifario'} />

            <div className="ciete-page ciete-page-narrow">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title={isEditing ? 'Editar linea de tarifario' : 'Nueva linea de tarifario'}
                    description="La linea queda disponible para pedido_items futuros. No crea pedidos ni facturas."
                    backHref={route('maestros.index')}
                />

                <form onSubmit={submit} className="rounded-lg border border-border bg-surface p-5">
                    <div className="grid gap-4 md:grid-cols-2">
                        <label className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Tarifario</span>
                            <select
                                value={data.id_tarifario}
                                onChange={(event) => setData('id_tarifario', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            >
                                <option value="">Selecciona tarifario</option>
                                {tarifarios.map((tarifario) => (
                                    <option key={tarifario.id_tarifario} value={tarifario.id_tarifario}>
                                        {tarifario.nombre} {tarifario.version ? `(${tarifario.version})` : ''}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.id_tarifario} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Codigo tarifa</span>
                            <input
                                value={data.codigo_tarifa}
                                onChange={(event) => setData('codigo_tarifa', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.codigo_tarifa} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Grupo</span>
                            <input
                                value={data.grupo}
                                onChange={(event) => setData('grupo', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.grupo} className="mt-1" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Actuacion</span>
                            <input
                                value={data.actuacion}
                                onChange={(event) => setData('actuacion', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.actuacion} className="mt-1" />
                        </label>

                        <label className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Descripcion</span>
                            <textarea
                                value={data.descripcion}
                                onChange={(event) => setData('descripcion', event.target.value)}
                                rows={3}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.descripcion} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Tarifa anterior</span>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                value={data.tarifa_anterior}
                                onChange={(event) => setData('tarifa_anterior', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.tarifa_anterior} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Tarifa base</span>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                value={data.tarifa_base}
                                onChange={(event) => setData('tarifa_base', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.tarifa_base} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Tarifa aplicada</span>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                value={data.tarifa_aplicada}
                                onChange={(event) => setData('tarifa_aplicada', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.tarifa_aplicada} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Unidad</span>
                            <select
                                value={data.id_unidad}
                                onChange={(event) => setData('id_unidad', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            >
                                <option value="">Sin unidad</option>
                                {unidades.map((unidad) => (
                                    <option key={unidad.id_unidad} value={unidad.id_unidad}>
                                        {unidad.nombre} {unidad.abreviatura ? `(${unidad.abreviatura})` : ''}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.id_unidad} className="mt-1" />
                        </label>

                        <label className="flex items-center gap-2 md:col-span-2">
                            <input
                                type="checkbox"
                                checked={Boolean(data.activo)}
                                onChange={(event) => setData('activo', event.target.checked)}
                                className="rounded border-border text-(--ciete-red)"
                            />
                            <span className="text-sm font-semibold text-text-main">Activa para nuevas operaciones</span>
                        </label>
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
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
                            Guardar linea
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
