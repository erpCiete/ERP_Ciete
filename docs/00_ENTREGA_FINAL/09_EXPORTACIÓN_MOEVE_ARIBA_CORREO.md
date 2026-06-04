# Exportación Moeve: PDF, CSV, ARIBA y correo

## Origen

César García (Ciete) solicitó en la reunión del 19/05/2026 que el ERP generara los documentos que habitualmente se enviaban manualmente a Moeve para tramitar pedidos. El formato requerido sigue el esquema operativo de MOEVE con sus campos ARIBA.

## Qué se genera

Desde la ficha de cualquier pedido MOEVE con líneas, sección "Exportación Moeve":

### PDF / HTML imprimible
- Título: "OFERTA PRECIOS ACUERDO"
- Cabecera: E.S. Nº, fecha, nombre estación, localidad, "trabajo encargado por"
- Tabla de líneas: ítem, descripción, código MOEVE, unidades, precio/ud, total
- Total del pedido
- Nota contractual (condiciones del contrato MOEVE–Ciete)
- Si hay campos sin parametrizar, aparece un aviso al pie

### CSV
- Separador: `;` (punto y coma)
- Encoding: UTF-8 con BOM
- Nombre de archivo: `moeve_pedido_{numero_pedido}.csv`
- Columnas según el formato ARIBA de Moeve
- Los decimales usan coma (ej: `510,00`)

### Cuadro ARIBA
- Título: "ARIBA - TRAMITACION DE PEDIDOS"
- Cabecera con los 4 campos de gestión ARIBA
- Tabla de ítems: ID producto, descripción, cantidad, texto proveedor
- Datos de cierre: descripción, centro/concesión, precio, proveedor/contrato, confirmar

---

## Campos ARIBA requeridos

Estos campos se configuran en Maestros → Contratos → Editar → bloque "Datos ARIBA":

| Campo ARIBA | Campo en BD | Dónde aparece |
|------------|------------|--------------|
| Propuesta de Inversión / Opex | `ariba_propuesta_opex` | Cabecera ARIBA |
| Acción de gasto AC | `ariba_accion_gasto` | Cabecera ARIBA |
| Sociedad | `ariba_sociedad` (fallback) | Cabecera ARIBA |
| Cta. de Mayor | `ariba_cta_mayor` | CSV col.12 |
| Proveedor / Contrato | `ariba_nombre_proveedor` | CSV + ARIBA |

**Si estos campos están vacíos**, los documentos muestran "Pendiente de parametrizar".

La **Sociedad** tiene dos fuentes:
1. `estaciones_moeve_ext.cod_sociedad` (por estación, fuente primaria)
2. `contratos.ariba_sociedad` (fallback a nivel de contrato)

---

## Modal "Preparar correo Moeve"

El botón "✉ Preparar correo Moeve" en la ficha del pedido abre un modal con:

- **Asunto copiable**: `Solicitud de pedido Moeve - {codigo} - {nombre_estacion} - {numero_pedido}`
- **Cuerpo copiable**: texto listo para pegar en el cliente de correo
- **Botones de descarga**: Abrir PDF, Descargar CSV, Abrir cuadro ARIBA
- **Aviso**: los adjuntos deben añadirse manualmente (no hay integración SMTP con adjuntos automáticos)
- **Checklist**: 5 pasos antes de enviar

---

## Cómo validar con DEMO-MOE-LA-SENYERA

1. Login como `moeve@ciete.es` (Ejecución Moeve)
2. Ir a Pedidos → buscar `DEMO-MOE-LA-SENYERA`
3. Clic en "Ver ficha"
4. Sección "Exportación Moeve":
   - "PDF Moeve" → se abre en nueva pestaña
   - "CSV Moeve" → se descarga el archivo
   - "Cuadro ARIBA" → se abre en nueva pestaña
   - "✉ Preparar correo Moeve" → abre el modal

### Qué verificar en el PDF
- Título "OFERTA PRECIOS ACUERDO"
- E.S. Nº: 33450
- Nombre: LA SENYERA I
- Localidad: CUART DE POBLET (VALENCIA)
- 8 líneas de tarifa con códigos MOEVE y precios
- Total: 13.940,00 €

### Qué verificar en el ARIBA
- Cabecera: "ARIBA - TRAMITACION DE PEDIDOS"
- Sociedad: MOEVE Energy S.A. (o el valor configurado)
- Proveedor/Contrato: 772 / CIETE INGENIEROS S.A. (o el valor configurado)
- Sin "Pendiente de parametrizar" si el contrato está configurado

---

## Exportaciones por contexto

### MOEVE
- Tiene exportación completa: PDF/HTML, CSV y cuadro ARIBA.
- Rutas: `/pedidos/{id}/export/moeve/pdf`, `/pedidos/{id}/export/moeve/csv`, `/pedidos/{id}/export/moeve/ariba`
- Los botones aparecen solo cuando el pedido es de contexto MOEVE (controlado en frontend).
- El backend valida que el pedido pertenece al contexto MOEVE antes de exportar (HTTP 422 si no).

### REPSOL
- **No tiene exportación implementada.** No existe formato ARIBA para REPSOL.
- Los botones de exportación MOEVE no aparecen en pedidos REPSOL (filtrado por `selectedClientKey === 'moeve'`).
- Acceder manualmente a `/pedidos/{id_repsol}/export/moeve/pdf` devuelve HTTP 422 con mensaje claro.
- Si CIETE lo solicita, se añadirá exportación REPSOL con plantilla propia (sin campos ARIBA).

### OTROS CLIENTES
- Sin exportación. Mismo comportamiento que REPSOL.

### Qué pasa si se intenta exportar en formato incorrecto
El backend devuelve HTTP 422:
> "Este pedido no pertenece al contexto MOEVE y no puede exportarse en formato Moeve."

Validado manualmente el 2026-06-04 con pedido DEMO-REP-PARCIAL (id: 19415).

---

## Limitaciones actuales (P1)

- Los adjuntos no se añaden automáticamente al cliente de correo (requiere integración SMTP con soporte de adjuntos)
- El modal de correo genera el texto y proporciona botones de descarga, pero el envío es manual
- REPSOL no tiene exportación propia — pendiente de petición formal de negocio
