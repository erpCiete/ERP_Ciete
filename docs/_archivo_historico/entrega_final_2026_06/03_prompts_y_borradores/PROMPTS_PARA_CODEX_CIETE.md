# Prompts para Codex - CIETE

# PROMPT 3 — Recuperación contraseña, tickets y sidebar Trabajos

Ejecuta estos tres bloques en orden, sin mezclar responsabilidades.

NO toques producción.
NO hagas deploy.
NO hagas commit.
NO hagas push.
NO uses migrate:fresh, migrate:refresh ni db:wipe.
NO borres datos.
NO limpies worktree.
NO reviertas cambios ajenos.

---

# BLOQUE A — Revisar y poner en funcionamiento `/forgot-password`

## Objetivo

Revisar que la recuperación de contraseña funcione correctamente en el ERP CIETE.

Ruta esperada:

`/forgot-password`

Debe funcionar en local/demo sin enviar emails reales a usuarios externos.

## FASE A1 — Revisar stack actual

Revisar:

- rutas de autenticación;
- Laravel Breeze/Fortify/Jetstream si aplica;
- vistas React/Inertia de auth;
- controladores de password reset;
- configuración `MAIL_*`;
- tabla `password_reset_tokens` o equivalente;
- tests existentes de autenticación.

Buscar:

- `/forgot-password`;
- `/reset-password`;
- `ForgotPassword`;
- `ResetPassword`;
- `PasswordResetLink`;
- `password.email`;
- `password.update`.

## FASE A2 — Validar UI

Abrir navegador:

`/forgot-password`

Comprobar:

- carga sin error;
- diseño coherente con el ERP;
- textos en español;
- campo email;
- botón claro;
- mensajes de éxito/error;
- enlaces de vuelta al login.

## FASE A3 — Validar backend local

No enviar email real.

Usar uno de estos enfoques:

- `Mail::fake()` en tests;
- mail log;
- Mailpit/local si ya está configurado;
- driver `log` en local si procede.

Validar:

- usuario existente recibe enlace;
- usuario inexistente no revela información sensible;
- token se genera;
- ruta `/reset-password/{token}` carga;
- cambio de contraseña funciona;
- token usado no queda reutilizable;
- validaciones de contraseña.

## FASE A4 — Implementar hotfix si falta algo

Si `/forgot-password` no funciona:

- arreglar ruta;
- crear o ajustar pantalla React/Inertia;
- usar controlador estándar de Laravel si existe;
- no inventar sistema paralelo;
- mantener seguridad estándar.

## FASE A5 — Tests

Ejecutar o crear tests mínimos:

- solicitar link de recuperación;
- resetear contraseña con token válido;
- rechazar token inválido;
- validar email obligatorio;
- validar contraseña confirmada.

Ejecutar:

```bat
cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Password
cmd.exe /C npm run build
git diff --check
```

Si no hay tests con `Password`, crear uno específico si es razonable.

## Respuesta parcial Bloque A

Incluir:

- ruta funcionando;
- email local/log;
- tests;
- pendientes.

---

# BLOQUE B — Revisar sistema de tickets

## Objetivo

Revisar que el sistema de tickets esté listo y preparado para uso interno.

No desarrollar un sistema nuevo si ya existe.
Completar solo huecos razonables.

## FASE B1 — Inventario

Buscar:

- modelos de tickets;
- migraciones;
- controladores;
- rutas;
- páginas React;
- permisos;
- sidebar;
- tests.

Buscar términos:

- `Ticket`
- `tickets`
- `soporte`
- `support`
- `incidencia`
- `Issue`

## FASE B2 — Flujo esperado

El sistema debe soportar, como mínimo:

- crear ticket;
- ver listado;
- ver detalle;
- editar estado;
- prioridad;
- categoría/tipo si existe;
- asignar a técnico/admin si existe;
- comentarios o seguimiento si existe;
- cerrar ticket;
- auditoría o historial básico si existe;
- permisos por rol.

Estados recomendados:

- `abierto`
- `en_progreso`
- `pendiente_usuario`
- `resuelto`
- `cerrado`

Prioridades recomendadas:

- `baja`
- `media`
- `alta`
- `critica`

No cambiar nombres si ya existen y están integrados.

## FASE B3 — UI

Validar en navegador:

- sidebar muestra Tickets donde corresponda;
- Dirección puede crear/consultar si corresponde;
- técnico/admin puede gestionar;
- ejecución/contabilidad según permisos;
- listado limpio;
- filtros;
- detalle;
- comentarios;
- cambio de estado;
- cierre.

## FASE B4 — Backend/permisos

Revisar:

- políticas/permisos;
- validaciones requests;
- no acceder a tickets ajenos si no corresponde;
- no cerrar sin permiso;
- no asignar sin permiso;
- protección 403 donde corresponda.

## FASE B5 — Preparar lo mínimo que falte

Si falta algo pequeño:

- añadir ruta;
- añadir permiso;
- corregir sidebar;
- corregir formulario;
- corregir test.

Si falta el sistema entero:

- NO construirlo entero en este prompt;
- documentar estado real;
- proponer bloque de implementación aparte.

## FASE B6 — Tests

Ejecutar:

```bat
cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Ticket
cmd.exe /C npm run build
git diff --check
```

Si no existen tests de Ticket y el módulo existe:

- crear test mínimo:
    - usuario autorizado crea ticket;
    - usuario no autorizado recibe 403;
    - técnico cambia estado;
    - comentario se guarda si existe.

## Respuesta parcial Bloque B

Incluir:

- qué existe;
- qué funciona;
- qué se corrigió;
- qué falta;
- si está listo para demo.

---

# BLOQUE C — Ocultar sidebar en ventana Trabajos

## Objetivo

Cuando el usuario esté en `Trabajos`, la sidebar debe ocultarse automáticamente y comportarse como en modo responsive/tablet/móvil, para dejar más ancho útil a la tabla.

Ruta principal:

`/trabajos`

No aplicar globalmente a todo el ERP.
Solo a la pantalla `Trabajos`, salvo que haya una configuración limpia por layout.

## Comportamiento deseado

En `/trabajos`:

- sidebar cerrada/oculta por defecto;
- contenido usa más ancho;
- botón para abrir sidebar si el usuario lo necesita;
- comportamiento parecido al responsive mobile/tablet;
- no debe romper header;
- no debe crear scroll horizontal global innecesario;
- la tabla mantiene su scroll horizontal interno;
- si el usuario abre sidebar, debe poder cerrarla;
- al salir de Trabajos, el resto del ERP vuelve a comportamiento normal.

## FASE C1 — Revisar layout

Revisar:

- `AuthenticatedLayout`;
- sidebar component;
- responsive state;
- props de layout;
- `resources/js/Pages/Trabajos/Index.jsx`;
- clases `contentWidthClass`;
- CSS de `.ciete-page-operations`.

## FASE C2 — Implementación recomendada

Preferencia:

- añadir prop al layout:
    - `hideSidebarByDefault`
    - o `operationsMode`
    - o nombre equivalente.

- usarla solo en `Trabajos/Index.jsx`.
- no duplicar sidebar.
- reutilizar lógica responsive existente.
- añadir botón compacto tipo:
    - `Menú`
    - icono hamburguesa
    - posición superior izquierda o junto a cabecera.

Evitar:

- hacks globales;
- ocultar sidebar por CSS sin control;
- romper navegación en otras pantallas;
- esconder sidebar sin forma de recuperarla.

## FASE C3 — Validación visual

Validar en navegador:

- `/trabajos` desktop 1366/1440:
    - sidebar oculta;
    - tabla gana ancho;
    - botón menú visible;
    - sidebar se abre/cierra;
    - no scroll horizontal global.

- `/maestros`:
    - sidebar normal.

- `/pedidos`:
    - sidebar normal.

- responsive:
    - mantiene comportamiento móvil actual.

## FASE C4 — Tests/build

Ejecutar:

```bat
cmd.exe /C npm run build
git diff --check
```

Si hay tests frontend/lint disponibles, ejecutarlos.
No crear test complejo si el proyecto no lo tiene.

## Documentación común

Actualizar:

`docs/02_CLIENTE/tareasComparar.md`

Añadir entradas:

## 2026-06-02 — Recuperación contraseña revisada

## 2026-06-02 — Sistema de tickets revisado

## 2026-06-02 — Sidebar oculta en Trabajos

Cada entrada debe incluir:

- objetivo;
- archivos tocados;
- validaciones;
- resultado;
- pendientes.

## Respuesta final obligatoria

# Revisión auth, tickets y layout Trabajos

## Resultado general

## Recuperación contraseña

### Estado inicial

### Cambios aplicados

### Validaciones

### Pendientes

## Sistema tickets

### Estado inicial

### Cambios aplicados

### Validaciones

### Pendientes

## Sidebar en Trabajos

### Estado inicial

### Cambios aplicados

### Validación visual

### Pendientes

## Archivos modificados

## Tests/build

## Riesgos

## Siguiente paso recomendado

Si alguno de los tres bloques requiere desarrollo mayor, no lo improvises. Documenta:

- qué falta;
- qué sí quedó revisado;
- qué prompt específico debe ejecutarse después.

## Notas

- Si un prompt sustituye a otro, indícalo explícitamente.
- Si un prompt depende de documentos concretos, enlázalos o nómbralos aquí.
