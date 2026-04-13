# Bitacora FRONT ┬À Daniel Lopez

## General
### Objetivo de la semana
- Levantar estructura base Clientes v1 con API real
- Crear hook useClientes que encapsule Axios
- Implementar rutas /clientes en web.php
- Garantizar flujo completo: listado ÔåÆ crear ÔåÆ editar ÔåÆ eliminar con badges visibles

## 2026-04-07
### Objetivo del dia
- Revisar plan de ejecuci├│n detallado
- Preparar estructura de componentes y hooks
- Coordinar con equipo (Edu backend, D. Bascope, Pablo)

### Tareas realizadas
- Lectura plan_ejecucion_detallado.md completo
- Coordinaci├│n Discord sobre entregables del mi├®rcoles
- Validaci├│n estructura carpetas /Components/ui/ y /hooks/

### Archivos tocados
- docs/05-SPRINTS/Sprint_02/Sprint_02_FRONT_Plan_Ejecucion.md (lectura)

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- Comenzar por componentes base (badges) antes de p├íginas principales
- Hook useClientes antes de armar Index.jsx
- Rutas web.php en paralelo con creaci├│n de componentes

### Pendiente para ma├▒ana
- F1: Clientes Index.jsx y Form.jsx
- F3: Hook useClientes.js conectado al mock
- F4: Rutas /clientes en web.php y menu lateral

### Handoff
- Dani ÔåÆ equipo FRONT: arquitectura y plan coordinado, listo para ejecutar mi├®rcoles

## 2026-04-08
### Objetivo del dia
- F1: Crear Clientes/Index.jsx y Form.jsx con estructura completa
- F3: Crear hook useClientes.js conectado al mock de Edu
- F4: Habilitar rutas /clientes en web.php y menu lateral
- Asegurar listado de clientes visible con badge de contexto y datos del mock

### Tareas realizadas
- Ô£à F1: Clientes/Index.jsx creado con tabla (Nombre, Contexto, Direcci├│n, Estado, Acciones)
- Ô£à F1: Index.jsx incluye estados loading (spinner), empty ("No hay clientes"), error ("No se pudo cargar")
- Ô£à F1: BadgeCliente integrado en cada fila (visible, obligatorio por plan)
- Ô£à F1: Clientes/Form.jsx creado con campos Nombre, Contexto (select), Direcci├│n, Estado
- Ô£à F1: Validaci├│n visible en cliente (campos requeridos marcados en rojo)
- Ô£à F1: Selector Contexto sin preselecci├│n (usuario siempre elige, seg├║n regla cr├¡tica)
- Ô£à F3: Hook useClientes.js creado - encapsula GET a /api/v1/clientes
- Ô£à F3: Hook retorna { data, loading, error, fetch() } siguiendo patr├│n React est├índar
- Ô£à F3: Estructura esperada del mock validada con Edu (data, meta, errors)
- Ô£à F3: Manejo de errores 422 preparado (estructura lista para Form.jsx)
- Ô£à F4: Rutas /clientes a├▒adidas en web.php (coordinado con Pablo - CONTROL ARCHIVO)
- Ô£à F4: Enlace /clientes a├▒adido en AuthenticatedLayout.jsx sidebar
- Ô£à F4: Claves i18n agregadas: "clientes" en es.js y "clients" en en.js
- Ô£à npm run build pasa sin errores

### Archivos tocados
- resources/js/Pages/Clientes/Index.jsx (creado)
- resources/js/Pages/Clientes/Form.jsx (creado)
- resources/js/hooks/useClientes.js (creado)
- routes/web.php (editado - a├▒adidas rutas /clientes)
- resources/js/Layouts/AuthenticatedLayout.jsx (editado - link sidebar)
- resources/js/i18n/locales/es.js (editado - clave "clientes")
- resources/js/i18n/locales/en.js (editado - clave "clients")

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 09:30 | BadgeCliente no recib├¡a prop desde Index.jsx | Bajo | Verificado destructuring en Index, confirmado flujo correcto | Cerrado |
| 14:15 | Mock endpoint no accesible desde local | Medio | Coordinado con Edu, obtenida URL exacta del mock en Discord | Cerrado |
| 15:45 | npm run build fallaba por import cyclic en useClientes | Alto | Reorganizado estructura hook, movidas dependencias a nivel correcto | Cerrado |

### Decisiones tomadas
- BadgeCliente integrado obligatoriamente en Index (requisito plan, visible en cada fila)
- useClientes retorna datos completos sin transformaci├│n (API-first, confianza en backend)
- Selector Contexto sin valor por defecto (regla cr├¡tica del plan: usuario siempre elige)
- Estructura de validaci├│n 422 en Form.jsx preparada para jueves (campo a campo, no alert gen├®rico)
- Rutas web.php bajo CONTROL ARCHIVO (m├íximo 15 min, coordinado con Pablo)

### Pendiente para ma├▒ana
- F6: Completar flujo POST real (actualmente Form.jsx apunta a mock)
- F6: Implementar PUT para editar cliente existente
- F6: Modal de confirmaci├│n antes de DELETE
- F6: Cargar datos en Form.jsx para editar (GET por {id})
- F6: Paginaci├│n en Index.jsx (meta.current_page, meta.last_page)
- Coordinar con Jhon/Jimmy en Estaciones (usar mismo patr├│n useClientes)

### Handoff
- Dani ÔåÆ D. Bascope: revisar flujo UI en Form.jsx, estructura lista para POST real ma├▒ana
- Dani ÔåÆ Pablo: web.php modificado y probado, listo para merge
- Dani ÔåÆ Jhon: patr├│n useClientes + Index.jsx + Form.jsx disponible para copiar en Estaciones
- Dani ÔåÆ Edu: confirmada estructura mock, conectada exitosamente

## 2026-04-09
### Objetivo del dia
- F6: Completar flujo POST/PUT/DELETE real contra /api/v1/clientes (no mock)
- Form.jsx conectado a POST real y redirige al listado tras ├®xito
- Implementar edici├│n: GET/{id} ÔåÆ Form precompletado ÔåÆ PUT
- Modal de confirmaci├│n antes de DELETE
- Errores 422 capturados y mostrados campo a campo
- Validar Clientes v1 completo y funcional

### Tareas realizadas
- Ô£à F6: useClientes.js extendido con POST real a /api/v1/clientes
- Ô£à F6: useClientes.js extendido con PUT real a /api/v1/clientes/{id}
- Ô£à F6: useClientes.js extendido con DELETE real a /api/v1/clientes/{id}
- Ô£à F6: Form.jsx conectado a POST real - submit ejecuta POST y redirige a /clientes tras ├®xito
- Ô£à F6: Errores 422 capturados en catch de Axios y mostrados campo a campo (no alert gen├®rico)
- Ô£à F6: Cada error de validaci├│n Laravel aparece bajo el input correcto
- Ô£à F6: Paginaci├│n implementada en Index.jsx (lee meta.current_page y meta.last_page del servidor)
- Ô£à F6: Modal de confirmaci├│n agregado antes de ejecutar DELETE (Yes/No buttons)
- Ô£à F6: Flujo editar implementado: bot├│n editar ÔåÆ GET /{id} ÔåÆ Form precompletado ÔåÆ PUT guardar
- Ô£à F6: Listado se refresca autom├íticamente tras POST/PUT/DELETE exitosos
- Ô£à F6: Boton "Nuevo cliente" abre Form.jsx limpio
- Ô£à F6: Redirecci├│n autom├ítica a /clientes tras crear o editar exitosamente
- Ô£à npm run build pasa sin errores - versi├│n lista para producci├│n

### Archivos tocados
- resources/js/hooks/useClientes.js (modificado - POST, PUT, DELETE operacionales)
- resources/js/Pages/Clientes/Index.jsx (modificado - paginaci├│n, modal delete, refresh autom├ítico)
- resources/js/Pages/Clientes/Form.jsx (modificado - POST/PUT real, manejo errores 422 campo a campo)

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:15 | Errores 422 no se destructuraban correctamente | Alto | Debugueada respuesta error.response.data.errors en catch, ajustado destructuring | Cerrado |
| 11:45 | Modal delete ejecutaba DELETE sin esperar confirmaci├│n | Medio | Implementado modal con Yes/No, preventDefault en form submission | Cerrado |
| 13:30 | Paginaci├│n no reseteaba a p├ígina 1 tras crear cliente | Medio | Agregada l├│gica de reset page=1 despu├®s de POST exitoso | Cerrado |
| 15:00 | Badge de contexto desaparec├¡a despu├®s de editar cliente | Bajo | Confirmado que API retorna contexto, flujo de estado correcto, cerrado | Cerrado |

### Decisiones tomadas
- Errores 422: destructurado response.data.errors, iterado sobre keys, mostrado mensaje bajo input exacto (no alert)
- Modal delete: confirmaci├│n expl├¡cita requerida antes de enviar DELETE request (mejor UX)
- Paginaci├│n: reset autom├ítico a page 1 tras crear cliente nuevo (usuario ve lo que acaba de crear)
- Edici├│n: GET /{id} antes de mostrar Form.jsx, todos los datos precargados desde API (no localStorage)
- Validaci├│n: sin hardcodeo de datos. Todo viene de la API en tiempo real (confianza en backend)
- Flujo coherente: POST ÔåÆ redirect ÔåÆ GET list ÔåÆ mostrar en tabla con badge visible

### Pendiente para ma├▒ana
- F7: Estaciones (Jhon + Jimmy - pueden usar mismo patr├│n que Clientes)
- F8: Verificar todos los estados de UI (loading, empty, error) en Estaciones
- F9: Revisar menu/rutas con Pablo si hay ajustes pendientes
- Q1: QA funcional - verificar checklist del plan contra Clientes v1
- Documentaci├│n cierre sprint con lecciones aprendidas

### Handoff
- Dani ÔåÆ D. Bascope: Clientes v1 COMPLETO y operativo. POST/PUT/DELETE real funcionando. Listo para demo viernes.
- Dani ÔåÆ Jhon/Jimmy: useEstaciones debe ser copy-paste exacto de useClientes (solo cambiar endpoint /api/v1/estaciones). Structure Index.jsx y Form.jsx id├®ntica a Clientes.
- Dani ÔåÆ Pablo: Clientes v1 pas├│ npm run build sin errores. Listo para merge a develop.
- Dani ÔåÆ Carlos Daniel: Bit├ícoras del equipo FRONT al d├¡a. Clientes en GO. Status: ON TRACK para cierre viernes.

## 2026-04-10
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

### Pendiente para ma├▒ana
- 

### Handoff
-
