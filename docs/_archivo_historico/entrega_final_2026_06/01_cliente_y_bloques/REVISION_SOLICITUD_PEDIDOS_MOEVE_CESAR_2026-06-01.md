# Revisión solicitud pedidos Moeve — correo César 2026-05-20

## Actualización 2026-06-01 — hotfix P0 ejecutado

- Los tres adjuntos reales del caso `La Senyera` ya están copiados en:
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/`
- Se ha ejecutado un hotfix mínimo dentro del alcance del correo:
    - `PDF/HTML` alineado con cabecera real, título `OFERTA PRECIOS ACUERDO`, tabla de líneas y total.
    - `CSV` rehecho con cabeceras de negocio próximas al adjunto real y compatibilidad Excel.
    - `ARIBA` rehecho con cabecera exacta `ARIBA - TRAMITACION DE PEDIDOS` y estructura copiable al cuerpo del correo.
- El backend Moeve usa ahora un payload común:
    - `app/Services/Exports/MoevePedidoExportService.php`
- Los campos no resueltos por modelo quedan visibles como:
    - `Pendiente de parametrizar`
- Estado operativo actual:
    - `OK operativo con pendientes visibles de parametrización`

## 1. Fuente

- Correo de César García del `2026-05-20 15:54`: `ERP Ciete - Solicitud de pedidos Moeve`.
- Literal funcional clave:
    - adjuntar `PDF`;
    - adjuntar `CSV`;
    - pegar en el cuerpo del correo `ARIBA - TRAMITACION DE PEDIDOS`.
- Fuentes contrastadas:
    - `docs/02_CLIENTE/tareasComparar.md`
    - `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
    - `docs/02_CLIENTE/CIERRE_FINAL_NUEVA_VERSION_CIETE_2026-06-01.md`
    - `docs/02_CLIENTE/BLOQUE_4_EXPORTACION_MOEVE_2026-06-01.md`
    - `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
    - `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `routes/web/operativa.php`
    - `resources/js/Pages/Pedidos/Form.jsx`
    - `resources/views/pedidos/export/moeve_pdf.blade.php`
    - `resources/views/pedidos/export/moeve_ariba.blade.php`
    - `tests/Feature/PedidoTest.php`

## 2. Qué pidió César

- Tres piezas separadas y reconocibles como flujo real Moeve:
    - `PDF`;
    - `CSV`;
    - tabla `ARIBA - TRAMITACION DE PEDIDOS` para pegar en el cuerpo del correo.
- Además, el caso real citado fija un patrón concreto de campos y un ejemplo cerrado:
    - estación `E.S. LA SENYERA I - Nº33450`;
    - sociedad ARIBA `1024`;
    - proveedor/contrato `8010023472 / CIETE, S.A.`;
    - total `13.940,00 €`;
    - líneas concretas de producto, cantidad, texto proveedor y precio.

## 3. Archivos de referencia

- Ya están localizados en el repo:
    - `20260506_33450_La Senyera I_Pedido Ciete (1).pdf`
    - `20260506_33450_La Senyera I_Pedido Ciete (1).csv`
    - `20260506_33450_La Senyera I_Pedido Ciete (1).xlsx`
- Uso aplicado:
    - `CSV` y `XLSX` como fuente principal de estructura y labels.
    - `PDF` como referencia visual operativa.

## 4. Caso La Senyera

- El correo sí define un caso operativo real y bastante cerrado.
- El ERP actual no contiene en el modelo revisado todos los campos explícitos del ejemplo:
    - sociedad `1024`;
    - proveedor/contrato `8010023472 / CIETE, S.A.`;
    - propuesta de inversión / Opex;
    - acción de gasto;
    - cuenta de mayor;
    - confirmar `SI/NO`;
    - aprobado por, si aplica.
- Por tanto, aunque el ERP pueda exportar tres salidas, no puede darse por válido frente a `La Senyera` sin esa comparación de adjuntos y sin revisar faltantes de modelo.

## 5. Comparación PDF

- Estado actual:
    - existe endpoint `GET /pedidos/{pedido}/export/moeve/pdf`;
    - realmente devuelve HTML imprimible, no PDF binario;
    - la vista usada es `resources/views/pedidos/export/moeve_pdf.blade.php`.
- Qué sí muestra:
    - número de pedido;
    - trabajo y descripción;
    - estación;
    - contrato;
    - tarifario;
    - fecha de solicitud/recepción;
    - estado;
    - tabla de líneas con código tarifa, código servicio, concepto, unidad, cantidad, precio y total;
    - total final del pedido.
- Qué no reproduce todavía como formato real del correo:
    - `E.S. Nº` como campo explícito;
    - localidad separada al estilo del PDF real;
    - `trabajo encargado por`;
    - proveedor/contrato en el formato del correo;
    - nota o leyenda equivalente al PDF real;
    - `aprobado por`, si aplica;
    - estructura visual cerrada del PDF de `La Senyera`.
- Conclusión PDF:
    - hay salida técnica;
    - no puede afirmarse que “se parece al PDF real” más allá de una equivalencia funcional muy básica.

## 6. Comparación CSV

- Estado actual:
    - existe endpoint `GET /pedidos/{pedido}/export/moeve/csv`;
    - el backend genera un CSV con cabeceras técnicas y una fila por línea de pedido.
- Cabeceras actuales:
    - `numero_pedido`
    - `fecha_solicitud`
    - `trabajo`
    - `codigo_estacion`
    - `estacion`
    - `contrato`
    - `tarifario`
    - `linea`
    - `codigo_tarifa`
    - `codigo_servicio`
    - `numero_tarifa`
    - `concepto`
    - `unidad`
    - `cantidad`
    - `precio_unitario`
    - `total_linea`
    - `importe_pedido`
- Qué sí cubre:
    - datos básicos del pedido;
    - líneas reales;
    - importes.
- Qué queda sin validar o sin reflejar:
    - equivalencia con el CSV real de `La Senyera`;
    - orden real de columnas;
    - nombres de columna exigidos por Moeve;
    - campos de ARIBA/negocio ajenos al modelo actual.
- Conclusión CSV:
    - existe CSV funcional;
    - no está validado contra el archivo real del correo.

## 7. Comparación cuadro ARIBA

- Estado actual:
    - existe endpoint `GET /pedidos/{pedido}/export/moeve/ariba`;
    - la vista usada es `resources/views/pedidos/export/moeve_ariba.blade.php`.
- Qué sí muestra:
    - número de pedido;
    - trabajo;
    - estación;
    - contrato y tarifario;
    - total económico;
    - tabla de líneas.
- Qué no reproduce todavía:
    - cabecera exacta `ARIBA - TRAMITACION DE PEDIDOS`;
    - `Propuesta de Inversión / Opex acción gasto`;
    - `Acción de gasto AC`;
    - `Sociedad`;
    - `Cta. de Mayor`;
    - `ID de producto / ID del contrato`;
    - `Texto Proveedor` como columna diferenciada;
    - `Descripción` como columna diferenciada de negocio;
    - `Centro / Concesión`;
    - `Proveedor / Contrato`;
    - `Confirmar SI/NO`.
- Conclusión ARIBA:
    - hoy es un resumen HTML técnico;
    - no reproduce todavía el cuadro ARIBA que César pidió pegar en el correo.

## 8. Estado actual del ERP

- El ERP cumple parcialmente el objetivo técnico:
    - hay tres endpoints;
    - el frontend muestra tres acciones de exportación;
    - el backend exige pedido con trabajo, tarifario, líneas válidas y totales coherentes;
    - no exporta pedidos vacíos o inconsistentes.
- Pero no cumple todavía la equivalencia funcional con el correo real de Moeve.
- La expresión correcta hoy no es “Moeve exportado según el correo real”, sino:
    - `exportación técnica inicial de Moeve pendiente de contraste real`.

## 9. Ajustes aplicados, si hubo

- Sí se ha aplicado hotfix mínimo de código, sin abrir desarrollo grande:
    - `app/Services/Exports/MoevePedidoExportService.php`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `resources/views/pedidos/export/moeve_pdf.blade.php`
    - `resources/views/pedidos/export/moeve_ariba.blade.php`
    - `resources/js/Pages/Pedidos/Form.jsx`
    - `tests/Feature/PedidoTest.php`
- Ajustes documentales:
    - `docs/02_CLIENTE/BLOQUE_4_EXPORTACION_MOEVE_2026-06-01.md`
    - `docs/02_CLIENTE/tareasComparar.md`
    - `docs/02_CLIENTE/CIERRE_FINAL_NUEVA_VERSION_CIETE_2026-06-01.md`
    - `docs/02_CLIENTE/HOTFIX_MOEVE_EXPORT_CORREO_CESAR_2026-06-01.md`

## 10. Datos faltantes

- Ya no faltan en repo los tres adjuntos reales del correo.
- Siguen faltando o no están garantizados en el modelo revisado los siguientes campos de negocio:
    - sociedad ARIBA;
    - cuenta de mayor;
    - acción de gasto;
    - propuesta de inversión / Opex;
    - proveedor/contrato con formato real;
    - confirmar `SI/NO`;
    - posible `aprobado por`.
- Sin esos datos, el sistema puede renderizar una plantilla, pero no cerrar una réplica real del correo.

## 11. Riesgos

- Riesgo de demo:
    - presentar `Bloque 4` como cerrado frente a negocio cuando en realidad solo está resuelto técnicamente.
- Riesgo documental:
    - llamar “exportación Moeve” a una salida demasiado genérica respecto al correo real.
- Riesgo funcional:
    - que el usuario espere un formato `La Senyera` y reciba un HTML/ARIBA simplificados.

## 12. Conclusión

- Estado actual:
    - `OK operativo con pendientes visibles de parametrización`
- Afecta a:
    - `Moeve`
- Queda resuelto:
    - estructura operativa `PDF + CSV + ARIBA`;
    - cabecera ARIBA exacta;
    - cabecera PDF reconocible para CIETE;
    - CSV de negocio más próximo al adjunto real.
- Queda pendiente:
    - parametrizar con dato real de negocio:
        - `Cta. de Mayor`
        - `Propuesta de Inversión / Opex acción gasto`
        - `Acción de gasto AC`
        - `Proveedor / Contrato` exacto si no coincide con el código de contrato interno
    - validación manual final en navegador y demo con CIETE.
