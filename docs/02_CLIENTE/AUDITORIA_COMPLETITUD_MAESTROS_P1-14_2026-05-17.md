# Auditoria de completitud de maestros por contexto P1-14 2026-05-17

> **Documento vivo.**  
> Fuente funcional principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`  
> Documento operativo vinculado: `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`  
> Backlog maestro vinculado: `docs/02_CLIENTE/tareasComparar.md`  
> Base auditada: `abaco_ciete` mediante consultas read-only via bootstrap Laravel local.  
> Nota de reconciliacion 2026-05-18: auditoria cerrada sobre muestra local/demo reducida; pendiente de repetir sobre base post-importacion real de P1-12.

## 1. Alcance y metodo

Esta auditoria P1-14 se ha ejecutado en modo solo diagnostico, sin tocar codigo, datos, seeders, migraciones ni importaciones. El objetivo ha sido medir si cada contexto tiene dato maestro suficiente para soportar el flujo base real:

- `Trabajo -> Pedido -> Factura`
- separacion estricta por contexto,
- contrato/tarifario compatibles,
- y sociedad/CIF valida cuando existe facturacion.

Fuentes revisadas en esta auditoria:

- Transcripcion funcional y backlog vivo.
- Modelos maestros: `ContextoCliente`, `Contrato`, `ContratoEmpresaFacturadora`, `Empresa`, `Tarifario`, `TarifarioLinea`, `Unidad`, `EstacionServicio`, `TipoDocumento`, `TipoTrabajo`, `User`.
- Filtros y validaciones reales del flujo:
    - `routes/web.php` (`buildEmpresasFacturadoras`, `buildFacturacionCatalogos`, `buildPedidoItemsFacturables`)
    - `app/Http/Controllers/Api/FacturaController.php` (`validateEmpresaFacturadora`)
    - `app/Http/Requests/Api/StoreTrabajoRequest.php`
    - `app/Http/Requests/Api/StorePedidoRequest.php`
    - `app/Http/Requests/Api/StoreFacturaRequest.php`
- Consultas read-only contra la BD local via `php -r` con bootstrap Laravel.

### 1.1 Reconciliacion posterior P1-12 vs P1-14

Comprobacion solo lectura ejecutada el 2026-05-18 sobre el entorno local:

| Dato | Valor actual en `abaco_ciete` | P1-12 post-importacion real | Coincidencia |
| ---- | -----------------------------: | --------------------------: | ------------ |
| Contextos | 3 | 3 | Si |
| Trabajos | 50 | 11.614 | No |
| Pedidos | 40 | 9.685 | No |
| `pedido_items` | 40 | 9.701 | No |
| Facturas | 18 | 2.733 | No |
| `factura_items` | 20 | 9.194 | No |
| Importaciones registradas | 0 | 11 | No |

Desglose actual por contexto:

| Contexto | Trabajos | Pedidos | `pedido_items` | Facturas | `factura_items` |
| -------- | -------: | ------: | -------------: | -------: | --------------: |
| MOEVE | 20 | 17 | 17 | 14 | 16 |
| REPSOL | 20 | 17 | 17 | 0 | 0 |
| OTROS CLIENTES | 10 | 6 | 6 | 4 | 4 |

Conclusion de reconciliacion: **P1-14 auditó una base local/demo reducida**, no la base post-importacion real documentada en P1-12. El diagnostico funcional de maestros se mantiene para la muestra auditada, pero sus conteos no deben usarse como verdad final de negocio. Antes de usar P1-14 como soporte definitivo de decisiones operativas, debe repetirse la auditoria sobre la base post-importacion real.

## 2. Resumen ejecutivo

El gap principal no esta en contratos, tarifarios, lineas, unidades, estaciones ni tipos. El bloqueo real esta concentrado en la cadena de facturacion `Contrato -> sociedad autorizada -> CIF valido`.

- **MOEVE** tiene base suficiente para Trabajos y Pedidos, pero esta bloqueado para Facturas: sus 2 contratos activos tienen relaciones activas en `contrato_empresas_facturadoras`, pero todas apuntan a empresas sin CIF valido.
- **REPSOL** tiene base suficiente para Trabajos y Pedidos, pero esta bloqueado para Facturas: su contrato activo no tiene ninguna relacion activa en `contrato_empresas_facturadoras`.
- **OTROS CLIENTES** es el unico contexto que hoy queda operativo extremo a extremo en dato maestro para el flujo base actual: contrato activo, tarifario activo, lineas activas, estaciones, tipos, responsables internos y 4 sociedades/CIF validas para facturar.
- **TODOS** sigue siendo un contexto de vision global. No se ha encontrado ninguna evidencia que lo convierta en contexto legitimo de alta.

## 3. Conteo resumido por contexto

| Contexto       | Contratos activos | Contratos sin tarifario | Contratos sin sociedad valida | Tarifarios activos | Lineas activas | Empresas activas | Empresas activas con CIF | Sociedades validas para facturar | Estaciones activas | Tipos documento | Tipos trabajo | Responsables internos | Trabajos | Pedidos | Pedido items pendientes facturables | Veredicto P1-14                                              |
| -------------- | ----------------: | ----------------------: | ----------------------------: | -----------------: | -------------: | ---------------: | -----------------------: | -------------------------------: | -----------------: | --------------: | ------------: | --------------------: | -------: | ------: | ----------------------------------: | ------------------------------------------------------------ |
| MOEVE          |                 2 |                       0 |                             2 |                  2 |             11 |                4 |                        1 |                                0 |                 19 |               1 |            13 |                     5 |       20 |      17 |                                   1 | Incompleto para facturacion; operativo para trabajo y pedido |
| REPSOL         |                 1 |                       0 |                             1 |                  1 |             20 |                1 |                        1 |                                0 |                 19 |               8 |            12 |                     5 |       20 |      17 |                                  17 | Incompleto para facturacion; operativo para trabajo y pedido |
| OTROS CLIENTES |                 1 |                       0 |                             0 |                  1 |             10 |               10 |                       10 |                                4 |                 10 |               1 |            11 |                     4 |       10 |       6 |                                   2 | Listo en dato maestro para el flujo base actual              |

Lectura directa:

- No hay contratos activos sin tarifario en ningun contexto.
- No hay tarifarios activos sin lineas activas.
- No hay lineas activas sin unidad valida.
- No hay estaciones activas sin codigo ni sin empresa cliente.
- No hay tipos de trabajo activos apuntando a tipos de documento invalidos.
- El unico hueco bloqueante y transversal en la muestra local es facturacion por sociedad/CIF.

## 4. Analisis especial del sintoma "Sociedad vacia"

### 4.1 Cadena real que hoy usa el sistema

En el flujo actual de Facturas la lista de sociedades facturadoras no sale de un catalogo libre. Depende de esta cadena real:

1. El usuario trabaja en un `id_contexto` valido y nunca desde `TODOS` para crear.
2. Los items facturables derivan un contrato real desde `pedido_items -> pedido -> trabajo` o desde tarifario.
3. `routes/web.php` solo construye opciones si existe una fila activa en `contrato_empresas_facturadoras` y, ademas, la empresa:
    - pertenece al mismo contexto,
    - esta activa,
    - y tiene CIF no vacio.
4. Al guardar, `FacturaController::validateEmpresaFacturadora()` vuelve a exigir:
    - empresa existente,
    - empresa activa,
    - CIF no vacio,
    - empresa del mismo contexto,
    - y permiso explicito para el contrato derivado de los items.

Conclusion: `Sociedad vacia` no es, en esta muestra, un bug cosmetico de selector. Es un sintoma de gobernanza de maestro facturable.

### 4.2 Evidencia por contexto

#### MOEVE

- Contratos afectados: `MOEVE-686`, `MOEVE-772`.
- Relaciones activas detectadas: 4.
- Sociedades validas finales: 0.
- Causa real: las relaciones activas apuntan a `CCP`, `CEPSA` y `MV`, todas sin CIF cargado.
- Resultado operativo: el selector puede quedarse vacio aunque existan pivots, porque el filtro real elimina empresas sin CIF.

#### REPSOL

- Contrato afectado: `REPSOL-MARCO-2024-2027`.
- Relaciones activas detectadas: 0.
- Sociedades validas finales: 0.
- Causa real: no existe configuracion activa en `contrato_empresas_facturadoras` para el contrato marco.
- Resultado operativo: lista vacia por ausencia total de autorizacion contrato-sociedad.

#### OTROS CLIENTES

- Contrato activo: `OTROS-DEMO-2026`.
- Relaciones activas detectadas: 4.
- Sociedades validas finales: 4.
- Empresas validas detectadas: `Cliente Demo Industrial Norte`, `Cliente Demo Infraestructura`, `Cliente Demo Instalaciones`, `Cliente Demo Logistica Oeste`.
- Resultado operativo: el contexto no presenta el sintoma de `Sociedad vacia` en el dato local auditado.

### 4.3 Diagnostico funcional final

| Causa                                                 | MOEVE                 | REPSOL                | OTROS CLIENTES        | Impacto                                 | Correccion tipo                  |
| ----------------------------------------------------- | --------------------- | --------------------- | --------------------- | --------------------------------------- | -------------------------------- |
| Pivot contrato-sociedad inexistente                   | No                    | Si                    | No                    | Lista vacia y bloqueo de factura        | dato maestro                     |
| Pivot activo pero empresa sin CIF                     | Si                    | No                    | No                    | Lista vacia tras aplicar filtros reales | dato maestro                     |
| Empresa inactiva                                      | No                    | No                    | No                    | Bloqueo de seleccion/guardado           | dato maestro                     |
| Desajuste de contexto entre pivot, empresa y contrato | No detectado          | No detectado          | No detectado          | Riesgo de incoherencia cross-context    | validacion backend / importacion |
| Falta de diagnostico visible al usuario               | Parcialmente mitigado | Parcialmente mitigado | Parcialmente mitigado | UX operativa mejorable                  | filtro UI / UX diagnostica       |

## 5. Minimo viable por modulo

### 5.1 Trabajos

Reglas backend relevantes hoy:

- Siempre exige `numero_trabajo`, `descripcion_trabajo`, `id_estacion_servicio`, `fecha_encargo` y contexto valido.
- En **MOEVE** exige `id_contrato`.
- En **REPSOL** exige `id_tipo_documento` e `id_tipo_trabajo`.
- El responsable interno es opcional, pero si se informa debe existir en `usuarios` y estar activo.

Resultado por contexto:

| Contexto       | Hay estaciones suficientes | Hay contratos/tipos exigidos                                                    | Hay responsables internos | Veredicto                 |
| -------------- | -------------------------- | ------------------------------------------------------------------------------- | ------------------------- | ------------------------- |
| MOEVE          | Si (19)                    | Si: 2 contratos                                                                 | Si (5)                    | Listo para crear trabajos |
| REPSOL         | Si (19)                    | Si: 8 tipos documento y 12 tipos trabajo                                        | Si (5)                    | Listo para crear trabajos |
| OTROS CLIENTES | Si (10)                    | Si: 1 tipo documento y 11 tipos trabajo; contrato no es obligatorio por backend | Si (4)                    | Listo para crear trabajos |

### 5.2 Pedidos

Reglas backend relevantes hoy:

- Exige `id_trabajo` accesible.
- `id_tarifario` es opcional a nivel backend, pero la operativa economica y la UI necesitan lineas coherentes para que el pedido tenga sentido facturable.
- La UI actual exige al menos una linea en el pedido.

Resultado por contexto:

| Contexto       | Hay trabajos | Hay tarifarios y lineas      | Veredicto          |
| -------------- | -----------: | ---------------------------- | ------------------ |
| MOEVE          |           20 | Si: 2 tarifarios y 11 lineas | Listo para pedidos |
| REPSOL         |           20 | Si: 1 tarifario y 20 lineas  | Listo para pedidos |
| OTROS CLIENTES |           10 | Si: 1 tarifario y 10 lineas  | Listo para pedidos |

### 5.3 Facturas

Reglas backend relevantes hoy:

- Exige `items` con al menos una linea.
- Si hay items, exige `id_empresa_facturadora` valida segun contexto, CIF y permiso contrato-sociedad.
- El contrato se deriva desde los `pedido_items` y no desde una cabecera libre.

Resultado por contexto:

| Contexto       | Hay pedido items pendientes | Hay sociedad/CIF valida | Veredicto                         |
| -------------- | --------------------------: | ----------------------- | --------------------------------- |
| MOEVE          |                           1 | No                      | Bloqueado para nuevas facturas    |
| REPSOL         |                          17 | No                      | Bloqueado para nuevas facturas    |
| OTROS CLIENTES |                           2 | Si (4 opciones)         | Listo para facturar en flujo base |

## 6. Matriz detallada de gaps y suficiencia

| Maestro o fuente                      | Campo o relacion critica                                             | Evidencia real                                                    | Diagnostico                               | Impacto funcional                                         | Modulo afectado               | Prioridad | Correccion tipo                         |
| ------------------------------------- | -------------------------------------------------------------------- | ----------------------------------------------------------------- | ----------------------------------------- | --------------------------------------------------------- | ----------------------------- | --------- | --------------------------------------- |
| `contratos`                           | `id_contexto`, `activo`, enlace a `tarifarios`                       | 2 activos en MOEVE, 1 en REPSOL, 1 en OTROS; 0 sin tarifario      | Suficiente en la muestra local            | No bloquea por si mismo                                   | trabajos / pedidos / facturas | Media     | seguimiento                             |
| `tarifarios`                          | `id_contrato`, `activo`                                              | 2 / 1 / 1 activos; 0 sin lineas                                   | Suficiente                                | No bloquea                                                | pedidos / facturas            | Media     | seguimiento                             |
| `tarifario_lineas` + `unidades`       | `id_tarifario`, `id_unidad`, `activo`                                | 11 / 20 / 10 lineas activas; 0 lineas con unidad invalida         | Suficiente                                | No bloquea precio ni cantidad                             | pedidos / facturas            | Media     | seguimiento                             |
| `empresas`                            | `cif`, `activo`, `id_contexto`                                       | MOEVE: 4 activas pero solo 1 con CIF; REPSOL 1/1; OTROS 10/10     | MOEVE incompleto                          | Impide poblar sociedades facturadoras validas             | facturas / maestros           | Alta      | dato maestro                            |
| `contrato_empresas_facturadoras`      | `id_contrato`, `id_empresa`, `id_contexto`, `activo`                 | MOEVE: 4 activas pero 0 validas; REPSOL: 0; OTROS: 4 validas      | MOEVE y REPSOL bloqueados                 | Factura no puede elegir sociedad valida                   | facturas / maestros           | Alta      | dato maestro                            |
| `estaciones_servicio`                 | `codigo_estacion`, `id_empresa_cliente`, `activo`                    | 19 / 19 / 10 activas; 0 sin codigo; 0 sin empresa                 | Suficiente                                | No bloquea trabajos                                       | trabajos                      | Media     | seguimiento                             |
| `tipos_documento`                     | `id_contexto`, `activo`                                              | 1 / 8 / 1 activos                                                 | Suficiente                                | Cubre exigencia REPSOL y OTROS local                      | trabajos                      | Media     | seguimiento                             |
| `tipos_trabajo`                       | `id_tipo_documento`, `activo`                                        | 13 / 12 / 11 activos; 0 invalidos                                 | Suficiente                                | No bloquea formularios ni relacion con tipo documento     | trabajos                      | Media     | seguimiento                             |
| `usuarios` + `usuario_contextos`      | usuario activo y asignacion activa a contexto                        | 5 / 5 / 4 responsables internos disponibles                       | Suficiente                                | Permite asignacion interna                                | trabajos                      | Media     | permisos / seguimiento                  |
| Responsable externo                   | `trabajos.responsable_cliente` + defaults en `tipos_trabajo`         | No existe maestro dedicado; hoy es texto libre o default por tipo | Gap de gobernanza, no bloqueo inmediato   | Riesgo de dispersion y baja normalizacion                 | trabajos / informes           | Media     | dato maestro futuro                     |
| `trabajos`, `pedidos`, `pedido_items` | volumen minimo operativo real                                        | 20/17/17 en MOEVE, 20/17/17 en REPSOL, 10/6/6 en OTROS            | Hay base transaccional en los 3 contextos | Permite validar operativa real, no solo maestros          | trabajos / pedidos / facturas | Media     | seguimiento                             |
| Esquema local `trabajos`              | ausencia de columna `activo` en BD pese a que el modelo la contempla | `SHOW COLUMNS FROM trabajos LIKE 'activo'` devuelve vacio         | Drift tecnico entre modelo y tabla local  | Puede falsear auditorias o codigo que suponga esa columna | tecnico transversal           | Media     | validacion backend / alineacion esquema |

## 7. Ejemplos concretos auditados

### 7.1 Contratos activos sin sociedad facturable valida

- MOEVE:
    - `MOEVE-686` -> 2 relaciones activas, 0 sociedades validas.
    - `MOEVE-772` -> 2 relaciones activas, 0 sociedades validas.
- REPSOL:
    - `REPSOL-MARCO-2024-2027` -> 0 relaciones activas, 0 sociedades validas.

### 7.2 Empresas activas sin CIF

- MOEVE:
    - `CCP`
    - `CEPSA`
    - `MV`

No se han detectado empresas activas sin CIF en REPSOL ni en OTROS CLIENTES.

### 7.3 Contratos activos con cobertura correcta de facturacion

- OTROS CLIENTES:
    - `OTROS-DEMO-2026` -> 4 relaciones activas y 4 sociedades validas.

## 8. Clasificacion final de OTROS CLIENTES

**Estado P1-14:** `listo` en el dato maestro local auditado para el flujo base `Trabajo -> Pedido -> Factura`.

Motivos:

- Tiene contrato activo.
- Tiene tarifario activo y lineas activas con unidad valida.
- Tiene 10 empresas activas y 10 con CIF.
- Tiene 4 relaciones activas contrato-sociedad plenamente validas.
- Tiene estaciones, tipos y responsables internos suficientes.
- Tiene trabajos, pedidos y `pedido_items` pendientes facturables reales.

Reserva funcional:

- Este veredicto es de completitud de dato maestro y viabilidad operativa actual. No sustituye validaciones posteriores de UX, cascadas o reglas adicionales que se traten en P1-15 y P1-16.

## 9. Acciones recomendadas sin implementar aun

1. **MOEVE - dato maestro**: cargar CIF valido en `CCP`, `CEPSA` y `MV` o sustituir las relaciones por las sociedades realmente autorizadas para `MOEVE-686` y `MOEVE-772`.
2. **REPSOL - dato maestro**: crear al menos una relacion activa en `contrato_empresas_facturadoras` para `REPSOL-MARCO-2024-2027` con empresa activa y CIF valido del mismo contexto.
3. **P1-15 despues de esta auditoria**: estandarizar mensajes diagnosticos para diferenciar estos tres casos en UI: sin pivot, pivot sin CIF, empresa fuera de contexto/inactiva.
4. **Alineacion tecnica**: revisar el drift `Trabajo` modelo vs tabla local por la ausencia de `trabajos.activo` antes de endurecer reglas basadas en ese campo.

## 10. Validaciones ejecutadas

- `git status --short` al inicio para confirmar que existian cambios previos fuera de `docs/**` y que esta tarea seria solo documental.
- Consultas read-only via `php -r` + bootstrap Laravel sobre `abaco_ciete` para conteos por contexto.
- Verificacion especifica del filtro real de sociedades en `routes/web.php` y del guardado en `FacturaController::validateEmpresaFacturadora()`.
- Verificacion de reglas reales de alta en `StoreTrabajoRequest`, `StorePedidoRequest` y `StoreFacturaRequest`.
- Verificacion read-only de drift de esquema: `SHOW COLUMNS FROM trabajos LIKE 'activo'`.

## 11. Cierre P1-14

La auditoria deja cerrado el diagnostico de completitud de maestros por contexto para la muestra local/demo actual:

- **MOEVE**: incompleto por sociedades/CIF.
- **REPSOL**: incompleto por ausencia de pivot contrato-sociedad.
- **OTROS CLIENTES**: listo en dato maestro base actual.

Con esto P1-14 queda cubierto como auditoria de muestra local/demo. Tras la reconciliacion con P1-12, queda pendiente repetir P1-14 sobre la base post-importacion real antes de usar sus numeros como verdad final de negocio. No se ha iniciado implementacion correctiva de P1-15 ni P1-16 en este documento.
