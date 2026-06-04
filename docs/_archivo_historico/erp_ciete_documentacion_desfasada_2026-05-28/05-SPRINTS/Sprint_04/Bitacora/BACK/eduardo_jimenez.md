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
```

## 2026-04-22
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-23
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-24
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 
