# INFORME LIMPIEZA DOCUMENTAL A.10 (2026-05-19)

## 1) Objetivo

Limpiar y ordenar documentacion antes de Fase B, sin perder trazabilidad funcional, decisiones, evidencias ni material fuente de cliente.

## 2) Criterios de conservacion

- No borrar fuentes funcionales, decisiones, auditorias, backlog, matrices, validaciones ni KOs.
- No borrar material fuente cliente (transcripciones, PDF, XLSX).
- Ante duda: conservar o archivar en historico.
- No tocar codigo, base de datos, produccion, ramas ni commits.

## 3) Documentos protegidos

- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`
- `docs/02_CLIENTE/HISTORIAL_DECISIONES_ERP_CIETE.md`
- `docs/02_CLIENTE/MATRIZ_ACCESOS_ROLES_ERP_CIETE_2026-05-18.md`
- `docs/02_CLIENTE/MATRIZ_ADMIN_TECNICO_ERP_CIETE_2026-05-18.md`
- `docs/02_CLIENTE/MATRIZ_TIPOS_NUMERICOS_CODIGOS_A6-R_2026-05-18.md`
- `docs/02_CLIENTE/VALIDACION_OPERATIVA_PERFILES_ESCENARIOS_ERP_CIETE_2026-05-18.md`
- `docs/02_CLIENTE/VALIDACION_OPERATIVA_KO_EVIDENCIAS_ERP_CIETE_2026-05-18.md`
- `docs/02_CLIENTE/materiales/*` (material fuente citado)
- `docs/05-SPRINTS/**/Bitacora/**` (quien hizo que)

## 4) Carpetas creadas

- `docs/00_FUENTES_CLIENTE/` (`reuniones`, `transcripciones`, `excels`, `material_original`)
- `docs/04_EVIDENCIAS/` (`tests`, `capturas`, `builds`, `validaciones_manuales`)
- `docs/_archivo_historico/` (`borradores`, `reemplazados`, `legacy`)

## 5) Archivos movidos

- `docs/MemoriaProyecto/**` -> `docs/_archivo_historico/legacy/MemoriaProyecto/**`
- `docs/informes/**` -> `docs/_archivo_historico/legacy/informes/**`
- `docs/03_API_ERP/bbdd.pdf` -> `docs/03_API_ERP/historicos/bbdd.pdf`

Nota de reemplazo:

- El contenido de `docs/MemoriaProyecto/**` queda reemplazado como referencia activa por `docs/README.md` y el bloque historico en `docs/_archivo_historico/`.

## 6) Archivos eliminados

No se eliminaron archivos en A.10.

## 7) Duplicados detectados

- Duplicados exactos por hash SHA-256: ninguno.
- Documentos cercanos por tema (con valor historico distinto):  
  `AUDITORIA_COMPLETITUD_MAESTROS_P1-14_2026-05-17.md` y  
  `AUDITORIA_COMPLETITUD_MAESTROS_P1-14_REAL_POST_IMPORTACION_2026-05-18.md`.

## 8) Documentos canonicos finales

1. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
2. `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
3. `docs/02_CLIENTE/tareasComparar.md`
4. `docs/02_CLIENTE/MATRIZ_ACCESOS_ROLES_ERP_CIETE_2026-05-18.md`
5. `docs/02_CLIENTE/MATRIZ_ADMIN_TECNICO_ERP_CIETE_2026-05-18.md`
6. `docs/02_CLIENTE/VALIDACION_OPERATIVA_PERFILES_ESCENARIOS_ERP_CIETE_2026-05-18.md`
7. `docs/02_CLIENTE/VALIDACION_OPERATIVA_KO_EVIDENCIAS_ERP_CIETE_2026-05-18.md`
8. `docs/02_CLIENTE/MATRIZ_TIPOS_NUMERICOS_CODIGOS_A6-R_2026-05-18.md`

## 9) Peso aproximado antes/despues

- Antes (inventario A.10): `1664644` bytes en `docs/` (209 archivos).
- Despues: `1664793` bytes en `docs/` (212 archivos).

Resultado:

- No hay reduccion neta de bytes porque se priorizo seguridad/trazabilidad y se anadieron indices/reportes.
- Si se requiere reducir peso real, la siguiente accion segura es revisar `capturas.zip` historico para deduplicar contra capturas sueltas.

## 10) Riesgos o dudas pendientes

- `docs/_archivo_historico/legacy/MemoriaProyecto/Sprint_1/frontend/capturas.zip` no pudo inspeccionarse en detalle por falta de herramienta `unzip/zipinfo` en entorno actual.  
  Estado: conservado y archivado, pendiente de revision manual.
- `docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx` se mantiene en su ruta actual porque esta citado por documentacion tecnica y puede estar referenciado en flujo funcional.

## 11) Confirmaciones de alcance

- No se borro la transcripcion CIETE.
- No se borraron decisiones funcionales.
- No se borraron evidencias funcionales/KO.
- No se borro documentacion de responsabilidades del equipo.
- No se toco codigo funcional.
- No se toco base de datos ni migraciones/seeders.
- No se toco produccion.
- No se hizo commit.
