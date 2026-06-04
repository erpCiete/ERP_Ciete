import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, usePage } from '@inertiajs/react';
import { ChevronDown, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

function Section({ id, title, icon, children, isOpen, onToggle, badges = [] }) {
    return (
        <div className="overflow-hidden rounded-xl border border-border bg-surface">
            <button
                type="button"
                onClick={() => onToggle(id)}
                className="flex w-full items-start justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-2 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset"
                aria-expanded={isOpen}
                aria-controls={`help-section-${id}`}
            >
                <span className="flex min-w-0 items-start gap-2.5">
                    <span className="pt-0.5 text-base">{icon}</span>
                    <span className="min-w-0">
                        <span className="block text-sm font-medium text-text-main">{title}</span>
                        {badges.length > 0 && (
                            <span className="mt-2 flex flex-wrap gap-1.5">
                                {badges.map((badge) => (
                                    <RoleBadge
                                        key={`${id}-${badge.label}`}
                                        label={badge.label}
                                        color={badge.color}
                                    />
                                ))}
                            </span>
                        )}
                    </span>
                </span>
                <ChevronDown
                    size={16}
                    className={`mt-0.5 shrink-0 text-text-hint transition-transform duration-200 ${
                        isOpen ? 'rotate-180' : ''
                    }`}
                />
            </button>

            {isOpen && (
                <div
                    id={`help-section-${id}`}
                    className="border-t border-border px-4 py-4 text-sm leading-relaxed text-text-muted"
                >
                    {children}
                </div>
            )}
        </div>
    );
}

function RoleBadge({ label, color = 'bg-accent/15 text-accent' }) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${color}`}
        >
            {label}
        </span>
    );
}

function HelpStep({ number, text }) {
    return (
        <div className="flex items-start gap-2.5">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">
                {number}
            </span>
            <span className="text-xs text-text-muted">{text}</span>
        </div>
    );
}

function HelpList({ items = [], className = '' }) {
    return (
        <ul className={`space-y-2 ${className}`.trim()}>
            {items.map((item, index) => (
                <li key={`${item}-${index + 1}`} className="flex items-start gap-2">
                    <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                    <span className="text-xs text-text-muted">{item}</span>
                </li>
            ))}
        </ul>
    );
}

function HelpCheckList({ items = [], className = '' }) {
    return (
        <ul className={`space-y-2 ${className}`.trim()}>
            {items.map((item, index) => (
                <li key={`${item}-${index + 1}`} className="flex items-start gap-2">
                    <span className="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-state-done-bg text-[10px] font-bold text-state-done-text">
                        ✓
                    </span>
                    <span className="text-xs text-text-muted">{item}</span>
                </li>
            ))}
        </ul>
    );
}

function HelpSubsection({ title, body, bullets = [], checks = [] }) {
    return (
        <div className="rounded-xl border border-border bg-surface-2/60 px-4 py-3">
            <h4 className="text-sm font-semibold text-text-main">{title}</h4>
            {body && <p className="mt-2 text-xs text-text-muted">{body}</p>}
            {bullets.length > 0 && <HelpList items={bullets} className="mt-3" />}
            {checks.length > 0 && <HelpCheckList items={checks} className="mt-3" />}
        </div>
    );
}

function HelpCallout({ text, tone = 'warning' }) {
    const tones = {
        info: 'border-primary/25 bg-primary/5 text-text-main',
        warning: 'border-state-pending-dot/25 bg-state-pending-bg text-state-pending-text',
        danger: 'border-state-blocked-dot/25 bg-state-blocked-bg text-state-blocked-text',
    };

    return (
        <div className={`rounded-lg border px-3 py-2 text-xs ${tones[tone] ?? tones.warning}`}>
            {text}
        </div>
    );
}

const HELP_CAT_ADMIN = 'admin';
const HELP_CAT_DIRECCION = 'direccion';
const HELP_CAT_RESTO = 'resto';

function getHelpCategory(user) {
    if (!user) return HELP_CAT_RESTO;

    const slugs = Array.isArray(user.role_slugs) ? user.role_slugs : [];

    if (user.can_access_admin_panel || slugs.includes('admin')) {
        return HELP_CAT_ADMIN;
    }

    if (
        user.is_director
        || user.can_access_direction_panel
        || slugs.includes('director')
        || slugs.includes('direccion')
    ) {
        return HELP_CAT_DIRECCION;
    }

    return HELP_CAT_RESTO;
}

const SECTION_ORDER_BY_CATEGORY = {
    [HELP_CAT_ADMIN]: [
        'intro',
        'roles',
        'maestros',
        'tickets',
        'trabajos',
        'pedidos',
        'facturacion_cierre',
        'exportacion',
        'password',
    ],
    [HELP_CAT_DIRECCION]: [
        'intro',
        'roles',
        'facturacion_cierre',
        'trabajos',
        'pedidos',
        'exportacion',
        'maestros',
        'tickets',
        'password',
    ],
    [HELP_CAT_RESTO]: [
        'intro',
        'roles',
        'trabajos',
        'pedidos',
        'exportacion',
        'facturacion_cierre',
        'tickets',
        'password',
        'maestros',
    ],
};

const HELP_MANUAL = {
    es: {
        sections: [
            {
                id: 'intro',
                icon: '⚡',
                title: 'Inicio rápido',
                badges: [
                    { label: 'Operativa diaria', color: 'bg-primary/15 text-primary' },
                ],
                intro:
                    'La operativa real parte de Trabajos. Desde ahí se fija el marco del caso y se encadenan pedido, exportación, facturación y cierre.',
                steps: [
                    'Entrar en `Trabajos` con el contexto correcto. Si el contexto activo es `Todos`, el ERP deja consultar, pero no crear nuevos registros.',
                    'Crear o localizar el trabajo. El número operativo puede autogenerarse y la estación debe pertenecer al contexto correcto.',
                    'Seleccionar o confirmar el tarifario del trabajo. En la vista Excel puede elegirse directamente y, si existe un predeterminado del contrato, se prioriza ese.',
                    'Crear el pedido desde el propio trabajo para mantener la relación trabajo -> pedido.',
                    'Añadir líneas, revisar importes y continuar con exportación Moeve o facturación según el caso.',
                ],
                notes: [
                    {
                        tone: 'info',
                        text: 'La ayuda muestra primero lo más útil para tu perfil, pero deja visibles las secciones avanzadas al final.',
                    },
                ],
            },
            {
                id: 'roles',
                icon: '🧭',
                title: 'Roles',
                badges: [
                    { label: 'Dirección', color: 'bg-state-progress-bg text-state-progress-text' },
                    { label: 'Ejecución', color: 'bg-primary/15 text-primary' },
                    { label: 'Contabilidad', color: 'bg-accent/15 text-accent' },
                    { label: 'Técnico / admin', color: 'bg-state-done-bg text-state-done-text' },
                ],
                intro:
                    'El ERP no se usa igual para todos. El alcance cambia por permisos, contexto activo y estado del registro.',
                subsections: [
                    {
                        title: 'Dirección',
                        bullets: [
                            'Visión global de trabajos, pedidos, facturas y bloqueos.',
                            'Revisión de cierre, validación y trazabilidad de cambios.',
                            'Consulta de maestros críticos cuando impactan en la operativa.',
                            'Seguimiento de auditoría y estados derivados antes de dar un caso por finalizado.',
                        ],
                    },
                    {
                        title: 'Ejecución',
                        bullets: [
                            'Trabaja principalmente en `Trabajos`.',
                            'Crea pedidos desde el trabajo y completa líneas desde el tarifario.',
                            'Revisa exportaciones si su perfil tiene permiso operativo.',
                            'Mantiene el caso vivo hasta dejarlo listo para facturar o revisar.',
                        ],
                    },
                    {
                        title: 'Contabilidad',
                        bullets: [
                            'Consulta pedidos y facturas como base económica.',
                            'Gestiona facturación parcial o completa sin perder vínculo con el trabajo.',
                            'Usa los estados derivados para detectar qué queda pendiente.',
                        ],
                    },
                    {
                        title: 'Técnico / admin',
                        bullets: [
                            'Gestiona usuarios, permisos y mantenimiento de soporte.',
                            'Revisa catálogos y maestros de sistema cuando afectan al flujo.',
                            'Mantiene auditoría, avisos y administración del soporte interno.',
                        ],
                    },
                ],
            },
            {
                id: 'trabajos',
                icon: '🛠️',
                title: 'Trabajos',
                badges: [
                    { label: 'Ejecución', color: 'bg-primary/15 text-primary' },
                    { label: 'Dirección', color: 'bg-state-progress-bg text-state-progress-text' },
                ],
                intro:
                    '`Trabajos` es la pantalla principal operativa. El dato correcto aquí evita errores posteriores en pedidos, exportaciones, facturas y cierre.',
                subsections: [
                    {
                        title: 'Búsqueda, filtros y ordenación',
                        bullets: [
                            'La vista soporta búsqueda amplia por número, estación, pedido, contrato, tarifario, responsable e importes.',
                            'La vista Excel añade filtros avanzados por estación, categoría, tarifario, pedido, multipedido y estados económicos.',
                            'Las columnas ordenables siguen ciclo de 3 clics: ascendente, descendente y vuelta al orden por defecto.',
                        ],
                    },
                    {
                        title: 'Alta y datos clave',
                        bullets: [
                            'Puede crearse desde formulario o desde la vista Excel, según el flujo de cada perfil.',
                            'El número de trabajo y el número operativo CIETE pueden autogenerarse cuando aplica.',
                            'La estación se elige dentro del contexto correcto. En la vista Excel puede buscarse por código o nombre; en el formulario estándar se selecciona desde el listado disponible.',
                            'La categorización cambia por cliente: en Moeve se usa categoría/contrato; en Repsol se usa tipo documental, tipo de trabajo y, si aplica, número de aviso.',
                        ],
                    },
                    {
                        title: 'Contrato y tarifario',
                        bullets: [
                            'El trabajo debe quedar con el marco económico correcto antes del primer pedido.',
                            'Cuando existe tarifario predeterminado del contrato, el sistema lo prioriza.',
                            'En la vista Excel el tarifario puede seleccionarse y guardarse en la fila del trabajo.',
                            'Si el trabajo ya tiene pedidos, el tarifario queda fijado y deja de editarse en esa fila para no romper coherencia.',
                        ],
                    },
                    {
                        title: 'Pedidos asociados',
                        bullets: [
                            'Los pedidos del trabajo se muestran listados por orden de creación.',
                            'Desde la misma fila puedes crear el primer pedido o crear otro adicional si el caso lo necesita.',
                            'El trabajo conserva la trazabilidad del resumen económico total aunque haya varios pedidos.',
                        ],
                    },
                ],
                warnings: [
                    'No cambies estación, contrato o tarifario para cuadrar un caso ya avanzado. Si el alta quedó mal, corrígelo con criterio y antes de seguir generando dependencias.',
                ],
            },
            {
                id: 'pedidos',
                icon: '🧾',
                title: 'Pedidos',
                badges: [
                    { label: 'Ejecución', color: 'bg-primary/15 text-primary' },
                    { label: 'Contabilidad', color: 'bg-accent/15 text-accent' },
                ],
                intro:
                    'El pedido nace del trabajo y hereda su marco de datos. No sustituye al trabajo: sirve para controlar líneas, importes, exportación y paso posterior a facturación.',
                subsections: [
                    {
                        title: 'Cómo se crean',
                        bullets: [
                            'Se crean desde el trabajo para arrastrar contexto, trabajo y tarifario correctos.',
                            'El backend valida que el pedido herede el mismo tarifario del trabajo.',
                            'Si el trabajo no tiene tarifario válido, el pedido no debe abrirse como si fuera completo.',
                        ],
                    },
                    {
                        title: 'Líneas del pedido',
                        bullets: [
                            'Las líneas salen del tarifario del trabajo o, en su defecto, del contrato operativo asociado.',
                            'El selector permite localizar la línea por código, número de tarifa o descripción.',
                            'Las cantidades admiten decimales y los importes se recalculan automáticamente.',
                            'Si una línea no pertenece al trabajo, al contexto o al tarifario correcto, el sistema la rechaza.',
                        ],
                    },
                    {
                        title: 'Uso operativo',
                        bullets: [
                            'Pedidos funciona como vista de consulta y control económico, además de edición.',
                            'Sirve para contrastar importes solicitados, pedido emitido y facturación posterior.',
                            'No conviene mantener pedidos vacíos solo para reservar un hueco en el flujo.',
                        ],
                    },
                ],
            },
            {
                id: 'exportacion',
                icon: '📤',
                title: 'Exportación Moeve',
                badges: [
                    { label: 'Moeve', color: 'bg-primary/15 text-primary' },
                ],
                intro:
                    'La exportación Moeve sale desde `Pedidos` y solo tiene sentido cuando el pedido ya es consistente.',
                subsections: [
                    {
                        title: 'Salidas disponibles',
                        bullets: [
                            'PDF en HTML imprimible.',
                            'CSV.',
                            'Cuadro `ARIBA - TRAMITACION DE PEDIDOS`.',
                            'El remate actual se alineó con el caso real revisado de `La Senyera` como referencia operativa.',
                        ],
                    },
                    {
                        title: 'Validaciones antes de exportar',
                        bullets: [
                            'No debe exportarse un pedido vacío.',
                            'No debe exportarse un pedido con líneas inconsistentes o importes sin recalcular.',
                            'El backend exige trabajo, tarifario y líneas válidas antes de generar la salida.',
                        ],
                    },
                    {
                        title: 'Campos pendientes de parametrización',
                        bullets: [
                            'Si faltan datos de negocio no modelados todavía, deben aparecer como pendientes visibles, no inventados.',
                            'Esto afecta especialmente a algunos campos del bloque ARIBA y variantes de proveedor/contrato.',
                        ],
                    },
                ],
                notes: [
                    {
                        tone: 'info',
                        text: 'La exportación es operativa, pero sigue dependiendo de la parametrización real del caso para remates de formato o campos de negocio.',
                    },
                ],
            },
            {
                id: 'facturacion_cierre',
                icon: '📊',
                title: 'Facturación y cierre',
                badges: [
                    { label: 'Contabilidad', color: 'bg-accent/15 text-accent' },
                    { label: 'Dirección / cierre', color: 'bg-state-progress-bg text-state-progress-text' },
                ],
                intro:
                    'Facturar y cerrar no es esconder el caso: es dejarlo trazado y coherente con lo realmente ejecutado.',
                subsections: [
                    {
                        title: 'Facturación parcial y completa',
                        bullets: [
                            'La facturación puede ser parcial o completa según el importe realmente cubierto.',
                            'El sistema usa esa relación para calcular estados derivados del trabajo.',
                            'Si pedido y factura no cuadran todavía, el trabajo debe seguir visible para revisión.',
                        ],
                    },
                    {
                        title: 'Estados derivados',
                        bullets: [
                            'Estados como `pendiente_facturar`, `facturado` o `finalizado` no deben tratarse como etiquetas cosméticas.',
                            'El ERP calcula parte de esos estados desde pedidos, factura y cierre.',
                            'Solo los estados manuales admitidos deben tocarse a mano.',
                        ],
                    },
                    {
                        title: 'Cierre secundario y finalización',
                        bullets: [
                            'El panel de cierre sirve para revisar si el trabajo está listo para cerrarse o sigue bloqueado.',
                            '`Finalizado` solo debe usarse cuando ya corresponde por trazabilidad económica y documental.',
                            'Cada cierre o reapertura relevante deja rastro en auditoría.',
                        ],
                    },
                ],
                warnings: [
                    'No cambies estados para limpiar paneles ni cierres un trabajo si aún arrastra incoherencias de pedido, factura o documentación.',
                ],
            },
            {
                id: 'maestros',
                icon: '🗂️',
                title: 'Maestros',
                badges: [
                    { label: 'Dirección', color: 'bg-state-progress-bg text-state-progress-text' },
                    { label: 'Técnico / admin', color: 'bg-state-done-bg text-state-done-text' },
                ],
                intro:
                    'Los maestros definen la base del ERP. Si aquí se mezcla o se duplica, el error se propaga a toda la operativa.',
                subsections: [
                    {
                        title: 'Árbol operativo',
                        body: '`Empresa / cliente -> Sociedades / CIF -> Contratos -> Tarifarios -> Líneas de tarifa`',
                    },
                    {
                        title: 'Reglas clave',
                        bullets: [
                            'El tarifario predeterminado se define por contrato y debe ser único cuando existe esa marca.',
                            'Desde maestros puede marcarse qué tarifario queda como predeterminado.',
                            'Si contrato o tarifario tienen histórico operativo, el ERP desactiva en lugar de borrar.',
                            'Clientes, estaciones, contratos y tarifarios deben mantenerse en el contexto correcto.',
                        ],
                    },
                    {
                        title: 'Qué toca cada perfil',
                        bullets: [
                            'Dirección valida maestros críticos cuando afectan a cierre, facturación o trabajo diario.',
                            'Técnico/admin mantiene catálogos de sistema, usuarios y permisos.',
                            'Los catálogos puramente técnicos no deben tocarse como si fueran operativa diaria.',
                        ],
                    },
                ],
                notes: [
                    {
                        tone: 'info',
                        text: 'Si aparece un diagnóstico o un aviso de dependencia rota, corrígelo en maestros antes de seguir creando trabajos, pedidos o facturas.',
                    },
                ],
            },
            {
                id: 'tickets',
                icon: '🎫',
                title: 'Tickets de soporte interno',
                badges: [
                    { label: 'Soporte', color: 'bg-primary/15 text-primary' },
                    { label: 'Admin', color: 'bg-state-done-bg text-state-done-text' },
                ],
                intro:
                    'El soporte interno ya existe y debe usarse para incidencias con seguimiento real, no como comentario informal.',
                subsections: [
                    {
                        title: 'Como solicitante',
                        bullets: [
                            'Crear ticket desde `/soporte` con tema, asunto y detalle útil.',
                            'Comentar sobre el mismo ticket cuando haya nueva información.',
                            'Evitar duplicados si el caso ya está abierto.',
                        ],
                    },
                    {
                        title: 'Gestión por soporte/admin',
                        bullets: [
                            'La asignación, prioridad, cambio de estado y respuesta de gestión se controlan desde `admin/soporte`.',
                            'Estados habituales: pendiente, en revisión, resuelto o archivado según el caso.',
                            'El cierre real del ticket debe dejar trazabilidad y no perder el histórico de comentarios.',
                        ],
                    },
                ],
                warnings: [
                    'No uses soporte para maquillar errores de datos ya conocidos. Primero corrige la operativa y luego documenta la incidencia si sigue existiendo.',
                ],
            },
            {
                id: 'password',
                icon: '🔑',
                title: 'Recuperación de contraseña',
                badges: [
                    { label: 'Acceso', color: 'bg-accent/15 text-accent' },
                ],
                intro:
                    'La recuperación de acceso se inicia desde la ruta estándar del ERP.',
                subsections: [
                    {
                        title: 'Pasos',
                        checks: [
                            'Ir a `/forgot-password`.',
                            'Introducir el correo asociado al usuario.',
                            'Seguir el enlace recibido para definir la nueva contraseña.',
                        ],
                    },
                    {
                        title: 'Entorno local o demo',
                        bullets: [
                            'En local/demo el envío real depende de la configuración de correo del entorno.',
                            'Si el mensaje no llega, revisa spam y confirma con técnico/admin si ese entorno tiene correo saliente activo.',
                        ],
                    },
                ],
            },
        ],
    },
};

function renderSectionContent(section) {
    return (
        <div className="space-y-4">
            <p>{section.intro}</p>

            {section.steps?.length > 0 && (
                <div className="space-y-2">
                    {section.steps.map((step, index) => (
                        <HelpStep
                            key={`${section.id}-step-${index + 1}`}
                            number={index + 1}
                            text={step}
                        />
                    ))}
                </div>
            )}

            {section.subsections?.length > 0 && (
                <div className="space-y-3">
                    {section.subsections.map((subsection) => (
                        <HelpSubsection
                            key={`${section.id}-${subsection.title}`}
                            title={subsection.title}
                            body={subsection.body}
                            bullets={subsection.bullets}
                            checks={subsection.checks}
                        />
                    ))}
                </div>
            )}

            {section.checks?.length > 0 && <HelpCheckList items={section.checks} />}

            {section.tips?.length > 0 && (
                <div className="space-y-2">
                    {section.tips.map((tip, index) => (
                        <HelpCallout key={`${section.id}-tip-${index + 1}`} text={tip} tone="info" />
                    ))}
                </div>
            )}

            {section.warnings?.length > 0 && (
                <div className="space-y-2">
                    {section.warnings.map((warning, index) => (
                        <HelpCallout
                            key={`${section.id}-warning-${index + 1}`}
                            text={warning}
                            tone="warning"
                        />
                    ))}
                </div>
            )}

            {section.notes?.length > 0 && (
                <div className="space-y-2">
                    {section.notes.map((note, index) => (
                        <HelpCallout
                            key={`${section.id}-note-${index + 1}`}
                            text={typeof note === 'string' ? note : note.text}
                            tone={typeof note === 'string' ? 'info' : note.tone}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

function collectSearchStrings(value) {
    if (!value) return [];

    if (typeof value === 'string') return [value];

    if (Array.isArray(value)) {
        return value.flatMap((item) => collectSearchStrings(item));
    }

    if (typeof value === 'object') {
        return Object.values(value).flatMap((item) => collectSearchStrings(item));
    }

    return [];
}

function normalizeSearchText(value) {
    return collectSearchStrings(value)
        .join(' ')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
}

function sortSectionsForCategory(sections, category) {
    const priority = SECTION_ORDER_BY_CATEGORY[category] ?? SECTION_ORDER_BY_CATEGORY[HELP_CAT_RESTO];
    const priorityMap = new Map(priority.map((id, index) => [id, index]));

    return [...sections].sort((left, right) => {
        const leftRank = priorityMap.get(left.id) ?? Number.MAX_SAFE_INTEGER;
        const rightRank = priorityMap.get(right.id) ?? Number.MAX_SAFE_INTEGER;

        return leftRank - rightRank;
    });
}

export default function Help() {
    const user = usePage().props.auth.user;
    const { t, locale } = useI18n();
    const [openSections, setOpenSections] = useState(new Set(['intro', 'roles']));
    const [search, setSearch] = useState('');

    const manual = HELP_MANUAL[locale] ?? HELP_MANUAL.es;
    const category = getHelpCategory(user);

    const toggleSection = (id) => {
        setOpenSections((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    const orderedSections = useMemo(
        () => sortSectionsForCategory(manual.sections, category),
        [manual.sections, category],
    );

    const filteredSections = useMemo(() => {
        const query = search.trim();
        if (!query) return orderedSections;

        const normalizedQuery = normalizeSearchText(query);

        return orderedSections.filter((section) =>
            normalizeSearchText(section).includes(normalizedQuery),
        );
    }, [orderedSections, search]);

    const categoryConfig = {
        [HELP_CAT_ADMIN]: {
            badge: { label: 'Admin', color: 'bg-primary/15 text-primary' },
            banner: {
                es: 'Manual completo. Primero aparecen maestros, soporte y control, y debajo queda la operativa general.',
            },
        },
        [HELP_CAT_DIRECCION]: {
            badge: { label: 'Dirección / cierre', color: 'bg-state-progress-bg text-state-progress-text' },
            banner: {
                es: 'Guía de dirección. Se priorizan facturación, cierre y revisión global, sin ocultar la operativa diaria.',
            },
        },
        [HELP_CAT_RESTO]: {
            badge: { label: 'Operativa diaria', color: 'bg-accent/15 text-accent' },
            banner: {
                es: 'Guía operativa. Se priorizan trabajos, pedidos y exportación; lo avanzado queda más abajo.',
            },
        },
    };

    const cfg = categoryConfig[category] ?? categoryConfig[HELP_CAT_RESTO];
    const banner = cfg.banner[locale] ?? cfg.banner.es;

    return (
        <AuthenticatedLayout header={t('help.header')}>
            <Head title={t('help.headTitle')} />

            <div className="ciete-page ciete-page-reading">
                <div>
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                        {t('help.eyebrow')}
                    </p>
                    <h2 className="text-xl font-semibold text-text-main">{t('help.title')}</h2>
                    <p className="mt-1 text-sm text-text-muted">{t('help.description')}</p>
                </div>

                <div className="flex items-start gap-3 rounded-xl border border-border bg-surface-2 px-4 py-3">
                    <RoleBadge label={cfg.badge.label} color={cfg.badge.color} />
                    <p className="text-xs text-text-muted">{banner}</p>
                </div>

                <div className="relative">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-text-hint" />
                    <input
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('help.searchPlaceholder')}
                        className="w-full rounded-xl border border-border bg-surface py-2.5 pl-9 pr-4 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div className="space-y-2">
                    {filteredSections.map((section) => (
                        <Section
                            key={section.id}
                            id={section.id}
                            title={section.title}
                            icon={section.icon}
                            badges={section.badges}
                            isOpen={search.trim() ? true : openSections.has(section.id)}
                            onToggle={toggleSection}
                        >
                            {renderSectionContent(section)}
                        </Section>
                    ))}

                    {filteredSections.length === 0 && (
                        <div className="py-12 text-center text-sm text-text-hint">
                            {t('help.noResults')}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
