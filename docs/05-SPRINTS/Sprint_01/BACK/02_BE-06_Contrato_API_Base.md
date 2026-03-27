# BE-06 · Contrato API Base (Sprint 01)

**Estado:** Draft  
**Owner:** Backend  
**Fecha de revision:** 25/03/2026

## 1) Objetivo real
Definir un contrato JSON base unico para respuestas de API (exito y error) en autenticacion inicial, para que Front y Back integren sin interpretaciones distintas.

Este documento es contractual de integracion. No implementa endpoints por si mismo.

## 2) Alcance y no alcance

### 2.1 Si cubre
- Formato comun de respuesta (`success`, `message`, `data`, `meta`).
- Formato comun de error (`error_code`, `errors`).
- Contrato objetivo para `login`, `logout` y `me` en API.
- Compatibilidad con el flujo web actual para no mezclar criterios.
- Tabla base de codigos HTTP y `error_code`.

### 2.2 No cubre
- Implementacion tecnica de endpoints Auth (BE-01).
- RBAC operativo y politicas finales (BE-02).
- Testing funcional final del sprint (BE-04).
- Cierre tecnico global del sprint (BE-07).

## 3) Estado real confirmado en el repo (25/03/2026)

### 3.1 Evidencia revisada
- `bootstrap/app.php`
- `routes/web.php`
- `routes/auth.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `resources/js/Pages/Auth/Login.jsx`

### 3.2 Situacion actual
1. No existe carga de rutas API en bootstrap (`withRouting` solo incluye `web` y `commands`).
2. No hay `routes/api.php` en el repositorio actual.
3. Auth actual funciona en flujo web/session con redirects (`/login`, `/logout`).
4. Login usa `nombre_usuario` y tambien acepta email en ese mismo campo.
5. El formulario real de frontend (Inertia) envia `nombre_usuario` y `password`.
6. Los tests actuales de auth validan redirects, no JSON API.

Conclusion: el backend web actual no es API JSON de Auth; este documento define el contrato para la futura capa API.

## 4) Contrato JSON base v1

### 4.1 Envelope de exito (obligatorio)
```json
{
  "success": true,
  "message": "Operacion correcta",
  "data": {},
  "meta": {
    "timestamp": "2026-03-25T08:30:00Z",
    "request_id": "c3de6fc9-1d9e-4f5c-b7d2-b8e75e96f0f4"
  }
}
```

Reglas:
- `success`: boolean obligatorio.
- `message`: string breve, apto para UI/log.
- `data`: objeto o `null` segun endpoint.
- `meta.timestamp`: ISO-8601 UTC obligatorio.
- `meta.request_id`: opcional pero recomendado para trazabilidad.

### 4.2 Envelope de error (obligatorio)
```json
{
  "success": false,
  "message": "Credenciales incorrectas",
  "error_code": "AUTH_INVALID_CREDENTIALS",
  "errors": {
    "nombre_usuario": [
      "Las credenciales proporcionadas no son validas."
    ]
  },
  "meta": {
    "timestamp": "2026-03-25T08:30:00Z",
    "request_id": "c3de6fc9-1d9e-4f5c-b7d2-b8e75e96f0f4"
  }
}
```

Reglas:
- `error_code`: obligatorio en todo error.
- `errors`: mapa de campo -> lista de mensajes. Puede ser `{}` en errores no ligados a campos.
- `data`: no se envia en respuestas de error.

## 5) Contrato web actual (referencia de proyecto)

| Ruta web | Metodo | Tipo de respuesta actual |
|---|---|---|
| `/login` | GET | Vista Inertia (200) |
| `/login` | POST | Redirect a `index` si OK |
| `/logout` | POST | Redirect a `index` |
| `/admin` | GET | Redirect a `login` si guest |
| `/admin` | GET | 403 si autenticado sin rol admin |

Notas:
- Este comportamiento es correcto para la app web actual.
- Este comportamiento no sustituye el contrato JSON para API.

## 6) Endpoints objetivo de integracion (Auth API v1)

| Metodo | Path | Caso | HTTP esperado |
|---|---|---|---|
| POST | `/api/v1/auth/login` | Login correcto | 200 |
| POST | `/api/v1/auth/login` | Credenciales invalidas | 401 |
| POST | `/api/v1/auth/login` | Error de validacion | 422 |
| POST | `/api/v1/auth/logout` | Logout correcto | 200 |
| GET | `/api/v1/auth/me` | Sesion valida | 200 |
| GET | `/api/v1/auth/me` | Sin autenticacion | 401 |

Notas:
- El versionado/prefijo propuesto es `/api/v1/auth/*` (pendiente de confirmacion final en BE-01).
- El campo de entrada para login en Sprint 01 se mantiene como `nombre_usuario`.
- Se recomienda mantener compatibilidad de nombres de campo con formulario web actual.

## 7) Ejemplos de contrato por endpoint API

### 7.1 Login OK
Request (ejemplo):
```json
{
  "nombre_usuario": "admin",
  "password": "secret"
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
      "is_admin": true,
      "ultimo_login_at": "2026-03-25T08:28:11Z"
    },
    "context": {
      "id_contexto": 3
    }
  },
  "meta": {
    "timestamp": "2026-03-25T08:30:00Z"
  }
}
```

### 7.2 Login KO (credenciales)
```json
{
  "success": false,
  "message": "Credenciales incorrectas",
  "error_code": "AUTH_INVALID_CREDENTIALS",
  "errors": {
    "nombre_usuario": [
      "Las credenciales proporcionadas no son validas."
    ]
  },
  "meta": {
    "timestamp": "2026-03-25T08:30:00Z"
  }
}
```

### 7.3 Login KO (validacion)
```json
{
  "success": false,
  "message": "Error de validacion",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "nombre_usuario": [
      "El campo nombre_usuario es obligatorio."
    ],
    "password": [
      "El campo password es obligatorio."
    ]
  },
  "meta": {
    "timestamp": "2026-03-25T08:31:00Z"
  }
}
```

### 7.4 Logout OK
```json
{
  "success": true,
  "message": "Logout correcto",
  "data": null,
  "meta": {
    "timestamp": "2026-03-25T08:31:00Z"
  }
}
```

### 7.5 Me OK
```json
{
  "success": true,
  "message": "Usuario autenticado",
  "data": {
    "user": {
      "id_usuario": 1,
      "id_contexto": 3,
      "nombre_usuario": "admin",
      "email": "admin@ciete.es",
      "activo": true,
      "is_admin": true,
      "ultimo_login_at": "2026-03-25T08:28:11Z"
    },
    "context": {
      "id_contexto": 3
    }
  },
  "meta": {
    "timestamp": "2026-03-25T08:31:00Z"
  }
}
```

## 8) Mapa minimo HTTP y error_code

| HTTP | error_code | Uso |
|---|---|---|
| 200 | OK | Operacion correcta |
| 400 | BAD_REQUEST | Payload invalido o mal formado |
| 401 | AUTH_INVALID_CREDENTIALS | Credenciales invalidas |
| 401 | AUTH_UNAUTHENTICATED | Sin sesion/token valido |
| 403 | AUTH_FORBIDDEN | Sin permisos suficientes |
| 422 | VALIDATION_ERROR | Fallo de validacion de campos |
| 429 | RATE_LIMIT_EXCEEDED | Exceso de intentos |
| 500 | INTERNAL_ERROR | Error interno no controlado |

## 9) Reglas operativas para implementacion
1. Toda ruta bajo `/api/*` responde siempre JSON (nunca redirects HTML).
2. Toda respuesta API incluye `meta.timestamp`.
3. Toda respuesta de error incluye `error_code`.
4. Validaciones devuelven `422 + VALIDATION_ERROR` con detalle en `errors`.
5. Front no debe depender de textos literales de `message`; debe depender de HTTP + `error_code`.
6. Mantener nombres de campos de login (`nombre_usuario`, `password`) para reducir friccion con la web existente.

## 10) Criterios de aceptacion para marcar BE-06 Done
1. Front y Back aprueban este documento como contrato unico de integracion base.
2. No hay ambiguedad en formato de `login`, `logout` y `me` para exito/error.
3. Se confirma el uso de `error_code` obligatorio en errores.
4. Se confirma el prefijo objetivo `/api/v1/auth/*` (o se documenta el cambio en este mismo archivo).
5. Queda explicita la separacion entre contrato API futuro y comportamiento web actual con redirects.

## 11) Handoff al equipo
- Implementaciones de BE-01 deben respetar este contrato sin variaciones de envelope.
- Si cambia cualquier campo del contrato, se actualiza este archivo antes de mergear.
- Mientras Auth siga en flujo web con redirects, este documento sigue siendo referencia contractual para la API futura.
