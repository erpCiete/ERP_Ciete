# Base de datos: estructura y decisiones

## Diagrama de dominio

```
Contexto (MOEVE=1 / REPSOL=2 / OTROS=3)
  └── Empresa / Cliente
        ├── EstacionServicio  (código, nombre, municipio, provincia)
        │     └── EstacionMoeveExt  (datos operativos MOEVE: cod_sociedad, tecnico, etc.)
        └── Contrato
              ├── ariba_cta_mayor, ariba_propuesta_opex, ariba_accion_gasto
              ├── ariba_nombre_proveedor, ariba_sociedad
              └── Tarifario (predeterminado: uno por contrato)
                    └── TarifarioLinea (código, descripción, precio, unidad)

Trabajo (número autogenerado: MOE-XXXXXX / REP-XXXXXX / OTR-XXXXXX)
  ├── id_contexto
  ├── id_empresa_cliente
  ├── id_estacion_servicio
  ├── id_tarifario  (se bloquea al crear el primer pedido)
  ├── id_contrato
  ├── estado (derivado automáticamente)
  └── Pedido (numero_pedido manual)
        └── PedidoItem (id_tarifario_linea, cantidad, precio_unitario, total_linea)
              └── FacturaItem → Factura
                    └── CierreTrabajo (panel de dirección)
```

---

## Por qué existen los contextos

Ciete trabaja con dos grandes clientes energéticos (MOEVE y REPSOL) que tienen formatos, tarifas y flujos distintos. El contexto permite:

- **Aislamiento de datos**: un usuario de MOEVE no ve los trabajos de REPSOL.
- **Formatos específicos**: la exportación ARIBA solo aplica a MOEVE.
- **Tarifas distintas**: cada contrato tiene sus propias líneas de tarifa.
- **Numeración separada**: los trabajos llevan prefijo según contexto.

Un tercer contexto (OTROS CLIENTES) existe para proyectos con clientes menores que no necesitan el flujo ARIBA.

---

## Por qué el tarifario es único por trabajo

Una vez creado el primer pedido, el tarifario queda bloqueado. Esto es una decisión de negocio: el precio acordado con el cliente al inicio del trabajo no debe cambiar retroactivamente afectando las facturas ya emitidas.

Si el precio cambia (nuevo contrato marco), se crea un nuevo trabajo.

---

## Por qué la sociedad facturadora se resuelve en la factura

Ciete puede facturar a Repsol/MOEVE bajo diferentes CIFs (distintas sociedades del grupo). El contrato define qué sociedades están autorizadas. La factura concreta determina bajo qué sociedad/CIF se emite.

En ARIBA, el campo Sociedad se toma de:
1. `estaciones_moeve_ext.cod_sociedad` (preferente, por estación)
2. `contratos.ariba_sociedad` (fallback si la estación no tiene datos MOEVE)

---

## Por qué los estados son derivados

Un trabajo tiene un campo `estado` pero su valor real se recalcula cada vez que:
- Un pedido cambia de estado
- Una factura se emite o anula

Estados posibles y su lógica:

| Estado | Cuándo aparece |
|--------|---------------|
| `en_curso` | No hay pedido o el trabajo no está terminado |
| `terminado` | Trabajo marcado como terminado pero sin pedido facturable |
| `pendiente_facturar` | Hay pedido pero no se ha facturado todo |
| `facturado` | Importe facturado ≥ importe pedido |
| `finalizado` | Dirección cierra el trabajo desde el panel de cierre |
| `cancelado` | Estado terminal, no se recalcula |

`cancelado` y `finalizado` son terminales: una vez alcanzados, no se recalculan aunque cambie la facturación.

---

## Campos ARIBA en contratos

Añadidos el 2026-06-04 en la migración `add_ariba_fields_to_contratos`:

| Campo | Uso en exportación |
|-------|------------------|
| `ariba_cta_mayor` | Columna "Cuenta de mayor" en CSV |
| `ariba_propuesta_opex` | Cabecera ARIBA: Propuesta de Inversión / Opex |
| `ariba_accion_gasto` | Cabecera ARIBA: Acción de gasto AC |
| `ariba_nombre_proveedor` | Columna "Proveedor / Contrato" en CSV y ARIBA |
| `ariba_sociedad` | Fallback de Sociedad cuando la estación no tiene `cod_sociedad` |

Se configuran desde Maestros → Contratos → Editar → bloque "Datos ARIBA / Solicitud Moeve".

---

## Migraciones importantes recientes

| Migración | Fecha | Qué hace |
|-----------|-------|---------|
| `add_ariba_fields_to_contratos` | 2026-06-04 | 4 campos ARIBA en contratos |
| `add_ariba_sociedad_to_contratos` | 2026-06-04 | Fallback de Sociedad |
| `add_pendiente_facturar_to_trabajos` | 2026-05-x | Nuevo estado derivado |
| Migraciones de audit_logs | 2026-05-x | Auditoría completa |

---

## Scripts SQL finales (database/manual/)

| Script | Uso |
|--------|-----|
| `2026_06_02_reset_demo_integral_muestra_reducida.sql` | Reset completo a datos demo actuales. Solo local. |
| `2026_06_01_reset_demo_operativa_minima.sql` | Reset a datos mínimos para pruebas básicas. Solo local. |

Scripts obsoletos (movidos a historial o no ejecutar):
- `2026_05_07_insert_muestra_operativa_50_casos.sql` — datos de prueba tempranos
- `2026_05_18_insert_muestra_flujo_diario_controlado.sql` — datos de validación B1
- `2026_05_19_insert_muestra_b2_validacion_flujo_diario.sql` — datos de validación B2
- `2026_05_19_delete_muestra_b2_validacion_flujo_diario.sql` — limpieza de B2

---

## Qué no tocar sin revisar

- **`contratos.id_tarifario_predeterminado`**: Si hay pedidos activos, cambiar el tarifario predeterminado no afecta los pedidos existentes, pero sí los nuevos. Revisar siempre.
- **`estados` de trabajos en producción**: No actualizar directamente en SQL. Usar el sistema de estados derivados.
- **`migraciones`**: No borrar. No revertir si hay datos reales.
- **`audit_logs`**: No borrar. Son la trazabilidad legal del sistema.
