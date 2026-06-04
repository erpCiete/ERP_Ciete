# Revision documental 02/05/2026

> **Documento histórico.**  
> Este documento refleja una decisión, planificación o análisis anterior del proyecto.  
> Puede contener nombres, estados, modelos o prioridades ya superadas.  
> Fuente de verdad vigente: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: revision historica compactada.

Esta revision se conserva como evidencia del estado documental anterior. No es fuente de verdad vigente.

Fuente de verdad actual:

- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`

Historial de evolucion:

- `docs/02_CLIENTE/HISTORIAL_DECISIONES_ERP_CIETE.md`

## Resultado de la revision original

La revision detecto que la documentacion mezclaba:

- decisiones funcionales vigentes,
- planes de sprint,
- bitacoras,
- contratos API antiguos,
- referencias a Cepsa/obras/proyectos,
- estados legacy como `borrador` y `cerrado`,
- facturacion ligada a trabajo,
- cobros como parte del flujo,
- legalizaciones como bloque de cierre,
- naming antiguo `operative/executive`.

## Decision posterior

El proyecto queda alineado con las decisiones cerradas de mayo de 2026:

- TODOS es vista global operativa, no contexto real.
- MOEVE, REPSOL y OTROS CLIENTES son contextos reales.
- Trabajo puede existir sin pedido.
- Pedido item es la unidad economica real.
- Factura agrupa items.
- Relacion principal:

  `factura -> factura_items -> pedido_items -> pedidos -> trabajos`

- Cobros quedan fuera del flujo principal.
- Legalizaciones completas, presupuestos e importacion avanzada quedan fuera del P0.
- Auditoria no debe registrar ruido visual como actividad relevante.

## Uso de este documento

Usar solo como referencia historica para entender por que se hizo la limpieza documental.

No usar como contrato funcional ni como backlog.
