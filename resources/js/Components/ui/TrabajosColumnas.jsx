import React from 'react';

/**
 * TrabajosColumnas
 * Componente que renderiza columnas condicionales según el contexto MOEVE/REPSOL.
 *
 * Props:
 * - isMoeve (boolean): Si true, muestra columnas MOEVE (Contrato, Categoría)
 * - isRepsol (boolean): Si true, muestra columnas REPSOL (Tipo documento, Tipo trabajo, Nº aviso)
 * - trabajo (object): Objeto del trabajo con los datos
 *
 * Columnas MOEVE: Contrato, Categoría
 * Columnas REPSOL: Tipo documento, Tipo trabajo, Nº aviso
 */
export default function TrabajosColumnas({ isMoeve = false, isRepsol = false, trabajo = {} }) {
    const contratoValue = trabajo.contrato ?? trabajo.contrato_nombre ?? '—';
    const categoriaValue = trabajo.categoria ?? trabajo.categoria_nombre ?? '—';
    const tipoDocumentoValue = trabajo.tipoDocumento ?? trabajo.tipo_documento ?? '—';
    const tipoTrabajoValue = trabajo.tipoTrabajo ?? trabajo.tipo_trabajo ?? '—';
    const numeroAvisoValue = trabajo.numeroAviso ?? trabajo.numero_aviso ?? '—';

    return (
        <>
            {isMoeve && (
                <>
                    <td className="px-4 py-3 text-sm text-text-main">{contratoValue}</td>
                    <td className="px-4 py-3 text-sm text-text-main">{categoriaValue}</td>
                </>
            )}
            {isRepsol && (
                <>
                    <td className="px-4 py-3 text-sm text-text-main">{tipoDocumentoValue}</td>
                    <td className="px-4 py-3 text-sm text-text-main">{tipoTrabajoValue}</td>
                    <td className="px-4 py-3 text-sm text-text-main">{numeroAvisoValue}</td>
                </>
            )}
        </>
    );
}
