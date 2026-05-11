# Plan de reestructuracion BBDD

> **Documento histórico.**  
> Este documento refleja una decisión, planificación o análisis anterior del proyecto.  
> Puede contener nombres, estados, modelos o prioridades ya superadas.  
> Fuente de verdad vigente: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: referencia historica tecnica compactada.

Fuente funcional: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

## Principio

No se reestructura toda la base de datos. Se aplica una evolucion compatible con datos existentes.

## Cambios P0 necesarios

| Area | Decision |
|---|---|
| Facturas | Usar `factura_items` como relacion principal |
| Legacy | Mantener `facturas.id_trabajo` nullable y `factura_pedidos` temporalmente |
| PedidoItems | Actualizar por ID, no borrar/recrear |
| Sociedades/CIF | Relacion minima de sociedades permitidas por contrato/tarifa |
| Contextos | Bloquear altas desde TODOS |
| Trabajos | Estados funcionales CIETE |
| Auditoria | Excluir ruido visual de actividad relevante |

## Tabla recomendada minima

Nombre orientativo:

`contrato_empresas_facturadoras`

Objetivo:

- Relacionar contrato/tarifa con sociedades/CIF permitidas.
- Evitar facturas contra sociedad incompatible.
- Mantener historico aunque contrato quede inactivo.

## No hacer

- No borrar tablas legacy antes de migrar uso funcional.
- No hacer `migrate:fresh`.
- No borrar datos.
- No eliminar bitacoras ni historico documental.
- No mover cobros al P0.
