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

function HelpField({ label, description }) {
    return (
        <div className="flex flex-col gap-0.5 rounded-lg bg-surface-2 px-3 py-2">
            <span className="text-xs font-semibold text-text-main">{label}</span>
            <span className="text-xs text-text-muted">{description}</span>
        </div>
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

function HelpFaqItem({ question, answer }) {
    return (
        <div className="rounded-lg bg-surface-2 px-3 py-2">
            <p className="text-xs font-semibold text-text-main">{question}</p>
            <p className="mt-1 text-xs text-text-muted">{answer}</p>
        </div>
    );
}

const HELP_CAT_ADMIN     = 'admin';
const HELP_CAT_DIRECCION = 'direccion';
const HELP_CAT_RESTO     = 'resto';

/**
 * Normalise the authenticated user into one of three manual categories:
 *   HELP_CAT_ADMIN     → can_access_admin_panel
 *   HELP_CAT_DIRECCION → is_director / can_access_direction_panel
 *   HELP_CAT_RESTO     → all other authenticated users (ejecucion*, contable, …)
 */
function getHelpCategory(user) {
    if (!user) return HELP_CAT_RESTO;
    const slugs = Array.isArray(user.role_slugs) ? user.role_slugs : [];
    if (user.can_access_admin_panel || slugs.includes('admin')) {
        return HELP_CAT_ADMIN;
    }
    if (
        user.is_director ||
        user.can_access_direction_panel ||
        slugs.includes('director') ||
        slugs.includes('direccion')
    ) {
        return HELP_CAT_DIRECCION;
    }
    return HELP_CAT_RESTO;
}

/**
 * Section visibility map — three categories: 'admin', 'direccion', 'resto', 'todos'.
 * Admin always sees every section regardless of this map.
 */
const SECTION_AUDIENCE = {
    intro:              ['todos'],
    access:             ['todos'],
    daily_flow:         ['todos'],
    home:               ['todos'],
    dashboard:          ['todos'],
    navigation:         ['todos'],
    contexts:           ['todos'],
    roles:              ['todos'],
    tables:             ['todos'],
    forms:              ['todos'],
    works:              ['todos'],
    orders:             ['todos'],
    invoices:           ['todos'],
    tarifarios:         ['todos'],
    maestros:           ['admin', 'direccion'],
    legalizations:      ['todos'],
    closure:            ['admin', 'direccion'],
    messages:           ['todos'],
    support:            ['todos'],
    admin:              ['admin'],
    audit:              ['admin', 'direccion'],
    imports:            ['admin'],
    reports:            ['todos'],
    devices:            ['todos'],
    states_diagnostics: ['todos'],
    best_practices:     ['todos'],
    faq:                ['todos'],
};

const HELP_MANUAL = {
    es: {
        sections: [
            {
                id: 'intro',
                icon: '📘',
                title: 'Introducción al ERP CIETE',
                intro:
                    'Este manual explica la operativa real del ERP de CIETE. El sistema nace para sustituir flujos históricos basados en Excel, evitar datos planos sin control de cambios, reducir problemas de edición simultánea y separar de forma fiable la información por cliente, contexto, estación, estado y usuario.',
                subsections: [
                    {
                        title: 'Por qué existe este ERP',
                        body:
                            'CIETE trabajó durante años con hojas Excel para Repsol y para Cepsa, hoy MOEVE. Ese modelo servía para arrancar, pero no daba un control sólido cuando el volumen crecía, aparecían varias personas editando a la vez o hacía falta explotar informes por cliente sin mezclar datos.',
                        bullets: [
                            'Evita versiones paralelas del mismo fichero.',
                            'Permite saber qué usuario cambió un dato y en qué momento.',
                            'Ayuda a no dejar trabajos hechos sin pedido, sin factura o sin trazabilidad.',
                            'Permite consultar histórico por estación, cliente y contexto sin reconstruirlo manualmente.',
                        ],
                    },
                    {
                        title: 'Qué lógica sigue el sistema',
                        bullets: [
                            'Trabajos es el registro central y articula el resto del flujo.',
                            'Pedidos y facturas completan la parte económica y de control.',
                            'Clientes y estaciones deben mantenerse limpios para no contaminar los datos operativos.',
                            'Legalizaciones, cierre, auditoría, soporte e importaciones completan la gestión.',
                        ],
                    },
                    {
                        title: 'Cómo usar este manual',
                        bullets: [
                            'Úsalo como referencia diaria, no solo como ayuda puntual.',
                            'Si eres usuario nuevo, empieza por contextos, navegación, tablas y formularios.',
                            'Si validas cierres, importaciones o incidencias, revisa también auditoría y cierre.',
                        ],
                    },
                ],
                warnings: [
                    'No trabajes en el ERP como si fuera una hoja libre. Cada módulo tiene un propósito, un orden operativo y reglas de trazabilidad.',
                ],
            },
            {
                id: 'access',
                icon: '🔐',
                title: 'Acceso, sesión y buenas prácticas',
                intro:
                    'Entrar al ERP no es solo iniciar sesión. También implica revisar que estás en la cuenta correcta, con el contexto correcto y con los filtros adecuados antes de empezar a editar.',
                subsections: [
                    {
                        title: 'Al entrar en el sistema',
                        checks: [
                            'Confirma que usas tu cuenta y no una sesión abierta de otro usuario.',
                            'Revisa el contexto activo antes de crear o modificar datos.',
                            'Comprueba si el idioma, el tema y los accesos visibles corresponden a tu perfil.',
                        ],
                    },
                    {
                        title: 'Buenas prácticas de sesión',
                        bullets: [
                            'No dejes formularios largos abiertos durante mucho tiempo si la operativa puede haber cambiado mientras tanto.',
                            'Cuando guardes una acción sensible, vuelve al listado y valida el resultado.',
                            'Si no ves un módulo o una acción, revisa primero permisos, rol y contexto antes de reportar una incidencia.',
                        ],
                    },
                    {
                        title: 'Qué evitar',
                        bullets: [
                            'No compartas una sesión activa con otro usuario.',
                            'No asumas que la ausencia de un dato significa error si hay filtros activos.',
                            'No sigas editando cuando el sistema muestra bloqueos por estado o cierre sin revisar la causa.',
                        ],
                    },
                ],
                tips: [
                    'Si cambias de cliente o de tipo de trabajo durante la jornada, revisa el contexto al volver a crear registros.',
                ],
            },
            {
                id: 'daily_flow',
                icon: '🗓️',
                title: 'Flujo diario recomendado',
                intro:
                    'Sigue este orden de operativa habitual para reducir errores, duplicados y descuadres entre módulos.',
                steps: [
                    'Revisar el contexto activo antes de empezar: MOEVE, REPSOL u OTROS CLIENTES.',
                    'Buscar el trabajo existente en el listado. Solo crear uno nuevo si no existe; nunca crear sin buscar antes.',
                    'Verificar que la estación pertenece al cliente del contexto correcto. Revisar el contrato o tarifario asignado al trabajo si aplica.',
                    'Abrir o crear el pedido asociado al trabajo. Seleccionar la línea de tarifario correspondiente en cada ítem.',
                    'Revisar importes, unidades y líneas del pedido antes de confirmar.',
                    'Crear o revisar la factura del trabajo. Comprobar la sociedad facturadora y los datos económicos.',
                    'Usar los diagnósticos funcionales disponibles en Maestros y Tarifarios cuando aparezcan avisos.',
                    'Si el trabajo ya está terminado, revisarlo desde el panel de cierre si tu perfil lo permite.',
                ],
                tips: [
                    'Si vas a revisar varios módulos seguidos, vuelve al listado antes de saltar al siguiente para no perder referencias abiertas.',
                    'Revisa los avisos del inicio antes de iniciar tareas de cierre, importación o validación masiva.',
                ],
            },
            {
                id: 'home',
                icon: '🏠',
                title: 'Home e inicio del sistema',
                intro:
                    'La pantalla inicial sirve para orientarte, consultar avisos y abrir accesos rápidos. La operativa diaria ocurre en los módulos, pero Home ayuda a empezar con contexto.',
                fields: [
                    {
                        label: 'Avisos internos',
                        description:
                            'Recogen cambios de criterio, incidencias temporales, recordatorios operativos y mensajes breves relevantes para el equipo.',
                    },
                    {
                        label: 'Accesos rápidos',
                        description:
                            'La ayuda contextual permite abrir mensajes, ayuda, soporte y estado del sistema desde cualquier pantalla autenticada.',
                    },
                    {
                        label: 'Uso recomendado',
                        description:
                            'Úsala para comprobar si hay avisos antes de entrar en listados o antes de continuar una tarea iniciada otro día.',
                    },
                ],
                steps: [
                    'Lee los avisos del día antes de continuar con cierres, importaciones o validaciones masivas.',
                    'Confirma el módulo al que vas a entrar y desde ahí empieza la operativa.',
                ],
            },
            {
                id: 'dashboard',
                icon: '📊',
                title: 'Dashboard y paneles de resumen',
                intro:
                    'Los paneles de resumen están pensados para detectar carga, pendientes y situaciones anómalas. No sustituyen la revisión del registro real.',
                subsections: [
                    {
                        title: 'Cómo interpretar una métrica',
                        bullets: [
                            'Una cifra alta o baja no implica por sí sola que haya un error.',
                            'Cada tarjeta debe leerse junto al listado o módulo que la alimenta.',
                            'Los paneles operativos, los de administración y los de cierre no persiguen la misma lectura.',
                        ],
                    },
                    {
                        title: 'Uso práctico',
                        bullets: [
                            'Úsalo para priorizar revisión de pendientes, no para cambiar estados a ciegas.',
                            'Si detectas un volumen extraño, abre el listado, revisa contexto y valida filtros antes de tocar datos.',
                            'En cargas altas, combina dashboard con búsquedas y filtros para localizar el grupo exacto de registros.',
                        ],
                    },
                ],
                warnings: [
                    'No cambies estados solo para que un panel se vea mejor. El estado debe reflejar la situación real del registro.',
                ],
                checks: [
                    'Relaciona la métrica con el listado de origen.',
                    'Confirma el contexto activo antes de interpretar cualquier cifra.',
                ],
            },
            {
                id: 'navigation',
                icon: '🧭',
                title: 'Navegación general',
                intro:
                    'La navegación está pensada para moverse rápido entre áreas sin perder orientación. En móvil se compacta, pero la lógica de trabajo sigue siendo la misma.',
                fields: [
                    {
                        label: 'Sidebar',
                        description:
                            'Agrupa los módulos por bloques de trabajo y es la vía principal para entrar en operaciones, maestros, soporte y paneles especiales.',
                    },
                    {
                        label: 'Navbar',
                        description:
                            'Resume dónde estás, muestra acciones superiores y se compacta en pantallas pequeñas para no robar ancho al contenido.',
                    },
                    {
                        label: 'Buscador del manual',
                        description:
                            'Permite localizar palabras clave del manual como trabajo, pedido, factura, cierre, contexto o importación.',
                    },
                ],
                steps: [
                    'Entra al módulo desde el sidebar o desde el acceso superior disponible.',
                    'Revisa el título de la pantalla y los filtros visibles antes de editar.',
                    'En móvil, cierra el panel lateral después de navegar para recuperar ancho útil.',
                ],
                tips: [
                    'Si vas a revisar varios módulos encadenados, vuelve siempre al listado antes de saltar al siguiente para no perder referencias abiertas.',
                ],
            },
            {
                id: 'contexts',
                icon: '🧩',
                title: 'Contextos de trabajo: MOEVE, REPSOL y otros clientes',
                intro:
                    'Un contexto define el ámbito de datos con el que estás trabajando. No es una etiqueta visual. Sirve para separar clientes, estaciones, trabajos, pedidos, facturas e informes que no deben mezclarse.',
                subsections: [
                    {
                        title: 'Qué es un contexto',
                        body:
                            'El contexto delimita qué datos puedes consultar y sobre cuáles puedes operar. Es la base para mantener separadas las operativas de MOEVE, REPSOL y otros clientes cuando comparten estructura pero no deben compartir registros.',
                        bullets: [
                            'Evita ver trabajos de clientes mezclados por error.',
                            'Permite separar informes por cliente sin reprocesar datos a mano.',
                            'Ayuda a mantener estaciones, contratos y documentación en su ámbito correcto.',
                        ],
                    },
                    {
                        title: 'Si tienes un solo contexto',
                        bullets: [
                            'Trabaja siempre dentro de ese ámbito.',
                            'Si no aparece una estación o un cliente, valida permisos antes de crear registros nuevos.',
                        ],
                    },
                    {
                        title: 'Si tienes varios contextos',
                        checks: [
                            'Confirma el contexto antes de crear un trabajo, pedido o factura.',
                            'Comprueba que la estación pertenece al cliente correcto.',
                            'Revisa que no exista ya un registro equivalente en otro contexto visible para tu perfil.',
                        ],
                    },
                    {
                        title: 'Diferencias habituales por cliente',
                        bullets: [
                            'REPSOL suele trabajar con número de aviso como dato previo al pedido o a la factura según el flujo acordado.',
                            'MOEVE, antigua CEPSA, puede requerir lógica de autofacturación.',
                            'Otros clientes pueden compartir módulos, pero no deben contaminar estaciones, informes o trabajos de MOEVE y REPSOL.',
                        ],
                    },
                ],
                warnings: [
                    'Si una estación no aparece, revisa primero el contexto activo antes de darla de alta de nuevo.',
                    'No mezcles estaciones, trabajos o informes de REPSOL y MOEVE aunque el nombre comercial se parezca.',
                ],
            },
            {
                id: 'roles',
                icon: '🛡️',
                title: 'Roles, permisos y visibilidad',
                intro:
                    'El manual está organizado en tres categorías según el perfil del usuario: Admin, Dirección/Cierre y Resto de usuarios. Si una funcionalidad no aparece, puede deberse al perfil, al estado del registro o al contexto activo.',
                subsections: [
                    {
                        title: 'Admin',
                        body:
                            'Acceso completo al manual y al sistema. Puede gestionar usuarios, roles, contextos, mantenimiento, importaciones y configuración global.',
                    },
                    {
                        title: 'Dirección / Cierre',
                        body:
                            'Ve la parte operativa y el panel de cierre. Puede acceder a auditoría y maestros si el perfil tiene esos permisos asignados. No ve la sección de administración técnica ni gestión de usuarios.',
                    },
                    {
                        title: 'Resto de usuarios',
                        body:
                            'Perfil operativo general. Incluye trabajos, pedidos, facturas, tarifarios, mensajes y soporte. No ve el panel de cierre ni la sección de administración. Las variantes de ejecución MOEVE y REPSOL son contextos de alcance dentro de este mismo perfil.',
                    },
                    {
                        title: 'Antes de reportar un problema de acceso',
                        checks: [
                            'Revisa si la sección o acción corresponde a tu categoría de perfil.',
                            'Comprueba si el registro está finalizado o bloqueado por estado.',
                            'Valida si el dato está en otro contexto no visible para tu perfil.',
                        ],
                    },
                ],
                tips: [
                    'Cuando una acción no aparezca, no des por hecho que es un fallo. Puede estar oculta por permisos, por el estado del registro o porque el contexto activo no incluye ese dato.',
                ],
            },
            {
                id: 'tables',
                icon: '📋',
                title: 'Tablas, búsquedas, filtros y paginación',
                intro:
                    'Con muchos registros al año, una mala búsqueda genera duplicados, errores de revisión y cierres incorrectos. Las tablas son la base de la consulta diaria.',
                subsections: [
                    {
                        title: 'Cómo consultar bien',
                        bullets: [
                            'Busca por número, estación, cliente, descripción, aviso, pedido o referencia conocida.',
                            'Combina filtros de estado con rangos de fecha cuando el volumen sea alto.',
                            'Mantén los filtros activos durante la paginación para revisar lotes de forma ordenada.',
                        ],
                    },
                    {
                        title: 'Qué hacer antes de editar desde un listado',
                        checks: [
                            'Comprueba que el filtro de contexto es el esperado.',
                            'Verifica que estás en la página correcta del listado.',
                            'Abre el registro correcto antes de asumir que falta un dato.',
                        ],
                    },
                    {
                        title: 'Tablas grandes en pantallas pequeñas',
                        body:
                            'En móvil o en anchos reducidos algunas tablas pueden desplazarse horizontalmente dentro de su propio bloque. Es el comportamiento esperado para evitar scroll horizontal global en toda la página.',
                    },
                ],
                fields: [
                    {
                        label: 'Búsqueda libre',
                        description:
                            'Sirve para localizar rápidamente registros concretos sin recorrer páginas completas.',
                    },
                    {
                        label: 'Filtros por estado',
                        description:
                            'Ayudan a preparar listas de trabajo: pendientes, en revisión, terminados, bloqueados o finalizados.',
                    },
                    {
                        label: 'Paginación',
                        description:
                            'Permite revisar grandes volúmenes por tramos sin perder el criterio aplicado.',
                    },
                ],
                warnings: [
                    'No edites ni elimines desde un listado sin revisar los filtros activos. Un filtro mal entendido puede hacerte leer el registro fuera de contexto.',
                ],
            },
            {
                id: 'forms',
                icon: '📝',
                title: 'Formularios: crear, editar y validar datos',
                intro:
                    'Los formularios están pensados para guardar datos estructurados, trazables y reutilizables. No sustituyen una conversación interna ni un comentario informal.',
                subsections: [
                    {
                        title: 'Antes de guardar',
                        checks: [
                            'Busca primero si el registro ya existe.',
                            'Confirma el contexto activo.',
                            'Revisa si la estación, el cliente o el trabajo asociados son correctos.',
                            'Comprueba que el estado corresponde a la situación real.',
                        ],
                    },
                    {
                        title: 'Cómo decidir entre crear y editar',
                        bullets: [
                            'Si ya existe un registro equivalente, corrige el existente en lugar de crear uno nuevo.',
                            'Si dudas, vuelve al listado y busca por más de un criterio antes de duplicar datos.',
                            'Usa observaciones para aclarar un caso, no para sustituir campos estructurados ya existentes.',
                        ],
                    },
                    {
                        title: 'Validación funcional',
                        bullets: [
                            'Si el formulario tiene bloques específicos por cliente, revísalos aunque no todos apliquen siempre.',
                            'No des por válido un dato económico si aún no cuadra con trabajo, contrato o pedido.',
                            'Si el sistema marca un error de validación, corrígelo antes de avanzar.',
                        ],
                    },
                ],
                tips: [
                    'En móvil, recorre el formulario completo antes de guardar para comprobar que no dejas botones o bloques finales sin revisar.',
                ],
            },
            {
                id: 'works',
                icon: '🔧',
                title: 'Módulo de trabajos',
                intro:
                    'Trabajos es el registro central de la operativa. Desde aquí se conectan pedidos, facturas, legalizaciones y cierre. Si un trabajo se crea mal o duplicado, todo lo que venga detrás pierde fiabilidad.',
                subsections: [
                    {
                        title: 'Qué representa un trabajo',
                        body:
                            'Un trabajo representa una actuación real sobre una estación, un cliente o un expediente. Debe quedar en el contexto correcto y con suficiente información para sostener seguimiento, facturación, auditoría y cierre.',
                        bullets: [
                            'Es el origen operativo del flujo.',
                            'Debe poder consultarse históricamente por estación.',
                            'Permite controlar estado, fechas, trazabilidad y relación con módulos posteriores.',
                        ],
                    },
                    {
                        title: 'Cuándo se usa',
                        bullets: [
                            'Cuando entra un nuevo encargo o actuación real.',
                            'Cuando hay que regularizar una actuación existente que todavía no tenía registro operativo estructurado.',
                            'Cuando el trabajo debe quedar preparado para asociarle más adelante pedido, factura o legalización.',
                        ],
                    },
                    {
                        title: 'Cómo consultar trabajos',
                        bullets: [
                            'Busca por número de trabajo, estación, descripción, aviso, estado o fecha de encargo.',
                            'Si revisas histórico, combina contexto y estación para no mezclar ámbitos.',
                            'Si sospechas duplicidad, compara estación, descripción, fecha de encargo y trazabilidad antes de tocar nada.',
                        ],
                    },
                    {
                        title: 'Antes de crear un trabajo',
                        checks: [
                            'La estación existe y pertenece al cliente correcto.',
                            'El contexto activo es el correcto.',
                            'No existe ya un trabajo equivalente.',
                            'Los datos específicos del cliente están claros antes de guardar.',
                        ],
                    },
                    {
                        title: 'Qué revisar antes de cerrar o marcar como terminado',
                        checks: [
                            'La fecha de encargo está bien informada.',
                            'La fecha real de terminación existe si el trabajo está en TT o terminado.',
                            'Pedido, factura y legalización están revisados cuando apliquen.',
                            'No hay incidencias bloqueantes sin resolver.',
                        ],
                    },
                ],
                fields: [
                    {
                        label: 'Fecha de encargo',
                        description:
                            'Es la fecha base del trabajo. Cuando el sistema hable de fecha del trabajo, por defecto se interpreta como fecha de encargo salvo indicación expresa.',
                    },
                    {
                        label: 'Fecha real de terminación',
                        description:
                            'Debe reflejar el final real de la actuación. No debe inventarse para cuadrar listados.',
                    },
                    {
                        label: 'TT',
                        description:
                            'TT equivale a trabajo terminado. No debe usarse sin fecha real de terminación y sin revisión mínima del resto del caso.',
                    },
                    {
                        label: 'Estado',
                        description:
                            'Valores posibles: pendiente, en curso, terminado (TT), facturado, finalizado, cancelado. Cada estado condiciona qué acciones posteriores son coherentes o están bloqueadas.',
                    },
                ],
                warnings: [
                    'No dupliques trabajos por comodidad. Antes de crear, busca por número, estación o descripción.',
                    'No cierres un trabajo si faltan pedido, factura, legalización relevante o fecha real de terminación cuando apliquen.',
                    'Si el trabajo pertenece a una estación incorrecta, corrige primero esa relación antes de avanzar con pedido o factura.',
                ],
                tips: [
                    'Si un trabajo va a tener seguimiento económico, deja bien informados desde el principio el contexto, la estación y la referencia funcional del cliente.',
                ],
            },
            {
                id: 'orders',
                icon: '📦',
                title: 'Módulo de pedidos',
                intro:
                    'El pedido depende de un trabajo. No sustituye al trabajo ni lo reemplaza. Su función es dejar trazada la parte de pedido y sus líneas para poder contrastarla con facturación, cierre y seguimiento económico.',
                subsections: [
                    {
                        title: 'Qué papel tiene un pedido',
                        bullets: [
                            'Formaliza la parte de pedido asociada a un trabajo.',
                            'Permite revisar líneas, cantidades, importes y fechas.',
                            'Ayuda a detectar si la operativa económica está incompleta o desalineada.',
                        ],
                    },
                    {
                        title: 'Tarifario, contrato y cascada de líneas',
                        body:
                            'Al seleccionar un trabajo en el formulario de pedido, el sistema carga automáticamente las líneas del tarifario vinculadas a ese trabajo (por id_tarifario o id_contrato). Solo aparecen las líneas que corresponden al contexto y al trabajo elegido.',
                        bullets: [
                            'Si cambias el trabajo, las líneas tarifarias disponibles cambian automáticamente.',
                            'Si el trabajo tiene contrato o tarifario asignado, solo se muestran las líneas aplicables a ese contrato o tarifario.',
                            'Si no aparecen líneas, el trabajo puede no tener contrato ni tarifario con líneas activas.',
                            'Cada ítem del pedido requiere seleccionar una línea tarifaria si hay líneas disponibles. El sistema bloquea el guardado si falta esta selección.',
                        ],
                    },
                    {
                        title: 'Cómo consultar pedidos',
                        bullets: [
                            'Busca por número, trabajo asociado, estación, fecha o estado.',
                            'Comprueba si el pedido pertenece al mismo contexto del trabajo que lo origina.',
                            'Si hay varias revisiones del mismo caso, valida la trazabilidad antes de corregir importes o líneas.',
                        ],
                    },
                    {
                        title: 'Qué revisar antes de guardar',
                        checks: [
                            'Trabajo correcto y en el contexto correcto.',
                            'Número de pedido sin duplicidad evidente.',
                            'Fecha y estado coherentes.',
                            'Líneas, cantidades e importes revisados.',
                            'Contrato o referencia de tarifario revisados cuando apliquen.',
                        ],
                    },
                    {
                        title: 'Casos especiales y descuadres',
                        bullets: [
                            'Puede ocurrir que la factura llegue antes que el pedido.',
                            'Si el pedido es posterior a la factura, no fuerces el caso como regularizado si aún no se puede trazar bien.',
                            'Si detectas una diferencia clara con el trabajo o con el tarifario, deja la situación preparada para revisión en lugar de maquillarla.',
                        ],
                    },
                ],
                warnings: [
                    'No uses el pedido para tapar un trabajo mal creado o mal relacionado.',
                    'No des una línea por válida solo porque estaba en un Excel antiguo; valida siempre el marco contractual y el tarifario.',
                ],
            },
            {
                id: 'invoices',
                icon: '🧾',
                title: 'Módulo de facturas',
                intro:
                    'La factura consolida la parte económica del flujo. Debe poder relacionarse con el trabajo y, cuando exista, con el pedido. Si la relación es débil, el control de cobro y el cierre pierden calidad.',
                subsections: [
                    {
                        title: 'Qué hace este módulo',
                        bullets: [
                            'Registra el documento económico asociado a un trabajo.',
                            'Ayuda a detectar trabajos ejecutados pero no cobrados.',
                            'Permite contrastar pedido, importes, referencias y documento final.',
                        ],
                    },
                    {
                        title: 'Diferencias por cliente',
                        bullets: [
                            'En MOEVE puede existir lógica de autofacturación.',
                            'En REPSOL puede intervenir el número de aviso, el pedido previo o la referencia de factura según el flujo acordado.',
                            'No todos los contextos tienen exactamente los mismos campos o controles económicos.',
                        ],
                    },
                    {
                        title: 'Cómo consultar y revisar',
                        bullets: [
                            'Busca por número de factura, trabajo, estación, pedido o estado.',
                            'Revisa si la factura está en el mismo contexto del trabajo asociado.',
                            'Si hay duplicidad aparente, abre ambas referencias antes de corregir.',
                        ],
                    },
                    {
                        title: 'Qué revisar antes de guardar',
                        checks: [
                            'Trabajo asociado correcto.',
                            'Número de factura sin duplicidad evidente.',
                            'Importes revisados.',
                            'Estado coherente con el documento.',
                            'Campos específicos del cliente completos.',
                        ],
                    },
                    {
                        title: 'Qué hacer si factura y pedido no cuadran',
                        bullets: [
                            'No cierres el caso como si estuviera regularizado si aún no puedes trazar pedido y factura correctamente.',
                            'Deja el estado preparado para seguimiento y revisión posterior.',
                            'Registra la relación con el trabajo para que no quede una actuación sin control de cobro.',
                        ],
                    },
                ],
                warnings: [
                    'No dupliques facturas para corregir una relación errónea. Revisa primero si debes editar la existente.',
                    'Si una factura llega antes que el pedido, no fuerces el cierre ni marques el caso como resuelto sin trazabilidad suficiente.',
                    'Si cambias el trabajo en una factura ya parcialmente rellenada, revisa la sociedad facturadora y los campos económicos después del cambio.',
                ],
            },
            {
                id: 'tarifarios',
                icon: '📄',
                title: 'Tarifarios y contratos',
                intro:
                    'Los tarifarios recogen las líneas de tarifa aplicables a los trabajos. Están vinculados a contratos. La cadena contrato → tarifario → líneas tarifarias es la base del control económico en pedidos.',
                subsections: [
                    {
                        title: 'Para qué sirven',
                        bullets: [
                            'Un contrato establece el marco comercial con el cliente para un periodo o ámbito dado.',
                            'Un tarifario recoge las líneas de precio aplicables en ese contrato.',
                            'Las líneas de tarifario son las que se seleccionan en los ítems de los pedidos.',
                        ],
                    },
                    {
                        title: 'Cascada contrato → tarifario → líneas',
                        body:
                            'Un tarifario solo puede vincularse a un contrato activo. Si el contrato no existe o está inactivo, las líneas del tarifario no estarán disponibles en los pedidos del trabajo.',
                        checks: [
                            'Confirma que el contrato está activo antes de crear o editar un tarifario.',
                            'Verifica que el tarifario tiene líneas antes de asignarlo a un trabajo.',
                            'Si un trabajo no tiene líneas disponibles en pedidos, revisa primero el contrato y el tarifario asignado.',
                        ],
                    },
                    {
                        title: 'Precauciones',
                        bullets: [
                            'No elimines líneas de tarifario si hay pedidos activos que las referencian.',
                            'No cambies el contrato de un tarifario sin revisar el impacto en pedidos existentes.',
                            'Si el formulario de tarifario muestra un aviso de contrato inactivo o cascada incompleta, resuélvelo antes de asignar el tarifario a nuevos trabajos.',
                        ],
                    },
                ],
                warnings: [
                    'Si en el formulario de pedido no aparecen líneas tarifarias para un trabajo, el primer punto a revisar es si ese trabajo tiene contrato o tarifario con líneas activas asignado.',
                ],
            },
            {
                id: 'clients-stations',
                icon: '⛽',
                title: 'Clientes y estaciones',
                intro:
                    'Clientes y estaciones son maestros de base. Si aquí se mezclan datos, los errores se arrastran a trabajos, pedidos, facturas, informes y cierre.',
                subsections: [
                    {
                        title: 'Clientes',
                        bullets: [
                            'Cada cliente debe mantenerse en su contexto correcto.',
                            'Evita duplicar clientes con variaciones mínimas de nombre.',
                            'Usa de forma consistente razón social, identificadores y observaciones.',
                        ],
                    },
                    {
                        title: 'Estaciones',
                        bullets: [
                            'Las estaciones deben estar separadas por cliente para evitar mezclar operativas.',
                            'Cada estación debe poder consultarse históricamente para saber qué trabajos se han hecho allí.',
                            'Código, dirección y estado ayudan a validar que estás trabajando sobre la estación correcta.',
                        ],
                    },
                    {
                        title: 'Antes de crear o corregir',
                        checks: [
                            'Busca por nombre, código y variantes habituales de escritura.',
                            'Comprueba si ya existe una estación equivalente en el cliente correcto.',
                            'No reutilices una estación de otro cliente solo porque la dirección se parezca.',
                        ],
                    },
                ],
                warnings: [
                    'No dupliques estaciones con nombres parecidos. El histórico operativo quedará dividido y perderás trazabilidad.',
                ],
            },
            {
                id: 'maestros',
                icon: '🗂️',
                title: 'Maestros',
                intro:
                    'Maestros centraliza los datos de referencia del sistema: clientes, estaciones de servicio, sociedades facturadoras y otros catálogos. La calidad de los maestros afecta directamente a todos los módulos operativos.',
                subsections: [
                    {
                        title: 'Qué gestiona',
                        bullets: [
                            'Clientes: entidades con las que se trabaja o a las que se factura.',
                            'Estaciones de servicio: ubicaciones sobre las que se ejecutan los trabajos.',
                            'Sociedades facturadoras: entidades jurídicas que emiten las facturas. Deben tener CIF registrado.',
                            'Tipos de trabajo, tipos de documento y otras categorías del sistema.',
                        ],
                    },
                    {
                        title: 'Diagnóstico funcional',
                        body:
                            'Maestros incluye un panel de diagnóstico que detecta inconsistencias de configuración: contratos sin sociedad vinculada, CIF no registrados u otras dependencias rotas entre entidades. Cuando aparecen alertas, deben revisarse antes de operar sobre los datos afectados.',
                        bullets: [
                            'Cada alerta indica el tipo de problema y, cuando es posible, ofrece un enlace a la configuración afectada.',
                            'Las alertas no bloquean el sistema, pero señalan riesgos operativos reales.',
                            'Resuélvelas antes de crear nuevos registros que dependan de la configuración afectada.',
                        ],
                    },
                    {
                        title: 'Antes de crear o corregir un maestro',
                        checks: [
                            'Busca primero por nombre, código y variantes habituales de escritura.',
                            'No dupliques clientes ni estaciones con diferencias mínimas en el nombre.',
                            'No reutilices una estación de otro cliente aunque la dirección sea parecida.',
                            'Mantén el CIF actualizado en las sociedades facturadoras.',
                        ],
                    },
                ],
                warnings: [
                    'Un maestro incorrecto (cliente duplicado, estación en contexto equivocado, sociedad sin CIF) genera errores en pedidos, facturas y cierres que son difíciles de corregir a posteriori.',
                ],
            },
            {
                id: 'legalizations',
                icon: '📑',
                title: 'Legalizaciones',
                intro:
                    'Las legalizaciones proceden de un control específico, pero forman parte de la información relacionada con obras y trabajos. No son un apunte decorativo: pueden afectar a revisión, seguimiento y cierre.',
                subsections: [
                    {
                        title: 'Cómo entenderlas',
                        bullets: [
                            'Pueden existir como control paralelo, pero deben conectarse con la operativa principal.',
                            'Su estado debe ser visible cuando afecta al avance o al cierre.',
                            'Ayudan a saber si una actuación está solo ejecutada o también regularizada en su parte documental.',
                        ],
                    },
                    {
                        title: 'Qué revisar',
                        checks: [
                            'Que la legalización corresponda al trabajo correcto.',
                            'Que su estado esté actualizado.',
                            'Que una incidencia de legalización no quede escondida en observaciones ambiguas.',
                        ],
                    },
                ],
                warnings: [
                    'No des por listo un trabajo para cierre si la legalización relevante sigue pendiente de forma bloqueante.',
                ],
            },
            {
                id: 'closure',
                icon: '✅',
                title: 'Panel de cierre',
                badges: [{ label: 'Cierre', color: 'bg-state-progress-bg text-state-progress-text' }],
                intro:
                    'Finalizar un trabajo significa bloquearlo para la operativa ordinaria tras una revisión final. El panel de cierre existe para asegurar que el caso queda terminado, trazado y consistente antes de darlo por finalizado.',
                subsections: [
                    {
                        title: 'Qué implica cerrar un trabajo',
                        bullets: [
                            'Bloquea la edición ordinaria del registro.',
                            'Exige revisar fechas, estado, pedido, factura, legalización e incidencias.',
                            'Deja rastro de quién valida o modifica el estado final del caso.',
                        ],
                    },
                    {
                        title: 'Quién debe usarlo',
                        bullets: [
                            'Perfiles de cierre o dirección con acceso autorizado.',
                            'Usuarios responsables de validar que un trabajo ya no debe volver a la operativa normal.',
                        ],
                    },
                    {
                        title: 'Qué comprobar antes de cerrar',
                        checks: [
                            'Existe fecha real de terminación.',
                            'El trabajo está realmente terminado y no solo pendiente de trámite administrativo.',
                            'Pedido y factura son trazables o el descuadre está documentado y preparado para revisión.',
                            'La legalización está revisada cuando afecta al caso.',
                            'No quedan bloqueos funcionales pendientes.',
                        ],
                    },
                    {
                        title: 'Qué no se debe forzar',
                        bullets: [
                            'No cierres por presión de cuadrar un panel o un informe.',
                            'No reabras ni toques trabajos finalizados sin autorización y sin motivo claro.',
                            'No conviertas un cierre en un atajo para ocultar un descuadre económico o documental.',
                        ],
                    },
                ],
                warnings: [
                    'Los trabajos finalizados deben mantenerse bloqueados. Solo perfiles autorizados deben tocarlos y siempre con trazabilidad clara.',
                    'El cierre está ligado a auditoría e histórico. Si se fuerza mal, el problema queda registrado y afecta a revisiones posteriores.',
                ],
            },
            {
                id: 'messages',
                icon: '💬',
                title: 'Mensajes internos',
                intro:
                    'La mensajería interna sirve para coordinar trabajo dentro del ERP sin depender de correo externo para cada duda funcional.',
                subsections: [
                    {
                        title: 'Cuándo usar mensajes',
                        bullets: [
                            'Para consultas operativas entre usuarios.',
                            'Para avisar de un caso que requiere revisión de otro rol.',
                            'Para dejar una referencia rápida vinculada a una tarea o módulo.',
                        ],
                    },
                    {
                        title: 'Cómo redactar un mensaje útil',
                        checks: [
                            'Indica módulo afectado.',
                            'Añade número, trabajo o referencia si existe.',
                            'Resume qué ocurre y qué necesitas.',
                            'Usa prioridad alta solo si realmente bloquea operativa.',
                        ],
                    },
                ],
                tips: [
                    'Si el asunto afecta a un trabajo, pedido o factura, menciona la referencia para que la búsqueda posterior sea más fácil.',
                ],
            },
            {
                id: 'support',
                icon: '🛟',
                title: 'Soporte',
                intro:
                    'Soporte es el canal para reportar errores, bloqueos o dudas que necesitan seguimiento formal. Es válido para incidencias técnicas y funcionales. No solo para problemas de sistema.',
                subsections: [
                    {
                        title: 'Cuándo abrir un ticket de soporte',
                        bullets: [
                            'Cuando no puedes continuar con la operativa.',
                            'Cuando un dato no guarda y no es un problema claro de validación.',
                            'Cuando hay incoherencias entre módulos que requieren revisión.',
                            'Cuando has revisado contexto, permisos y filtros y el problema persiste.',
                        ],
                    },
                    {
                        title: 'Estados de un ticket',
                        bullets: [
                            'Pendiente: ticket abierto, aún no atendido.',
                            'En revisión: el equipo técnico o funcional está analizando el caso.',
                            'Resuelta: el caso ha sido atendido y cerrado.',
                            'Archivada: ticket archivado sin resolución activa (histórico).',
                        ],
                    },
                    {
                        title: 'Qué debe incluir la solicitud',
                        checks: [
                            'Pantalla o módulo afectado.',
                            'Acción realizada.',
                            'Resultado esperado.',
                            'Resultado observado.',
                            'Referencia o ejemplo suficiente para reproducir el caso.',
                        ],
                    },
                ],
                warnings: [
                    'No abras varios tickets para el mismo caso si ya existe uno en seguimiento.',
                    'No uses soporte para solicitudes de permisos o configuración que deben gestionarse por administración.',
                ],
            },
            {
                id: 'admin',
                icon: '⚙️',
                title: 'Administración',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'Administración agrupa gestión de usuarios, roles, contextos, mantenimiento funcional, revisión estructural del dato e importaciones con impacto global.',
                subsections: [
                    {
                        title: 'Responsabilidades habituales',
                        bullets: [
                            'Crear y mantener usuarios.',
                            'Asignar roles, permisos y contextos.',
                            'Revisar maestros, incidencias repetidas e importaciones.',
                            'Supervisar que la separación por cliente siga siendo correcta.',
                        ],
                    },
                    {
                        title: 'Qué vigilar especialmente',
                        bullets: [
                            'Accesos innecesarios a datos de otros contextos.',
                            'Duplicados estructurales en clientes, estaciones o referencias económicas.',
                            'Procesos masivos sin trazabilidad suficiente.',
                        ],
                    },
                ],
            },
            {
                id: 'audit',
                icon: '🕵️',
                title: 'Auditoría y trazabilidad',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'El ERP debe permitir reconstruir qué se hizo, quién lo hizo y cuándo. Esa trazabilidad es parte del motivo por el que el sistema sustituye operativas puramente basadas en Excel.',
                subsections: [
                    {
                        title: 'Para qué sirve la trazabilidad',
                        bullets: [
                            'Para revisar cambios de estado, regularizaciones y cierres.',
                            'Para saber por qué un dato quedó modificado.',
                            'Para sostener revisiones funcionales, económicas o históricas.',
                        ],
                    },
                    {
                        title: 'Dónde impacta',
                        bullets: [
                            'En trabajos, pedidos, facturas y legalizaciones.',
                            'En cierres y reaperturas autorizadas.',
                            'En importaciones, incidencias y correcciones masivas.',
                        ],
                    },
                ],
                warnings: [
                    'No uses observaciones vagas para justificar cambios importantes. Si una decisión es sensible, debe quedar explicada de forma comprensible.',
                ],
            },
            {
                id: 'imports',
                icon: '📥',
                title: 'Importaciones de Excel',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'Las importaciones existen porque el ERP convive con datos históricos procedentes de Excel. Su objetivo no es introducir información sin control, sino normalizarla y dejarla preparada para trabajar con ella dentro del sistema.',
                subsections: [
                    {
                        title: 'Cuándo usar una importación',
                        bullets: [
                            'Cuando existe información histórica fiable que no compensa cargar a mano.',
                            'Cuando hay que consolidar datos de clientes, estaciones, trabajos o relaciones similares.',
                            'Cuando la importación sirve para completar un histórico sin romper la estructura actual del ERP.',
                        ],
                    },
                    {
                        title: 'Proceso esperado',
                        checks: [
                            'Subida del fichero.',
                            'Previsualización.',
                            'Detección de errores.',
                            'Corrección antes de confirmar.',
                            'Confirmación final con trazabilidad.',
                        ],
                    },
                    {
                        title: 'Qué revisar antes de confirmar',
                        checks: [
                            'No hay duplicados evidentes.',
                            'No se mezclan estaciones o clientes de contextos distintos.',
                            'Los encabezados y tipos de dato siguen la estructura esperada.',
                            'La normalización es coherente con los formularios manuales.',
                        ],
                    },
                    {
                        title: 'Qué errores evitar',
                        bullets: [
                            'No confirmar la carga si la previsualización ya muestra incoherencias.',
                            'No aceptar nombres sucios solo porque ya venían así en el Excel histórico.',
                            'No mezclar referencias de REPSOL y MOEVE en una misma importación si pertenecen a ámbitos separados.',
                            'No perder la trazabilidad de quién importó, cuándo y con qué resultado.',
                        ],
                    },
                ],
                warnings: [
                    'Una importación incorrecta puede contaminar varios módulos a la vez. Revisa siempre la previsualización antes de confirmar.',
                ],
                tips: [
                    'Si el fichero histórico requiere demasiadas excepciones, corrígelo primero fuera del flujo final de importación en lugar de forzar la carga.',
                ],
            },
            {
                id: 'reports',
                icon: '📈',
                title: 'Informes y explotación de datos',
                intro:
                    'Uno de los objetivos del ERP es poder separar y explotar la información sin rehacer informes manuales cada vez. Para eso el dato debe nacer limpio y mantenerse consistente.',
                subsections: [
                    {
                        title: 'Qué se espera de los informes',
                        bullets: [
                            'Separación por cliente o contexto.',
                            'Consulta por estación, trabajo, pedido, factura o estado.',
                            'Apoyo al seguimiento operativo y económico.',
                            'Base fiable para revisar histórico y pendientes.',
                        ],
                    },
                    {
                        title: 'Condiciones para que funcionen bien',
                        bullets: [
                            'No mezclar contextos.',
                            'No duplicar clientes o estaciones.',
                            'No cerrar trabajos sin revisión mínima.',
                            'No dejar vacíos campos clave por comodidad.',
                        ],
                    },
                ],
                tips: [
                    'Si un informe no encaja con la realidad, revisa primero la calidad del dato fuente antes de pedir un cambio visual.',
                ],
            },
            {
                id: 'devices',
                icon: '📱',
                title: 'Buenas prácticas por dispositivo',
                intro:
                    'La experiencia del ERP debe seguir siendo usable en pantalla grande, portátil y móvil. Cambia la forma de interactuar, pero no la lógica de trabajo ni las validaciones.',
                subsections: [
                    {
                        title: 'Pantalla grande o portátil',
                        bullets: [
                            'Es el entorno más cómodo para revisar tablas largas, comparar columnas y validar cierres o importaciones.',
                            'Recomendado para revisión cruzada entre trabajos, pedidos, facturas y auditoría.',
                        ],
                    },
                    {
                        title: 'Móvil',
                        bullets: [
                            'Es adecuado para consultas, filtros rápidos, seguimiento y ediciones puntuales.',
                            'Los formularios se apilan en una columna cuando hace falta.',
                            'Las tablas grandes pueden requerir scroll horizontal interno dentro de su bloque.',
                        ],
                    },
                    {
                        title: 'Qué vigilar en móvil',
                        checks: [
                            'Cerrar el panel lateral después de navegar.',
                            'Revisar la parte final del formulario antes de guardar.',
                            'Usar el scroll interno de tablas cuando una columna no cabe.',
                        ],
                    },
                ],
                warnings: [
                    'Si en una pantalla pequeña no ves una acción, desplázate dentro del panel o del bloque correspondiente antes de asumir que falta el botón.',
                ],
            },
            {
                id: 'states_diagnostics',
                icon: '🔍',
                title: 'Estados y diagnósticos',
                intro:
                    'Esta sección describe los estados disponibles en los módulos principales y los diagnósticos automáticos que el sistema muestra en formularios y paneles.',
                subsections: [
                    {
                        title: 'Estados de trabajos',
                        bullets: [
                            'Pendiente: trabajo registrado, pendiente de iniciar.',
                            'En curso: trabajo en ejecución.',
                            'Terminado (TT): actuación ejecutada. Requiere fecha real de terminación.',
                            'Facturado: trabajo con factura emitida.',
                            'Finalizado: trabajo cerrado y bloqueado para operativa ordinaria.',
                            'Cancelado: trabajo cancelado. No reutilizar.',
                        ],
                    },
                    {
                        title: 'Estados de facturas',
                        bullets: [
                            'Pendiente: factura registrada, aún no emitida.',
                            'Emitida: factura emitida al cliente.',
                            'Enviada: factura enviada, en proceso de cobro.',
                        ],
                    },
                    {
                        title: 'Estados de tickets de soporte',
                        bullets: [
                            'Pendiente: ticket abierto sin atención.',
                            'En revisión: caso siendo analizado.',
                            'Resuelta: caso cerrado favorablemente.',
                            'Archivada: ticket archivado como histórico.',
                        ],
                    },
                    {
                        title: 'Diagnóstico funcional en Maestros',
                        body:
                            'El módulo de Maestros incluye un panel de diagnóstico que detecta inconsistencias de configuración: contratos sin sociedad facturadora vinculada, CIF no registrados u otras dependencias rotas. Aparece automáticamente cuando hay alertas activas.',
                        bullets: [
                            'No bloquea el sistema, pero indica riesgos operativos reales.',
                            'Cada alerta ofrece información sobre el tipo de inconsistencia y, cuando es posible, un enlace a la configuración afectada.',
                            'Resuélvelas antes de crear nuevos registros que dependan de los datos afectados.',
                        ],
                    },
                    {
                        title: 'Cascada tarifaria en pedidos',
                        body:
                            'Al seleccionar un trabajo en el formulario de pedido, el sistema filtra automáticamente las líneas del tarifario disponibles según el contexto, el tarifario y el contrato de ese trabajo. Si se cambia el trabajo, las líneas disponibles cambian y los ítems con línea asignada pueden quedar inválidos.',
                        bullets: [
                            'Si el trabajo no tiene tarifario o contrato asignado, no habrá líneas disponibles.',
                            'Si hay líneas disponibles y un ítem no tiene ninguna seleccionada, el sistema bloquea el guardado.',
                        ],
                    },
                    {
                        title: 'Limpieza automática en facturas al cambiar trabajo',
                        body:
                            'Cuando se cambia el trabajo en el formulario de factura, el sistema limpia automáticamente la sociedad facturadora y otros campos dependientes para evitar inconsistencias.',
                        bullets: [
                            'Tras cambiar el trabajo, revisa siempre la sociedad y los campos económicos antes de guardar.',
                        ],
                    },
                    {
                        title: 'Diagnóstico de contratos en tarifarios',
                        body:
                            'El formulario de tarifarios avisa cuando el contrato asociado no está activo o cuando la cascada contrato → tarifario → líneas está incompleta.',
                        bullets: [
                            'Un tarifario sin contrato activo no ofrecerá líneas en pedidos.',
                            'Revisa el estado del contrato antes de asignar el tarifario a un trabajo.',
                        ],
                    },
                ],
            },
            {
                id: 'best_practices',
                icon: '✅',
                title: 'Buenas prácticas',
                intro:
                    'Seguir estas prácticas reduce errores, duplicados y descuadres en la operativa diaria.',
                checks: [
                    'Revisar el contexto activo antes de crear o editar cualquier registro.',
                    'No mezclar MOEVE y REPSOL: cada contexto tiene su operativa separada.',
                    'Buscar antes de crear: trabajos, pedidos, clientes y estaciones pueden ya existir.',
                    'Revisar el contrato y tarifario del trabajo antes de crear pedidos con ítems tarifados.',
                    'Revisar importes, unidades y líneas tarifarias antes de emitir una factura.',
                    'Usar el módulo de soporte para incidencias, no mensajes informales.',
                    'No modificar registros finalizados o bloqueados sin autorización expresa.',
                    'Confirmar el contexto activo después de cambiar de cliente durante la jornada.',
                    'Revisar el diagnóstico de Maestros si aparecen alertas antes de operar sobre los datos afectados.',
                ],
                warnings: [
                    'Un dato creado en el contexto incorrecto puede generar duplicados y descuadres difíciles de corregir a posteriori.',
                    'No cambies estados para que un panel o informe se vea mejor. El estado debe reflejar la realidad del registro.',
                ],
            },
            {
                id: 'faq',
                icon: '❓',
                title: 'Preguntas frecuentes',
                intro:
                    'Respuestas a las dudas más habituales por módulo, rol y situación.',
                faq: [
                    {
                        q: '¿Por qué no veo todos los módulos?',
                        a: 'El ERP muestra solo los módulos que tu rol y permisos permiten ver. Si necesitas acceso a un módulo, contacta con administración.',
                    },
                    {
                        q: '¿Por qué no puedo editar un trabajo cerrado?',
                        a: 'Los trabajos finalizados están bloqueados para la operativa ordinaria. Solo perfiles autorizados (Dirección o Admin) pueden reabrir un trabajo, siempre con motivo documentado.',
                    },
                    {
                        q: '¿Qué hago si no aparecen líneas tarifarias en un pedido?',
                        a: 'Verifica que el trabajo seleccionado tiene un contrato o tarifario asignado con líneas activas. Si el trabajo no tiene tarifario o contrato, no se mostrarán opciones en los ítems del pedido.',
                    },
                    {
                        q: '¿Qué pasa si cambio el trabajo en una factura?',
                        a: 'Al cambiar el trabajo, el sistema limpia automáticamente la sociedad facturadora y otros campos dependientes. Revisa y vuelve a rellenar esos campos antes de guardar.',
                    },
                    {
                        q: '¿Cómo sé si estoy trabajando en MOEVE o REPSOL?',
                        a: 'El contexto activo se muestra en la barra lateral o en el selector de contexto visible en la interfaz. Revísalo siempre antes de crear o editar registros.',
                    },
                    {
                        q: '¿Dónde comunico una incidencia o bloqueo?',
                        a: 'Usa el módulo de Soporte para abrir un ticket. Describe el módulo afectado, la acción realizada, el resultado esperado y el resultado observado.',
                    },
                    {
                        q: '¿Qué hago si un pedido no cuadra con la factura?',
                        a: 'No forces el cierre ni marques el caso como resuelto sin trazabilidad suficiente. Deja el estado visible para revisión y documenta la situación en observaciones o en un ticket de soporte.',
                    },
                    {
                        q: '¿Qué significa TT en un trabajo?',
                        a: 'TT significa trabajo terminado. Indica que la actuación fue ejecutada. Para marcarlo como TT debe existir fecha real de terminación.',
                    },
                    {
                        q: '¿Qué hago si el diagnóstico de Maestros muestra alertas?',
                        a: 'Revisa las alertas antes de crear nuevos registros dependientes. Cada alerta indica el tipo de inconsistencia y, cuando es posible, enlaza a la configuración afectada.',
                    },
                ],
            },
        ],
    },
    en: {
        sections: [
            {
                id: 'intro',
                icon: '📘',
                title: 'Introduction to CIETE ERP',
                intro:
                    'This manual explains the real day-to-day use of the CIETE ERP. The system replaces historical Excel-based workflows, avoids flat untraceable data, reduces simultaneous editing issues and keeps client, context, station and status separation under control.',
                subsections: [
                    {
                        title: 'Why this ERP exists',
                        body:
                            'CIETE worked for years with Excel files for Repsol and for Cepsa, now MOEVE. That model was not enough when volume increased, several users edited at the same time or reports had to be separated by client.',
                        bullets: [
                            'It avoids parallel versions of the same file.',
                            'It shows who changed a record and when.',
                            'It reduces the risk of completing work without later control of orders or invoices.',
                            'It supports station, client and context history.',
                        ],
                    },
                    {
                        title: 'Main operating logic',
                        bullets: [
                            'Works is the central record.',
                            'Orders and invoices complete the economic flow.',
                            'Clients and stations must stay clean.',
                            'Legalizations, closure, audit, support and imports complete the system.',
                        ],
                    },
                ],
                warnings: [
                    'Do not treat the ERP like a free spreadsheet. Each module has an operating purpose and traceability rules.',
                ],
            },
            {
                id: 'access',
                icon: '🔐',
                title: 'Access, session and good practices',
                intro:
                    'Using the ERP correctly starts with the right account, the right context and the right filters before editing anything.',
                subsections: [
                    {
                        title: 'When entering the system',
                        checks: [
                            'Confirm the session belongs to you.',
                            'Review the active context before creating or editing.',
                            'Check visible modules, language and theme.',
                        ],
                    },
                    {
                        title: 'Good session habits',
                        bullets: [
                            'Do not leave long forms open for too long if the case may have changed.',
                            'After a sensitive save, return to the list and verify the result.',
                            'If an action is missing, check permissions and context before reporting an incident.',
                        ],
                    },
                ],
            },
            {
                id: 'daily_flow',
                icon: '🗓️',
                title: 'Recommended daily workflow',
                intro:
                    'Follow this order to reduce errors, duplicates and mismatches between modules.',
                steps: [
                    'Review the active context before starting: MOEVE, REPSOL or OTHER CLIENTS.',
                    'Search for the work or create it only if it does not exist.',
                    'Verify the station belongs to the correct client context. Check the contract or rate sheet if applicable.',
                    'Open or create the order linked to the work. Select the rate line for each item.',
                    'Review lines, quantities and amounts before confirming the order.',
                    'Create or review the invoice for the work. Check the billing entity and financial data.',
                    'Use functional diagnostics in Masters and Rate Sheets when alerts appear.',
                    'If the work is finished, review it from the closure panel if your role allows it.',
                ],
                tips: [
                    'When reviewing several modules in sequence, return to the list before switching to avoid losing open references.',
                ],
            },
            {
                id: 'home',
                icon: '🏠',
                title: 'Home and system start',
                intro:
                    'Home is an orientation screen. Daily work still happens in the operational modules, but home helps surface notices and quick access points.',
                fields: [
                    {
                        label: 'Internal notices',
                        description:
                            'Short reminders, temporary incidents and working criteria for the team.',
                    },
                    {
                        label: 'Quick access',
                        description:
                            'Messages, help, support and system status are available from the authenticated layout.',
                    },
                    {
                        label: 'Recommended use',
                        description:
                            'Check notices before continuing closures, imports or pending validations.',
                    },
                ],
            },
            {
                id: 'dashboard',
                icon: '📊',
                title: 'Dashboards and summary panels',
                intro:
                    'Dashboards help prioritize work. They are not the place to fix data without opening the related record or list.',
                subsections: [
                    {
                        title: 'How to read a metric',
                        bullets: [
                            'A number alone does not prove an error.',
                            'Read each card together with the related module or list.',
                            'Operational, admin and closure panels do not mean the same thing.',
                        ],
                    },
                ],
                warnings: [
                    'Do not change statuses only to make a panel look cleaner.',
                ],
            },
            {
                id: 'navigation',
                icon: '🧭',
                title: 'General navigation',
                intro:
                    'Navigation is meant to move quickly between areas without losing orientation. On mobile it becomes compact, but the workflow stays the same.',
                fields: [
                    {
                        label: 'Sidebar',
                        description:
                            'Main access to modules grouped by work area.',
                    },
                    {
                        label: 'Navbar',
                        description:
                            'Shows the current screen and top-level actions.',
                    },
                    {
                        label: 'Manual search',
                        description:
                            'Use it to find terms such as work, order, invoice, closure, context or import.',
                    },
                ],
                steps: [
                    'Open the module from the sidebar or the available top action.',
                    'Review the page title and visible filters before editing.',
                    'On mobile, close the side panel after navigating to recover space.',
                ],
            },
            {
                id: 'contexts',
                icon: '🧩',
                title: 'Working contexts: MOEVE, REPSOL and other clients',
                intro:
                    'A context defines the data scope you are working in. It exists to keep clients, stations, works, orders, invoices and reports separated when they must not be mixed.',
                subsections: [
                    {
                        title: 'What a context means',
                        bullets: [
                            'It prevents mixing client data by mistake.',
                            'It allows reporting by client without manual rebuilding.',
                            'It keeps stations, contracts and records inside the correct scope.',
                        ],
                    },
                    {
                        title: 'If you have multiple contexts',
                        checks: [
                            'Confirm the active context before creating a record.',
                            'Check that the station belongs to the correct client.',
                            'Review whether a similar record already exists in another visible context.',
                        ],
                    },
                    {
                        title: 'Common client-specific differences',
                        bullets: [
                            'REPSOL often uses a notice number before the order step.',
                            'MOEVE may require self-billing logic.',
                            'Other clients may share structure but should not contaminate MOEVE or REPSOL data.',
                        ],
                    },
                ],
                warnings: [
                    'If a station does not appear, check the active context before creating a new one.',
                ],
            },
            {
                id: 'roles',
                icon: '🛡️',
                title: 'Roles, permissions and visibility',
                intro:
                    'The manual is organised in three categories based on the user profile: Admin, Direction/Closure and General users. If a feature is not visible, it may be due to your profile, the record status or the active context.',
                subsections: [
                    {
                        title: 'Admin',
                        body:
                            'Full access to the manual and the system. Can manage users, roles, contexts, imports, maintenance and global configuration.',
                    },
                    {
                        title: 'Direction / Closure',
                        body:
                            'Sees the operational content and the closure panel. Can access audit and master data if those permissions are assigned. Does not see admin technical sections or user management.',
                    },
                    {
                        title: 'General users',
                        body:
                            'Operational profile. Covers works, orders, invoices, rate sheets, messages and support. Does not see the closure panel or the admin section. MOEVE and REPSOL execution variants are context scopes within this profile.',
                    },
                    {
                        title: 'Before reporting an access problem',
                        checks: [
                            'Check whether the section or action matches your profile category.',
                            'Check if the record is closed or locked by its status.',
                            'Verify the record is in a context visible to your profile.',
                        ],
                    },
                ],
            },
            {
                id: 'tables',
                icon: '📋',
                title: 'Tables, search, filters and pagination',
                intro:
                    'With many records per year, careful search habits are essential. Tables are the base for finding the right record without creating duplicates.',
                subsections: [
                    {
                        title: 'How to search correctly',
                        bullets: [
                            'Search by number, station, client, description, notice, order or known reference.',
                            'Combine status and date filters when volume is high.',
                            'Keep filters active while paginating through large sets.',
                        ],
                    },
                    {
                        title: 'Large tables on small screens',
                        body:
                            'Some wide tables use internal horizontal scrolling on mobile or narrow widths. This is expected and avoids global page overflow.',
                    },
                ],
                warnings: [
                    'Before editing from a list, review active filters so you do not read the record out of context.',
                ],
            },
            {
                id: 'forms',
                icon: '📝',
                title: 'Forms: create, edit and validate data',
                intro:
                    'Forms are meant to store structured and traceable data. They are not a replacement for unresolved informal notes.',
                subsections: [
                    {
                        title: 'Before saving',
                        checks: [
                            'Search first to avoid duplicates.',
                            'Confirm the active context.',
                            'Review the related client, station or work.',
                            'Check that the status matches reality.',
                        ],
                    },
                    {
                        title: 'Create or edit',
                        bullets: [
                            'If an equivalent record already exists, edit it instead of creating another one.',
                            'Use notes to clarify a case, not to replace structured fields.',
                        ],
                    },
                ],
            },
            {
                id: 'works',
                icon: '🔧',
                title: 'Works module',
                intro:
                    'Works is the central operational record. Orders, invoices, legalizations and closure depend on it.',
                subsections: [
                    {
                        title: 'What a work represents',
                        bullets: [
                            'A real intervention on a station, client or file.',
                            'The operational origin of later order and invoice steps.',
                            'A record that should remain historically searchable by station.',
                        ],
                    },
                    {
                        title: 'Before creating a work',
                        checks: [
                            'The station exists and belongs to the correct client.',
                            'The active context is correct.',
                            'There is no equivalent work already created.',
                            'Client-specific fields are understood before saving.',
                        ],
                    },
                    {
                        title: 'Critical points',
                        bullets: [
                            'The main date is the assignment date.',
                            'TT means finished work and should include a real completion date.',
                            'Status drives later review and closure.',
                            'Do not duplicate works because later economic control will become unreliable.',
                        ],
                    },
                ],
                warnings: [
                    'Do not close a work if order, invoice, legalization or real completion data are still unresolved when they apply.',
                ],
            },
            {
                id: 'orders',
                icon: '📦',
                title: 'Orders module',
                intro:
                    'An order depends on a work and supports the economic review of lines, amounts and later reconciliation.',
                subsections: [
                    {
                        title: 'What the order does',
                        bullets: [
                            'Registers the order linked to a work.',
                            'Controls lines, quantities, amounts and dates.',
                            'Supports comparison with invoice and closure.',
                        ],
                    },
                    {
                        title: 'Rate sheet and contract',
                        body:
                            'The expected rule is to request only what belongs to the applicable rate sheet, with the contract acting as the rate-sheet reference.',
                    },
                    {
                        title: 'Before saving',
                        checks: [
                            'Correct work and correct context.',
                            'Order number reviewed.',
                            'Date, status, lines and amounts checked.',
                            'Contract or rate-sheet reference reviewed when needed.',
                        ],
                    },
                    {
                        title: 'Special cases',
                        bullets: [
                            'An invoice may arrive before the order.',
                            'If the order is later than the invoice, do not force the case as regularized until both can be traced correctly.',
                        ],
                    },
                ],
                warnings: [
                    'Do not use the order to hide a badly created work.',
                ],
            },
            {
                id: 'invoices',
                icon: '🧾',
                title: 'Invoices module',
                intro:
                    'The invoice consolidates the financial part of the workflow and should remain linked to the work and, when available, to the order.',
                subsections: [
                    {
                        title: 'Purpose',
                        bullets: [
                            'Register the financial document linked to a work.',
                            'Help detect completed work that has not yet been charged.',
                            'Support comparison between work, order and final billing document.',
                        ],
                    },
                    {
                        title: 'Client-specific differences',
                        bullets: [
                            'MOEVE may use self-billing logic.',
                            'REPSOL may require notice number, order or invoice reference depending on the flow.',
                            'Not every context uses the same control fields.',
                        ],
                    },
                    {
                        title: 'Before saving',
                        checks: [
                            'Correct linked work.',
                            'Invoice number reviewed.',
                            'Amounts checked.',
                            'Status aligned with the document stage.',
                            'Client-specific fields completed.',
                        ],
                    },
                ],
                warnings: [
                    'If invoice and order do not match yet, keep the case traceable instead of forcing closure.',
                    'If you change the work in an invoice already partially filled, review the billing entity and amounts after the change.',
                ],
            },
            {
                id: 'tarifarios',
                icon: '📄',
                title: 'Rate sheets and contracts',
                intro:
                    'Rate sheets define the tariff lines applicable to works. They are linked to contracts. The chain contract → rate sheet → lines is the basis of order economic control.',
                subsections: [
                    {
                        title: 'Contract → rate sheet → lines cascade',
                        body:
                            'A rate sheet can only be linked to an active contract. If the contract does not exist or is inactive, the rate sheet lines will not be available in orders.',
                        checks: [
                            'Confirm the contract is active before creating or editing a rate sheet.',
                            'Verify the rate sheet has lines before assigning it to a work.',
                            'If no rate lines appear in an order, the first check is whether the work has an active contract or rate sheet assigned.',
                        ],
                    },
                ],
                warnings: [
                    'Do not delete rate sheet lines if there are active orders referencing them.',
                ],
            },
            {
                id: 'clients-stations',
                icon: '⛽',
                title: 'Clients and stations',
                intro:
                    'Clients and stations are master data. If they are mixed or duplicated, the error spreads to the rest of the ERP.',
                subsections: [
                    {
                        title: 'Clients',
                        bullets: [
                            'Each client must stay in the correct context.',
                            'Avoid near-duplicate names.',
                            'Use identifiers consistently.',
                        ],
                    },
                    {
                        title: 'Stations',
                        bullets: [
                            'Stations must remain separated by client.',
                            'Each station should keep searchable historical work.',
                            'Code, address and status help validate the correct station.',
                        ],
                    },
                    {
                        title: 'Before creating or correcting',
                        checks: [
                            'Search by name, code and common spelling variants.',
                            'Confirm the correct client before creating a new station.',
                        ],
                    },
                ],
            },
            {
                id: 'legalizations',
                icon: '📑',
                title: 'Legalizations',
                intro:
                    'Legalizations come from a separate control flow, but they should be treated as related information for works and projects.',
                subsections: [
                    {
                        title: 'How to read them',
                        bullets: [
                            'They may exist as a parallel control, but must connect to the main operation.',
                            'Their status should be visible when it affects review or closure.',
                        ],
                    },
                    {
                        title: 'What to review',
                        checks: [
                            'The legalization belongs to the correct work.',
                            'Its status is updated.',
                            'Relevant issues are not hidden in vague notes.',
                        ],
                    },
                ],
            },
            {
                id: 'closure',
                icon: '✅',
                title: 'Closure panel',
                badges: [{ label: 'Closure', color: 'bg-state-progress-bg text-state-progress-text' }],
                intro:
                    'Closing a work means blocking it for ordinary editing after a final review.',
                subsections: [
                    {
                        title: 'What closure implies',
                        bullets: [
                            'It locks the record for ordinary editing.',
                            'It requires review of dates, status, order, invoice, legalization and incidents.',
                            'It leaves traceability of who validated the final situation.',
                        ],
                    },
                    {
                        title: 'Before closing',
                        checks: [
                            'A real completion date exists.',
                            'The work is truly finished.',
                            'Order and invoice are traceable or the mismatch is documented.',
                            'Legalization is reviewed when relevant.',
                            'No blocking incident remains unresolved.',
                        ],
                    },
                ],
                warnings: [
                    'Closed works should remain blocked and only authorized profiles should modify them.',
                ],
            },
            {
                id: 'messages',
                icon: '💬',
                title: 'Internal messages',
                intro:
                    'Internal messages help coordinate operational work without relying on external email for every question.',
                subsections: [
                    {
                        title: 'Useful message content',
                        checks: [
                            'Affected module.',
                            'Reference number or work when available.',
                            'Short explanation of the issue or request.',
                            'Priority only when it really blocks work.',
                        ],
                    },
                ],
            },
            {
                id: 'support',
                icon: '🛟',
                title: 'Support',
                intro:
                    'Support is the channel for formal incident tracking. Valid for technical and functional issues.',
                subsections: [
                    {
                        title: 'When to open support',
                        bullets: [
                            'When you cannot continue working.',
                            'When data does not save and it is not a clear validation issue.',
                            'When module relations look inconsistent.',
                            'When context, permissions and filters have been reviewed and the issue persists.',
                        ],
                    },
                    {
                        title: 'Ticket states',
                        bullets: [
                            'Pending: ticket open, awaiting review.',
                            'In review: case being analyzed.',
                            'Resolved: case closed.',
                            'Archived: ticket archived as historical record.',
                        ],
                    },
                    {
                        title: 'What the request should include',
                        checks: [
                            'Affected screen or module.',
                            'Action performed.',
                            'Expected result.',
                            'Observed result.',
                            'Enough reference to reproduce the case.',
                        ],
                    },
                ],
                warnings: [
                    'Do not open multiple tickets for the same case.',
                ],
            },
            {
                id: 'admin',
                icon: '⚙️',
                title: 'Administration',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'Administration covers users, roles, contexts, master-data quality, maintenance and high-impact imports.',
                subsections: [
                    {
                        title: 'Typical responsibilities',
                        bullets: [
                            'Maintain users, roles and contexts.',
                            'Review imports and repeated incidents.',
                            'Keep client separation and structural data quality under control.',
                        ],
                    },
                ],
            },
            {
                id: 'audit',
                icon: '🕵️',
                title: 'Audit and traceability',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'The ERP must allow teams to reconstruct what happened, who changed it and when.',
                subsections: [
                    {
                        title: 'Why traceability matters',
                        bullets: [
                            'It supports status review, regularization and closure.',
                            'It explains why a record changed.',
                            'It helps historical and functional validation.',
                        ],
                    },
                ],
                warnings: [
                    'Do not justify important changes with vague notes.',
                ],
            },
            {
                id: 'imports',
                icon: '📥',
                title: 'Excel imports',
                badges: [{ label: 'Admin', color: 'bg-primary/15 text-primary' }],
                intro:
                    'Imports exist because the ERP still coexists with historical Excel data. The goal is not blind loading, but controlled normalization.',
                subsections: [
                    {
                        title: 'Expected import process',
                        checks: [
                            'File upload.',
                            'Preview.',
                            'Error detection.',
                            'Correction before confirmation.',
                            'Final confirmation with traceability.',
                        ],
                    },
                    {
                        title: 'What to review before confirming',
                        checks: [
                            'No obvious duplicates.',
                            'No mixed clients or stations from separate contexts.',
                            'Headers and data types match the expected structure.',
                            'Imported values respect the same normalization as manual forms.',
                        ],
                    },
                    {
                        title: 'What to avoid',
                        bullets: [
                            'Do not confirm a preview that already shows inconsistencies.',
                            'Do not import dirty historical labels without review.',
                            'Do not mix REPSOL and MOEVE references if they belong to separate scopes.',
                        ],
                    },
                ],
                warnings: [
                    'A bad import can contaminate several modules at once. Always review the preview.',
                ],
            },
            {
                id: 'reports',
                icon: '📈',
                title: 'Reports and data exploitation',
                intro:
                    'One of the ERP goals is to support reliable reporting without manual rebuilding every time. That depends on clean source data.',
                subsections: [
                    {
                        title: 'What reports should support',
                        bullets: [
                            'Separation by client or context.',
                            'Queries by station, work, order, invoice or status.',
                            'Operational and economic follow-up.',
                            'Reliable historical review.',
                        ],
                    },
                ],
            },
            {
                id: 'devices',
                icon: '📱',
                title: 'Good practices by device',
                intro:
                    'The ERP should remain usable on large screens, laptops and mobile devices. The interaction changes, but validation rules do not.',
                subsections: [
                    {
                        title: 'Large screen or laptop',
                        bullets: [
                            'Best for long tables, cross-checking columns, closure and imports.',
                        ],
                    },
                    {
                        title: 'Mobile',
                        bullets: [
                            'Suitable for quick consultation, filters, follow-up and small edits.',
                            'Forms collapse to one column when needed.',
                            'Wide tables may use internal horizontal scrolling.',
                        ],
                    },
                ],
            },
            {
                id: 'states_diagnostics',
                icon: '🔍',
                title: 'States and diagnostics',
                intro:
                    'This section covers available states per module and the automatic diagnostics the ERP shows in forms and panels.',
                subsections: [
                    {
                        title: 'Work states',
                        bullets: [
                            'Pending: registered, not yet started.',
                            'In progress: being executed.',
                            'Finished (TT): completed. Requires a real completion date.',
                            'Invoiced: work with issued invoice.',
                            'Closed: locked for ordinary editing.',
                            'Cancelled: cancelled work.',
                        ],
                    },
                    {
                        title: 'Invoice states',
                        bullets: [
                            'Pending: registered, not yet issued.',
                            'Issued: sent to client.',
                            'Sent: in collection process.',
                        ],
                    },
                    {
                        title: 'Support ticket states',
                        bullets: [
                            'Pending: open, awaiting review.',
                            'In review: being analyzed.',
                            'Resolved: closed.',
                            'Archived: stored as historical record.',
                        ],
                    },
                    {
                        title: 'Functional diagnostic in Masters',
                        body:
                            'The Masters module shows a diagnostic panel that detects configuration inconsistencies: contracts without billing entities, missing CIFs or broken dependencies.',
                        bullets: [
                            'Does not block the system but flags real operational risks.',
                            'Resolve alerts before creating dependent records.',
                        ],
                    },
                    {
                        title: 'Rate sheet cascade in orders',
                        body:
                            'Selecting a work in an order filters available rate lines by context, rate sheet and contract. Changing the work resets item rate line selections.',
                    },
                    {
                        title: 'Invoice work change cleanup',
                        body:
                            'Changing the work in an invoice automatically clears the billing entity and dependent fields. Review them before saving.',
                    },
                ],
            },
            {
                id: 'best_practices',
                icon: '✅',
                title: 'Best practices',
                intro:
                    'Following these practices reduces errors, duplicates and mismatches in daily operations.',
                checks: [
                    'Review the active context before creating or editing any record.',
                    'Do not mix MOEVE and REPSOL operations.',
                    'Search before creating: works, orders, clients and stations may already exist.',
                    'Review the contract and rate sheet before creating itemized orders.',
                    'Review amounts, quantities and lines before issuing an invoice.',
                    'Use the support module for incidents.',
                    'Do not modify closed or locked records without explicit authorization.',
                    'Check Masters diagnostics when alerts appear before creating dependent records.',
                ],
                warnings: [
                    'A record created in the wrong context may generate duplicates and mismatches that are hard to correct later.',
                    'Do not change statuses just to make a panel look cleaner. Status must reflect reality.',
                ],
            },
            {
                id: 'faq',
                icon: '❓',
                title: 'Frequently asked questions',
                intro:
                    'Answers to the most common questions by module, role and situation.',
                faq: [
                    {
                        q: 'Why can I not see all modules?',
                        a: 'The ERP shows only modules your role and permissions allow. Contact administration if you need additional access.',
                    },
                    {
                        q: 'Why can I not edit a closed work?',
                        a: 'Closed works are locked for ordinary editing. Only authorized profiles (Direction or Admin) can reopen a work, always with a documented reason.',
                    },
                    {
                        q: 'What do I do if no rate lines appear in an order?',
                        a: 'Check that the selected work has a contract or rate sheet with active lines assigned.',
                    },
                    {
                        q: 'What happens if I change the work in an invoice?',
                        a: 'The system clears the billing entity and other dependent fields automatically. Review and fill them again before saving.',
                    },
                    {
                        q: 'How do I know if I am working in MOEVE or REPSOL?',
                        a: 'The active context is shown in the sidebar or the context selector in the interface.',
                    },
                    {
                        q: 'Where do I report an incident?',
                        a: 'Use the Support module to open a ticket. Include the affected module, action, expected result and observed result.',
                    },
                    {
                        q: 'What do I do if an order and invoice do not match?',
                        a: 'Do not force closure. Keep the record visible for review and document the situation in notes or a support ticket.',
                    },
                    {
                        q: 'What does TT mean on a work?',
                        a: 'TT means finished work. It indicates the intervention was completed. A real completion date is required to set TT status.',
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

            {section.fields?.length > 0 && (
                <div className="grid gap-2 sm:grid-cols-2">
                    {section.fields.map((field) => (
                        <HelpField
                            key={`${section.id}-${field.label}`}
                            label={field.label}
                            description={field.description}
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

            {section.faq?.length > 0 && (
                <div className="space-y-3">
                    {section.faq.map((item, index) => (
                        <HelpFaqItem
                            key={`${section.id}-faq-${index + 1}`}
                            question={item.q}
                            answer={item.a}
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

export default function Help() {
    const user = usePage().props.auth.user;
    const { t, locale } = useI18n();
    const [openSections, setOpenSections] = useState(new Set(['intro']));
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

    // Admin sees every section. Other categories filter by SECTION_AUDIENCE.
    // Search operates only on sections already visible to this user.
    const visibleSections = useMemo(() => {
        if (category === HELP_CAT_ADMIN) return manual.sections;
        return manual.sections.filter((section) => {
            const allowed = SECTION_AUDIENCE[section.id] ?? ['todos'];
            return allowed.includes('todos') || allowed.includes(category);
        });
    }, [manual.sections, category]);

    const filteredSections = useMemo(() => {
        const query = search.trim();
        if (!query) return visibleSections;
        const normalizedQuery = normalizeSearchText(query);
        return visibleSections.filter((section) =>
            normalizeSearchText(section).includes(normalizedQuery),
        );
    }, [visibleSections, search]);

    const categoryConfig = {
        [HELP_CAT_ADMIN]:     {
            badge:  { label: 'Admin',              color: 'bg-primary/15 text-primary' },
            banner: {
                es: 'Manual completo — todas las secciones están disponibles para tu perfil.',
                en: 'Full manual — all sections are available for your profile.',
            },
        },
        [HELP_CAT_DIRECCION]: {
            badge:  { label: 'Dirección / Cierre', color: 'bg-state-progress-bg text-state-progress-text' },
            banner: {
                es: 'Manual Dirección / Cierre — incluye operativa general y el panel de cierre.',
                en: 'Direction / Closure manual — includes general operations and the closure panel.',
            },
        },
        [HELP_CAT_RESTO]:     {
            badge:  { label: 'Resto de usuarios',  color: 'bg-accent/15 text-accent' },
            banner: {
                es: 'Manual operativo — cubre los módulos de la operativa diaria.',
                en: 'Operational manual — covers the daily operational modules.',
            },
        },
    };
    const cfg    = categoryConfig[category] ?? categoryConfig[HELP_CAT_RESTO];
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

                <p className="text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('help.footer')}
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
