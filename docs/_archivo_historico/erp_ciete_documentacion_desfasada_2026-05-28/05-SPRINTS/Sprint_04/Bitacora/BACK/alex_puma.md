# Bitacora BACK · Alex Puma

## General
### Objetivo de la semana
- 

## 2026-04-20
### Objetivo del dia
Desarrollar el CRUD backend para el módulo de Pedidos, exponiendo los endpoints vía API REST y configurando las rutas web para Inertia.

### Tareas realizadas
Creación de la Factory para el modelo Pedido para facilitar la generación de datos de prueba.

Configuración y protección de rutas web (Inertia) para las vistas del CRUD de Pedidos (index, create, edit).

Registro del recurso API (apiResource) en las rutas protegidas para conectar con el PedidoController.

Análisis preventivo del seeder de la base de datos para evitar colisiones de datos.

### Archivos tocados
database/factories/PedidoFactory.php (Nuevo)

routes/web.php (Modificado - HOT FILE)

routes/api.php (Modificado - HOT FILE)

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 12:40 | Riesgo de sobrescritura/corrupción de datos demo en el DatosBaseSeeder (HOT FILE). |Medio| Se analizó el código existente y se detectó que ya había upserts deterministas de pedidos vinculados a trabajos. Se canceló la inyección de datos aleatorios. | Cerrado |

### Decisiones tomadas
No modificar DatosBaseSeeder.php: Se decidió mantener el seeder intacto para preservar la integridad de los datos de demostración preexistentes (los cuales ya incluyen pedidos e ítems enlazados correctamente a trabajos, facturas y cobros), evitando ensuciar la base de datos con registros aleatorios de la Factory.

### Pendiente para mañana
Iniciar el desarrollo del frontend (Vistas Inertia para el CRUD de Pedidos) consumiendo los endpoints y rutas creadas hoy. (Correspondiente a las tareas bloqueadas, ej: F04-01).

Realizar pruebas de integración para asegurar que la carga anidada (PedidoItem) funciona correctamente en los nuevos endpoints.

### Handoff
El backend del módulo de Pedidos está funcional y las rutas están expuestas.

Las rutas requieren el permiso pedidos.ver.

Aviso para QA/Frontend: No es necesario ejecutar un nuevo migrate:fresh --seed con fábricas nuevas; el seeder actual ya levanta 4 pedidos de demostración perfectamente relacionados para empezar a probar las vistas desde el primer minuto.

## 2026-04-21
### Objetivo del dia
Desarrollar el CRUD backend para el módulo de Facturas, implementando lógica de validación condicional según la empresa (doble factura para REPSOL y validación por número CCP para MOEVE). 

### Tareas realizadas
Creación de FacturaResource para estandarizar la salida JSON y exponer la relación con la tabla pivote de pedidos (factura_pedidos).

Implementación de StoreFacturaRequest y UpdateFacturaRequest con reglas dinámicas basadas en el id_contexto del usuario (Compound unique id_trabajo + orden_factura para REPSOL; unique numero_factura_ccp para MOEVE).

Desarrollo de FacturaController implementando el listado con filtros y las operaciones CRUD, asegurando la sincronización de pedidos dentro de transacciones de base de datos (DB::transaction). 

### Archivos tocados
app/Http/Controllers/Api/FacturaController.php

app/Http/Requests/Api/StoreFacturaRequest.php

app/Http/Requests/Api/UpdateFacturaRequest.php

app/Http/Resources/Api/FacturaResource.php 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:30 | Determinar la forma más segura de aplicar la lógica de validación por empresa sin ensuciar el request. | Bajo | Se decidió inyectar el id_contexto directamente desde el usuario autenticado (Auth::user()?->id_contexto) dentro del método rules() de los FormRequests. | Cerrado |

### Decisiones tomadas
Se utilizó el contexto del usuario en sesión (id_contexto) como fuente de la verdad para aplicar la lógica de validación de MOEVE o REPSOL, evitando depender de parámetros enviados desde el cliente que podrían ser manipulados.

Se añadió un borrado en cascada manual (detach()) en el método destroy del controlador como medida de seguridad para limpiar la tabla pivote de pedidos en caso de que la migración no tuviera la restricción onDelete('cascade'). 

### Pendiente para mañana
Iniciar las tareas que estaban bloqueadas por este CRUD: Tarea F04-02, Tarea B04-04 y Tarea D04-01 (presumiblemente el frontend de facturas y su integración). 

### Handoff
El API REST de Facturas está completamente operativo y validado.

El frontend ya puede enviar un array pedidos (con id_pedido e importe_aplicado) durante el POST/PUT para que se sincronicen automáticamente en la tabla pivote.

La API devolverá los errores 422 Unprocessable Entity correctamente formateados si se intenta duplicar una factura según la lógica del cliente activo. 

## 2026-04-22
### Objetivo del dia
Implementar el controlador de importación para el procesamiento de archivos Excel (columnas A-E) utilizando la librería PhpSpreadsheet, asegurando un manejo eficiente de la memoria y un flujo de trabajo seguro (Subida -> Preview -> Confirmación). 

### Tareas realizadas
Instalación e integración de la dependencia phpoffice/phpspreadsheet.

Desarrollo del controlador ImportacionController.php con los endpoints store (subida temporal), preview (lectura de datos para revisión) y confirm (procesamiento final).

Adaptación del controlador para trabajar con el ExcelParserService existente, aprovechando el sistema de lectura por trozos (Chunks) para optimizar el consumo de RAM.

Implementación de validaciones para archivos .xlsx, .xls y .csv y manejo de excepciones para evitar errores fatales en archivos mal formateados. 

### Archivos tocados
app/Http/Controllers/Api/ImportacionController.php (Creado/Modificado)

composer.json (No tocado , modificarse vía composer require) 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 09:15 | Necesidad de procesar archivos potencialmente grandes sin exceder el límite de memoria de PHP. | Medio | Se integró el ChunkReadFilter del servicio para procesar el archivo en bloques de 200 filas. | Cerrado |
|10:45|Riesgo de colisión en archivos temporales si varios usuarios importan a la vez.|Bajo|Se implementó el uso de uniqid() para generar nombres de archivo únicos en la carpeta temporal.|Cerrado|

### Decisiones tomadas
Almacenamiento Temporal: Los archivos se guardan en importaciones/temp hasta que el usuario confirma la importación, momento en el cual se eliminan para mantener el servidor limpio.

Preview Limitado: El endpoint de previsualización devuelve solo las primeras 5 filas parseadas para garantizar una respuesta rápida en el frontend.

Inyección de Dependencias: Se configuró el controlador para recibir el ExcelParserService vía constructor, facilitando futuros testeos unitarios.

### Pendiente para mañana
Desarrollar la lógica de negocio final para la inserción masiva en la base de datos (Modelos de Trabajo/Pedidos) tras la confirmación (Tarea F04-03).

Realizar pruebas de carga con archivos de más de 5,000 registros para validar la estabilidad del filtro de lectura.

Handoff 

### Handoff
El flujo de importación base está operativo.

La API ya expone los endpoints necesarios para que el frontend pueda mostrar la tabla de previsualización.

Importante: Se requiere que el entorno tenga instalada la extensión php-zip y php-xml para el correcto funcionamiento de PhpSpreadsheet.
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
