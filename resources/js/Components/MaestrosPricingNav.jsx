import { Link, usePage } from '@inertiajs/react';

const items = [
    {
        key: 'contratos',
        label: 'Contratos',
        routeName: 'maestros.contratos.index',
        permission: 'contratos.ver',
    },
    {
        key: 'tarifarios',
        label: 'Tarifarios',
        routeName: 'maestros.tarifarios.index',
        permission: 'tarifarios.ver',
    },
    {
        key: 'lineas',
        label: 'Lineas',
        routeName: 'maestros.tarifario-lineas.index',
        permission: 'tarifario_lineas.ver',
    },
    {
        key: 'sociedades',
        label: 'Sociedades / CIF',
        routeName: 'maestros.sociedades.index',
        permission: 'sociedades_facturadoras.ver',
    },
];

function canSee(user, permission) {
    return Boolean(user?.is_director || user?.permission_slugs?.includes(permission));
}

export default function MaestrosPricingNav({ current }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    const visibleItems = items.filter((item) => canSee(user, item.permission));

    if (visibleItems.length <= 1) {
        return null;
    }

    return (
        <section className="rounded-2xl border border-border bg-surface px-4 py-3 shadow-sm">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-text-hint">
                    Contratos y tarifas
                </p>

                <nav className="flex flex-wrap gap-2" aria-label="Navegacion maestros de contratacion y tarifas">
                    {visibleItems.map((item) => {
                        const isActive = item.key === current;

                        return (
                            <Link
                                key={item.key}
                                href={route(item.routeName)}
                                className={`rounded-full px-3 py-2 text-sm font-semibold transition ${
                                    isActive
                                        ? 'bg-(--ciete-red) text-white'
                                        : 'border border-border bg-surface text-text-main hover:bg-surface-2'
                                }`}
                            >
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
            </div>
        </section>
    );
}
