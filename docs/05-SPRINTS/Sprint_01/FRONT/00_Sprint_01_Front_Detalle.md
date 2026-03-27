# Sprint 01 · FRONT · Plan Unificado

**Periodo:** 23/03/2026 - 26/03/2026  
**Foco:** Arranque + base visible (React)

## Objetivo frontend
Establecer la base técnica del frontend en React y dejar montada la primera versión visible del ERP. El usuario debe poder loguearse, ver el panel principal, moverse por el menú lateral aunque las secciones aún estén vacías y dejar preparada la base global de tema, multilenguaje y coherencia visual para que el frontend crezca de forma ordenada.

## Fechas reales de ejecución restantes
- 25/03/2026
- 26/03/2026

## Asignación de tareas
| ID | Tarea | Responsable | Prioridad | Estado | Bloqueos |
|---|---|---|---|---|---|
| FE-00 | Crear el proyecto: instalar React con Vite y organizar las carpetas del código. | D. Bascope | Alta | Doing | - |
| FE-01 | Pantalla de Login: formulario funcional para entrar a la aplicación. | D. Bascope | Alta | Doing | - |
| FE-02 | Colores y Estilos: configurar el rojo `#E8000D`, grises y variables CSS globales de Ciete. | D. Bascope | Alta | ToDo | - |
| FE-03 | Menú Lateral: barra de navegación izquierda con los botones de cada módulo. | Jhon | Alta | ToDo | Depende FE-00 |
| FE-04 | Pantalla Inicial: el panel central (Dashboard) que aparece tras el login. | Jimmy | Alta | ToDo | Depende FE-00 |
| FE-05 | Componentes Básicos: crear un botón y una tabla modelo reutilizables. | Jhon | Media | ToDo | Depende FE-02 |
| FE-06 | Navegación: configurar las rutas para que el menú cambie de pantalla. | Jhon | Alta | ToDo | Depende FE-03 |
| FE-07 | Revisión de Diseño: supervisión y aprobación del aspecto visual y de la coherencia general. | D. López | Alta | ToDo | Depende FE-01, FE-03, FE-04 |
| FE-08 | Fotos y Manual: capturas del login, dashboard, menú y navegación para documentación. | Carlos | Media | ToDo | Depende FE-01, FE-04, FE-06 |
| FE-09 | Modo Claro/Oscuro: base global de tema con variables CSS, cambio de tema y persistencia local. | Pablo Sevillano | Alta | ToDo | Depende FE-02 |
| FE-10 | Multilenguaje Base: infraestructura inicial de idiomas en React aplicada en login, menú y dashboard. | Pablo Sevillano | Media-Alta | ToDo | Depende FE-00 |
| FE-11 | Microinteracciones Ligeras: transiciones, hover, focus y feedback visual sin cargar el sistema. | Pablo Sevillano | Media | ToDo | Depende FE-02, FE-03, FE-04 |
| FE-12 | Revisión Transversal Front: consistencia visual y estructural entre login, layout, dashboard y componentes. | Pablo Sevillano | Alta | ToDo | Depende FE-01, FE-03, FE-04, FE-05, FE-06 |
| FE-13 | Criterio Base de UI: base compartida para colores, espaciados, botones, tablas, estados y comportamiento visual. | Pablo Sevillano | Alta | ToDo | Depende FE-02 |

## Reparto por fechas

### 25/03/2026 · Día 1
**Objetivo del día:** Dejar montada toda la base estructural del frontend para que el día 26 sea integrar, revisar, corregir y documentar.

**Entregables del día:**
- Proyecto React operativo
- Variables CSS globales
- Login visible y usable
- Sidebar visible
- Dashboard base visible
- Rutas mínimas funcionando
- Base de tema claro/oscuro iniciada
- Base de multilenguaje iniciada

| Responsable | Tareas del 25/03/2026 | Objetivo del día |
|---|---|---|
| D. Bascope | FE-00 + FE-01 + FE-02 | Proyecto arrancado, login montado y colores/variables globales preparados. |
| Jhon | FE-03 | Menú lateral construido sobre la estructura creada. |
| Jimmy | FE-04 | Dashboard inicial visible y alineado con el layout. |
| Pablo Sevillano | FE-09 + FE-10 + FE-13 | Base de tema, multilenguaje y criterio visual común. |
| D. López | FE-07 (revisión parcial) | Supervisar consistencia entre login, sidebar y dashboard. |
| Carlos | Preparación documental | Organizar carpeta/evidencias y plantilla de capturas. |

**Orden recomendado del día:**
- Primera mitad: D. Bascope (FE-00 y FE-02), Pablo (FE-13), Jhon y Jimmy esperan base estable.
- Segunda mitad: D. Bascope (FE-01), Jhon (FE-03), Jimmy (FE-04), Pablo (FE-09 y FE-10), D. López (revisión parcial).

**Cierre obligatorio 25/03:**
- Login montado
- Dashboard visible
- Sidebar visible
- Colores globales definidos
- Estructura React estable
- Primera base real de tema e idioma

### 26/03/2026 · Día 2
**Objetivo del día:** Integrar todo, rematar navegación, pulir coherencia visual, añadir vida ligera al sistema y cerrar evidencias.

**Entregables del día:**
- Navegación entre vistas
- Componentes base reutilizables
- Tema claro/oscuro funcional en base
- Multilenguaje funcional en base
- Microinteracciones ligeras
- Revisión visual final
- Capturas y validación del sprint

| Responsable | Tareas del 26/03/2026 | Objetivo del día |
|---|---|---|
| Jhon | FE-05 + FE-06 | Botón y tabla base creados, navegación entre pantallas cerrada. |
| Pablo Sevillano | FE-11 + FE-12 + remate FE-09/FE-10 | Microinteracciones, coherencia global y cierre tema/idioma en login, menú y dashboard. |
| D. Bascope | Soporte a integración | Corregir bloqueos de login, estilos globales o estructura. |
| Jimmy | Ajustes del dashboard | Adaptar dashboard a rutas, tema, idioma y revisión visual. |
| D. López | FE-07 (validación final) | Revisar y aprobar visualmente el resultado final. |
| Carlos | FE-08 | Capturas definitivas para documentación. |

**Orden recomendado del día:**
- Primera mitad: Jhon (FE-06), Pablo (cierre FE-09/FE-10), Jimmy (ajustes dashboard), D. Bascope (soporte), D. López (revisión intermedia).
- Segunda mitad: Jhon (FE-05), Pablo (FE-11 y FE-12), Carlos (FE-08), D. López (validación final).

**Cierre obligatorio 26/03:**
- Navegación funcional
- Login -> dashboard operativo
- Sidebar estable
- Tema claro/oscuro base
- Multilenguaje base
- Componentes reutilizables mínimos
- Revisión final hecha
- Documentación visual preparada

## Prioridad real por orden
| Prioridad | Tareas |
|---|---|
| Crítica | FE-00, FE-01, FE-02, FE-03, FE-04, FE-06 |
| Alta | FE-07, FE-09, FE-12, FE-13 |
| Media | FE-05, FE-08, FE-10 |
| Secundaria si falta tiempo | FE-11 |

## Qué no se puede quedar fuera
**Mínimo del sprint válido:**
- FE-00
- FE-01
- FE-02
- FE-03
- FE-04
- FE-06
- FE-07
- FE-08

**Mínimo de tus tareas:**
- FE-09
- FE-12
- FE-13

FE-10 puede quedar como base mínima y FE-11 puede quedar en versión ligera si falta tiempo.

## Reglas obligatorias
- **Tecnología:** El proyecto se desarrolla en React, eliminando Blade en frontend.
- **Estilos:** No usar colores fijos (hardcoded); usar variables CSS.
- **Flujo:** El Login debe ser la puerta de entrada obligatoria al Dashboard.
- **Coherencia:** Todo componente nuevo debe respetar la línea visual común del ERP.
- **Ligereza:** Las mejoras visuales deben aportar vida al sistema sin cargarlo ni perjudicar rendimiento.
- **Escalabilidad:** Tema claro/oscuro y multilenguaje deben montarse como base reutilizable.

## Plan de contingencia
### Si el 25 no está listo el sidebar
- Jhon cierra la estructura mínima del menú.
- Pablo y D. Bascope ayudan a integrar visualmente.
- Se aplazan detalles estéticos del menú.

### Si el 26 falla navegación o integración
- FE-05 baja de prioridad.
- FE-11 se reduce al mínimo.
- Se prioriza cerrar rutas, login, dashboard y revisión visual.

### Si no da tiempo con multilenguaje
- Dejar solo login.
- Dejar solo sidebar.
- Dejar solo dashboard.
- No traducir módulos vacíos adicionales.

## Evidencias de cierre
- Proyecto arrancando correctamente con `npm run dev`.
- Login funcional con redirección al panel principal.
- Menú lateral navegando entre secciones.
- Colores corporativos visibles en elementos principales.
- Dashboard inicial visible tras el acceso.
- Base funcional de modo claro/oscuro.
- Base funcional de multilenguaje en login, menú y dashboard.
- Componentes básicos reutilizables creados.
- Microinteracciones ligeras y coherentes.
- PRs revisados y aprobados por D. López.
- Documentación con capturas actualizada por Carlos.

## Cierre del sprint / criterio de validación
El Sprint 01 de frontend se considera válido cuando la base visible del ERP en React quede operativa: login, entrada a dashboard, navegación lateral entre módulos vacíos, identidad visual aplicada y base transversal funcional para tema claro/oscuro, multilenguaje y consistencia visual general. No se exige conexión completa con backend, pero sí un frontend estable, navegable y listo para el siguiente sprint.
