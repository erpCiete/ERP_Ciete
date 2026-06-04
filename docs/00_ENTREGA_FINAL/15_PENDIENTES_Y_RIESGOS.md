# Pendientes y riesgos

## P1 — Importantes, no bloquean demo

| # | Pendiente | Descripción | Impacto |
|---|-----------|-------------|---------|
| 1 | **Valores ARIBA son placeholders** | Los valores configurados en el contrato 772 MOEVE (OPEX-MOEVE-2026, AC-MOEVE-2026, etc.) son de demo. César debe actualizarlos con los valores reales de Moeve antes de producción. | Si se usan en demo con Ciete, se ven los placeholders. Hay que avisarles. |
| 2 | **CSV no soportado en importación** | El `ExcelParserService` usa PhpSpreadsheet para Excel. CSV falla en el parser aunque la UI lo anuncia. | No bloquea: el flujo manual de trabajos/pedidos funciona completamente. Solo afecta importación masiva CSV. |
| 3 | **Adjuntos automáticos en correo Moeve** | El modal "Preparar correo Moeve" genera texto y botones de descarga, pero no envía el correo automáticamente con adjuntos. | Requiere integración SMTP con soporte de adjuntos (Mailer de Laravel con adjuntos, Mailgun, SendGrid, etc.). El flujo manual con el modal es operativo. |
| 4 | **Sin pantalla para importes no asignables** | Si llega un cobro de Moeve que no corresponde a ningún trabajo/pedido, no hay una pantalla de "pendientes de asignar". | Caso raro. Contabilidad lo gestiona fuera del ERP por ahora. |
| 5 | **Tarifario Repsol auto-selección de alternativo** | Al crear un nuevo trabajo Repsol, el sistema puede auto-seleccionar el tarifario alternativo en lugar del principal. A investigar en operativa real. | No bloquea la demo si se verifica manualmente al crear. |
| 6 | **`/importaciones/subir` da 500 si se accede por URL directa** | La URL directa falla porque el formulario espera estar dentro del contexto de la app. Solo funciona desde `/importaciones`. | Acceso indirecto. No bloquea el flujo normal. |
| 7 | **Exportación REPSOL no implementada (diseño)** | REPSOL no tiene exportación porque no existe formato ARIBA para REPSOL según negocio. Los botones MOEVE no aparecen en pedidos REPSOL y el backend devuelve 422 si se intenta. Documentado. | No bloquea. Si CIETE lo solicita, se añade exportación REPSOL con plantilla propia. |
| 8 | **Tarifa Repsol — valores ARIBA del demo son placeholders** | El contrato 772 MOEVE tiene valores demo (OPEX-MOEVE-2026, etc.). Repsol no usa ARIBA. Solo MOEVE necesita actualizar los valores reales. | César debe actualizar el contrato 772 MOEVE en Maestros antes de la demo. |

## P2 — Deuda técnica

| Deuda | Descripción |
|-------|-------------|
| `TrabajoController::patchField` largo | Método de ~300 líneas con múltiples responsabilidades. Candidato a refactor cuando la cobertura de tests sea más amplia. |
| Rutas de pedidos/facturas en closures | `routes/web/operativa.php` tiene rutas de pedidos y facturas como closures. Sería mejor tener controladores web dedicados como `TrabajoController`. |
| ExcelParserService solo Excel | El parser no soporta CSV. Fix: configurar el CSV Reader de PhpSpreadsheet con el delimitador correcto. |
| Tarifarios Repsol posiblemente incompletos | En la reunión del 19/05 se mencionó que podrían faltar Excel de tarifarios Repsol (edificación, obras). Confirmar con César/Amaya. |

## Riesgos para la demo

| Riesgo | Mitigación |
|--------|-----------|
| ARIBA muestra placeholders | Actualizar los valores reales en Maestros → Contratos antes de la demo |
| César pregunta por campos ARIBA reales | Avisar proactivamente que son placeholders de demo |
| Correo Moeve sin adjuntos automáticos | El modal manual es la alternativa. Explicar el flujo: descargar CSV, guardar PDF, adjuntar manualmente. |
| Datos demo visibles (prefijo DEMO-) | Los trabajos demo usan prefijo DEMO-610XXX y DEMO-620XXX. Son datos reales de Ciete con ese prefijo. Funcionan para la demo. |
| Tests de BD de testing pueden fallar | Si la BD `abaco_ciete_testing` no está sincronizada, algunos tests fallan por FK. Ejecutar `php artisan migrate --database=testing` si pasa. |

## Lo que funciona bien para la demo

- Flujo completo Trabajo → Pedido → Factura → Cierre: ✅
- Exportación ARIBA de DEMO-MOE-LA-SENYERA: ✅ (si se actualizan los valores)
- Panel de cierre con DEMO-MOE-CIERRE: ✅ (ya fue finalizado en pruebas, se puede crear uno nuevo)
- Roles y permisos: ✅ (soporte 403, estado 403, cierre solo dirección)
- Auditoría: ✅
- Password reset: ✅
