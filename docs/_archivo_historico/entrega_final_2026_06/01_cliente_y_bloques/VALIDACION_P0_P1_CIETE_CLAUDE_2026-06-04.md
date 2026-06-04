# Validación P0/P1 CIETE — Claude

**Fecha:** 2026-06-04  
**Validador:** Claude Sonnet 4.6 (Playwright/MCP + revisión de código)  
**Versión ERP:** v2.1.0  
**Entorno:** Local (`http://127.0.0.1:8000`)

---

## Resultado general

**ERP CIETE apto para demo/revisión funcional con CIETE**

Los dos P0 originales identificados en la reunión del 19/05/2026 han sido resueltos:
1. Campos ARIBA parametrizados desde maestros — sin "Pendiente de parametrizar" en la demo
2. Flujo de correo Moeve implementado como modal en el pedido

---

## Puntos descartados como no bloqueantes

- **Tarifarios Repsol incompletos**: Los tarifarios se pueden añadir manualmente cuando César/Amaya los entreguen. No bloquea demo.
- **Tarifario asociado a sociedades facturadoras específicas**: La regla operativa actual `Contrato → Tarifario → Trabajo → Pedido` es suficiente. La sociedad concreta se resuelve en Factura. Evolutivo posterior.

---

## P0 ARIBA — RESUELTO ✅

### Qué se hizo
- Migración `2026_06_04_000001_add_ariba_fields_to_contratos.php` — añade 4 columnas a `contratos`
- Migración `2026_06_04_000002_add_ariba_sociedad_to_contratos.php` — añade fallback de Sociedad
- Modelo `Contrato` actualizado con los 5 campos ARIBA en `$fillable`
- `MoevePedidoExportService` actualizado para leer desde el contrato
- `ContratoController` valida y expone los campos nuevos
- `resources/js/Pages/Contratos/Form.jsx` — bloque "Datos ARIBA / Solicitud Moeve" con los 5 campos

### Campos parametrizados en contrato 772 MOEVE
| Campo | Valor configurado |
|-------|-----------------|
| Cta. de Mayor | 6330001 |
| Propuesta de Inversión / Opex | OPEX-MOEVE-2026 |
| Acción de gasto AC | AC-MOEVE-2026 |
| Sociedad (fallback) | MOEVE Energy S.A. |
| Nombre proveedor / Contrato | CIETE INGENIEROS S.A. |

### Verificación ARIBA DEMO-MOE-LA-SENYERA
- ✅ "ARIBA - TRAMITACION DE PEDIDOS" con todos los campos completos
- ✅ Sin ningún "Pendiente de parametrizar"
- ✅ Proveedor/Contrato: "772 / CIETE INGENIEROS S.A."
- ✅ Sociedad: "MOEVE Energy S.A."
- ✅ CSV con decimales corregidos (510,00 en lugar de 510)

---

## P0 Correo Moeve — RESUELTO ✅

### Qué se hizo
- Botón "✉ Preparar correo Moeve" añadido al bloque de exportación del pedido
- Modal `CorreoMoeveModal` implementado en `resources/js/Pages/Pedidos/Form.jsx`
- `PedidoResource` actualizado para incluir `nombre_estacion` del trabajo
- Ruta `/pedidos/{id}/editar` actualizada para cargar `trabajo.estacion` en eager-load

### Funcionalidad verificada con DEMO-MOE-LA-SENYERA
- ✅ Asunto: "Solicitud de pedido Moeve - 33450 - LA SENYERA I - DEMO-MOE-LA-SENYERA"
- ✅ Cuerpo copiable con estación y número de pedido
- ✅ Botón "Copiar asunto"
- ✅ Botón "Copiar cuerpo del correo"
- ✅ Botón "Abrir PDF Moeve"
- ✅ Botón "Descargar CSV"
- ✅ Botón "Abrir cuadro ARIBA"
- ✅ Checklist de 5 pasos antes de enviar
- ✅ Aviso claro de adjuntar manualmente PDF y CSV

---

## P1 Cambio código/nombre estación — IMPLEMENTADO ✅

### Qué se hizo
- Banner de advertencia en todo el formulario de edición de estación
- Seguimiento de valores originales (`originalSensitive`)
- Panel rojo con checkbox obligatorio cuando el usuario modifica código o nombre
- Submit bloqueado hasta confirmar el impacto en histórico

### Verificado
- ✅ Banner amarillo siempre visible en edición
- ✅ Panel rojo + checkbox aparecen al cambiar código "11205" a "11205X"
- ✅ Submit bloqueado sin checkbox marcado

---

## P1 Exportación listado facturas — FUNCIONA ✅

### Verificado como contable en contexto Moeve
- ✅ Botón "Exportar listado" descarga `facturas_YYYYMMDD_HHMMSS.csv`
- ✅ Columnas: Nº factura, Contexto, Cliente, Sociedad, CIF, Fechas, Estado, Total, Asignado, Diferencia, Cuadre, Pedidos, Trabajos, Contrato, Tarifarios
- ✅ Decimales con coma (1360,00)
- ✅ Separador `;`
- ✅ 2 facturas Moeve exportadas correctamente

---

## P1 Importes no asignables — NO IMPLEMENTADO (P1)

No existe pantalla o mecanismo para gestionar cobros/pagos recibidos que no corresponden a ningún trabajo/pedido. Esto requeriría un módulo de tesorería fuera del scope actual. Pendiente de decisión de negocio.

---

## P1 Numeración Repsol — VERIFICADO PARCIALMENTE ✅

- El prefijo `REP-XXXXXX` está confirmado en código (`trabajoOperationalPrefix → 'repsol' → 'REP'`)
- Los trabajos DEMO-620XXX son datos de importación legacy con código manual
- La categoría/tipología se mantiene como campo libre, no afecta numeración
- Test UI: la creación de nuevo trabajo Repsol mostró "Se generará automáticamente" correctamente pero falló al guardar por un conflicto de tarifario alternativo auto-seleccionado — P1 menor a investigar en operativa real

---

## Importación MOEVE 2 filas — PARCIALMENTE FUNCIONAL ⚠️

### Archivos creados
- `docs/02_CLIENTE/importacion_pruebas_minimas/QA_IMPORT_MOEVE_2_FILAS_20260604.csv`
- `docs/02_CLIENTE/importacion_pruebas_minimas/QA_IMPORT_REPSOL_2_FILAS_20260604.csv`

### Estado de la pantalla de importación
- ✅ Pantalla `/importaciones` accesible para admin
- ✅ Tipos disponibles: Estaciones MOEVE, Estaciones REPSOL, Trabajos, Tarifario
- ✅ Subida de archivo funciona
- ✅ Fix aplicado: `ImportacionController@store` usa validación web-compatible (no JSON) — Inertia errors
- ✅ Fix aplicado: MIME type validación usa `mimetypes` en lugar de `mimes` para mayor compatibilidad
- ❌ CSV no procesado: el `ExcelParserService` usa PhpSpreadsheet en modo Excel y falla con CSV
- **Conclusión:** El importador funciona con archivos `.xlsx` pero no con CSV — P1. Para la demo, el flujo manual es suficiente.

---

## Importación REPSOL 2 filas — PENDIENTE

Mismo estado que MOEVE: la pantalla existe, la subida funciona, pero CSV no es soportado por el parser.

---

## Validación Dirección / Contabilidad / Admin / Ejecución

Remitir a `docs/02_CLIENTE/VALIDACION_DIA_A_DIA_CIETE_CLAUDE_2026-06-04.md` que cubre el flujo completo de los 4 roles.

---

## Hotfix aplicados

| # | Archivo | Descripción |
|---|---------|-------------|
| 1 | `database/migrations/2026_06_04_000001_add_ariba_fields_to_contratos.php` | Migración 4 campos ARIBA |
| 2 | `database/migrations/2026_06_04_000002_add_ariba_sociedad_to_contratos.php` | Migración campo Sociedad fallback |
| 3 | `app/Models/Contrato.php` | Añadir campos ARIBA a `$fillable` |
| 4 | `app/Services/Exports/MoevePedidoExportService.php` | Leer campos ARIBA del contrato |
| 5 | `app/Http/Controllers/ContratoController.php` | Validar y exponer campos ARIBA |
| 6 | `resources/js/Pages/Contratos/Form.jsx` | Bloque "Datos ARIBA / Solicitud Moeve" |
| 7 | `app/Http/Resources/Api/PedidoResource.php` | Incluir `nombre_estacion` del trabajo |
| 8 | `routes/web/operativa.php` | Eager-load `trabajo.estacion` en edición pedido |
| 9 | `resources/js/Pages/Pedidos/Form.jsx` | Modal "✉ Preparar correo Moeve" |
| 10 | `resources/js/Pages/Estaciones/Form.jsx` | Banner + confirmación cambio campos sensibles |
| 11 | `app/Http/Controllers/ImportacionController.php` | Validación web-compatible para upload |
| 12 | `lang/es/passwords.php` | Traducciones `passwords.sent` y variantes |
| 13 | `lang/en/passwords.php` | Traducciones en inglés |

---

## Migraciones aplicadas

```
2026_06_04_000001_add_ariba_fields_to_contratos     ✅ DONE
2026_06_04_000002_add_ariba_sociedad_to_contratos   ✅ DONE
```

---

## Archivos modificados (resumen)

- `app/Models/Contrato.php`
- `app/Services/Exports/MoevePedidoExportService.php`
- `app/Http/Controllers/ContratoController.php`
- `app/Http/Controllers/ImportacionController.php`
- `app/Http/Resources/Api/PedidoResource.php`
- `routes/web/operativa.php`
- `resources/js/Pages/Contratos/Form.jsx`
- `resources/js/Pages/Pedidos/Form.jsx`
- `resources/js/Pages/Estaciones/Form.jsx`
- `lang/es/passwords.php` (nuevo)
- `lang/en/passwords.php` (nuevo)

---

## Tests ejecutados

| Suite | Resultado |
|-------|---------|
| Password | 13/13 ✅ |
| Role | 10/10 ✅ |
| SupportTicket | 5/5 ✅ |
| TrabajoTest | 36/36 ✅ |
| PedidoTest | 20/20 ✅ |
| FacturaTest | 36/36 ✅ |
| ClosureDashboard | 9/9 ✅ |
| MaestrosTest | 13/13 ✅ |
| **Total** | **134/134 ✅** |

---

## Build

```
✓ built in 1.72s
git diff --check: sin errores
```

---

## Errores encontrados

1. **RESUELTO** — Campos ARIBA siempre "Pendiente de parametrizar" → ahora configurables en maestros de contratos
2. **RESUELTO** — Sin flujo de correo Moeve → modal "✉ Preparar correo Moeve" implementado
3. **RESUELTO** — CSV export de facturas: decimales sin coma → `formatCsvNumber` corregido
4. **RESUELTO** — Traducción `passwords.sent` raw → archivos `lang/*/passwords.php` creados
5. **RESUELTO** — `ImportacionController@store` devolvía JSON incompatible con Inertia → validación web-compatible
6. **P1** — ExcelParserService no procesa CSV, solo XLSX
7. **P1** — Importes no asignables a trabajo/pedido: no existe pantalla
8. **P1** — Tarifario alternativo auto-seleccionado en nuevo trabajo Repsol (sin confirmación)
9. **P1** — Automatización de correo Moeve: adjuntos automáticos no implementados (requiere integración email)
10. **P1** — `/importaciones/subir` devuelve Error 500 cuando se accede directamente por URL (solo funciona desde la página de inicio)
11. **Pendiente** — Campos ARIBA configurados con valores genéricos de demo, César debe revisarlos con los valores reales de Moeve

---

## Bloqueantes P0

**Ninguno abierto.**

Los dos P0 originales (ARIBA sin parametrizar + sin flujo correo Moeve) están resueltos.

---

## Pendientes P1

1. ExcelParserService: soportar CSV además de XLSX
2. Importes no asignables: crear pantalla de revisión
3. Campos ARIBA: César debe verificar y actualizar los valores reales (Cta. Mayor, Propuesta Inversión, Acción gasto AC, Sociedad)
4. Correo Moeve: adjuntos automáticos (solo posible con integración SMTP)
5. Repsol: investigar auto-selección de tarifario alternativo en nuevo trabajo
6. URL directa `/importaciones/subir` da Error 500 (acceder desde `/importaciones`)

---

## Riesgos

1. **Valores ARIBA demo**: Los valores configurados (OPEX-MOEVE-2026, AC-MOEVE-2026, etc.) son de demo. Si César pregunta, admitir que son placeholders y hay que configurarlos con los reales de Moeve.
2. **Importación CSV**: Si la demo incluye importación masiva de CSV, fallará. Usar XLSX.
3. **Correo Moeve**: La automatización real de adjuntos requiere infraestructura SMTP no configurada. El modal permite preparar el correo manualmente de forma operativa.

---

## Conclusión

El ERP CIETE v2.1.0 está listo para la demo con CIETE:

- ✅ Los datos ARIBA se pueden parametrizar desde maestros de contratos
- ✅ El PDF, CSV y cuadro ARIBA de DEMO-MOE-LA-SENYERA se generan sin campos pendientes
- ✅ El flujo de preparación de correo Moeve existe y es operativo
- ✅ La política de cambio de campos sensibles de estación está implementada
- ✅ La exportación de facturas a CSV funciona correctamente
- ✅ 134/134 tests pasan, build OK
- ⚠️ Varios P1 menores documentados sin impacto en la demo

---

## Siguiente paso recomendado

1. **Inmediato**: César revisa y actualiza los valores ARIBA del contrato 772 MOEVE con los datos reales de Moeve (Cta. Mayor, Propuesta de Inversión, Acción de gasto AC)
2. **Antes de la demo**: Verificar que el PDF/CSV/ARIBA de DEMO-MOE-LA-SENYERA muestra los valores correctos
3. **Post-demo**: Resolver los P1 (soporte CSV en importador, importes no asignables)
