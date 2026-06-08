# Testing y validación

## Estado actual de tests

La última ejecución completa sobre `abaco_ciete_testing` terminó con **277 tests correctos, 1 fallido y 1694 aserciones**. El build de producción finalizó correctamente.

```bash
php artisan test
# Tests: 1 failed, 277 passed (1694 assertions)
```

De los 3 fallos que documentaba la entrega anterior, se han revisado y corregido 2:

- `AdminAccessTest`: tenía la expectativa al revés — esperaba `200` en `/dashboard` para admin, pero `RoleModuleAccessTest` (que ya pasaba) confirma que `/dashboard` debe devolver `403` para todos los roles por igual (el `DashboardController`/`Dashboard.jsx` existen pero la página no está enlazada en el menú; el bloqueo es intencional, no un olvido). Se corrigió la expectativa del test para que coincida con el resto de la suite.
- `ImportacionesAccessTest`: era un fallo real y más serio de lo que parecía — las rutas `importaciones.create`/`importaciones.preview` apuntaban a vistas (`Form.jsx`/`Preview.jsx`) que nunca llegaron a construirse, y `store()` redirigía a esa página inexistente también en producción. El flujo real está centralizado en `Importaciones/Index.jsx` (formulario y previsualización embebidos). Se eliminaron esas rutas/métodos huérfanos y `store()` ahora redirige a `Importaciones/Index` con la previsualización ya cargada, que es lo que esa pantalla esperaba recibir.

Fallo pendiente:

- `ContextCreationGuardTest`: la creación de un pedido de OTROS CLIENTES falla si el trabajo no tiene tarifario válido. Es una decisión de negocio pendiente (¿debe OTROS CLIENTES seguir la misma regla de tarifario que MOEVE/REPSOL?), no urgente porque ese contexto todavía no tiene clientes activos.

No ejecutes varias suites simultáneamente sobre la misma base `abaco_ciete_testing`.

## Suites disponibles

| Suite | Comando | Qué cubre |
|-------|---------|----------|
| Password | `--filter=Password` | Reset, token, caducidad, no-revelación |
| Role | `--filter=Role` | Acceso por rol a módulos |
| SupportTicket | `--filter=SupportTicketTest` | Tickets de soporte |
| Maestros | `--filter=MaestrosTest` | Tarifarios, contratos, árbol |
| Trabajo | `--filter=TrabajoTest` | CRUD, estados, concurrencia, cierre |
| Pedido | `--filter=PedidoTest` | CRUD, líneas, exportación Moeve |
| Factura | `--filter=Factura` | CRUD, parcial, completa, estados |
| Cierre | `--filter=ClosureDashboardTest` | Panel de cierre, bloqueos, finalización |

## Comandos de test

```bash
# Suite completa
php artisan test

# Suites específicas
php artisan test --filter=TrabajoTest
php artisan test --filter=PedidoTest
php artisan test --filter=Factura
php artisan test --filter=ClosureDashboardTest

# Con detalle de fallos
php artisan test --filter=TrabajoTest -v
```

## Validación manual con Playwright/MCP

Para validación visual end-to-end se usó Playwright MCP. Los tests manuales cubrieron:

**Cuentas demo usadas:**

| Usuario | Email | Contraseña |
|---------|-------|-----------|
| Dirección | `cesar@ciete.es` | `Cesar1234!` |
| Contabilidad | `contable@ciete.es` | `Contable1234!` |
| Admin técnico | `admin@ciete.es` | `Admin1234!` |
| Ejecución | `usuario@ciete.es` | `Usuario1234!` |
| Ejecución Moeve | `moeve@ciete.es` | `Moeve1234!` |

**Módulos validados manualmente:**
- Login y contextos por rol
- Trabajos: búsqueda, filtros, creación inline, edición, concurrencia
- Pedidos: creación desde trabajo, líneas, decimales, bloqueo de tarifario
- Facturas: parcial, completa, anulación
- Cierre: panel, bloqueo, finalización de DEMO-MOE-CIERRE
- Exportación: PDF, CSV, ARIBA, modal correo
- Maestros: árbol, datos ARIBA
- Soporte: 403 para perfiles normales
- Estado: solo admin técnico
- Password reset: mismo mensaje para email existente e inexistente
- Exportación REPSOL: botones MOEVE no aparecen, acceso directo bloqueado con 422

## Validación manual con Playwright/MCP

Para activar la validación visual en el navegador se usa Playwright MCP. La configuración está en `.mcp.json` (raíz del proyecto). Este archivo apunta al servidor MCP de Playwright.

```json
{
  "mcpServers": {
    "playwright": {
      "type": "stdio",
      "command": "npx",
      "args": ["@playwright/mcp@latest"]
    }
  }
}
```

Los snapshots y logs generados por Playwright se guardan en `.playwright-mcp/` (excluida del repo en `.gitignore`). Los CSV descargados durante la validación están en `docs/_archivo_historico/entrega_final_2026_06/06_evidencias_playwright/`.

La carpeta `.claude/` en raíz contiene `settings.local.json` con configuración local de Claude Code. Es ignorada por git. No es necesaria para el funcionamiento del ERP.

## Checklist previa a demo con CIETE

- [ ] Login funciona con todas las cuentas demo
- [ ] DEMO-MOE-LA-SENYERA exporta PDF/CSV/ARIBA sin "Pendiente de parametrizar"
- [ ] El cuadro ARIBA muestra los campos reales (no placeholders)
- [ ] El modal "Preparar correo Moeve" muestra el nombre de la estación correctamente
- [ ] DEMO-620005 (REPSOL) muestra facturación parcial (200 de 585 €)
- [ ] Cierre: DEMO-MOE-CIERRE aparece como "Listo para finalizar"
- [ ] Soporte da 403 para César, contable y usuario
- [ ] Estado da 403 para César, contable y usuario
- [ ] Build: `npm run build` OK

## Checklist previa a deploy en producción

- [ ] Variables de entorno actualizadas (APP_KEY, DB, MAIL)
- [ ] `APP_ENV=production` y `APP_DEBUG=false`
- [ ] `npm run build` ejecutado en servidor
- [ ] `php artisan migrate` ejecutado
- [ ] `php artisan config:cache`, `route:cache`, `view:cache`
- [ ] Permisos de `storage/` y `bootstrap/cache/` correctos
- [ ] SMTP configurado y verificado
- [ ] Backups excluidos del repositorio (`.gitignore`)
- [ ] César ha actualizado los valores ARIBA reales en maestros de contratos
