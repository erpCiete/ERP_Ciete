# Instrucciones y forma de trabajo Git

Responsable: Eduardo Jiménez  
Objetivo: definir y mantener las reglas de trabajo en Git para todo el equipo.

## Flujo de Trabajo: Nuestro Tablero
Para que todos tengamos visibilidad total y evitemos cuellos de botella (especialmente en integraciones con Frontend), usaremos un tablero unificado. Intentad actualizar el estado de las tareas diariamente.

El ciclo de vida de cada tarea pasará por las siguientes columnas:
- **Backlog**: Tareas identificadas y registradas, pero que aún no están priorizadas o refinadas para este ciclo.
- **Ready**: La tarea está perfectamente definida, documentada y lista para desarrollo. Si terminas tu tarea actual, coge una de aquí.
- **In Progress**: Tareas en las que se está trabajando activamente. Intentemos no tener más de 2 tareas por persona en esta columna al mismo tiempo.
- **In Review**: Se abrió una Pull Request (PR) siguiendo la plantilla obligatoria. El código está esperando revisión de pares o aprobación final.
- **Testing**: El PR fue aprobado a nivel de código y está validándose en local o entorno de pruebas para verificar criterios de aceptación y que no rompe nada.
- **Done**: Código mergeado en la rama principal, probado y documentado. Tarea finalizada al 100%.

## 1. Flujo de ramas
- Rama principal de integración diaria: `develop`.
- Rama de release/estable: `main`.
- Convención de ramas de feature: `feature/<area>-<id>-<descripcion-corta>`.
- Convención de ramas de fix/hotfix: `fix/<area>-<id>-<descripcion-corta>` y `hotfix/<descripcion-corta>`.
- Todo trabajo nuevo parte de `develop` salvo hotfix urgente en producción.

## 2. Reglas de commits
- Formato recomendado: `<tipo>(<area>): <mensaje corto>`.
- Tipos permitidos: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`.
- Idioma: español técnico, claro y breve.
- Incluir referencia de tarea cuando aplique: `FE-xx` o `BE-xx`.
- Ejemplos válidos:
- `feat(front): FE-06 rutas base entre login y dashboard`
- `fix(back): BE-02 corrige middleware RBAC en rutas privadas`
- `docs(git): actualiza guía de trabajo y tablero`

## 3. Pull Requests
- Cuándo abrir PR: al cerrar una tarea funcional o un bloque coherente y probado.
- Base de PR: `develop` para trabajo normal, `main` solo en hotfix de producción.
- Plantilla mínima obligatoria:
- Objetivo y contexto.
- Tareas/IDs cubiertos.
- Cambios principales.
- Cómo probar.
- Riesgos o impactos.
- Evidencias (capturas/logs) si aplica.
- Revisión mínima:
- 1 revisor del área.
- 1 revisor adicional si toca seguridad, auth o base de datos.
- Criterios para merge:
- CI o pruebas locales en verde.
- Sin conflictos.
- Checklist completa.
- Aprobaciones requeridas.

## 4. Integración entre BACK y FRONT
- Orden recomendado de integración:
- Definir primero contrato API.
- Integrar Auth (login/logout + sesión).
- Integrar rutas protegidas.
- Ajustar respuestas y errores comunes.
- Dependencias entre subequipos:
- Front no debe bloquearse esperando lógica completa; Back entrega contratos y stubs temprano.
- Back prioriza endpoints y formatos que desbloqueen navegación e integración de Front.
- Manejo de conflictos:
- Resolver en la rama de trabajo antes de pedir merge.
- Si hay conflicto cruzado BACK/FRONT, se revisa en llamada corta y se documenta decisión en el PR.

## 5. Buenas prácticas obligatorias
- Actualizar rama local con `develop` antes de abrir PR.
- No hacer merge sin revisión.
- No subir secretos, tokens ni credenciales.
- No subir binarios innecesarios o archivos temporales.
- Referenciar sprint/tarea en commits y PR.
- Mantener PRs pequeñas y enfocadas por objetivo.

## 6. Checklist antes de merge
- [ ] Código probado en entorno local.
- [ ] Cambios documentados en el sprint.
- [ ] Sin conflictos pendientes.
- [ ] PR con plantilla completa.
- [ ] Aprobaciones requeridas conseguidas.
- [ ] Riesgos y alcance del cambio claros.

## 7. Registro de cambios de esta guía
- Fecha: 24/03/2026
- Cambio: se define flujo real de tablero, ramas, commits, PR, integración BACK/FRONT y checklist operativa.
- Responsable: Eduardo Jiménez
