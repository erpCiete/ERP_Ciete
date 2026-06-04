# Bitácora unificada ERP CIETE

## Resumen ejecutivo

ERP interno para Ciete Ingenieros desarrollado por ABACO en 2026. El proyecto partió de un Excel operativo que gestionaba trabajos con MOEVE y REPSOL. El objetivo era digitalizarlo y añadir pedidos, facturas, cierre y exportación al formato ARIBA de MOEVE.

La validación original de entrega del 2026-06-04 registró 134 tests en verde, sin bloqueantes P0 y con validación manual completa vía Playwright. La suite actual ha crecido y su último resultado está documentado en `13_TESTING_VALIDACIÓN.md`.

---

## Línea temporal

### 2026-04-28 — Arranque
- Definición del dominio: trabajos, pedidos, estaciones, contratos, tarifarios.
- Modelo de datos inicial con contextos MOEVE/REPSOL.
- Primeras migraciones y seeders base.

### 2026-05-01 a 05-17 — Sprints 00-04 (equipo)
- Sprint 00: Base de BD, auth, roles, permisos.
- Sprint 01-02: APIs de trabajos, estaciones, contratos.
- Sprint 03: Vista de trabajos con tabla Excel + formulario de edición.
- Sprint 04: Pedidos con líneas de tarifa. Facturas básicas.
- Los sprints fueron ejecutados en equipo antes de la entrega a Pablo.

### 2026-05-17 a 05-19 — Auditoría y ajuste
- Auditoría de cobertura de datos: 14 puntos revisados.
- Importación real desde 11 Excels operativos de Ciete.
- Datos reales de MOEVE y REPSOL cargados.

### 2026-05-19 — Reunión con CIETE
- Primera demo con César García y Amaya García de Ciete.
- Se acordó: vista tipo Excel como pantalla central, pedido desde trabajo, tarifario predeterminado, exportación Moeve.
- Se identificaron necesidades ARIBA: PDF, CSV, cuadro ARIBA, correo.
- Fuente: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`

### 2026-05-28 — Cierre técnico inicial
- Cierre documental de los bloques ejecutados.
- Documentación histórica de sprints archivada.
- Estado: flujo principal funcional, sin validación manual completa.

### 2026-06-01 — Bloque funcional completo
- Exportación Moeve: PDF, CSV, cuadro ARIBA operativos.
- Correo sugerido en modal "Preparar correo Moeve".
- Estados automáticos derivados de pedidos/facturas.
- Cierre con checklist. Roles y permisos refinados.
- Soporte restringido a admin técnico.

### 2026-06-04 — Validación final y entrega
- Validación original completa vía Playwright (134/134 tests, build y revisión manual).
- Campos ARIBA parametrizados desde maestros de contratos.
- Concurrencia por campo implementada y verificada.
- Política de cambio de código/nombre de estación con confirmación.
- Revisión y limpieza de comentarios de código.
- Documentación de entrega creada.

---

## Decisiones clave

### Contextos vs Roles
Los **contextos** (MOEVE, REPSOL, OTROS) controlan qué datos puede ver y crear cada usuario. Los **roles** controlan qué pantallas puede usar. Son independientes. Un usuario de Ejecución puede tener acceso a MOEVE y REPSOL a la vez pero cambia su contexto activo desde el perfil.

### Tarifario bloqueado tras primer pedido
Una vez que un trabajo tiene un pedido, el tarifario no puede cambiarse. Esta restricción evita inconsistencias entre el precio acordado y las líneas facturadas. Es una decisión de negocio de Ciete.

### Estados derivados, no editables
El estado visible de un trabajo (en_curso, pendiente_facturar, facturado, finalizado) se calcula automáticamente desde el estado de sus pedidos y facturas. Solo `cancelado` y `finalizado` son terminales. El usuario no puede forzar el estado directamente, excepto a estados manuales específicos.

### Factura por ítems, no por pedido completo
Una factura puede incluir ítems de diferentes pedidos y trabajos, siempre que sean del mismo contrato. Esto permite facturación parcial sin forzar pedidos completos.

### ARIBA en el contrato, no en la estación
Los campos ARIBA (Cta. de Mayor, Propuesta de Inversión, etc.) se parametrizan en el maestro de contratos, no en la estación. La sociedad facturadora tiene un campo de fallback a nivel de contrato que se usa cuando la estación no tiene `cod_sociedad` configurado.

### Concurrencia optimista por campo
El sistema detecta si otro usuario modificó un campo en los últimos 60 minutos y muestra un aviso con el valor anterior, el nuevo, quién lo cambió y cuándo. El test automatizado confirma el 409. La verificación manual requiere sesiones realmente concurrentes.

---

## Bloques funcionales cerrados

| Bloque | Qué incluye |
|--------|-------------|
| Trabajos | Tabla Excel inline, formulario moderno, modo cierre, filtros |
| Pedidos | Crear desde trabajo, líneas de tarifa, buscador, decimales |
| Facturas | Parcial/completa, ítems de varios trabajos, anulación |
| Cierre | Panel dirección, checklist, bloqueo de trabajos no listos |
| Exportación Moeve | PDF, CSV con coma decimal, ARIBA, modal correo |
| Maestros | Árbol empresa→sociedad→contrato→tarifario→líneas |
| ARIBA parametrizable | 5 campos en maestros de contratos |
| Roles y permisos | Director, Ejecución (Moeve/Repsol/General), Contabilidad, Admin técnico |
| Soporte | Solo admin técnico, 403 para el resto |
| Password reset | Token no reutilizable, sin revelar si email existe |
| Auditoría | Registro completo con campo, usuario, valor anterior/nuevo |
| Concurrencia | 409 si otro modificó el campo en <60 min |

---

## Cambios importantes de base de datos

| Fecha | Migración | Motivo |
|-------|-----------|--------|
| 2026-06-04 | `add_ariba_fields_to_contratos` | Campos ARIBA parametrizables desde UI |
| 2026-06-04 | `add_ariba_sociedad_to_contratos` | Fallback de Sociedad cuando la estación no tiene cod_sociedad |
| 2026-05-* | `add_pendiente_facturar_to_trabajos_estado_enum` | Nuevo estado derivado |
| Varias | Migraciones de roles/permisos | Granularidad de acceso por módulo |

---

## Incidencias y hotfixes

| Fecha | Incidencia | Fix |
|-------|-----------|-----|
| 2026-06-04 | CSV decimales sin coma (510 en vez de 510,00) | `formatCsvNumber` eliminó el `rtrim` que recortaba ceros |
| 2026-06-04 | `passwords.sent` sin traducir | Creados `lang/es/passwords.php` y `lang/en/passwords.php` |
| 2026-06-04 | `ImportacionController@store` devolvía JSON en lugar de Inertia | Cambio a `$request->validate()` con `mimetypes` |
| 2026-06-04 | Test de concurrencia fallaba (mismo segundo) | `now()->addSecond()` en el test para garantizar timestamps distintos |
| 2026-06-04 | Modal correo Moeve mostraba "estación" vacío | Eager-load de `trabajo.estacion` en la ruta de edición del pedido |

---

## Pendientes reales

Ver [15_PENDIENTES_Y_RIESGOS.md](15_PENDIENTES_Y_RIESGOS.md) para el detalle.

Resumen:
- P1: ExcelParserService no procesa CSV (solo XLSX)
- P1: Importes no asignables a trabajo/pedido (sin pantalla)
- P1: Valores ARIBA configurados con placeholders demo
- P1: Automatización real de correo (adjuntos automáticos requiere SMTP)

---

## Qué documentación histórica queda archivada

En `docs/_archivo_historico/erp_ciete_documentacion_desfasada_2026-05-28/`:
- Bitácoras individuales por desarrollador (Sprints 00-07)
- Planes de sprint
- Documentos de análisis preliminar

En `docs/_archivo_historico/entrega_final_2026_06/`:
- Documentos intermedios del cliente archivados en esta entrega
- Ver `README.md` en esa carpeta
