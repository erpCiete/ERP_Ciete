# Reunión técnica — Migración ERP CIETE a servidores Control Cube

**2026-06-09 · Oficina de Ciete · Con: Informático de Ciete / Control Cube**

## 1. Objetivo
Valorar si y cómo migrar el ERP CIETE del entorno actual a servidores de Control Cube. Vemos la migración viable, pero de forma controlada: primero conocer el servidor de destino, luego decidir pasos, tiempos y responsables. Sin migrar a ciegas.

## 2. Stack del ERP (resumen)
- Laravel + PHP 8.2+ (Composer) · MariaDB 10.4.x
- Inertia + React + Vite/npm → `public/build`
- Raíz web: carpeta `public`
- Sensible: `.env`, `storage`, `bootstrap/cache`, `public/build`, backups
- Despliegue actual: documentado, manual

## 3. Información que pedimos (pendiente de Ciete/Control Cube)

**Servidor:** SO (Linux/Windows) · tipo (VPS/dedicado/hosting/on-premise/Plesk/cPanel/Docker) · servidor web (Apache/Nginx/IIS) · ¿ruta pública a `/public`?

**PHP/Laravel:** versión PHP (necesitamos 8.2+) · extensiones · ¿Composer? · ¿`php artisan` por SSH? · permisos en `storage`/`bootstrap/cache` · ¿cron? · ¿colas?

**Base de datos:** ¿MariaDB o MySQL? versión exacta · ¿phpMyAdmin/consola? · usuario+BD para pre/producción · backups · ¿aceptan MariaDB?

**Frontend/build:** ¿Node/npm en servidor? · ¿subimos `public/build` ya compilado? · límites para assets

**Accesos:** SSH · SFTP · panel admin · quién tendrá acceso · gestión de credenciales

**Dominio/seguridad:** dominio/subdominio previsto · SSL/HTTPS · acceso (público con login / red interna / VPN / IP) · política de seguridad y backups

**Correo:** ¿SMTP real? · remitente autorizado · límites de envío

> Todo pendiente de confirmar — no asumimos nada.

## 4. Migración propuesta
1. Versión estable lista → 2. Backup código + BD → 3. Preproducción en Control Cube → 4. Migrar código, `.env`, `public/build`, `storage`, BD → 5. Validar (login, roles, trabajos, pedidos, facturas, importaciones/exportaciones) → 6. Ajustar diferencias del servidor → 7. Migración a producción → 8. Mantener entorno anterior como respaldo temporal.

## 5. Riesgos sin validar
PHP incompatible · MariaDB/MySQL incompatible · dominio apuntando fuera de `/public` · permisos incorrectos · `.env` mal configurado · falta de SSL · falta de backups · `public/build` roto · pérdida/sobrescritura de datos · sin rollback.

## 6. Decisiones a cerrar
Servidor definitivo · motor de BD · dominio/subdominio · ¿preproducción sí/no? · quién despliega · quién mantiene backups · quién custodia credenciales · fecha/ventana de cambio.

## 7. Frase para la reunión
> "Por nuestra parte vemos viable migrar el ERP a servidores de Control Cube, pero lo haría de forma controlada: primero preproducción, validación completa y después producción. Para avanzar necesitamos confirmar la ficha técnica del servidor, accesos, base de datos, dominio, SSL y backups."
