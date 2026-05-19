import axios from 'axios';
import { useEffect, useState } from 'react';

function sameValue(a, b) {
    return String(a ?? '') === String(b ?? '');
}

function readConflict(responseData, attemptedValue, fieldName) {
    return {
        message: responseData?.message ?? 'Este campo fue modificado por otro usuario.',
        campo: responseData?.campo ?? fieldName,
        currentValue: responseData?.valor_actual ?? responseData?.current_value ?? null,
        myValue: responseData?.valor_intentado ?? attemptedValue,
        currentUpdatedAt: responseData?.updated_at_actual ?? responseData?.current_updated_at ?? null,
        usuarioModificacion: responseData?.usuario_modificacion ?? null,
        modificadoRecientemente: Boolean(responseData?.modificado_recientemente ?? false),
    };
}

/**
 * Optimistic single-field persistence with updated_at conflict detection.
 */
export function useOptimisticField({
    entityId,
    entityUpdatedAt,
    fieldName,
    initialValue,
    patchRoute,
    onSaved,
}) {
    const [value, setValue] = useState(initialValue ?? '');
    const [savedValue, setSavedValue] = useState(initialValue ?? '');
    const [updatedAt, setUpdatedAt] = useState(entityUpdatedAt ?? null);
    const [isSaving, setIsSaving] = useState(false);
    const [error, setError] = useState(null);
    const [conflict, setConflict] = useState(null);

    useEffect(() => {
        const nextValue = initialValue ?? '';
        setValue(nextValue);
        setSavedValue(nextValue);
        setError(null);
        setConflict(null);
    }, [entityId, fieldName, initialValue]);

    useEffect(() => {
        setUpdatedAt(entityUpdatedAt ?? null);
    }, [entityId, entityUpdatedAt]);

    async function save(nextValue = value, options = {}) {
        if (!options.force && sameValue(nextValue, savedValue)) {
            setError(null);
            return true;
        }

        setIsSaving(true);
        setError(null);
        setConflict(null);

        try {
            const response = await axios.patch(patchRoute, {
                campo: fieldName,
                valor: nextValue,
                updated_at: options.updatedAt ?? updatedAt,
            });

            const responseData = response.data ?? {};
            const resolvedValue = responseData.valor ?? responseData.trabajo?.[fieldName] ?? nextValue;
            const resolvedUpdatedAt = responseData.updated_at ?? responseData.trabajo?.updated_at ?? updatedAt;

            setValue(resolvedValue ?? '');
            setSavedValue(resolvedValue ?? '');
            setUpdatedAt(resolvedUpdatedAt);
            onSaved?.({
                ...responseData,
                campo: responseData.campo ?? fieldName,
                valor: resolvedValue,
                updated_at: resolvedUpdatedAt,
            });

            return true;
        } catch (err) {
            const responseData = err.response?.data ?? {};

            if (err.response?.status === 409) {
                const nextConflict = readConflict(responseData, nextValue, fieldName);
                setConflict(nextConflict);
                setError(nextConflict.message);
                return false;
            }

            const message = responseData.message
                ?? Object.values(responseData.errors ?? {})?.flat()?.[0]
                ?? 'No se pudo guardar el campo.';
            setError(message);
            return false;
        } finally {
            setIsSaving(false);
        }
    }

    function cancel() {
        setValue(savedValue ?? '');
        setError(null);
        setConflict(null);
    }

    async function resolveConflict(action = 'reload') {
        if (!conflict) {
            return false;
        }

        if (action === 'cancel') {
            cancel();
            return true;
        }

        if (action === 'reload' || action === 'keepTheirs') {
            const serverValue = conflict.currentValue ?? '';
            setValue(serverValue);
            setSavedValue(serverValue);
            setUpdatedAt(conflict.currentUpdatedAt ?? updatedAt);
            setConflict(null);
            setError(null);
            onSaved?.({
                campo: conflict.campo ?? fieldName,
                valor: conflict.currentValue,
                updated_at: conflict.currentUpdatedAt ?? updatedAt,
            });
            return true;
        }

        if (action === 'keepMine' || action === 'retry') {
            return save(conflict.myValue, {
                force: true,
                updatedAt: conflict.currentUpdatedAt ?? updatedAt,
            });
        }

        return false;
    }

    return {
        value,
        setValue,
        setLocalValue: setValue,
        isSaving,
        error,
        conflict,
        save,
        cancel,
        resolveConflict,
    };
}
