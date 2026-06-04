# Despliegue en producción

## Preparación local (antes de subir)

```bash
# 1. Asegúrate de que los tests pasan
php artisan test

# 2. Compila los assets para producción
npm run build

# 3. Comprueba que no hay errores de whitespace
git diff --check
```

## Variables de entorno en producción

Crea o edita el `.env` en el servidor con estos valores:

```env
APP_NAME="ERP Ciete"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.ciete.es   # URL real del servidor

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abaco_ciete
DB_USERNAME=usuario_produccion
DB_PASSWORD=contraseña_segura

SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ciete.es
MAIL_PASSWORD=contraseña_smtp
MAIL_FROM_ADDRESS=noreply@ciete.es
MAIL_FROM_NAME="ERP Ciete"

# Clave de la aplicación (generar nueva para producción)
APP_KEY=base64:...
```

**No copies el `.env` de local a producción** — genera una nueva `APP_KEY` con:
```bash
php artisan key:generate
```

## Pasos de despliegue

### 1. Subir el código al servidor
```bash
git pull origin main
# o rsync / scp si no hay git en el servidor
```

### 2. Instalar/actualizar dependencias
```bash
composer install --no-dev --optimize-autoloader
npm install        # si node está disponible en el servidor
npm run build      # compila los assets
```

Si el servidor no tiene Node, compila en local y sube la carpeta `public/build/`.

### 3. Ejecutar migraciones
```bash
php artisan migrate --force
```

El `--force` evita la confirmación interactiva en producción.

### 4. Limpiar y recompilar caché
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Permisos de storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Reiniciar queue workers (si aplica)
```bash
php artisan queue:restart
```

---

## Checklist post-deploy

- [ ] La aplicación carga en `https://erp.ciete.es`
- [ ] Login funciona con las cuentas de producción
- [ ] El correo de recuperación de contraseña llega
- [ ] La exportación PDF/CSV funciona
- [ ] El historial de auditoría registra cambios
- [ ] Los backups automáticos de BD están configurados

---

## Estructura de carpetas en servidor

```
/var/www/erp-ciete/
├── app/
├── public/               ← Document root del servidor web
│   └── build/            ← Assets compilados (NO en git)
├── storage/              ← Logs, archivos temporales (NO en git)
├── .env                  ← Configuración (NO en git)
└── ...
```

El `Document Root` del servidor web (Apache/Nginx) debe apuntar a `/var/www/erp-ciete/public/`, no al raíz del proyecto.

### Nginx ejemplo
```nginx
server {
    listen 80;
    server_name erp.ciete.es;
    root /var/www/erp-ciete/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}
```

---

## Lo que NO subir al repositorio

Asegúrate de que `.gitignore` incluye:
```
.env
/storage/*.key
/public/build
/vendor
/node_modules
/database/backups/
```
