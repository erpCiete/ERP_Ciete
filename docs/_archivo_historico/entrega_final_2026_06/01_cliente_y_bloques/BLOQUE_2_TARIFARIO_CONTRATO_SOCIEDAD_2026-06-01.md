# Bloque 2 - Tarifario, contrato y sociedad facturadora

## 1. Objetivo

Cerrar la regla funcional provisional que une:

`Contexto / cliente -> empresa cliente -> contrato -> tarifario -> trabajo -> pedido -> sociedad facturadora en factura`

Sin inventar tablas ni campos nuevos y sin mezclar todavía líneas, exportación, estados ni facturación operativa completa.

Corrección de enfoque de Bloque 2:

- `Contrato` y `Tarifario` pertenecen al flujo de maestros.
- El cuello de botella real de este bloque no es adelantar toda la sociedad facturadora a `Trabajo` o `Pedido`.
- El cuello de botella real es verificar si ya existe una forma de marcar un `Tarifario` como predeterminado/habitual dentro de un contrato y, si no existe, implementarla dentro de Bloque 2.

## 2. Fuentes revisadas

- `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/ESTADO_Y_BLOQUES_RESTANTES_REUNION_CIETE_2026-05-29.md`
- `docs/02_CLIENTE/ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`
- `app/Models/Empresa.php`
- `app/Models/Contrato.php`
- `app/Models/ContratoEmpresaFacturadora.php`
- `app/Models/Tarifario.php`
- `app/Models/Trabajo.php`
- `app/Models/Pedido.php`
- `app/Models/Factura.php`
- `app/Models/ContextoCliente.php`
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `app/Http/Requests/Api/StoreTrabajoRequest.php`
- `app/Http/Requests/Api/StorePedidoRequest.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `database/migrations/2026_03_24_000020_create_empresas_contactos_base_tables.php`
- `database/migrations/2026_03_24_000025_create_estaciones_tables.php`
- `database/migrations/2026_03_24_000035_create_tarifarios_tables.php`
- `database/migrations/2026_03_24_000050_create_trabajos_operativa_tables.php`
- `database/migrations/2026_05_02_000120_create_factura_items_and_update_facturas.php`
- `database/migrations/2026_05_05_000130_create_contrato_empresas_facturadoras.php`
- `database/abaco_ciete_prod_dump.sql`
- `database/manual/2026_05_07_insert_muestra_operativa_50_casos.sql`
- `tests/Feature/FacturaTest.php`

## 3. Estructura real detectada

| Entidad | Tabla/modelo real | Campos clave | Relaciones actuales | Problemas detectados |
| ------- | ----------------- | ------------ | ------------------- | -------------------- |
| Contexto | `contextos_cliente` / `ContextoCliente` | `id_contexto`, `codigo`, `nombre` | Contexto base de empresas, contratos, tarifarios, estaciones, trabajos, pedidos y facturas | No modela por sí solo cliente fiscal; solo agrupa operativa |
| Cliente | No existe modelo `Cliente`; se usa `empresas` / `Empresa` con `tipo_empresa` | `id_empresa`, `id_contexto`, `nombre`, `cif`, `tipo_empresa` | `Empresa` se usa como cliente de estación, contrato, trabajo y factura | Mezcla concepto de cliente y sociedad facturadora en la misma entidad técnica |
| Empresa cliente | `empresas` / `Empresa` | `id_empresa`, `id_contexto`, `tipo_empresa`, `cif` | `Contrato.id_empresa_cliente`, `Trabajo.id_empresa_cliente`, `Factura.id_empresa_cliente`, `EstacionServicio.id_empresa_cliente` | No separa semánticamente cliente comercial y sociedad/CIF facturadora |
| Sociedad facturadora | No existe modelo `Sociedad`; se usa `Empresa` + pivote `contrato_empresas_facturadoras` / `ContratoEmpresaFacturadora` | `id_empresa`, `id_contrato`, `id_contexto`, `activo` | `Contrato.empresasFacturadoras()`, `Factura.id_empresa_facturadora` validada contra pivote | `Trabajo` y `Pedido` no guardan sociedad facturadora |
| Contrato | `contratos` / `Contrato` | `id_contrato`, `id_contexto`, `id_empresa_cliente`, `codigo_contrato`, `activo` | `belongsTo Empresa`, `hasMany Tarifario`, `hasMany Trabajo`, `belongsToMany Empresa` como facturadoras permitidas | El contrato sí conoce empresas facturadoras permitidas, pero el flujo de trabajo no elige una |
| Tarifario | `tarifarios` / `Tarifario` | `id_tarifario`, `id_contexto`, `id_contrato`, `nombre`, `version`, `activo` | `belongsTo Contrato`, `hasMany Trabajo`, `hasMany Pedido`, `hasMany TarifarioLinea` | No tiene campo de predeterminado ni vínculo directo a una sociedad facturadora concreta |
| Estación | `estaciones_servicio` | `id_estacion_servicio`, `id_contexto`, `id_empresa_cliente`, `codigo_estacion` | Cada estación pertenece a una empresa cliente dentro de un contexto | La estación ayuda a fijar empresa cliente, pero no sociedad facturadora |
| Trabajo | `trabajos` / `Trabajo` | `id_contexto`, `id_empresa_cliente`, `id_estacion_servicio`, `id_contrato`, `id_tarifario` | `belongsTo Empresa`, `Estacion`, `Contrato`, `Tarifario`; `hasMany Pedido` | No tiene `id_empresa_facturadora` ni grupo de CIFs; solo puede quedar atado a empresa cliente + contrato + tarifario |
| Pedido | `pedidos` / `Pedido` | `id_contexto`, `id_trabajo`, `id_tarifario`, `numero_pedido` | `belongsTo Trabajo`, `belongsTo Tarifario`, `hasMany PedidoItem` | No tiene contrato explícito ni sociedad facturadora; hereda de trabajo de forma implícita |
| Factura | `facturas` / `Factura` | `id_contexto`, `id_trabajo`, `id_contrato`, `id_empresa_cliente`, `id_empresa_facturadora` | `belongsTo Contrato`, `Empresa` cliente, `Empresa` facturadora; `hasMany FacturaItem` | La validación fuerte de sociedad ocurre aquí, demasiado tarde para que `Trabajo` la anticipe |

Lectura consolidada de estructura:

- No existe modelo `Sociedad` separado.
- El repositorio usa `Empresa` tanto para cliente como para sociedad/CIF facturadora.
- La autorización real de sociedades facturadoras existe y está modelada, pero solo en `ContratoEmpresaFacturadora`.
- `Trabajo` y `Pedido` no pueden decidir ni persistir una sociedad facturadora concreta con el modelo actual.

## 4. Qué decía la reunión

| Tema | Qué se dijo | Decisión clara / duda | Impacto en código |
| ---- | ----------- | --------------------- | ----------------- |
| Moeve y varias sociedades | En Moeve una misma tarifa puede servir para varias sociedades que facturan | Clara | Encaja con `ContratoEmpresaFacturadora` como lista permitida, no con un único campo en `Tarifario` |
| Repsol y tarifa por sociedad | En Repsol se verbaliza que cada sociedad tiende a tener su propia tarifa | Parcialmente clara | Refuerza usar contrato/tarifario como ancla principal del flujo |
| Tarifario por cliente o por sociedad | La reunión corrige la idea inicial de cliente puro y empuja a asociar tarifa/contrato a sociedades facturadoras elegibles | Clara como dirección, no cerrada al detalle | El modelo actual solo lo soporta indirectamente por contrato y pivote |
| Varias sociedades posibles | Se plantea que una tarifa pueda aplicar a una o varias sociedades del cliente | Clara | El pivote actual soporta varias empresas por contrato |
| Predeterminado | Se comenta que al crear trabajo a veces no se sabe aún qué sociedad facturará y que debería salir una opción por defecto y luego poder cambiarse | Duda abierta | No existe campo `predeterminado` ni lugar claro donde persistir esa decisión en `Trabajo` |
| Trabajo y contrato | El trabajo queda asociado al contrato y desde ahí se condiciona lo demás | Clara | Ya existe en `trabajos.id_contrato` y se deriva desde `id_tarifario` |
| Pedido hereda criterio | El pedido debe nacer del trabajo y conservar el criterio tarifario | Clara | Ya existe herencia de `id_tarifario` desde `Trabajo` a `PedidoController` |
| Facturas de pedidos compatibles | Una factura puede agrupar pedidos de trabajos distintos, pero compatibles y bajo misma tarifa/contrato | Clara | Facturación necesita contrato coherente y sociedad permitida por ese contrato |
| CIF en factura | La factura sí necesita sociedad/CIF concreto | Clara | Ya existe `id_empresa_facturadora` y validación contra `ContratoEmpresaFacturadora` |

Nota de revisión posterior:

- El correo de César del `2026-05-20 17:03` es posterior a esta reunión y fija una regla más concreta para Repsol: `La tarifa de Repsol es única`.
- Por tanto, cualquier lectura previa de “Repsol por sociedad” o “varios tarifarios Repsol” debe conservarse solo como antecedente histórico y no como regla vigente cerrada.
- A fecha `2026-06-01`, la interpretación operativa más segura es:
    - mantener el modelo general `Contrato -> Tarifario -> Trabajo -> Pedido`;
    - documentar Repsol como un único tarifario activo aplicable en el contexto Repsol;
    - dejar como pendiente de negocio únicamente una excepción futura si César o CIETE la contradicen expresamente.

## 5. Regla funcional propuesta

### Regla funcional definitiva provisional

`Contrato y tarifarios se gestionan en maestros; un tarifario puede quedar marcado como predeterminado/habitual; Trabajos usa ese tarifario por defecto; Pedido lo hereda; Factura decide la sociedad facturadora válida.`

### Regla operativa

1. Los contratos se gestionan en maestros.
2. Los tarifarios se crean y asocian dentro del flujo contrato/tarifario de maestros.
3. Dentro de un contrato puede haber uno o varios tarifarios.
4. Uno de esos tarifarios debe poder marcarse como predeterminado/habitual.
5. En `Trabajos`, la columna `Tarifario` debe mostrar por defecto ese tarifario habitual si existe.
6. Si solo hay un tarifario compatible, debe autoseleccionarse.
7. Si hay varios y uno está marcado como predeterminado, debe usarse ese.
8. Si hay varios y ninguno está marcado como predeterminado, el usuario debe seleccionar uno antes de crear pedido.
9. El usuario puede cambiar el tarifario mientras el trabajo no tenga pedidos.
10. Si el trabajo ya tiene pedidos, el tarifario queda bloqueado.
11. Crear pedido solo se desbloquea cuando el trabajo tiene tarifario válido.
12. El pedido hereda siempre el mismo `id_tarifario` del trabajo.
13. Las líneas del pedido saldrán de ese tarifario en Bloque 3.
14. La sociedad facturadora/CIF concreto se decide después en `Factura`, validándose contra `ContratoEmpresaFacturadora`.

### Separación correcta de responsabilidades

- `Tarifario predeterminado/habitual`: sí pertenece a Bloque 2.
- `Sociedad facturadora concreta`: pertenece al flujo de factura, salvo que negocio pida explícitamente adelantarla a `Trabajo` o `Pedido`.

### Respuestas directas a las preguntas del bloque

1. `¿De dónde sale el Tarifario que se muestra en Trabajos?`
   Sale de `trabajos.id_tarifario`, con nombre enriquecido desde `tarifarios.nombre` y `contratos.nombre`. En comportamiento esperado, se precarga desde el tarifario predeterminado/habitual del contrato si existe.

2. `¿Depende de cliente, sociedad facturadora, contrato o estación?`
   Hoy depende de `contexto + estación -> empresa cliente -> contrato -> tarifario`. La sociedad facturadora no debería condicionar este punto salvo que negocio cambie el alcance.

3. `¿Cuándo se puede cambiar?`
   Solo antes de que existan pedidos asociados al trabajo.

4. `¿Qué pasa si el trabajo ya tiene pedidos?`
   El cambio de tarifario debe bloquearse para no romper coherencia entre trabajo, pedido y futuras líneas.

5. `¿Qué debe heredar el pedido?`
   El mismo `id_tarifario` del trabajo y, por derivación, el mismo contrato implícito.

6. `¿Qué necesita luego facturación?`
   `id_contrato` coherente y `id_empresa_facturadora` válida dentro del pivote `contrato_empresas_facturadoras`.

7. `¿Cómo se evita elegir un tarifario incompatible?`
   Filtrando por contexto, empresa cliente del trabajo/estación, contrato activo y tarifario activo; y, cuando exista marca de habitual, aplicándola como selección por defecto.

8. `¿Qué hacer si hay varias sociedades facturadoras posibles?`
   No resolverlo en `Trabajo/Pedido` por defecto; dejarlo para `Factura`, donde ya existe validación contra las sociedades permitidas por contrato.

9. `¿Existe predeterminado real en datos actuales o no?`
   No aparece un campo explícito revisado en modelo, migraciones ni catálogos actuales.

10. `¿Qué queda pendiente de confirmación con negocio?`
   Cómo debe modelarse el tarifario predeterminado/habitual en maestros y cómo debe consumirse desde `Trabajos`.

## 6. Regla aplicada en código, si aplica

Sí se ha aplicado código nuevo en este bloque.

Campo usado:

- no existía campo reutilizable real;
- se ha creado migración mínima sobre `tarifarios`:
  - `es_predeterminado`
  - boolean
  - default `false`

Regla aplicada:

- un contrato puede tener varios tarifarios activos;
- solo uno puede quedar marcado como `es_predeterminado` por contrato;
- al marcar uno como predeterminado desde maestros, se desmarcan los demás del mismo contrato;
- al desactivar un tarifario predeterminado, queda desmarcado y no se promociona otro automáticamente.

Aplicación en maestros:

- `Tarifarios/Form.jsx` muestra:
  - `Tarifario predeterminado`
  - `Marcar como predeterminado`
  - `Predeterminado para nuevos trabajos`
- `Tarifarios/Index.jsx` muestra visualmente qué tarifario es el predeterminado.

Aplicación en `Trabajos`:

- el catálogo operativo expone `is_default` desde `tarifarios.es_predeterminado`;
- en la fila nueva de `Trabajos`, si existe tarifario predeterminado compatible se preselecciona;
- si solo existe uno compatible, se autoselecciona;
- si el trabajo ya tiene pedidos, `TrabajoController@patchField` sigue bloqueando el cambio de tarifario.

Aplicación en `Pedidos`:

- `StorePedidoRequest` bloquea crear pedido si el trabajo no tiene tarifario válido;
- `StorePedidoRequest` y `UpdatePedidoRequest` rechazan un `id_tarifario` distinto al del trabajo;
- `PedidoController@store` y `PedidoController@update` fuerzan la herencia del `id_tarifario` desde el trabajo.

Conclusión de implementación:

- `estado del bloque hoy`: **implementado en código**
- `implementación nueva`: **sí, migración mínima + maestros + Trabajos + refuerzo Pedidos**

## 7. Casos Moeve

- Moeve admite en la reunión una tarifa y varias sociedades facturadoras posibles.
- El modelo actual lo soporta solo a nivel de contrato/factura.
- En `Trabajo`, el criterio seguro hoy es:
  - elegir tarifario compatible con estación y empresa cliente;
  - no forzar sociedad facturadora todavía;
  - elegir la sociedad/CIF en factura, validada contra el contrato.

Lectura práctica:

- Moeve sí puede trabajar con un mismo contrato/tarifario y varias sociedades facturadoras posteriores.
- Por eso no es seguro fijar una sola sociedad desde `Trabajos` sin más datos.

## 8. Casos Repsol

- En la reunión aparece la idea de que Repsol está más cerca de tarifa por sociedad o, como mínimo, de mayor separación por tarifa.
- Esa hipótesis queda superada documentalmente por el correo posterior de César del `2026-05-20 17:03`: `La tarifa de Repsol es única`.
- El modelo actual sigue resolviendo `Trabajo -> Contrato -> Tarifario`.
- Si en datos reales cada sociedad acaba teniendo su propio contrato/tarifario, entonces la selección por empresa cliente + contrato/tarifario ya acerca bastante el problema.
- Si negocio exige elegir sociedad concreta antes de factura, el modelo sigue quedándose corto igual que en Moeve.

Lectura práctica:

- Repsol queda hoy mejor descrito como un único tarifario activo con múltiples líneas/conceptos, no como varios tarifarios alternativos simultáneos.
- Aun así, la persistencia explícita de sociedad facturadora sigue faltando en trabajo/pedido.

## 9. Dudas pendientes de negocio

1. `¿Puede haber un predeterminado distinto por sociedad facturadora dentro del mismo contrato?`
2. `¿El predeterminado debe aplicarse solo a nuevos trabajos o también recalcular trabajos sin pedido ya existentes?`
3. `¿La sociedad facturadora debe seguir decidiéndose en factura salvo instrucción expresa en contra?`

## 10. Riesgos

- La unicidad del predeterminado queda garantizada a nivel de aplicación, no mediante restricción parcial nativa de base de datos.
- Si negocio exige predeterminado por sociedad facturadora y no por contrato, este modelo se quedará corto.
- Recalcular automáticamente trabajos antiguos sin pedido podría generar sorpresa operativa si no se valida antes con negocio.
- Mezclar en este bloque la sociedad facturadora concreta rompería la separación actual `Trabajo/Pedido` frente a `Factura`.

## 11. Criterios de aceptación para cerrar Bloque 2

- Existe una regla única y documentada de cómo se elige `Tarifario` en `Trabajos`.
- Queda separado documentalmente `tarifario predeterminado/habitual` de `sociedad facturadora concreta`.
- Queda claro que `Contrato` se deriva desde `Tarifario`.
- Queda claro cuándo puede cambiarse el tarifario y cuándo no.
- El pedido hereda siempre el mismo tarifario del trabajo.
- Queda claro que la sociedad facturadora, con el modelo actual, se decide en factura y se valida contra el contrato.
- Existe soporte real en maestros para marcar el tarifario predeterminado.
- `Trabajos` consume el predeterminado compatible y `Pedido` queda protegido contra desalineación tarifaria.

## 12. Siguiente paso: Bloque 3

Siguiente paso recomendado:

- Bloque 3 — Líneas de pedido desde tarifario, manteniendo la herencia `Trabajo -> Tarifario -> Pedido -> Líneas`.
- No mezclar todavía exportación Moeve, decimales ni estados automáticos fuera del alcance propio de Bloque 3.
