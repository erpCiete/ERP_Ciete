# Especificación columnas Trabajos — 2026-05-29

## 1. Objetivo

Dejar cerrada una especificación documental de cómo deben llamarse, mostrarse y trabajarse las columnas de la vista `Trabajos`, tomando como fuente principal la reunión/transcripción de 2026-05-19 y contrastándola con el código real actual.

Estado a 2026-05-29:

- La nomenclatura visual y el orden visible de columnas han quedado aplicados en la UI.
- La decisión de navegación para `Pedidos` queda fijada en este documento.
- La validación manual final en navegador sigue pendiente.

## 2. Fuentes revisadas

- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/tareasComparar.md`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `app/Models/Trabajo.php`

## 3. Inventario actual de columnas

Inventario tomado de `TABLE_COLUMNS` y del render real de filas en `TrabajosExcelView.jsx`.

| Orden actual | Columna visible actual | Campo/s reales | Editable sí/no | Buscable/filtrable sí/no | Observaciones |
| ---------- | ---------------------- | -------------- | -------------- | ------------------------ | ------------- |
| 1 | `Nº trabajo` | `numero_trabajo_visible`, `numero_trabajo_operativo`, fallback `numero_trabajo` | Sí | Sí | La celda muestra el visible y puede añadir `Ref. interna ####` si difiere del base. |
| 2 | `Nº estación` | `codigo_estacion`, `id_estacion_servicio` | Sí | Sí | Selector editable de estación; al cambiarlo arrastra nombre, municipio y provincia. |
| 3 | `Nombre estación` | `nombre_estacion` | Sí, indirecto | Sí | No se edita como texto libre en la fila; depende de la estación elegida. |
| 4 | `Municipio` | `municipio` | Sí, indirecto | Sí | Igual que `Nombre estación`: depende de la estación. |
| 5 | `Provincia` | `provincia` | Sí, indirecto | Sí | Igual que `Municipio`. |
| 6 | `Categoría de trabajo` | `categoria`, `id_tipo_documento`, `id_tipo_trabajo`, `tipo_trabajo_nombre` | Sí | Sí | En Moeve funciona como categoría más libre; en Repsol mezcla categoría y tipo de trabajo dentro de la misma columna visible. |
| 7 | `Descripción` | `descripcion_trabajo` | Sí | Sí | Texto principal operativo del trabajo. |
| 8 | `Tarifario` | `id_contrato`, `id_tarifario`, `nombre_contrato`, `nombre_tarifa` | Sí, condicional | Sí | Visible con nombre operativo de negocio; el contrato sigue existiendo como dato estructural y filtro separado. |
| 9 | `Pedidos` | `pedidos_resumen`, `pedidos_count`, `numero_pedido_principal` | No, directo | Sí | Resume `Pedido 1`, `Pedido 2`, `Pedido 3` y navega a pedido o listado. |
| 10 | `Acción pedido` | Derivado de `id_tarifario`, `pedidos_count`, permisos y contexto | No, directo | No | Muestra `Crear pedido`, `Crear otro pedido` o `Seleccionar tarifario`. |
| 11 | `Importe pedido` | `importe_pedido_total` | No | Sí | Total agregado desde pedidos; se usa también en búsqueda y filtro avanzado. |
| 12 | `Solicitado` | `importe_solicitado_total` | No | Sí | Total agregado; indica importe solicitado, no un booleano. |
| 13 | `Facturado` | `importe_facturado_total` | No | Sí | Total agregado; sirve para control económico y filtros. |
| 14 | `Estado` | `estado` | Sí | Sí | Campo operativo visible y además criterio de orden; `cancelado` se empuja al final. |
| 15 | `Responsable` | `id_responsable_ciete`, `nombre_responsable`, `responsable_ciete` | Sí | Sí | Responsable interno CIETE asignado al trabajo. |
| 16 | `Fecha encargo` | `fecha_encargo` | Sí | Sí | Actualmente es filtro rápido en cabecera. |
| 17 | `Fecha solicitud pedido` | `fecha_solicitud_pedido` | No | Sí | El dato real es una fecha; el nombre visible ya queda alineado con ello. |
| 18 | `Fecha terminación` | `fecha_terminacion`, `fecha_terminado` | Sí | Sí | Fecha de cierre/terminación a nivel de ejecución. |
| 19 | `Observaciones` | `observaciones` | Sí | Sí | Se trabaja con celda compacta y modal, no como texto desplegado permanente. |
| 20 | `Acciones` | Navegación y utilidades de fila | No, directo | No | Hoy contiene al menos `Abrir ficha` y acciones auxiliares. |

Filtros y búsquedas actuales contrastados con `TrabajosExcelView.jsx`, `Index.jsx` y `TrabajoController@index`:

- Búsqueda global: número de trabajo, número operativo, aviso, descripción, observaciones, categoría, responsable, fechas, estación, pedido, contrato, tarifa, empresa e importes.
- Filtros rápidos visibles: búsqueda global, estado, responsable, fecha desde, fecha hasta, nº estación, municipio, provincia.
- Filtros avanzados: contrato, tarifario, número de pedido, con/sin pedidos, multipedido, importe pedido, facturado, solicitado, estación y categoría de trabajo.
- Chips activos: muestran los filtros aplicados y permiten quitarlos uno a uno.

## 4. Lo que dice la reunión/transcripción

| Tema/columna | Qué se dijo en reunión | Fragmento o referencia | Decisión clara / duda |
| ---------- | ---------------------- | ---------------------- | --------------------- |
| Vista principal tipo Excel | Quieren trabajar desde una vista tipo Excel y no depender de fichas para lo operativo diario. | `listado_exhaustivo...` 011; `reunion...` apartado `9. Necesidad de vista tipo Excel` | Clara |
| Orden inicial de columnas | El orden de referencia debe parecerse a su Excel real. | `reunion...` apartado `8. Trabajos, numeración y vista operativa`: “mismo orden que en nuestro trabajo de control” | Clara |
| Bloque inicial de identificación | Lo primero debe ser ordinal/nº trabajo, nº estación, nombre estación, municipio y provincia. | `reunion...` 8: “número ordinal, el primero. Número de estación, nombre de estación, municipio... provincia...” | Clara |
| Número interno vs número “vuestro” | Distinguen entre número base interno/técnico y número visible de negocio. | `reunion...` 8: “Eso es un número interno... Hay otro. Hay otro que es el vuestro.” | Clara |
| Numeración por contexto/tipología | Moeve trabaja más unificado; Repsol arrastra numeración por tipología. | `reunion...` 8: “Moeve tiene un solo número. Repsol tiene un número por cada tipología.” | Clara como diagnóstico, no como regla final cerrada |
| Categoría de trabajo | La reunión habla más de `categoría de trabajo` que de un label mixto; la unificación exacta Moeve/Repsol sigue abierta, pero la columna visible debe llamarse así. | `listado_exhaustivo...` 014, 068; `reunion...` 8: “tenemos una categoría de trabajo y una descripción de trabajo... hay que unificarlo” | Clara en nomenclatura, con duda funcional interna |
| Descripción | La descripción debe seguir visible y editable en línea. | `listado_exhaustivo...` 014; `reunion...` 8 y `9` | Clara |
| Pedido dentro de Trabajos | El pedido debe agruparse dentro del flujo de trabajos, no como paso separado diario. | `listado_exhaustivo...` 017, 019, 026 | Clara |
| Crear trabajo sin pedido | El trabajo puede existir antes del pedido. | `listado_exhaustivo...` 015; `reunion...` 1161-1163 | Clara |
| Tarifario / contrato | En la operativa diaria hablan más de `tarifario` que de una columna llamada `contrato/tarifa`; el contrato sigue siendo estructural, pero la selección visible se verbaliza como `tarifario`. | `listado_exhaustivo...` 019, 072-073, 091; `reunion...` 565-567, 1099-1147 | Clara en nomenclatura visible, con lógica contractual pendiente |
| Importes | Se usan como columnas de control en la tabla, sobre todo pedido/solicitado/facturado. | `listado_exhaustivo...` 026-029; `reunion...` 1213-1230 | Clara |
| Solicitado en blanco o cero | En blanco tiende a significar no solicitado; cero suele apuntar a cancelado. | `reunion...` 1228-1230 | Clara operativamente, pendiente de automatización global |
| Estado | En Excel lo tratan como campo muy calculado/derivado, aunque visible y filtrable. | `listado_exhaustivo...` 027-029; `reunion...` 513-531 | Clara |
| Cancelados | Deben verse, pero al final, no mezclados arriba. | `listado_exhaustivo...` 029; `reunion...` 1203-1230 | Clara |
| Fecha terminación | La fecha la marca el técnico; puede registrarse al cerrar, pero no es un cálculo “mágico”. | `reunion...` 944-966 | Clara |
| Observaciones | Deben ser accesibles desde tabla Excel con edición puntual, idealmente sin ocupar una gran celda permanente. | `reunion...` 1043-1077 | Clara |
| Datos de estación irrelevantes | Fecha de baja u otros campos de estación no deben contaminar la vista operativa. | `reunion...` 1185-1191 | Clara |
| Sidebar Pedidos | `Trabajos` debe ser el centro operativo y `Pedidos` se conserva como módulo visible de consulta y control. | `tareasComparar.md` 216-226 y decisión fijada de este documento | Clara |

## 5. Tabla final aplicada

Propuesta de tabla final recomendada para implementación posterior, manteniendo la tabla compacta y alineada con la reunión.

| Orden final | Nombre visible final | Campo real | Cómo se muestra | Cómo se edita | Filtro/búsqueda | Motivo |
| ---------- | -------------------- | ---------- | --------------- | ------------- | --------------- | ------ |
| 1 | `Nº trabajo` | `numero_trabajo_visible` con soporte de `numero_trabajo` y `numero_trabajo_operativo` | Valor visible de negocio; referencia interna solo como apoyo secundario, no como columna aparte | Inline en la lógica que se decida, sin duplicar columnas | Sí, global | La reunión pide un único número visible de trabajo. |
| 2 | `Nº estación` | `codigo_estacion` | Código/ordinal operativo de estación | Selector de estación | Sí, global y filtro específico | Está en el bloque inicial literal pedido por negocio. |
| 3 | `Nombre estación` | `nombre_estacion` | Texto truncado con tooltip si hace falta | Indirecto al cambiar estación | Sí, global | Es parte del orden operativo real. |
| 4 | `Municipio` | `municipio` | Texto simple, compacto | Indirecto al cambiar estación | Sí, global y filtro específico | La reunión lo cita explícitamente. |
| 5 | `Provincia` | `provincia` | Texto simple, compacto | Indirecto al cambiar estación | Sí, global y filtro específico | La reunión lo cita explícitamente. |
| 6 | `Categoría de trabajo` | `categoria`, `id_tipo_documento`, `id_tipo_trabajo`, `tipo_trabajo_nombre` | Una sola columna funcional que absorbe la casuística Moeve/Repsol | Inline con selector o campo según contexto | Sí, global y filtro específico | La transcripción se apoya más en `categoría de trabajo` como nombre de negocio. |
| 7 | `Descripción` | `descripcion_trabajo` | Texto visible principal del trabajo | Inline | Sí, global | Es una de las piezas centrales del Excel operativo. |
| 8 | `Tarifario` | `id_contrato`, `id_tarifario`, `nombre_contrato`, `nombre_tarifa` | Columna compacta con nombre operativo real; el texto puede incluir referencia contractual cuando ayude a distinguir opciones | Inline solo donde proceda | Sí, global y filtros avanzados | La reunión usa `tarifario` como nombre operativo del dato que el trabajo selecciona. |
| 9 | `Pedidos` | `pedidos_resumen`, `pedidos_count` | Secuencia `Pedido 1`, `Pedido 2`, `Pedido 3`, clicable | No como texto libre; navegación/consulta | Sí, global y filtros avanzados | La reunión empuja a integrar pedido dentro de trabajos. |
| 10 | `Acción pedido` | Derivado | Botón contextual corto | Acción contextual | No | Hace visible el siguiente paso sin ensuciar la columna `Pedidos`. |
| 11 | `Importe pedido` | `importe_pedido_total` | Importe agregado, alineado a la derecha | No en esta vista | Sí, global y filtro avanzado | Control económico principal. |
| 12 | `Solicitado` | `importe_solicitado_total` | Importe agregado, alineado a la derecha | No en esta vista | Sí, global y filtro avanzado | Debe seguir visible para control de solicitud. |
| 13 | `Facturado` | `importe_facturado_total` | Importe agregado, alineado a la derecha | No en esta vista | Sí, global y filtro avanzado | Debe seguir visible para control económico. |
| 14 | `Estado` | `estado` | Badge/select compacto en una sola línea | Inline por ahora; a futuro más derivado | Sí, global y filtro específico | Debe leerse como apoyo visual, con etiquetas reales `Trabajo en curso`, `Terminado`, `Pendiente de facturar`, `Facturado`, `Finalizado`, `Cancelado`. |
| 15 | `Responsable` | `id_responsable_ciete`, `nombre_responsable` | Nombre compacto | Inline por selector | Sí, global y filtro específico | Útil para explotación operativa. |
| 16 | `Fecha encargo` | `fecha_encargo` | Fecha corta | Inline | Sí, global y filtro rápido | Fecha de entrada del trabajo. |
| 17 | `Fecha solicitud pedido` | `fecha_solicitud_pedido` | Fecha corta | No en esta vista | Sí, global | El dato real es una fecha y ya queda nombrado con precisión. |
| 18 | `Fecha terminación` | `fecha_terminacion` | Fecha corta | Inline | Sí, global | La reunión la considera una fecha operativa real del técnico. |
| 19 | `Observaciones` | `observaciones` | Botón/celda compacta con modal o popover | Modal puntual | Sí, global | La reunión pide acceso desde Excel, pero sin ensanchar toda la tabla. |
| 20 | `Acciones` | Derivado | Abrir ficha y acciones de soporte | Botones | No | Conviene dejarlo al final y muy compacto. |

## 6. Columnas a ocultar/fusionar/renombrar

### 6.1. Columnas o nombres que no deberían verse como columna separada

- `numero_trabajo` y `numero_trabajo_operativo` no deberían exponerse como dos columnas distintas.
- `numero_interno`, `numero_ciete`, `numero_operativo` o equivalentes legacy no deberían aparecer como labels visibles si no existen como campo funcional autónomo.
- `id_tipo_documento` e `id_tipo_trabajo` no deberían abrirse como dos columnas principales separadas mientras negocio siga pensando en una sola idea de tipo/categoría visible.
- `id_contrato` e `id_tarifario` no deberían verse como columnas técnicas independientes; deben vivir bajo una única columna visible `Tarifario`.
- `id_pedido_principal`, `numero_pedido_principal`, `pedidos_count` y `pedidos_resumen` no deben aflorar como columnas crudas.
- `responsable_cliente` no es hoy una columna principal de la vista operativa y no conviene meterla sin necesidad.

### 6.2. Renombres recomendados

- `Fecha solicitud pedido` queda aplicado como nombre visible final para esa columna.
- `Categoría de trabajo` queda como nombre visible aplicado para la columna única.
- `Tarifario` queda aplicado como nombre visible final de la antigua columna `Contrato / Tarifa`.
- `Nº trabajo` debe seguir siendo el único nombre visible principal para la numeración del trabajo.

### 6.3. Campos que deberían quedar en detalle, tooltip o modal

- Referencia interna/base de numeración del trabajo: solo apoyo secundario, no columna propia.
- Observaciones extensas: modal o popover, no bloque de texto expandido permanente.
- Datos de baja o estado administrativo de estación: fuera de la tabla operativa.
- Detalles de pedido demasiado largos o líneas de pedido: fuera de esta tabla.

## 7. Tratamiento de Pedidos en sidebar

### Decisión fijada

- `Pedidos` se mantiene visible en el sidebar.
- `Trabajos` queda como pantalla operativa principal para ejecución.
- `Pedidos` queda como módulo de consulta, búsqueda, fichas y control.
- Dirección, contabilidad y soporte pueden seguir usándolo como acceso habitual.
- En este bloque no se cambian permisos ni se oculta la entrada de navegación.

### Motivo

La reunión empuja a centralizar la operativa en `Trabajos`, pero el producto sigue necesitando un módulo explícito de consulta y control de pedidos. Mantener `Pedidos` visible evita romper flujos de dirección, contabilidad y soporte, sin devolverle el papel de pantalla operativa principal.

## 8. Dudas funcionales pendientes

- Si la numeración visible definitiva debe seguir una regla única por contexto o por tipología, especialmente en Repsol.
- Si `Categoría de trabajo` debe seguir siendo una sola columna visible o terminar dividiéndose conceptualmente en documento/tipología en algunos contextos.
- Si `Estado` debe permanecer editable o pasar a una lógica más derivada/automática con menos intervención manual.
- Si `Fecha solicitud pedido` debe quedarse siempre visible en tabla o pasar a columna opcional según el ancho disponible.
- Si `Tarifario` debe quedarse bloqueado tras existir pedidos en todos los contextos o solo en algunos.
- Si `Observaciones` necesita además un indicador visual más fuerte cuando exista contenido relevante.

## 9. Recomendación de implementación posterior

1. No cambiar permisos ni ocultación del sidebar en este bloque; `Pedidos` ya queda fijado como módulo visible de consulta y control.
2. Validar manualmente con casos reales Moeve y Repsol la nomenclatura ya aplicada.
3. Si la validación visual es correcta, dar por cerrado este bloque de columnas antes de entrar en Bloque 2 de contrato/tarifario/sociedad.
