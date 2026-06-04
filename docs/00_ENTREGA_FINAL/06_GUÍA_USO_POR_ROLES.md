# Guía de uso por roles

## Resumen de accesos

| Módulo | Dirección | Ejecución | Contabilidad | Admin técnico |
|--------|-----------|-----------|-------------|--------------|
| Trabajos (ver) | ✅ | ✅ | ❌ | ✅ |
| Trabajos (crear/editar) | ✅ | ✅ | ❌ | ✅ |
| Pedidos (ver) | ✅ | ✅ | Solo consulta | ✅ |
| Pedidos (crear/editar) | ✅ | ✅ | ❌ | ✅ |
| Facturas | ✅ (ver) | ❌ | ✅ (total) | ✅ |
| Cierre | ✅ | ❌ | ❌ | ❌ |
| Maestros (ver) | ✅ | ❌ | ❌ | ✅ |
| Maestros (editar) | ✅ | ❌ | ❌ | ✅ |
| Soporte | ❌ (403) | ❌ (403) | ❌ (403) | ✅ |
| Estado técnico | ❌ (403) | ❌ (403) | ❌ (403) | ✅ |
| Auditoría | ✅ | ❌ | ❌ | ✅ |
| Usuarios | ✅ (ver) | ❌ | ❌ | ✅ (total) |

---

## Dirección / César (`cesar@ciete.es`)

### Pantalla principal: Trabajos
- Cambia el contexto desde Perfil (Moeve / Repsol / Otros) según con qué cliente trabaje hoy.
- La tabla muestra todos los trabajos con estado, importes y pedidos.
- Puede filtrar por estado, responsable, estación, municipio, provincia.
- Puede ordenar por cualquier columna.

### Panel de cierre (`/cierre`)
- Ve todos los trabajos en proceso de cierre.
- Muestra: pendientes de revisión, listos para finalizar, bloqueados.
- Puede marcar trabajos para revisión (individualmente o en bloque).
- Puede finalizar trabajos que están "Listos para finalizar" (checklist completa).
- **No puede finalizar trabajos bloqueados** — muestra el motivo.

### Maestros (`/maestros`)
- Ve y puede editar el árbol: Empresa → Sociedad/CIF → Contrato → Tarifario → Líneas.
- Puede cambiar el tarifario predeterminado de un contrato.
- Puede editar los **datos ARIBA** del contrato (Maestros → Contratos → Editar → bloque "Datos ARIBA / Solicitud Moeve").

### Auditoría (`/registro-actividad`)
- Ve todos los cambios del sistema: quién modificó qué campo, cuándo y qué valor tenía antes.
- Puede filtrar por usuario, módulo, acción, campo, fecha.

### Lo que NO puede hacer
- Acceder a `/soporte` (403)
- Acceder a `/estado` (403)
- Crear usuarios

---

## Ejecución (`usuario@ciete.es`, `moeve@ciete.es`, `repsol@ciete.es`)

### Flujo de trabajo diario

**1. Crear trabajo**
- Ir a Trabajos → Nuevo trabajo
- La fila aparece en la tabla para editar inline
- Buscar estación por código o nombre
- El tarifario se precarga automáticamente al seleccionar la estación
- Añadir descripción y guardar

**2. Crear pedido desde el trabajo**
- En la fila del trabajo, clic en "Crear pedido"
- Ir a la ficha del pedido
- Añadir líneas de tarifa buscando por código o descripción
- La cantidad puede ser decimal
- El total se recalcula automáticamente
- Guardar pedido

**3. Exportar (Ejecución Moeve)**
- En la ficha del pedido, sección "Exportación Moeve"
- Botones: PDF Moeve, CSV Moeve, Cuadro ARIBA
- Botón "✉ Preparar correo Moeve" para generar asunto, cuerpo copiable y checklist

### Reglas importantes para Ejecución
- **El tarifario se bloquea al crear el primer pedido** — elige bien antes.
- **Puedes cambiar el tarifario predeterminado antes del primer pedido** si lo necesitas.
- **No puedes cerrar trabajos** — eso es de Dirección.
- El número de trabajo se genera automáticamente (MOE-XXXXXX para Moeve).

---

## Contabilidad (`contable@ciete.es`)

### Pedidos
- Puede ver el listado de pedidos con filtros.
- Puede abrir la ficha para ver las líneas e importes.
- **No puede crear ni editar pedidos** — eso es de Ejecución.

### Facturas (`/facturas`)
- Puede crear facturas desde ítems de pedidos pendientes.
- Selecciona los ítems que quiere facturar (parcial o completo).
- Elige la sociedad/CIF facturadora.
- Puede facturar ítems de diferentes pedidos del mismo contrato.
- **La factura queda cuadrada** cuando el importe asignado = importe total declarado.

**Crear factura parcial:**
1. Nueva factura → seleccionar los ítems que se facturan ahora.
2. El importe se calcula automáticamente.
3. Guardar → el trabajo queda con estado "pendiente_facturar" si no está completo.

**Crear factura completa:**
1. Seleccionar todos los ítems pendientes.
2. El trabajo pasa a estado "facturado" automáticamente.

### Anular factura
- Botón "Anular factura" con confirmación.
- La factura queda en estado "Anulada" con trazabilidad conservada.
- Los ítems vuelven a estar disponibles para facturar.

### Exportar listado de facturas
- Botón "Exportar listado" descarga CSV con todas las facturas filtradas.
- Columnas: nº factura, contexto, cliente, sociedad, CIF, fecha, estado, total, asignado, diferencia, cuadre, pedidos, trabajos, contrato, tarifario.

### Lo que NO puede hacer
- Acceder a Trabajos (no tiene permiso)
- Acceder a `/soporte` (403)
- Acceder a `/estado` (403)

---

## Admin técnico (`admin@ciete.es`)

### Soporte (`/soporte`, `/admin/soporte`)
- Ve todos los tickets abiertos con filtros por estado y prioridad.
- Puede comentar y cambiar el estado de cualquier ticket.
- Puede archivar tickets resueltos.

### Estado del sistema (`/estado`)
- Panel de monitorización: BD, disco, correo, cola, app, mantenimiento.
- Muestra timestamp de última comprobación.
- Solo accesible para admin técnico.

### Usuarios (`/admin/usuarios`)
- Lista completa con nombre, email, contextos, roles y estado.
- Puede editar y desactivar usuarios.
- Puede crear nuevos usuarios con rol y contexto.

### Auditoría (`/registro-actividad`)
- Acceso completo al log de auditoría.
- Puede exportar el log a CSV/XLSX.
- Puede limpiar registros si el volumen es excesivo.

### Importaciones (`/importaciones`)
- Puede subir archivos Excel para importar datos (trabajos, estaciones, tarifarios).
- La importación tiene un paso de preview/dry-run antes de confirmar.
- **CSV no está completamente soportado** (P1) — usa XLSX.

### Maestros técnicos
- Acceso completo a todos los maestros.
- Puede editar los campos ARIBA en contratos.
- Puede desactivar contratos, tarifarios y estaciones.
