# Bitacora BACK · Eduardo Jimenez

## General
### Objetivo de la semana
- Implementar los módulos de Pedidos y Facturas con aislamiento de contexto estricto (MOEVE/REPSOL) e iniciar la importación v1.

## 2026-04-20
### Objetivo del dia
- Iniciar la Tarea B04-01 (API CRUD de Pedidos).
- Configurar la estructura de rutas protegidas y volcar la data demo inicial en BBDD para no bloquear al Front.

### Tareas realizadas
- Refactorización del archivo de rutas de la API para separar permisos de lectura (`pedidos.ver`) y escritura (`pedidos.gestionar`) en el nuevo recurso de pedidos.
- Actualización de seeders para inyectar 4 pedidos demo (2 MOEVE, 2 REPSOL), sus líneas anidadas (`pedido_items`) y 2 facturas condicionales. 

### Archivos tocados
- `routes/api.php`
- `database/seeders/DatosBaseSeeder.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:15 | Permiso genérico `pedidos.ver` en todo el `apiResource` permitía escritura no autorizada. | Alto | Segregación de rutas `match`, `post` y `delete` bajo el middleware `pedidos.gestionar`. | Cerrado |
| 11:30 | Archivo de controlador creado con extensión `.Php`, lo cual provocaría "Class not found" en el servidor Linux de producción. | Alto | Renombrado inmediato a `PedidoController.php`. | Cerrado |

### Decisiones tomadas
- Se utilizó lógica `upsert` en el `DatosBaseSeeder.php` basando las llaves en los IDs fijos extraídos del dump de producción (`abaco_ciete.sql`) para mantener coherencia y evitar colisiones al ejecutar `migrate:fresh --seed`.
- Se aplicó RBAC explícito a nivel de enrutamiento API antes de inyectar las políticas en los FormRequests.

### Pendiente para mañana
- Añadir en PermisosSeeder.php nuevos permisos: pedidos.ver, pedidos.gestionar, facturas.ver, facturas.gestionar
- Preparar la inyección de la lógica condicional en la Tarea B04-02 (CRUD Facturas).

### Handoff
- Las rutas `/api/v1/pedidos` ya están disponibles en backend. Frontend ya puede ir preparando las llamadas Axios u hooks requeridos. Carga de datos base disponible tras un `migrate:fresh --seed`.

## 2026-04-21
### Objetivo del dia
- Finalizar la Tarea B04-01 (Pedidos) alineando el código al esquema final de base de datos y garantizando 100% de cobertura en tests.
- Iniciar y completar la refactorización de la Tarea B04-02 (API CRUD de Facturas) implementando la lógica condicional estricta para MOEVE (CCP) y REPSOL (Doble factura).

### Tareas realizadas
- Refactorización profunda de Pedidos (`StorePedidoRequest`, `UpdatePedidoRequest`, `PedidoResource`, `PedidoFactory`) para mapear estrictamente las 19 columnas de la nueva BBDD (eliminando IDs obsoletos y adaptando los items a `codigo_servicio`).
- Creación y ejecución de la batería de pruebas `PedidoTest.php`. Se validó con éxito el RBAC, el aislamiento multi-tenant y la integridad transaccional (100% passing).
- Refactorización de la capa de Facturas (`FacturaController`, Requests, Resource, Factory). Se programaron condicionales dinámicos que obligan al uso de `numero_factura_ccp` (MOEVE) u `orden_factura` único por trabajo (REPSOL).
- Limpieza y unificación de `DatosBaseSeeder` eliminando bloques duplicados (upserts redundantes).
- Refactorización de `web.php` para proteger las vistas Inertia/React (`/crear` y `/editar`) bajo el middleware `permission:*.gestionar`.

### Archivos tocados
- `app/Http/Requests/Api/StorePedidoRequest.php` y `UpdatePedidoRequest.php`
- `app/Http/Resources/Api/PedidoResource.php`
- `database/Factories/PedidoFactory.php`
- `tests/Feature/PedidoTest.php`
- `app/Http/Controllers/Api/FacturaController.php` y `PedidoController.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php` y `UpdateFacturaRequest.php`
- `app/Http/Resources/Api/FacturaResource.php`
- `database/Factories/FacturaFactory.php`
- `database/Seeders/DatosBaseSeeder.php`
- `routes/web.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 09:30 | Excepción SQL: `Unknown column 'concepto_libre'` en el Seeder. | Alto | El código usaba el esquema antiguo. Se actualizó el Seeder, el Controller y los Requests para utilizar el nuevo esquema (`codigo_servicio`, `descripcion_servicio`). | Cerrado |
| 11:15 | `Fatal error`: Choque de firmas (Type Hinting) en `normalizeNullableString`. | Medio | Se eliminó la función redundante en los FormRequests hijos para heredar correctamente el parámetro `mixed` desde `BaseApiRequest`. | Cerrado |
| 12:45 | `Foreign key constraint fails` al ejecutar `PedidoTest.php`. | Alto | Las factorías generaban contextos aleatorios que chocaban con la jerarquía Empresa-Trabajo. Se forzó el paso del contexto en la factoría y se inicializó `ContextosClienteSeeder` en el `setUp()` del test. | Cerrado |
| 14:00 | Error 500 al guardar Pedido por método `createdResponse()` inexistente. | Bajo | Sustitución del trait por el retorno nativo de Laravel `->response()->setStatusCode(201)`. | Cerrado |

### Decisiones tomadas
- Se modificaron las reglas en `UpdateFacturaRequest` para extraer inteligentemente el `id_trabajo` desde el modelo si el Frontend no lo envía en una petición `PATCH` parcial, previniendo fallos en la regla `unique` compuesta de Repsol.
- Se estableció como estándar que los campos monetarios (`total`, `iva`, etc.) se casteen explícitamente a `(float)` en los Resources para evitar strings numéricos en React.

### Pendiente para mañana
- Desarrollar y ejecutar la suite de pruebas `tests/Feature/FacturaTest.php` (Tarea B04-04) para blindar la doble facturación y los aislamientos de contexto.
- Coordinar con FrontEnd el consumo de los nuevos payloads.

### Handoff
- Endpoints de Pedidos y Facturas operando al 100% bajo el nuevo modelo de datos.
- Las vistas en `web.php` ahora rechazan correctamente a usuarios de solo-lectura, mejorando la UX y la seguridad. Base de datos segura para hacer `migrate:fresh --seed`.


## 2026-04-22
### Objetivo del dia
- Implementar el motor de importaciones masivas de Excel (Tarea B04-03) aplicando las mejores prácticas para evitar saturación de memoria en el servidor.
- Preparar la estructura de tablas temporales (staging) para previsualización.

### Tareas realizadas
- Desarrollo de la clase `ExcelParserService` implementando lectura por trozos (`ChunkReadFilter`) usando la librería `PhpSpreadsheet`.
- Mapeo estricto de columnas (A-E) y creación de parseador inteligente para fechas serializadas de Excel.
- Refactorización de `ImportacionController` para implementar el flujo en 3 fases: Subida -> Staging (`importacion_filas`) -> Confirmación Transaccional.
- Creación de Form Request y API Resource para las validaciones y listados de historiales de importación.

### Archivos tocados
- `app/Services/ExcelParserService.php`
- `app/Http/Controllers/ImportacionController.php`
- `app/Http/Requests/Api/StoreImportacionRequest.php`
- `app/Http/Resources/Api/ImportacionResource.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 11:30 | Error 500: `Allowed memory size exhausted` al cargar un Excel grande. | Alto | Implementación de la interfaz `IReadFilter` (`ChunkReadFilter`) para leer el archivo en bloques de 200 filas. | Cerrado |
| 16:20 | Archivos temporales saturando el disco del servidor (`Storage`). | Medio | Se añadió la eliminación forzada del Excel original (`Storage::delete`) inmediatamente después de volcar los datos a la tabla de staging. | Cerrado |

### Decisiones tomadas
- Se decidió insertar los registros en la base de datos de previsualización (`importacion_filas`) en bloques usando `array_chunk($filas, 500)` para optimizar el rendimiento de MySQL.
- Relegar las validaciones de negocio (ej. "Estación no existe" o "Trabajo duplicado") al método `preview` para que el frontend las pinte de rojo antes de confirmar, mejorando enormemente la UX.

### Pendiente para mañana
- Finalizar pruebas de integración Frontend/Backend en los módulos de Facturas y Pedidos con los contextos cruzados (MOEVE/REPSOL).

### Handoff
- Backend de importaciones v1 100% operativo. El frontend ya puede consumir las rutas de `/importaciones` usando Inertia.

---

## 2026-04-23
### Objetivo del dia
- Resolver incidencias críticas (Blockers) de integración entre Frontend (React/Inertia) y Backend en la creación y listado de Pedidos y Facturas.

### Tareas realizadas
- Reconfiguración del sistema de aislamiento Multi-Tenant (Multicliente). Se adaptó el código para que el usuario Administrador (Contexto 3) pueda ver y operar sobre todos los contextos.
- Corrección del envío de datos desde `routes/web.php` a las vistas de React mediante Inyección de Resources de API en Inertia.
- Creación de un "traductor" de nomenclatura en los métodos `prepareForValidation` de Facturas para alinear los payloads del Front con las exigencias estrictas del esquema de la Base de Datos.

### Archivos tocados
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/FacturaController.php`
- `routes/web.php`
- `app/Http/Requests/Api/StoreFacturaRequest.php`
- `app/Http/Requests/Api/UpdateFacturaRequest.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 09:30 | Select de Trabajos vacío / Error 422 `The selected id trabajo is invalid` al crear. | Alto (Blocker) | El admin no pasaba la regla de contexto de BBDD. Se modificó la validación y el controlador para heredar el `id_contexto` del Trabajo seleccionado. | Cerrado |
| 11:45 | "Pedidos Fantasma": Al crear un pedido, no aparecía en el listado `Index.jsx`. | Alto | `web.php` estaba forzando un array `[]` vacío. Se cambió a inyectar `PedidoResource::collection()` directamente desde la BD. | Cerrado |
| 16:15 | Error fantasma 422 en Form de Facturas (ningún input marcado en rojo). | Alto | Front enviaba `factura_ccp` y nulls; Back esperaba `numero_factura_ccp` e `id_empresa`. Se aplicó traducción de keys y deducción de empresa en el `FormRequest`. | Cerrado |
| 18:50 | Error HTTP 500 `Call to undefined method fillFactura()`. | Alto (Blocker) | Métodos privados `fillFactura` y `syncPedidos` borrados accidentalmente durante refactor. Fueron restaurados al final del controlador. | Cerrado |

### Decisiones tomadas
- Se aplicó la filosofía de "Smart Backend": En lugar de modificar los estados o nombres de variables del Frontend, el Backend se encarga de interceptar la petición (`prepareForValidation`), sanearla, deducir relaciones implícitas (ej. inferir la empresa a través del trabajo) y convertir nulos a ceros para que la validación fluya sin fricción.

### Pendiente para mañana
- Consolidar código, limpiar ramas locales y abrir Pull Requests para pasar a Producción/Develop.

### Handoff
- El flujo completo de Pedidos y Facturas funciona perfectamente tanto a nivel visual (React) como transaccional (MySQL), tolerando correctamente las reglas de MOEVE (CCP) y REPSOL (Doble orden).

---

## 2026-04-24
### Objetivo del dia
- Cierre del Sprint 04. Mergeo de todas las ramas trabajadas a la rama principal (`develop`). Documentación de las Pull Requests y resolución final de dudas de arquitectura.

### Tareas realizadas
- Creación de *Pull Request* del Backend agrupando la refactorización de Tareas B04-01, B04-02 y B04-03.
- Creación de *Pull Request* del Frontend detallando la implementación de renderizado condicional, manejo de errores y consumo de la nueva API.
- Revisión cruzada del código subido para comprobar el cumplimiento de los estándares (PSR-12 en PHP, buenas prácticas de Hooks en React).

### Archivos tocados
- Historial de Git (Merge commits).

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| N/A | Jornada libre de bugs, enfocada 100% en documentación y despliegue. | - | N/A | - |

### Decisiones tomadas
- Se documentaron formalmente en los PRs las decisiones de negocio clave adoptadas durante la semana (el aislamiento de contexto y el tratamiento de claves virtuales). Esto facilitará el "onboarding" si entra otro programador al proyecto.

### Pendiente para el lunes
- Arrancar el Sprint 05 (Módulos adicionales o mejoras de visualización de métricas).

### Handoff
- Todo el código del Sprint 04 ha sido empaquetado, verificado y está listo en `develop`.