import { usePage } from '@inertiajs/react';

const hasRoute = (name) => {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
};

export function useMastersBackLink() {
    const { auth } = usePage().props;
    const user = auth?.user;

    if (user?.can_access_admin_panel && !user?.is_director && hasRoute('admin.dashboard')) {
        return {
            href: route('admin.dashboard'),
            label: 'Volver a panel de administrador',
        };
    }

    if (hasRoute('maestros.index')) {
        return {
            href: route('maestros.index'),
            label: 'Volver a maestros',
        };
    }

    return {
        href: null,
        label: 'Volver',
    };
}
