import axios from 'axios';
import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

/**
 * Helper para extraer errores de validación de Laravel (422)
 */
function extractErrors(error) {
    if (error?.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }
    return {};
}

export function useFacturas() {
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const clearErrors = useCallback(() => {
        setErrors({});
    }, []);

    const clearFieldError = useCallback((field) => {
        setErrors((currentErrors) => {
            if (!currentErrors[field]) return currentErrors;
            const nextErrors = { ...currentErrors };
            delete nextErrors[field];
            return nextErrors;
        });
    }, []);

    // ─── Operaciones de API (Axios) ──────────────────────────────────────────

    const getFactura = useCallback(async (id) => {
        setLoading(true);
        try {
            const response = await axios.get(`/api/v1/facturas/${id}`);
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const saveFactura = useCallback(async (payload, id = null) => {
        setLoading(true);
        setErrors({});
        try {
            const response = id
                ? await axios.put(`/api/v1/facturas/${id}`, payload)
                : await axios.post('/api/v1/facturas', payload);
            return response.data;
        } catch (error) {
            setErrors(extractErrors(error));
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    const deleteFactura = useCallback(async (id) => {
        setLoading(true);
        try {
            await axios.delete(`/api/v1/facturas/${id}`);
        } finally {
            setLoading(false);
        }
    }, []);

    // ─── Navegación y Acciones de UI (Inertia) ───────────────────────────────

    const irAListado = () => router.visit(route('facturas.index'));
    const irACrear   = () => router.visit(route('facturas.create'));
    const irAEditar  = (id) => router.visit(route('facturas.edit', id));

    const eliminarFactura = (id, onSuccess) => {
        router.delete(route('facturas.destroy', id), {
            onSuccess: () => {
                if (onSuccess) onSuccess();
            },
            preserveScroll: true,
        });
    };

    return {
        // Estado
        loading,
        errors,
        // Limpieza de errores
        clearErrors,
        clearFieldError,
        // Métodos API
        getFactura,
        saveFactura,
        deleteFactura,
        // Navegación
        irAListado,
        irACrear,
        irAEditar,
        eliminarFactura,
    };
}
