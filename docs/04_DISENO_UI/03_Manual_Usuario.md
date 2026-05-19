# Manual de usuario ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`.

Estado: manual provisional compactado.

Referencia de autoridad:

`docs/02_CLIENTE/reunionCieteCompletaFormato.txt`, aterrizada en `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md` y priorizada en `docs/02_CLIENTE/tareasComparar.md`

Este manual sustituye versiones anteriores que mezclaban admin operativo, cobros, legalizaciones o facturas por trabajo como flujo principal.

## 1. Contextos

- MOEVE, REPSOL y OTROS CLIENTES son contextos reales.
- TODOS es una vista global operativa segun permisos.
- Desde TODOS se puede ver, filtrar y editar registros existentes.
- Desde TODOS no se pueden crear registros nuevos.
- Para crear, selecciona primero MOEVE, REPSOL u OTROS CLIENTES en la topbar.

## 2. Roles

- Direccion: gestion funcional, usuarios, auditoria y supervision.
- Ejecucion: operativa de trabajos y pedidos segun permisos.
- Ejecucion MOEVE: operativa del contexto MOEVE.
- Ejecucion REPSOL: operativa del contexto REPSOL.
- Contabilidad: facturas y control economico.
- Soporte/admin tecnico: soporte, mantenimiento y tareas tecnicas.

## 3. Trabajos

- Un trabajo puede crearse sin pedido.
- Estado inicial: `en_curso`.
- Estados:
    - `en_curso`
    - `terminado`
    - `pendiente_facturar`
    - `facturado`
    - `finalizado`
    - `cancelado`
- `terminado` significa ejecucion hecha.
- `finalizado` significa ejecucion y economia resueltas.
- Los cancelados siguen visibles y filtrables.

## 4. Pedidos e items

- Un trabajo puede tener varios pedidos.
- Cada pedido pertenece a un solo trabajo.
- Los items de pedido son la unidad economica/facturable.
- Un item puede facturarse parcialmente.

## 5. Facturas

- La factura agrupa items.
- Puede incluir items de varios pedidos y trabajos.
- Todos los items deben compartir contrato/tarifa compatible.
- La sociedad/CIF debe estar permitida.
- El listado debe mostrar importe, asignado, diferencia y estado de cuadre.
- El ERP termina en factura; cobros quedan fuera del flujo principal.

## 6. Ciete Excel

- Vista operativa diaria.
- Tabla densa.
- Filtros.
- Edicion por celda.
- Guardado por campo.
- Observaciones en modal.
- Conflictos de edicion con aviso inline.

## 7. Ciete Moderno

- Fichas.
- Formularios.
- Detalle limpio.
- No sustituye a Ciete Excel.

## 8. Estaciones

Columnas principales:

- codigo estacion
- nombre estacion
- municipio
- provincia

La fecha de baja se conserva, pero no es columna protagonista. Las estaciones no se borran fisicamente.

## 9. Auditoria

- Nombre visible: Auditoria.
- Muestra por defecto actividad operativa relevante.
- No muestra como relevante cambios de tema, idioma, modo visual o preferencias cosmeticas.
- Exportar/limpiar logs: Direccion.

## 10. Modulos fuera de flujo principal

- Cobros.
- Presupuestos.
- Legalizaciones completas.
- Importacion Excel avanzada.
