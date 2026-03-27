# Lo que no hace la API (fase inicial) · ERP Ciete

## 1. Alcance del documento
Registrar funcionalidades fuera de fase o no confirmadas para evitar expectativas incorrectas.

## 2. Fuera de alcance por fase

| ID | Funcionalidad no incluida en fase inicial | Motivo | Impacto | Prioridad futura |
|---|---|---|---|---|
| NAPI-01 | Integraciones con sistemas externos no confirmados | No definido por cliente | Medio | Media |
| NAPI-02 | Automatizaciones avanzadas fuera del flujo núcleo | Priorización de núcleo operativo | Medio | Media |
| NAPI-03 | Analítica avanzada no operativa (BI extendido) | Requiere cierre previo de modelo base | Bajo | Baja |
| NAPI-04 | Cobertura de procesos secundarios no críticos | Foco en operación principal | Medio | Media |
| NAPI-05 | Reglas especiales no documentadas por cliente | Falta especificación | Alto | Alta |

## 3. No confirmado técnicamente aún
- Catálogo final de endpoints públicos.
- Estrategia definitiva de versionado (`/v1`, `/v2`).
- Política cerrada de exportación periódica a Excel.
- Integración de alertas en tiempo real (correo, notificación interna).

## 4. Riesgos si se asume como incluido
- Desviación de alcance y retraso del núcleo funcional.
- Incremento de deuda técnica por cambios sin especificación.
- Confusión entre "definido" y "entregado".

## 5. Regla de control
Todo punto de esta lista solo pasa a "incluido" cuando:
- Exista requisito formal aprobado.
- Tenga impacto/prioridad evaluados.
- Se asigne a sprint y responsable.
