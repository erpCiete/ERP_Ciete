# Lo que hace la API · ERP Ciete

## 1. Alcance del documento
Este documento describe lo que la API debe cubrir según los requisitos funcionales definidos con cliente.

Nota:
- Este contenido es base funcional de referencia.
- La validación de implementación real se reflejará con estado `Implementado` en cada ítem.

## 2. Capacidades funcionales esperadas

| ID | Capacidad API | Comportamiento esperado | Estado |
|---|---|---|---|
| API-01 | Autenticación y sesión | Acceso autenticado y controlado por usuario | Definido |
| API-02 | Autorización por rol | Restringir acciones por perfil y estado de obra | Definido |
| API-03 | Segmentación por cliente | Aislar datos de Repsol y Cepsa en consultas/listados | Definido |
| API-04 | CRUD de obras | Alta, consulta, edición y cierre de obras con validaciones | Definido |
| API-05 | Flujo de estados de obra | Gestionar transiciones del workplan con historial | Definido |
| API-06 | Control de pedidos/avisos | Vincular pedido/aviso a obra y validar coherencia | Definido |
| API-07 | Control de facturación | Relacionar obra con estado de facturación y alertar pendientes | Definido |
| API-08 | Legalizaciones 1:N | Registrar múltiples entradas de legalización por obra | Definido |
| API-09 | Estaciones por cliente | Listar y buscar estaciones con pertenencia a cliente | Definido |
| API-10 | Tarifarios | Consultar y validar trabajo contra tarifario aplicable | Definido |
| API-11 | Informes operativos | Exponer datos para reportes por cliente, estado, tipo y empleado | Definido |
| API-12 | Trazabilidad | Registrar quién cambia qué y cuándo en entidades críticas | Definido |

## 3. Reglas de negocio que debe aplicar la API
- No mezclar datos de Repsol y Cepsa.
- No permitir edición de obra cerrada sin permiso explícito.
- No permitir transiciones de estado inválidas.
- Validar coherencia entre importe de trabajo y pedido.
- Garantizar integridad entre obra, pedido, facturación y legalizaciones.

## 4. Endpoints de referencia (propuestos)
- `POST /api/auth/login`
- `GET /api/clientes`
- `GET /api/estaciones?cliente_id=`
- `GET /api/obras`
- `POST /api/obras`
- `PUT /api/obras/{id}`
- `PATCH /api/obras/{id}/estado`
- `POST /api/obras/{id}/legalizaciones`
- `GET /api/pedidos`
- `POST /api/pedidos`
- `GET /api/facturacion/pendientes`
- `GET /api/informes/obras`

## 5. Criterio de aceptación técnico-funcional
Cada capacidad pasa a `Implementado` cuando exista:
- Endpoint operativo.
- Validación de reglas de negocio.
- Prueba funcional documentada.
- Trazabilidad con requisito cliente.
