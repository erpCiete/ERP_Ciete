import axios from 'axios';
import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

function extractErrors(error) {
    if (error?.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }
    return {};
}

export function usePedidos() {
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

    const getPedido = useCallback(async (id) => {
        setLoading(true);
        try {
            const response = await axios.get(`/api/v1/pedidos/${id}`);
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const savePedido = useCallback(async (payload, id = null) => {
        setLoading(true);
        setErrors({});
        try {
            const response = id
                ? await axios.put(`/api/v1/pedidos/${id}`, payload)
                : await axios.post('/api/v1/pedidos', payload);
            return response.data;
        } catch (error) {
            setErrors(extractErrors(error));
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    const deletePedido = useCallback(async (id) => {
        setLoading(true);
        try {
            await axios.delete(`/api/v1/pedidos/${id}`);
        } finally {
            setLoading(false);
        }
    }, []);

    const irAListado = () => router.visit(route('pedidos.index'));
    const irACrear = () => router.visit(route('pedidos.create'));
    const irAEditar = (id) => router.visit(route('pedidos.edit', id));

    const eliminarPedido = (id, onSuccess) => {
        router.delete(route('pedidos.destroy', id), {
            onSuccess: () => {
                if (onSuccess) onSuccess();
            },
            preserveScroll: true,
        });
    };

    return {
        getPedido,
        savePedido,
        deletePedido,
        clearErrors,
        clearFieldError,
        loading,
        errors,
        irAListado,
        irACrear,
        irAEditar,
        eliminarPedido
    };
}
