# Sprint 01 · BACK · Plan Unificado

**Periodo:** 23/03/2026 - 26/03/2026  
**Foco:** Arranque técnico + seguridad base + estructura mínima operativa (Laravel)

## Objetivo backend
Levantar la arquitectura base del backend en Laravel, consolidar la seguridad inicial heredada del Sprint 00 y dejar preparada una base técnica limpia, estable y sin conflictos para todo el proyecto.

El backend debe dejar operativo el **control de acceso (Login/Logout)**, la **protección inicial de rutas**, la **estructura mínima de controladores y endpoints base** y la **preparación técnica del contexto de cliente**, para que el equipo Frontend pueda avanzar sin bloqueos técnicos.

Además, desde este primer sprint debe quedar contemplada la **separación lógica y estricta entre Repsol y Cepsa**, ya que será una base crítica del ERP en siguientes fases.

## Fechas reales de ejecución restantes
- 25/03/2026
- 26/03/2026

## Flujo de Trabajo: Nuestro Tablero
Para que todos tengamos visibilidad total y evitemos cuellos de botella (especialmente en integraciones con Frontend), usaremos un tablero unificado. Intentad actualizar el estado de sus tareas diariamente.

El ciclo de vida de cada tarea pasará por las siguientes columnas:

- **Backlog:** Tareas identificadas y registradas, pero que aún no están priorizadas o refinadas para este ciclo.
- **Ready:** La tarea está perfectamente definida, documentada y lista para desarrollo. Si terminas tu tarea actual, coge una de aquí.
- **In Progress:** Tareas en las que se está trabajando activamente. Intentemos no tener cada uno más de 2 tareas en esta columna al mismo tiempo. Foco y terminación equipo.
- **In Review:** Has abierto una Pull Request (PR) siguiendo nuestra plantilla obligatoria. El código está esperando revisión de pares o mi aprobación para asegurar la calidad y seguridad.
- **Testing:** El PR ha sido aprobado a nivel de código y está desplegado en local/entorno de pruebas para que QA (Miguel Ángel) verifique que cumple los criterios de aceptación y no rompe nada (especialmente la separación de datos Repsol/Cepsa).
- **Done:** Código mergeado en la rama principal, probado y documentado. Tarea finalizada al 100%.

---

## Asignación de tareas
| ID | Tarea | Responsable | Prioridad | Estado | Bloqueos |
|---|---|---|---|---|---|
| BE-00 | Definición de repositorio, flujo Git y protección de ramas. | Eduardo Jiménez | Alta | Doing | Tarea transversal. Bloquea el inicio de nuevos desarrollos si no queda cerrada. |
| BE-01 | Desarrollo de endpoints de Auth (Login/Logout) y control de sesión. | Alex Puma | Alta | Ready | El Front depende directamente de esto para conectar el acceso base. |
| BE-02 | Integración de middlewares RBAC y conexión con BBDD del Sprint 00. | Chad Arzaga | Alta | Ready | Revisar base heredada y asegurar la protección inicial de rutas. |
| BE-03 | Creación de rutas y controladores stub (vacíos) de módulos principales. | Carlos Puchol | Media | Backlog | Debe quedar base para que Front pueda montar navegación aunque aún no haya lógica real. |
| BE-04 | Pruebas funcionales de Auth/RBAC y redacción de documentación de API. | Miguel Ángel Taborda | Alta | Backlog | Depende de que Auth y RBAC lleguen a Testing. |
| BE-05 | Pulido de base de datos inicial: revisar migraciones, nomenclatura, relaciones mínimas y coherencia con la base heredada. | Pablo Sevillano | Alta | ToDo | Coordinar con Chad para no duplicar trabajo sobre RBAC y estructura heredada. |
| BE-06 | Contrato API base: definir estructura común de respuestas JSON, errores y payloads mínimos de integración con Front. | Pablo Sevillano | Media-Alta | ToDo | Depende parcialmente de cómo quede BE-01 para cerrar el formato real de Auth. |
| BE-07 | Cierre técnico de sprint: checklist de integración, validación de rama y revisión final de evidencias backend. | Pablo Sevillano | Media | ToDo | Depende del avance general del sprint. Es tarea de remate y cierre. |

## Dependencias con FRONT
Como equipo Backend, somos el posible bloqueador técnico del equipo Front, así que debemos entregar con urgencia estas piezas:
1. **API de Login operativa** para que Front pueda conectar su formulario y gestionar sesión.
2. **Contrato inicial de respuestas** para que Front sepa qué devuelve cada endpoint base.
3. **Estructura de rutas mínimas** para que puedan preparar la navegación del ERP aunque aún no haya lógica funcional completa.

## Handoff técnico BE-05/BE-06
Documentación técnica preparada para arrancar implementación sin ambigüedad y sin invadir tareas ajenas:
- `01_BE-05_Pulido_BD_Inicial.md`
- `02_BE-06_Contrato_API_Base.md`

## Reparto por fechas

### 25/03/2026 · Día 1
**Objetivo del día:** Dejar montada la base estructural del backend para que el día 26 sea integrar, probar, documentar y cerrar.

**Entregables del día:**
- Flujo Git y ramas claras
- Auth base iniciada
- RBAC base conectado
- Rutas stub iniciales
- Revisión inicial de base de datos y migraciones
- Contrato API base empezado

| Responsable | Tareas del 25/03/2026 | Objetivo del día |
|---|---|---|
| Eduardo Jiménez | BE-00 | Dejar claro el flujo Git, protección de ramas y base estable de trabajo para todo el equipo. |
| Alex Puma | BE-01 | Levantar login/logout y control de sesión inicial. |
| Chad Arzaga | BE-02 | Conectar RBAC con la base heredada y asegurar protección de rutas inicial. |
| Carlos Puchol | BE-03 | Preparar rutas y controladores stub de módulos principales. |
| Pablo Sevillano | BE-05 + inicio BE-06 | Revisar base de datos/migraciones y empezar a fijar el contrato API común con Front. |
| Miguel Ángel Taborda | Preparación de pruebas y documentación | Preparar base de testing, checklist y estructura documental para cerrar más rápido el día 26. |

**Orden recomendado del día:**
- Primera mitad: Eduardo (BE-00), Alex (BE-01), Chad (BE-02), Pablo (BE-05).
- Segunda mitad: Carlos (BE-03), Pablo (inicio BE-06), Miguel (preparación de pruebas y documentación).

**Cierre obligatorio 25/03:**
- Flujo Git claro
- Login/logout levantado al menos en base
- RBAC inicial conectado
- Rutas stub creadas
- Revisión inicial de base de datos hecha
- Contrato API empezado

### 26/03/2026 · Día 2
**Objetivo del día:** Integrar todo, validar seguridad, rematar documentación, revisar coherencia técnica y dejar el backend listo para no bloquear a Front.

**Entregables del día:**
- Auth funcional validado
- Protección de rutas aplicada en base
- Rutas y controladores stub ordenados
- Contrato API mínimo compartido con Front
- Base de datos inicial revisada y pulida
- Testing y documentación mínimos cerrados
- Revisión técnica final del sprint

| Responsable | Tareas del 26/03/2026 | Objetivo del día |
|---|---|---|
| Eduardo Jiménez | Soporte de integración + revisión de PRs | Desatascar bloqueos, revisar merges y asegurar estabilidad de la rama de integración. |
| Alex Puma | Remate BE-01 | Dejar Auth operativa y ajustada para pruebas reales. |
| Chad Arzaga | Remate BE-02 | Dejar protección base de rutas y acceso bien encajada con Auth. |
| Carlos Puchol | Remate BE-03 | Dejar ordenadas las rutas y controladores stub de módulos principales. |
| Pablo Sevillano | Remate BE-05 + BE-06 + BE-07 | Cerrar revisión técnica de base de datos, contrato API y checklist final de cierre backend. |
| Miguel Ángel Taborda | BE-04 | Ejecutar pruebas funcionales mínimas y cerrar la documentación técnica básica. |

**Orden recomendado del día:**
- Primera mitad: Alex (remate Auth), Chad (protección de rutas), Pablo (cierre BE-05 y BE-06), Carlos (remate rutas), Eduardo (revisión intermedia).
- Segunda mitad: Miguel (BE-04), Pablo (BE-07), Eduardo (revisión final de integración), equipo completo corrigiendo bloqueos puntuales.

**Cierre obligatorio 26/03:**
- Login/logout funcional
- Protección base de rutas operativa
- Rutas stub listas para integración con Front
- Contrato API mínimo definido
- Base de datos inicial revisada
- Testing mínimo ejecutado
- Documentación técnica base cerrada

## Prioridad real por orden
| Prioridad | Tareas |
|---|---|
| Crítica | BE-00, BE-01, BE-02, BE-03 |
| Alta | BE-04, BE-05, BE-06 |
| Media | BE-07 |

## Qué no se puede quedar fuera
**Mínimo del sprint válido:**
- BE-00
- BE-01
- BE-02
- BE-03
- BE-04

**Mínimo de tus tareas:**
- BE-05
- BE-06

BE-07 puede quedar como cierre técnico resumido si falta tiempo, pero conviene dejarlo hecho para cerrar el sprint con orden real.

## Reglas obligatorias
- **Tecnología:** El backend se desarrolla en Laravel.
- **Seguridad:** No se exponen rutas privadas sin autenticación o control de acceso mínimo.
- **Coherencia:** No se improvisa el formato de respuestas JSON de la API.
- **Integración:** Toda ruta o respuesta que afecte a Front debe quedar mínimamente documentada.
- **Arquitectura:** La separación entre Repsol y Cepsa debe contemplarse desde la base técnica.
- **Repositorio:** No se mergea nada sin revisión previa.
- **Prioridad real:** Si una tarea bloquea a Front, se atiende antes que cualquier mejora secundaria.

## Plan de contingencia
### Si el 25 no está listo Auth
- Alex y Eduardo fijan al menos estructura mínima y respuesta esperada.
- Pablo cierra BE-06 para que Front pueda trabajar con contrato API aunque falte remate técnico.
- El día 26 se prioriza totalmente Auth sobre cualquier tarea secundaria.

### Si el 26 falla integración Auth + RBAC
- Se deja RBAC en versión mínima de protección base.
- Se prioriza que login, sesión y rutas privadas esenciales funcionen.
- Se aplazan refinados por rol más finos si aún no son críticos para el sprint.

### Si no da tiempo a pulir toda la base de datos
- Se corrigen primero inconsistencias críticas de migraciones, nombres y relaciones.
- Se dejan para el siguiente sprint ajustes no bloqueantes de estructura.
- Se documentan claramente las decisiones pendientes.

## Evidencias de cierre
- Repositorio estructurado y limpio, con flujo Git respetado por todos.
- PRs asociadas a Login y estructura base revisadas y mergeadas.
- Endpoints de autenticación disponibles y funcionales en base.
- Protección inicial de rutas aplicada.
- Rutas y controladores stub creados para módulos principales.
- Pruebas unitarias o funcionales mínimas de Auth/RBAC en verde.
- Documentación técnica de endpoints de autenticación generada y accesible para Front.
- Base de datos inicial revisada y pulida en su parte crítica.
- Contrato API común definido para facilitar integración con Front.

## Cierre del sprint / criterio de validación
El Sprint 01 de backend se considera válido cuando la base técnica del ERP en Laravel quede operativa: login/logout, control de sesión, protección inicial de rutas, estructura mínima de rutas y controladores, base documental de integración con Front y una primera revisión seria de la base de datos y arquitectura. No se exige aún lógica de negocio completa por módulo, pero sí un backend ordenado, seguro, integrable y preparado para crecer en el siguiente sprint.
