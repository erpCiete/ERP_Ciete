import React, { useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { useI18n } from '@/i18n';
import InputError from '@/Components/InputError';

export default function ImportacionUpload({ preview, importaciones, resumen, detalleImportacion, detalleFilas, agrupaciones, filtros = {} }) {
    const { t } = useI18n();
    const registros = importaciones?.data ?? [];
    const detalleRegistros = detalleFilas?.data ?? [];
    const resumenActual = resumen ?? {};
    const grupos = agrupaciones ?? {};

    const [fase, setFase] = useState(preview ? 'preview' : 'upload');
    const [filterValues, setFilterValues] = useState({
        contexto: filtros.contexto ?? '',
        archivo: filtros.archivo ?? '',
        hoja: filtros.hoja ?? '',
        severidad: filtros.severidad ?? '',
        clasificacion: filtros.clasificacion ?? '',
        tipo: filtros.tipo ?? '',
        resultado: filtros.resultado ?? '',
        detalle: filtros.detalle ?? '',
    });

    useEffect(() => {
        setFilterValues({
            contexto: filtros.contexto ?? '',
            archivo: filtros.archivo ?? '',
            hoja: filtros.hoja ?? '',
            severidad: filtros.severidad ?? '',
            clasificacion: filtros.clasificacion ?? '',
            tipo: filtros.tipo ?? '',
            resultado: filtros.resultado ?? '',
            detalle: filtros.detalle ?? '',
        });
    }, [filtros]);

    const { data, setData, post, processing, progress, errors } = useForm({
        tipo: '',
        archivo: null,
    });

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 10 * 1024 * 1024) {
                alert("Error: El archivo excede el límite de 10MB permitido.");
                return;
            }
            setData('archivo', file);
        }
    };

    const submitUpload = (e) => {
        e.preventDefault();
        post(route('importaciones.store'), {
            onSuccess: () => setFase('preview'),
        });
    };

    const submitFilters = (e) => {
        e.preventDefault();

        router.get(route('importaciones.index'), filterValues, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        router.get(route('importaciones.index'), {}, {
            preserveState: true,
            replace: true,
        });
    };

    const setFilter = (key, value) => {
        setFilterValues((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const severityTone = {
        info: 'border-slate-300 text-slate-600',
        warning: 'border-amber-300 text-amber-700',
        error: 'border-red-300 text-red-700',
    };

    const classificationTone = {
        acceptable: 'border-emerald-300 text-emerald-700',
        functional_decision: 'border-orange-300 text-orange-700',
        technical_improvement: 'border-sky-300 text-sky-700',
        do_not_invent: 'border-fuchsia-300 text-fuchsia-700',
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{t('importaciones.title')}</h2>}
        >
            <Head title={t('importaciones.title')} />

            <div className="mx-auto max-w-7xl space-y-6">
                {registros.length > 0 && (
                    <>
                        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                            {[
                                ['Importaciones', resumenActual.importaciones ?? 0],
                                ['Filas leídas', resumenActual.filas_leidas ?? 0],
                                ['Importadas', resumenActual.filas_importadas ?? 0],
                                ['Ignoradas', resumenActual.filas_ignoradas ?? 0],
                                ['Avisos', resumenActual.filas_con_aviso ?? 0],
                                ['Errores', resumenActual.filas_con_error ?? 0],
                            ].map(([label, value]) => (
                                <div key={label} className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                    <p className="text-xs font-semibold uppercase text-text-hint">{label}</p>
                                    <p className="mt-2 text-2xl font-semibold text-text-main">{value}</p>
                                </div>
                            ))}
                        </section>

                        <section className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                            <div className="flex flex-col gap-1">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Filtro operativo</h3>
                                <p className="text-sm text-text-muted">Archivo, hoja, severidad y clasificación para separar ruido aceptable de decisiones funcionales reales.</p>
                            </div>

                            <form onSubmit={submitFilters} className="mt-4 grid gap-3 lg:grid-cols-4">
                                <input value={filterValues.contexto} onChange={(e) => setFilter('contexto', e.target.value)} placeholder="Contexto" className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main" />
                                <input value={filterValues.archivo} onChange={(e) => setFilter('archivo', e.target.value)} placeholder="Archivo" className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main" />
                                <input value={filterValues.hoja} onChange={(e) => setFilter('hoja', e.target.value)} placeholder="Hoja" className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main" />
                                <select value={filterValues.severidad} onChange={(e) => setFilter('severidad', e.target.value)} className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main">
                                    <option value="">Todas las severidades</option>
                                    <option value="warning">Aviso</option>
                                    <option value="error">Error</option>
                                    <option value="info">Info</option>
                                </select>
                                <select value={filterValues.clasificacion} onChange={(e) => setFilter('clasificacion', e.target.value)} className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main">
                                    <option value="">Todas las clasificaciones</option>
                                    <option value="acceptable">Aceptable</option>
                                    <option value="functional_decision">Decisión funcional</option>
                                    <option value="technical_improvement">Mejora técnica</option>
                                    <option value="do_not_invent">No inventar datos</option>
                                </select>
                                <input value={filterValues.tipo} onChange={(e) => setFilter('tipo', e.target.value)} placeholder="Código de aviso" className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main" />
                                <select value={filterValues.resultado} onChange={(e) => setFilter('resultado', e.target.value)} className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main">
                                    <option value="">Todos los resultados</option>
                                    <option value="ignored">Ignoradas</option>
                                    <option value="warning">Avisos</option>
                                    <option value="error">Errores</option>
                                </select>
                                <select value={String(filterValues.detalle ?? '')} onChange={(e) => setFilter('detalle', e.target.value)} className="rounded-md border border-border bg-surface-2 px-3 py-2 text-sm text-text-main">
                                    <option value="">Detalle del último visible</option>
                                    {registros.map((registro) => (
                                        <option key={registro.id_importacion} value={registro.id_importacion}>
                                            {registro.archivo_original}
                                        </option>
                                    ))}
                                </select>
                                <div className="flex gap-2 lg:col-span-4">
                                    <button type="submit" className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark)">
                                        Filtrar
                                    </button>
                                    <button type="button" onClick={clearFilters} className="rounded-md border border-border px-4 py-2 text-sm font-semibold text-text-main">
                                        Limpiar
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                            <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h3 className="text-sm font-semibold uppercase text-text-hint">Historial de cargas</h3>
                                    <p className="text-sm text-text-muted">Resumen por archivo con filas leídas, importadas, ignoradas, avisos y errores.</p>
                                </div>
                                <span className="text-xs font-medium text-text-hint">{registros.length} registros visibles</span>
                            </div>

                            <div className="mt-4 overflow-x-auto rounded-lg border border-border">
                                <table className="min-w-full divide-y divide-border text-sm">
                                    <thead className="bg-surface-2 text-left text-xs font-semibold uppercase text-text-hint">
                                        <tr>
                                            <th className="px-4 py-3">Archivo</th>
                                            <th className="px-4 py-3">Contexto</th>
                                            <th className="px-4 py-3">Tipo</th>
                                            <th className="px-4 py-3">Estado</th>
                                            <th className="px-4 py-3 text-right">Leídas</th>
                                            <th className="px-4 py-3 text-right">Importadas</th>
                                            <th className="px-4 py-3 text-right">Ignoradas</th>
                                            <th className="px-4 py-3 text-right">Avisos</th>
                                            <th className="px-4 py-3 text-right">Errores</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        {registros.map((registro) => (
                                            <tr key={registro.id_importacion} className={`bg-surface ${detalleImportacion?.id_importacion === registro.id_importacion ? 'bg-(--ciete-red)/5' : ''}`}>
                                                <td className="max-w-[18rem] truncate px-4 py-3 font-medium text-text-main">
                                                    {registro.archivo_original}
                                                </td>
                                                <td className="px-4 py-3 text-text-muted">{registro.contexto?.codigo ?? '-'}</td>
                                                <td className="px-4 py-3 text-text-muted">{registro.tipo}</td>
                                                <td className="px-4 py-3">
                                                    <span className="rounded-full border border-border px-2 py-1 text-xs font-medium text-text-muted">
                                                        {registro.estado}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-right text-text-muted">{registro.total_filas}</td>
                                                <td className="px-4 py-3 text-right text-text-muted">{registro.filas_importadas}</td>
                                                <td className="px-4 py-3 text-right text-text-muted">{registro.filas_ignoradas ?? 0}</td>
                                                <td className="px-4 py-3 text-right text-text-muted">{registro.filas_con_aviso ?? 0}</td>
                                                <td className="px-4 py-3 text-right text-text-muted">{registro.filas_con_error}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section className="grid gap-4 xl:grid-cols-3">
                            <div className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Avisos por archivo</h3>
                                <div className="mt-3 space-y-2 text-sm">
                                    {(grupos.avisosPorArchivo ?? []).map((row) => (
                                        <div key={row.archivo} className="flex items-center justify-between gap-3">
                                            <span className="truncate text-text-main">{row.archivo}</span>
                                            <span className="text-text-muted">{row.total}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Avisos por hoja</h3>
                                <div className="mt-3 space-y-2 text-sm">
                                    {(grupos.avisosPorHoja ?? []).map((row) => (
                                        <div key={`${row.archivo}-${row.hoja}`} className="space-y-1">
                                            <div className="truncate font-medium text-text-main">{row.hoja}</div>
                                            <div className="flex items-center justify-between gap-3 text-text-muted">
                                                <span className="truncate">{row.archivo}</span>
                                                <span>{row.total}</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Top códigos de aviso</h3>
                                <div className="mt-3 space-y-2 text-sm">
                                    {(grupos.avisosPorTipo ?? []).map((row) => (
                                        <div key={`${row.codigo}-${row.clasificacion}`} className="space-y-1">
                                            <div className="truncate font-medium text-text-main">{row.codigo}</div>
                                            <div className="flex items-center justify-between gap-3 text-text-muted">
                                                <span>{row.clasificacion}</span>
                                                <span>{row.total}</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </section>

                        <section className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                            <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h3 className="text-sm font-semibold uppercase text-text-hint">Detalle de avisos y filas ignoradas</h3>
                                    <p className="text-sm text-text-muted">
                                        {detalleImportacion?.archivo_original
                                            ? `Archivo seleccionado: ${detalleImportacion.archivo_original}`
                                            : 'Sin archivo seleccionado.'}
                                    </p>
                                </div>
                                <span className="text-xs font-medium text-text-hint">{detalleRegistros.length} filas visibles</span>
                            </div>

                            <div className="mt-4 overflow-x-auto rounded-lg border border-border">
                                <table className="min-w-full divide-y divide-border text-sm">
                                    <thead className="bg-surface-2 text-left text-xs font-semibold uppercase text-text-hint">
                                        <tr>
                                            <th className="px-4 py-3">Hoja</th>
                                            <th className="px-4 py-3">Fila</th>
                                            <th className="px-4 py-3">Severidad</th>
                                            <th className="px-4 py-3">Clasificación</th>
                                            <th className="px-4 py-3">Código</th>
                                            <th className="px-4 py-3">Mensaje</th>
                                            <th className="px-4 py-3">Decisión sugerida</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        {detalleRegistros.map((row) => (
                                            <tr key={row.id_importacion_fila} className="bg-surface">
                                                <td className="max-w-[12rem] truncate px-4 py-3 text-text-main">{row.hoja_origen ?? '-'}</td>
                                                <td className="px-4 py-3 text-text-muted">{row.numero_fila}</td>
                                                <td className="px-4 py-3">
                                                    <span className={`rounded-full border px-2 py-1 text-xs font-medium ${severityTone[row.severidad] ?? 'border-border text-text-muted'}`}>
                                                        {row.severidad ?? '-'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className={`rounded-full border px-2 py-1 text-xs font-medium ${classificationTone[row.clasificacion] ?? 'border-border text-text-muted'}`}>
                                                        {row.clasificacion ?? '-'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs text-text-muted">{row.codigo ?? '-'}</td>
                                                <td className="min-w-[22rem] px-4 py-3 text-text-main">{row.mensaje_error ?? '-'}</td>
                                                <td className="min-w-[20rem] px-4 py-3 text-text-muted">{row.decision_sugerida ?? '-'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section className="grid gap-4 xl:grid-cols-2">
                            <div className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Filas ignoradas</h3>
                                <div className="mt-3 grid gap-3 md:grid-cols-2">
                                    <div className="space-y-2 text-sm">
                                        {(grupos.ignoradasPorArchivo ?? []).map((row) => (
                                            <div key={row.archivo} className="flex items-center justify-between gap-3">
                                                <span className="truncate text-text-main">{row.archivo}</span>
                                                <span className="text-text-muted">{row.total}</span>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="space-y-2 text-sm">
                                        {(grupos.ignoradasPorHoja ?? []).map((row) => (
                                            <div key={`${row.archivo}-${row.hoja}`} className="space-y-1">
                                                <div className="truncate font-medium text-text-main">{row.hoja}</div>
                                                <div className="flex items-center justify-between gap-3 text-text-muted">
                                                    <span className="truncate">{row.archivo}</span>
                                                    <span>{row.total}</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-lg border border-border bg-surface p-4 shadow-sm">
                                <h3 className="text-sm font-semibold uppercase text-text-hint">Columnas pendientes</h3>
                                <div className="mt-3 max-h-72 space-y-2 overflow-y-auto text-sm">
                                    {(grupos.columnasPendientes ?? []).map((row) => (
                                        <div key={`${row.archivo}-${row.hoja}-${row.columna}`} className="rounded-md border border-border p-3">
                                            <div className="font-medium text-text-main">{row.columna}</div>
                                            <div className="mt-1 text-xs text-text-muted">{row.archivo} · {row.hoja}</div>
                                            <div className="mt-2 text-xs text-text-muted">{row.decision}</div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </section>
                    </>
                )}

                {fase === 'upload' && (
                    <form onSubmit={submitUpload} className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-8">
                        <section>
                            <h3 className="text-sm font-semibold uppercase text-text-hint">{t('importaciones.selectType')}</h3>
                            <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                {['estacionesMoeve', 'estacionesRepsol', 'trabajos', 'tarifario'].map((tipo) => (<label key={tipo} className={`flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition ${data.tipo === tipo ? 'border-(--ciete-red) bg-(--ciete-red)/5' : 'border-border'}`}>
                                    <input
                                        type="radio"
                                        name="tipo"
                                        value={tipo}
                                        onChange={e => setData('tipo', e.target.value)}
                                        className="text-(--ciete-red) focus:ring-(--ciete-red)"
                                    />
                                    <span className="text-sm font-medium text-text-main">{t(`importaciones.types.${tipo}`)}</span>
                                </label>
                                ))}
                            </div>
                            <InputError message={errors.tipo} className="mt-2" />
                        </section>

                        <section>
                            <div className="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border py-12 transition hover:border-(--ciete-red)/40">
                                <input
                                    type="file"
                                    accept=".xlsx,.csv,.xls"
                                    onChange={handleFileChange}
                                    className="absolute inset-0 cursor-pointer opacity-0"
                                />
                                <div className="text-center">
                                    <p className="text-sm font-medium text-text-main">
                                        {data.archivo ? data.archivo.name : t('importaciones.dropzone')}
                                    </p>
                                    <p className="mt-1 text-xs text-text-hint">{t('importaciones.formats')}</p>
                                </div>
                            </div>
                            <InputError message={errors.archivo} className="mt-2" />
                        </section>

                        {progress && (
                            <div className="h-2 w-full rounded-full bg-surface-2 overflow-hidden">
                                <div className="h-full bg-(--ciete-red) transition-all" style={{ width: `${progress.percentage}%` }} />
                            </div>
                        )}

                        <div className="flex justify-end pt-4">
                            <button
                                type="submit"
                                disabled={processing || !data.archivo || !data.tipo}
                                className="w-full rounded-md bg-(--ciete-red) px-6 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark) disabled:opacity-50 sm:w-auto"
                            >
                                {processing ? t('importaciones.processing') : t('importaciones.upload')}
                            </button>
                        </div>
                    </form>
                )}

                {fase === 'preview' && preview && (
                    <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-6">
                        <h3 className="text-lg font-medium text-text-main">{t('importaciones.preview')}</h3>

                        <p className="text-xs text-text-hint">{t('help.sections.mobile.tablesNote')}</p>
                        <div className="overflow-x-auto rounded-lg border border-border">
                            <table className="min-w-full divide-y divide-border text-sm">
                                <thead className="bg-surface-2 text-left text-xs font-semibold uppercase text-text-hint">
                                    <tr>
                                        <th className="px-4 py-3">Fila</th>
                                        <th className="px-4 py-3">Datos del Excel</th>
                                        <th className="px-4 py-3">Estado / Errores</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {preview.map((fila, i) => (
                                        <tr key={i} className={fila.valido ? 'bg-green-500/5' : 'bg-red-500/5'}>
                                            <td className="px-4 py-3 text-text-muted">{fila.numero_fila}</td>
                                            <td className="px-4 py-3 font-mono text-xs">
                                                {JSON.stringify(fila.datos)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {fila.valido ? (
                                                    <span className="text-green-600 font-bold">✅ OK</span>
                                                ) : (
                                                    <div className="text-red-600 text-xs">
                                                        {fila.errores.map((err, idx) => <p key={idx}>• {err}</p>)}
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex flex-col-reverse gap-3 border-t border-border pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <button onClick={() => setFase('upload')} className="text-sm font-medium text-text-muted hover:text-text-main">
                                {t('importaciones.cancel')}
                            </button>
                            <Link
                                href={route('importaciones.confirm', preview[0].id_importacion)}
                                method="post"
                                as="button"
                                className="w-full rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700 sm:w-auto"
                            >
                                {t('importaciones.confirm')}
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
