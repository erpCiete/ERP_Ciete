> Documento preparado para impresion en blanco y negro.
>
> ERP CIETE v2.1.0
> Documento de apoyo para reunion
> Entorno: http://130.110.232.84/

---

# ERP CIETE v2.1.0

## Resumen de avances para revision funcional

Datos de cabecera:

- Version: ERP CIETE v2.1.0
- Entorno de demostracion: http://130.110.232.84/
- Objetivo: revision practica del funcionamiento diario del ERP
- Fecha: 2026-05-19

---

## 1. Objetivo de esta version

Esta version esta orientada a validar el trabajo diario con una base operativa real. El enfoque principal es revisar, en un flujo continuo y entendible, como se conecta la operativa desde el trabajo hasta el cierre.

Incluye:

- uso de datos reales importados;
- roles diferenciados para operativa, revision y administracion tecnica;
- separacion por contexto (MOEVE, REPSOL y OTROS CLIENTES);
- revision de trabajos, pedidos, facturas, cierre y soporte administrativo.

---

## 2. Que se ha incorporado desde la revision anterior

[OK] Carga de datos reales desde Excel.

[OK] Mejora del flujo trabajo -> pedido -> items -> factura.

[OK] Validacion de sociedad/CIF antes de confirmar facturacion.

[OK] Separacion funcional por contexto de trabajo.

[OK] Vista Ciete Excel para alto volumen.

[OK] Vista Ciete Moderno para revision guiada.

[OK] Panel de cierre para Direccion.

[OK] Administracion tecnica para usuarios, soporte y avisos.

[OK] Mensajes de inicio visibles para comunicacion interna.

[OK] Estado del sistema accesible desde administracion.

[OK] Paginacion rapida para navegacion de listados amplios.

[OK] Trazabilidad y auditoria de cambios operativos.

---

## 3. Datos reales cargados

| Dato                      | Volumen aproximado |
| ------------------------- | -----------------: |
| Trabajos                  |            11.600+ |
| Pedidos                   |             9.600+ |
| Items de pedido           |             9.700+ |
| Facturas                  |             2.700+ |
| Items de factura          |             9.100+ |
| Importaciones registradas |                 11 |

> Los datos proceden de los Excel operativos revisados para MOEVE y REPSOL.

---

## 4. Flujo principal de trabajo

Trabajo
-> Pedido
-> Items
-> Factura
-> Revision / Cierre

Descripcion breve por fase:

- Trabajo: unidad operativa base donde comienza la gestion.
- Pedido: registro economico asociado al trabajo.
- Items: lineas economicas que definen cantidad, concepto e importe.
- Factura: agrupacion de items con validacion de sociedad/CIF.
- Revision / Cierre: control funcional para detectar pendientes y completar circuito.

---

## 5. Roles contemplados

| Rol                    | Uso principal                                                 |
| ---------------------- | ------------------------------------------------------------- |
| Direccion              | Revision, cierre, trazabilidad y control funcional            |
| Contabilidad           | Pedidos, facturas, sociedad/CIF y facturacion                 |
| Ejecucion MOEVE        | Gestion operativa de trabajos MOEVE                           |
| Ejecucion REPSOL       | Gestion operativa de trabajos REPSOL                          |
| Usuario multicontexto  | Cambio entre contextos permitidos                             |
| Administracion tecnica | Usuarios, soporte, mantenimiento, avisos y estado del sistema |

---

## 6. Contextos de trabajo

El ERP organiza la operativa en tres contextos:

- MOEVE;
- REPSOL;
- OTROS CLIENTES.

No se opera desde una vista global para crear o editar. Cada contexto mantiene sus datos separados y su flujo operativo propio.

---

## 7. Modulos principales disponibles

- Inicio;
- Trabajos;
- Pedidos;
- Facturas;
- Maestros;
- Cierre;
- Administracion;
- Soporte;
- Mensajes;
- Estado del sistema.

---

## 8. Que se revisara en la demostracion

[PASO] Acceso al entorno y entrada al sistema.

[PASO] Pantalla de inicio y mensajes activos.

[PASO] Cambio de contexto y separacion de datos.

[PASO] Revision de trabajos reales.

[PASO] Revision de pedidos e items.

[PASO] Revision de facturas y validacion de sociedad/CIF.

[PASO] Consulta del panel de cierre para Direccion.

[PASO] Recorrido por administracion tecnica.

[PASO] Uso de paginacion rapida en listados amplios.

[PASO] Diferencia de uso entre Ciete Excel y Ciete Moderno.

---

## 9. Cierre

Este documento resume los avances principales de ERP CIETE v2.1.0 para facilitar una revision practica y ordenada durante la reunion.

---

Documento de apoyo para revision funcional del ERP CIETE v2.1.0.
