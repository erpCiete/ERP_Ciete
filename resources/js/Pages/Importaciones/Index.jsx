import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { useI18n } from '@/i18n';
import InputError from '@/Components/InputError';

export default function ImportacionUpload({ preview }) {
    const { t } = useI18n();

    const [fase, setFase] = useState(preview ? 'preview' : 'upload');

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

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-(--ciete-slate)">{t('importaciones.title')}</h2>}
        >
            <Head title={t('importaciones.title')} />

            <div className="mx-auto max-w-4xl space-y-6">

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
                                className="rounded-md bg-(--ciete-red) px-6 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark) disabled:opacity-50"
                            >
                                {processing ? t('importaciones.processing') : t('importaciones.upload')}
                            </button>
                        </div>
                    </form>
                )}

                {fase === 'preview' && preview && (
                    <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm space-y-6">
                        <h3 className="text-lg font-medium text-text-main">{t('importaciones.preview')}</h3>

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

                        <div className="flex justify-between border-t border-border pt-4">
                            <button onClick={() => setFase('upload')} className="text-sm font-medium text-text-muted hover:text-text-main">
                                {t('importaciones.cancel')}
                            </button>
                            <Link
                                href={route('importaciones.confirm', preview[0].id_importacion)}
                                method="post"
                                as="button"
                                className="rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700"
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
