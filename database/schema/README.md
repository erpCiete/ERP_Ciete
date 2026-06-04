# Schema — ERP CIETE v2.2.0

## `abaco_ciete_v220_2026-06-04.sql`

Dump completo de la base de datos demo en el estado de entrega (v2.2.0, 2026-06-04).

Incluye estructura + datos demo MOEVE, REPSOL y OTROS CLIENTES.

### Cómo restaurar

```bash
mysql -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
```

O desde XAMPP:
```bash
C:\xampp\mysql\bin\mysql.exe -u root < database/schema/abaco_ciete_v220_2026-06-04.sql
```

### Usuarios demo incluidos

Ver contraseñas en `docs/00_ENTREGA_FINAL/01_INSTALACIÓN_LOCAL.md`

| Email | Rol |
|-------|-----|
| cesar@ciete.es | Dirección |
| contable@ciete.es | Contabilidad |
| admin@ciete.es | Admin técnico |
| usuario@ciete.es | Ejecución |
| moeve@ciete.es | Ejecución Moeve |

### Alternativa — instalar desde cero con migraciones

```bash
php artisan migrate
php artisan db:seed
```

Esto crea la estructura limpia + datos base (sin los datos demo de MOEVE/REPSOL).
Para cargar los datos demo usar el script de reset:

```bash
mysql -u root abaco_ciete < database/manual/2026_06_02_reset_demo_integral_muestra_reducida.sql
```
