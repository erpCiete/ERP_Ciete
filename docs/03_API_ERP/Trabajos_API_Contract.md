# Contrato tecnico vigente - Trabajos, pedidos y facturacion

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: contrato tecnico vivo compactado.

Fuente funcional: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Este documento sustituye el contrato API anterior que trataba `facturas.id_trabajo` y `factura_pedidos` como flujo principal. Esas estructuras quedan como legacy/compatibilidad.

## Alcance técnico de la API

La API debe:

- Autenticar usuarios segun la configuracion vigente del ERP.
- Exponer listados contextuales de trabajos, pedidos, facturas, estaciones y maestros.
- Aplicar aislamiento por contexto real.
- Permitir vista global TODOS para ver, filtrar y editar registros existentes segun permisos.
- Bloquear cualquier creacion desde TODOS.
- Crear trabajos sin pedido.
- Asociar pedidos a trabajos.
- Gestionar `pedido_items` como unidad economica real.
- Gestionar facturas por `factura_items` como flujo funcional principal.
- Registrar Auditoria operativa relevante.

La API no debe:

- Tratar TODOS como contexto real.
- Tratar OTROS CLIENTES como TODOS.
- Facturar como hija de un unico trabajo.
- Borrar historico fisicamente en entidades con trazabilidad.
- Borrar/recrear `pedido_items` que puedan estar facturados.
- Registrar `interface_mode`, tema, idioma o preferencias visuales como actividad operativa relevante.
- Incluir cobros, presupuestos, legalizaciones completas, importacion Excel avanzada, actualizacion automatica de estaciones o costes como flujo principal P0.

## 1. Contextos

| Concepto | Regla |
|---|---|
| MOEVE | Contexto real |
| REPSOL | Contexto real |
| OTROS CLIENTES | Contexto real |
| TODOS | Vista global operativa, no contexto real |

Reglas API:

- GET/listados pueden trabajar desde TODOS segun permisos y contextos accesibles.
- PATCH/PUT sobre registro existente puede trabajar desde TODOS si el usuario tiene permiso y acceso al contexto real del registro.
- POST debe bloquearse desde TODOS.
- DELETE fisico debe evitarse en entidades con historico.

## 2. Trabajos

Entidad principal: `trabajos`.

Relaciones:

- Trabajo pertenece a contexto real.
- Trabajo puede pertenecer a estacion.
- Trabajo puede tener contrato/tarifario.
- Trabajo 1:N pedidos.

Estados funcionales:

- `en_curso`
- `terminado`
- `pendiente_facturar`
- `facturado`
- `finalizado`
- `cancelado`

Compatibilidad:

- `borrador` puede existir en datos/codigo legacy, pero no es estado funcional CIETE.
- `cerrado` puede existir en datos/codigo legacy, pero la decision funcional vigente usa `finalizado`.

PATCH por campo:

- Endpoint esperado: `PATCH /api/v1/trabajos/{trabajo}/campo`.
- Payload:
  - `campo`
  - `valor`
  - `updated_at`
- Debe validar permisos, contexto, campo editable y conflicto por `updated_at`.

## 3. Pedidos

Entidad: `pedidos`.

Reglas:

- Pedido pertenece a un solo trabajo.
- Trabajo puede tener varios pedidos.
- Pedido puede llegar despues de crear o terminar el trabajo.
- Pedido puede tener varios `pedido_items`.

Campos economicos relevantes:

- `importe_pedido`
- `importe_solicitado`
- `importe_facturado`
- `fecha_solicitud`
- `fecha_recepcion`

## 4. Pedido items

Entidad: `pedido_items`.

Reglas:

- Es la unidad economica/facturable real.
- Puede venir de `tarifario_lineas`.
- Puede facturarse parcialmente.
- No debe borrarse/recrearse si puede estar vinculado a facturacion.
- Debe actualizarse por ID.

## 5. Facturas

Entidad principal: `facturas`.

Relacion funcional principal:

`factura -> factura_items -> pedido_items -> pedidos -> trabajos`

Legacy:

- `facturas.id_trabajo`: nullable/legacy.
- `factura_pedidos`: temporal/legacy.

Reglas:

- Una factura puede incluir items de varios pedidos.
- Una factura puede incluir items de varios trabajos.
- Todos los items deben compartir contrato/tarifa compatible.
- La sociedad/CIF debe estar permitida para contrato/tarifa/grupo.

Campos de listado plano:

- `numero_factura`
- sociedad
- CIF
- fecha
- importe
- importe asignado
- diferencia
- estado de cuadre

Calculos:

- `importe_asignado = sum(factura_items.importe_facturado)`
- `diferencia = importe_factura - importe_asignado`
- `estado_cuadre`:
  - `cuadrada`
  - `con_diferencia`
  - `sin_items`

## 6. Contratos, tarifas y sociedades

Entidades:

- `contratos`
- `tarifarios`
- `tarifario_lineas`
- `empresas` para sociedades/CIF

Relacion pendiente/necesaria:

- sociedad/CIF permitida por contrato/tarifa.

Nombre orientativo de tabla si se implementa:

- `contrato_empresas_facturadoras`

No crear una estructura mayor si esta relacion minima resuelve el caso.

## 7. Auditoria

Debe registrar actividad operativa relevante:

- trabajos
- pedidos
- pedido_items
- facturas
- factura_items
- estaciones
- empresas/sociedades
- contratos/tarifas
- importaciones relevantes

No debe registrar como actividad relevante:

- `interface_mode`
- tema
- idioma
- preferencias visuales
- cambios cosmeticos

## 8. Fuera del contrato principal actual

- Cobros.
- Presupuestos.
- Legalizaciones completas.
- Importacion Excel avanzada.

Pueden existir rutas o tablas legacy, pero no deben bloquear el flujo P0.
