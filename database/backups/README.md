# Backups locales — ERP CIETE

Estos archivos son backups locales de puntos de restauración de la BD demo.

**No se suben al repositorio** (excluidos en `.gitignore`).  
**No son documentación activa de entrega.**

Consérvelos localmente para poder restaurar si algo va mal durante el desarrollo.

---

## Contenido

Backups automáticos creados antes de operaciones destructivas (resets, importaciones masivas):

- `*_pre_p1_14_real_*` — antes de importación P1-14 real
- `*_pre_fase_b_*` — antes de fase B
- `*_pre_b1_2_*` — antes de casos validación B1/B2
- `*_pre_limpieza_demo_*` — antes de limpieza demo
- `*_pre_reset_integral_demo_*` — antes de reset integral (el más reciente útil)
- `*_pre_tarifarios_alternativos_*` — antes de añadir tarifarios alternativos

El más reciente útil para restaurar la demo es:
`abaco_ciete_pre_reset_integral_demo_20260602_165854.sql`
