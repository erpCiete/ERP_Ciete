# Equipo y roles ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: documento vivo resumido.

Fuente funcional:

`docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`

## Roles funcionales vigentes

| Rol | Uso |
|---|---|
| Direccion | Rol funcional operativo, supervision, usuarios y Auditoria |
| Ejecucion | Operativa general de trabajos/pedidos segun permisos |
| Ejecucion MOEVE | Operativa limitada al contexto MOEVE |
| Ejecucion REPSOL | Operativa limitada al contexto REPSOL |
| Contabilidad | Facturas y control economico |
| Soporte/admin tecnico | Soporte, mantenimiento y tareas tecnicas |

## Reglas

- Admin no es rol operativo normal de CIETE.
- Direccion y admin tecnico pueden gestionar usuarios.
- Direccion no debe ver pantallas tecnicas innecesarias.
- Los permisos reales deben gobernar acciones de ver, crear, editar, exportar y limpiar.

## Contextos

- MOEVE, REPSOL y OTROS CLIENTES son contextos reales.
- TODOS es vista global operativa segun permisos.
- TODOS no crea registros nuevos.
