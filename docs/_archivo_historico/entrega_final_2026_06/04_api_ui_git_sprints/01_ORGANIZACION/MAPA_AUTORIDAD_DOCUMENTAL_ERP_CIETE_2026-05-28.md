# Mapa de autoridad documental ERP CIETE — 2026-05-28

## 1. Objetivo

Este documento fija que debe usar Codex, cualquier agente tecnico y el equipo como fuente principal antes de implementar cambios en el ERP CIETE. Su objetivo es evitar que planes antiguos, tutoriales de demo, sprints cerrados o documentos repetidos se mezclen con la nueva ola de trabajo.

La documentacion historica se conserva por trazabilidad, pero no debe dirigir implementacion si contradice la reunion de Cesar/Amaya del 19/05/2026, el estado funcional nuevo o el codigo real.

## 2. Regla de autoridad actual

Orden recomendado:

1. Codigo real actual.
2. `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
3. `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
4. `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md`
5. `docs/02_CLIENTE/tareasComparar.md`
6. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
7. Fases B1/B2 de validacion real.
8. Auditorias recientes 2026-05-17 a 2026-05-19.
9. Documentacion historica solo como contexto.
10. Sprints antiguos solo como historial, nunca como instruccion vigente.

Si hay contradiccion, prevalece el codigo real para saber que existe y prevalecen la reunion exhaustiva, estado funcional y plan nuevo para saber que debe hacerse en la nueva fase.

## 3. Documentos de autoridad alta

| Ruta | Motivo | Cuando usar | Riesgo |
| --- | --- | --- | --- |
| `app/*`, `resources/js/*`, `routes/*`, `database/*` | Prueba real de lo que existe | Antes de afirmar avance o tocar funcionalidad | Puede no reflejar aun decisiones nuevas pendientes |
| `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md` | Vaciado completo del DOCX de reunion Cesar/Amaya | Fuente principal funcional de esta fase | Transcripcion con ruido; usar las secciones interpretadas y referencias de linea |
| `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md` | Foto de estado actual cruzando plan, backlog y codigo | Para decidir que esta completo, parcial o pendiente | Requiere contrastar con codigo antes de cerrar tareas |
| `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md` | Plan tecnico de nueva ola y bloques A-G | Para ordenar implementacion incremental | El plan de 8 dias es amplio; usar tambien el plan corto |
| `docs/02_CLIENTE/PLAN_CORTO_EJECUCION_REUNION_CESAR_AMAYA_2026-05-28.md` | Traduccion corta y accionable de la reunion | Para prompts por bloque y ejecucion diaria | Debe actualizarse si Pablo cambia prioridades |
| `docs/02_CLIENTE/tareasComparar.md` | Backlog maestro vivo y trazabilidad | Para comprobar historial de tareas y evidencias | Mezcla olas antiguas con la nueva; no usar solo |
| `docs/02_CLIENTE/reunionCieteCompletaFormato.txt` | Transcripcion formateada previa | Para contraste con la reunion fuente | Menos estructurada que el vaciado exhaustivo nuevo |

## 4. Documentos secundarios utiles

| Ruta | Utilidad | Limitacion | Como contrastarlo |
| --- | --- | --- | --- |
| `docs/02_CLIENTE/FASE_B1_IMPORTACION_REAL_EXCEL_2026-05-18.md` | Evidencia de importacion real y fuentes Excel | No define la nueva ola funcional | Contrastar con codigo, B2 y plan corto |
| `docs/02_CLIENTE/FASE_B2_VALIDACION_FUNCIONAL_COMPLETA_POST_IMPORTACION_2026-05-19.md` | Validacion funcional post-importacion | Puede mezclar demo/presentacion anterior | Usar como evidencia, no como plan |
| `docs/02_CLIENTE/B1_1_AJUSTES_VISUALES_Y_AUDITORIA_COBERTURA_DATOS_2026-05-19.md` | Evidencia tecnica de ajustes y auditoria de cobertura | Pertenece a fase anterior | Contrastar con estado funcional 2026-05-28 |
| `docs/02_CLIENTE/B1_2_REVISION_GLOBAL_TABLAS_CASOS_DEMO_Y_VERSION_2026-05-19.md` | Revision global de tablas y demo | No sustituye reunion Cesar/Amaya | Usar como evidencia de validacion |
| `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md` | Alineacion funcional previa a nueva ola | Anterior a reunion del 19/05 y plan nuevo | Usar solo si no contradice fuentes altas |
| `docs/02_CLIENTE/AUDITORIA_COMPLETITUD_MAESTROS_P1-14_2026-05-17.md` | Completitud de maestros por contexto | Foto anterior a importacion real | Contrastar con B1/B2 |
| `docs/02_CLIENTE/AUDITORIA_COMPLETITUD_MAESTROS_P1-14_REAL_POST_IMPORTACION_2026-05-18.md` | Completitud tras importacion real | Evidencia tecnica, no plan de UX | Contrastar con codigo y plan corto |
| `docs/02_CLIENTE/MATRIZ_ACCESOS_ROLES_ERP_CIETE_2026-05-18.md` | Matriz de roles/permisos | Puede requerir revision por rol tecnico actual | Contrastar con tests y `User.php` |
| `docs/02_CLIENTE/MATRIZ_ADMIN_TECNICO_ERP_CIETE_2026-05-18.md` | Frontera admin tecnico/negocio | Naming puede estar desfasado | Contrastar con plan corto Bloque 6 |
| `docs/02_CLIENTE/VALIDACION_OPERATIVA_PERFILES_ESCENARIOS_ERP_CIETE_2026-05-18.md` | Escenarios por perfil | Evidencia anterior a nueva ola | Contrastar con tests de permisos |
| `docs/02_CLIENTE/VALIDACION_OPERATIVA_KO_EVIDENCIAS_ERP_CIETE_2026-05-18.md` | Registro de KOs y evidencias | No todo sigue pendiente | Cruzar con `tareasComparar.md` |
| `docs/03_API_ERP/Trabajos_API_Contract.md` | Contrato tecnico de trabajos | Puede no cubrir nueva UX de pedidos desde trabajo | Contrastar con codigo real |
| `docs/03_API_ERP/Mapeo_Importacion_Excel.md` | Mapeo de importacion | No usar para decisiones funcionales de UI | Contrastar con importadores reales |

## 5. Documentos historicos o peligrosos

| Ruta | Motivo de desfase | Riesgo | Accion recomendada |
| --- | --- | --- | --- |
| `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` | Sintesis previa que ya no debe ser fuente principal | Puede imponer decisiones anteriores a la reunion 19/05 | Mantener con cabecera historica/secundaria |
| `docs/02_CLIENTE/historicos/*` | Analisis y tareas tempranas de mayo | Puede reabrir enfoques superados | Mantener como historico |
| `docs/03_API_ERP/historicos/*` | Planes de BBDD/API previos | Puede empujar rediseños grandes no aprobados | Mantener solo como contexto tecnico |
| `docs/04_DISENO_UI/historicos/*` | Guias visuales antiguas | Puede contradecir interfaz compacta actual | Mantener solo como referencia historica |
| `docs/05-SPRINTS/*` | Sprints y bitacoras de ejecucion pasada | Puede parecer plan vigente | Archivar como historial de sprint |
| `docs/07_REVISIONES_DOCUMENTALES/*` | Revision documental de 02/05 superada | Apunta a autoridad antigua | Archivar como revision historica |
| Tutoriales y resumenes demo v2.1.0 | Material de presentacion, no backlog actual | Puede confundirse con estado final | Archivar como demo historica |
| `docs/_archivo_historico/*` | Material ya archivado | No debe guiar implementacion | Mantener como archivo |

## 6. Propuesta de limpieza documental

Mantener vivos:

- `listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
- `plan_8_dias_nueva_version_erp_ciete.md`
- `PLAN_CORTO_EJECUCION_REUNION_CESAR_AMAYA_2026-05-28.md`
- `tareasComparar.md`
- `reunionCieteCompletaFormato.txt`
- B1/B2 y auditorias recientes como evidencia.

Resumir:

- README principal de `docs/`.
- README de `docs/02_CLIENTE/`.
- `docs/BIBLIA_DESARROLLO.md`.

Mover a historico:

- Sprints antiguos.
- Revision documental 02/05.
- Tutoriales, guiones y resumenes imprimibles de demo v2.1.0.

Marcar como historico con cabecera:

- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`
- Cualquier documento antiguo que siga en ubicacion viva y pueda confundirse con autoridad actual.

Dejar solo como evidencia:

- Fases B1/B2.
- Validaciones operativas.
- Auditorias de completitud y cobertura.
- Matrices de roles/tipos mientras no contradigan codigo o plan nuevo.

## 7. Instruccion para proximos agentes

```text
Antes de implementar, usa como fuente principal:
1. Codigo real actual.
2. docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md
3. docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md
4. docs/02_CLIENTE/PLAN_CORTO_EJECUCION_REUNION_CESAR_AMAYA_2026-05-28.md
5. docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md
6. docs/02_CLIENTE/tareasComparar.md

No uses como fuente principal:
- docs/05-SPRINTS/*
- docs/07_REVISIONES_DOCUMENTALES/*
- docs/02_CLIENTE/historicos/*
- docs/03_API_ERP/historicos/*
- docs/04_DISENO_UI/historicos/*
- docs/_archivo_historico/*
- tutoriales, guiones y resumenes de demo v2.1.0
- docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md salvo como sintesis historica/secundaria

Si hay contradiccion, prevalece el codigo real para saber que existe y prevalecen la reunion exhaustiva del 19/05, el estado funcional 2026-05-28 y el plan corto para decidir que implementar ahora.
```
