# Requisitos y acuerdos vigentes ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`.

Estado: documento vivo resumido.

Referencia de autoridad: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`, aterrizada en `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md` y priorizada en `docs/02_CLIENTE/tareasComparar.md`.

## Requisitos funcionales vigentes

| ID    | Requisito                                                              | Prioridad |
| ----- | ---------------------------------------------------------------------- | --------- |
| RF-01 | Separar datos por contexto real: MOEVE, REPSOL y OTROS CLIENTES        | P0        |
| RF-02 | Tratar TODOS como vista global operativa, no como contexto real        | P0        |
| RF-03 | Bloquear creacion desde TODOS y pedir contexto real                    | P0        |
| RF-04 | Permitir editar registros existentes desde TODOS segun permisos        | P0        |
| RF-05 | Crear trabajos sin pedido                                              | P0        |
| RF-06 | Mantener relacion Trabajo 1:N Pedido                                   | P0        |
| RF-07 | Mantener relacion Pedido 1:N PedidoItem                                | P0        |
| RF-08 | No borrar/recrear `pedido_items` si pueden estar vinculados a facturas | P0        |
| RF-09 | Facturar por `factura_items`, no por trabajo unico                     | P0        |
| RF-10 | Validar contrato/tarifa comun en una factura                           | P0        |
| RF-11 | Validar sociedad/CIF permitida para contrato/tarifa/grupo              | P0        |
| RF-12 | Calcular importe asignado, diferencia y estado de cuadre de factura    | P0        |
| RF-13 | Usar estados funcionales de trabajo aprobados por CIETE                | P0        |
| RF-14 | Mantener cancelados visibles, filtrables y al final                    | P0        |
| RF-15 | Usar Ciete Excel como tabla densa editable por campo                   | P0        |
| RF-16 | Mantener Ciete Moderno como ficha/formulario limpio                    | P1        |
| RF-17 | Mostrar Auditoria como modulo visible y operativo                      | P1        |
| RF-18 | No auditar ruido visual como actividad relevante                       | P0        |
| RF-19 | No borrar estaciones fisicamente                                       | P1        |
| RF-20 | Mantener cobros fuera del flujo principal                              | P2        |

## Acuerdos de producto

- El trabajo es la entidad operativa central.
- El item de pedido es la unidad economica real.
- La factura agrupa items.
- El ERP termina en factura.
- Direccion es rol funcional operativo.
- Admin queda como rol tecnico/soporte.
- OTROS CLIENTES es contexto real.
- TODOS no es OTROS CLIENTES.

## Acuerdos tecnicos

- El contexto activo vive en sesion backend.
- Puede recordarse el ultimo contexto entre sesiones como comodidad.
- Crear desde TODOS debe bloquearse en backend.
- Editar desde TODOS debe validarse por permisos y contexto real del registro.
- `facturas.id_trabajo` queda nullable/legacy.
- `factura_pedidos` queda temporal/legacy.
- Los contratos antiguos se conservan, pero no se ofrecen para nuevas operaciones.
