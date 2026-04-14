import axios from 'axios';
import { useCallback, useState } from 'react';

function extractErrors(error) {
    if (error?.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }

    return {};
}

export function useClientes() {
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

    const getClientes = useCallback(async (params = {}) => {
        setLoading(true);

        try {
            const response = await axios.get('/api/v1/clientes', { params });
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const getCliente = useCallback(async (id) => {
        setLoading(true);

        try {
            const response = await axios.get(`/api/v1/clientes/${id}`);
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const saveCliente = useCallback(async (payload, id = null) => {
        setLoading(true);
        setErrors({});

        try {
            const response = id
                ? await axios.put(`/api/v1/clientes/${id}`, payload)
                : await axios.post('/api/v1/clientes', payload);

            return response.data;
        } catch (error) {
            setErrors(extractErrors(error));
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    const deleteCliente = useCallback(async (id) => {
        setLoading(true);

        try {
            await axios.delete(`/api/v1/clientes/${id}`);
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        getClientes,
        getCliente,
        saveCliente,
        deleteCliente,
        clearErrors,
        clearFieldError,
        loading,
        errors,
    };
}
