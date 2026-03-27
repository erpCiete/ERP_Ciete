# BE-06 · Contrato API Base (Sprint 01)

**Estado:** Done  
**Owner:** Backend  
**Fecha de revision:** 27/03/2026

## 1) Objetivo
Documentar el contrato de integracion actual entre backend y frontend para auth API y modulos stub.

## 2) Alcance implementado

### 2.1 Incluye
- API versionada bajo `/api/v1/*`.
- Endpoints auth JSON:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET /api/v1/auth/me`
- Endpoints stub:
  - `GET /api/v1/obras`
  - `GET /api/v1/pedidos`
  - `GET /api/v1/estaciones`
- Proteccion por sesion (`web` + `auth`).

### 2.2 Pendiente
- Auth por token (Sanctum).
- Middleware de contexto cliente.
- RBAC API por permiso.
- Logica real de negocio en modulos.

## 3) Estado real del login
- El login actual valida y autentica con `email`.
- `nombre_usuario` y `login` se aceptan solo como alias que se mapean a `email` en `LoginRequest`.
- El frontend actual envia `email` y `password`.

## 4) Archivos clave
- `bootstrap/app.php`
- `routes/api.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ObraController.php`
- `app/Http/Controllers/Api/PedidoController.php`
- `app/Http/Controllers/Api/EstacionController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `tests/Feature/ApiAuthTest.php`

## 5) Rutas publicadas

| Metodo | Ruta | Auth |
|---|---|---|
| POST | `/api/v1/auth/login` | No |
| POST | `/api/v1/auth/logout` | Si |
| GET | `/api/v1/auth/me` | Si |
| GET | `/api/v1/obras` | Si |
| GET | `/api/v1/pedidos` | Si |
| GET | `/api/v1/estaciones` | Si |

## 6) Contrato de respuesta

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

## 7) Ejemplo login API

Request:
```json
{
  "email": "admin@ciete.es",
  "password": "Admin1234!"
}
```

Response:
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

## 8) Pruebas de referencia
- `tests/Feature/ApiAuthTest.php` cubre login, `me` y acceso a stubs con autenticacion.

## 9) Criterio de done del documento
- El documento refleja el estado real actual del repositorio sin contratos legacy.

