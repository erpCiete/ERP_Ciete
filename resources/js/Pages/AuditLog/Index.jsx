import ContextualPageHeader from '@/Components/ContextualPageHeader';
import PaginationControls from '@/Components/ui/PaginationControls';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTheme } from '@/theme';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

const ACCION_BADGES = {
    crear: 'bg-green-100 text-green-800',
    actualizar: 'bg-blue-100 text-blue-800',
    eliminar: 'bg-red-100 text-red-800',
    activar: 'bg-emerald-100 text-emerald-800',
    desactivar: 'bg-gray-100 text-gray-700',
    cambiar_estado: 'bg-yellow-100 text-yellow-800',
    importar: 'bg-purple-100 text-purple-800',
    exportar: 'bg-indigo-100 text-indigo-800',
    limpiar_logs: 'bg-orange-100 text-orange-800',
    cambiar_contexto: 'bg-sky-100 text-sky-800',
};

function AccionBadge({ accion, label }) {
    const cls = ACCION_BADGES[accion] ?? 'bg-gray-100 text-gray-700';
    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${cls}`}>
            {label ?? accion}
        </span>
    );
}

function TruncatedCell({ value, maxLen = 60 }) {
    const [open, setOpen] = useState(false);
    if (!value) return <span className="text-text-hint">—</span>;
    const str = String(value);
    if (str.length <= maxLen) return <span>{str}</span>;
    return (
        <>
            <button
                type="button"
                className="text-left text-xs text-text-muted underline decoration-dashed hover:text-text-main"
                onClick={() => setOpen(true)}
            >
                {str.slice(0, maxLen)}…
            </button>
            {open && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                    onClick={() => setOpen(false)}
                >
                    <div
                        className="max-h-[80vh] max-w-lg overflow-y-auto rounded-xl bg-surface p-6 shadow-xl"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <p className="mb-4 text-sm text-text-main whitespace-pre-wrap break-words">{str}</p>
                        <button
                            type="button"
                            className="text-sm font-medium text-text-muted hover:text-text-main"
                            onClick={() => setOpen(false)}
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}

function UserLabel({ log }) {
    const label = log.usuario_resuelto
        ?? (log.usuario ? `${log.usuario.nombre ?? ''} ${log.usuario.apellidos ?? ''}`.trim() : '');

    if (!label) return <span className="text-text-hint">Sistema</span>;

    return (
        <div className="max-w-[220px]">
            <p className="truncate text-xs font-semibold text-text-main">{label}</p>
            {log.usuario?.email && <p className="truncate text-[11px] text-text-hint">{log.usuario.email}</p>}
        </div>
    );
}

function ChangeSummary({ log }) {
    const changes = log.cambios_resueltos ?? [];

    if (changes.length === 0) {
        return <TruncatedCell value={log.descripcion ?? log.resumen_resuelto} maxLen={90} />;
    }

    return (
        <div className="max-w-[340px] space-y-1 text-xs">
            <p className="font-medium text-text-main">{log.resumen_resuelto}</p>
            <div className="space-y-1 text-text-muted">
                {changes.slice(0, 3).map((change) => (
                    <div key={change.campo} className="grid grid-cols-[90px_1fr] gap-2">
                        <span className="truncate font-semibold text-text-main" title={change.campo}>{change.campo_label ?? change.campo}</span>
                        <span className="truncate" title={`${change.anterior ?? '—'} → ${change.nuevo ?? '—'}`}>
                            {String(change.anterior ?? '—')} → {String(change.nuevo ?? '—')}
                        </span>
                    </div>
                ))}
                {changes.length > 3 && <p className="text-text-hint">+{changes.length - 3} cambios mas</p>}
            </div>
        </div>
    );
}

function LimpiarModal({ filtros, onClose }) {
    const [fechaHasta, setFechaHasta] = useState(filtros.fecha_hasta ?? '');
    const [modulo, setModulo] = useState(filtros.modulo ?? '');
    const [loading, setLoading] = useState(false);

    function submit(conExportacion) {
        if (!fechaHasta) return;
        if (conExportacion) {
            // First download, then delete
            const params = new URLSearchParams({
                formato: 'csv',
                fecha_hasta: fechaHasta,
                ...(modulo ? { modulo } : {}),
            });
            window.open(route('registro.actividad.exportar') + '?' + params.toString(), '_blank');
        }
        setLoading(true);
        router.delete(route('registro.actividad.limpiar'), {
            data: {
                fecha_hasta: fechaHasta,
                modulo: modulo || undefined,
                confirmar: true,
                con_exportacion: conExportacion,
            },
            onFinish: () => {
                setLoading(false);
                onClose();
            },
        });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40" onClick={onClose}>
            <div
                className="w-full max-w-md rounded-xl bg-surface p-6 shadow-xl"
                onClick={(e) => e.stopPropagation()}
            >
                <h2 className="mb-1 text-base font-semibold text-text-main">Limpiar registros de auditoría</h2>
                <p className="mb-4 text-sm text-text-muted">
                    Antes de limpiar, descarga una copia de seguridad. Esta acción es irreversible.
                </p>

                <div className="mb-4 space-y-3">
                    <div>
                        <label className="mb-1 block text-xs font-medium text-text-muted">Fecha hasta (requerida)</label>
                        <input
                            type="date"
                            value={fechaHasta}
                            onChange={(e) => setFechaHasta(e.target.value)}
                            className="w-full rounded-md border border-border bg-surface px-3 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                        />
                    </div>
                    <div>
                        <label className="mb-1 block text-xs font-medium text-text-muted">Módulo (opcional)</label>
                        <input
                            type="text"
                            value={modulo}
                            onChange={(e) => setModulo(e.target.value)}
                            placeholder="Todos los módulos"
                            className="w-full rounded-md border border-border bg-surface px-3 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                        />
                    </div>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md px-4 py-2 text-sm text-text-muted hover:text-text-main"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        disabled={!fechaHasta || loading}
                        onClick={() => submit(false)}
                        className="rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-50"
                    >
                        Solo limpiar (sin copia)
                    </button>
                    <button
                        type="button"
                        disabled={!fechaHasta || loading}
                        onClick={() => submit(true)}
                        className="rounded-md bg-(--ciete-red) px-4 py-2 text-sm font-semibold text-white hover:bg-(--ciete-red-dark) disabled:opacity-50"
                    >
                        Descargar copia y limpiar
                    </button>
                </div>
            </div>
        </div>
    );
}

// ─── Shared filter bar ────────────────────────────────────────────────────────

function FiltrosBar({ localFiltros, setLocalFiltros, aplicarFiltros, exportar, canLimpiar, setShowLimpiar, modulosFiltro, accionesFiltro, contextosFiltro, usuariosFiltro }) {
    const soloDatos = localFiltros.solo_datos !== false && localFiltros.solo_datos !== 'false';

    function toggleSoloDatos() {
        const next = !soloDatos;
        setLocalFiltros((f) => ({ ...f, solo_datos: next }));
        aplicarFiltros({ solo_datos: next, page: 1 });
    }

    return (
        <div className="mb-4 rounded-xl border border-border bg-surface p-4 shadow-sm">
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                <input
                    type="text"
                    placeholder="Buscar…"
                    value={localFiltros.search ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, search: e.target.value }))}
                    onKeyDown={(e) => e.key === 'Enter' && aplicarFiltros({ page: 1 })}
                    className="rounded-md border border-border bg-surface px-3 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red) sm:col-span-2 xl:col-span-1"
                />
                <select
                    value={localFiltros.id_usuario ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, id_usuario: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                >
                    <option value="">Todos los usuarios</option>
                    {usuariosFiltro.map((u) => {
                        const label = `${u.nombre ?? ''} ${u.apellidos ?? ''}`.trim() || u.email || u.nombre_usuario || `#${u.id_usuario}`;
                        return <option key={u.id_usuario} value={u.id_usuario}>{label}</option>;
                    })}
                </select>
                <select
                    value={localFiltros.modulo ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, modulo: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                >
                    <option value="">Todos los módulos</option>
                    {modulosFiltro.map((m) => <option key={m} value={m}>{m}</option>)}
                </select>
                <select
                    value={localFiltros.accion ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, accion: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                >
                    <option value="">Todas las acciones</option>
                    {accionesFiltro.map((a) => <option key={a} value={a}>{a}</option>)}
                </select>
                <select
                    value={localFiltros.id_contexto ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, id_contexto: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                >
                    <option value="">Todos los contextos</option>
                    {contextosFiltro.map((c) => <option key={c.id_contexto} value={c.id_contexto}>{c.nombre}</option>)}
                </select>
                <input
                    type="number"
                    min="1"
                    placeholder="ID registro"
                    value={localFiltros.entity_id ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, entity_id: e.target.value }))}
                    onKeyDown={(e) => e.key === 'Enter' && aplicarFiltros({ page: 1 })}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />
                <input
                    type="text"
                    placeholder="Campo"
                    value={localFiltros.campo ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, campo: e.target.value }))}
                    onKeyDown={(e) => e.key === 'Enter' && aplicarFiltros({ page: 1 })}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                />
                <input
                    type="date"
                    value={localFiltros.fecha_desde ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, fecha_desde: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                    title="Desde"
                />
                <input
                    type="date"
                    value={localFiltros.fecha_hasta ?? ''}
                    onChange={(e) => setLocalFiltros((f) => ({ ...f, fecha_hasta: e.target.value }))}
                    className="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-(--ciete-red)"
                    title="Hasta"
                />
            </div>

            <div className="mt-3 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    onClick={() => aplicarFiltros({ page: 1 })}
                    className="rounded-md bg-(--ciete-red) px-4 py-1.5 text-sm font-semibold text-white hover:bg-(--ciete-red-dark)"
                >
                    Filtrar
                </button>
                <button
                    type="button"
                    onClick={() => {
                        setLocalFiltros({ solo_datos: true });
                        router.get(route('registro.actividad.index'), { solo_datos: 'true' }, { preserveState: false });
                    }}
                    className="rounded-md border border-border px-4 py-1.5 text-sm text-text-muted hover:text-text-main"
                >
                    Limpiar filtros
                </button>

                {/* Toggle solo actividad operativa */}
                <button
                    type="button"
                    onClick={toggleSoloDatos}
                    className={`rounded-md border px-3 py-1.5 text-xs font-medium transition ${
                        soloDatos
                            ? 'border-(--ciete-red)/40 bg-(--ciete-red)/10 text-(--ciete-red)'
                            : 'border-border bg-surface text-text-muted hover:text-text-main'
                    }`}
                    title={soloDatos
                        ? 'Muestra cambios de trabajos, pedidos, facturas, clientes, estaciones y datos de negocio. Clic para ver todo el historial.'
                        : 'Muestra también perfil, usuarios, roles, permisos, contexto, exportaciones, limpiezas y eventos internos. Clic para volver a actividad operativa.'}
                >
                    {soloDatos ? 'Solo actividad operativa' : 'Todo el historial'}
                </button>

                <div className="flex w-full flex-wrap gap-2 xl:ml-auto xl:w-auto xl:justify-end">
                    <button type="button" onClick={() => exportar('csv')} className="rounded-md border border-border px-3 py-1.5 text-sm text-text-muted hover:text-text-main">CSV</button>
                    <button type="button" onClick={() => exportar('xlsx')} className="rounded-md border border-border px-3 py-1.5 text-sm text-text-muted hover:text-text-main">XLSX</button>
                    {canLimpiar && (
                        <button
                            type="button"
                            onClick={() => setShowLimpiar(true)}
                            className="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100"
                        >
                            Limpiar registros
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

// ─── Vista Ciete Excel — tabla densa tipo hoja de cálculo ────────────────────

const ACCION_COLORS_EXCEL = {
    crear:          'text-emerald-700 bg-emerald-50',
    actualizar:     'text-blue-700 bg-blue-50',
    eliminar:       'text-red-700 bg-red-50',
    cambiar_estado: 'text-amber-700 bg-amber-50',
    importar:       'text-violet-700 bg-violet-50',
    activar:        'text-teal-700 bg-teal-50',
    desactivar:     'text-gray-600 bg-gray-100',
};

function AccionPill({ accion, label }) {
    const cls = ACCION_COLORS_EXCEL[accion] ?? 'text-gray-600 bg-gray-100';
    return (
        <span className={`inline-block rounded px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wide ${cls}`}>
            {label ?? accion}
        </span>
    );
}

function ExcelView({ rows, currentPage, lastPage, total, aplicarFiltros }) {
    const COLS = ['Fecha', 'Usuario', 'Módulo', 'Acción', 'ID', 'Campo', 'Anterior', 'Nuevo', 'Contexto'];

    if (rows.length === 0) {
        return (
            <div className="flex min-h-[200px] items-center justify-center rounded-xl border border-border bg-surface text-sm text-text-hint">
                Sin registros para los filtros aplicados.
            </div>
        );
    }

    return (
        <>
            <div className="overflow-x-auto rounded-xl border border-border shadow-sm">
                <table className="min-w-[1100px] w-full table-fixed divide-y divide-border text-xs">
                    <colgroup>
                        <col className="w-[130px]" />
                        <col className="w-[150px]" />
                        <col className="w-[110px]" />
                        <col className="w-[120px]" />
                        <col className="w-[50px]" />
                        <col className="w-[100px]" />
                        <col className="w-[160px]" />
                        <col className="w-[160px]" />
                        <col className="w-[100px]" />
                    </colgroup>
                    <thead className="sticky top-0 bg-surface-2">
                        <tr>
                            {COLS.map((h) => (
                                <th key={h} className="border-b border-border px-2 py-1.5 text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {h}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border bg-surface">
                        {rows.map((log, idx) => (
                            <tr key={log.id_audit} className={idx % 2 === 0 ? 'bg-surface' : 'bg-surface-2/40'}>
                                <td className="px-2 py-1 font-mono text-[11px] text-text-muted whitespace-nowrap">
                                    {log.created_at
                                        ? new Date(log.created_at).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })
                                        : '—'}
                                </td>
                                <td className="px-2 py-1">
                                    <p className="truncate font-semibold text-text-main" title={log.usuario_resuelto}>
                                        {log.usuario
                                            ? `${log.usuario.nombre ?? ''} ${log.usuario.apellidos ?? ''}`.trim() || log.usuario.email || '—'
                                            : 'Sistema'}
                                    </p>
                                </td>
                                <td className="px-2 py-1 truncate font-mono text-text-muted" title={log.modulo_resuelto}>
                                    {log.modulo_label ?? log.modulo_resuelto ?? '—'}
                                </td>
                                <td className="px-2 py-1">
                                    <AccionPill accion={log.accion} label={log.accion_resuelta} />
                                </td>
                                <td className="px-2 py-1 font-mono text-text-hint text-right">
                                    {log.entity_id_resuelto ?? '—'}
                                </td>
                                <td className="px-2 py-1 truncate text-text-muted font-mono" title={log.campo}>
                                    {log.campo_label ?? log.campo ?? '—'}
                                </td>
                                <td className="px-2 py-1">
                                    <TruncatedCell value={log.valor_anterior_resuelto} maxLen={45} />
                                </td>
                                <td className="px-2 py-1">
                                    <TruncatedCell value={log.valor_nuevo_resuelto} maxLen={45} />
                                </td>
                                <td className="px-2 py-1 truncate text-text-muted" title={log.contexto?.nombre}>
                                    {log.contexto?.nombre ?? '—'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <PaginationControls pagination={{ current_page: currentPage, last_page: lastPage, total }} onPageChange={(p) => aplicarFiltros({ page: p })} />
        </>
    );
}

// ─── Vista Moderno — timeline de actividad ─────────────────────────────────────

const ACCION_ICON_STYLE = {
    crear:          { dot: 'bg-emerald-500', ring: 'ring-emerald-100' },
    actualizar:     { dot: 'bg-blue-500',    ring: 'ring-blue-100' },
    eliminar:       { dot: 'bg-red-500',     ring: 'ring-red-100' },
    cambiar_estado: { dot: 'bg-amber-500',   ring: 'ring-amber-100' },
    importar:       { dot: 'bg-violet-500',  ring: 'ring-violet-100' },
    activar:        { dot: 'bg-teal-500',    ring: 'ring-teal-100' },
    desactivar:     { dot: 'bg-gray-400',    ring: 'ring-gray-100' },
};

function TimelineRow({ log }) {
    const iconStyle = ACCION_ICON_STYLE[log.accion] ?? { dot: 'bg-gray-400', ring: 'ring-gray-100' };
    const changes = log.cambios_resueltos ?? [];
    const userName = log.usuario
        ? `${log.usuario.nombre ?? ''} ${log.usuario.apellidos ?? ''}`.trim() || log.usuario.email || 'Usuario'
        : 'Sistema';
    const fecha = log.created_at
        ? new Date(log.created_at).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })
        : '—';

    return (
        <div className="flex gap-4">
            {/* Línea vertical + punto */}
            <div className="flex flex-col items-center">
                <div className={`mt-1 h-3 w-3 flex-shrink-0 rounded-full ring-4 ${iconStyle.dot} ${iconStyle.ring}`} />
                <div className="mt-1 flex-1 w-px bg-border" />
            </div>

            {/* Contenido */}
            <div className="min-w-0 flex-1 pb-5">
                <div className="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                    <span className="text-sm font-semibold text-text-main">{userName}</span>
                    <AccionBadge accion={log.accion} label={log.accion_resuelta} />
                    <span className="text-sm text-text-muted">{log.modulo_label ?? log.modulo_resuelto ?? log.tabla ?? '—'}</span>
                    {log.entity_id_resuelto && (
                        <span className="font-mono text-xs text-text-hint">#{log.entity_id_resuelto}</span>
                    )}
                    <span className="text-xs text-text-hint sm:ml-auto sm:whitespace-nowrap">{fecha}</span>
                </div>

                {/* Descripción / cambios */}
                {changes.length > 0 ? (
                    <div className="mt-2 rounded-lg border border-border bg-surface-2/60 px-3 py-2">
                        <div className="space-y-1">
                            {changes.slice(0, 4).map((ch) => (
                                <div key={ch.campo} className="flex items-baseline gap-2 text-xs">
                                    <span className="w-28 flex-shrink-0 truncate font-mono font-semibold text-text-muted" title={ch.campo}>{ch.campo_label ?? ch.campo}</span>
                                    <span className="flex-1 truncate text-text-hint line-through" title={String(ch.anterior ?? '')}>{String(ch.anterior ?? '—')}</span>
                                    <span className="text-text-hint">→</span>
                                    <span className="flex-1 truncate font-medium text-text-main" title={String(ch.nuevo ?? '')}>{String(ch.nuevo ?? '—')}</span>
                                </div>
                            ))}
                            {changes.length > 4 && (
                                <p className="text-[11px] text-text-hint">+{changes.length - 4} campos más</p>
                            )}
                        </div>
                    </div>
                ) : log.descripcion ? (
                    <p className="mt-1.5 text-xs text-text-muted leading-relaxed">{log.descripcion}</p>
                ) : null}

                {log.contexto?.nombre && (
                    <p className="mt-1.5 text-[11px] text-text-hint">Contexto: {log.contexto.nombre}</p>
                )}
            </div>
        </div>
    );
}

function ModernoView({ rows, currentPage, lastPage, total, aplicarFiltros }) {
    if (rows.length === 0) {
        return (
            <div className="flex min-h-[200px] items-center justify-center rounded-xl border border-border bg-surface text-sm text-text-hint">
                Sin registros para los filtros aplicados.
            </div>
        );
    }

    // Group by date
    const grouped = rows.reduce((acc, log) => {
        const day = log.created_at
            ? new Date(log.created_at).toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
            : 'Sin fecha';
        if (!acc[day]) acc[day] = [];
        acc[day].push(log);
        return acc;
    }, {});

    return (
        <>
            <div className="space-y-6">
                {Object.entries(grouped).map(([day, dayLogs]) => (
                    <section key={day}>
                        <h3 className="mb-4 text-xs font-bold uppercase tracking-widest text-text-hint capitalize">{day}</h3>
                        <div className="rounded-xl border border-border bg-surface px-5 py-4 shadow-sm">
                            {dayLogs.map((log) => (
                                <TimelineRow key={log.id_audit} log={log} />
                            ))}
                        </div>
                    </section>
                ))}
            </div>
            <PaginationControls pagination={{ current_page: currentPage, last_page: lastPage, total }} onPageChange={(p) => aplicarFiltros({ page: p })} />
        </>
    );
}

// ─── Page export ──────────────────────────────────────────────────────────────

export default function AuditLogIndex({
    logs,
    filtros,
    modulosFiltro = [],
    accionesFiltro = [],
    contextosFiltro = [],
    usuariosFiltro = [],
    canLimpiar = false,
}) {
    const { visualStyle } = useTheme();
    const isCieteExcel = visualStyle === 'ciete_excel';

    const [localFiltros, setLocalFiltros] = useState({ solo_datos: true, ...filtros });
    const [showLimpiar, setShowLimpiar] = useState(false);

    const rows = logs?.data ?? [];
    const currentPage = logs?.current_page ?? 1;
    const lastPage = logs?.last_page ?? 1;
    const total = logs?.total ?? rows.length;

    function aplicarFiltros(extra = {}) {
        const params = { ...localFiltros, ...extra };
        Object.keys(params).forEach((k) => {
            if (params[k] === '' || params[k] === null || params[k] === undefined) delete params[k];
        });
        // Ensure solo_datos is always sent so backend keeps it
        if (params.solo_datos === undefined) params.solo_datos = 'true';
        router.get(route('registro.actividad.index'), params, { preserveState: true, replace: true });
    }

    function exportar(formato) {
        const params = new URLSearchParams();
        Object.entries(localFiltros).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) params.append(k, String(v));
        });
        params.set('formato', formato);
        window.open(route('registro.actividad.exportar') + '?' + params.toString(), '_blank');
    }

    const flash = usePage().props.flash ?? {};

    return (
        <AuthenticatedLayout>
            <Head title="Registro de actividad operativa" />

            <div className={`ciete-page ${isCieteExcel ? 'ciete-page-full' : 'ciete-page-wide'}`}>
                <ContextualPageHeader
                    eyebrow="Operaciones"
                    title="Registro de actividad operativa"
                    description={`Registro de actividad operativa · ${total} registros`}
                />

                {flash.success && (
                    <div className="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}

                <FiltrosBar
                    localFiltros={localFiltros}
                    setLocalFiltros={setLocalFiltros}
                    aplicarFiltros={aplicarFiltros}
                    exportar={exportar}
                    canLimpiar={canLimpiar}
                    setShowLimpiar={setShowLimpiar}
                    modulosFiltro={modulosFiltro}
                    accionesFiltro={accionesFiltro}
                    contextosFiltro={contextosFiltro}
                    usuariosFiltro={usuariosFiltro}
                />

                {isCieteExcel ? (
                    <ExcelView
                        rows={rows}
                        currentPage={currentPage}
                        lastPage={lastPage}
                        total={total}
                        aplicarFiltros={aplicarFiltros}
                    />
                ) : (
                    <ModernoView
                        rows={rows}
                        currentPage={currentPage}
                        lastPage={lastPage}
                        total={total}
                        aplicarFiltros={aplicarFiltros}
                    />
                )}
            </div>

            {showLimpiar && (
                <LimpiarModal filtros={localFiltros} onClose={() => setShowLimpiar(false)} />
            )}
        </AuthenticatedLayout>
    );
}
