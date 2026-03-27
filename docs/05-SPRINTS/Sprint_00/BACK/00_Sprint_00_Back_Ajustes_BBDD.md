# Sprint 00 · BACK · Ajustes de BBDD

## Objetivo
Dejar el esquema SQL base del ERP más avanzado para arrancar desarrollo con control de roles/permisos, trazabilidad y auditoría, alineado con requisitos de cliente.

## Archivo modificado
- `database/schema/erp_ciete_base.sql`

## Cambios realizados

### 1) RBAC (roles y permisos)
Se añadieron tablas para autorización avanzada:
- `roles`
- `permisos`
- `rol_permisos`
- `usuario_roles`
- `usuario_permisos`

También se actualizó `usuarios` para mantener compatibilidad inicial con `rol_legacy` y permitir transición a RBAC completo.

### 2) Regla especial de cierre (caso César)
Se añadió el rol funcional:
- `control_cierre`

Y permisos asociados:
- `proyecto_editar_cerrado`
- `proyecto_reabrir_cerrado`
- `proyecto_cerrar`

Se dejó ejemplo SQL de asignación del rol a usuario `cesar`.

### 3) Trazabilidad de estados
Se añadió:
- `proyecto_historial_estados`

Permite guardar:
- estado anterior
- estado nuevo
- fecha de cambio
- usuario que realiza el cambio
- motivo

### 4) Auditoría transversal
Se añadió:
- `auditoria_cambios`

Permite registrar evento de negocio/técnico:
- entidad y entidad_id
- acción
- usuario
- ip/user-agent
- detalle JSON

### 5) Índices para operación y crecimiento
Se añadieron índices compuestos para consultas frecuentes:
- cliente + estado + fecha en proyectos/facturas
- cliente + nombre en estaciones
- cliente + estado en pedidos
- índices de historial y auditoría por fecha

### 6) Semilla inicial de seguridad
Se añadieron inserciones base de:
- roles de sistema (`admin`, `gestion`, `tecnico`, `consulta`, `control_cierre`)
- permisos clave de proyectos/pedidos/facturas/legalizaciones/informes
- mapeo rol-permisos inicial

## Motivos
- Cumplir requisito cliente de control por permisos en trabajos cerrados.
- Evitar dependencias de rol por nombre personal.
- Preparar una base de datos robusta para varios miles de registros/año.
- Facilitar auditoría y trazabilidad desde el inicio.

## Impacto funcional esperado
- Mayor seguridad y gobernanza operativa.
- Mejor soporte para reglas de negocio críticas de cobro/cierre.
- Base preparada para crecimiento de usuarios y módulos.

## Pendientes de implementación (capa aplicación)
- Aplicar verificación de permisos en servicios/controladores.
- Registrar automáticamente historial de estado y auditoría en operaciones críticas.
- Unificar estrategia de autenticación si se mantiene `users` de Laravel en paralelo a `usuarios` del ERP.

## Criterio de validación técnica
- Ejecutar SQL base en entorno limpio sin errores.
- Verificar creación de tablas RBAC, historial y auditoría.
- Verificar semilla de roles/permisos y mapeos.
