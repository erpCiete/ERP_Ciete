import ContextualPageHeader from '@/Components/ContextualPageHeader';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, Minus, Plus, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

function formatPrice(value) {
    const number = Number.parseFloat(value ?? '0');

    if (Number.isNaN(number)) {
        return value || '-';
    }

    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
}

function textMatches(fields, needle) {
    if (needle === '') {
        return true;
    }

    return fields.some((field) => String(field ?? '').toLowerCase().includes(needle));
}

function toneClass(tone = 'neutral') {
    if (tone === 'warning') {
        return 'border-amber-500/40 bg-amber-500/10 text-amber-100';
    }

    if (tone === 'success') {
        return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-100';
    }

    return 'border-border bg-surface-2 text-text-main';
}

function CompactBadge({ children, tone = 'neutral' }) {
    return (
        <span className={`inline-flex rounded border px-2 py-0.5 text-[11px] font-semibold ${toneClass(tone)}`}>
            {children}
        </span>
    );
}

function CompactLink({ href, children }) {
    return (
        <Link
            href={href}
            className="inline-flex items-center rounded border border-border px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-text-main transition hover:bg-surface-2"
        >
            {children}
        </Link>
    );
}

function isRealId(value) {
    return typeof value === 'number' && Number.isFinite(value);
}

function buildInitialExpandedKeys(contracts, selected) {
    const keys = new Set();

    if (!Array.isArray(contracts) || contracts.length === 0) {
        return [];
    }

    const selectedContract = contracts.find((contract) => contract.id === selected?.contrato_id) ?? contracts[0];
    const companyId = selectedContract?.empresa?.id ?? `sin-empresa-${selectedContract?.id ?? '0'}`;
    keys.add(`empresa-${companyId}`);

    if (selectedContract) {
        if (selectedContract.sociedades.length === 0) {
            keys.add(`sociedad-empty-${selectedContract.id}`);
        } else {
            selectedContract.sociedades.forEach((sociedad) => {
                keys.add(`sociedad-${companyId}-${sociedad.id}`);
            });
        }

        keys.add(`contrato-${selectedContract.id}`);
    }

    if (selected?.tarifario_id) {
        keys.add(`tarifario-${selected.tarifario_id}`);
    }

    return Array.from(keys);
}

export default function ContratosTarifas({ overview, contracts = [], selected, can = {}, supportsPredeterminado = false }) {
    const [search, setSearch] = useState('');
    const [activeFilter, setActiveFilter] = useState('all');
    const [alertsOnly, setAlertsOnly] = useState(false);
    const [predeterminadoOnly, setPredeterminadoOnly] = useState(false);
    const [withoutLinesOnly, setWithoutLinesOnly] = useState(false);
    const [expandedKeys, setExpandedKeys] = useState(() => buildInitialExpandedKeys(contracts, selected));

    useEffect(() => {
        setExpandedKeys((current) => {
            const next = new Set(current);

            buildInitialExpandedKeys(contracts, selected).forEach((key) => next.add(key));

            return Array.from(next);
        });
    }, [contracts, selected]);

    const selectedTarifarioId = selected?.tarifario_id ?? null;
    const selectedLineas = Array.isArray(selected?.lineas) ? selected.lineas : [];
    const searchNeedle = search.trim().toLowerCase();

    const isExpanded = (key) => expandedKeys.includes(key);

    const toggleExpanded = (key, options = {}) => {
        setExpandedKeys((current) => {
            const next = new Set(current);

            if (next.has(key)) {
                next.delete(key);
            } else {
                next.add(key);
            }

            return Array.from(next);
        });

        if (options.contractId && options.tarifarioId && options.tarifarioId !== selectedTarifarioId) {
            router.get(
                route('maestros.contratos-tarifas'),
                { contrato: options.contractId, tarifario: options.tarifarioId },
                { preserveScroll: true, preserveState: true },
            );
        }
    };

    const openContract = (contractId) => {
        router.get(
            route('maestros.contratos-tarifas'),
            { contrato: contractId },
            { preserveScroll: true, preserveState: true },
        );
    };

    const markAsDefault = (tarifarioId) => {
        router.put(route('maestros.tarifarios.set-default', tarifarioId), {}, {
            preserveScroll: true,
        });
    };

    const companiesMap = new Map();

    contracts.forEach((contract) => {
        const companyId = contract.empresa?.id ?? `sin-empresa-${contract.id}`;
        const companyKey = `empresa-${companyId}`;

        if (!companiesMap.has(companyKey)) {
            companiesMap.set(companyKey, {
                key: companyKey,
                id: companyId,
                nombre: contract.empresa?.nombre || 'Empresa sin nombre',
                cif: contract.empresa?.cif || '',
                activa: Boolean(contract.empresa?.activa),
                contracts: [],
            });
        }

        companiesMap.get(companyKey).contracts.push(contract);
    });

    const companies = Array.from(companiesMap.values())
        .map((company) => {
            const societiesMap = new Map();

            company.contracts.forEach((contract) => {
                if (!contract.sociedades.length) {
                    const emptyKey = `sociedad-empty-${contract.id}`;

                    if (!societiesMap.has(emptyKey)) {
                        societiesMap.set(emptyKey, {
                            key: emptyKey,
                            id: null,
                            nombre: 'Sin sociedad / CIF valida',
                            cif: '',
                            activa: false,
                            hasValidCif: false,
                            contracts: [],
                        });
                    }

                    societiesMap.get(emptyKey).contracts.push(contract);
                    return;
                }

                contract.sociedades.forEach((sociedad) => {
                    const societyKey = `sociedad-${company.id}-${sociedad.id}`;

                    if (!societiesMap.has(societyKey)) {
                        societiesMap.set(societyKey, {
                            key: societyKey,
                            id: sociedad.id,
                            nombre: sociedad.empresa?.nombre || 'Sociedad sin nombre',
                            cif: sociedad.empresa?.cif || '',
                            activa: Boolean(sociedad.activa && sociedad.empresa?.activa),
                            hasValidCif: Boolean(sociedad.has_valid_cif),
                            contracts: [],
                        });
                    }

                    societiesMap.get(societyKey).contracts.push(contract);
                });
            });

            company.societies = Array.from(societiesMap.values()).sort((left, right) => left.nombre.localeCompare(right.nombre));

            return company;
        })
        .sort((left, right) => left.nombre.localeCompare(right.nombre));

    const visibleCompanies = companies.filter((company) => {
        return company.contracts.some((contract) => {
            const contractHasAlerts = !contract.has_valid_sociedad
                || !contract.tarifario_predeterminado
                || contract.tarifarios.some((tarifario) => !tarifario.activo || tarifario.lineas_count === 0);
            const stateMatches = activeFilter === 'all'
                || (activeFilter === 'active' && (contract.activo || contract.tarifarios.some((tarifario) => tarifario.activo)))
                || (activeFilter === 'inactive' && (!contract.activo || contract.tarifarios.some((tarifario) => !tarifario.activo)));
            const alertMatches = !alertsOnly || contractHasAlerts;
            const predeterminadoMatches = !predeterminadoOnly || contract.tarifarios.some((tarifario) => tarifario.predeterminado);
            const withoutLinesMatches = !withoutLinesOnly || contract.tarifarios.some((tarifario) => tarifario.lineas_count === 0);
            const lineFields = contract.id === selected?.contrato_id
                ? selectedLineas.flatMap((linea) => [linea.codigo, linea.descripcion, linea.actuacion])
                : [];
            const searchMatches = textMatches([
                company.nombre,
                company.cif,
                contract.codigo,
                contract.nombre,
                ...contract.sociedades.flatMap((sociedad) => [sociedad.empresa?.nombre, sociedad.empresa?.cif]),
                ...contract.tarifarios.flatMap((tarifario) => [tarifario.nombre, tarifario.version]),
                ...lineFields,
            ], searchNeedle);

            return stateMatches && alertMatches && predeterminadoMatches && withoutLinesMatches && searchMatches;
        });
    });

    const rows = [];

    visibleCompanies.forEach((company) => {
        const companyAlertCount = company.contracts.filter((contract) => !contract.has_valid_sociedad).length;

        rows.push({
            key: company.key,
            level: 0,
            label: company.nombre,
            subtitle: company.cif ? `CIF ${company.cif}` : 'Sin CIF',
            type: 'Empresa / cliente',
            active: company.activa,
            predeterminado: '-',
            usage: `${company.contracts.length} contrato(s)`,
            alerts: companyAlertCount > 0 ? [`${companyAlertCount} sin sociedad valida`] : [],
            expandable: company.societies.length > 0,
            selected: false,
            actions: [
                hasRoute('clientes.index') ? { label: 'Ver', href: route('clientes.index') } : null,
                can.clientes?.edit && isRealId(company.id) && hasRoute('clientes.edit')
                    ? { label: 'Editar', href: route('clientes.edit', company.id) }
                    : null,
                can.sociedades?.create && hasRoute('maestros.sociedades.index')
                    ? { label: 'Nueva sociedad/CIF', href: route('maestros.sociedades.index', { empresa: company.id }) }
                    : null,
            ].filter(Boolean),
        });

        if (!isExpanded(company.key)) {
            return;
        }

        company.societies.forEach((society) => {
            rows.push({
                key: society.key,
                level: 1,
                label: society.nombre,
                subtitle: society.cif || 'CIF pendiente',
                type: 'Sociedad / CIF',
                active: society.activa,
                predeterminado: '-',
                usage: `${society.contracts.length} contrato(s)`,
                alerts: [
                    ...(!society.hasValidCif ? ['CIF pendiente'] : []),
                    ...(!society.activa ? ['Relacion inactiva'] : []),
                ],
                expandable: society.contracts.length > 0,
                selected: false,
                actions: [
                    hasRoute('maestros.sociedades.index')
                        ? { label: 'Ver', href: route('maestros.sociedades.index', { search: society.cif || society.nombre }) }
                        : null,
                    can.contratos?.create && hasRoute('maestros.contratos.create')
                        ? { label: 'Nuevo contrato', href: route('maestros.contratos.create', { empresa: company.id, sociedad: society.id }) }
                        : null,
                ].filter(Boolean),
            });

            if (!isExpanded(society.key)) {
                return;
            }

            society.contracts.forEach((contract) => {
                const contractRowKey = `${society.key}-contrato-${contract.id}`;

                rows.push({
                    key: contractRowKey,
                    treeKey: `contrato-${contract.id}`,
                    level: 2,
                    label: contract.codigo,
                    subtitle: contract.nombre || 'Contrato sin nombre',
                    type: 'Contrato',
                    active: contract.activo,
                    predeterminado: '-',
                    usage: `${contract.trabajos_count} trabajos · ${contract.facturas_count} facturas`,
                    alerts: [
                        ...(!contract.has_valid_sociedad ? ['Sin sociedad valida'] : []),
                        ...(contract.tarifarios_count === 0 ? ['Sin tarifarios'] : []),
                        ...(!contract.tarifario_predeterminado ? ['Sin predeterminado'] : []),
                    ],
                    expandable: contract.tarifarios.length > 0,
                    selected: contract.id === selected?.contrato_id,
                    onSelect: () => openContract(contract.id),
                    actions: [
                        hasRoute('maestros.contratos.index')
                            ? { label: 'Ver', href: route('maestros.contratos.index', { search: contract.codigo }) }
                            : null,
                        can.contratos?.edit && hasRoute('maestros.contratos.edit')
                            ? { label: 'Editar', href: route('maestros.contratos.edit', contract.id) }
                            : null,
                        can.tarifarios?.create && hasRoute('maestros.tarifarios.create')
                            ? { label: 'Nuevo tarifario', href: route('maestros.tarifarios.create', { contrato: contract.id }) }
                            : null,
                        hasRoute('maestros.sociedades.index')
                            ? { label: 'Gestionar sociedades', href: route('maestros.sociedades.index', { search: contract.codigo }) }
                            : null,
                    ].filter(Boolean),
                });

                if (!isExpanded(`contrato-${contract.id}`)) {
                    return;
                }

                contract.tarifarios.forEach((tarifario) => {
                    const tarifarioTreeKey = `tarifario-${tarifario.id}`;
                    const lineas = selectedTarifarioId === tarifario.id ? selectedLineas : [];

                    rows.push({
                        key: `${contractRowKey}-tarifario-${tarifario.id}`,
                        treeKey: tarifarioTreeKey,
                        level: 3,
                        label: tarifario.nombre,
                        subtitle: tarifario.version ? `Version ${tarifario.version}` : 'Sin version',
                        type: 'Tarifario',
                        active: tarifario.activo,
                        predeterminado: tarifario.predeterminado
                            ? 'Predeterminado'
                            : can.tarifarios?.edit && tarifario.activo
                                ? 'action:set-default'
                                : '-',
                        usage: `${tarifario.trabajos_count} trabajos · ${tarifario.pedidos_count} pedidos`,
                        alerts: [
                            ...(!tarifario.activo ? ['Inactivo'] : []),
                            ...(tarifario.lineas_count === 0 ? ['Sin lineas'] : []),
                        ],
                        expandable: tarifario.lineas_count > 0 || selectedTarifarioId === tarifario.id,
                        selected: selectedTarifarioId === tarifario.id,
                        actions: [
                            hasRoute('maestros.tarifarios.index')
                                ? { label: 'Ver', href: route('maestros.tarifarios.index', { search: tarifario.nombre }) }
                                : null,
                            can.tarifarios?.edit && hasRoute('maestros.tarifarios.edit')
                                ? { label: 'Editar', href: route('maestros.tarifarios.edit', tarifario.id) }
                                : null,
                            can.lineas?.create && hasRoute('maestros.tarifario-lineas.create')
                                ? { label: 'Nueva linea', href: route('maestros.tarifario-lineas.create', { tarifario: tarifario.id }) }
                                : null,
                            hasRoute('maestros.tarifario-lineas.index')
                                ? { label: 'Ver lineas', href: route('maestros.tarifario-lineas.index', { tarifario: tarifario.id }) }
                                : null,
                        ].filter(Boolean),
                        toggleOptions: { contractId: contract.id, tarifarioId: tarifario.id },
                    });

                    if (!isExpanded(tarifarioTreeKey)) {
                        return;
                    }

                    if (selectedTarifarioId !== tarifario.id) {
                        rows.push({
                            key: `${contractRowKey}-tarifario-${tarifario.id}-loading`,
                            level: 4,
                            label: 'Cargar lineas',
                            subtitle: `Selecciona este tarifario para leer hasta ${selected?.lineas_limit ?? 200} lineas`,
                            type: 'Linea de tarifa',
                            active: true,
                            predeterminado: '-',
                            usage: `${tarifario.lineas_count} lineas`,
                            alerts: [],
                            expandable: false,
                            selected: false,
                            actions: [
                                { label: 'Ver', href: route('maestros.contratos-tarifas', { contrato: contract.id, tarifario: tarifario.id }) },
                            ],
                        });
                        return;
                    }

                    if (lineas.length === 0) {
                        rows.push({
                            key: `${contractRowKey}-tarifario-${tarifario.id}-empty`,
                            level: 4,
                            label: 'Sin lineas activas cargadas',
                            subtitle: '',
                            type: 'Linea de tarifa',
                            active: false,
                            predeterminado: '-',
                            usage: `${tarifario.lineas_count} lineas`,
                            alerts: ['Sin lineas'],
                            expandable: false,
                            selected: false,
                            actions: [],
                        });
                    }

                    lineas.forEach((linea) => {
                        rows.push({
                            key: `${contractRowKey}-tarifario-${tarifario.id}-linea-${linea.id}`,
                            level: 4,
                            label: linea.codigo || 'Linea',
                            subtitle: linea.descripcion || linea.actuacion || '-',
                            type: 'Linea de tarifa',
                            active: linea.activo,
                            predeterminado: '-',
                            usage: `${formatPrice(linea.precio)} €${linea.unidad ? ` · ${linea.unidad}` : ''}`,
                            alerts: linea.activo ? [] : ['Inactiva'],
                            expandable: false,
                            selected: false,
                            actions: [
                                can.lineas?.edit && hasRoute('maestros.tarifario-lineas.edit')
                                    ? { label: 'Editar', href: route('maestros.tarifario-lineas.edit', linea.id) }
                                    : null,
                            ].filter(Boolean),
                        });
                    });
                });
            });
        });
    });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-(--ciete-slate)">Contratos y tarifas</h2>}>
            <Head title="Contratos y tarifas" />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Maestros"
                    title="Contratos y tarifas"
                    description="Empresa -> Sociedades/CIF -> Contratos -> Tarifarios -> Lineas"
                    backHref={route('maestros.index')}
                />

                {overview?.context?.is_all && (
                    <div className="mb-4 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
                        Selecciona un contexto real para revisar el arbol operativo.
                    </div>
                )}

                <section className="mb-4 overflow-hidden rounded-lg border border-border bg-surface">
                    <div className="grid gap-3 px-3 py-3 lg:grid-cols-[minmax(0,1fr)_auto]">
                        <div className="min-w-0 text-sm text-text-muted">
                            {overview?.context?.nombre || 'Sin contexto'} · {overview?.counts?.contratos ?? 0} contratos · {overview?.counts?.tarifarios ?? 0} tarifarios · {overview?.counts?.lineas ?? 0} lineas
                        </div>
                        <div className="flex flex-wrap items-center justify-end gap-2">
                            <CompactBadge>Lectura agregada</CompactBadge>
                            {Number(overview?.alerts?.contratos_sin_sociedad_valida ?? 0) > 0 && (
                                <CompactBadge tone="warning">
                                    {overview.alerts.contratos_sin_sociedad_valida} sin sociedad valida
                                </CompactBadge>
                            )}
                        </div>
                    </div>

                    <div className="border-t border-border px-3 py-3">
                        <div className="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto_auto_auto_auto]">
                            <label className="relative block">
                                <Search className="pointer-events-none absolute left-3 top-2.5 size-4 text-text-hint" />
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(event) => setSearch(event.target.value)}
                                        placeholder="Buscar empresa, sociedad, contrato, tarifario o linea"
                                        className="w-full rounded border border-border bg-surface px-9 py-2 text-sm text-text-main placeholder:text-text-hint"
                                    />
                                </label>

                            <select
                                value={activeFilter}
                                onChange={(event) => setActiveFilter(event.target.value)}
                                className="rounded border border-border bg-surface px-3 py-2 text-sm text-text-main"
                            >
                                <option value="all">Todos</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Con inactivos</option>
                            </select>

                            <label className="flex items-center gap-2 rounded border border-border px-3 py-2 text-sm text-text-main">
                                <input type="checkbox" checked={alertsOnly} onChange={(event) => setAlertsOnly(event.target.checked)} />
                                <span>Con alertas</span>
                            </label>

                            <label className="flex items-center gap-2 rounded border border-border px-3 py-2 text-sm text-text-main">
                                <input type="checkbox" checked={predeterminadoOnly} onChange={(event) => setPredeterminadoOnly(event.target.checked)} />
                                <span>Predeterminado</span>
                            </label>

                            <label className="flex items-center gap-2 rounded border border-border px-3 py-2 text-sm text-text-main">
                                <input type="checkbox" checked={withoutLinesOnly} onChange={(event) => setWithoutLinesOnly(event.target.checked)} />
                                <span>Sin lineas</span>
                            </label>
                        </div>
                    </div>
                </section>

                {Number(overview?.alerts?.contratos_sin_sociedad_valida ?? 0) > 0 && (
                    <section className="mb-4 flex items-center gap-2 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
                        <AlertTriangle className="size-4" />
                        <span>{overview.alerts.contratos_sin_sociedad_valida} contrato(s) sin sociedad/CIF valida.</span>
                    </section>
                )}

                <section className="overflow-hidden rounded-lg border border-border bg-surface">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-left text-sm">
                            <thead className="bg-surface-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-text-hint">
                                <tr>
                                    <th className="px-3 py-2">Nombre / Codigo</th>
                                    <th className="px-3 py-2">Tipo</th>
                                    <th className="px-3 py-2">Estado</th>
                                    <th className="px-3 py-2">Predeterminado</th>
                                    <th className="px-3 py-2">Uso</th>
                                    <th className="px-3 py-2">Alertas</th>
                                    <th className="px-3 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-3 py-6 text-sm text-text-muted">
                                            No hay filas para los filtros seleccionados.
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((row) => {
                                        const treeKey = row.treeKey || row.key;
                                        const expanded = row.expandable ? isExpanded(treeKey) : false;

                                        return (
                                            <tr key={row.key} className={`border-t border-border ${row.selected ? 'bg-red-500/10' : 'bg-surface'}`}>
                                                <td className="px-3 py-2">
                                                    <div className="flex items-start gap-2" style={{ paddingLeft: `${row.level * 20}px` }}>
                                                        {row.expandable ? (
                                                            <button
                                                                type="button"
                                                                onClick={() => toggleExpanded(treeKey, row.toggleOptions)}
                                                                className="mt-0.5 inline-flex size-5 shrink-0 items-center justify-center rounded border border-border text-text-main transition hover:bg-surface-2"
                                                            >
                                                                {expanded ? <Minus className="size-3" /> : <Plus className="size-3" />}
                                                            </button>
                                                        ) : (
                                                            <span className="inline-flex size-5 shrink-0 items-center justify-center text-text-hint">·</span>
                                                        )}

                                                        <div className="min-w-0">
                                                            {row.onSelect ? (
                                                                <button
                                                                    type="button"
                                                                    onClick={row.onSelect}
                                                                    className="ciete-dark-table-accent truncate text-left font-medium hover:underline"
                                                                >
                                                                    {row.label}
                                                                </button>
                                                            ) : (
                                                                <div className="truncate font-medium text-text-main">{row.label}</div>
                                                            )}
                                                            {row.subtitle && <div className="truncate text-xs text-text-muted">{row.subtitle}</div>}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-3 py-2 text-xs text-text-muted">{row.type}</td>
                                                <td className="px-3 py-2">
                                                    <CompactBadge tone={row.active ? 'success' : 'neutral'}>
                                                        {row.active ? 'Activo' : 'Inactivo'}
                                                    </CompactBadge>
                                                </td>
                                                <td className="px-3 py-2 text-xs text-text-main">
                                                    {row.predeterminado === 'action:set-default' ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                if (row.toggleOptions?.tarifarioId) {
                                                                    markAsDefault(row.toggleOptions.tarifarioId);
                                                                }
                                                            }}
                                                            disabled={!supportsPredeterminado}
                                                            title={supportsPredeterminado ? 'Marcar como predeterminado' : 'No disponible en este entorno'}
                                                            className="rounded border border-border px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-text-muted transition hover:bg-surface-2 hover:text-text-main disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            Marcar
                                                        </button>
                                                    ) : (
                                                        row.predeterminado
                                                    )}
                                                </td>
                                                <td className="px-3 py-2 text-xs text-text-muted">{row.usage}</td>
                                                <td className="px-3 py-2">
                                                    <div className="flex flex-wrap gap-1">
                                                        {row.alerts.length === 0 ? (
                                                            <span className="text-xs text-text-hint">-</span>
                                                        ) : (
                                                            row.alerts.map((alert) => (
                                                                <CompactBadge key={`${row.key}-${alert}`} tone="warning">
                                                                    {alert}
                                                                </CompactBadge>
                                                            ))
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-3 py-2">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        {row.actions.map((action) => (
                                                            <CompactLink key={`${row.key}-${action.label}`} href={action.href}>
                                                                {action.label}
                                                            </CompactLink>
                                                        ))}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="border-t border-border px-3 py-2 text-xs text-text-muted">
                        Las pantallas separadas de contratos, sociedades, tarifarios y lineas siguen activas como gestion tecnica.
                    </div>
                </section>

                {selected?.lineas_truncated && (
                    <div className="mt-4 rounded border border-border bg-surface px-3 py-2 text-xs text-text-muted">
                        Lectura limitada a {selected.lineas_limit} lineas del tarifario seleccionado. Para revision completa usa la pantalla separada de lineas.
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
