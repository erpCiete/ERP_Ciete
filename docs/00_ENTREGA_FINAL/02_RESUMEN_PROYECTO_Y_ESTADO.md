# Resumen del proyecto y estado actual

## ¿Qué es ERP CIETE?

ERP interno de Ciete Ingenieros S.A. para gestionar el trabajo diario con clientes energéticos (MOEVE, REPSOL). Centraliza:

- **Trabajos** de ingeniería en estaciones de servicio
- **Pedidos** con líneas de tarifa asociadas
- **Facturas** parciales y completas por ítem
- **Cierre** de obra con checklist de validación
- **Exportación Moeve**: PDF, CSV y cuadro ARIBA para tramitación de pedidos
- **Maestros**: contratos, tarifarios, estaciones, empresas
- **Roles y permisos** granulares por contexto de cliente

## Estado a 2026-06-04

| Métrica | Valor |
|---------|-------|
| Versión | v2.2.0 |
| Tests | **270 correctos / 3 fallidos** |
| Build | ✅ Correcto |
| Bloqueantes P0 | 0 |
| Pendientes P1 | 6 (ver `15_PENDIENTES_Y_RIESGOS.md`) |

Los fallos detectados en la última ejecución completa afectan a `AdminAccessTest`, `ContextCreationGuardTest` e `ImportacionesAccessTest`. Consulta [`13_TESTING_VALIDACIÓN.md`](13_TESTING_VALIDACIÓN.md) para ver el detalle.

## Stack técnico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | Laravel | 12.54.1 |
| PHP | PHP | 8.4.12 |
| Adapter SPA | Inertia.js | 2/3 |
| Frontend | React | 19 |
| CSS | Tailwind CSS | 4 |
| Bundler | Vite | 8 |
| Auth | Sanctum (sesiones cookie) | — |
| DB | MySQL 8 | — |

## Funcionalidades completadas

| Módulo | Estado |
|--------|--------|
| Trabajos (tabla Excel + formulario moderno) | ✅ Completo |
| Pedidos con líneas de tarifa | ✅ Completo |
| Facturas parciales y completas | ✅ Completo |
| Cierre de obra (panel de dirección) | ✅ Completo |
| Exportación PDF Moeve | ✅ Completo |
| Exportación CSV Moeve | ✅ Completo |
| Cuadro ARIBA | ✅ Completo |
| Modal "Preparar correo Moeve" | ✅ Completo |
| Parametrización ARIBA en maestros | ✅ Completo |
| Estados automáticos (derivados de pedidos/facturas) | ✅ Completo |
| Concurrencia por campo (<60 min, 409) | ✅ Completo |
| Roles y permisos por contexto | ✅ Completo |
| Soporte restringido a admin técnico | ✅ Completo |
| Password reset con token no reutilizable | ✅ Completo |
| Auditoría completa de cambios | ✅ Completo |
| Importación Excel (xlsx) | ✅ Parcial (CSV no soportado) |
| Política de cambio de código de estación | ✅ Completo |

## Contextos operativos

El sistema distingue tres contextos:

- **MOEVE**: Clientes de la red de gasolineras Moeve Energy. Exportación específica PDF/CSV/ARIBA.
- **REPSOL**: Red de estaciones Repsol. Mismo flujo pero sin exportación ARIBA.
- **OTROS CLIENTES**: Para clientes pequeños o proyectos puntuales.

Cada usuario está asignado a uno o varios contextos. El contexto activo determina qué datos ve y puede crear.

## Datos demo cargados

La base demo contiene datos reales de trabajo importados de Ciete (con prefijo DEMO-):

- **DEMO-610001** — La Senyera I (MOEVE, pedido completo con 8 líneas, exportable)
- **DEMO-610004** — VIRGISA (MOEVE, facturado y cerrado)
- **DEMO-620005** — CRED A. DE LA MIEL (REPSOL, facturación parcial)
- Más de 20 estaciones reales, tarifarios y contratos

## Próximos pasos antes de producción

1. César actualiza valores ARIBA reales en el contrato 772 MOEVE (Maestros → Contratos)
2. Verificar SMTP real en `.env` de producción
3. Ejecutar `npm run build` en servidor
4. Verificar permisos de `storage/`
5. Ver [14_DESPLIEGUE.md](14_DESPLIEGUE.md)
