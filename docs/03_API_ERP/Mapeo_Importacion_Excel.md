# Mapeo de importacion Excel -> ERP Ciete

Estado: documento operativo para programar parser/importador  
Fecha auditoria: 2026-04-17  
Fuente de verdad usada: base de datos de la raiz del proyecto (`database/database.sqlite` y `database/abaco_ciete.sql`)

## 1. Criterio de verdad de BD

| Fuente | Resultado |
|---|---|
| `database/database.sqlite` | Esquema aplicado con 9 migraciones. No contiene datos operativos. |
| `database/abaco_ciete.sql` | Dump de la misma linea de esquema, con inserts base. |
| `.env` | Apunta a MySQL `abaco_ciete`, pero este documento usa la BD de raiz por decision explicita. |
| Migraciones actuales `2026_03_24_000015+` | Existen en repo, pero no representan la BD de raiz. Se tratan como propuesta futura. |

## 2. Hechos bloqueantes del esquema raiz

| Area | En BD raiz existe | En BD raiz no existe |
|---|---|---|
| Entidad central | `proyectos` | `trabajos` |
| Lineas de pedido/factura | `pedidos_lineas`, `facturas_lineas` | `pedido_items`, `factura_pedidos` |
| Tarifas | `servicios`, `tarifarios`, `tarifario_servicios` | `contratos`, `tarifario_lineas` |
| Clasificacion de trabajos | Campos texto en `proyectos` | `tipos_documento`, `tipos_trabajo` |
| Estaciones | `estaciones_servicio` con columnas MOEVE/REPSOL mezcladas | `estaciones_moeve_ext`, `estaciones_repsol_ext` |
| Importacion | Ninguna tabla | `importaciones`, `importacion_filas` |
| Auditoria | Comentarios/workplan por proyecto | `audit_log` |
| Permisos | `proyectos.*`, `proyectos_cerrados.*` | `trabajos.*`, `importaciones.*`, `auditoria.ver` |

Decision operativa: mientras se use esta BD, "Trabajo" en UI/API/importacion equivale a `proyectos`.

## 3. Inventario de fuentes Excel

| Archivo | Hoja | Cliente/contexto BD | Uso |
|---|---|---|---|
| `docs/Abaco/excelsactualizados/Mapeo_Moeve_Repsol_Envolvente.xlsx` | `Hoja1` | CEPSA/MOEVE y REPSOL | Referencia de columnas. No importar. |
| `Moeve/Moeve/01 Control de Trabajos Moeve.xlsx` | `Trabajos` | `contextos_cliente.codigo=CEPSA` | Proyectos, pedidos y datos de facturacion. |
| idem | `FACTURAS EMITIDAS` | CEPSA | Facturas emitidas. |
| idem | `Control`, `Hoja1`, `Hoja2` | CEPSA | Reporting. Solo staging/consulta. |
| `Moeve/Moeve/02 Listado EESS Espana y Portugal 16-03-26.xlsx` | `Espana 16-03-26` | CEPSA | Maestro de estaciones. |
| idem | Historicas | CEPSA | Backfill comparativo si se aprueba. |
| `docs/Contrato 772 MOEVE - Tarifario.xlsx` | `TARIFARIO` | CEPSA | Servicios/tarifario. |
| `Repsol/01 Control Trabajos DISENO REPSOL.xlsx` | `AUTOFACTURACION`, `ALFONSO PCN` | REPSOL | Proyectos, pedidos, lineas, facturas. |
| idem | `Rangos` | REPSOL | No hay tabla destino. Staging/propuesta de catalogo. |
| Repsol varios | `LISTADO EESS` | REPSOL | Maestro de estaciones. Importar una sola vez. |
| Repsol varios | `TARIFA 23-27` | REPSOL | Servicios/tarifario. Importar una sola vez. |
| `02 EDIFICACION`, `03 OBRAS Z10`, `03 OBRAS Z50`, `05 LICENCIAS`, `09 FV`, `10 ESTRUCTURAS Y VERTIDOS`, `12 MTO`, `13 PUNTOS DE RECARGA` | `AUTOFACTURACION*`, `OTROS` | REPSOL | Operativa de proyectos. |

Notas:
- En BD raiz el contexto se llama `CEPSA`. Los Excel actuales hablan de MOEVE. No cambiar el codigo de contexto sin migracion controlada.
- La tabla `empresas` del SQLite esta vacia; el dump SQL si contiene CEPSA, REPSOL y CIETE. El importador debe exigir maestros cargados antes de confirmar.

## 4. Orden de carga

| Orden | Datos | Tablas destino |
|---:|---|---|
| 1 | Contextos, empresas, usuarios, roles | `contextos_cliente`, `empresas`, `usuarios`, `roles`, `permisos` |
| 2 | Unidades y servicios base | `unidades`, `servicios` |
| 3 | Tarifarios y precios | `tarifarios`, `tarifario_servicios` |
| 4 | Estaciones | `estaciones_servicio` |
| 5 | Proyectos/trabajos | `proyectos`, `proyectos_workplan` opcional |
| 6 | Pedidos y lineas | `pedidos`, `pedidos_lineas` |
| 7 | Facturas y lineas | `facturas`, `facturas_lineas` |
| 8 | Legalizaciones/comentarios | `legalizaciones`, `legalizaciones_contactos`, `comentarios_legalizaciones` |

## 5. Mapeo tecnico: trabajos Excel -> `proyectos`

| Archivo/hoja | Columna Excel | Campo normalizado | Tabla.columna | Tipo | Req. | Limpieza | Validacion/relacion | Si falta referencia | Observaciones |
|---|---|---|---|---|---|---|---|---|---|
| MOEVE `Trabajos` | `Nº` | `codigo_proyecto` | `proyectos.codigo_proyecto` | string(100) | Si | Entero/string, trim. Formato `MOEVE-{N}`. | Unico `(id_contexto,codigo_proyecto)`. | Error. | No usar solo `Nº` sin prefijo. |
| REPSOL `AUTOFACTURACION*` | `Nº` | `codigo_proyecto` | `proyectos.codigo_proyecto` | string(100) | Si | Trim. Formato `REPSOL-{doc}-{zona}-{hoja}-{N}`. | Unico por contexto. | Error. | Necesario porque `Nº` se repite por Excel/hoja/zona. |
| Ambos | `Nombre`, `NOMBRE`, descripcion corta | `nombre_proyecto` | `proyectos.nombre_proyecto` | string(180) | Si | Trim, colapsar espacios. | Max 180. | Usar descripcion/trabajo si no hay nombre. | Campo requerido por BD. |
| Ambos | `DESCRIPCION DEL SERVICIO`, `DESCRIPCION DEL TRABAJO` | `descripcion_libre` | `proyectos.descripcion_libre` | text | Recomendado | Trim. | Texto libre. | Warning. | No hay `descripcion_trabajo`. |
| Ambos | `Categoría`, `TIPO DE TRABAJO` | `descripcion_seleccionable` | `proyectos.descripcion_seleccionable` | string(255) | No | Trim. | Max 255. | Null + warning. | BD raiz no tiene `tipos_trabajo`. |
| REPSOL | `TIPO DE TRABAJO`, hoja/rango | `workplan_resumen` | `proyectos.workplan_resumen` | text | No | Guardar resumen estructurado corto. | JSON/texto valido. | Staging. | Mejor propuesta: tabla catalogo `tipos_trabajo`. |
| Ambos | `Nº ES`, `ES` | `codigo_estacion_origen` | Resolver `id_estacion_servicio`; copiar en observaciones si hace falta | string | Cond. | Preservar ceros; `NA`, `--` -> null. | Buscar estacion por contexto y codigo. | Error salvo trabajos directos/sin estacion. | Ver reglas estaciones. |
| Ambos | `LOCALIDAD`, `Provincia` | `estacion_validacion` | Staging | string | No | Trim. | Comparar con estacion resuelta. | Warning. | No actualizar estacion desde trabajo. |
| Ambos | `Fecha Encargo` | `fecha_encargo` | `proyectos.fecha_encargo` | date | No | Serial Excel o `dd/mm/yyyy` -> `YYYY-MM-DD`. | Fecha valida. | Warning. |  |
| Ambos | `Fecha T`, `FECHA TERMINACION TRABAJO` | `fecha_fin_real` | `proyectos.fecha_fin_real` | date | No | Fecha; textos -> null + warning. | >= `fecha_inicio_real` si existe. | Warning. | Tambien marca `trabajo_terminado=1` si fecha valida. |
| Ambos | `Status` | `estado_general`, `cerrado`, `bloqueado_cierre` | `proyectos.estado_general`, `cerrado`, `bloqueado_cierre` | enum/bool | Si | Normalizar estado. | Enum raiz. | Estado no reconocido -> `en_curso` + bloqueado. | Ver seccion estados. |
| Ambos | `OBSERVACIONES` | `observaciones_operativas` | `proyectos.observaciones_operativas` | text | No | Trim. | Texto libre. | Null. | No mezclar errores tecnicos. |
| Ambos | Observaciones de facturacion | `observaciones_facturacion` | `proyectos.observaciones_facturacion` | text | No | Trim. | Texto libre. | Null. | Usar para incidencias de pedido/factura de negocio. |
| REPSOL | `Nº AVISO`, `Nº AVISO / P.KEOPS` | `numero_aviso` | `proyectos.numero_aviso` | string(100) | No | Trim, no convertir a int. | Max 100. | Null + warning si tipo lo exige. | Tambien se copia a `pedidos.numero_aviso`. |
| REPSOL | `ORDEN MANTEN.` | `orden_mantenimiento_origen` | Staging/propuesta | string | No | Trim. | No hay columna destino. | Guardar en staging/warning. | Propuesta: `proyectos.orden_mantenimiento`. |
| Ambos | `RESPONSABLE CIETE` | `id_usuario_responsable` | `proyectos.id_usuario_responsable` | FK | No | Alias normalizado. | Usuario del mismo contexto. | Null + warning; no crear usuario. | `13 PUNTOS DE RECARGA` no trae columna. |
| Ambos | `RESPONSABLE MOEVE/REPSOL` | `responsable_cliente_origen` | Staging/propuesta | string | No | Trim. | No hay columna destino directa. | Guardar en staging/warning. | Podria mapearse a contacto si existe. |
| MOEVE | `Contrato` | `contrato_origen` | Staging/propuesta | string | No | Trim. | No existe `contratos`. | Warning. | Root DB solo tiene `tarifarios`. |

## 6. Mapeo tecnico: pedidos y lineas

| Archivo/hoja | Columna Excel | Campo normalizado | Tabla.columna | Tipo | Req. | Limpieza | Validacion/relacion | Si falta referencia | Observaciones |
|---|---|---|---|---|---|---|---|---|---|
| Ambos | `Nº PEDIDO` | `numero_pedido` | `pedidos.numero_pedido` | string(100) | Si para crear pedido | Trim; preservar ceros. `-`, `NA` -> null. | Unico `(id_contexto,numero_pedido)`. | No crear pedido. | Riesgo: un pedido puede aparecer en varias lineas/trabajos. |
| Ambos | `Nº AVISO` | `numero_aviso` | `pedidos.numero_aviso` | string(100) | No | Trim. | Max 100. | Null. | Copia operativa. |
| REPSOL | `FECHA SOLICITUD PEDIDO` | `fecha_solicitud_pedido` | `pedidos.fecha_solicitud_pedido` | date | No | Fecha Excel/texto. | Fecha valida. | Warning. |  |
| Ambos | Fecha recepcion pedido | `fecha_recepcion_pedido` | `pedidos.fecha_recepcion_pedido` | date | No | Fecha. | Fecha valida. | Warning. | Si no existe en Excel, null. |
| REPSOL/MOEVE | `FECHA SOLICITUD FACTURA` | `fecha_solicitud_factura` | `pedidos.fecha_solicitud_factura` | date | No | Fecha. | Fecha valida. | Warning. | En MOEVE suele venir como importe solicitado, no fecha. |
| Ambos | `IMPORTE PEDIDO` | `subtotal`, `total` | `pedidos.subtotal`, `pedidos.total` | decimal(14,2) | Si hay pedido | Decimal. | >= 0. | Error si no numerico. | Si no hay IVA desglosado: `subtotal=total`, `iva=0`. |
| Ambos | `Facturacion Solicitada`, `IMPORTE SOLICITADO` | `importe_solicitado_origen` | Staging/propuesta | decimal | No | Decimal. | >= 0. | Warning. | No hay columna en BD raiz. |
| Ambos | Estado pedido calculado | `estado` | `pedidos.estado` | enum | No | Reglas de negocio. | `pendiente`, `solicitado`, `recibido`, `en_ejecucion`, `cerrado`, `anulado`. | Default `pendiente`. |  |
| REPSOL | `CODIGO SERVICIO` | `codigo_servicio` | `servicios.codigo` o staging | string | Cond. | Trim. | Buscar `servicios.codigo`. | Crear servicio solo tras preview o dejar linea libre. | No existe columna en `pedidos_lineas`. |
| REPSOL | `Numero Tarifa (con punto)` | `numero_tarifa` | Staging/propuesta | string | No | Preservar puntos. | Cruzar con servicio/tarifa si hay regla. | Warning. | Root DB no lo almacena. |
| Ambos | Descripcion servicio | `concepto_libre` | `pedidos_lineas.concepto_libre` | text | No | Trim. | Texto. | Null. |  |
| REPSOL | `IMPORTE UNITARIO` | `precio_unitario` | `pedidos_lineas.precio_unitario` | decimal(14,2) | Si hay linea | Decimal. | >= 0. | Error. |  |
| REPSOL | `UDs DEL PEDIDO` | `cantidad` | `pedidos_lineas.cantidad` | decimal(14,3) | Si hay linea | Decimal. | > 0. | Error. |  |
| Ambos | Importe linea | `total_linea` | `pedidos_lineas.total_linea` | decimal(14,2) | Si hay linea | Decimal o `cantidad*precio`. | Tolerancia 0.02. | Error si descuadra fuerte. |  |

## 7. Mapeo tecnico: facturas

| Fuente | Columna Excel | Campo normalizado | Tabla.columna | Tipo | Req. | Limpieza | Validacion/relacion | Si falta referencia | Observaciones |
|---|---|---|---|---|---|---|---|---|---|
| MOEVE `FACTURAS EMITIDAS` | `Nº FACTURA CIETE` | `numero_factura` | `facturas.numero_factura` | string(100) | Si | Trim. | Unico `(id_contexto,numero_factura)`. | Error. | Numero principal en BD. |
| MOEVE `FACTURAS EMITIDAS` | `Nº FACTURA CCP` | `numero_factura_ccp` | Staging/propuesta | string | No | Trim. | No hay columna. | Guardar en staging. | Propuesta: `facturas.numero_factura_ccp`. |
| Ambos | Fecha factura | `fecha_emision` | `facturas.fecha_emision` | date | Si para crear factura | Fecha Excel/texto. | Requerida por BD. | Mantener en staging si falta. |  |
| Ambos | Importe factura | `base_imponible`, `total` | `facturas.base_imponible`, `facturas.total` | decimal(14,2) | Si | Decimal. | >= 0. | Error. | Si no hay IVA: `iva=0`, `total=base`. |
| Ambos | IVA | `iva` | `facturas.iva` | decimal(14,2) | No | Decimal. | >= 0. | Default 0. |  |
| REPSOL simple | `FACTURA` | `numero_factura` | `facturas.numero_factura` | string(100) | Cond. | Trim. | Unico por contexto. | Si falta numero, no se puede persistir en BD raiz. | BD exige numero. |
| REPSOL doble | `1a FACTURA`, `2a FACTURA`, `Nº FACTURA` | Facturas separadas | `facturas` | varios | Cond. | Usar posicion de columna. | Crear una factura por numero/importe real. | Staging si no hay numero/fecha. | BD no tiene `orden_factura`; guardar orden en `descripcion_libre` o staging. |
| Ambos | Linea factura | `facturas_lineas.*` | `facturas_lineas` | varios | No | Igual que pedido_linea. | FK factura/contexto. | No crear lineas si no hay factura. |  |

Regla BD raiz: `facturas.id_pedido` es nullable, pero `id_proyecto` es obligatorio. Si llega factura antes que pedido, solo se puede confirmar si ya existe el proyecto. Cuando aparezca pedido, actualizar `facturas.id_pedido`.

## 8. Mapeo tecnico: estaciones

| Cliente | Columna Excel | Campo normalizado | Tabla.columna | Tipo | Req. | Limpieza | Validacion/relacion | Si falta |
|---|---|---|---|---|---|---|---|---|
| MOEVE | `CONCESION` | `cod_cepsa`, `concesion`, `codigo_estacion_interno` | `estaciones_servicio.*` | string | Si | Trim, preservar ceros. | Unico por contexto. | Error |
| REPSOL | `C.EMP`, `Nº ES` | `cod_repsol`, `codigo_estacion_interno` | `estaciones_servicio.*` | string | Si | Trim. | Unico por contexto. | Error |
| Ambos | Nombre comercial | `nombre` | `estaciones_servicio.nombre` | string(180) | Si | Trim. | Requerido. | Error |
| Ambos | Direccion | `direccion` | `estaciones_servicio.direccion` | string(255) | No | Trim. | Max 255. | Null |
| Ambos | CP | `codigo_postal` | `estaciones_servicio.codigo_postal` | string(20) | No | Preservar ceros. | CP espanol si aplica. | Warning |
| Ambos | Localidad | `poblacion` | `estaciones_servicio.poblacion` | string(120) | No | Trim. | Max 120. | Null |
| Ambos | Provincia | `provincia` | `estaciones_servicio.provincia` | string(120) | No | Trim. | Max 120. | Null |
| MOEVE | `Y_WGS84`, `X_WGS84` | lat/lon | `latitud_wgs84`, `longitud_wgs84` | decimal | No | Coma/punto decimal. | Rango geografico. | Warning |
| MOEVE | `TÉCNICO GESTION` | `tecnico_gestion` | `estaciones_servicio.tecnico_gestion` | string | No | Trim. | Max 255 aprox. | Null |
| MOEVE | Telefonos/email tecnico | telefono/email tecnico | `telefono_tecnico_gestion`, `email_tecnico_gestion` | string | No | Normalizar telefono/email. | Email valido. | Warning |
| MOEVE | `RESPONSABLE / GESTOR` | `responsable_es_gestor` | `estaciones_servicio.responsable_es_gestor` | string | No | Trim. | Max 255 aprox. | Null |
| MOEVE | `SEDE / e-mail` | `sede` | `estaciones_servicio.sede` | string | No | Trim. | Max 255 aprox. | Null |
| MOEVE | `F.BAJA` | `f_baja`, `activo` | `f_baja`, `activo` | date/bool | No | Fecha. | Si existe, `activo=false`. | Warning |
| REPSOL | `CLIENTE`, encargado, gerente, litros, ventas | extras | Staging/propuesta | json | No | Trim/decimal. | No hay columnas destino. | Staging |

## 9. Mapeo tecnico: tarifarios

| Fuente | Columna Excel | Campo normalizado | Tabla.columna | Tipo | Req. | Limpieza | Validacion | Observaciones |
|---|---|---|---|---|---|---|---|---|
| MOEVE/Repsol | Codigo servicio | `codigo` | `servicios.codigo` | string | Si | Trim. | Unico global. | Riesgo: codigo puede colisionar entre clientes. |
| MOEVE/Repsol | Nombre/actuacion | `nombre` | `servicios.nombre` | string | Si | Trim. | Requerido. |  |
| MOEVE/Repsol | Descripcion | `descripcion_libre` | `servicios.descripcion_libre` | text | No | Trim. | Texto. |  |
| MOEVE/Repsol | Unidad | `id_unidad` | `servicios.id_unidad` | FK | Si | Normalizar ud/hora/km. | Debe existir `unidades`. | Crear catalogo antes. |
| MOEVE/Repsol | Tarifario/version | `nombre`, `version` | `tarifarios.nombre`, `version` | string | Si | Trim. | Unico `(id_contexto,nombre,version)`. | No hay contratos. |
| MOEVE/Repsol | Precio | `precio_unitario` | `tarifario_servicios.precio_unitario` | decimal | Si | Decimal. | >= 0. |  |
| Repsol | Factor `0.9562` | factor | Staging/propuesta | decimal | No | Decimal. | > 0. | Root DB no tiene `factor_multiplicador`. |
| Repsol | Numero tarifa | `numero_tarifa` | Staging/propuesta | string | No | Preservar puntos. | No hay destino. | Propuesta: columna en `tarifario_servicios` o tabla nueva. |

## 10. Normalizacion comun

| Tipo | Regla |
|---|---|
| Fechas serial Excel | Usar sistema 1900: `1899-12-30 + serial`. Guardar `YYYY-MM-DD`. |
| Fechas texto | Aceptar `dd/mm/yyyy`, `dd-mm-yyyy`, `yyyy-mm-dd`. Texto mixto -> null + warning. |
| Importes | Quitar moneda, espacios, miles; aceptar coma decimal. Guardar 2 decimales. |
| Cantidades | Decimal con 3 decimales. `-`, vacio, `NA` -> null o default segun campo. |
| Booleanos | `SI`, `SÍ`, `S`, `TRUE`, `1`, `OK`, `TT`, `COMPLETO` -> true. `NO`, `FALSE`, `0` -> false. |
| Textos vacios | `''`, `-`, `--`, `NA`, `N/A`, `NO CONSTA`, `#VALUE!` -> null. |
| Codigos externos | Siempre string. Preservar ceros, puntos y barras. |
| Cliente/contexto | Viene de la importacion, no del texto Excel. MOEVE se carga en contexto `CEPSA` salvo migracion. |
| Estacion | Matching por contexto y codigo: CEPSA/MOEVE usa `cod_cepsa`/`concesion`; REPSOL usa `cod_repsol`. |
| Responsables | CIETE se intenta resolver contra `usuarios`; cliente queda en staging si no hay contacto. |

## 11. Reglas de negocio de importacion

| Regla | Decision |
|---|---|
| Identidad de trabajo | `proyectos.codigo_proyecto` generado de forma determinista desde cliente/documento/hoja/zona/`Nº`. |
| Duplicados de trabajo | Si existe `(id_contexto,codigo_proyecto)`, actualizar solo campos mapeados y guardar diff en staging/log propuesto. |
| Identidad de pedido | BD fuerza `(id_contexto,numero_pedido)`. Si un pedido se reparte entre varios proyectos, bloquear y pedir decision. |
| Factura antes que pedido | Crear factura solo si existe proyecto y hay `numero_factura` + `fecha_emision`; dejar `id_pedido=null`. |
| Factura sin numero o fecha | No persistir en BD raiz; dejar en staging como incidencia. |
| Referencias rotas | No crear proyecto/pedido/factura definitivo si falta empresa/contexto. Estacion puede ser null solo si se marca trabajo sin estacion. |
| Separacion por cliente | Todas las busquedas se hacen con `id_contexto`. Nunca cruzar estaciones/tarifas CEPSA y REPSOL. |
| MOEVE vs REPSOL | MOEVE tiene contrato/categoria y facturas emitidas CCP sin destino completo; REPSOL tiene tipo documento/tipo trabajo/tarifa doble sin catalogos en BD raiz. |
| Catalogos previos | `contextos_cliente`, `empresas`, `unidades`, `servicios`, `tarifarios`, `tarifario_servicios`, `estaciones_servicio`, `usuarios`. |
| Paso a definitivo | Solo filas validas. Filas con warnings pueden confirmarse si no rompen FKs/uniques; filas con errores no. |

## 12. Flujo recomendado

1. Subida de archivo y seleccion de tipo: `estaciones`, `tarifario`, `trabajos`, `facturas`.
2. Lectura de workbook y deteccion de hoja/perfil.
3. Normalizacion de cabeceras y valores.
4. Resolucion de contexto, empresa, estacion, usuario, servicio y tarifario.
5. Validacion de FKs, uniques, tipos, fechas, importes y reglas cliente.
6. Preview paginado con filas normalizadas, errores y warnings.
7. Correccion manual de campos resolubles: estacion, usuario, servicio, codigo proyecto.
8. Confirmacion transaccional por lotes.
9. Persistencia en tablas finales.
10. Log de resultado y export de errores.

## 13. Staging necesario

La BD raiz no tiene tablas de importacion. Para trazabilidad y reintentos, crear antes de programar el importador:

| Tabla | Columnas minimas | Motivo |
|---|---|---|
| `importaciones` | `id_importacion`, `id_contexto`, `id_usuario`, `tipo`, `archivo_original`, `hash_archivo`, `total_filas`, `filas_validas`, `filas_error`, `filas_warning`, `estado`, `started_at`, `finished_at`, timestamps | Cabecera, idempotencia y resumen. |
| `importacion_filas` | `id_importacion_fila`, `id_importacion`, `numero_fila`, `hoja`, `datos_origen_json`, `datos_normalizados_json`, `estado`, `errores_json`, `warnings_json`, `tabla_destino`, `id_registro_destino`, timestamps | Preview, correccion y auditoria por fila. |
| `importacion_decisiones` | `id`, `id_importacion`, `clave`, `valor`, `id_usuario`, timestamps | Resolver decisiones manuales repetibles. |

Estados staging: `subido`, `leyendo`, `validado`, `con_errores`, `confirmando`, `completado`, `fallido`, `cancelado`.

## 14. Riesgos y huecos reales

| Riesgo/hueco | Impacto | Decision/propuesta |
|---|---|---|
| No existe `trabajos`; existe `proyectos`. | API/importacion deben mapear nombres de negocio a tabla legacy. | Usar `proyectos` ahora o migrar antes de construir. |
| No hay staging. | No hay preview, reintento ni auditoria fiable. | Crear tablas de staging antes del parser. |
| No hay `contratos`. | Campo contrato MOEVE no tiene destino. | Staging o crear tabla `contratos`. |
| No hay `tipos_documento`/`tipos_trabajo`. | Rangos Repsol no se pueden normalizar. | Staging o catalogos nuevos. |
| `pedidos.numero_pedido` unico por contexto. | Un pedido con varias lineas/trabajos colisiona. | Revisar unique o agrupar lineas bajo un solo pedido. |
| `servicios.codigo` unico global. | Codigos iguales en CEPSA/REPSOL pueden colisionar. | Hacer codigo compuesto o unique por contexto mediante tabla nueva. |
| Factura requiere `numero_factura` y `fecha_emision`. | Repsol/MOEVE con solicitud sin factura no se puede persistir como factura. | Guardar solicitud en pedido/staging hasta emision. |
| No hay `numero_factura_ccp`, `orden_factura`, `factura_pedidos`. | Doble factura y CCP quedan incompletos. | Ampliar facturas o mantener en staging. |
| No hay tablas extension estaciones. | Muchos campos de listados EESS se pierden. | Crear extension o atributos JSON si negocio los necesita. |
| SQLite raiz vacia de maestros salvo contextos/permisos/roles. | Importacion real fallara por FKs si no se cargan seeds del SQL. | Cargar maestros base antes de importar Excels. |
