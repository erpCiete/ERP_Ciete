# Scripts SQL manuales — ERP CIETE

**SOLO PARA USO LOCAL / DEMO. NO EJECUTAR EN PRODUCCIÓN.**

---

## Script activo

### `2026_06_02_reset_demo_integral_muestra_reducida.sql`

Reset completo de la base demo al estado de validación integral de 2026-06-02.
Incluye datos DEMO-610XXX (MOEVE), DEMO-620XXX (REPSOL) y DEMO-630XXX (OTROS).

Cuándo usarlo: cuando quieras devolver la BD local al estado demo estándar.

```bash
mysql -u root abaco_ciete < database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql
```

---

## Scripts archivados

En `_archivo_historico/` están los scripts de validaciones anteriores (B1, B2, muestra operativa 50 casos).
No ejecutar — su estado ya fue incorporado en el script activo.

---

## Advertencias

- No ejecutar ningún script de esta carpeta en producción.
- No usar `migrate:fresh` sobre `abaco_ciete` — borra todos los datos demo.
- Los backups SQL están en `database/backups/` (no se suben al repo, ver `.gitignore`).
