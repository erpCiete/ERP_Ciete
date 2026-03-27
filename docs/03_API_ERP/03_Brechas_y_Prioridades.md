# Brechas y prioridades · ERP Ciete

## 1. Objetivo

Comparar lo requerido por negocio con la cobertura API para priorizar implementación y reducir riesgo de operación/cobro.

## 2. Matriz de brechas inicial

| ID    | Brecha detectada                                                  | Impacto                              | Prioridad | Responsable sugerido | Sprint objetivo | Estado    |
| ----- | ----------------------------------------------------------------- | ------------------------------------ | --------- | -------------------- | --------------- | --------- |
| BR-01 | Separación estricta Repsol/Cepsa sin mezcla en consultas/reportes | Riesgo crítico de datos y reporting  | Alta      | Backend              | Sprint 01       | Pendiente |
| BR-02 | Flujo de estados completo con reglas de transición                | Riesgo operativo en control de obra  | Alta      | Backend              | Sprint 01       | Pendiente |
| BR-03 | Bloqueo de obras cerradas por permisos                            | Riesgo de modificación indebida      | Alta      | Backend              | Sprint 01       | Pendiente |
| BR-04 | Coherencia obra-pedido-factura (validación de importes)           | Riesgo directo de cobro              | Alta      | Backend              | Sprint 01       | Pendiente |
| BR-05 | Legalizaciones 1:N con trazabilidad temporal                      | Pérdida de control documental        | Alta      | Backend              | Sprint 01       | Pendiente |
| BR-06 | Búsqueda de estaciones por cliente con buen rendimiento           | Ineficiencia operativa               | Media     | Backend              | Sprint 02       | Pendiente |
| BR-07 | Endpoints de informes por cliente/tipo/estado                     | Baja visibilidad de negocio          | Alta      | Backend              | Sprint 02       | Pendiente |
| BR-08 | Registro de auditoría (quién, qué, cuándo)                        | Falta de trazabilidad                | Alta      | Backend              | Sprint 02       | Pendiente |
| BR-09 | Consistencia visual/funcional en consumo de estados API           | Fricción de uso en operación diaria  | Media     | Frontend             | Sprint 01       | Pendiente |
| BR-10 | Manejo robusto de errores API en UI                               | Riesgo de uso incorrecto y retrabajo | Media     | Frontend             | Sprint 01       | Pendiente |

## 3. Orden recomendado de ejecución

1. Reglas críticas de cobro y control (BR-01 a BR-05).
2. Explotación operativa y trazabilidad (BR-06 a BR-08).
3. Robustez de experiencia de uso front sobre API (BR-09 y BR-10).

## 4. Criterio de cierre por brecha

Una brecha pasa a `Cerrada` cuando se cumpla todo:

- Implementación técnica desplegada.
- Prueba funcional validada.
- Evidencia documental en sprint.
- Aceptación de coordinación funcional.

<!--
BE-05 · Pulido de Base de Datos Inicial (Sprint 01)

Resumen de criterios y convenciones adoptadas:
- Se toma como fuente canónica la estructura definida por las migraciones Laravel `2026_03_24_*`.
- Los SQL legacy quedan como referencia histórica, no como base operativa.
- Nomenclatura y relaciones: usar `id_contexto` para nuevo código, mantener `id_contacto_empresa` nullable en usuarios.
- Política de sesiones: `sessions` (técnica), `sesiones_login` (negocio), `sesiones` (legacy, fuera de uso).
- La matriz de incoherencias y decisiones (ver docs/05-SPRINTS/Sprint_01/BACK/01_BE-05_Pulido_BD_Inicial.md) es la referencia común para el equipo.
- Cambios de Auth/RBAC y homologación legacy quedan fuera de este alcance.

Para detalles y matriz completa, consultar el archivo BE-05 correspondiente.
-->
