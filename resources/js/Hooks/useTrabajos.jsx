import { router } from '@inertiajs/react';

export function useTrabajos() {

    const irAListado = () => router.visit(route('trabajos.index'));

    const irACrear = () => router.visit(route('trabajos.create'));

    const irAEditar = (id) => router.visit(route('trabajos.edit', id));

    const eliminarTrabajo = (id, onSuccess) => {
        if (confirm('¿Estás seguro de que deseas eliminar este trabajo?')) {
            router.delete(route('trabajos.destroy', id), {
                onSuccess: () => {
                    if (onSuccess) onSuccess();
                },
                preserveScroll: true,
            });
        }
    };

    return {
        irAListado,
        irACrear,
        irAEditar,
        eliminarTrabajo
    };
}
