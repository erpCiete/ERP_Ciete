# Guion para la reunión — Migración a Control Cube

**2026-06-09 · Oficina de Ciete · Notas para mí, para guiar la conversación**

## Cómo abrir
"Vemos viable migrar el ERP a servidores de Control Cube, pero de forma controlada: primero preproducción, validación completa, y después producción. Para avanzar necesito que me confirméis algunos datos del servidor — os he traído el listado de requisitos por escrito."

→ *(entregar la hoja de requisitos técnicos)*

## Preguntas a hacer, bloque por bloque

**Servidor:** ¿SO (Linux/Windows)? ¿Tipo (VPS/dedicado/hosting/on-premise/Plesk/cPanel/Docker)? ¿Servidor web (Apache/Nginx/IIS)? ¿Pueden apuntar la raíz pública a `/public`?

**PHP:** ¿Qué versión tienen? (necesitamos 8.2+) · ¿Composer? · ¿SSH/`php artisan`? · ¿permisos de escritura en `storage`/`bootstrap/cache`?

**Base de datos:** ¿MariaDB o MySQL, qué versión? · ¿phpMyAdmin o consola? · ¿BD y usuario propios para nosotros? · ¿cómo hacen backups?

**Frontend:** ¿Node/npm en servidor, o prefieren que les demos `public/build` ya compilado?

**Accesos:** ¿SSH/SFTP? · ¿panel de administración? · ¿quién va a tener acceso y cómo gestionamos las credenciales?

**Dominio/SSL:** ¿qué dominio o subdominio se usaría? · ¿quién gestiona el SSL?

**Correo:** ¿hay SMTP real disponible para que la app envíe correos?

## Cómo plantear la migración
Backup → preproducción en Control Cube → migrar código + `.env` + `public/build` + `storage` + base de datos → validar todo (login, roles, trabajos, pedidos, facturas, importaciones) → ajustar lo que falle → pasar a producción → mantener el entorno actual como respaldo un tiempo.

## Si preguntan "¿por qué no lo hacemos ya?"
Riesgos de migrar sin validar: versión de PHP o de MariaDB/MySQL distinta, dominio mal apuntado (fuera de `/public`), permisos incorrectos, `.env` mal configurado, falta de SSL o de backups, assets rotos, pérdida de datos, y no tener forma de volver atrás.

## Lo que quiero dejar cerrado o anotado hoy
- [ ] Servidor definitivo
- [ ] Motor de base de datos confirmado
- [ ] Dominio/subdominio
- [ ] ¿Hacemos preproducción? (sí/no)
- [ ] Quién despliega
- [ ] Quién mantiene los backups
- [ ] Quién custodia las credenciales
- [ ] Fecha/ventana de cambio aproximada

## Notas de la reunión (para rellenar in situ)


