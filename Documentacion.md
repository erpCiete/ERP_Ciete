# BE-06 · Contrato API Base (Sprint 01)

**Estado:** Done  
**Owner:** Backend  
**Fecha de revisión:** 25/03/2026

## 1 Objetivo real
Definir y dejar operativa una capa API JSON mínima para autenticación y navegación base de módulos, de forma que Frontend pueda integrarse sin depender de vistas Blade/Inertia ni de redirects web.

Esta API convive con la autenticación web actual del proyecto.

---

## 2 Alcance implementado en Sprint 01

### 2.1 Sí cubre
- Rutas API versionadas bajo `/api/v1/*`
- Endpoints JSON de autenticación:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET /api/v1/auth/me`
- Endpoints stub JSON de módulos:
  - `GET /api/v1/obras`
  - `GET /api/v1/pedidos`
  - `GET /api/v1/estaciones`
- Protección de rutas API mediante autenticación por sesión
- Formato común de respuesta JSON
- Tests funcionales básicos de API en verde

### 2.2 No cubre todavía
- Autenticación por token con Sanctum
- Middleware específico de contexto cliente Repsol/Cepsa
- Middleware RBAC por permiso reutilizable
- Lógica real de negocio en módulos
- Filtrado real por `id_contexto` en consultas de dominio

---

## 3 Estado real implementado en el repositorio

### 3.1 Archivos implicados
- `bootstrap/app.php`
- `routes/api.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ObrasController.php`
- `app/Http/Controllers/Api/PedidosController.php`
- `app/Http/Controllers/Api/EstacionesController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Models/User.php`
- `tests/Feature/ApiAuthTest.php`

### 3.2 Decisión técnica actual
La API implementada en Sprint 01 usa autenticación por **sesión** y middleware `web`, no token-based auth.  
Esto se ha hecho para reutilizar la lógica existente del login web y entregar una capa API funcional mínima en este sprint.

---

## 4 Rutas implementadas

| Método | Path | Auth requerida | Estado |
|---|---|---:|---|
| POST | `/api/v1/auth/login` | No | Implementado |
| POST | `/api/v1/auth/logout` | Sí | Implementado |
| GET | `/api/v1/auth/me` | Sí | Implementado |
| GET | `/api/v1/obras` | Sí | Stub implementado |
| GET | `/api/v1/pedidos` | Sí | Stub implementado |
| GET | `/api/v1/estaciones` | Sí | Stub implementado |

---

## 5 Contrato JSON base

### 5.1 Envelope de éxito
```json
{
  "success": true,
  "message": "Operacion correcta",
  "data": {},
  "meta": {
    "timestamp": "2026-03-25T15:57:00.000000Z"
  }
}
Reglas:

success: boolean obligatorio
message: string breve
data: objeto, array o null
meta.timestamp: obligatorio en ISO-8601 UTC
5.2 Envelope de error
{
  "message": "Unauthenticated."
}
Notas:

En esta fase, los errores de autenticación estándar de Laravel siguen devolviendo el formato por defecto del framework en algunos casos.
La unificación completa de errores con error_code queda pendiente para una iteración posterior.
6) Endpoints de autenticación
6.1 POST /api/v1/auth/login
Objetivo
Autenticar al usuario usando nombre_usuario o email en el campo nombre_usuario, junto con password.

Request
{
  "nombre_usuario": "admin",
  "password": "Admin1234!"
}
Response OK
{
  "success": true,
  "message": "Login correcto",
  "data": {
    "user": {
      "id_usuario": 1,
      "id_contexto": 3,
      "nombre": "Administrador",
      "apellidos": "ERP Ciete",
      "nombre_usuario": "admin",
      "email": "admin@ciete.es",
      "telefono": null,
      "activo": true,
      "ultimo_login_at": "2026-03-25T15:57:00.000000Z",
      "is_admin": true
    },
    "context": {
      "id_contexto": 3
    }
  },
  "meta": {
    "timestamp": "2026-03-25T15:57:00.000000Z"
  }
}
Comportamiento real
Regenera sesión
Actualiza ultimo_login_at
Inserta trazabilidad en sesiones_login
Guarda sesion_login_id en sesión
Posibles respuestas
200 si credenciales válidas
422 si faltan campos
422 o error de validación de credenciales según flujo actual de LoginRequest
6.2 POST /api/v1/auth/logout
Objetivo
Cerrar la sesión autenticada actual y registrar el cierre en sesiones_login.

Request
Sin body obligatorio.

Response OK
{
  "success": true,
  "message": "Logout correcto",
  "data": null,
  "meta": {
    "timestamp": "2026-03-25T15:58:00.000000Z"
  }
}
Comportamiento real
Busca la sesión de login abierta
Marca fecha_hora_logout
Ejecuta Auth::guard('web')->logout()
Invalida la sesión
Regenera el token CSRF
Posibles respuestas
200 si logout correcto
401 si no hay usuario autenticado
6.3 GET /api/v1/auth/me
Objetivo
Devolver el usuario autenticado actual y su contexto.

Request
Sin body.

Response OK
{
  "success": true,
  "message": "Usuario autenticado",
  "data": {
    "user": {
      "id_usuario": 1,
      "id_contexto": 3,
      "nombre": "Administrador",
      "apellidos": "ERP Ciete",
      "nombre_usuario": "admin",
      "email": "admin@ciete.es",
      "telefono": null,
      "activo": true,
      "ultimo_login_at": "2026-03-25T15:57:00.000000Z",
      "is_admin": true
    },
    "context": {
      "id_contexto": 3
    }
  },
  "meta": {
    "timestamp": "2026-03-25T15:58:10.000000Z"
  }
}
Response sin autenticación
{
  "message": "Unauthenticated."
}
Posibles respuestas
200 si hay sesión autenticada
401 si no hay sesión activa
7) Endpoints stub de módulos
7.1 GET /api/v1/obras
{
  "success": true,
  "message": "Listado de obras disponible",
  "data": [],
  "meta": {
    "timestamp": "2026-03-25T15:58:30.000000Z"
  }
}
7.2 GET /api/v1/pedidos
{
  "success": true,
  "message": "Listado de pedidos disponible",
  "data": [],
  "meta": {
    "timestamp": "2026-03-25T15:58:30.000000Z"
  }
}
7.3 GET /api/v1/estaciones
{
  "success": true,
  "message": "Listado de estaciones disponible",
  "data": [],
  "meta": {
    "timestamp": "2026-03-25T15:58:30.000000Z"
  }
}
Objetivo de estos endpoints
Desbloquear la integración con Frontend
Fijar URLs estables de consumo
Fijar el formato JSON base
Permitir evolucionar la lógica real en sprints posteriores sin cambiar contrato
8) Seguridad actual
8.1 Auth
Las rutas API protegidas usan:

middleware web
middleware auth
Esto implica autenticación por sesión.

8.2 RBAC
El modelo User ya dispone de métodos auxiliares:

hasRole(string $slug): bool
hasPermission(string $slug): bool
isAdmin(): bool
El middleware reutilizable por permiso queda pendiente.

8.3 Contexto cliente
El usuario tiene id_contexto y la API ya devuelve ese dato en respuestas de auth, pero todavía no existe middleware dedicado que fuerce segregación de consultas por cliente.

9) Testing ejecutado
9.1 Test implementado
tests/Feature/ApiAuthTest.php
9.2 Casos cubiertos
Login API correcto
Acceso no autenticado a /api/v1/auth/me devuelve 401
Acceso autenticado a /api/v1/auth/me
Acceso autenticado a módulos stub
9.3 Estado de ejecución
Tests funcionales en verde
Existen warnings deprecados de PDO/MySQL en PHP 8.5, pero no bloquean la ejecución ni invalidan el resultado funcional
10) Cómo probar manualmente
10.1 Levantar backend
php artisan serve
10.2 URLs base
http://127.0.0.1:8000/api/v1/auth/login
http://127.0.0.1:8000/api/v1/auth/logout
http://127.0.0.1:8000/api/v1/auth/me
http://127.0.0.1:8000/api/v1/obras
http://127.0.0.1:8000/api/v1/pedidos
http://127.0.0.1:8000/api/v1/estaciones
10.3 Ejecutar tests
php artisan test --filter=ApiAuthTest
11) Limitaciones conocidas
La API usa sesión, no token.
Los errores JSON aún no están completamente unificados con error_code.
Los endpoints de módulos devuelven colecciones vacías.
Falta middleware explícito de contexto Repsol/Cepsa.
Falta middleware RBAC reutilizable por permisos.
12) Criterio de Done en este estado
Se considera cumplido el mínimo aceptable de Sprint 01 backend porque:

Existe capa API funcional bajo /api/v1/
Login, logout y me funcionan en JSON
Los módulos principales tienen rutas stub operativas
Las rutas sensibles están protegidas por autenticación
Hay tests funcionales ejecutándose correctamente
Frontend ya dispone de endpoints estables para integración inicial

13) Handoff al equipo
Frontend puede integrar ya contra /api/v1/auth/
Frontend puede usar /api/v1/obras, /api/v1/pedidos y /api/v1/estaciones como contratos base
El siguiente paso técnico recomendado es implementar middleware de contexto cliente y RBAC por permisos
Si se migra a Sanctum en el futuro, debe actualizarse este documento