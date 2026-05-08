# Mapeo importacion Excel

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Estado: referencia P2 viva compactada.

La importacion Excel avanzada no forma parte del P0. Este documento se conserva para cuando se retome la migracion/importacion controlada.

## Fuente vigente antes de importar

- `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`
- `docs/02_CLIENTE/tareasComparar.md`

## Lo que los Excel ya confirmaron

- CIETE trabaja en tablas densas.
- El orden de columnas es importante para adopcion.
- El codigo de estacion es columna principal.
- Municipio y provincia son campos de busqueda reales.
- Hay trabajos sin pedido.
- Hay trabajos cancelados que deben conservarse.
- Los pedidos e importes no siempre llegan en orden.
- La factura debe contrastarse contra importes/items asignados.

## Criterio de importacion futura

- Los Excel sirven como evidencia y muestras de migracion.
- No son la fuente de verdad funcional.
- No obligan a copiar exactamente toda la estructura Excel.
- Ciete Excel debe reproducir la agilidad operativa, no los errores historicos.

## Reglas que debe respetar una futura importacion

- No crear desde TODOS.
- No mezclar MOEVE, REPSOL y OTROS CLIENTES.
- No borrar estaciones fisicamente.
- No borrar/recrear `pedido_items` si pueden quedar facturados.
- Mantener cancelados visibles.
- Mantener contratos/tarifas historicos.
- Tratar `facturas.id_trabajo` y `factura_pedidos` como legacy.
- Importar facturacion futura por `factura_items`.

## Estado

Pendiente P2.
