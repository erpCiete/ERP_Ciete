# Database — ERP CIETE v2.2.0

## Inicio rápido

**Opción A — Restaurar dump completo (más rápido):**
```bash
mysql -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
```

**Opción B — Desde cero con migraciones:**
```bash
php artisan migrate
php artisan db:seed
# Cargar datos demo:
mysql -u root abaco_ciete < database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql
```

---

## Estructura

```
database/
├── schema/
│   ├── abaco_ciete_v220_2026-06-04.sql   ← Dump completo con datos demo
│   └── README.md
├── migrations/                            ← Migraciones Laravel (no borrar)
├── seeders/                               ← Seeders de instalación
│   ├── DatabaseSeeder.php                 ← Punto de entrada (php artisan db:seed)
│   ├── RolesSeeder.php
│   ├── PermisosSeeder.php
│   ├── RolPermisosSeeder.php
│   ├── ContextosClienteSeeder.php
│   ├── UsuariosInicialesSeeder.php
│   ├── HomeNoticeSeeder.php
│   ├── DatosBaseSeeder.php
│   ├── CatalogoBaseSeeder.php
│   └── private/                           ← Seeders con datos reales Ciete
├── factories/                             ← Factories para tests
├── manual/
│   ├── README.md
│   ├── 2026_06_02_reset_demo_integral_muestra_reducida.sql  ← Reset demo
│   └── _archivo_historico/                ← Scripts anteriores (no usar)
└── backups/
    └── README.md                          ← Backups locales (no en repo)
```

---

## Migraciones

No borrar, no reordenar. Son la fuente de verdad estructural del proyecto.

```bash
php artisan migrate          # instalar
php artisan migrate:status   # ver estado
```

---

## Seeders activos

| Seeder | Para qué sirve |
|--------|---------------|
| `DatabaseSeeder` | Punto de entrada: llama a todos los de abajo |
| `ContextosClienteSeeder` | Contextos MOEVE, REPSOL, OTROS CLIENTES |
| `RolesSeeder` | Roles del sistema |
| `PermisosSeeder` | Permisos por módulo |
| `RolPermisosSeeder` | Asignación rol → permisos |
| `CatalogoBaseSeeder` | Unidades, tipos de documento, tipos de trabajo |
| `UsuariosInicialesSeeder` | Usuarios demo con roles y contextos |
| `HomeNoticeSeeder` | Avisos del dashboard |

Los seeders en `private/` contienen datos reales de Ciete (excluidos del repo en `.gitignore`).

---

## Scripts manuales

Solo para uso local/demo. **No ejecutar en producción.**

**Script activo:** `manual/2026_06_02_reset_demo_integral_muestra_reducida.sql`  
Devuelve la BD al estado demo estándar con datos MOEVE, REPSOL y OTROS.

---

## Backups

Los backups en `backups/` son locales y no se suben al repo (`.gitignore`).

---

## Advertencias

- No usar `migrate:fresh` sobre `abaco_ciete` — borra todos los datos demo.
- No ejecutar scripts de `manual/` en producción.
- Los dumps de `_archivo_historico/dumps/` son versiones antiguas — no usar.
