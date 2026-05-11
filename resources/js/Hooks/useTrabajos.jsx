import axios from 'axios';
import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

function extractErrors(error) {
    if (error?.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }

    return {};
}

export function useTrabajos() {
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const clearErrors = useCallback(() => {
        setErrors({});
    }, []);

    const clearFieldError = useCallback((field) => {
        setErrors((currentErrors) => {
            if (!currentErrors[field]) {
                return currentErrors;
            }

            const nextErrors = { ...currentErrors };
            delete nextErrors[field];

            return nextErrors;
        });
    }, []);

    const getTrabajo = useCallback(async (id) => {
        setLoading(true);

        try {
            const response = await axios.get(`/api/v1/trabajos/${id}`);
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const saveTrabajo = useCallback(async (payload, id = null) => {
        setLoading(true);
        setErrors({});

        try {
            const response = id
                ? await axios.put(`/api/v1/trabajos/${id}`, payload)
                : await axios.post('/api/v1/trabajos', payload);

            return response.data;
        } catch (error) {
            setErrors(extractErrors(error));
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    const deleteTrabajo = useCallback(async (id) => {
        setLoading(true);

        try {
            await axios.delete(`/api/v1/trabajos/${id}`);
        } finally {
            setLoading(false);
        }
    }, []);

    const irAListado = () => router.visit(route('trabajos.index'));

    const irACrear = () => router.visit(route('trabajos.create'));

    const irAEditar = (id) => router.visit(route('trabajos.edit', id));

    const eliminarTrabajo = (id, onSuccess) => {
        router.delete(route('trabajos.destroy', id), {
            onSuccess: () => {
                if (onSuccess) onSuccess();
            },
            preserveScroll: true,
        });
    };

    return {
        getTrabajo,
        saveTrabajo,
        deleteTrabajo,
        clearErrors,
        clearFieldError,
        loading,
        errors,
        irAListado,
        irACrear,
        irAEditar,
        eliminarTrabajo
    };
}
