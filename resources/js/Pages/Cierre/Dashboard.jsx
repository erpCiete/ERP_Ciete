import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const FLOW_STEPS = [
    'encargo',
    'pedido',
    'ejecucion',
    'terminado',
    'revision_cierre',
    'cerrado',
];

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

function formatMoneyIntl(value, formatter) {
    return formatter.format(Number(value || 0));
}

function formatDateIntl(value, formatter) {
    if (!value) return '-';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '-';

    return formatter.format(date);
}

function buildValidationItems(work) {
    const exceedsOrderAmount = work.importeTrabajo > work.importePedido;

    return [
        {
            id: 'finishedConfirmed',
            labelKey: 'closureDashboard.checklist.finishedConfirmed',
            ok: work.terminadoConfirmado,
            blocking: true,
        },
        {
            id: 'endDate',
            labelKey: 'closureDashboard.checklist.endDate',
            ok: Boolean(work.fechaFinReal),
            blocking: true,
        },
        {
            id: 'validOrder',
            labelKey: 'closureDashboard.checklist.validOrder',
            ok: Boolean(work.numeroPedido),
            blocking: true,
        },
        {
            id: 'amountReviewed',
            labelKey: 'closureDashboard.checklist.amountReviewed',
            ok: !exceedsOrderAmount || work.revisionEconomicaAprobada,
            blocking: true,
            noteKey:
                exceedsOrderAmount && !work.revisionEconomicaAprobada
                    ? 'closureDashboard.messages.amountExceeded'
                    : null,
        },
        {
            id: 'legalizationChecked',
            labelKey: 'closureDashboard.checklist.legalizationChecked',
            ok:
                work.legalizacionEstado === 'completa' ||
                work.legalizacionEstado === 'no_aplica' ||
                (work.legalizacionEstado === 'en_revision' && !work.legalizacionCritica),
            blocking: true,
        },
        {
            id: 'mandatoryFields',
            labelKey: 'closureDashboard.checklist.mandatoryFields',
            ok: work.camposObligatoriosCompletos,
            blocking: true,
        },
        {
            id: 'noBlockingIncidents',
            labelKey: 'closureDashboard.checklist.noBlockingIncidents',
            ok: !work.incidenciaBloqueante,
            blocking: true,
        },
        {
            id: 'contractTariff',
            labelKey: 'closureDashboard.checklist.contractTariff',
            ok: !work.requiereContratoTarifario || work.contratoTarifarioValidado,
            blocking: true,
        },
        {
            id: 'flowReady',
            labelKey: 'closureDashboard.checklist.flowReady',
            ok: ['revision_cierre', 'cerrado'].includes(work.faseActual),
            blocking: true,
        },
        {
            id: 'readyToClose',
            labelKey: 'closureDashboard.checklist.readyToClose',
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

function closureActionKey(status) {
    if (status === 'listo') return 'closureDashboard.actions.close';
    if (status === 'bloqueado') return 'closureDashboard.actions.review';
    if (status === 'cerrado') return 'closureDashboard.actions.reopen';

    return 'closureDashboard.actions.validate';
}

function formatCsvValue(value) {
    const text = String(value ?? '');
    return `"${text.replace(/"/g, '""')}"`;
}

export default function ClosureDashboard() {
    const { t, locale } = useI18n();
    const user = usePage().props.auth.user;
    const localeForIntl = locale === 'en' ? 'en-US' : 'es-ES';
    const moneyFormatter = useMemo(
        () =>
            new Intl.NumberFormat(localeForIntl, {
                style: 'currency',
                currency: 'EUR',
                maximumFractionDigits: 2,
            }),
        [localeForIntl],
    );
    const dateFormatter = useMemo(
        () =>
            new Intl.DateTimeFormat(localeForIntl, {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            }),
        [localeForIntl],
    );
    const formatMoneyValue = (value) => formatMoneyIntl(value, moneyFormatter);
    const formatDateValue = (value) => formatDateIntl(value, dateFormatter);
    const statusLabel = (status) => t(`closureDashboard.statusLabels.${status}`);
    const legalizationLabel = (status) => t(`closureDashboard.legalizationLabels.${status}`);

    const actorName =
        user?.nombre_usuario ||
        [user?.nombre, user?.apellidos].filter(Boolean).join(' ') ||
        t('closureDashboard.fallbackUser');

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
                return formatDateValue(work.trazabilidad.fechaCierre) === today;
            }).length,
        };
    }, [scopedWorks]);

    const closureIncidents = useMemo(() => {
        const items = [
            {
                id: 'no-end-date',
                label: t('closureDashboard.incidents.items.noEndDate'),
                count: scopedWorks.filter((work) => work.terminadoConfirmado && !work.fechaFinReal).length,
            },
            {
                id: 'budget-overrun',
                label: t('closureDashboard.incidents.items.budgetOverrun'),
                count: scopedWorks.filter((work) => work.importeTrabajo > work.importePedido && !work.revisionEconomicaAprobada).length,
            },
            {
                id: 'legalization-pending',
                label: t('closureDashboard.incidents.items.legalizationPending'),
                count: scopedWorks.filter((work) => ['pendiente', 'en_revision'].includes(work.legalizacionEstado) && work.legalizacionCritica).length,
            },
            {
                id: 'incomplete-data',
                label: t('closureDashboard.incidents.items.incompleteData'),
                count: scopedWorks.filter((work) => !work.camposObligatoriosCompletos).length,
            },
            {
                id: 'economic-review',
                label: t('closureDashboard.incidents.items.economicReview'),
                count: scopedWorks.filter((work) => work.importeTrabajo > work.importePedido && !work.revisionEconomicaAprobada).length,
            },
        ];

        return items.filter((item) => item.count > 0);
    }, [scopedWorks, t]);

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
                    reaperturaMotivo: reason || t('closureDashboard.messages.defaultReopenReason'),
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
            setReopenReason(t('closureDashboard.messages.defaultReopenReason'));
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

        const csvHeader = [
            t('closureDashboard.columns.client'),
            t('closureDashboard.columns.station'),
            t('closureDashboard.columns.noticeNumber'),
            t('closureDashboard.columns.orderNumber'),
            t('closureDashboard.columns.owner'),
            t('closureDashboard.columns.closureStatus'),
            t('closureDashboard.columns.legalization'),
            t('closureDashboard.columns.orderAmount'),
            t('closureDashboard.columns.workAmount'),
        ];

        const lines = [csvHeader.join(',')];

        rows.forEach((work) => {
            lines.push([
                formatCsvValue(work.cliente),
                formatCsvValue(work.estacion),
                formatCsvValue(work.numeroAviso),
                formatCsvValue(work.numeroPedido),
                formatCsvValue(work.responsable),
                formatCsvValue(statusLabel(resolveClosureStatus(work))),
                formatCsvValue(legalizationLabel(work.legalizacionEstado)),
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
        link.download = `${t('closureDashboard.export.filePrefix')}-${status}.csv`;
        link.click();

        URL.revokeObjectURL(url);
    };

    const handleExportPdf = (status) => {
        const rows = buildExportRows(status);
        if (rows.length === 0) return;

        const popup = window.open('', '_blank', 'width=1200,height=900');
        if (!popup) return;

        const title = `${t('closureDashboard.header')} - ${statusLabel(status)}`;
        const now = new Date().toLocaleString(localeForIntl);

        const tableRows = rows
            .map(
                (work) => `
                    <tr>
                        <td>${work.cliente}</td>
                        <td>${work.estacion}</td>
                        <td>${work.numeroAviso}</td>
                        <td>${work.numeroPedido}</td>
                        <td>${work.responsable}</td>
                        <td>${statusLabel(resolveClosureStatus(work))}</td>
                        <td>${legalizationLabel(work.legalizacionEstado)}</td>
                        <td>${formatMoneyValue(work.importePedido)}</td>
                        <td>${formatMoneyValue(work.importeTrabajo)}</td>
                    </tr>
                `,
            )
            .join('');

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
                    <p>${t('closureDashboard.export.generatedAt')}: ${now}</p>
                    <table>
                        <thead>
                            <tr>
                                <th>${t('closureDashboard.columns.client')}</th>
                                <th>${t('closureDashboard.columns.station')}</th>
                                <th>${t('closureDashboard.columns.noticeNumber')}</th>
                                <th>${t('closureDashboard.columns.orderNumber')}</th>
                                <th>${t('closureDashboard.columns.owner')}</th>
                                <th>${t('closureDashboard.columns.closureStatus')}</th>
                                <th>${t('closureDashboard.columns.legalization')}</th>
                                <th>${t('closureDashboard.columns.orderAmount')}</th>
                                <th>${t('closureDashboard.columns.workAmount')}</th>
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
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('closureDashboard.header')}
                </h2>
            }
        >
            <Head title={t('closureDashboard.headTitle')} />

            <div className="mx-auto w-full space-y-5 px-2 py-8">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('closureDashboard.panelLabel')}
                        </p>
                        <h1 className="text-xl font-semibold text-text-main">{t('closureDashboard.header')}</h1>
                        <p className="mt-1 text-sm text-text-muted">{t('closureDashboard.subtitle')}</p>
                    </div>

                    <div className="grid w-full gap-2 rounded-[12px] border border-border bg-surface p-3 shadow-sm lg:w-auto lg:min-w-120 lg:grid-cols-2">
                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('closureDashboard.filters.context')}
                            <select
                                value={contextFilter}
                                onChange={(event) => setContextFilter(event.target.value)}
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main"
                            >
                                <option value="todos">{t('closureDashboard.filters.all')}</option>
                                <option value="repsol">Repsol</option>
                                <option value="cepsa">Cepsa</option>
                            </select>
                        </label>

                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('closureDashboard.filters.status')}
                            <select
                                value={statusFilter}
                                onChange={(event) => setStatusFilter(event.target.value)}
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main"
                            >
                                <option value="todos">{t('closureDashboard.filters.all')}</option>
                                <option value="pendiente">{statusLabel('pendiente')}</option>
                                <option value="listo">{statusLabel('listo')}</option>
                                <option value="bloqueado">{statusLabel('bloqueado')}</option>
                                <option value="cerrado">{statusLabel('cerrado')}</option>
                            </select>
                        </label>

                        <label className="col-span-full flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('closureDashboard.filters.quickSearch')}
                            <input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder={t('closureDashboard.filters.searchPlaceholder')}
                                className="rounded-md border-border bg-surface-2 text-xs text-text-main placeholder:text-text-hint"
                            />
                        </label>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-6">
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-pending-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.pendingToClose')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.pendientes}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.pendingReview')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.readyToClose')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.listos}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.validForClose')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.blockedByIncident')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.bloqueados}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.requiresIntervention')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-blocked-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.finishedWithoutEndDate')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.sinFechaFin}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.requiredDataPending')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-accent bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.pendingLegalizations')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.legalizacionesPendientes}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.impactsClose')}</p>
                    </div>
                    <div className="rounded-[10px] border border-border border-l-[3px] border-l-state-done-dot bg-surface p-3 shadow-sm">
                        <p className="mb-1 text-[9px] font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.metrics.closedToday')}</p>
                        <p className="text-2xl font-medium leading-none text-text-main">{metrics.cerradosHoy}</p>
                        <p className="mt-1 text-[9px] text-text-hint">{t('closureDashboard.metrics.confirmedClosures')}</p>
                    </div>
                </div>

                <section className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <div className="border-b border-border px-4 py-3">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 className="text-sm font-medium text-text-main">{t('closureDashboard.main.title')}</h3>
                                <p className="mt-1 text-xs text-text-hint">{t('closureDashboard.main.subtitle')}</p>
                            </div>
                            <div className="grid w-full grid-cols-2 gap-2 lg:w-auto lg:grid-cols-none lg:auto-cols-max lg:grid-flow-col lg:items-center">
                                <span className="col-span-2 text-[10px] font-bold uppercase tracking-widest text-text-hint lg:col-span-1">
                                    {t('closureDashboard.main.selectedCount', { count: selectedWorks.length })}
                                </span>
                                <label className="inline-flex items-center justify-center gap-1 rounded-md border border-border bg-surface-2 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main">
                                    {t('closureDashboard.main.format')}
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
                                    <span className="sm:hidden">{t('closureDashboard.actions.shortMarkReviewed')}</span>
                                    <span className="hidden sm:inline">{t('closureDashboard.actions.markReviewed')}</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleExport('pendiente')}
                                    className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                >
                                    <span className="sm:hidden">{t('closureDashboard.actions.shortExportPending')}</span>
                                    <span className="hidden sm:inline">{t('closureDashboard.actions.exportPending')}</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleExport('bloqueado')}
                                    className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                >
                                    <span className="sm:hidden">{t('closureDashboard.actions.shortExportBlocked')}</span>
                                    <span className="hidden sm:inline">{t('closureDashboard.actions.exportBlocked')}</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={handleMassClose}
                                    disabled={!canMassClose}
                                    className="col-span-2 inline-flex items-center justify-center rounded-md bg-(--ciete-red) px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark) disabled:cursor-not-allowed disabled:opacity-50 lg:col-span-1"
                                >
                                    {t('closureDashboard.actions.massClose')}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-2 p-3 md:hidden">
                        {visibleWorks.length === 0 ? (
                            <div className="rounded-lg border border-border bg-surface-2 px-3 py-6 text-center text-sm text-text-hint">
                                {t('closureDashboard.main.empty')}
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
                                                    aria-label={`${t('closureDashboard.main.selectOne')} ${work.numeroAviso}`}
                                                    className="rounded-sm border-border"
                                                />
                                                {work.numeroAviso}
                                            </label>
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${closureStatusBadgeClass(closureStatus)}`}>
                                                {statusLabel(closureStatus)}
                                            </span>
                                        </div>

                                        <p className="mb-2 text-xs font-medium text-text-main">{work.estacion}</p>

                                        <div className="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] leading-tight">
                                            <p><span className="text-text-hint">{t('closureDashboard.columns.client')}:</span> {work.cliente}</p>
                                            <p><span className="text-text-hint">{t('closureDashboard.mobile.orderShort')}:</span> {work.numeroPedido}</p>
                                            <p><span className="text-text-hint">{t('closureDashboard.columns.owner')}:</span> {work.responsable}</p>
                                            <p><span className="text-text-hint">{t('closureDashboard.mobile.endDateShort')}:</span> {formatDateValue(work.fechaFinReal)}</p>
                                            <p><span className="text-text-hint">{t('closureDashboard.mobile.orderAmountShort')}:</span> {formatMoneyValue(work.importePedido)}</p>
                                            <p><span className="text-text-hint">{t('closureDashboard.mobile.workAmountShort')}:</span> {formatMoneyValue(work.importeTrabajo)}</p>
                                            <p className="col-span-2">
                                                <span className="text-text-hint">{t('closureDashboard.columns.legalization')}:</span>{' '}
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(work.legalizacionEstado)}`}>
                                                    {legalizationLabel(work.legalizacionEstado)}
                                                </span>
                                            </p>
                                        </div>

                                        <div className="mt-2 flex items-center gap-1.5 text-[11px] text-text-main">
                                            <span className={`inline-block h-2 w-2 rounded-full ${work.incidenciaBloqueante ? 'bg-state-blocked-dot' : 'bg-state-done-dot'}`} />
                                            <span>{work.incidencias.length} {t('closureDashboard.mobile.incidentsSuffix')}</span>
                                        </div>

                                        <div className="mt-3 grid grid-cols-2 gap-2">
                                            <button
                                                type="button"
                                                onClick={() => handleMainAction(work)}
                                                className={`inline-flex items-center justify-center rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest transition ${
                                                    closureStatus === 'bloqueado'
                                                        ? 'bg-state-blocked-bg text-state-blocked-text hover:opacity-90'
                                                        : closureStatus === 'listo'
                                                          ? 'bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)'
                                                          : closureStatus === 'cerrado'
                                                            ? 'bg-accent/10 text-accent hover:bg-accent/20'
                                                            : 'bg-state-pending-bg text-state-pending-text hover:opacity-90'
                                                }`}
                                            >
                                                {t(closureActionKey(closureStatus))}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => setActiveWorkId(work.id)}
                                                className="inline-flex items-center justify-center rounded-md border border-border bg-surface px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                            >
                                                {t('closureDashboard.actions.viewDetail')}
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
                                            aria-label={t('closureDashboard.main.selectVisible')}
                                            className="rounded-sm border-border"
                                        />
                                    </th>
                                    <th className="w-16 px-2 py-2">{t('closureDashboard.columns.client')}</th>
                                    <th className="w-40 px-2 py-2">{t('closureDashboard.columns.station')}</th>
                                    <th className="w-22 px-2 py-2">{t('closureDashboard.columns.noticeNumber')}</th>
                                    <th className="w-22 px-2 py-2">{t('closureDashboard.columns.orderNumber')}</th>
                                    <th className="w-36 px-2 py-2">{t('closureDashboard.columns.workType')}</th>
                                    <th className="w-32 px-2 py-2">{t('closureDashboard.columns.owner')}</th>
                                    <th className="w-26 px-2 py-2">{t('closureDashboard.columns.assignmentDate')}</th>
                                    <th className="w-26 px-2 py-2">{t('closureDashboard.columns.realEndDate')}</th>
                                    <th className="w-28 px-2 py-2">{t('closureDashboard.columns.orderAmount')}</th>
                                    <th className="w-28 px-2 py-2">{t('closureDashboard.columns.workAmount')}</th>
                                    <th className="w-26 px-2 py-2">{t('closureDashboard.columns.legalization')}</th>
                                    <th className="w-28 px-2 py-2">{t('closureDashboard.columns.closureStatus')}</th>
                                    <th className="w-22 px-2 py-2">{t('closureDashboard.columns.incidents')}</th>
                                    <th className="w-26 px-2 py-2">{t('closureDashboard.columns.action')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {visibleWorks.length === 0 ? (
                                    <tr>
                                        <td colSpan={15} className="px-4 py-10 text-center text-sm text-text-hint">
                                            {t('closureDashboard.main.empty')}
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
                                                        aria-label={`${t('closureDashboard.main.selectOne')} ${work.numeroAviso}`}
                                                        className="rounded-sm border-border"
                                                    />
                                                </td>
                                                <td className="px-2 py-2.5 text-[11px] font-semibold leading-tight text-text-main wrap-break-word">{work.cliente}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main wrap-break-word">{work.estacion}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main wrap-break-word">{work.numeroAviso}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main wrap-break-word">{work.numeroPedido}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main wrap-break-word">{work.tipoTrabajo}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main wrap-break-word">{work.responsable}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-muted">{formatDateValue(work.fechaEncargo)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-muted">{formatDateValue(work.fechaFinReal)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">{formatMoneyValue(work.importePedido)}</td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">{formatMoneyValue(work.importeTrabajo)}</td>
                                                <td className="px-2 py-2.5">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(work.legalizacionEstado)}`}>
                                                        {legalizationLabel(work.legalizacionEstado)}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2.5">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${closureStatusBadgeClass(closureStatus)}`}>
                                                        {statusLabel(closureStatus)}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2.5 text-[11px] leading-tight text-text-main">
                                                    <div className="flex items-center gap-1.5">
                                                        <span className={`inline-block h-2 w-2 rounded-full ${work.incidenciaBloqueante ? 'bg-state-blocked-dot' : 'bg-state-done-dot'}`} />
                                                        <span>{work.incidencias.length} {t('closureDashboard.table.incidentsShort')}</span>
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
                                                                      ? 'bg-(--ciete-red) text-white hover:bg-(--ciete-red-dark)'
                                                                      : closureStatus === 'cerrado'
                                                                        ? 'bg-accent/10 text-accent hover:bg-accent/20'
                                                                        : 'bg-state-pending-bg text-state-pending-text hover:opacity-90'
                                                            }`}
                                                        >
                                                            {t(closureActionKey(closureStatus))}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => setActiveWorkId(work.id)}
                                                            className="inline-flex items-center justify-center rounded-md border border-border bg-surface-2 px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                                                        >
                                                            {t('closureDashboard.actions.viewDetail')}
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
                            <h3 className="text-sm font-medium text-text-main">{t('closureDashboard.incidents.title')}</h3>
                            <p className="mt-1 text-xs text-text-hint">{t('closureDashboard.incidents.subtitle')}</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setStatusFilter('bloqueado')}
                            className="inline-flex items-center rounded-md border border-border bg-surface-2 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-border"
                        >
                            {t('closureDashboard.incidents.viewBlocked')}
                        </button>
                    </div>

                    {closureIncidents.length === 0 ? (
                        <p className="text-sm text-text-hint">{t('closureDashboard.incidents.empty')}</p>
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
                        aria-label={t('closureDashboard.detail.closeReviewAria')}
                    />

                    <aside className="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-border bg-surface shadow-2xl">
                        <div className="sticky top-0 z-10 border-b border-border bg-surface px-5 py-4">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('closureDashboard.detail.title')}</p>
                                    <h3 className="mt-1 text-base font-semibold text-text-main">
                                        {activeWork.numeroAviso} · {activeWork.estacion}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setActiveWorkId(null)}
                                    className="rounded-md border border-border bg-surface-2 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-text-main"
                                >
                                    {t('common.actions.close')}
                                </button>
                            </div>
                        </div>

                        <div className="space-y-5 px-5 py-5">
                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.baseData')}</h4>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.client')}:</span> {activeWork.cliente}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.station')}:</span> {activeWork.estacion}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.noticeNumber')}:</span> {activeWork.numeroAviso}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.orderNumber')}:</span> {activeWork.numeroPedido}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.workType')}:</span> {activeWork.tipoTrabajo}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.owner')}:</span> {activeWork.responsable}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.assignmentDate')}:</span> {formatDateValue(activeWork.fechaEncargo)}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.realEndDate')}:</span> {formatDateValue(activeWork.fechaFinReal)}</p>
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.flowState')}</h4>
                                <div className="mt-3 flex flex-wrap gap-1.5">
                                    {FLOW_STEPS.map((step, index) => {
                                        const currentIndex = FLOW_STEPS.findIndex((item) => item === activeWork.faseActual);
                                        const isDone = index < currentIndex;
                                        const isCurrent = step === activeWork.faseActual;

                                        return (
                                            <span
                                                key={step}
                                                className={`inline-flex items-center rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wide ${
                                                    isCurrent
                                                        ? 'bg-(--ciete-red) text-white'
                                                        : isDone
                                                          ? 'bg-state-done-bg text-state-done-text'
                                                          : 'bg-surface text-text-hint'
                                                }`}
                                            >
                                                {t(`closureDashboard.flow.${step}`)}
                                            </span>
                                        );
                                    })}
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.economicControl')}</h4>
                                <div className="mt-3 space-y-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.orderAmount')}:</span> {formatMoneyValue(activeWork.importePedido)}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.columns.workAmount')}:</span> {formatMoneyValue(activeWork.importeTrabajo)}</p>
                                    <p>
                                        <span className="text-text-hint">{t('closureDashboard.detail.difference')}:</span>{' '}
                                        <span className={amountDiff > 0 ? 'font-semibold text-state-blocked-text' : 'font-semibold text-state-done-text'}>
                                            {formatMoneyValue(amountDiff)}
                                        </span>
                                    </p>
                                </div>
                                {amountDiff > 0 && !activeWork.revisionEconomicaAprobada && (
                                    <p className="mt-3 rounded-md bg-state-blocked-bg px-2.5 py-2 text-xs font-medium text-state-blocked-text">
                                        {t('closureDashboard.messages.amountExceeded')}
                                    </p>
                                )}
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.legalizations')}</h4>
                                <div className="mt-3 space-y-2 text-xs text-text-main">
                                    <p>
                                        <span className="text-text-hint">{t('closureDashboard.detail.state')}:</span>{' '}
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${legalizationBadgeClass(activeWork.legalizacionEstado)}`}>
                                            {legalizationLabel(activeWork.legalizacionEstado)}
                                        </span>
                                    </p>
                                    <p><span className="text-text-hint">{t('closureDashboard.detail.notes')}:</span> {activeWork.legalizacionObservaciones}</p>
                                    <p>
                                        <span className="text-text-hint">{t('closureDashboard.detail.closureImpact')}:</span>{' '}
                                        {activeWork.legalizacionCritica
                                            ? t('closureDashboard.detail.blocksClosure')
                                            : t('closureDashboard.detail.doesNotBlockClosure')}
                                    </p>
                                </div>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.finalChecklist')}</h4>
                                <ul className="mt-3 space-y-2">
                                    {detailChecks.map((check) => (
                                        <li key={check.id} className="rounded-md border border-border bg-surface px-3 py-2">
                                            <div className="flex items-center justify-between gap-3 text-xs">
                                                <span className="text-text-main">{t(check.labelKey)}</span>
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${check.ok ? 'bg-state-done-bg text-state-done-text' : 'bg-state-blocked-bg text-state-blocked-text'}`}>
                                                    {check.ok ? t('closureDashboard.detail.ok') : t('closureDashboard.detail.pending')}
                                                </span>
                                            </div>
                                            {check.noteKey && (
                                                <p className="mt-1 text-[11px] text-state-blocked-text">{t(check.noteKey)}</p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.traceability')}</h4>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-text-main">
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.markedFinishedBy')}:</span> {activeWork.trazabilidad?.marcadoTerminadoPor || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.markedFinishedAt')}:</span> {activeWork.trazabilidad?.fechaMarcadoTerminado || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.endDateBy')}:</span> {activeWork.trazabilidad?.fechaFinInformadaPor || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.endDateAt')}:</span> {activeWork.trazabilidad?.fechaFinInformadaAt || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.amountUpdatedBy')}:</span> {activeWork.trazabilidad?.importeActualizadoPor || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.amountUpdatedAt')}:</span> {activeWork.trazabilidad?.importeActualizadoAt || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.closedBy')}:</span> {activeWork.trazabilidad?.cerradoPor || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.closedAt')}:</span> {activeWork.trazabilidad?.fechaCierre ? formatDateValue(activeWork.trazabilidad.fechaCierre) : '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.reopenedBy')}:</span> {activeWork.trazabilidad?.reabiertoPor || '-'}</p>
                                    <p><span className="text-text-hint">{t('closureDashboard.traceability.reopenedAt')}:</span> {activeWork.trazabilidad?.fechaReapertura ? formatDateValue(activeWork.trazabilidad.fechaReapertura) : '-'}</p>
                                </div>
                                {activeWork.trazabilidad?.reaperturaMotivo && (
                                    <p className="mt-2 text-xs text-text-main">
                                        <span className="text-text-hint">{t('closureDashboard.traceability.reopenReason')}:</span> {activeWork.trazabilidad.reaperturaMotivo}
                                    </p>
                                )}
                            </section>

                            <section className="rounded-[10px] border border-border bg-surface-2 p-4">
                                <h4 className="text-xs font-bold uppercase tracking-widest text-text-muted">{t('closureDashboard.detail.sections.finalAction')}</h4>

                                {detailStatus === 'cerrado' ? (
                                    <div className="mt-3 space-y-2">
                                        <label className="flex flex-col gap-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                            {t('closureDashboard.traceability.reopenReason')}
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
                                            {t('closureDashboard.actions.reopenWork')}
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
                                            className="inline-flex items-center rounded-md bg-(--ciete-red) px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark) disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {t('closureDashboard.actions.closeWork')}
                                        </button>

                                        {(detailStatus !== 'listo' || hasBlockingIssues) && (
                                            <p className="text-xs text-state-blocked-text">
                                                {t('closureDashboard.messages.cannotCloseYet')}
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
