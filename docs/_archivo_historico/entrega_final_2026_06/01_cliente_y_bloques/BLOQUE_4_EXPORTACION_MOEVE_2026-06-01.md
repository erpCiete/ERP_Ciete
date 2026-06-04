# Bloque 4 - Exportacion Moeve PDF + CSV + cuadro ARIBA

## Actualización 2026-06-01 — remate P0 correo César

- Los adjuntos reales del caso `La Senyera` ya están copiados en el repo y han servido de referencia directa.
- `Bloque 4` deja de ser solo “exportación técnica inicial” y pasa a:
    - `exportación operativa Moeve con pendientes visibles de parametrización`
- Remates ejecutados:
    - payload común de exportación en `app/Services/Exports/MoevePedidoExportService.php`;
    - `PDF/HTML` alineado con cabecera real y tabla reconocible;
    - `CSV` con cabeceras de negocio próximas al adjunto real;
    - `ARIBA` con cabecera exacta `ARIBA - TRAMITACION DE PEDIDOS`;
    - aviso suave en frontend sobre campos pendientes de parametrizar;
    - tests de `PedidoTest` ampliados para exportación Moeve.

## 1. Objetivo

Cerrar una primera exportación operativa desde `Pedido` completo, sin mezclar:

- Bloque 5;
- automatismos de estado;
- cambios en facturación más allá de lectura;
- permisos nuevos;
- despliegue.

## 2. Fuentes revisadas

- `docs/02_CLIENTE/PROMPTS_PARA_CODEX_CIETE.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `app/Http/Controllers/Api/PedidoController.php`
- `routes/web/operativa.php`
- `resources/js/Pages/Pedidos/Form.jsx`
- `tests/Feature/PedidoTest.php`
- `tests/Feature/FacturaExportTest.php`

## 3. Decisión de formato

No aparece en la documentación revisada un formato cerrrado campo a campo para Moeve o ARIBA a nivel de pedido.

Revisión posterior del correo de César del `2026-05-20 15:54`:

- sí existe una instrucción funcional concreta posterior:
    - adjuntar `PDF`;
    - adjuntar `CSV`;
    - pegar en el cuerpo del correo `ARIBA - TRAMITACION DE PEDIDOS`.
- los adjuntos reales del caso `La Senyera` ya están copiados en este repo con estos nombres:
    - `20260506_33450_La Senyera I_Pedido Ciete (1).pdf`
    - `20260506_33450_La Senyera I_Pedido Ciete (1).csv`
    - `20260506_33450_La Senyera I_Pedido Ciete (1).xlsx`
- por tanto, este bloque ya puede leerse como una exportación operativa contrastada a nivel de layout y labels mínimos, aunque no todavía como parametrización completa 1:1 de todos los campos de negocio.

Se aplica por tanto un formato inicial razonable y mínimo:

- `CSV Moeve`:
    - cabecera con pedido, fecha, trabajo, estación, contrato y tarifario;
    - detalle por línea con código tarifa, código servicio, número tarifa, concepto, unidad, cantidad, precio y total;
    - importe total repetido como control.
- `PDF Moeve`:
    - se resuelve como HTML imprimible, no como binario PDF;
    - incluye cabecera amplia y tabla de líneas.
- `Cuadro ARIBA`:
    - resumen HTML compacto con metadatos principales y tabla de líneas.

Motivo:

- no hay librería PHP de PDF integrada y clara en el proyecto;
- sí existe patrón backend reutilizable para exportar CSV;
- el prompt permite usar HTML imprimible si no hay soporte PDF claro.
- siguen faltando algunos datos de negocio reales para completar una parametrización 1:1 con `La Senyera`.

## 4. Regla aplicada

La exportación solo se permite si el pedido es coherente y completo:

1. el pedido debe tener `trabajo`;
2. el pedido debe tener `tarifario`;
3. debe existir al menos una línea;
4. cada línea debe pertenecer al tarifario del pedido;
5. la suma de `pedido_items.total_linea` debe coincidir con `pedidos.importe_pedido`;
6. la suma de `pedido_items.cantidad` debe coincidir con `pedidos.unidades_pedido`.

Si alguna condición falla, no se exporta.

## 5. Implementación

### Backend

Se añaden tres rutas web protegidas por `pedidos.ver`:

- `GET /pedidos/{pedido}/export/moeve/pdf`
- `GET /pedidos/{pedido}/export/moeve/csv`
- `GET /pedidos/{pedido}/export/moeve/ariba`

Se implementa en `PedidoController`:

- carga ampliada de relaciones para exportación;
- validación de consistencia;
- generación de payload común;
- descarga CSV;
- render de vistas HTML;
- registro en auditoría con acción `exportar`.

### Frontend

En `Pedidos/Form.jsx`:

- aparece un bloque `Exportación Moeve` solo al editar un pedido existente;
- si el pedido tiene líneas exportables:
    - `Exportar PDF Moeve`
    - `Exportar CSV Moeve`
    - `Cuadro ARIBA`
- si no tiene líneas:
    - aviso claro de que no puede exportarse todavía.

También se informa de que la exportación usa el pedido ya guardado en servidor.

## 6. Validación

### PHP

Usando PHP de Windows/XAMPP:

- `PedidoTest`: OK, `20` tests y `76` aserciones.
- `TrabajoTest`: OK, `30` tests y `242` aserciones.
- `MaestrosTest`: OK, `11` tests y `62` aserciones.

Incidencia de entorno detectada:

- un intento en paralelo de `TrabajoTest` y `MaestrosTest` contaminó la BD de testing MySQL;
- repetidos en secuencial, ambos quedaron correctos.

### Frontend

- `npm run build`: OK.
- `git diff --check`: OK, con avisos CRLF solo en ficheros ajenos.

### Manual

- no ejecutada en navegador desde esta sesión por no disponer de GUI/browser.

## 7. Estado final

- `estado del bloque`: operativo con hotfix P0 ejecutado
- `pendiente residual`: validación visual/manual y parametrización de algunos campos de negocio
- `siguiente paso si se acepta este cierre`: `Bloque 5`

Nota de alcance:

- este documento no debe interpretarse como confirmación de que el layout actual coincide con el pedido real de Moeve;
- confirma que existen tres salidas técnicas y ahora también operativas para el flujo real de correo;
- mantiene como pendiente la validación final de negocio sobre campos hoy marcados como `Pendiente de parametrizar`.
