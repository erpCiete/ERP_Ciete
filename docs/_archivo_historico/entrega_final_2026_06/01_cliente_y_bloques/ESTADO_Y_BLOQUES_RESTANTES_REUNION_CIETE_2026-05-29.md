# Estado y bloques restantes ERP CIETE — 2026-05-29

## 1. Objetivo

Resumen de cierre del estado actual tras trabajar lo pedido en la reunión y orden recomendado para continuar sin mezclar bloques.

## 2. Fuente principal

La fuente principal de contraste funcional y nomenclatura es:

- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/tareasComparar.md`

## 3. Qué se ha completado

- `Trabajos` queda fijado como pantalla principal operativa.
- Vista Excel compacta operativa en tabla única.
- Pedido desde trabajo ya integrado en la fila.
- Varios pedidos en trabajo mostrados como `Pedido 1`, `Pedido 2`, `Pedido 3`.
- `Pedidos` se mantiene en sidebar como consulta/control.
- Buscador avanzado operativo.
- Filtros rápidos, filtros avanzados y chips activos operativos.
- Ajuste de ancho wide/ultrawide aplicado a la vista `Trabajos`.
- `Nº trabajo` automático en alta rápida.
- Nomenclatura principal de columnas ya corregida.
- `Tarifario` fijado como nombre visible de la antigua columna `Contrato / Tarifa`.
- Estados visibles ajustados a nomenclatura real y en una sola línea.
- Alta rápida que guarda primero el trabajo y luego abre creación de pedido.
- `Categoría de trabajo` en alta rápida con selector buscable por código y nombre cuando existe catálogo real.
- En la base actual sí existe catálogo real en los contextos operativos revisados:
  - `tipos_trabajo`: contexto `1 => 29`, `2 => 226`, `3 => 2`
  - `tipos_documento`: contexto `1 => 2`, `2 => 12`, `3 => 1`
- `Abrir ficha` ya no parte línea en la tabla.

## 4. Qué queda pendiente

- Validación manual final de `Trabajos` en navegador.
- Bloque 2 — Tarifario / contrato / sociedad facturadora.
- Bloque 3 — Líneas de pedido desde tarifario.
- Buscador de líneas por descripción/código.
- Decimales en cantidad/precio/importes.
- Exportación Moeve PDF + CSV + cuadro ARIBA.
- Estados automáticos derivados.
- Facturación y cierre secundario.
- Roles/permisos finos.
- Auditoría/concurrencia extendida.
- Maestros: desactivar en vez de borrar y confirmar cambios sensibles.

## 5. Bloques restantes propuestos

### Bloque 2 — Tarifario, contrato y sociedad facturadora

Objetivo:
- Cerrar la regla real de asociación entre trabajo, tarifario, contrato y sociedad facturadora.

Alcance:
- origen del tarifario por cliente/contexto/sociedad;
- valor por defecto real;
- comportamiento cuando existen varias sociedades facturadoras;
- coherencia entre trabajo, pedido y facturación posterior.

Archivos probables:
- `app/Http/Controllers/Api/TrabajoController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Resources/Api/TrabajoResource.php`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- modelos/catálogos de `Contrato`, `Tarifario` y sociedad facturadora

Criterios de aceptación:
- el trabajo siempre queda asociado al tarifario correcto;
- el origen del predeterminado es real y trazable;
- no se puede seleccionar combinación incoherente cliente/sociedad/tarifario;
- la alta desde `Trabajos` y el pedido heredan el mismo criterio.

### Bloque 3 — Líneas de pedido y decimales

Objetivo:
- Crear líneas de pedido desde tarifario con selección rápida y soporte decimal correcto.

Alcance:
- selector buscable de línea/ítem por código y descripción;
- cantidades, precios e importes con decimales donde proceda;
- herencia desde tarifario al pedido.

Archivos probables:
- `resources/js/Pages/Pedidos/Form.jsx`
- componentes de líneas de pedido
- `app/Http/Controllers/Api/PedidoController.php`
- requests/validaciones de pedido y líneas

Criterios de aceptación:
- líneas seleccionables por código/descripcion;
- cálculos correctos con decimales;
- sin duplicar lógica ni romper pedidos ya creados.

### Bloque 4 — Exportación Moeve

Objetivo:
- Generar salida operativa Moeve desde pedido completo.

Alcance:
- PDF;
- CSV;
- cuadro ARIBA;
- validación con caso real de negocio.

Criterios de aceptación:
- exportaciones coherentes con pedido y líneas;
- formato usable por negocio sin edición manual masiva posterior.

### Bloque 5 — Estados automáticos, facturación y cierre

Objetivo:
- Convertir estados visibles en estados realmente derivados del flujo.

Alcance:
- `Trabajo en curso`, `Terminado`, `Pendiente de facturar`, `Facturado`, `Finalizado`, `Cancelado`;
- cancelados visibles al final;
- cierre como diagnóstico secundario, no como duplicado del flujo principal.

Criterios de aceptación:
- estados consistentes con pedido, solicitado, facturado y cierre;
- menos intervención manual en `estado`;
- cancelados siguen visibles y ordenados al final.

### Bloque 6 — Roles, maestros y auditoría

Objetivo:
- Ajustar los permisos finos y endurecer maestros/auditoría sin romper operativa.

Alcance:
- técnico, dirección, contabilidad y ejecución;
- maestros sin borrado físico;
- confirmaciones sensibles;
- auditoría por campo y concurrencia extendida.

Criterios de aceptación:
- permisos coherentes por rol;
- cambios sensibles trazados;
- maestros desactivables sin borrar histórico.

### Bloque G — Validación final y limpieza

Objetivo:
- Cerrar validación transversal antes de dar por estable la ola actual.

Alcance:
- build;
- tests;
- validación navegador;
- documentación final;
- limpieza técnica de arrastres menores.

Criterios de aceptación:
- flujo principal estable en navegador;
- documentación alineada con código;
- sin deuda visible crítica en la vista principal.

## 6. Orden recomendado

1. Validación visual final de `Trabajos`.
2. Bloque 2.
3. Bloque 3.
4. Bloque 4.
5. Bloque 5.
6. Bloque 6.
7. Bloque G.

## 7. Riesgos

- No empezar líneas antes de cerrar tarifario/sociedad.
- No exportar Moeve antes de cerrar estructura de pedido/líneas.
- No automatizar estados sin pedido/facturación bien conectados.
- No tocar maestros con borrado físico.
