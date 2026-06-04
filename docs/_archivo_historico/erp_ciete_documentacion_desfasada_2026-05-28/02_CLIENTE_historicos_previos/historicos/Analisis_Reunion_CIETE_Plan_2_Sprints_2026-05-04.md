# Analisis reunion CIETE y plan de 2 sprints

> **Documento histórico.**  
> Este documento refleja una decisión, planificación o análisis anterior del proyecto.  
> Puede contener nombres, estados, modelos o prioridades ya superadas.  
> Fuente de verdad vigente: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: documento historico compactado.

Este documento conserva el resultado del analisis posterior a la reunion, pero ya no es la fuente de verdad funcional. La decision final vigente esta en:

`docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`

El backlog vivo esta en:

`docs/02_CLIENTE/tareasComparar.md`

## Conclusiones que siguen vigentes

- Trabajo es la entidad operativa central.
- El trabajo puede existir sin pedido.
- Un trabajo puede tener varios pedidos.
- Un pedido pertenece a un solo trabajo.
- `pedido_item` es la unidad economica/facturable real.
- La factura debe agrupar items, no depender de un unico trabajo.
- Contextos reales: MOEVE, REPSOL y OTROS CLIENTES.
- TODOS es vista global operativa, no contexto real.
- Ciete Excel es la vista operativa diaria.
- Ciete Moderno conserva fichas y detalle.
- Auditoria debe centrarse en actividad operativa relevante.

## Decisiones matizadas despues del analisis

- TODOS no es solo consulta: permite editar registros existentes segun permisos.
- OTROS CLIENTES es contexto real completo.
- Admin queda como rol tecnico/soporte, no como rol operativo normal de CIETE.
- Cobros quedan fuera del flujo principal.
- Legalizaciones completas quedan fuera del flujo critico inmediato.
- Cambios visuales como `interface_mode`, tema o idioma no deben auditarse como actividad relevante.

## Uso actual

Consultar solo como evidencia historica de como se llego a las decisiones finales.

No usar como plan de implementacion vigente.
