# Matriz exacta del administrador técnico ERP CIETE

Fecha: 2026-05-18
Ámbito: Fase A.3, entorno local, código + seeders + auditoría read-only de la base actual
Autoridad funcional: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt` > auditorías funcionales > backlog vivo `docs/02_CLIENTE/tareasComparar.md`

## 1. Frontera objetivo aprobada para `admin`

`admin` deja de ser un superusuario funcional del ERP y pasa a representar un administrador técnico.

| Superficie                                                                    | Admin técnico  | Director                   | Contable        | Ejecución         | Nota de frontera                                            |
| ----------------------------------------------------------------------------- | -------------- | -------------------------- | --------------- | ----------------- | ----------------------------------------------------------- |
| `/admin`                                                                      | sí             | no                         | no              | no                | acceso por `admin.panel.ver`                                |
| `/admin/usuarios*`                                                            | sí             | sí                         | no              | no                | por permisos `usuarios.*`                                   |
| `/admin/soporte*`                                                             | sí             | no                         | no              | no                | por `soporte.gestionar`                                     |
| `/admin/auditoria`                                                            | sí             | sí                         | no              | no                | por `auditoria.ver`                                         |
| `/admin/maintenance`                                                          | sí             | no                         | no              | no                | por `mantenimiento.gestionar`                               |
| `/admin/notices` y `/mensajes/broadcast`                                      | sí             | sí                         | no              | no                | por `avisos.gestionar`                                      |
| `/importaciones*`                                                             | sí             | no por defecto             | no              | no                | por `importaciones.*`                                       |
| `/dashboard`                                                                  | no             | sí                         | no              | no                | panel de dirección                                          |
| `/cierre*`                                                                    | no             | sí                         | no              | no                | cierre solo dirección                                       |
| `/trabajos`, `/pedidos`, `/facturas`, `/clientes`, `/estaciones`, `/maestros` | lectura        | gestión completa según rol | solo lo propio  | solo lo operativo | `admin` conserva lectura operativa, no mutación por defecto |
| Mutaciones operativas web/API                                                 | no por defecto | sí según permiso           | sí en su ámbito | sí en su ámbito   | la separación se valida por permiso de acción               |

## 2. Implementación aplicada en código y seeders

- Nuevos permisos técnicos: `admin.panel.ver`, `mantenimiento.gestionar`, `avisos.gestionar`.
- `RolPermisosSeeder` redefine `admin` como paquete técnico: usuarios, soporte, auditoría, mantenimiento, avisos, importaciones y lectura operativa.
- `User` deja de tratar `admin` como bypass funcional y expone capacidades explícitas:
    - `canAccessAdminPanel()`
    - `canManageUsers()`
    - `canManageSupport()`
    - `canManageMaintenance()`
    - `canManageNotices()`
    - `canViewAudit()`
    - `canManageImports()`
    - `canViewOperationalData()`
    - `canMutateOperationalData()`
    - el conjunto de permisos efectivo del rol `admin` blinda el runtime contra slugs legacy de mutación operativa y garantiza panel técnico/capacidades técnicas aunque la base local antigua no esté resincronizada
- `routes/web.php` y middlewares quedan separados por acción/capacidad:
    - `/dashboard` y `/cierre` dejan de aceptar `admin`
    - `/admin` deja de depender del rol raw y usa `permission:admin.panel.ver`
    - soporte, mantenimiento, avisos, auditoría e importaciones pasan a permiso explícito
    - usuarios se separa por `ver/crear/editar`
- Frontend alineado con flags compartidos de Inertia:
    - sidebar por capacidades en vez de `is_admin`
    - sidebar admin técnico limpia con `Inicio`, `Panel administrador`, `Estado del sistema`, `Ayuda` y `Perfil`
    - panel admin técnico reagrupado por bloques: sistema, usuarios/accesos, soporte, comunicación interna, importaciones y lectura operativa soporte
    - diferenciación visual entre `Auditoría técnica` y `Registro de actividad operativa`
    - operativa principal en modo solo lectura visible para admin técnico, sin acciones de edición cuando no existe permiso efectivo
    - dashboard comun solo enseña `admin` y `cierre` cuando el usuario tiene la capacidad real
    - mensajes y estado reutilizan `can_manage_notices` y `can_access_admin_panel`

## 3. Validación ejecutada

Validación de frontend:

- `npm run build`: PASS

Validación de backend y acceso:

- `AdminAccessTest`: PASS
- `AdminDashboardTest`: PASS
- `AdminTechnicalMutationTest`: PASS
- `ContextCreationGuardTest`: PASS
- `ExcelModeAccessTest`: PASS
- `ImportacionesAccessTest`: PASS
- `InternalCommunicationTest`: PASS
- `MaintenanceModeTest`: PASS
- `PermissionRoutesTest`: PASS
- `RoleModuleAccessTest`: PASS

Resultado conjunto del corte final:

- `47` tests PASS
- `426` assertions PASS

Revalidación A.5 (2026-05-18):

- Requests API marcados en rojo en VS Code: corregidos sin cambiar la política funcional; el problema era de tipado estático en acceso al usuario activo.
- `TrabajoTest`: PASS (`19` tests, `96` assertions) tras realinear los casos positivos de edición/cancelación de trabajos finalizados con `director`; `admin` mantiene cobertura negativa en `AdminTechnicalMutationTest`.
- `php artisan test`: PASS (`95` tests, `411` assertions) tras el barrido residual final.

Revalidación A.7 (2026-05-19):

- `ClosureDashboardTest`: realineado para confirmar que `admin` técnico recibe `403` en `/cierre` y `director` mantiene acceso operativo.
- `EstacionesTest`: realineado para confirmar que `admin` técnico conserva lectura y sigue sin creación web; el positivo de creación queda en `director`.
- `ErrorPagesTest`, `PasswordConfirmationTest` y `RegistrationTest`: cerrados sin reactivar registro público ni mutación funcional para `admin`; el portal protegido redirige a login en GET desconocidos de invitado y mantiene 404 corporativo para autenticados.
- `php artisan test`: PASS (`218` tests, `1286` assertions).
- `npm run build`: PASS.

## 4. Auditoría read-only de la base local actual

La siguiente tabla refleja la base actual persistida. No se ha modificado durante esta fase por restricción expresa de no tocar la base real/post-importación.

| Usuario             | Roles actuales     | Contextos activos      | Contexto principal | Modo            | `admin.panel.ver` | `soporte.gestionar` | `auditoria.ver` | `mantenimiento.gestionar` | `avisos.gestionar` | `importaciones.ver` | Mutación operativa actual | Estado frente a la matriz A.3                                                                                                          |
| ------------------- | ------------------ | ---------------------- | ------------------ | --------------- | ----------------- | ------------------- | --------------- | ------------------------- | ------------------ | ------------------- | ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `admin@ciete.es`    | `admin`            | `MOEVE, REPSOL, OTROS` | `OTROS`            | `ciete_moderno` | no                | sí                  | sí              | no                        | no                 | sí                  | sí                        | KO: sigue con mutación operativa y aún no tiene panel admin técnico, mantenimiento ni avisos según la nueva matriz                     |
| `cesar@ciete.es`    | `director`         | `MOEVE, REPSOL, OTROS` | `OTROS`            | `ciete_excel`   | no                | no                  | sí              | no                        | no                 | no                  | sí                        | Parcial: alineado en no tener panel admin/soporte/importaciones, pero falta `avisos.gestionar` según la nueva matriz seed              |
| `usuario@ciete.es`  | `ejecucion`        | `MOEVE, REPSOL, OTROS` | `OTROS`            | `ciete_excel`   | no                | no                  | no              | no                        | no                 | no                  | sí                        | OK: sin panel admin, auditoría, cierre ni facturas                                                                                     |
| `moeve@ciete.es`    | `ejecucion_moeve`  | `MOEVE`                | `MOEVE`            | `ciete_excel`   | no                | no                  | no              | no                        | no                 | no                  | sí                        | OK: perfil operativo contextual                                                                                                        |
| `repsol@ciete.es`   | `ejecucion_repsol` | `REPSOL`               | `REPSOL`           | `ciete_excel`   | no                | no                  | no              | no                        | no                 | no                  | sí                        | OK: perfil operativo contextual                                                                                                        |
| `contable@ciete.es` | `contable`         | `MOEVE, REPSOL, OTROS` | `OTROS`            | `ciete_excel`   | no                | no                  | no              | no                        | no                 | no                  | sí                        | Parcial: mantiene mutación de facturas esperada, pero conserva `trabajos.ver` en la base actual y eso no encaja con la matriz validada |

## 5. Lectura ejecutiva del gap actual

- Código, seeders y runtime efectivo: alineados con la separación `admin técnico` y validados por tests.
- Base local actual: no alineada todavía en persistencia con la matriz A.3 porque no se ha aplicado resincronización controlada de `roles/permisos/usuario_roles/rol_permisos`.
- Impacto principal del gap:
    - el `admin` persistido actual sigue sin reflejar la nueva matriz en tablas, aunque en ejecución ya queda blindado por código
    - el `director` persistido actual no recibe aún `avisos.gestionar`
    - el `contable` persistido actual conserva `trabajos.ver`

## 6. Siguiente paso permitido

Queda pendiente una actuación controlada sobre la base local actual, separada de esta fase de código:

1. resincronizar permisos y relaciones de rol con los seeders A.3
2. reauditar usuarios persistidos
3. validar manualmente `admin@ciete.es`, `cesar@ciete.es` y `contable@ciete.es` en la base ya sincronizada

Hasta que ese paso no se ejecute, la referencia técnica correcta para producto es: código + seeders + runtime efectivo + tests; la referencia de datos persistidos es: auditoría read-only anterior.
