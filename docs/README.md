# Documentacion ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: indice canonico de documentacion.

Este repositorio conserva documentos historicos, bitacoras, planes de sprint, materiales recibidos del cliente y documentos tecnicos. Para evitar duplicados funcionales, la lectura debe hacerse con este orden.

## 1. Fuente de verdad vigente

Documento principal:

- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`

Documento de evolucion:

- `docs/02_CLIENTE/HISTORIAL_DECISIONES_ERP_CIETE.md`

Estos dos documentos mandan sobre cualquier alcance, plan, manual o contrato anterior cuando haya contradiccion funcional.

## 2. Documentos vivos utiles

| Documento | Uso |
|---|---|
| `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` | Fuente funcional vigente |
| `docs/02_CLIENTE/HISTORIAL_DECISIONES_ERP_CIETE.md` | Trazabilidad de cambios de criterio |
| `docs/02_CLIENTE/01_Alcance_y_No_Alcance.md` | Alcance vigente resumido |
| `docs/02_CLIENTE/02_Requisitos_y_Acuerdos.md` | Requisitos vigentes resumidos |
| `docs/02_CLIENTE/tareasComparar.md` | Backlog funcional vigente P0/P1/P2 |
| `docs/03_API_ERP/Trabajos_API_Contract.md` | Contrato tecnico vigente de trabajos y flujo relacionado |
| `docs/BIBLIA_DESARROLLO.md` | Guia tecnica viva, no historica |

## 3. Documentos historicos o de evidencia

Estos documentos se conservan, pero no son fuente funcional vigente:

- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/historicos/Analisis_Reunion_CIETE_Plan_2_Sprints_2026-05-04.md`
- `docs/02_CLIENTE/historicos/Tareas_Pablo_01_02_Mayo_2026.md`
- `docs/07_REVISIONES_DOCUMENTALES/REVISION_DOCUMENTAL_02_05_2026.md`
- `docs/05-SPRINTS/**`
- `docs/MemoriaProyecto/**`
- `docs/Abaco/**`
- PDFs, Excels, SQLs, capturas y material bruto recibido.

## 4. Bitacoras

Las bitacoras no se limpian, no se compactan y no se reinterpretan como documentacion funcional vigente.

Rutas protegidas:

- `docs/05-SPRINTS/**/Bitacora/**`

Las bitacoras son registro historico de trabajo del equipo.

## 5. Regla de limpieza documental

- No duplicar decisiones funcionales fuera del documento de decisiones.
- No tratar planes antiguos como verdad actual.
- No borrar bitacoras.
- No borrar material recibido del cliente.
- Si un documento antiguo contradice la fuente de verdad, se marca como historico o se compacta hacia una referencia.
- Los documentos vivos deben ser cortos, accionables y enlazar a la fuente de verdad.

## 6. Lectura recomendada para implementar

1. Leer `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.
2. Revisar `docs/02_CLIENTE/tareasComparar.md` para prioridad P0/P1/P2.
3. Revisar `docs/03_API_ERP/Trabajos_API_Contract.md` si se toca backend/API.
4. Revisar `BIBLIA_DESARROLLO.md` para reglas tecnicas.
5. Consultar reunion/transcripciones solo como evidencia historica.
