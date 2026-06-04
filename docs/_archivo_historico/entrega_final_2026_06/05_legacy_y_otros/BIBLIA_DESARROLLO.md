# Biblia de desarrollo ERP CIETE

## 1. Estado actual

ERP CIETE v2.1.0 validado técnicamente. Nueva ola basada en reunión César/Amaya 2026-05-19.

## 2. Fuentes de autoridad

1. Código real actual.
2. `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
3. `docs/02_CLIENTE/estado_funcional_nueva_version_erp_ciete_2026-05-28.md`
4. `docs/02_CLIENTE/PLAN_CORTO_EJECUCION_REUNION_CESAR_AMAYA_2026-05-28.md`
5. `docs/02_CLIENTE/tareasComparar.md`

## 3. Arquitectura viva

Laravel + React/Inertia + Vite + MySQL/MariaDB.

## 4. Flujo funcional vigente

Trabajo → Pedido → Líneas de pedido → Facturas → Cierre/diagnóstico.

## 5. Decisiones vigentes

* Trabajos es el centro operativo.
* Un trabajo puede existir sin pedido.
* El pedido debe crearse/asignarse desde trabajo.
* Estándar: un trabajo, un pedido.
* Excepción: varios pedidos por trabajo.
* Líneas desde tarifario.
* Contrato/Tarifario/Sociedad facturadora como flujo conjunto.
* Decimales en cantidad/precio cuando proceda.
* Exportación Moeve desde pedido: PDF + CSV + cuadro ARIBA.
* Contabilidad controla facturas.
* Técnico no es administrador de negocio.
* Cierre queda como diagnóstico secundario.
* Auditoría y concurrencia por campo.

## 6. Qué NO tocar sin orden explícita

* Base de datos destructiva.
* Importaciones reales.
* Materiales cliente.
* Bitácoras.
* Producción.
* Permisos profundos.
* Exportaciones complejas fuera de bloque.

## 7. Forma de trabajo

* Un bloque por prompt.
* Cambios pequeños y verificables.
* No mezclar bloques.
* Ejecutar build/tests según alcance.
* Actualizar backlog vivo al cerrar bloque.
