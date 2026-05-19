import { useState } from 'react';

/**
 * Normaliza los distintos formatos de meta de paginación que devuelve el backend.
 *
 * Soporta:
 *  - Plano (Inertia ResourceCollection): { current_page, last_page, total, per_page, from, to }
 *  - Anidado (llamadas API legacy):       { pagination: { current_page, last_page, total, … } }
 */
function normalizeMeta(meta) {
    if (!meta) return null;

    const src = meta.pagination ?? meta;

    const currentPage = Number(src.current_page ?? 1);
    const lastPage    = Number(src.last_page    ?? 1);

    if (!Number.isFinite(currentPage) || !Number.isFinite(lastPage) || lastPage < 1) return null;

    return {
        currentPage,
        lastPage,
        total:   src.total   != null ? Number(src.total)   : null,
        perPage: src.per_page != null ? Number(src.per_page) : null,
        from:    src.from    != null ? Number(src.from)    : null,
        to:      src.to      != null ? Number(src.to)      : null,
    };
}

/**
 * Controles de paginación reutilizables.
 *
 * Props:
 *  - pagination    {object}   Meta de paginación (plana o anidada).
 *  - onPageChange  {function} Callback (page: number) => void.
 *  - entityLabel   {string}   Etiqueta del recurso para "Mostrando X-Y de Z …". Default: 'registros'.
 */
export default function PaginationControls({ pagination, onPageChange, entityLabel = 'registros' }) {
    const [inputValue, setInputValue] = useState('');
    const [inputError, setInputError] = useState(false);

    const meta = normalizeMeta(pagination);

    if (!meta || meta.lastPage <= 1) return null;

    const { currentPage, lastPage, total, from, to } = meta;

    function navigate(page) {
        const target = Math.max(1, Math.min(lastPage, page));
        if (target !== currentPage) onPageChange(target);
    }

    function handleGoTo(e) {
        e.preventDefault();
        const val = parseInt(inputValue, 10);

        if (!Number.isInteger(val) || val < 1 || val > lastPage) {
            setInputError(true);
            setTimeout(() => setInputError(false), 1200);
            return;
        }

        setInputError(false);
        setInputValue('');
        navigate(val);
    }

    const infoText =
        from != null && to != null && total != null
            ? `Mostrando ${from}–${to} de ${total} ${entityLabel}`
            : total != null
                ? `${total} ${entityLabel} · Pág. ${currentPage} / ${lastPage}`
                : `Pág. ${currentPage} / ${lastPage}`;

    const btnBase =
        'rounded-md border border-border px-2.5 py-1.5 text-xs font-semibold text-text-main transition hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-40 select-none';

    return (
        <div className="mt-1 flex flex-col gap-2 text-xs text-text-hint sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            {/* Información de registros */}
            <span className="shrink-0">{infoText}</span>

            {/* Controles de navegación */}
            <div className="flex flex-wrap items-center gap-1.5">
                {/* Primera */}
                <button
                    type="button"
                    className={btnBase}
                    disabled={currentPage <= 1}
                    onClick={() => navigate(1)}
                    title="Primera página"
                >
                    «
                </button>

                {/* Anterior */}
                <button
                    type="button"
                    className={btnBase}
                    disabled={currentPage <= 1}
                    onClick={() => navigate(currentPage - 1)}
                >
                    Anterior
                </button>

                <span className="whitespace-nowrap px-1 font-semibold text-text-muted">
                    {currentPage} / {lastPage}
                </span>

                {/* Siguiente */}
                <button
                    type="button"
                    className={btnBase}
                    disabled={currentPage >= lastPage}
                    onClick={() => navigate(currentPage + 1)}
                >
                    Siguiente
                </button>

                {/* Última */}
                <button
                    type="button"
                    className={btnBase}
                    disabled={currentPage >= lastPage}
                    onClick={() => navigate(lastPage)}
                    title="Última página"
                >
                    »
                </button>

                {/* Ir a página */}
                <form onSubmit={handleGoTo} className="ml-2 flex items-center gap-1">
                    <input
                        type="number"
                        min={1}
                        max={lastPage}
                        value={inputValue}
                        onChange={(e) => { setInputValue(e.target.value); setInputError(false); }}
                        placeholder="Ir a…"
                        aria-label="Ir a página"
                        className={`w-16 rounded-md border ${inputError ? 'border-red-400' : 'border-border'} bg-surface px-2 py-1.5 text-center text-xs text-text-main transition focus:outline-none focus:ring-1 focus:ring-primary`}
                    />
                    <button type="submit" className={btnBase}>
                        Ir
                    </button>
                </form>
            </div>
        </div>
    );
}
