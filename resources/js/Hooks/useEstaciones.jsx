import axios from 'axios';
import { useCallback, useState, useEffect } from 'react';

function extractErrors(error) {
    if (error?.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }

    return {};
}

export function useEstaciones() {
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [estaciones, setEstaciones] = useState([]);

    const getEstaciones = useCallback(async (params = {}) => {
        setLoading(true);

        try {
            const response = await axios.get('/api/v1/estaciones', { params });
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

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

    // Cargar estaciones al montar el componente
    useEffect(() => {
        let isMounted = true;

        const loadEstaciones = async () => {
            try {
                const data = await getEstaciones();
                if (isMounted && data) {
                    setEstaciones(data.data || data);
                }
            } catch (err) {
                if (isMounted) {
                    console.error('Error cargando estaciones:', err);
                    setEstaciones([]);
                }
            }
        };

        loadEstaciones();

        return () => {
            isMounted = false;
        };
    }, []);

    const getEstacion = useCallback(async (id) => {
        setLoading(true);

        try {
            const response = await axios.get(`/api/v1/estaciones/${id}`);
            return response.data;
        } finally {
            setLoading(false);
        }
    }, []);

    const saveEstacion = useCallback(async (payload, id = null) => {
        setLoading(true);
        setErrors({});

        try {
            const response = id
                ? await axios.put(`/api/v1/estaciones/${id}`, payload)
                : await axios.post('/api/v1/estaciones', payload);

            return response.data;
        } catch (error) {
            setErrors(extractErrors(error));
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    const deleteEstacion = useCallback(async (id) => {
        setLoading(true);

        try {
            await axios.delete(`/api/v1/estaciones/${id}`);
        } finally {
            setLoading(false);
        }
    }, []);

    return { 
        getEstaciones,
        getEstacion,
        saveEstacion,
        deleteEstacion,
        clearErrors,
        clearFieldError,
        loading,
        errors,
        estaciones,
    };
}
