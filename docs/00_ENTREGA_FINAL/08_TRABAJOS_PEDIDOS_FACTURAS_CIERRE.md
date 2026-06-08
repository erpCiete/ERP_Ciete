# Trabajos, pedidos, facturas y cierre

## Flujo completo

```
1. Crear trabajo
       ↓
2. (Opcional) Crear pedido desde el trabajo
       ↓
3. Añadir líneas de tarifa al pedido
       ↓
4. (Ejecución MOEVE) Exportar PDF/CSV/ARIBA para solicitar pedido a MOEVE
       ↓
5. (Contabilidad) Crear factura con los ítems del pedido
       ↓
6. (Dirección) Revisar en panel de cierre y finalizar el trabajo
```

---

## 1. Trabajos

### Crear trabajo
- Desde la tabla de trabajos → "Nuevo trabajo"
- La fila aparece al principio de la tabla para edición inline
- Campos obligatorios: estación, descripción, fecha de encargo
- El número se genera automáticamente: `MOE-XXXXXX` (Moeve), `REP-XXXXXX` (Repsol), `OTR-XXXXXX` (Otros)
- El tarifario predeterminado del contrato se precarga al seleccionar la estación

### Editar trabajo
- Clic en el campo de la tabla para edición inline (modo Excel)
- O "Abrir ficha" para el formulario completo (modo Moderno)
- La edición inline y el formulario completo envían solo campos modificados junto con `updated_at`, para no pisar campos que el usuario no tocó
- El guardado tiene detección de concurrencia por campo: si otro usuario modificó ese mismo campo en los últimos 60 minutos, aparece una modal de primer plano con usuario, hora, valor anterior, valor actual y el texto que se iba a guardar
- En la modal, Cancelar conserva lo escrito sin guardar; Guardar cambios reintenta con el texto actual y vuelve a avisar si hubo otro cambio posterior

### Estados del trabajo

| Estado | Cuándo |
|--------|--------|
| `en_curso` | Sin pedido, o con pedido pero sin facturación |
| `terminado` | Terminado pero sin pedido facturable |
| `pendiente_facturar` | Hay pedido con importe, sin factura completa |
| `facturado` | Facturación completa del pedido |
| `finalizado` | Dirección lo finaliza desde el panel de cierre |
| `cancelado` | Cancelado manualmente (estado terminal) |

Los estados `finalizado` y `cancelado` son terminales: no cambian aunque varíe la facturación.

### Tarifario bloqueado
Una vez creado el primer pedido, el tarifario queda bloqueado. El combobox de tarifario en la fila cambia de editable a texto fijo.

---

## 2. Pedidos

### Crear pedido
- Desde la tabla de trabajos → "Crear pedido"
- Redirige al formulario de nuevo pedido con el trabajo preseleccionado
- El número de pedido es manual (ej: `DEMO-MOE-LA-SENYERA`)

### Añadir líneas de tarifa
- Sección "Líneas del pedido" → "Añadir línea"
- Buscar por **código** (ej: `165023`) o por **descripción** (ej: `TOMA`)
- Al seleccionar una línea, se autocompleta: descripción, precio unitario
- La cantidad puede ser decimal (ej: 2.5 ud)
- El total de línea y el total del pedido se recalculan automáticamente

### Múltiples pedidos por trabajo
Un trabajo puede tener más de un pedido (caso excepcional). El botón "Crear otro pedido" permite añadir un segundo pedido al mismo trabajo.

---

## 3. Facturas

### Crear factura
- Desde `/facturas` → "Nueva factura"
- O desde el trabajo → sección de facturas

El formulario muestra los **ítems facturables disponibles** (ítems de pedidos no completamente facturados). Se pueden seleccionar ítems de distintos pedidos y trabajos, siempre del mismo contrato.

### Facturación parcial
Selecciona solo algunos ítems. El trabajo queda en estado `pendiente_facturar`.

### Facturación completa
Selecciona todos los ítems pendientes. El trabajo pasa a `facturado`.

### Cuadre de factura
La factura muestra la diferencia entre el importe declarado (Cta. Mayor) y el importe de los ítems asignados. Una factura está "cuadrada" cuando diferencia = 0.

### Anular factura
Botón "Anular factura" con confirmación. El estado pasa a "Anulada" y los ítems vuelven a estar disponibles.

---

## 4. Panel de cierre

Acceso: `/cierre` (solo Dirección)

### Qué muestra
- Trabajos en proceso de cierre con su estado de validación
- Contadores: pendientes de revisión, listos para finalizar, bloqueados
- Checklist de cada trabajo: fechas, importes, legalizaciones, incidencias

### Flujo de cierre
1. Trabajo llega a estado `facturado`
2. Aparece en el panel con estado "Pendiente de revisión"
3. Dirección revisa la checklist
4. Si todo está OK: estado "Listo para finalizar"
5. Dirección hace clic en "Finalizar" → trabajo pasa a `finalizado`

### Trabajos bloqueados
Si un trabajo tiene incidencias (sin fecha de terminación, legalizaciones pendientes, importe no cuadrado), aparece como "Bloqueado" con el motivo específico.

### Finalización masiva
Se pueden seleccionar varios trabajos "Listos para finalizar" y finalizarlos en bloque.
