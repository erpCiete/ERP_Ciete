# ERP CIETE — Léeme primero

**Versión:** v2.2.0
**Fecha de entrega:** 2026-06-04
**Estado:** En revisión funcional; build correcto y 3 tests pendientes
**Desarrollado por:** ABACO para Ciete Ingenieros S.A.

---

## ¿Qué es esto?

ERP interno para Ciete Ingenieros. Gestiona trabajos de ingeniería para clientes energéticos (MOEVE, REPSOL), pedidos, facturas, cierre de obra y exportación de documentos al formato operativo de MOEVE (PDF, CSV, cuadro ARIBA).

## ¿Por dónde empezar?

| Quiero... | Lee... |
|-----------|--------|
| Instalar en local | [01_INSTALACIÓN_LOCAL.md](01_INSTALACIÓN_LOCAL.md) |
| Ver el estado actual | [02_RESUMEN_PROYECTO_Y_ESTADO.md](02_RESUMEN_PROYECTO_Y_ESTADO.md) |
| Entender la arquitectura | [04_ARQUITECTURA_TÉCNICA.md](04_ARQUITECTURA_TÉCNICA.md) |
| Entender la base de datos | [05_BASE_DATOS_DECISIONES.md](05_BASE_DATOS_DECISIONES.md) |
| Saber cómo trabaja cada rol | [06_GUÍA_USO_POR_ROLES.md](06_GUÍA_USO_POR_ROLES.md) |
| Entender contratos y tarifas | [07_MAESTROS_CONTRATOS_TARIFARIOS.md](07_MAESTROS_CONTRATOS_TARIFARIOS.md) |
| Entender el flujo principal | [08_TRABAJOS_PEDIDOS_FACTURAS_CIERRE.md](08_TRABAJOS_PEDIDOS_FACTURAS_CIERRE.md) |
| Entender exportación Moeve | [09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md](09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md) |
| Ver qué queda pendiente | [15_PENDIENTES_Y_RIESGOS.md](15_PENDIENTES_Y_RIESGOS.md) |
| Inicio rápido | [16_GUÍA_RÁPIDA.md](16_GUÍA_RÁPIDA.md) |

## Estado en una línea

**Build de producción correcto. Suite actual: 270 tests correctos y 3 fallidos.**
Los campos ARIBA están parametrizados. El flujo Trabajo → Pedido → Factura → Cierre funciona de extremo a extremo.

## Lo más importante que debes saber

1. El ERP trabaja por **contextos** (MOEVE / REPSOL / OTROS CLIENTES). Cada usuario opera en su contexto.
2. El **tarifario se bloquea** en cuanto se crea el primer pedido — no se puede cambiar después.
3. Los **estados del trabajo** son derivados automáticamente de pedidos y facturas. No se editan a mano.
4. La exportación Moeve (PDF, CSV, ARIBA) requiere que el contrato tenga los **campos ARIBA parametrizados** en Maestros.
5. El **cierre** de un trabajo lo hace Dirección desde `/cierre`, no Ejecución.
6. El soporte es exclusivo del **Admin técnico**. Dirección, Ejecución y Contabilidad ven 403.

## Advertencias

- Los archivos de `database/backups/` no se suben al repo (están en `.gitignore`).
- No uses `migrate:fresh` en la base demo local (`abaco_ciete`) — borras datos reales de prueba.
- Los valores ARIBA configurados en demo (OPEX-MOEVE-2026, etc.) son placeholders — César debe actualizarlos con los valores reales de Moeve antes de usar en producción.
