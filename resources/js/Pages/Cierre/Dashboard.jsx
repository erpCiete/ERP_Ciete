import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const FLOW_STEPS = [
    { id: 'encargo', label: 'Encargo' },
    { id: 'pedido', label: 'Pedido' },
    { id: 'ejecucion', label: 'En curso' },
    { id: 'terminado', label: 'Terminado' },
    { id: 'revision_cierre', label: 'Revisión de cierre' },
    { id: 'cerrado', label: 'Cerrado' },
];

const CLOSURE_STATUS_LABELS = {
    pendiente: 'Pendiente de revisión',
    listo: 'Listo para cerrar',
    bloqueado: 'Bloqueado',
    cerrado: 'Cerrado',
};

const LEGALIZATION_LABELS = {
    pendiente: 'Pendiente',
    en_revision: 'En revisión',
    completa: 'Completa',
    no_aplica: 'No aplica',
};

const INITIAL_WORKS = [
    {
        id: 1,
        cliente: 'Repsol',
        estacion: 'Repsol M-50 Norte',
        numeroAviso: 'AV-24031',
        numeroPedido: 'PED-9012',
        tipoTrabajo: 'Sustitución surtidor',
        responsable: 'Diego Ramos',
        fechaEncargo: '2026-03-10',
        fechaFinReal: '2026-03-24',
        importePedido: 11800,
        importeTrabajo: 11720,
        legalizacionEstado: 'completa',
        legalizacionObservaciones: 'Documentación validada por legalizaciones.',
        legalizacionCritica: false,
        incidenciaBloqueante: false,
        incidencias: ['Revisión documental completada'],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: true,
        revisionEconomicaAprobada: true,
        requiereContratoTarifario: true,
        contratoTarifarioValidado: true,
        faseActual: 'revision_cierre',
        revisionCierreMarcada: true,
        cerrado: false,
        trazabilidad: {
            marcadoTerminadoPor: 'Lucía Esteban',
            fechaMarcadoTerminado: '2026-03-24 10:18',
            fechaFinInformadaPor: 'Lucía Esteban',
            fechaFinInformadaAt: '2026-03-24 10:20',
            importeActualizadoPor: 'Diego Ramos',
            importeActualizadoAt: '2026-03-24 11:05',
            cerradoPor: null,
            fechaCierre: null,
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
    {
        id: 2,
        cliente: 'Cepsa',
        estacion: 'Cepsa Arcos',
        numeroAviso: 'AV-24045',
        numeroPedido: 'PED-9040',
        tipoTrabajo: 'Actualización cuadro eléctrico',
        responsable: 'César Martín',
        fechaEncargo: '2026-03-12',
        fechaFinReal: null,
        importePedido: 6400,
        importeTrabajo: 6120,
        legalizacionEstado: 'en_revision',
        legalizacionObservaciones: 'A la espera de firma de instalador autorizado.',
        legalizacionCritica: true,
        incidenciaBloqueante: true,
        incidencias: ['Trabajo terminado sin fecha real de terminación'],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: false,
        revisionEconomicaAprobada: true,
        requiereContratoTarifario: false,
        contratoTarifarioValidado: true,
        faseActual: 'terminado',
        revisionCierreMarcada: false,
        cerrado: false,
        trazabilidad: {
            marcadoTerminadoPor: 'César Martín',
            fechaMarcadoTerminado: '2026-03-25 16:44',
            fechaFinInformadaPor: null,
            fechaFinInformadaAt: null,
            importeActualizadoPor: 'César Martín',
            importeActualizadoAt: '2026-03-25 16:49',
            cerradoPor: null,
            fechaCierre: null,
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
    {
        id: 3,
        cliente: 'Repsol',
        estacion: 'Repsol Logroño Este',
        numeroAviso: 'AV-24051',
        numeroPedido: 'PED-9055',
        tipoTrabajo: 'Reforma zona lavado',
        responsable: 'Marta Aguilar',
        fechaEncargo: '2026-03-15',
        fechaFinReal: '2026-03-26',
        importePedido: 9800,
        importeTrabajo: 11290,
        legalizacionEstado: 'pendiente',
        legalizacionObservaciones: 'Pendiente de registro ante organismo autonómico.',
        legalizacionCritica: true,
        incidenciaBloqueante: true,
        incidencias: ['Importe de trabajo supera pedido sin validación económica'],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: true,
        revisionEconomicaAprobada: false,
        requiereContratoTarifario: true,
        contratoTarifarioValidado: true,
        faseActual: 'revision_cierre',
        revisionCierreMarcada: false,
        cerrado: false,
        trazabilidad: {
            marcadoTerminadoPor: 'Marta Aguilar',
            fechaMarcadoTerminado: '2026-03-26 18:11',
            fechaFinInformadaPor: 'Marta Aguilar',
            fechaFinInformadaAt: '2026-03-26 18:12',
            importeActualizadoPor: 'Marta Aguilar',
            importeActualizadoAt: '2026-03-26 18:13',
            cerradoPor: null,
            fechaCierre: null,
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
    {
        id: 4,
        cliente: 'Cepsa',
        estacion: 'Cepsa Seseña',
        numeroAviso: 'AV-24022',
        numeroPedido: 'PED-8991',
        tipoTrabajo: 'Mantenimiento preventivo',
        responsable: 'Álvaro Mena',
        fechaEncargo: '2026-03-05',
        fechaFinReal: '2026-03-18',
        importePedido: 5200,
        importeTrabajo: 5200,
        legalizacionEstado: 'no_aplica',
        legalizacionObservaciones: 'Mantenimiento sin impacto legalizatorio.',
        legalizacionCritica: false,
        incidenciaBloqueante: false,
        incidencias: [],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: true,
        revisionEconomicaAprobada: true,
        requiereContratoTarifario: true,
        contratoTarifarioValidado: true,
        faseActual: 'revision_cierre',
        revisionCierreMarcada: false,
        cerrado: false,
        trazabilidad: {
            marcadoTerminadoPor: 'Álvaro Mena',
            fechaMarcadoTerminado: '2026-03-18 13:05',
            fechaFinInformadaPor: 'Álvaro Mena',
            fechaFinInformadaAt: '2026-03-18 13:07',
            importeActualizadoPor: 'Álvaro Mena',
            importeActualizadoAt: '2026-03-18 13:08',
            cerradoPor: null,
            fechaCierre: null,
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
    {
        id: 5,
        cliente: 'Repsol',
        estacion: 'Repsol Toledo Sur',
        numeroAviso: 'AV-23998',
        numeroPedido: 'PED-8930',
        tipoTrabajo: 'Adecuación tanque',
        responsable: 'César Martín',
        fechaEncargo: '2026-02-28',
        fechaFinReal: '2026-03-20',
        importePedido: 15100,
        importeTrabajo: 14980,
        legalizacionEstado: 'completa',
        legalizacionObservaciones: 'Expediente archivado sin salvedades.',
        legalizacionCritica: false,
        incidenciaBloqueante: false,
        incidencias: [],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: true,
        revisionEconomicaAprobada: true,
        requiereContratoTarifario: true,
        contratoTarifarioValidado: true,
        faseActual: 'cerrado',
        revisionCierreMarcada: true,
        cerrado: true,
        trazabilidad: {
            marcadoTerminadoPor: 'César Martín',
            fechaMarcadoTerminado: '2026-03-20 15:21',
            fechaFinInformadaPor: 'César Martín',
            fechaFinInformadaAt: '2026-03-20 15:23',
            importeActualizadoPor: 'César Martín',
            importeActualizadoAt: '2026-03-20 15:25',
            cerradoPor: 'César Martín',
            fechaCierre: '2026-03-27 09:10',
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
    {
        id: 6,
        cliente: 'Cepsa',
        estacion: 'Cepsa Albacete Centro',
        numeroAviso: 'AV-24066',
        numeroPedido: 'PED-9078',
        tipoTrabajo: 'Actualización PCI',
        responsable: 'Nora Vidal',
        fechaEncargo: '2026-03-18',
        fechaFinReal: '2026-03-26',
        importePedido: 8600,
        importeTrabajo: 8600,
        legalizacionEstado: 'en_revision',
        legalizacionObservaciones: 'Pendiente de visto bueno interno de calidad.',
        legalizacionCritica: false,
        incidenciaBloqueante: false,
        incidencias: ['Revisión económica pendiente de firma de responsable'],
        terminadoConfirmado: true,
        camposObligatoriosCompletos: true,
        revisionEconomicaAprobada: true,
        requiereContratoTarifario: true,
        contratoTarifarioValidado: true,
        faseActual: 'revision_cierre',
        revisionCierreMarcada: false,
        cerrado: false,
        trazabilidad: {
            marcadoTerminadoPor: 'Nora Vidal',
            fechaMarcadoTerminado: '2026-03-26 17:32',
            fechaFinInformadaPor: 'Nora Vidal',
            fechaFinInformadaAt: '2026-03-26 17:35',
            importeActualizadoPor: 'Nora Vidal',
            importeActualizadoAt: '2026-03-26 17:40',
            cerradoPor: null,
            fechaCierre: null,
            reabiertoPor: null,
            fechaReapertura: null,
            reaperturaMotivo: null,
        },
    },
];

const moneyFormatter = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 2,
});

const dateFormatter = new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

const statusOrder = {
    pendiente: 0,
    listo: 1,
    bloqueado: 2,
    cerrado: 3,
};

function normalizeText(value) {
    return String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function formatDate(value) {
    if (!value) return '-';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '-';

    return dateFormatter.format(date);
}

function buildValidationItems(work) {
    const exceedsOrderAmount = work.importeTrabajo > work.importePedido;

    return [
        {
            id: 'finishedConfirmed',
            label: 'Trabajo terminado confirmado',
            ok: work.terminadoConfirmado,
            blocking: true,
        },
        {
            id: 'endDate',
            label: 'Fecha real de terminación informada',
            ok: Boolean(work.fechaFinReal),
            blocking: true,
        },
        {
            id: 'validOrder',
            label: 'Pedido válido',
            ok: Boolean(work.numeroPedido),
            blocking: true,
        },
        {
            id: 'amountReviewed',
            label: 'Importe revisado',
            ok: !exceedsOrderAmount || work.revisionEconomicaAprobada,
            blocking: true,
            note: exceedsOrderAmount && !work.revisionEconomicaAprobada
                ? 'El importe del trabajo supera el pedido y requiere revisión económica.'
                : '',
        },
        {
            id: 'legalizationChecked',
            label: 'Legalización revisada',
            ok:
                work.legalizacionEstado === 'completa' ||
                work.legalizacionEstado === 'no_aplica' ||
                (work.legalizacionEstado === 'en_revision' && !work.legalizacionCritica),
            blocking: true,
        },
        {
            id: 'mandatoryFields',
            label: 'Campos obligatorios completos',
            ok: work.camposObligatoriosCompletos,
            blocking: true,
        },
        {
            id: 'noBlockingIncidents',
            label: 'Sin incidencias bloqueantes',
            ok: !work.incidenciaBloqueante,
            blocking: true,
        },
        {
            id: 'contractTariff',
            label: 'Contrato/tarifario validado',
            ok: !work.requiereContratoTarifario || work.contratoTarifarioValidado,
            blocking: true,
        },
        {
            id: 'flowReady',
            label: 'Flujo preparado para cierre',
            ok: ['revision_cierre', 'cerrado'].includes(work.faseActual),
            blocking: true,
        },
        {
            id: 'readyToClose',
            label: 'Listo para cierre',
            ok: work.revisionCierreMarcada || work.cerrado,
            blocking: false,
        },
    ];
}

function resolveClosureStatus(work) {
    if (work.cerrado) return 'cerrado';

    const checks = buildValidationItems(work);
    const hasBlockingChecks = checks.some((item) => item.blocking && !item.ok);

    if (hasBlockingChecks) return 'bloqueado';
    if (!work.revisionCierreMarcada) return 'pendiente';

    return 'listo';
}

function closureStatusBadgeClass(status) {
    const map = {
        pendiente: 'bg-state-pending-bg text-state-pending-text',
        listo: 'bg-state-done-bg text-state-done-text',
        bloqueado: 'bg-state-blocked-bg text-state-blocked-text',
        cerrado: 'bg-accent/10 text-accent',
    };

    return map[status] ?? 'bg-surface-2 text-text-muted';
}

function legalizationBadgeClass(status) {
    const map = {
        pendiente: 'bg-state-pending-bg text-state-pending-text',
        en_revision: 'bg-accent/10 text-accent',
        completa: 'bg-state-done-bg text-state-done-text',
        no_aplica: 'bg-surface-2 text-text-muted',
    };

    return map[status] ?? 'bg-surface-2 text-text-muted';
}

function closureActionLabel(status) {
    if (status === 'listo') return 'Cerrar';
    if (status === 'bloqueado') return 'Revisar';
    if (status === 'cerrado') return 'Reabrir';

    return 'Validar';
}

function formatCsvValue(value) {
    const text = String(value ?? '');
    return `"${text.replace(/"/g, '""')}"`;
}

export default function ClosureDashboard() {
    const user = usePage().props.auth.user;
    const actorName =
        user?.nombre_usuario ||
        [user?.nombre, user?.apellidos].filter(Boolean).join(' ') ||
        'Usuario cierre';

    const [works, setWorks] = useState(INITIAL_WORKS);
    const [contextFilter, setContextFilter] = useState('todos');
    const [statusFilter, setStatusFilter] = useState('todos');
    const [search, setSearch] = useState('');
    const [selectedIds, setSelectedIds] = useState([]);
    const [activeWorkId, setActiveWorkId] = useState(null);
    const [reopenReason, setReopenReason] = useState('');
    const [exportFormat, setExportFormat] = useState('csv');

    const scopedWorks = useMemo(() => {
        const normalizedSearch = normalizeText(search);

        return works
            .filter((work) => (contextFilter === 'todos' ? true : work.cliente.toLowerCase() === contextFilter))
            .filter((work) => {
                if (!normalizedSearch) return true;

                const haystack = normalizeText([
                    work.estacion,
                    work.numeroAviso,
                    work.numeroPedido,
                    work.responsable,
                ].join(' '));

                return haystack.includes(normalizedSearch);
            })
            .sort((a, b) => statusOrder[resolveClosureStatus(a)] - statusOrder[resolveClosureStatus(b)]);
    }, [works, contextFilter, search]);

    const visibleWorks = useMemo(() => {
        if (statusFilter === 'todos') return scopedWorks;
        return scopedWorks.filter((work) => resolveClosureStatus(work) === statusFilter);
    }, [scopedWorks, statusFilter]);

    const selectedWorks = useMemo(
        () => works.filter((work) => selectedIds.includes(work.id)),
        [works, selectedIds],
    );

    const activeWork = useMemo(
        () => works.find((work) => work.id === activeWorkId) ?? null,
        [works, activeWorkId],
    );

    const metrics = useMemo(() => {
        const statuses = scopedWorks.map(resolveClosureStatus);
        const today = dateFormatter.format(new Date());

        return {
            pendientes: statuses.filter((status) => status === 'pendiente').length,
            listos: statuses.filter((status) => status === 'listo').length,
            bloqueados: statuses.filter((status) => status === 'bloqueado').length,
            sinFechaFin: scopedWorks.filter((work) => !work.fechaFinReal).length,
            legalizacionesPendientes: scopedWorks.filter((work) => ['pendiente', 'en_revision'].includes(work.legalizacionEstado)).length,
            cerradosHoy: scopedWorks.filter((work) => {
                if (!work.cerrado || !work.trazabilidad?.fechaCierre) return false;
                return formatDate(work.trazabilidad.fechaCierre) === today;
            }).length,
        };
    }, [scopedWorks]);

    const closureIncidents = useMemo(() => {
        const items = [
            {
                id: 'no-end-date',
                label: 'Trabajos terminados sin fecha fin',
                count: scopedWorks.filter((work) => work.terminadoConfirmado && !work.fechaFinReal).length,
            },
            {
                id: 'budget-overrun',
                label: 'Trabajos con pedido insuficiente',
                count: scopedWorks.filter((work) => work.importeTrabajo > work.importePedido && !work.revisionEconomicaAprobada).length,
            },
            {
                id: 'legalization-pending',
                label: 'Legalizaciones pendientes críticas',
                count: scopedWorks.filter((work) => ['pendiente', 'en_revision'].includes(work.legalizacionEstado) && work.legalizacionCritica).length,
            },
            {
                id: 'incomplete-data',
                label: 'Datos obligatorios incompletos',
                count: scopedWorks.filter((work) => !work.camposObligatoriosCompletos).length,
            },
            {
                id: 'economic-review',
                label: 'Revisión económica pendiente',
                count: scopedWorks.filter((work) => work.importeTrabajo > work.importePedido && !work.revisionEconomicaAprobada).length,
            },
        ];

        return items.filter((item) => item.count > 0);
    }, [scopedWorks]);

    const allVisibleSelected =
        visibleWorks.length > 0 && visibleWorks.every((work) => selectedIds.includes(work.id));

    const canMassClose =
        selectedWorks.length > 0 &&
        selectedWorks.every((work) => resolveClosureStatus(work) === 'listo');

    const updateWork = (id, updater) => {
        setWorks((current) =>
            current.map((work) => (work.id === id ? updater(work) : work)),
        );
    };

    const closeWork = (id) => {
        updateWork(id, (work) => {
            if (resolveClosureStatus(work) !== 'listo') return work;

            return {
                ...work,
                cerrado: true,
                revisionCierreMarcada: true,
                faseActual: 'cerrado',
                trazabilidad: {
                    ...work.trazabilidad,
                    cerradoPor: actorName,
                    fechaCierre: new Date().toISOString(),
                },
            };
        });
    };

    const reopenWork = (id, reason) => {
        updateWork(id, (work) => {
            if (!work.cerrado) return work;

            return {
                ...work,
                cerrado: false,
                revisionCierreMarcada: false,
                faseActual: 'revision_cierre',
                trazabilidad: {
                    ...work.trazabilidad,
                    reabiertoPor: actorName,
                    fechaReapertura: new Date().toISOString(),
                    reaperturaMotivo: reason || 'Reapertura solicitada desde panel de cierre.',
                },
            };
        });
    };

    const handleMainAction = (work) => {
        const status = resolveClosureStatus(work);

        if (status === 'listo') {
            closeWork(work.id);
            return;
        }

        setActiveWorkId(work.id);
        if (status === 'cerrado') {
            setReopenReason('Reapertura por revisión de cierre.');
        }
    };

    const handleSelectRow = (workId) => {
        setSelectedIds((current) =>
            current.includes(workId)
                ? current.filter((id) => id !== workId)
                : [...current, workId],
        );
    };

    const handleSelectAllVisible = () => {
        if (allVisibleSelected) {
            setSelectedIds((current) =>
                current.filter((id) => !visibleWorks.some((work) => work.id === id)),
            );
            return;
        }

        setSelectedIds((current) => {
            const next = new Set(current);
            visibleWorks.forEach((work) => next.add(work.id));
            return [...next];
        });
    };

    const handleMassMarkReviewed = () => {
        setWorks((current) =>
            current.map((work) =>
                selectedIds.includes(work.id)
                    ? {
                        ...work,
                        revisionCierreMarcada: true,
                    }
                    : work,
            ),
        );
    };

    const handleMassClose = () => {
        setWorks((current) =>
            current.map((work) => {
                if (!selectedIds.includes(work.id)) return work;
                if (resolveClosureStatus(work) !== 'listo') return work;

                return {
                    ...work,
                    cerrado: true,
                    revisionCierreMarcada: true,
                    faseActual: 'cerrado',
                    trazabilidad: {
                        ...work.trazabilidad,
                        cerradoPor: actorName,
                        fechaCierre: new Date().toISOString(),
                    },
                };
            }),
        );
    };

    const buildExportRows = (status) =>
        scopedWorks.filter((work) => resolveClosureStatus(work) === status);

    const handleExportCsv = (status) => {
        const rows = buildExportRows(status);
        if (rows.length === 0) return;

        // Export sencillo en CSV para seguimiento operativo del rol cierre.
        const csvHeader = [
            'Cliente',
            'Estación',
            'Nº aviso',
            'Nº pedido',
            'Responsable',
            'Estado cierre',
            'Legalización',
            'Importe pedido',
            'Importe trabajo',
        ];

        const lines = [csvHeader.join(',')];

        rows.forEach((work) => {
            lines.push([
                formatCsvValue(work.cliente),
                formatCsvValue(work.estacion),
                formatCsvValue(work.numeroAviso),
                formatCsvValue(work.numeroPedido),
                formatCsvValue(work.responsable),
                formatCsvValue(CLOSURE_STATUS_LABELS[resolveClosureStatus(work)]),
                formatCsvValue(LEGALIZATION_LABELS[work.legalizacionEstado]),
                formatCsvValue(work.importePedido),
                formatCsvValue(work.importeTrabajo),
            ].join(','));
        });

        const blob = new Blob([`\uFEFF${lines.join('\n')}`], {
            type: 'text/csv;charset=utf-8;',
        });

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `panel-cierre-${status}.csv`;
        link.click();

        URL.revokeObjectURL(url);
    };

    const handleExportPdf = (status) => {
        const rows = buildExportRows(status);
        if (rows.length === 0) return;

        const popup = window.open('', '_blank', 'width=1200,height=900');
        if (!popup) return;

        const title = `Panel de cierre - ${status}`;
        const now = new Date().toLocaleString('es-ES');

        const tableRows = rows
            .map(
                (work) => `
                    <tr>
                        <td>${work.cliente}</td>
                        <td>${work.estacion}</td>
                        <td>${work.numeroAviso}</td>
                        <td>${work.numeroPedido}</td>
                        <td>${work.responsable}</td>
                        <td>${CLOSURE_STATUS_LABELS[resolveClosureStatus(work)]}</td>
                        <td>${LEGALIZATION_LABELS[work.legalizacionEstado]}</td>
                        <td>${formatMoney(work.importePedido)}</td>
                        <td>${formatMoney(work.importeTrabajo)}</td>
                    </tr>
                `,
            )
            .join('');

        // Generamos una vista de impresión limpia para exportar como PDF desde el navegador.
        popup.document.write(`
            <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif; margin: 24px; color: #1a1a1a; }
                        h1 { margin: 0 0 6px 0; font-size: 18px; }
                        p { margin: 0 0 14px 0; font-size: 12px; color: #5c6370; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th, td { border: 1px solid #e3e6ea; padding: 6px 8px; text-align: left; vertical-align: top; }
                        th { background: #f8f9fb; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.06em; }
                    </style>
                </head>
                <body>
                    <h1>${title}</h1>
                    <p>Generado: ${now}</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Estación</th>
                                <th>Nº aviso</th>
                                <th>Nº pedido</th>
                                <th>Responsable</th>
                                <th>Estado cierre</th>
                                <th>Legalización</th>
                                <th>Importe pedido</th>
                                <th>Importe trabajo</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                </body>
            </html>
        `);
        popup.document.close();
        popup.focus();
        popup.print();
    };

    const handleExport = (status) => {
        if (exportFormat === 'pdf') {
            handleExportPdf(status);
            return;
        }

        handleExportCsv(status);
    };

    const detailChecks = activeWork ? buildValidationItems(activeWork) : [];
    const detailStatus = activeWork ? resolveClosureStatus(activeWork) : null;
    const hasBlockingIssues = detailChecks.some((item) => item.blocking && !item.ok);
    const amountDiff = activeWork ? activeWork.importeTrabajo - activeWork.importePedido : 0;

    return (
        <AuthenticatedLayout
            contentWidthClass="max-w-none"
            header={
                <h2 className="text-xl font-semibold leading-tight text-[var(--ciete-slate)]">
                    Panel de cierre
                </h2>
            }
        >
            <Head title="Panel de cierre" />

            <div className="mx-auto w-full space-y-5 px-2 py-8">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            Centro de validación final
                        </p>
                        <h1 className="text-xl font-semibold text-text-main">Panel de cierre</h1>
                        <p className="mt-1 text-sm text-text-muted">Revisión final, validación y cierre de trabajos</p>
                    </div>

                    <div className="grid w-full gap-2 rounded-[12px] border border-border bg-surface p-3 shadow-sm lg:w-auto lg:min-w-[30rem] lg:grid-cols-2">
                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            Contexto
                            <select
                                value={contextFilter}
                                onChange={(event) => setContextFilter(event.target.value)}
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main"
                            >
                                <option value="todos">Todos</option>
                                <option value="repsol">Repsol</option>
                                <option value="cepsa">Cepsa</option>
                            </select>
                        </label>

                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            Estado
                            <select
                                value={statusFilter}
                                onChange={(event) => setStatusFilter(event.target.value)}
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main"
                            >
                                <option value="todos">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="listo">Listo para cerrar</option>
                                <option value="bloqueado">Bloqueado</option>
                                <option value="cerrado">Cerrado</option>
                            </select>
                        </label>

                        <label className="col-span-full flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            Buscador rápido
                            <input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Estación, nº aviso, nº pedido o responsable"
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main placeholder:text-text-hint"
                            />
                        </label>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-6">
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-pending-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Pendientes de cierre</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.pendientes}</p>
                        <p className="mt-1 text-[9px] text-text-hint">pendiente de revisión</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Listos para cerrar</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.listos}</p>
                        <p className="mt-1 text-[9px] text-text-hint">válidos para cierre</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Bloqueados por incidencia</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.bloqueados}</p>
                        <p className="mt-1 text-[9px] text-text-hint">requieren intervención</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Terminados sin fecha fin</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.sinFechaFin}</p>
                        <p className="mt-1 text-[9px] text-text-hint">dato obligatorio pendiente</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-accent bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Legalizaciones pendientes</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.legalizacionesPendientes}</p>
                        <p className="mt-1 text-[9px] text-text-hint">impacto en cierre</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">Cerrados hoy</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.cerradosHoy}</p>
                        <p className="mt-1 text-[9px] text-text-hint">cierres confirmados</p>
                    </div>
                </div>

                <section className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <div className="border-b border-border px-4 py-3">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 className="text-sm font-medium text-text-main">Trabajos en revisión de cierre</h3>
                                <p className="mt-1 text-xs text-text-hint">Control de validaciones, incidencias y acciones finales</p>
                            </div>
                            <div className="grid w-full grid-cols-2 gap-2 lg:w-auto lg:grid-cols-none lg:auto-cols-max lg:grid-flow-col lg:items-center">
                                <span className="col-span-2 text-[10px] font-bold uppercase tracking-widest text-text-hint lg:col-span-1">
                                    {selectedWorks.length} seleccionados
                                </span>
                                <label className="inline-flex items-center justify-center gap-1 rounded-md border border-border bg-surface-2 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main">
                                    Formato
                                    <select
                                        value={exportFormat}
                                        onChange={(event) => setExportFormat(event.target.value)}
                                        className="rounded-md border-border bg-surface text-[10px] font-bold uppercase tracking-widest text-text-main"
                                    >
                                        <option value="csv">CSV</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </label>
                                <button
                                    type="button"
                                    onClick={handleMassMarkReviewed}
                                    disabled={selectedWorks.length === 0}
                                    className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span className="sm:hidden">Revisados</span>
                                    <span className="hidden sm:inline">Marcar revisados</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleExport('pendiente')}
                                    className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                >
                                    <span className="sm:hidden">Exp. pend.</span>
                                    <span className="hidden sm:inline">Exportar pendientes</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleExport('bloqueado')}
                                    className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                >
                                    <span className="sm:hidden">Exp. bloq.</span>
                                    <span className="hidden sm:inline">Exportar bloqueados</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={handleMassClose}
                                    disabled={!canMassClose}
                                    className="col-span-2 inline-flex items-center justify-center rounded-md bg-[var(--ciete-red)] px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-[var(--ciete-red-dark)] disabled:cursor-not-allowed disabled:opacity-50 lg:col-span-1"
                                >
                                    Cierre masivo
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-2 p-3 md:hidden">
                        {visibleWorks.length === 0 ? (
                            <div className="rounded-lg border border-border bg-surface-2 px-3 py-6 text-center text-sm text-text-hint">
                                No hay trabajos para los filtros seleccionados.
                            </div>
                        ) : (
                            visibleWorks.map((work) => {
                                const closureStatus = resolveClosureStatus(work);

                                return (
                                    <article key={work.id} className="rounded-[10px] border border-border bg-surface-2 p-3">
                                        <div className="mb-2 flex items-start justify-between gap-2">
                                            <label className="inline-flex items-center gap-2 text-[11px] font-semibold text-text-main">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedIds.includes(work.id)}
                                                    onChange={() => handleSelectRow(work.id)}
                                                    aria-label={`Seleccionar ${work.numeroAviso}`}
                                                    className="rounded border-border"
                                                />
                                                {work.numeroAviso}
                                            </label>
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${closureStatusBadgeClass(closureStatus)}`}>
                                                {CLOSURE_STATUS_LABELS[closureStatus]}
                                            </span>
                                        </div>

                                        <p className="mb-2 text-xs font-medium text-text-main">{work.estacion}</p>

                                        <div className="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] leading-tight">
                                            <p><span className="text-text-hint">Cliente:</span> {work.cliente}</p>
                                            <p><span className="text-text-hint">Pedido:</span> {work.numeroPedido}</p>
                                            <p><span className="text-text-hint">Responsable:</span> {work.responsable}</p>
                                            <p><span className="text-text-hint">Fecha fin:</span> {formatDate(work.fechaFinReal)}</p>
                                            <p><span className="text-text-hint">Imp. pedido:</span> {formatMoney(work.importePedido)}</p>
                                            <p><span className="text-text-hint">Imp. trabajo:</span> {formatMoney(work.importeTrabajo)}</p>
                                            <p className="col-span-2">
                                                <span className="text-text-hint">Legalización:</span>{' '}
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(work.legalizacionEstado)}`}>
                                                    {LEGALIZATION_LABELS[work.legalizacionEstado]}
                                                </span>
                                            </p>
                                        </div>

                                        <div className="mt-2 flex items-center gap-1.5 text-[11px] text-text-main">
                                            <span className={`inline-block h-2 w-2 rounded-full ${work.incidenciaBloqueante ? 'bg-state-blocked-dot' : 'bg-state-done-dot'}`} />
                                            <span>{work.incidencias.length} incidencias</span>
                                        </div>

                                        <div className="mt-3 grid grid-cols-2 gap-2">
                                            <button
                                                type="button"
                                                onClick={() => handleMainAction(work)}
                                                className={`inline-flex items-center justify-center rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest transition ${
                                                    closureStatus === 'bloqueado'
                                                        ? 'bg-state-blocked-bg text-state-blocked-text hover:opacity-90'
                                                        : closureStatus === 'listo'
                                                          ? 'bg-[var(--ciete-red)] text-white hover:bg-[var(--ciete-red-dark)]'
                                                          : closureStatus === 'cerrado'
                                                            ? 'bg-accent/10 text-accent hover:bg-accent/20'
                                                            : 'bg-state-pending-bg text-state-pending-text hover:opacity-90'
                                                }`}
                                            >
                                                {closureActionLabel(closureStatus)}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => setActiveWorkId(work.id)}
                                                className="inline-flex items-center justify-center rounded-md border border-border bg-surface px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                            >
                                                Ver detalle
                                            </button>
                                        </div>
                                    </article>
                                );
                            })
                        )}
                    </div>

                    <div className="hidden md:block">
                        <table className="w-full table-fixed">
                            <thead className="border-b border-border bg-surface-2">
                                <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    <th className="w-8 px-2 py-2">
                                        <input
                                            type="checkbox"
                                            checked={allVisibleSelected}
                                            onChange={handleSelectAllVisible}
                                            aria-label="Seleccionar trabajos visibles"
                                            className="rounded border-border"
                                        />
                                    </th>
                                    <th className="w-16 px-2 py-2">Cliente</th>
                                    <th className="w-[10rem] px-2 py-2">Estación</th>
                                    <th className="w-[5.5rem] px-2 py-2">Nº aviso</th>
                                    <th className="w-[5.5rem] px-2 py-2">Nº pedido</th>
                                    <th className="w-[9rem] px-2 py-2">Tipo de trabajo</th>
                                    <th className="w-[8rem] px-2 py-2">Responsable</th>
                                    <th className="w-[6.5rem] px-2 py-2">Fecha encargo</th>
                                    <th className="w-[6.5rem] px-2 py-2">Fecha real terminación</th>
                                    <th className="w-[7rem] px-2 py-2">Importe pedido</th>
                                    <th className="w-[7rem] px-2 py-2">Importe trabajo</th>
                                    <th className="w-[6.5rem] px-2 py-2">Legalización</th>
                                    <th className="w-[7rem] px-2 py-2">Estado de cierre</th>
                                    <th className="w-[5.5rem] px-2 py-2">Incidencias</th>
                                    <th className="w-[6.5rem] px-2 py-2">Acción</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {visibleWorks.length === 0 ? (
                                    <tr>
                                        <td colSpan={15} className="px-4 py-10 text-center text-sm text-text-hint">
                                            No hay trabajos para los filtros seleccionados.
                                        </td>
                                    </tr>
                                ) : (
                                    visibleWorks.map((work) => {
                                        const closureStatus = resolveClosureStatus(work);

                                        return (
                                            <tr key={work.id} className="align-top transition hover:bg-surface-2/60">
                                                <td className="px-2 py-2.5 align-top">
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedIds.includes(work.id)}
                                                        onChange={() => handleSelectRow(work.id)}
                                                        aria-label={`Seleccionar ${work.numeroAviso}`}
                                                        className="rounded border-border"
                                                    />
                                                </td>
                                                <td className="px-2 py-2.5 text-[11px] font-semibold leading-tight text-text-main break-words">{work.cliente}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main break-words">{work.estacion}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main break-words">{work.numeroAviso}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main break-words">{work.numeroPedido}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main break-words">{work.tipoTrabajo}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main break-words">{work.responsable}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-muted">{formatDate(work.fechaEncargo)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-muted">{formatDate(work.fechaFinReal)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">{formatMoney(work.importePedido)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">{formatMoney(work.importeTrabajo)}</td>
                                                <td className="px-2 py-2.5">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(work.legalizacionEstado)}`}>
                                                        {LEGALIZATION_LABELS[work.legalizacionEstado]}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2.5">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${closureStatusBadgeClass(closureStatus)}`}>
                                                        {CLOSURE_STATUS_LABELS[closureStatus]}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">
                                                    <div className="flex items-center gap-1.5">
                                                        <span className={`inline-block h-2 w-2 rounded-full ${work.incidenciaBloqueante ? 'bg-state-blocked-dot' : 'bg-state-done-dot'}`} />
                                                        <span>{work.incidencias.length} incid.</span>
                                                    </div>
                                                </td>
                                                <td className="px-2 py-2.5">
                                                    <div className="flex flex-col gap-1.5">
                                                        <button
                                                            type="button"
                                                            onClick={() => handleMainAction(work)}
                                                            className={`inline-flex items-center justify-center rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-widest transition ${
                                                                closureStatus === 'bloqueado'
                                                                    ? 'bg-state-blocked-bg text-state-blocked-text hover:opacity-90'
                                                                    : closureStatus === 'listo'
                                                                      ? 'bg-[var(--ciete-red)] text-white hover:bg-[var(--ciete-red-dark)]'
                                                                      : closureStatus === 'cerrado'
                                                                        ? 'bg-accent/10 text-accent hover:bg-accent/20'
                                                                        : 'bg-state-pending-bg text-state-pending-text hover:opacity-90'
                                                            }`}
                                                        >
                                                            {closureActionLabel(closureStatus)}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => setActiveWorkId(work.id)}
                                                            className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                                        >
                                                            Ver detalle
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rounded-[12px] border border-border bg-surface p-4 shadow-sm">
                    <div className="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h3 className="text-sm font-medium text-text-main">Incidencias de cierre</h3>
                            <p className="mt-1 text-xs text-text-hint">Resumen operativo para localizar bloqueos con rapidez</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setStatusFilter('bloqueado')}
                            className="inline-flex items-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                        >
                            Ver bloqueados
                        </button>
                    </div>

                    {closureIncidents.length === 0 ? (
                        <p className="text-sm text-text-hint">No hay incidencias relevantes para el alcance actual.</p>
                    ) : (
                        <ul className="space-y-2">
                            {closureIncidents.map((incident) => (
                                <li key={incident.id} className="flex items-center justify-between rounded-lg border border-border bg-surface-2 px-3 py-2">
                                    <span className="text-xs font-medium text-text-main">{incident.label}</span>
                                    <span className="inline-flex items-center rounded-full bg-state-blocked-bg px-2 py-0.5 text-[10px] font-bold text-state-blocked-text">
                                        {incident.count}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>

            {activeWork && (
                <>
                    <button
                        type="button"
                        className="fixed inset-0 z-40 bg-black/35"
                        onClick={() => setActiveWorkId(null)}
                        aria-label="Cerrar detalle de revisión"
                    />

                    <aside className="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-border bg-surface shadow-2xl">
                        <div className="sticky top-0 z-10 border-b border-border bg-surface px-5 py-4">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">Revisión de cierre</p>
                                    <h3 className="mt-1 text-base font-semibold text-text-main">
                                        {activeWork.numeroAviso} · {activeWork.estacion}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setActiveWorkId(null)}
                                    className="rounded-md border border-border bg-surface-2 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>

                        <div className="space-y-5 px-5 py-5">
                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Datos base</h4>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">Cliente:</span> {activeWork.cliente}</p>
                                    <p><span className="text-text-hint">Estación:</span> {activeWork.estacion}</p>
                                    <p><span className="text-text-hint">Nº aviso:</span> {activeWork.numeroAviso}</p>
                                    <p><span className="text-text-hint">Nº pedido:</span> {activeWork.numeroPedido}</p>
                                    <p><span className="text-text-hint">Tipo de trabajo:</span> {activeWork.tipoTrabajo}</p>
                                    <p><span className="text-text-hint">Responsable:</span> {activeWork.responsable}</p>
                                    <p><span className="text-text-hint">Fecha encargo:</span> {formatDate(activeWork.fechaEncargo)}</p>
                                    <p><span className="text-text-hint">Fecha fin real:</span> {formatDate(activeWork.fechaFinReal)}</p>
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Estado del flujo</h4>
                                <div className="mt-3 flex flex-wrap gap-1.5">
                                    {FLOW_STEPS.map((step, index) => {
                                        const currentIndex = FLOW_STEPS.findIndex((item) => item.id === activeWork.faseActual);
                                        const isDone = index < currentIndex;
                                        const isCurrent = step.id === activeWork.faseActual;

                                        return (
                                            <span
                                                key={step.id}
                                                className={`inline-flex items-center rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wide ${
                                                    isCurrent
                                                        ? 'bg-[var(--ciete-red)] text-white'
                                                        : isDone
                                                          ? 'bg-state-done-bg text-state-done-text'
                                                          : 'bg-surface text-text-hint'
                                                }`}
                                            >
                                                {step.label}
                                            </span>
                                        );
                                    })}
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Control económico</h4>
                                <div className="mt-3 space-y-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">Importe pedido:</span> {formatMoney(activeWork.importePedido)}</p>
                                    <p><span className="text-text-hint">Importe trabajo:</span> {formatMoney(activeWork.importeTrabajo)}</p>
                                    <p>
                                        <span className="text-text-hint">Diferencia:</span>{' '}
                                        <span className={amountDiff > 0 ? 'font-semibold text-state-blocked-text' : 'font-semibold text-state-done-text'}>
                                            {formatMoney(amountDiff)}
                                        </span>
                                    </p>
                                </div>
                                {amountDiff > 0 && !activeWork.revisionEconomicaAprobada && (
                                    <p className="mt-3 rounded-md bg-state-blocked-bg px-2.5 py-2 text-xs font-medium text-state-blocked-text">
                                        El importe supera el pedido y requiere validación económica antes del cierre.
                                    </p>
                                )}
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Legalizaciones</h4>
                                <div className="mt-3 space-y-2 text-xs text-text-main">
                                    <p>
                                        <span className="text-text-hint">Estado:</span>{' '}
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(activeWork.legalizacionEstado)}`}>
                                            {LEGALIZATION_LABELS[activeWork.legalizacionEstado]}
                                        </span>
                                    </p>
                                    <p><span className="text-text-hint">Observaciones:</span> {activeWork.legalizacionObservaciones}</p>
                                    <p>
                                        <span className="text-text-hint">Impacto en cierre:</span>{' '}
                                        {activeWork.legalizacionCritica ? 'Bloquea cierre hasta completar validación.' : 'No bloquea cierre final.'}
                                    </p>
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Checklist final de validación</h4>
                                <ul className="mt-3 space-y-2">
                                    {detailChecks.map((check) => (
                                        <li key={check.id} className="rounded-md border border-border bg-surface px-3 py-2">
                                            <div className="flex items-center justify-between gap-3 text-xs">
                                                <span className="text-text-main">{check.label}</span>
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${check.ok ? 'bg-state-done-bg text-state-done-text' : 'bg-state-blocked-bg text-state-blocked-text'}`}>
                                                    {check.ok ? 'OK' : 'Pendiente'}
                                                </span>
                                            </div>
                                            {check.note && (
                                                <p className="mt-1 text-[11px] text-state-blocked-text">{check.note}</p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Historial y trazabilidad</h4>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">Marcado terminado por:</span> {activeWork.trazabilidad?.marcadoTerminadoPor || '-'}</p>
                                    <p><span className="text-text-hint">Fecha marcado terminado:</span> {activeWork.trazabilidad?.fechaMarcadoTerminado || '-'}</p>
                                    <p><span className="text-text-hint">Fecha fin informada por:</span> {activeWork.trazabilidad?.fechaFinInformadaPor || '-'}</p>
                                    <p><span className="text-text-hint">Momento fecha fin:</span> {activeWork.trazabilidad?.fechaFinInformadaAt || '-'}</p>
                                    <p><span className="text-text-hint">Importe modificado por:</span> {activeWork.trazabilidad?.importeActualizadoPor || '-'}</p>
                                    <p><span className="text-text-hint">Momento modificación:</span> {activeWork.trazabilidad?.importeActualizadoAt || '-'}</p>
                                    <p><span className="text-text-hint">Cerrado por:</span> {activeWork.trazabilidad?.cerradoPor || '-'}</p>
                                    <p><span className="text-text-hint">Fecha cierre:</span> {activeWork.trazabilidad?.fechaCierre ? formatDate(activeWork.trazabilidad.fechaCierre) : '-'}</p>
                                    <p><span className="text-text-hint">Reabierto por:</span> {activeWork.trazabilidad?.reabiertoPor || '-'}</p>
                                    <p><span className="text-text-hint">Fecha reapertura:</span> {activeWork.trazabilidad?.fechaReapertura ? formatDate(activeWork.trazabilidad.fechaReapertura) : '-'}</p>
                                </div>
                                {activeWork.trazabilidad?.reaperturaMotivo && (
                                    <p className="mt-2 text-xs text-text-main">
                                        <span className="text-text-hint">Motivo de reapertura:</span> {activeWork.trazabilidad.reaperturaMotivo}
                                    </p>
                                )}
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">Acción final</h4>

                                {detailStatus === 'cerrado' ? (
                                    <div className="mt-3 space-y-2">
                                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                            Motivo de reapertura
                                            <textarea
                                                value={reopenReason}
                                                onChange={(event) => setReopenReason(event.target.value)}
                                                className="min-h-[84px] rounded-md border-border bg-surface text-xs text-text-main"
                                            />
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                reopenWork(activeWork.id, reopenReason);
                                                setActiveWorkId(null);
                                            }}
                                            className="inline-flex items-center rounded-md bg-state-blocked-bg px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-state-blocked-text transition hover:opacity-90"
                                        >
                                            Reabrir trabajo
                                        </button>
                                    </div>
                                ) : (
                                    <div className="mt-3 space-y-2">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                closeWork(activeWork.id);
                                                setActiveWorkId(null);
                                            }}
                                            disabled={detailStatus !== 'listo'}
                                            className="inline-flex items-center rounded-md bg-[var(--ciete-red)] px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-[var(--ciete-red-dark)] disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            Cerrar trabajo
                                        </button>

                                        {(detailStatus !== 'listo' || hasBlockingIssues) && (
                                            <p className="text-xs text-state-blocked-text">
                                                El trabajo no puede cerrarse todavía. Revisa checklist e incidencias bloqueantes.
                                            </p>
                                        )}
                                    </div>
                                )}
                            </section>
                        </div>
                    </aside>
                </>
            )}
        </AuthenticatedLayout>
    );
}
