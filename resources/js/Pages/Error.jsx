import CieteMark from '@/Components/CieteMark';
import ErrorShellLayout from '@/Layouts/ErrorShellLayout';
import { Head } from '@inertiajs/react';

const COPY = {
    es: {
        pageTitle: 'Error',
        label: 'Entorno corporativo protegido',
        codeLabel: 'Error {status}',
        footer: 'ERP Ciete',
        statuses: {
            '401': {
                title: 'Necesitas una sesión válida',
                message: 'La acción solicitada requiere una sesión iniciada. Accede de nuevo y continúa desde el menú principal.',
            },
            '403': {
                title: 'Acceso no disponible para tu perfil',
                message: 'Tu sesión actual no tiene permisos suficientes para abrir esta sección. Puedes seguir navegando por el resto del portal.',
            },
            '404': {
                title: 'La página solicitada no está disponible',
                message: 'La ruta que intentabas abrir no existe o ya no forma parte del portal actual.',
            },
            '419': {
                title: 'La sesión de la operación ha caducado',
                message: 'La solicitud ya no es válida por seguridad y debe repetirse desde la pantalla actual.',
            },
            '500': {
                title: 'Se ha producido una incidencia interna temporal',
                message: 'El sistema no ha podido completar la solicitud correctamente. Vuelve a intentarlo en unos minutos.',
            },
            '503': {
                title: 'Servicio temporalmente no disponible',
                message: 'El portal está realizando tareas internas o no puede atender la solicitud en este momento.',
            },
            generic: {
                title: 'La solicitud no ha podido completarse',
                message: 'Se ha producido una incidencia no prevista.',
            },
        },
    },
    en: {
        pageTitle: 'Error',
        label: 'Protected corporate environment',
        codeLabel: 'Error {status}',
        footer: 'Ciete ERP',
        statuses: {
            '401': {
                title: 'A valid session is required',
                message: 'The requested action requires an active session. Sign in again and continue from the main navigation.',
            },
            '403': {
                title: 'This area is not available for your profile',
                message: 'Your current session does not have enough permissions to open this section.',
            },
            '404': {
                title: 'The requested page is not available',
                message: 'The route you tried to open does not exist or is no longer part of the current portal.',
            },
            '419': {
                title: 'The operation session has expired',
                message: 'The request is no longer valid for security reasons and must be repeated from the current screen.',
            },
            '500': {
                title: 'A temporary internal incident has occurred',
                message: 'The system could not complete the request correctly. Please try again in a few minutes.',
            },
            '503': {
                title: 'Service temporarily unavailable',
                message: 'The portal is performing internal tasks or cannot process the request right now.',
            },
            generic: {
                title: 'The request could not be completed',
                message: 'An unexpected incident has occurred.',
            },
        },
    },
};

function resolveLocale() {
    if (typeof document === 'undefined') {
        return 'es';
    }

    const lang = String(document.documentElement.lang || 'es').toLowerCase();

    return lang.startsWith('en') ? 'en' : 'es';
}

function format(template, params = {}) {
    return template.replace(/\{(\w+)\}/g, (_, token) => String(params[token] ?? `{${token}}`));
}

function resolveErrorContent(locale, status) {
    const catalog = COPY[locale] ?? COPY.es;
    const key = String(status);
    const entry = catalog.statuses[key] ?? catalog.statuses.generic;

    return {
        catalog,
        title: entry.title,
        message: entry.message,
    };
}

export default function ErrorPage({ status = 500 }) {
    const locale = resolveLocale();
    const { catalog, title, message } = resolveErrorContent(locale, status);
    const pageTitle = `${catalog.pageTitle} ${status}`;

    return (
        <ErrorShellLayout header={pageTitle} footerText={catalog.footer}>
            <Head title={pageTitle} />

            <section className="relative overflow-hidden rounded-[28px] border border-border bg-gradient-to-br from-surface via-surface to-surface-2 p-8 shadow-sm md:p-12">
                <div className="pointer-events-none absolute -left-12 top-6 h-28 w-28 rounded-full bg-primary/8 blur-3xl" />
                <div className="pointer-events-none absolute -right-12 bottom-6 h-32 w-32 rounded-full bg-accent/10 blur-3xl" />
                <div className="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />

                <div className="relative flex flex-col gap-10 lg:flex-row lg:items-center">
                    <div className="flex justify-center lg:w-[28%] lg:justify-start">
                        <div className="relative rounded-[26px] border border-border bg-surface/90 p-5 shadow-sm">
                            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(254,0,0,0.10),transparent_55%),radial-gradient(circle_at_bottom_left,rgba(0,62,255,0.10),transparent_55%)]" />
                            <CieteMark className="relative z-10 w-28 sm:w-32" />
                        </div>
                    </div>

                    <div className="max-w-3xl text-center lg:text-left">
                        <p className="text-[10px] font-bold uppercase tracking-[0.24em] text-text-hint">
                            {catalog.label}
                        </p>
                        <p className="mt-3 text-sm font-semibold uppercase tracking-[0.18em] text-(--ciete-red)">
                            {format(catalog.codeLabel, { status })}
                        </p>
                        <h1 className="mt-4 text-3xl font-semibold tracking-tight text-text-main sm:text-4xl">
                            {title}
                        </h1>
                        <p className="mt-4 max-w-2xl text-base leading-relaxed text-text-muted sm:text-lg">
                            {message}
                        </p>
                    </div>
                </div>
            </section>
        </ErrorShellLayout>
    );
}
