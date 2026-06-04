import ContextualPageHeader from '@/Components/ContextualPageHeader';
import { useMastersBackLink } from '@/Hooks/useMastersBackLink';
import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function TarifarioForm({ tarifario = null, contratos = [], activeContext = null }) {
    const mastersBack = useMastersBackLink();
    const isEditing = Boolean(tarifario);
    const { data, setData, post, put, processing, errors } = useForm({
        id_contrato: tarifario?.id_contrato ?? '',
        nombre: tarifario?.nombre ?? '',
        version: tarifario?.version ?? '',
        fecha_inicio_vigencia: tarifario?.fecha_inicio_vigencia ?? '',
        fecha_fin_vigencia: tarifario?.fecha_fin_vigencia ?? '',
        factor_multiplicador: tarifario?.factor_multiplicador ?? '1.0000',
        moneda: tarifario?.moneda ?? 'EUR',
        es_predeterminado: tarifario?.es_predeterminado ?? false,
        activo: tarifario?.activo ?? true,
        observaciones: tarifario?.observaciones ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEditing) {
            put(route('maestros.tarifarios.update', tarifario.id_tarifario));
            return;
        }

        post(route('maestros.tarifarios.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Tarifario</h2>}>
            <Head title={isEditing ? 'Editar tarifario' : 'Nuevo tarifario'} />

            <div className="ciete-page ciete-page-narrow">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title={isEditing ? 'Editar tarifario' : 'Nuevo tarifario'}
                    backHref={route('maestros.index')}
                />

                <form onSubmit={submit} className="rounded-lg border border-border bg-surface p-5">
                    <div className="mb-5 rounded-lg border border-border bg-surface-2 px-4 py-3">
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">Contexto activo</p>
                        <p className="mt-1 text-sm font-semibold text-text-main">
                            {activeContext?.nombre ?? 'Contexto no disponible'}
                        </p>
                    </div>

                    {contratos.length === 0 && (
                        <div className="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                            <p className="text-sm font-medium text-amber-800">
                                No hay contratos activos para crear un tarifario en este contexto.
                            </p>
                            <Link
                                href={route('maestros.contratos.index')}
                                className="mt-3 inline-flex rounded-md border border-amber-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 transition hover:bg-amber-100"
                            >
                                Abrir contratos
                            </Link>
                        </div>
                    )}

                    <div className="grid gap-4 md:grid-cols-2">
                        <label className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Contrato</span>
                            <select
                                value={data.id_contrato}
                                onChange={(event) => setData('id_contrato', event.target.value)}
                                disabled={contratos.length === 0}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            >
                                <option value="">Selecciona contrato</option>
                                {contratos.map((contrato) => (
                                    <option key={contrato.id_contrato} value={contrato.id_contrato}>
                                        {contrato.codigo_contrato} - {contrato.nombre || 'Sin nombre'}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.id_contrato} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Nombre</span>
                            <input
                                value={data.nombre}
                                onChange={(event) => setData('nombre', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.nombre} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Version</span>
                            <input
                                value={data.version}
                                onChange={(event) => setData('version', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.version} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Inicio vigencia</span>
                            <input
                                type="date"
                                value={data.fecha_inicio_vigencia}
                                onChange={(event) => setData('fecha_inicio_vigencia', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.fecha_inicio_vigencia} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Fin vigencia</span>
                            <input
                                type="date"
                                value={data.fecha_fin_vigencia}
                                onChange={(event) => setData('fecha_fin_vigencia', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.fecha_fin_vigencia} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Factor multiplicador</span>
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                value={data.factor_multiplicador}
                                onChange={(event) => setData('factor_multiplicador', event.target.value)}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm"
                            />
                            <InputError message={errors.factor_multiplicador} className="mt-1" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-text-main">Moneda</span>
                            <input
                                value={data.moneda}
                                onChange={(event) => setData('moneda', event.target.value.toUpperCase())}
                                maxLength={3}
                                className="mt-1 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm uppercase"
                            />
                            <InputError message={errors.moneda} className="mt-1" />
                        </label>

                        <label className="flex items-center gap-2 md:col-span-2">
                            <input
                                type="checkbox"
                                checked={Boolean(data.activo)}
                                onChange={(event) => {
                                    const checked = event.target.checked;
                                    setData('activo', checked);

                                    if (!checked) {
                                        setData('es_predeterminado', false);
                                    }
                                }}
                                className="rounded border-border text-(--ciete-red)"
                            />
                            <span className="text-sm font-semibold text-text-main">Activo para nuevas operaciones</span>
                        </label>

                        <div className="block md:col-span-2">
                            <span className="text-sm font-semibold text-text-main">Tarifario predeterminado</span>
                            <div className="mt-2 flex items-start gap-3 rounded-lg border border-border bg-surface-2 px-4 py-3">
                                <input
                                    type="checkbox"
                                    checked={Boolean(data.es_predeterminado)}
                                    disabled={!data.activo}
                                    onChange={(event) => setData('es_predeterminado', event.target.checked)}
                                    className="mt-0.5 rounded border-border text-(--ciete-red)"
                                />
                                <span className="block text-sm font-semibold text-text-main">Usar por defecto en trabajos nuevos</span>
                            </div>
                            <InputError message={errors.es_predeterminado} className="mt-1" />
                        </div>

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
                            Guardar tarifario
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
