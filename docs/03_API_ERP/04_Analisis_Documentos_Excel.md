# Análisis Completo de Documentos — `docs/excelsactualizados/`

> **Fecha de análisis:** 13 de abril de 2026
> **Autor:** Arquitectura ERP Ciete
> **Total documentos analizados:** 13 archivos (1 .docx + 12 .xlsx)
> **Herramientas utilizadas:** `python-docx` (Word), `openpyxl` (Excel)

---

## Índice

1. [Email_Cesar_20260407.docx](#1-email_cesar_20260407docx)
2. [Mapeo_Moeve_Repsol_Envolvente.xlsx](#2-mapeo_moeve_repsol_envolventexlsx)
3. [Moeve/01 Control de Trabajos Moeve.xlsx](#3-moeve01-control-de-trabajos-moevexlsx)
4. [Moeve/02 Listado EESS España y Portugal.xlsx](#4-moeve02-listado-eess-españa-y-portugalxlsx)
5. [Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx](#5-repsol01-control-trabajos-diseño-repsolxlsx)
6. [Repsol/02 Control Trabajos EDIFICACIÓN.xlsx](#6-repsol02-control-trabajos-edificaciónxlsx)
7. [Repsol/03 Control Trabajos OBRAS REPSOL Z10.xlsx](#7-repsol03-control-trabajos-obras-repsol-z10xlsx--referencia)
8. [Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx](#8-repsol03-control-trabajos-obras-repsol-z50xlsx)
9. [Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx](#9-repsol05-control-trabajos-licencias-repsolxlsx)
10. [Repsol/09 Control Trabajos FV REPSOL.xlsx](#10-repsol09-control-trabajos-fv-repsolxlsx)
11. [Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx](#11-repsol10-control-trabajos-estructuras-y-vertidos-repsolxlsx)
12. [Repsol/12 Control Trabajos MTO REPSOL.xlsx](#12-repsol12-control-trabajos-mto-repsolxlsx)
13. [Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx](#13-repsol13-control-trabajos-puntos-de-recargaxlsx)
14. [Resumen de datos compartidos / duplicados](#14-resumen-de-datos-compartidos--duplicados)
15. [Tabla comparativa Moeve vs Repsol](#15-tabla-comparativa-moeve-vs-repsol)

---

## 1. Email_Cesar_20260407.docx

| Metadato        | Valor                           |
| --------------- | ------------------------------- |
| **Herramienta** | `python-docx`                   |
| **Formato**     | Word (.docx)                    |
| **Tipo**        | Email de instrucciones internas |

### Contenido

Email de César a Andrés explicando la estructura de los archivos Excel de control de trabajos.

### Puntos clave extraídos

- **Archivo de referencia:** "03 OBRAS Z10" es el Excel más completo y debe usarse como modelo base.
- **Modelo de negocio confirmado:** Trabajo → Contrato → Tarifa. Cada trabajo puede tener múltiples pedidos. Cada pedido tiene ítems con código de tarifa × unidades. Las facturas pueden cruzar varios pedidos.
- **Moeve vs Repsol:** Moeve tiene un Excel con control + facturas + hoja de control interno. Repsol tiene un Excel por tipo de documento.
- **Problema de concurrencia:** César advierte que el Excel actual bloquea la edición simultánea. Es uno de los motivos principales para migrar a ERP.
- **Informes:** Se mencionan como pendientes de definir.

### Implicación para la BBDD

- Confirma la relación **Trabajo → N Pedidos → N Ítems (código tarifa + unidades) → N Facturas**
- Necesidad de **control de concurrencia** (locking optimista o pesimista)
- Pendiente definir tablas de **informes/reportes**

---

## 2. Mapeo_Moeve_Repsol_Envolvente.xlsx

| Metadato        | Valor      |
| --------------- | ---------- |
| **Herramienta** | `openpyxl` |
| **Filas**       | 51         |
| **Columnas**    | 6          |
| **Hojas**       | 1          |

### Contenido

Mapeo campo a campo entre las columnas de los Excel de Moeve y Repsol. Define la "envolvente" (superset de todos los campos necesarios).

### Diferencias clave detectadas

| Campo / Aspecto               | Moeve                                    | Repsol                              |
| ----------------------------- | ---------------------------------------- | ----------------------------------- |
| Facturas por línea de trabajo | 1                                        | 1 ó 2 (1ª y 2ª factura) según tipo  |
| Nº AVISO / P.KEOPS            | No                                       | Sí                                  |
| FECHA reclamo APP             | No                                       | Sí                                  |
| ORDEN MANTENIMIENTO           | No                                       | Sí                                  |
| CÓDIGO SERVICIO               | No separado                              | Sí (campo independiente)            |
| Número Tarifa (con punto)     | No                                       | Sí (campo independiente)            |
| UDs SOLICITADAS               | No                                       | Sí                                  |
| IMPORTE SOLICITADO            | No                                       | Sí                                  |
| IMPORTE FACTURADO (calculado) | No                                       | Sí (= SUM facturas)                 |
| REV. PEDIDO COMPLETO          | No                                       | Sí (fórmula)                        |
| Pedido tiene más de 1 ítem    | No                                       | Sí (fórmula)                        |
| Pedido facturado completo     | No                                       | Sí (fórmula)                        |
| Contrato                      | Sí                                       | No (salvo hoja "OTROS" en Z10)      |
| Categoría                     | Sí                                       | No (usa TIPO DE TRABAJO)            |
| TEO                           | Existía, pero → **"No se incluirá"**     | No                                  |
| RESPONSABLE MOEVE/REPSOL      | → Unificado como **RESPONSABLE CLIENTE** |
| **Tarifario**                 | Sin factor                               | factor × Tarifa Base = Tarifa CIETE |

### Implicación para la BBDD

- Tabla `trabajos` con esquema envolvente (campos nullable para los exclusivos de cada cliente).
- Campo `responsable_cliente` genérico.
- Tabla `tarifas` con `tarifa_base`, `factor`, `tarifa_final`.
- Campos calculados: `importe_facturado`, `pedido_completo`, `pedido_tiene_mas_de_1_item`, `pedido_facturado_completo` → posibles campos computados, vistas o triggers.

---

## 3. Moeve/01 Control de Trabajos Moeve.xlsx

| Metadato        | Valor      |
| --------------- | ---------- |
| **Herramienta** | `openpyxl` |
| **Hojas**       | 5          |

### Desglose de hojas

| Hoja                  | Filas | Columnas | Contenido                                      |
| --------------------- | ----- | -------- | ---------------------------------------------- |
| **Trabajos**          | 6.686 | 127      | Listado principal de todos los trabajos Moeve  |
| **FACTURAS EMITIDAS** | 1.201 | 5        | Registro independiente de facturas emitidas    |
| **Control**           | 221   | 57       | Tabla pivote por responsable y año (2022–2026) |
| **Hoja1**             | 79    | —        | Listado de trabajos pendientes por persona     |
| **Hoja2**             | 29    | —        | Resumen por responsable con rangos de fecha    |

### Campos principales — Hoja "Trabajos"

```
Nº | ES | Nombre | LOCALIDAD | Provincia | Nº PEDIDO | IMPORTE PEDIDO |
Facturación Solicitada | FACTURADO Solo Esther | Fecha Encargo | Fecha T |
DESCRIPCION DEL SERVICIO | RESPONSABLE CIETE | RESPONSABLE MOEVE | TEO |
OBSERVACIONES | Nº Factura | Fecha factura | Contrato | Status | Categoría
```

### Campos principales — Hoja "FACTURAS EMITIDAS"

```
SOCIEDAD | Nº FACTURA CCP | Nº FACTURA CIETE | FECHA FACTURA | IMPORTE FACTURADO
```

### Implicación para la BBDD

- **6.686 trabajos a importar** para Moeve.
- Tabla `facturas` independiente (1.201 facturas con **doble numeración**: CCP y CIETE).
- Campo `contrato` en trabajos (no existe en Repsol genérico).
- Campo `categoría` es específico de Moeve.
- Tabla de **seguimiento/control** por responsable y año (posible vista materializada o tabla de reporting).

---

## 4. Moeve/02 Listado EESS España y Portugal.xlsx

| Metadato        | Valor      |
| --------------- | ---------- |
| **Herramienta** | `openpyxl` |
| **Hojas**       | 4          |

### Desglose de hojas

| Hoja                             | Filas | Columnas | Fecha      | Contenido                                          |
| -------------------------------- | ----- | -------- | ---------- | -------------------------------------------------- |
| **España 16-03-26** ⭐           | 3.583 | 92       | 16/03/2026 | **Versión más reciente** — usar esta               |
| **España 25-08-2025**            | 1.920 | 72       | 25/08/2025 | Versión anterior con @cepsa.com                    |
| **España y Portugal 26-06-2024** | 1.927 | 76       | 26/06/2024 | Versión más antigua, columnas con prefijo numérico |
| **Hoja1**                        | 1.129 | —        | —          | Datos de licencias/inspecciones por estación       |

### Campos principales — Hoja "España 16-03-26" (la buena)

```
CONCESIÓN | NOMBRE | dirección | COD.POSTAL | POBLACION | Provincia |
Nº MÁRGENES | ESTADO | Y_WGS84 | X_WGS84 | TÉCNICO GESTIÓN |
TL.TÉCNICO | E-MAIL TÉCNICO (@moeveglobal.com) | RESPONSABLE/GESTOR |
Nº TELÉFONO | TL.OFICINA | SEDE/email | Vínculo | Vínculo_2 |
F.BAJA | F.ALTA/MODIFICACION | COD RETAILGAS | COD.SOCIEDAD | SOCIEDAD
```

### Implicación para la BBDD

- **3.583 estaciones Moeve** a importar (usar versión "16-03-26").
- Campos Moeve exclusivos: `concesion`, `n_margenes`, `estado`, coordenadas GPS (`y_wgs84`, `x_wgs84`), `tecnico_gestion`, `responsable_gestor`, `cod_retailgas`, `cod_sociedad`, `sociedad`, `f_baja`, `f_alta_modificacion`.
- Estructura de estaciones Moeve es **completamente diferente** a Repsol.
- Datos de licencias/inspecciones → posible tabla `estacion_licencias`.

---

## 5. Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx

| Metadato            | Valor           |
| ------------------- | --------------- |
| **Herramienta**     | `openpyxl`      |
| **Hojas**           | 5               |
| **Tipo de factura** | Simple (1 sola) |
| **Tipo documento**  | DISEÑO          |

### Desglose de hojas

| Hoja                | Filas | Columnas | Contenido                                   |
| ------------------- | ----- | -------- | ------------------------------------------- |
| **AUTOFACTURACION** | 2.854 | 28       | Trabajos de diseño principales              |
| **ALFONSO PCN**     | 2.621 | 27       | Misma estructura, trabajos de otra persona  |
| **Rangos**          | 30    | —        | Tipos de trabajo + responsables por defecto |
| **LISTADO EESS**    | 3.275 | 65       | Listado estaciones Repsol                   |
| **TARIFA 23-27**    | 370   | 7        | Tarifario completo Repsol                   |

### Campos principales — Hoja "AUTOFACTURACION"

```
Nº | Nº ES | NOMBRE | LOCALIDAD | PROVINCIA | Nº AVISO |
FECHA SOLICITUD PEDIDO | ORDEN MANTEN. | Nº PEDIDO | FECHA ENCARGO |
TIPO DE TRABAJO | DESCRIPCION DEL TRABAJO | FECHA TERMINACIÓN |
CÓDIGO SERVICIO | DESCRIPCION DEL SERVICIO | IMPORTE UNITARIO |
UDs DEL PEDIDO | IMPORTE PEDIDO | UDs SOLICITADAS | IMPORTE SOLICITADO |
IMPORTE FACTURADO | RESPONSABLE CIETE | RESPONSABLE REPSOL |
OBSERVACIONES | FECHA SOLICITUD FACTURA | FACTURA | STATUS
```

### Tipos de trabajo (Rangos)

NPV, REFORMA GENERAL, REFORMA, FICHA TÉCNICA, TIENDA, etc.

### Implicación para la BBDD

- ~5.475 trabajos de diseño Repsol (2.854 + 2.621 de ALFONSO PCN).
- La hoja "ALFONSO PCN" confirma que **un mismo tipo de documento puede tener múltiples hojas por persona/área** → necesidad de campo `area` u `hoja_origen`.
- Factura simple (1 por línea).

---

## 6. Repsol/02 Control Trabajos EDIFICACIÓN.xlsx

| Metadato            | Valor               |
| ------------------- | ------------------- |
| **Herramienta**     | `openpyxl`          |
| **Hojas**           | 4                   |
| **Tipo de factura** | **Doble (1ª y 2ª)** |
| **Tipo documento**  | EDIFICACIÓN         |

### Desglose de hojas

| Hoja                | Filas       | Columnas | Contenido                                                    |
| ------------------- | ----------- | -------- | ------------------------------------------------------------ |
| **AUTOFACTURACION** | 1.048.548\* | 33       | Trabajos de edificación (\*máximo Excel, datos reales menos) |
| **Rangos**          | 71          | 9        | Extendido: incluye DESCRIPCION SERVICIO + IMPORTE UNITARIO   |
| **LISTADO EESS**    | 3.295       | —        | Listado estaciones Repsol                                    |
| **TARIFA 23-27**    | 370         | 8        | Tarifario Repsol                                             |

### Campos adicionales respecto a DISEÑO

```
FECHA SOLICITUD 1ª FACTURA | 1ª FACTURA | NUMERO 1ª FACTURA |
FECHA SOLICITUD 2ª FACTURA | 2ª FACTURA | NUMERO 2ª FACTURA
```

### Tipos de trabajo (Rangos)

STARBUCKS, STOP&GO, SPRINT, STOP&GO MINI, OBRAS MENORES, NORMALIZACIÓN

### Implicación para la BBDD

- Edificación usa esquema de **doble factura** (6 campos extra).
- Rangos incluye precios unitarios predefinidos por tipo → tabla `tipo_trabajo_tarifa`.
- Tipos de trabajo muy específicos de edificación.

---

## 7. Repsol/03 Control Trabajos OBRAS REPSOL Z10.xlsx — ⭐ REFERENCIA

| Metadato            | Valor               |
| ------------------- | ------------------- |
| **Herramienta**     | `openpyxl`          |
| **Hojas**           | 5                   |
| **Tipo de factura** | **Doble (1ª y 2ª)** |
| **Tipo documento**  | OBRAS               |
| **Zona**            | Z10                 |

### Desglose de hojas

| Hoja                       | Filas | Columnas | Contenido                                 |
| -------------------------- | ----- | -------- | ----------------------------------------- |
| **AUTOFACTURACION REPSOL** | 2.775 | 16.383\* | Estructura más completa de todas          |
| **OTROS**                  | 2.733 | 32       | Trabajos no estándar / contratos directos |
| **Rangos**                 | 46    | —        | 46 tipos de trabajo + responsables        |
| **LISTADO EESS**           | 3.275 | 65       | Listado estaciones Repsol                 |
| **TARIFA 23-27**           | 370   | 7        | Tarifario Repsol                          |

### Campos principales — AUTOFACTURACION REPSOL (envolvente máxima)

```
Nº | Nº ES | NOMBRE | LOCALIDAD | PROVINCIA | Nº AVISO |
FECHA SOLICITUD PEDIDO | ORDEN MANTEN. | Nº PEDIDO | FECHA ENCARGO |
TIPO DE TRABAJO | DESCRIPCION DEL TRABAJO | FECHA TERMINACIÓN TRABAJO |
CÓDIGO SERVICIO | Número Tarifa (con punto) | DESCRIPCION DEL SERVICIO |
IMPORTE UNITARIO | UDs DEL PEDIDO | IMPORTE PEDIDO | UDs SOLICITADAS |
IMPORTE SOLICITADO | IMPORTE FACTURADO |
RESPONSABLE CIETE | RESPONSABLE REPSOL | OBSERVACIONES |
FECHA SOLICITUD 1ª FACTURA | 1ª FACTURA | Nº FACTURA |
FECHA SOLICITUD 2ª FACTURA | 2ª FACTURA | Nº FACTURA |
STATUS | REV. PEDIDO COMPLETO | Pedido tiene más de 1 ítem | Pedido facturado completo
```

### Hoja "OTROS"

Trabajos no estándar con contratos directos (ej: Hidrolinera Abanto con Ibil). Misma estructura base pero para relaciones contractuales fuera del marco normal.

### Hallazgos clave

- **Número Tarifa (con punto)** es un campo DIFERENTE de CÓDIGO SERVICIO → son 2 campos distintos en la BD.
- Los 3 últimos campos (REV. PEDIDO COMPLETO, etc.) son **fórmulas Excel** → campos computados o triggers.
- La hoja "OTROS" confirma necesidad de campo `es_contrato_directo` o `tipo_contrato`.

### Implicación para la BBDD

- Este Excel define la **estructura envolvente máxima** de columnas.
- ~5.508 trabajos (2.775 + 2.733 OTROS).
- 46 tipos de trabajo distintos para Obras.

---

## 8. Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx

| Metadato                    | Valor               |
| --------------------------- | ------------------- |
| **Herramienta**             | `openpyxl`          |
| **Filas (AUTOFACTURACION)** | 2.707               |
| **Columnas**                | 35                  |
| **Tipo de factura**         | **Doble (1ª y 2ª)** |
| **Tipo documento**          | OBRAS               |
| **Zona**                    | Z50                 |

### Contenido

Idéntica estructura a Z10 pero para zona Z50.

### Implicación

Confirma que se necesita campo **`zona`** (Z10, Z50, etc.) en la tabla de trabajos.

---

## 9. Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx

| Metadato                    | Valor      |
| --------------------------- | ---------- |
| **Herramienta**             | `openpyxl` |
| **Filas (AUTOFACTURACION)** | 2.765      |
| **Columnas**                | 27         |
| **Tipo de factura**         | **Simple** |
| **Tipo documento**          | LICENCIAS  |

### Hojas

AUTOFACTURACION, Rangos, LISTADO EESS (3.282), TARIFA 23-27 (370)

### Particularidades

- Solo tipo LICENCIAS.
- Esquema reducido (27 cols vs 35 de Z10).
- Sin `Número Tarifa` separado.
- Sin doble factura.

---

## 10. Repsol/09 Control Trabajos FV REPSOL.xlsx

| Metadato                    | Valor             |
| --------------------------- | ----------------- |
| **Herramienta**             | `openpyxl`        |
| **Filas (AUTOFACTURACION)** | 2.529             |
| **Tipo de factura**         | **Simple**        |
| **Tipo documento**          | FV (Fotovoltaico) |

### Tipos de trabajo

ESTUDIO SOMBRAS, REVISIÓN ESTRUCTURAL, PROYECTO FV, PROYECTO ELÉCTRICO

### Hojas

AUTOFACTURACION, Rangos, LISTADO EESS (3.275), TARIFA 23-27 (370)

---

## 11. Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx

| Metadato                    | Valor                |
| --------------------------- | -------------------- |
| **Herramienta**             | `openpyxl`           |
| **Filas (AUTOFACTURACION)** | 2.581                |
| **Columnas**                | 31                   |
| **Tipo de factura**         | **Doble (1ª y 2ª)**  |
| **Tipo documento**          | ESTRUCTURAS_VERTIDOS |

### Tipos de trabajo

PATOLOGÍA, REVISIÓN ESTRUCTURAL, TOPOGRÁFICO, INSPECCIÓN

### Hojas

AUTOFACTURACION, Hoja1 (vacía), Rangos, LISTADO EESS (3.277), TARIFA 23-27 (370)

---

## 12. Repsol/12 Control Trabajos MTO REPSOL.xlsx

| Metadato                    | Valor               |
| --------------------------- | ------------------- |
| **Herramienta**             | `openpyxl`          |
| **Filas (AUTOFACTURACION)** | 2.434               |
| **Columnas**                | ~31                 |
| **Tipo de factura**         | **Doble (1ª y 2ª)** |
| **Tipo documento**          | MTO (Mantenimiento) |

### Tipos de trabajo

INDUSTRIA, DESMANTELAMIENTO, ADECUACIONES, SANEAMIENTO

### Hojas

AUTOFACTURACION, Rangos (44 filas), LISTADO EESS (3.275), TARIFA 23-27 (370)

### Campos (cabecera)

```
Nº | Nº ES | NOMBRE | LOCALIDAD | PROVINCIA | Nº AVISO |
FECHA SOLICITUD PEDIDO | ORDEN MANTEN. | Nº PEDIDO | FECHA ENCARGO |
TIPO DE TRABAJO | DESCRIPCION DEL TRABAJO | FECHA TERMINACIÓN TRABAJO |
CÓDIGO SERVICIO | DESCRIPCION DEL SERVICIO | IMPORTE UNITARIO |
UDs DEL PEDIDO | IMPORTE PEDIDO | UDs SOLICITADAS | IMPORTE SOLICITADO |
IMPORTE FACTURADO | RESPONSABLE CIETE | RESPONSABLE REPSOL |
OBSERVACIONES | FECHA SOLICITUD 1ª FACTURA | 1ª FACTURA | Nº FACTURA |
FECHA SOLICITUD 2ª FACTURA | 2ª FACTURA | Nº FACTURA | STATUS
```

---

## 13. Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx

| Metadato                    | Valor          |
| --------------------------- | -------------- |
| **Herramienta**             | `openpyxl`     |
| **Filas (AUTOFACTURACION)** | 3.290          |
| **Columnas**                | 26             |
| **Tipo de factura**         | **Simple**     |
| **Tipo documento**          | PUNTOS_RECARGA |

### Hojas

AUTOFACTURACION, Rangos (30), TARIFA 23-27 (370), LISTADO EESS (3.277)

### ⚠️ Particularidad importante

**NO tiene campo RESPONSABLE CIETE** — solo `RESPONSABLE REPSOL`. Esto significa que el campo `responsable_ciete` debe ser **nullable** en la BD.

### Campos (cabecera)

```
Nº | Nº ES | NOMBRE | LOCALIDAD | PROVINCIA | Nº AVISO |
FECHA SOLICITUD PEDIDO | ORDEN MANTEN. | Nº PEDIDO | FECHA ENCARGO |
TIPO DE TRABAJO | DESCRIPCION DEL TRABAJO | FECHA TERMINACIÓN TRABAJO |
CÓDIGO SERVICIO | DESCRIPCION DEL SERVICIO | IMPORTE UNITARIO |
UDs DEL PEDIDO | IMPORTE PEDIDO | UDs SOLICITADAS | IMPORTE SOLICITADO |
IMPORTE FACTURADO | RESPONSABLE REPSOL | OBSERVACIONES |
FECHA SOLICITUD FACTURA | FACTURA | STATUS
```

---

## 14. Resumen de datos compartidos / duplicados

### Datos idénticos en todos los Excel de Repsol (importar UNA sola vez)

| Dato                    | Filas aprox. | Presente en                 |
| ----------------------- | ------------ | --------------------------- |
| **LISTADO EESS Repsol** | ~3.275       | Todos los 9 Excel de Repsol |
| **TARIFA 23-27**        | 370          | Todos los 9 Excel de Repsol |

### Datos únicos por documento

| Dato                          | Documento                                                  |
| ----------------------------- | ---------------------------------------------------------- |
| **LISTADO EESS Moeve**        | Moeve/02 (3.583 filas, estructura completamente diferente) |
| **Rangos** (tipos de trabajo) | Cada Excel Repsol tiene sus propios tipos                  |
| **Hoja "OTROS"**              | Solo en Z10                                                |
| **Hoja "ALFONSO PCN"**        | Solo en DISEÑO                                             |
| **FACTURAS EMITIDAS**         | Solo en Moeve/01                                           |
| **Control / Seguimiento**     | Solo en Moeve/01                                           |

---

## 15. Tabla comparativa Moeve vs Repsol

| Aspecto                         | Moeve                                         | Repsol                                              |
| ------------------------------- | --------------------------------------------- | --------------------------------------------------- |
| **Facturas por trabajo**        | 1                                             | 1 ó 2 (según tipo de documento)                     |
| **Nº AVISO / ORDEN MANTEN.**    | No                                            | Sí                                                  |
| **Código Servicio + Nº Tarifa** | No (solo descripción libre)                   | Sí (2 campos distintos)                             |
| **Contrato**                    | Sí                                            | No (salvo hoja "OTROS" en Z10)                      |
| **Categoría**                   | Sí                                            | No (usa TIPO DE TRABAJO)                            |
| **RESPONSABLE CIETE**           | Siempre presente                              | Nullable (falta en Puntos Recarga)                  |
| **Estaciones**                  | 3.583 filas, CONCESIÓN, GPS, @moeveglobal.com | 3.275 filas, C.EMP, SOLRED, volúmenes combustible   |
| **Tarifario**                   | Sin factor multiplicador                      | factor 0.9562 × Tarifa Base                         |
| **Doble factura**               | Nunca                                         | EDIFICACIÓN, OBRAS Z10, OBRAS Z50, ESTRUCTURAS, MTO |
| **Factura simple**              | Siempre                                       | DISEÑO, LICENCIAS, FV, PUNTOS_RECARGA               |
| **Doble numeración factura**    | Sí (CCP + CIETE)                              | No (solo un número)                                 |
| **Estructura Excel**            | 1 archivo con todo                            | 1 archivo por tipo de documento                     |
| **Nº total registros trabajos** | ~6.686                                        | ~27.500 (suma de todos los Excel)                   |

### Documentos Repsol por tipo de factura

| Tipo de factura     | Documentos                                                     |
| ------------------- | -------------------------------------------------------------- |
| **Doble (1ª y 2ª)** | EDIFICACIÓN, OBRAS Z10, OBRAS Z50, ESTRUCTURAS Y VERTIDOS, MTO |
| **Simple**          | DISEÑO, LICENCIAS, FV, PUNTOS DE RECARGA                       |

---

## Conclusión

Los 13 documentos han sido leídos íntegramente usando herramientas automatizadas (`python-docx` y `openpyxl`). El análisis confirma que:

1. **No existe una estructura única** que sirva para Moeve y Repsol sin adaptación.
2. **El tarifario y listado de estaciones de Repsol están duplicados** en 9 archivos → importar una sola vez como maestro.
3. **El campo TEO se elimina** del nuevo sistema.
4. **La doble factura es condicional** según tipo de documento en Repsol.
5. **Los campos de responsable son asimétricos** (Ciete + Cliente, pero Ciete es nullable en Puntos de Recarga).
6. **El volumen total de datos es considerable**: ~34.000 trabajos + ~6.800 estaciones + 370 líneas de tarifa + 1.200 facturas Moeve.

Este análisis es la base para el documento de reestructuración de la BBDD.
