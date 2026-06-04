# Plan corto de ejecución — Reunión César/Amaya CIETE 2026-05-19

## 1. Objetivo

Este plan reemplaza el enfoque genérico de 8 días como guía diaria de ejecución. El objetivo es trabajar en bloques cortos, concretos y verificables, sin mezclar pedido, líneas, exportación, estados, permisos y documentación en una sola tanda.

El plan de 8 días queda como respaldo técnico amplio. Este documento es la guía operativa inmediata para prompts separados.

## 2. Fuente principal

La fuente principal es:

`docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`

Fuentes de contraste:

- `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
- `docs/02_CLIENTE/plan_8_dias_nueva_version_erp_ciete.md`
- `docs/02_CLIENTE/tareasComparar.md`
- código real actual

## 3. Decisiones definitivas de reunión

- Trabajos como centro.
- Trabajo puede existir sin pedido.
- Pedido se crea/asigna desde trabajo.
- Estándar 1 trabajo → 1 pedido, pero varios pedidos como excepción.
- Líneas desde tarifario.
- Selector por descripción/código.
- Decimales en cantidad/precio.
- Estados automáticos.
- Cancelados visibles al final.
- Fecha de terminación manual.
- Auditoría y concurrencia por campo.
- Estaciones por código/nombre.
- Maestros sin borrado real.
- Contratos + tarifarios + sociedades facturadoras como flujo conjunto.
- Contabilidad controla facturas.
- Técnico no administrador de negocio.
- Panel de cierre como diagnóstico secundario.
- Exportación desde pedido: PDF + CSV + cuadro ARIBA.
- Interfaz compacta tipo Excel.

## 4. Bloques de ejecución cortos

### Bloque 0 — Cierre documental y validación Bloque A

Objetivo:

- Confirmar que Trabajos queda como centro operativo antes de abrir Pedido desde trabajo.

Archivos:

- `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Pages/Trabajos/Index.jsx`

Tareas:

- Revisar manualmente la pantalla de Trabajos por rol/contexto.
- Confirmar lectura de pedido principal y trabajos sin pedido.
- Confirmar que el placeholder de "Crear pedido" no ejecuta lógica todavía.
- Registrar cualquier KO funcional sin implementar nada si solo es validación.

Validaciones:

- Trabajos carga correctamente.
- Trabajos sin pedido se entienden como "Sin pedido".
- Pedido principal se ve cuando existe.
- La vista sigue siendo compacta tipo Excel.

Criterios de aceptación:

- Pablo puede confirmar que Trabajos es la entrada diaria.
- No se mezcló lógica real de pedidos.
- Los KOs quedan documentados con evidencia.

Qué no tocar:

- Rutas/controladores de Pedidos.
- Líneas de pedido.
- Exportaciones.
- Estados automáticos.
- Facturas.

### Bloque 1 — Pedido desde trabajo mínimo viable

Objetivo:

- Permitir crear/asignar pedido desde trabajo sin romper que un trabajo exista sin pedido.

Tareas:

- Crear/asignar pedido desde la fila o ficha de trabajo.
- Mantener pedido principal como caso estándar.
- Permitir varios pedidos como excepción controlada.
- Mantener la navegación a Pedidos solo para detalle o casos secundarios.
- No tocar líneas salvo lo mínimo imprescindible para crear el pedido cabecera.

Validaciones:

- Trabajo sin pedido sigue siendo válido.
- Desde un trabajo se puede crear pedido principal.
- Desde un trabajo con pedido se puede ver/asignar sin duplicar por error.
- Caso de varios pedidos queda visible como excepción.

Criterios de aceptación:

- El usuario no necesita entrar primero al CRUD de Pedidos para arrancar el pedido normal.
- No cambia el modelo de líneas ni facturación.

Qué no tocar:

- Selector avanzado de tarifario.
- Decimales.
- Exportación Moeve.
- Estados derivados.
- Permisos profundos.

### Bloque 2 — Contrato/Tarifario/Sociedad como decisión previa

Objetivo:

- Revisar el modelo actual antes de tocar líneas para no inventar una UX incompatible con contratos, tarifarios y sociedades facturadoras.

Tareas:

- Revisar relaciones actuales entre contrato, tarifario, línea tarifaria, cliente/contexto y sociedad facturadora.
- Confirmar cómo se selecciona o deriva el tarifario en un pedido.
- Preparar diagnóstico o UX mínima si hay dudas.
- Documentar si el flujo debe pedir contrato/tarifario/sociedad antes de añadir líneas.
- No fusionar tablas salvo decisión explícita.

Validaciones:

- Repsol tarifa única sigue coherente.
- Moeve permite exportación futura desde pedido.
- Sociedad facturadora no queda ambigua.

Criterios de aceptación:

- Está claro qué dato condiciona las líneas.
- Está claro qué necesita el pedido antes de añadir ítems.

Qué no tocar:

- Migraciones.
- Importaciones.
- Reestructuración de tablas.
- Facturación.

### Bloque 3 — Líneas de pedido desde tarifario

Objetivo:

- Crear líneas de pedido operativas desde tarifario con búsqueda útil y cálculo decimal.

Tareas:

- Buscador por descripción/código.
- Autocompletar código, precio y descripción.
- Cantidad decimal.
- Precio decimal.
- Total de línea.
- Total de pedido.
- Validar que no se rompe la entrada manual permitida si sigue siendo necesaria.

Validaciones:

- Buscar por código localiza la línea correcta.
- Buscar por texto localiza la línea correcta.
- Cantidad decimal calcula bien.
- Precio decimal calcula bien.
- Total pedido coincide con suma de líneas.

Criterios de aceptación:

- Una persona puede crear un pedido real sin memorizar códigos.
- El cálculo económico es consistente.

Qué no tocar:

- PDF/CSV/ARIBA.
- Estados automáticos avanzados.
- Facturación parcial.
- Permisos.

### Bloque 4 — Exportación Moeve

Objetivo:

- Exportar desde pedido los documentos operativos necesarios para Moeve.

Tareas:

- Botón en pedido.
- PDF.
- CSV.
- Cuadro ARIBA o bloque copiable equivalente.
- Usar caso La Senyera como validación.

Validaciones:

- Pedido Moeve genera PDF correcto.
- Pedido Moeve genera CSV correcto.
- Cuadro ARIBA contiene datos necesarios.
- La Senyera se puede usar como caso funcional.

Criterios de aceptación:

- La exportación se hace desde pedido, no desde factura ni cierre.
- El usuario obtiene los tres formatos esperados.

Qué no tocar:

- Facturación contable.
- Importaciones.
- Roles generales.
- Cierre.

### Bloque 5 — Estados automáticos y cierre secundario

Objetivo:

- Derivar estados mínimos sin convertir el panel de cierre en centro operativo.

Tareas:

- Estados derivados mínimos.
- Cancelados al final.
- Diferenciar terminado/finalizado si aplica.
- Mantener fecha de terminación manual.
- Reposicionar cierre como diagnóstico.

Validaciones:

- Trabajo sin pedido.
- Pedido en preparación.
- Pedido recibido.
- Facturación parcial.
- Facturación completa.
- Trabajo terminado.
- Cancelado visible al final.

Criterios de aceptación:

- El estado ayuda a filtrar y diagnosticar.
- Trabajos sigue siendo el centro de operación.

Qué no tocar:

- Rediseño completo de cierre.
- Cambios masivos de permisos.
- Cambios de datos/importaciones.

### Bloque 6 — Roles, auditoría y validación final

Objetivo:

- Cerrar fronteras de rol y extender trazabilidad donde sea crítico.

Tareas:

- Revisar técnico vs dirección.
- Confirmar que contabilidad controla facturas.
- Extender auditoría por campo a puntos críticos.
- Revisar concurrencia donde haya edición sensible.
- Probar por rol.

Validaciones:

- Técnico no administra negocio por defecto.
- Dirección ve revisión/cierre sin invadir operación diaria.
- Contabilidad opera facturas.
- Ejecución no accede a lo que no corresponde.
- Auditoría registra cambios críticos.

Criterios de aceptación:

- No hay fugas claras de permisos.
- Los cambios críticos quedan trazables.

Qué no tocar:

- Refactor de seguridad completo si no es necesario.
- Nuevos roles sin decisión.
- Cambios de modelo de datos.

## 5. Prompts preparados por bloque

### Prompt Bloque 0

```text
Actúa como revisor funcional del ERP CIETE. Objetivo: cerrar validación manual del Bloque A, Trabajos como centro operativo, sin implementar nada salvo documentación si lo pido.

Archivos probables: docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md, docs/02_CLIENTE/tareasComparar.md, resources/js/Components/ui/TrabajosExcelView.jsx, resources/js/Pages/Trabajos/Index.jsx.

Qué tocar: solo documentación si hay KOs de validación.
Qué NO tocar: rutas, controladores, modelos, pedidos, líneas, exportaciones, estados, permisos, base de datos.
Validaciones: trabajo con pedido, trabajo sin pedido, lectura compacta, cancelados al final, rol/contexto.
Respuesta final: estado Bloque A, KOs, riesgos y si puede empezar Bloque 1.
```

### Prompt Bloque 1

```text
Actúa como desarrollador ERP CIETE. Objetivo: implementar Pedido desde Trabajo mínimo viable.

Archivos probables: routes/web/operativa.php, TrabajoController, PedidoController, TrabajoResource, TrabajosExcelView.jsx, Trabajos/Index.jsx, Pedidos/Form.jsx.

Qué tocar: flujo crear/asignar pedido desde trabajo, pedido principal, visibilidad de varios pedidos como excepción.
Qué NO tocar: líneas avanzadas, selector tarifario, decimales, PDF/CSV/ARIBA, facturas, cierre, importaciones.
Validaciones: trabajo sin pedido sigue válido; crear pedido desde trabajo; ver pedido principal; caso varios pedidos no rompe.
Respuesta final: archivos tocados, pruebas, riesgos y pendiente exacto.
```

### Prompt Bloque 2

```text
Actúa como analista técnico ERP CIETE. Objetivo: revisar Contrato/Tarifario/Sociedad antes de implementar líneas.

Archivos probables: modelos Contrato, Tarifario, LineaTarifario, SociedadFacturadora, Pedido, requests y formularios de Pedidos.

Qué tocar: preferentemente documentación o diagnóstico; código solo si se pide después.
Qué NO tocar: migraciones, importaciones, fusión de tablas, facturación, exportación.
Validaciones: Repsol tarifa única, Moeve con sociedad facturadora, impacto sobre pedido y líneas.
Respuesta final: modelo actual, decisión recomendada, riesgos y archivos que tocaría en Bloque 3.
```

### Prompt Bloque 3

```text
Actúa como desarrollador ERP CIETE. Objetivo: líneas de pedido desde tarifario con buscador descripción/código y decimales.

Archivos probables: ItemsTable.jsx, Pedidos/Form.jsx, StorePedidoRequest, UpdatePedidoRequest, PedidoItem, LineaTarifario.

Qué tocar: selector buscable, autocompletado, cantidad decimal, precio decimal, total línea y total pedido.
Qué NO tocar: exportación Moeve, estados automáticos, facturas, roles, cierre, importaciones.
Validaciones: búsqueda por código, búsqueda por descripción, cálculo decimal, total pedido.
Respuesta final: cambios, pruebas ejecutadas, casos validados y riesgos.
```

### Prompt Bloque 4

```text
Actúa como desarrollador ERP CIETE. Objetivo: exportación Moeve desde pedido: PDF, CSV y cuadro ARIBA.

Archivos probables: PedidoController, rutas operativa, vistas Pedidos, servicios/exportadores, configuración pdfkit si existe.

Qué tocar: botón en pedido, generación PDF, CSV y cuadro ARIBA.
Qué NO tocar: facturación contable, cierre, roles generales, importaciones, líneas salvo lectura.
Validaciones: caso La Senyera, pedido Moeve con líneas, descarga PDF, descarga CSV, cuadro copiable.
Respuesta final: formatos generados, archivos tocados, pruebas y limitaciones.
```

### Prompt Bloque 5

```text
Actúa como desarrollador ERP CIETE. Objetivo: estados automáticos mínimos y cierre como diagnóstico secundario.

Archivos probables: Trabajo, Pedido, ClosureDashboardService, controladores de trabajo/pedido/cierre, vistas de navegación.

Qué tocar: derivación mínima de estados, cancelados al final, terminado/finalizado, etiquetado de cierre como diagnóstico.
Qué NO tocar: exportación, importaciones, roles profundos, reestructuración completa del cierre.
Validaciones: sin pedido, pedido en preparación, recibido, parcial, completo, terminado, cancelado.
Respuesta final: reglas aplicadas, escenarios validados, pruebas y riesgos.
```

### Prompt Bloque 6

```text
Actúa como revisor/desarrollador de seguridad funcional ERP CIETE. Objetivo: roles, auditoría y validación final.

Archivos probables: User.php, policies/middleware si existen, matriz de permisos, AuditLogger, AuditLog, hooks de concurrencia, tests de roles.

Qué tocar: frontera técnico/dirección/contabilidad/ejecución, auditoría por campo en puntos críticos, pruebas por rol.
Qué NO tocar: nuevos roles no aprobados, migraciones, importaciones, rediseño general de seguridad.
Validaciones: técnico no administra negocio, contabilidad controla facturas, dirección revisa/cierra, ejecución solo opera lo suyo, auditoría registra cambios.
Respuesta final: hallazgos, cambios, pruebas por rol y riesgos residuales.
```

## 6. Orden recomendado para hoy

Hoy:

- Bloque 0: cerrar validación manual de Trabajos como centro operativo.
- Bloque 1: empezar solo si Bloque 0 no descubre KO bloqueante.

Mañana:

- Bloque 2: revisar contrato/tarifario/sociedad.
- Bloque 3: implementar líneas desde tarifario y decimales si Bloque 2 queda claro.

Día siguiente:

- Bloque 4: exportación Moeve.
- Bloque 5: estados automáticos y cierre secundario.
- Bloque 6: roles, auditoría y validación final.

## 7. Riesgos

- Documentación: documentos históricos pueden contradecir la nueva ola si se usan fuera del mapa de autoridad.
- Código: mezclar Bloque 1 con líneas, estados o exportación puede generar regresiones.
- Datos: importaciones y datos reales no deben tocarse sin validación específica.
- Permisos: técnico, dirección y contabilidad requieren revisión fina para no abrir negocio al rol equivocado.
- Alcance: intentar cerrar todo en un prompt grande aumenta el riesgo de cambios incompletos.

## 8. Checklist final

- Trabajos es la pantalla diaria principal.
- Trabajo sin pedido funciona.
- Pedido se crea/asigna desde trabajo.
- Pedido principal se distingue de pedidos extra.
- Contrato/tarifario/sociedad queda claro antes de líneas.
- Líneas salen de tarifario.
- Buscador por descripción/código funciona.
- Cantidad y precio admiten decimales.
- Total línea y total pedido calculan bien.
- Moeve exporta PDF.
- Moeve exporta CSV.
- Moeve tiene cuadro ARIBA.
- Repsol mantiene tarifa única o queda documentada la excepción.
- Estados automáticos mínimos funcionan.
- Cancelados quedan al final.
- Fecha de terminación manual se mantiene.
- Estaciones se localizan por código/nombre.
- Maestros no se borran físicamente.
- Contexto y rol siguen separados.
- Técnico no administra negocio por defecto.
- Contabilidad controla facturas.
- Dirección usa cierre/revisión como diagnóstico.
- Auditoría/concurrencia cubren campos críticos.
- Interfaz compacta tipo Excel sigue siendo usable.
