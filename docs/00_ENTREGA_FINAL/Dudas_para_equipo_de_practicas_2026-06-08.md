# Dudas para el equipo de prácticas — ERP Ciete

**Fecha:** 2026-06-08
**Contexto:** Ábaco retoma el desarrollo del ERP de Ciete (rama `Desplegadav2`, entrega **v2.2.0** del 2026-06-04). Estas preguntas surgen tras leer `docs/00_ENTREGA_FINAL/`, mapear el código y montar el entorno en local. Están ordenadas por bloques y marcadas con prioridad:

- 🔴 **Bloqueante / alto impacto** para retomar el desarrollo con garantías.
- 🟡 **Importante**, conviene aclararlo pronto.
- ⚪ **Aclaración / contexto**, sin prisa.

> Cada pregunta incluye lo que hemos observado, para que sea fácil reconocer el caso.

---

## A. Entorno, base de datos y reproducibilidad

**A1. 🔴 Motor de base de datos: ¿MariaDB o MySQL?**
El README dice "MySQL 8+ o MariaDB compatible", pero **el proyecto no arranca en MySQL 8 puro**: el dump `database/schema/abaco_ciete_v220_2026-06-04.sql` es un volcado de **MariaDB 10.4.32**, y las migraciones usan claves foráneas compuestas que MySQL 8 rechaza con `error 6125 Missing unique key` (ejemplo: `fk_tipos_trabajo_tipo_doc_contexto` sobre `tipos_documento`). Hemos tenido que montar **MariaDB** para que funcione.

- ¿Confirmáis que el motor objetivo es **MariaDB** (¿vía XAMPP?)? ¿Qué **versión exacta** usáis?
- ¿Hay intención de soportar MySQL 8 (cliente final), o nos estandarizamos todos en MariaDB?

**A2. 🟡 ¿Existe un dump compatible o seed con datos demo "ricos"?**
El dump no importa en MySQL 8; en MariaDB sí. Como alternativa, `php artisan migrate --seed` crea esquema + catálogos + usuarios, pero **sin** los trabajos de ejemplo (`DEMO-610001`, etc.).

- ¿El flujo recomendado para un entorno nuevo es **restaurar el dump** (MariaDB) o **migrate + seeders**? ¿Hay seeders que carguen datos demo equivalentes (vimos `database/seeders/private/` y `PrivateSeederGeneratorService`)?

**A3. ⚪ Variables de entorno y servicios externos.**
El `.env.example` trae SMTP de Gmail de ejemplo y `APP_ALLOW_DEMO_USERS=false`.

- ¿Qué valores reales de SMTP/correo usáis en local y en el servidor del cliente?
- ¿Para qué sirve exactamente `APP_ALLOW_DEMO_USERS` y cómo debe quedar en producción?

---

## B. Flujo de trabajo del repositorio

**B1. 🔴 Estrategia de ramas.**
La entrega vive en `Desplegadav2`, pero la rama por defecto del remoto es `develop` (existen además `main` y `UltimaVersion`).

- ¿Sobre qué rama desarrollamos lo nuevo y cómo se promociona a la versión desplegada? ¿Cuál es la rama "fuente de verdad" hoy?
- ¿Convenciones de commits / PR / revisión que sigáis?

**B2. ⚪ Entorno de despliegue del cliente.**

- ¿Dónde corre actualmente el ERP (servidor, hosting, on-premise en Ciete)? ¿Hay pipeline de despliegue o es manual (`docs/00_ENTREGA_FINAL/14_DESPLIEGUE.md`)?

---

## C. Estado funcional, tests y deuda técnica

**C1. 🟡 Los 3 tests que fallan.**
La entrega documenta 270 OK / 3 fallidos: `AdminAccessTest` (admin a `/dashboard` devuelve 403 en vez de 200), `ContextCreationGuardTest` (creación en OTROS exige tarifario), `ImportacionesAccessTest` (falta `resources/js/Pages/Importaciones/Form.jsx` en el manifest de Vite).

- ¿Son fallos reales pendientes de corregir, o "esperados" por estado/entorno? ¿Los abordamos nosotros?

**C2. 🟡 Deuda técnica conocida — ¿prioridad?**
Identificadas: `TrabajoController::patchField` (~300 líneas, múltiples responsabilidades), rutas de **pedidos y facturas como closures** en `routes/web/operativa.php` (en vez de controladores dedicados), `ExcelParserService` sin soporte CSV.

- ¿Hay interés en refactorizar esto a corto plazo o se prioriza funcionalidad nueva?

**C3. ⚪ Importación CSV y correo MOEVE con adjuntos.**
La UI de importación anuncia CSV pero el parser solo soporta `.xlsx`. El modal "Preparar correo Moeve" genera textos y descargas, pero **no envía el correo con adjuntos** automáticamente.

- ¿Son requisitos pendientes priorizados por el cliente o aceptables como están de momento?

---

## D. Modelo de negocio y decisiones abiertas (reunión cliente 19/05)

> En la transcripción de la reunión del 19/05 (César y Amaya) quedaron varios puntos a medio cerrar. Queremos saber cómo terminaron implementándose.

**D1. 🟡 Tarifario ↔ sociedad facturadora.**
El cliente pidió que el tarifario/contrato no dependa solo del cliente, sino que pueda asociarse a **una o varias sociedades facturadoras**. En el código vemos `contratos` con `tarifarios` colgando del contrato y una tabla pivote `contrato_empresas_facturadoras`.

- ¿Cómo quedó finalmente el modelo? ¿El tarifario se elige por contrato, por sociedad, o ambos? ¿Hay un tarifario "por defecto" al crear el trabajo cuando aún no se conoce la sociedad?

**D2. 🟡 Trabajo con varios pedidos.**
Se acordó estándar **1 trabajo – 1 pedido**, con la **excepción** de varios pedidos por trabajo (p. ej. dirección facultativa + coordinación). El modelo `Trabajo hasMany Pedido` lo soporta.

- ¿Está la UI preparada para esa excepción (botón "añadir pedido extra") y validada con el cliente?

**D3. ⚪ Panel de cierre.**
En la reunión, el cliente **cuestionó la utilidad** del panel de cierre frente a trabajar con filtros en la pantalla de Trabajos.

- ¿Cuál fue la decisión final: se mantiene como está, se rebaja a herramienta de diagnóstico, o se retira?

**D4. 🟡 Cobros / importes no asignables.**
Amaya describió el caso real de cobros que no casan con ningún trabajo/pedido ("170 € que no puedo asignar"). La doc dice que hoy Contabilidad lo gestiona fuera del ERP.

- ¿Se espera una pantalla de "pendientes de asignar" en el ERP, o se deja fuera?

**D5. ⚪ Estados e importes.**
Convención observada: importe de pedido **0 = cancelado**, **vacío = pendiente de tarificar**; estados del trabajo derivados automáticamente; cancelados visibles pero al final.

- ¿Es correcta esta interpretación y está cerrada con el cliente?

---

## E. Datos maestros pendientes

**E1. 🟡 Tarifarios Repsol.**
En la reunión se apuntó que podrían **faltar Excel de tarifarios de Repsol** (diseño, edificación, obras) por enviar/cargar.

- ¿Se recibieron y cargaron todos? ¿Falta alguno?

**E2. 🔴 Valores ARIBA reales (contrato 772 MOEVE).**
Los valores ARIBA configurados en demo (OPEX-MOEVE-2026, AC-MOEVE-2026, etc.) son **placeholders**. La exportación MOEVE/ARIBA depende de ellos.

- ¿César ha entregado ya los valores reales? Si no, ¿quién y cuándo los proporciona?

---

## F. Producto y próximos pasos

**F1. 🔴 Feedback del cliente tras la v2.2.0.**

- ¿Qué impresiones/correcciones devolvió Ciete tras la entrega del 4 de junio? ¿Hay una lista priorizada de lo siguiente a desarrollar?

**F2. ⚪ Roadmap.**

- ¿Qué módulos o mejoras están previstos a continuación (presupuestos, legalizaciones, importación, exportación Repsol, etc.)?

---

### Resumen de las críticas (🔴) para no perderlas en la reunión

- **A1** Confirmar MariaDB como motor oficial (y versión).
- **B1** Rama base para desarrollar y flujo de promoción.
- **E2** Valores ARIBA reales del contrato 772.
- **F1** Feedback del cliente y siguiente prioridad.
