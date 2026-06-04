# Soporte, estado del sistema y recuperación de contraseña

## Soporte interno

### Acceso
Solo el Admin técnico (`admin@ciete.es`) puede acceder a `/soporte` y `/admin/soporte`. El resto de roles recibe **403**.

### Qué puede hacer el admin
- Ver todos los tickets abiertos con filtros (estado, prioridad)
- Responder a tickets con comentarios
- Cambiar el estado: Pendiente → En revisión → Resuelta → Archivada
- Cerrar tickets

### Cómo crear tickets (cualquier usuario autenticado con permiso)
Actualmente los tickets se crean a través del flujo interno. El admin técnico puede crearlos desde el panel. Los usuarios normales no tienen acceso al módulo de soporte por diseño.

---

## Estado del sistema (`/estado`)

Solo accesible para el Admin técnico. Muestra:

| Check | Qué verifica |
|-------|-------------|
| Base de datos | Conectividad, driver, nombre, host |
| Disco/Storage | Capacidad libre, permisos de escritura |
| Correo | Canal configurado (mailer) |
| Cola | Driver de jobs |
| Aplicación | Versión, entorno, debug mode |
| Mantenimiento | Si está activo el modo mantenimiento |

Incluye diagnóstico técnico con detalle adicional para el admin.

---

## Recuperación de contraseña

### Flujo
1. Usuario va a `/forgot-password`
2. Introduce su email
3. Si el email existe en el sistema, recibe un correo con enlace
4. El enlace lleva a `/reset-password?token=...`
5. El usuario introduce la nueva contraseña (confirmación requerida)
6. El token no es reutilizable

### Seguridad
- **No revela si el email existe**: la misma respuesta para email existente e inexistente ("Si existe una cuenta con ese correo, recibirás un enlace...")
- **Token de un solo uso**: una vez usado, no se puede reutilizar
- **Expiración**: el token expira (tiempo configurable en `.env`)

### En local (desarrollo)
El correo no se envía sino que se guarda en `storage/logs/laravel.log`. Buscar `PASSWORD RESET` en el log para obtener el enlace.

```bash
tail -f storage/logs/laravel.log | grep "reset"
```

### En producción
Configurar el mailer SMTP en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ciete.es
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS=noreply@ciete.es
MAIL_FROM_NAME="ERP Ciete"
```

---

## Modo mantenimiento

El admin puede activar/desactivar el modo mantenimiento desde `/admin`.

Cuando está activo:
- Los usuarios ven una pantalla de "Sistema en mantenimiento"
- El admin y los usuarios con permiso de mantenimiento sí pueden acceder
- Los cambios son inmediatos (no requiere reiniciar)

Alternativa por línea de comandos:
```bash
php artisan down    # activar mantenimiento
php artisan up      # desactivar mantenimiento
```
