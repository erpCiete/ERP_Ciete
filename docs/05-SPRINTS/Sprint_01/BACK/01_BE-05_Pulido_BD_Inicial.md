# BE-05 · Pulido de Base de Datos Inicial (Sprint 01)

**Estado:** Draft  
**Owner:** Backend  
**Fecha de revision:** 25/03/2026

## 1) Objetivo real
Dejar cerrada la base tecnica de datos del Sprint 01 con una fotografia fiel del repositorio actual, fijando fuente canonica de esquema y reglas de uso para evitar bloqueos entre equipos.

Esta tarea es documental y de criterio. No implementa logica de negocio ni endpoints.

## 2) Fuentes revisadas

- `database/migrations/2026_03_24_000001_create_framework_support_tables.php`
- `database/migrations/2026_03_24_000002_create_personal_access_tokens_table.php`
- `database/migrations/2026_03_24_000010_create_security_core_tables.php`
- `database/migrations/2026_03_24_000020_create_empresas_contactos_base_tables.php`
- `database/migrations/2026_03_24_000030_create_security_users_tables.php`
- `database/migrations/2026_03_24_000040_create_comunicacion_operativa_base_tables.php`
- `database/migrations/2026_03_24_000050_create_flujo_negocio_tables.php`
- `database/migrations/2026_03_24_000060_create_legalizaciones_tables.php`
- `database/abaco_ciete.sql`
- `database/schema/erp_ciete_base.sql`
- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
- `app/Models/ContextoCliente.php`
- `database/factories/UserFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/ContextosClienteSeeder.php`
- `database/seeders/RolesSeeder.php`
- `database/seeders/PermisosSeeder.php`
- `database/seeders/RolPermisosSeeder.php`
- `database/seeders/DatosBaseSeeder.php`
- `database/seeders/UsuariosInicialesSeeder.php`
- `.env`

## 3) Estado real confirmado (proyecto web)

1. La base actual se define por las migraciones `2026_03_24_*` (8 archivos, 39 tablas).
2. El entorno local usa MySQL (`DB_DATABASE=abaco_ciete`) y drivers en BD para `session`, `cache` y `queue`.
3. El dominio de seguridad ya esta modelado y operativo en estructura:
- `contextos_cliente`, `roles`, `permisos`, `rol_permisos`
- `usuarios`, `usuario_roles`, `sesiones_login`
4. El modelo `User` usa:
- tabla `usuarios`
- PK `id_usuario`
- relacion many-to-many con `roles` via `usuario_roles`
- atributo calculado `is_admin`
5. Existen dos SQL en repo con distinto rol:
- `database/abaco_ciete.sql`: snapshot cercano al esquema actual.
- `database/schema/erp_ciete_base.sql`: esquema legacy claramente distinto.

## 4) Inventario estructural resumido

| Bloque | Tablas principales |
|---|---|
| Framework | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions`, `personal_access_tokens` |
| Seguridad | `contextos_cliente`, `roles`, `permisos`, `rol_permisos`, `usuarios`, `usuario_roles`, `sesiones_login` |
| Empresas/contactos | `empresas`, `contactos`, `contactos_empresas` |
| Comunicacion/operativa | `direcciones`, `telefonos`, `emails`, `estaciones_servicio`, `unidades`, `servicios`, `tarifarios`, `tarifario_servicios` |
| Flujo negocio | `proyectos`, `proyectos_workplan`, `proyectos_comentarios`, `presupuestos`, `presupuestos_lineas`, `pedidos`, `pedidos_lineas`, `facturas`, `facturas_lineas`, `cobros` |
| Legalizaciones | `legalizaciones`, `legalizaciones_contactos`, `comentarios_legalizaciones` |

## 5) Matriz de diferencias y riesgos actuales

| ID | Diferencia observada | Evidencia | Riesgo |
|---|---|---|---|
| DB-01 | Coexisten 3 fuentes de esquema (migraciones + 2 SQL) | `database/migrations/2026_03_24_*`, `database/abaco_ciete.sql`, `database/schema/erp_ciete_base.sql` | Confusion de fuente canonica |
| DB-02 | `erp_ciete_base.sql` usa naming legacy (`id`, `empresa_contexto_id`, `codigo` RBAC) | `database/schema/erp_ciete_base.sql` | Mapeos rotos y errores de integracion |
| DB-03 | `abaco_ciete.sql` esta alineado en tablas/campos, pero no 1:1 con migraciones (ej. `CHECK`, `ON DELETE` distintos, tipos/timestamps SQL) | `database/abaco_ciete.sql` vs migraciones `000020/000030/000040` | Drift silencioso si se usa como fuente de cambios |
| DB-04 | Doble capa de sesiones (`sessions` tecnica y `sesiones_login` negocio) | migraciones `000001` y `000030`; `AuthenticatedSessionController` | Mezcla de responsabilidades si no se documenta |
| DB-05 | Seeders dependen de IDs fijos y orden de carga | `DatabaseSeeder`, `ContextosClienteSeeder`, `RolesSeeder`, `DatosBaseSeeder`, `UsuariosInicialesSeeder` | Fallos de siembra si se altera orden/IDs |
| DB-06 | Credenciales de arranque en seeder (contexto local/demo) | comentario en `UsuariosInicialesSeeder` | Riesgo operativo si se replica en entornos no controlados |

## 6) Decisiones de BE-05 (actualizadas)

1. **Fuente canonica de desarrollo:** migraciones Laravel `2026_03_24_*`.
2. **Rol de `database/abaco_ciete.sql`:** snapshot de referencia/soporte, no fuente de diseno.
3. **Rol de `database/schema/erp_ciete_base.sql`:** historico/legacy, fuera de uso operativo.
4. **Convencion obligatoria de contexto:** `id_contexto` en codigo nuevo.
5. **Politica de sesiones:**
- `sessions`: sesion tecnica del framework
- `sesiones_login`: trazabilidad de negocio login/logout
6. **Usuarios iniciales:** mantener `id_contacto_empresa` nullable en Sprint 01 para no bloquear arranque.

## 7) Que puede aplazarse

- Homologar al 100% los SQL historicos a la convencion actual.
- Endurecer reglas de alta usuario-contacto cuando negocio cierre flujo definitivo.
- Revisar y endurecer manejo de credenciales de arranque para entornos superiores.

## 8) Fuera de alcance (BE-05)

- Implementar endpoints de Auth (BE-01).
- Implementar middleware RBAC operativo (BE-02).
- Testing funcional final (BE-04).
- Cierre tecnico completo de sprint (BE-07).

## 9) Criterio de Done

BE-05 queda Done cuando:
1. El equipo confirma migraciones `2026_03_24_*` como fuente canonica.
2. Queda documentado el rol diferenciado de `abaco_ciete.sql` y `erp_ciete_base.sql`.
3. La matriz DB-01..DB-06 se acepta como referencia para desarrollo Sprint 01.

## 10) Handoff al equipo

- Para cambios de BD: editar migraciones, no SQL historicos.
- Si se consulta `abaco_ciete.sql`, tratarlo como espejo de apoyo, no como verdad de diseno.
- Si aparece nomenclatura legacy (`empresa_contexto_id`, `codigo` RBAC, PK `id` generico), traducir a convencion actual antes de codificar.
