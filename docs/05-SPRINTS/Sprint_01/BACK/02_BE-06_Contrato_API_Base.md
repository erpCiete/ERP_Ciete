# BE-06 · Contrato API Base (Sprint 01)

**Estado:** Done  
**Owner:** Backend  
**Fecha de revision:** 27/03/2026

## 1) Objetivo
Definir y dejar documentado el contrato minimo de API JSON para autenticacion y rutas base de modulos.

## 2) Estado actual implementado

### 2.1 Cobertura real
- Rutas API cargadas en bootstrap con `routes/api.php`.
- Versionado API en `/api/v1/*`.
- Auth API por sesion (`web` + `auth`):
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET /api/v1/auth/me`
- Modulos stub:
  - `GET /api/v1/obras`
  - `GET /api/v1/pedidos`
  - `GET /api/v1/estaciones`

### 2.2 Lo que no cubre todavia
- Token auth con Sanctum.
- Middleware dedicado de contexto cliente.
- RBAC por permiso a nivel API de negocio.
- Logica de dominio completa en controladores stub.

## 3) Archivos de referencia
- `bootstrap/app.php`
- `routes/api.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ObraController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/EstacionController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `tests/Feature/ApiAuthTest.php`

## 4) Contrato actual de login
- Campo oficial: `email`.
- Campo `password` obligatorio.
- Compatibilidad temporal: `nombre_usuario` y `login` se mapean internamente a `email` antes de validar.
- Si el valor no es un email valido, responde error de validacion.

## 5) Rutas y estado

| Metodo | Path | Auth requerida | Estado |
|---|---|---:|---|
| POST | `/api/v1/auth/login` | No | Implementado |
| POST | `/api/v1/auth/logout` | Si | Implementado |
| GET | `/api/v1/auth/me` | Si | Implementado |
| GET | `/api/v1/obras` | Si | Stub |
| GET | `/api/v1/pedidos` | Si | Stub |
| GET | `/api/v1/estaciones` | Si | Stub |

## 6) Formato JSON actual

### 6.1 Exito
```json
{
  "success": true,
  "message": "Operacion correcta",
  "data": {},
  "meta": {
    "timestamp": "2026-03-27T10:00:00.000000Z"
  }
}
```

### 6.2 Error no autenticado
```json
{
  "message": "Unauthenticated."
}
```

## 7) Ejemplo de login API

Request:
```json
{
  "email": "admin@ciete.es",
  "password": "Admin1234!"
}
```

Response OK:
```json
{
  "success": true,
  "message": "Login correcto",
  "data": {
    "user": {
      "id_usuario": 1,
      "id_contexto": 3,
      "nombre_usuario": "admin",
      "email": "admin@ciete.es",
      "activo": true,
      "is_admin": true
    },
    "context": {
      "id_contexto": 3
    }
  },
  "meta": {
    "timestamp": "2026-03-27T10:00:00.000000Z"
  }
}
```

## 8) Seguridad actual
- API protegida con sesion (`web` + `auth`).
- Sin Sanctum operativo en este sprint.
- `User` ya expone helpers base de roles/permisos; middleware reusable queda pendiente.

## 9) Criterio de cierre BE-06
- Contrato API base documentado y alineado con el codigo actual.
- Endpoints de auth y stubs disponibles para integracion.
- Campo de login documentado de forma coherente con validacion actual.

