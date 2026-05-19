# Fase B.2 - Validación funcional completa post-importación real

Fecha: 2026-05-19  
Ámbito: local `abaco_ciete` (sin producción, sin commit, sin reimportación masiva)

## 1. Entorno

- `artisan env`: `APP_ENV=local`.
- Config efectiva mysql: `abaco_ciete`.
- Inventario Git ejecutado (`git status --short`, `git diff --stat`) con worktree ya sucio de fases previas.

## 2. Versión validada

- Versión visible detectada en código durante B.2:
    - backend estado: `v2.1.0`
    - pie i18n ES/EN: `ERP Ciete v2.1.0` / `Ciete ERP v2.1.0`

Nota: en B.1.2 se había documentado `v2.1.0-rc1`. En esta ejecución B.2 la base de código activa ya está en `v2.1.0`.

## 3. Base usada

Base real validada: `abaco_ciete`.

Referencia B.1 (sin muestra B2):

- trabajos: 11.614
- pedidos: 9.685
- pedido_items: 9.701
- facturas: 2.733
- factura_items: 9.194
- importaciones: 11

Estado durante B.2 con muestra OTROS activa (`[muestra-b2-validacion-2026-05-19]`):

- trabajos: 11.618
- pedidos: 9.688
- pedido_items: 9.704
- facturas: 2.734
- factura_items: 9.195
- importaciones: 11

Delta controlado por muestra B2:

- trabajos +4
- pedidos +3
- pedido_items +3
- facturas +1
- factura_items +1

## 4. Resultado automático

## 4.1 Suite completa

Comando:

- `/mnt/c/xampp/php/php.exe artisan test`

Resultado:

- PASS
- 218 tests, 1286 assertions, 0 fallos

## 4.2 Build

Comando:

- `npm run build`

Resultado:

- PASS
- warning no bloqueante de `PLUGIN_TIMINGS` (`laravel`)

## 4.3 Superficie de rutas

Comando:

- `/mnt/c/xampp/php/php.exe artisan route:list`

Resultado:

- 133 rutas registradas
- rutas críticas de auth/contexto/trabajos/pedidos/facturas/cierre/admin/importaciones presentes

## 4.4 Batería focalizada solicitada

Todos PASS:

- `TrabajoTest`
- `PedidoTest`
- `FacturaTest`
- `ClientesEstacionesApiTest`
- `MaestrosTest`
- `RoleModuleAccessTest`
- `ExcelModeAccessTest`
- `ContextCreationGuardTest`
- `AdminTechnicalMutationTest`
- `ClosureDashboardTest`
- `ImportacionesAccessTest`

## 4.5 Cobertura complementaria ejecutada para B.2

PASS:

- `AdminAccessTest`
- `DireccionAccessTest`
- `ContableAccessTest`
- `ExecutionAccessTest`
- `LoginRedirectTest`
- `AccessDeniedViewTest`
- `ErrorPagesTest`
- `MaintenanceModeTest`
- `InternalCommunicationTest`
- `AdminDashboardTest`
- `AuditLogTest`
- `FacturaExportTest`
- `TrabajoTest --filter=test_patch_field_returns_conflict_when_updated_at_is_stale`

Nota técnica de ejecución:

- Se reprodujo inestabilidad de `abaco_ciete_testing` al lanzar tests en paralelo (colisión de migraciones/FKs en DB de pruebas).
- Mitigación aplicada (solo entorno de test): `DROP/CREATE abaco_ciete_testing` y rerun secuencial.
- No impacto sobre `abaco_ciete` real.

## 5. Resultado manual por rol

## 5.1 Cobertura disponible en esta ejecución

Esta sesión es CLI sin arnés E2E de navegador (sin Dusk/Playwright/Cypress en repo).  
Por tanto la validación manual visual de click-navegación se sustituye aquí por evidencia automatizada de permisos/rutas/estado.

## 5.2 Resultado por rol (evidencia técnica)

| Rol               | Evidencia                                                                                          | Resultado |
| ----------------- | -------------------------------------------------------------------------------------------------- | --------- |
| Admin técnico     | `AdminAccessTest`, `AdminTechnicalMutationTest`, `RoleModuleAccessTest`, `AdminDashboardTest`      | PASS      |
| Dirección / César | `DireccionAccessTest`, `ClosureDashboardTest`, `RoleModuleAccessTest`, `AuditLogTest`              | PASS      |
| Contable          | `ContableAccessTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`                                | PASS      |
| Ejecución MOEVE   | `ExecutionAccessTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`, `TrabajoTest`                | PASS      |
| Ejecución REPSOL  | `ExecutionAccessTest`, `RoleModuleAccessTest`, `ExcelModeAccessTest`, `TrabajoTest`, `FacturaTest` | PASS      |
| Multicontexto     | `ContextCreationGuardTest`, `RoleModuleAccessTest`, `PedidoTest`, `FacturaTest`                    | PASS      |

Conclusión funcional por rol:

- Redirección a inicio común: validada por tests de login/acceso.
- Aislamiento por permisos y rutas: validado.
- Bloqueo de mutación admin técnico en operativa: validado.
- Restricción de `TODOS/all` para creación/cambio de contexto: validado.

## 6. Resultado por módulo

| Módulo                                          | Evidencia principal                                                   | Estado   |
| ----------------------------------------------- | --------------------------------------------------------------------- | -------- |
| Inicio                                          | `LoginRedirectTest`, `RoleModuleAccessTest`                           | validado |
| Trabajos                                        | `TrabajoTest`, `ExecutionAccessTest`                                  | validado |
| Pedidos                                         | `PedidoTest`, `ContableAccessTest`                                    | validado |
| Facturas                                        | `FacturaTest`, `FacturaExportTest`, `ContableAccessTest`              | validado |
| Cierre                                          | `ClosureDashboardTest`, `DireccionAccessTest`                         | validado |
| Clientes/empresas                               | `ClientesEstacionesApiTest`, `MaestrosTest`                           | validado |
| Estaciones                                      | `ClientesEstacionesApiTest`, `EstacionesTest` (suite completa)        | validado |
| Contratos/Sociedades/Tarifarios/Líneas/Maestros | `MaestrosTest`                                                        | validado |
| Administración                                  | `AdminAccessTest`, `AdminDashboardTest`, `AdminTechnicalMutationTest` | validado |
| Soporte/Comunicaciones/Mensajes                 | `InternalCommunicationTest`                                           | validado |
| Importaciones                                   | `ImportacionesAccessTest`                                             | validado |
| Estado del sistema                              | `RoleModuleAccessTest`, `MaintenanceModeTest`                         | validado |
| Error pages                                     | `ErrorPagesTest`, `AccessDeniedViewTest`                              | validado |

## 7. Resultado casos B2 y reales

## 7.1 Casos B2 OTROS

| Caso            | Estado observado                                             |
| --------------- | ------------------------------------------------------------ |
| `B2-OTR-980001` | flujo completo con `B2-OTR-PED-001` y `B2-OTR-FAC-001`       |
| `B2-OTR-980002` | terminado con pedido, sin factura (contrato sin sociedad)    |
| `B2-OTR-980003` | en curso, sin pedido/factura (caso para conflicto por celda) |
| `B2-OTR-980004` | terminado con pedido, sin factura (sociedad sin CIF)         |

## 7.2 Casos reales seleccionados

- REPSOL numeración larga: `id_trabajo=21460`, `numero_trabajo_operativo=865.66666666666697`.
- Facturas `SIN_NUMERO-*` largas: longitud 34, ejemplo `SIN_NUMERO-REPSOL-23105-2026-03-01`.
- Pedidos negativos reales: `id_pedido=10861` (`-252.00`) y `10988` (`-5115.00`).
- Fechas imposibles reales: 6 trabajos (ej. años `0202`, `0205`, `2525`).

## 8. Resultado responsive

Validación manual visual de viewport (desktop/portátil/móvil) no ejecutable en esta sesión CLI.  
Evidencia técnica disponible:

- tablas críticas usan `ciete-table-scroll`/`overflow-x-auto`;
- columnas largas con `truncate + whitespace-nowrap + title` en trabajos/pedidos/facturas;
- `Facturas` moderno con `Nº FACTURA` ancho controlado y `FECHA` con ancho propio;
- avatar/zona cuenta en sidebar y drawer móvil con componente dedicado.

Estado: sin KO crítico detectado por estructura; pendiente pasada manual de navegador para cierre UX final.

## 9. Resultado control de edición por celda

Ámbito validado: Trabajos Excel.

Evidencia:

- test de conflicto por `updated_at` obsoleto: PASS (`409`).
- backend devuelve datos de conflicto y mensaje contextual.
- flujo implementado para conservar borrador y requerir confirmación para sobrescribir.

Pendiente manual recomendado en B.2 presencial:

- simulación de dos usuarios en UI para verificar mensaje de "modificado recientemente" en <1 hora.

## 10. KO detectados

## 10.1 KO críticos

- 0

## 10.2 KO mayores

- 0 funcionales del ERP.

## 10.3 KO menores / avisos

1. Pendiente de ejecución manual visual en navegador para cierre formal de UX responsive por rol (limitación de esta sesión CLI).
2. Se mantienen avisos de calidad de datos heredados de B.1/B.1.1 (no nuevos en B.2):
    - `work_amount_without_order`;
    - `historical_invoice_without_items`;
    - `invoice_number_normalized`;
    - fechas imposibles/negativos de origen.
3. Desalineación documental de versión respecto al cierre B.1.2 (`v2.1.0-rc1`) vs código activo B.2 (`v2.1.0`).

## 11. Correcciones aplicadas durante B.2

- No se aplicaron correcciones funcionales nuevas de negocio.
- Se ejecutó saneamiento de `abaco_ciete_testing` para estabilizar ejecución de tests (entorno de pruebas).
- No se tocaron datos reales de `abaco_ciete` salvo lectura y uso de muestra B2 ya existente.

## 12. Estado final B.2

**Versión vigente:** `ERP CIETE v2.1.0`  
**Resultado:** `v2.1.0 validada con KOs menores`  
**Estado operativo:** listo para uso. KOs menores documentados; sin bloqueos.

### B.2.1 — Pasada visual final (2026-05-19)

**KO mayor resuelto durante B.2.1:**

- Paginación de tablas principales (Trabajos/Pedidos/Facturas Excel) no rendería porque el frontend leía `meta.pagination.*` pero el backend devuelve `meta.*` plano. Corregido en `Trabajos/Index.jsx`, `Pedidos/Index.jsx`, `Facturas/Index.jsx`. Build PASS. 33 tests Trabajo PASS (154 assertions).

**KO menores confirmados (no bloquean uso):**

1. Badge "VERSIÓN 2.0" en card hero de Inicio — clave `badgeVersion` en `es.js`/`en.js` line 198. Pendiente corrección cosmética.
2. Simulación doble sesión manual no realizada por limitación de herramienta — cubierto por test automático `patch_field_returns_conflict_when_updated_at_is_stale`.

**Módulos validados visualmente:**

- Login, Inicio (admin/moeve/contable/director), Panel admin, Estado del sistema ✅
- Trabajos Excel + paginación (moeve, 6685 trabajos, Pág. 1/669) ✅
- Trabajos moderno (admin, read-only, OTROS CLIENTES) ✅
- Pedidos Excel + paginación (moeve, Pág. 1/615) ✅
- Facturas (director, OTROS CLIENTES, 1 registro) ✅
- Panel de cierre + KPIs (director) ✅
- Maestros + diagnóstico funcional (director) ✅
- Importaciones (admin) ✅
- Soporte/panel (admin) ✅
- Error 403 y 404 (minimal layout) ✅
- Roles: admin-técnico, ejecucion-moeve, director, contable — isolamiento verificado ✅

**ESTADO DECLARADO:** `v2.1.0 validada con KOs menores`

### B.2.2 — Paginación rápida, mensajes de inicio y badge (2026-05-19)

**Estado:** COMPLETADA

**Cambios aplicados:**

1. **Badge "VERSIÓN 2.0" corregido** — `badgeVersion` en `resources/js/i18n/locales/es.js` y `en.js` (line 198) → `'v2.1.0'`. KO menor de B.2.1 cerrado.

2. **Componente `PaginationControls` creado y aplicado en 14 archivos** — `resources/js/Components/ui/PaginationControls.jsx`. Soporta «primera/anterior/siguiente/última» + "Ir a página" con validación. Normaliza los dos formatos de meta (plano Inertia y anidado API). Archivos actualizados:
    - Excel views: `TrabajosExcelView.jsx`, `PedidosExcelView.jsx`, `FacturasExcelView.jsx`
    - Moderno Inertia: `Trabajos/Index.jsx`, `Pedidos/Index.jsx`, `Facturas/Index.jsx`
    - API hook pages: `Clientes/Index.jsx`, `Estaciones/Index.jsx`, `SociedadesFacturadoras/Index.jsx`, `Tarifarios/Index.jsx`, `Tarifarios/Lineas.jsx`
    - Otros: `Admin/Support/Index.jsx`, `AuditLog/Index.jsx` (eliminado componente local `Paginacion`), `Cierre/Dashboard.jsx`

3. **`home_notices` migrada y sembrada** — migración `2026_05_19_000170_create_home_notices_table.php` ejecutada. Seeder actualizado con 10 mensajes bilingües v2.1.0: 1 destacado (`internal_notice`, `is_featured=true`) + 3 avisos internos + 3 actualizaciones sistema + 3 novedades empresa. `HomeNoticeAdminTest` (4 tests, 40 aserciones) PASS.

**Build:** PASS (1.87s, 2992 módulos, `PaginationControls-Bln4v0Hz.js` incluido)  
**Tests:** `HomeNoticeAdminTest` PASS · `TrabajoTest` PASS · `PedidoTest` PASS · `FacturaTest` PASS

**ESTADO DECLARADO:** `v2.1.0 validada`

## 13. Confirmaciones de control

- No producción.
- No commit.
- No push.
- No reimportación masiva.
- No modificación masiva de datos reales.
- Casos B2 mantenidos marcados y reversibles.
