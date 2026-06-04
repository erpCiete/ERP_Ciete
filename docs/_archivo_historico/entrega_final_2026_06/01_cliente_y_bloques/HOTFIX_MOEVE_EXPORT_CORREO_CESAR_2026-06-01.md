# Hotfix Moeve export — correo César 2026-05-20

## 1. Objetivo

Dejar operativa la exportación de solicitud de pedidos Moeve en el formato real pedido por César:

- `PDF`;
- `CSV`;
- `ARIBA - TRAMITACION DE PEDIDOS`.

## 2. Fuente real

- Correo César García `2026-05-20 15:54`.
- Adjuntos revisados en repo:
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).pdf`
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).csv`
    - `docs/00_FUENTES_CLIENTE/moeve_solicitud_pedidos_2026-05-20/20260506_33450_La Senyera I_Pedido Ciete (1).xlsx`

## 3. Cambios PDF/HTML

- Cabecera realista con:
    - `E.S. Nº`
    - `Fecha`
    - `Nombre`
    - `Localidad`
    - `Trabajo encargado por`
- Título `OFERTA PRECIOS ACUERDO`.
- Tabla con:
    - `Item`
    - `Descripción`
    - `Código MOEVE`
    - `Unidades`
    - `Precio unidad`
    - `Total`
- Total final, nota contractual y `Aprobado por`.
- Aviso visible de campos pendientes de parametrizar si aplica.

## 4. Cambios CSV

- CSV rehecho con cabeceras de negocio próximas al adjunto real.
- Separador `;`, BOM UTF-8 y decimales con coma.
- Fichero estable:
    - `moeve_pedido_{numero_pedido}.csv`

## 5. Cambios ARIBA

- Cabecera exacta:
    - `ARIBA - TRAMITACION DE PEDIDOS`
- Estructura copiable al cuerpo del correo:
    - `Propuesta de Inversión - \Opex acción gasto`
    - `Acción de gasto AC`
    - `Sociedad`
    - `Cta. de Mayor`
    - `ID de producto/ ID del contrato`
    - `Producto`
    - `Cantidad`
    - `Texto Proveedor`
    - `Descripción`
    - `Centro /Concesión`
    - `Precio`
    - `Proveedor/ Contrato`
    - `Confirmar`

## 6. Datos resueltos desde modelo

- `E.S. Nº` desde `codigo_estacion`.
- `Nombre` desde `nombre` de estación.
- `Localidad` desde `poblacion + provincia`.
- `Trabajo encargado por` desde `responsable_cliente` o datos Moeve de estación.
- `Descripción` desde `descripcion_trabajo` o `categoria`.
- `Código MOEVE / Producto` desde `codigo_servicio`, `codigo_tarifa` o `numero_tarifa`.
- `Texto Proveedor` desde descripción/concepto de línea.
- `Cantidad`, `Precio unidad` y `Total` desde la línea del pedido.
- `Sociedad` desde `estaciones_moeve_ext.cod_sociedad` si existe.

## 7. Datos pendientes de parametrizar

- `Cta. de Mayor`
- `Propuesta de Inversión / Opex acción gasto`
- `Acción de gasto AC`
- `Proveedor / Contrato` exacto cuando no coincida con el contrato interno

## 8. Validaciones

- `PedidoTest` ampliado para PDF, CSV y ARIBA.
- `npm run build`
- `git diff --check`

## 9. Resultado

- Estado:
    - `OK operativo con pendientes visibles de parametrización`
- Alcance respetado:
    - sin tocar Repsol;
    - sin tocar estados;
    - sin tocar facturación;
    - sin tocar cierre;
    - sin tocar roles.

## 10. Pendientes

- Validación manual final en navegador con CIETE.
- Parametrización real de campos de negocio hoy visibles como `Pendiente de parametrizar`.
