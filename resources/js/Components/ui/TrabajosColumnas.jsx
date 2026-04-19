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
    // Usamos Optional Chaining (?.) para intentar leer el '.nombre'.
// Si el objeto existe, pinta el nombre. Si el backend lo ha ocultado por el contexto, pinta '—'.
    const contratoValue = trabajo.contrato?.nombre ?? trabajo.contrato_nombre ?? '—';
    const categoriaValue = trabajo.categoria?.nombre ?? trabajo.categoria_nombre ?? '—'; 
    const tipoDocumentoValue = trabajo.tipo_documento?.nombre ?? trabajo.tipoDocumento?.nombre ?? '—';
    const tipoTrabajoValue = trabajo.tipo_trabajo?.nombre ?? trabajo.tipoTrabajo?.nombre ?? '—';
    const numeroAvisoValue = trabajo.numero_aviso ?? trabajo.numeroAviso ?? '—';

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
