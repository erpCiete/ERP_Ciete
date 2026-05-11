import ContextualPageHeader from '@/Components/ContextualPageHeader';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Building2,
    Factory,
    FileBadge,
    ListChecks,
    MapPin,
    ReceiptText,
    Tags,
    Users,
} from 'lucide-react';

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

const modules = [
    {
        key: 'usuarios',
        title: 'Usuarios',
        description: 'Usuarios reales, roles, contexto principal y activacion de acceso.',
        routeName: 'admin.users.index',
        permissionKey: 'usuarios',
        icon: Users,
    },
    {
        key: 'empresas',
        title: 'Empresas / clientes',
        description: 'Clientes y empresas base que se seleccionan en trabajos, contratos y facturacion.',
        routeName: 'clientes.index',
        permissionKey: 'clientes',
        icon: Building2,
    },
    {
        key: 'estaciones',
        title: 'Estaciones',
        description: 'Codigo, nombre, municipio y provincia de estaciones por contexto.',
        routeName: 'estaciones.index',
        permissionKey: 'estaciones',
        icon: MapPin,
    },
    {
        key: 'contratos',
        title: 'Contratos',
        description: 'Contratos marco o directos asociados a cliente y contexto.',
        routeName: 'maestros.contratos.index',
        permissionKey: 'contratos',
        icon: FileBadge,
    },
    {
        key: 'sociedades',
        title: 'Sociedades facturadoras',
        description: 'Relaciones contrato-sociedad/CIF permitidas para validar facturas.',
        routeName: 'maestros.sociedades.index',
        permissionKey: 'sociedades',
        icon: Factory,
    },
    {
        key: 'tarifarios',
        title: 'Tarifarios',
        description: 'Versiones de tarifa vigentes por contrato y contexto.',
        routeName: 'maestros.tarifarios.index',
        permissionKey: 'tarifarios',
        icon: ReceiptText,
    },
    {
        key: 'lineas_tarifario',
        title: 'Lineas de tarifario',
        description: 'Codigos, actuaciones, importes y unidades facturables.',
        routeName: 'maestros.tarifario-lineas.index',
        permissionKey: 'lineas',
        icon: ListChecks,
    },
    {
        key: 'catalogos',
        title: 'Catalogos auxiliares',
        description: 'Unidades, tipos de documento y tipos de trabajo en lectura de apoyo.',
        routeName: null,
        permissionKey: null,
        icon: Tags,
    },
];

export default function MaestrosIndex({ summary = {}, can = {} }) {
    const { auth } = usePage().props;
    const activeContext = auth?.user?.active_context;

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">Maestros</h2>}
        >
            <Head title="Maestros" />

            <div className="ciete-page ciete-page-wide">
                <ContextualPageHeader
                    eyebrow="Datos maestros"
                    title="Maestros"
                    description="Panel separado de la operativa diaria para preparar y mantener datos base del ERP CIETE."
                />

                {activeContext?.is_all && (
                    <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Selecciona MOEVE, REPSOL u OTROS CLIENTES para crear datos maestros.
                    </div>
                )}

                <section className="mb-6 rounded-lg border border-border bg-surface px-4 py-4">
                    <h3 className="text-sm font-semibold text-text-main">Separacion funcional</h3>
                    <p className="mt-1 text-sm text-text-muted">
                        Este panel gobierna empresas, estaciones, contratos, sociedades, tarifarios y catalogos. Trabajos,
                        pedidos, facturas, cierre y auditoria siguen en sus modulos operativos.
                    </p>
                </section>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {modules.map((module) => {
                        const Icon = module.icon;
                        const permissions = module.permissionKey ? can[module.permissionKey] : null;
                        const canView = module.permissionKey ? Boolean(permissions?.view) : true;
                        const href = module.routeName && hasRoute(module.routeName) ? route(module.routeName) : null;

                        if (!canView) {
                            return null;
                        }

                        return (
                            <article
                                key={module.key}
                                className="rounded-lg border border-border bg-surface p-4 shadow-sm"
                            >
                                <div className="flex items-start gap-3">
                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-surface-2 text-(--ciete-red)">
                                        <Icon className="size-5" aria-hidden="true" />
                                    </div>
                                    <div className="min-w-0">
                                        <h3 className="text-base font-semibold text-text-main">{module.title}</h3>
                                        <p className="mt-1 text-sm text-text-muted">{module.description}</p>
                                    </div>
                                </div>

                                <div className="mt-4 flex items-center justify-between gap-3">
                                    <span className="text-sm font-semibold text-text-main">
                                        {summary[module.key] ?? 0}
                                    </span>
                                    {href ? (
                                        <Link
                                            href={href}
                                            className="inline-flex items-center rounded-md border border-border px-3 py-2 text-xs font-semibold uppercase tracking-widest text-text-main transition hover:bg-surface-2"
                                        >
                                            Abrir
                                        </Link>
                                    ) : (
                                        <span className="text-xs font-semibold uppercase tracking-widest text-text-hint">
                                            Lectura
                                        </span>
                                    )}
                                </div>
                            </article>
                        );
                    })}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
