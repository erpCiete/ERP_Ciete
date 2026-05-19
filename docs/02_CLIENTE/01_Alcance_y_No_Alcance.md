# Alcance y no alcance ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`.

Estado: documento vivo resumido. Sustituye versiones anteriores de alcance que hablaban de obras, Cepsa, cierre legacy, cobros o legalizaciones como flujo principal.

Referencia de autoridad: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`, aterrizada en `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md` y priorizada en `docs/02_CLIENTE/tareasComparar.md`.

## Alcance P0

- Contextos reales: MOEVE, REPSOL y OTROS CLIENTES.
- TODOS como vista global operativa segun permisos: ver, filtrar y editar existentes.
- Bloqueo de creacion desde TODOS.
- Trabajos como entidad operativa central.
- Trabajo puede existir sin pedido.
- Trabajo 1:N pedidos.
- Pedido 1:N `pedido_items`.
- `pedido_item` como unidad economica/facturable real.
- Facturacion por items:
    - `factura -> factura_items -> pedido_items -> pedidos -> trabajos`.
- Validacion de contrato/tarifa comun en los items facturados.
- Validacion de sociedad/CIF permitida para contrato/tarifa/grupo.
- Estados funcionales de trabajo:
    - `en_curso`
    - `terminado`
    - `pendiente_facturar`
    - `facturado`
    - `finalizado`
    - `cancelado`
- Ciete Excel como vista operativa diaria.
- Ciete Moderno como ficha/formulario/detalle.
- Auditoria operativa relevante, sin ruido visual.

## Alcance P1

- Pulido de permisos entre Direccion y admin tecnico.
- Exportacion de listado de facturas filtrado o seleccionado.
- Exportacion individual de factura con detalle.
- Sustitucion de borrados fisicos por baja/anulacion/cancelacion donde exista historico.
- Estaciones con columnas principales:
    - codigo estacion
    - nombre estacion
    - municipio
    - provincia
- Control optimista extendido a otros modulos que adopten edicion tipo Excel.
- Limpieza visual de Ciete Moderno.

## Alcance P2

- Importacion Excel avanzada.
- Legalizaciones completas.
- Presupuestos/hoja de pedido.
- Actualizacion automatica de estaciones.
- Costes e imputacion avanzada.
- Integraciones futuras con cobros si CIETE lo decide mas adelante.

## No alcance inmediato

- Cobros como flujo principal.
- Borrado fisico de estaciones.
- Crear registros desde TODOS.
- Tratar `facturas.id_trabajo` como relacion funcional principal.
- Tratar `factura_pedidos` como flujo principal.
- Reapertura normal de trabajos ya finalizados.
- Duplicar decisiones funcionales fuera del documento fuente de verdad.
