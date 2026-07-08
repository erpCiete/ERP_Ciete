# Requisitos técnicos — ERP CIETE en servidores Control Cube

**Para:** Informático de Ciete / Control Cube · **De:** Equipo de desarrollo ERP CIETE · **2026-06-09**

## Qué es la aplicación
Aplicación web en **Laravel (PHP)** con frontend **Inertia + React** (compilado con Vite/npm) y base de datos **MariaDB**. Es lo que hoy usa Ciete como ERP de gestión de trabajos, pedidos, facturas e importaciones.

## Requisitos mínimos del servidor

**PHP**
- PHP **8.2 o superior**
- Composer disponible
- Acceso a consola/SSH para ejecutar comandos `php artisan`
- Extensiones habituales de Laravel: mbstring, openssl, PDO, tokenizer, XML, ctype, JSON, BCMath, Fileinfo, cURL (GD/Intl si se usan)

**Servidor web**
- Apache, Nginx o IIS
- El **document root debe apuntar a la carpeta `public`** del proyecto (no a la raíz del código)
- Soporte de reescritura de URLs (mod_rewrite o equivalente)

**Base de datos**
- **MariaDB 10.4.x** (recomendado) o MySQL compatible
- Base de datos y usuario dedicados, con permisos completos sobre esa base
- Acceso a consola o phpMyAdmin para su gestión

**Permisos de archivos**
- Permisos de escritura en `storage/` y `bootstrap/cache/`

**Frontend / build**
- No es imprescindible Node/npm en el servidor — podemos entregar `public/build` ya compilado
- Si lo prefieren compilar allí: Node 18+ y npm

**Dominio y SSL**
- Dominio o subdominio asignado a la aplicación
- Certificado SSL/HTTPS válido (HTTPS obligatorio)

**Accesos necesarios para desplegar**
- SSH o SFTP para subir el código
- Acceso a consola de base de datos
- Credenciales de panel de administración si existe (Plesk/cPanel)

**Opcional, según uso**
- Cron jobs / tareas programadas (Laravel scheduler)
- Servicio SMTP para que la aplicación pueda enviar correos

## Cómo proponemos hacerlo
Primero un entorno de **preproducción** (copia idéntica) para comprobar que todo funciona igual en el nuevo servidor, y solo después mover producción — minimizando riesgos para los datos y el servicio actual.
