# ERP CIETE v2.1.0
## Resumen ejecutivo para presentación

**Documento de una lectura — Mayo 2026**

> Entorno de revisión funcional: **http://130.110.232.84/**

---

## 1. Qué es ERP CIETE v2.1.0

El ERP CIETE v2.1.0 es el sistema de gestión operativa de CIETE. Centraliza el registro, seguimiento y facturación de los trabajos realizados por la empresa en estaciones de servicio MOEVE, REPSOL y otros clientes.

El sistema está organizado en tres **contextos** de trabajo completamente separados:

| Contexto | Ámbito |
|---|---|
| **MOEVE** | Trabajos y estaciones del cliente MOEVE |
| **REPSOL** | Trabajos y estaciones del cliente REPSOL |
| **OTROS CLIENTES** | Trabajos de otros clientes |

---

## 2. Qué cubre el sistema

| Área | Qué hace el ERP |
|---|---|
| **Trabajos** | Registro de cada actuación realizada. Es el eje del sistema. |
| **Pedidos** | Solicitud económica vinculada al trabajo. Puede existir antes o después. |
| **Ítems** | Líneas tarifadas del pedido. Unidad mínima de facturación. |
| **Facturas** | Agrupación de ítems, parcial o completa. Con validación de sociedad/CIF. |
| **Maestros** | Datos de referencia: clientes, estaciones, contratos, tarifas, sociedades. |
| **Cierre** | Revisión por Dirección: control de trabajos sin cobrar. |
| **Auditoría** | Trazabilidad de modificaciones: quién, qué, cuándo. |
| **Mensajes** | Avisos internos y comunicados desde la pantalla de inicio. |
| **Soporte** | Canal de incidencias técnicas gestionado por el administrador. |

---

## 3. Roles del sistema

| Perfil | Función principal |
|---|---|
| **Dirección** | Panel de cierre, revisión económica de trabajos, trazabilidad. |
| **Contabilidad** | Pedidos, facturas y validación de sociedad/CIF. |
| **Ejecución MOEVE** | Trabajos y pedidos del contexto MOEVE. |
| **Ejecución REPSOL** | Trabajos y pedidos del contexto REPSOL, con campos específicos. |
| **Usuario multicontexto** | Acceso a varios contextos según asignación. |
| **Administración técnica** | Usuarios, soporte, avisos, estado del sistema. No opera trabajos ni facturas. |

> Cada perfil solo accede a lo que necesita para su función.

---

## 4. Flujo diario

El flujo estándar de trabajo es siempre el mismo:

```
Trabajo → Pedido → Ítems → Factura → Cierre
```

1. Se registra o revisa un **trabajo** en el contexto correspondiente.
2. Se asocia el **pedido** con los datos económicos.
3. Se revisan los **ítems**: conceptos, cantidades, importes según tarifa.
4. Se genera la **factura**, validando que la sociedad/CIF sea la correcta.
5. El perfil de **Dirección** revisa en el panel de cierre que ningún trabajo quede sin cobrar.

---

## 5. Datos reales cargados

El sistema tiene cargado el historial operativo real de CIETE:

| Entidad | Cantidad |
|---|---|
| Trabajos | más de 11.600 |
| Pedidos | más de 9.600 |
| Ítems de pedido | más de 9.700 |
| Facturas | más de 2.700 |
| Ítems de factura | más de 9.100 |

Los datos provienen de los archivos de control de trabajos de MOEVE y de las distintas categorías de trabajos REPSOL (Diseño, Edificación, Obras, Licencias, Estructuras, Mantenimiento, FV, Puntos de Recarga), así como del listado real de estaciones de servicio.

---

## 6. Módulos principales

### Ciete Excel
Modo de trabajo diario con tablas densas, edición por celda, paginación rápida y acceso directo a una página concreta. Incluye aviso de conflicto cuando otro usuario modifica un campo recientemente.

### Ciete Moderno
Modo de presentación visual con tarjetas y vistas de detalle. Los mismos datos, presentados de forma más clara. Recomendado para revisiones y análisis.

### Maestros
Base de datos de referencia del sistema. Sin maestros correctamente configurados (estaciones, contratos, tarifas, sociedades facturadoras), el flujo operativo no puede completarse.

### Panel de cierre
Herramienta de Dirección para verificar que ningún trabajo terminado quede sin pedido ni sin factura. Incluye listado de trabajos sin pedido, sin factura y con factura parcial.

### Administración técnica
Gestión de usuarios, soporte, mensajes de inicio y estado del sistema. Perfil separado de la operativa diaria.

---

## 7. Qué se puede revisar en la demostración

| Qué revisar | Dónde verlo |
|---|---|
| Datos reales de MOEVE cargados | Trabajos → contexto MOEVE |
| Datos reales de REPSOL cargados | Trabajos → contexto REPSOL |
| Campos específicos REPSOL | Detalle de trabajo REPSOL |
| Pedidos e ítems tarifados | Módulo de Pedidos |
| Facturas con sociedad/CIF | Módulo de Facturas |
| Control de cierre | Panel de Cierre (Dirección) |
| Maestros de referencia | Módulo de Maestros |
| Mensajes de inicio | Pantalla de inicio |
| Estado del sistema | Panel de Administración técnica |

---

## 8. Cierre

El ERP CIETE v2.1.0 está preparado para dar soporte al trabajo diario de CIETE con datos reales, flujo operativo completo y roles diferenciados. La siguiente fase es confirmar los datos definitivos de maestros y usuarios con el equipo de CIETE para dejar el sistema listo para operación.

> Para cualquier consulta sobre el sistema, contactar con el administrador técnico del entorno.

---

*ERP CIETE v2.1.0 — Documento de presentación — Mayo 2026*
