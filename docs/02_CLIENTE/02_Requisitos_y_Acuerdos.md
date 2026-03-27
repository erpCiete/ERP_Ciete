# Requisitos y acuerdos · ERP Ciete

## 1. Requisitos funcionales del cliente

| ID | Requisito | Prioridad | Estado |
|---|---|---|---|
| RF-01 | Separación total de datos y reporting entre Repsol y Cepsa | Alta | Definido |
| RF-02 | Gestión completa del ciclo de obra (encargo, pedido, comienzo con/sin pedido, terminado, facturado, cerrado) | Alta | Definido |
| RF-03 | Relación obra-pedido-factura con control de coherencia | Alta | Definido |
| RF-04 | Registro de fecha de encargo y fecha real de terminación | Alta | Definido |
| RF-05 | Bloqueo de trabajos cerrados por rol/permiso | Alta | Definido |
| RF-06 | Control de legalizaciones con estructura 1:N por obra | Alta | Definido |
| RF-07 | Listado y buscador de estaciones por cliente | Media | Definido |
| RF-08 | Control de tarifarios asociados | Media | Definido |
| RF-09 | Informes por cliente, estado, tipo de trabajo y empleado | Alta | Definido |
| RF-10 | Trabajo multiusuario sin bloqueo de archivos | Alta | Definido |
| RF-11 | Trazabilidad de cambios sobre información crítica | Alta | Definido |
| RF-12 | Capacidad de operar con varios miles de registros/año | Alta | Definido |

## 2. Requisitos no funcionales
- Seguridad por autenticación, autorización y control de permisos.
- Rendimiento suficiente para operación diaria con crecimiento anual.
- Mantenibilidad del sistema para evolución por módulos.
- Disponibilidad operativa con estrategia de backup.

## 3. Acuerdos de proyecto
- No se considera válido replicar Excel en formato pantalla sin rediseñar proceso.
- Toda funcionalidad implementada debe vincularse a un requisito (RF-xx).
- Todo cambio de alcance se registra y aprueba antes de desarrollo.
- El estado de cada requisito debe reflejarse en documentación de sprint.

## 4. Acuerdos de trazabilidad documental
- Requisito origen -> módulo -> endpoint/controlador -> pantalla -> prueba.
- Toda decisión funcional relevante queda documentada en reunión.
- Todo bloqueo funcional debe registrar impacto y decisión tomada.

## 5. Preguntas abiertas para cierre funcional
- Definición exacta de cuándo una obra pasa a estado "cerrada".
- Roles finales con permisos globales y permisos por cliente.
- KPI prioritarios para dashboard ejecutivo.
- Política final de exportación a Excel por área.
