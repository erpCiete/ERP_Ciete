# Revisión tarifa única Repsol — correo César 2026-05-20

## 1. Fuente

- Correo de César García del `2026-05-20 17:03`.
- Literal clave: `La tarifa de Repsol es única.`
- Fuentes contrastadas:
    - `docs/02_CLIENTE/tareasComparar.md`
    - `docs/02_CLIENTE/ANALISIS_ESTADO_Y_PLAN_FINAL_CIETE_2026-06-01.md`
    - `docs/02_CLIENTE/CIERRE_FINAL_NUEVA_VERSION_CIETE_2026-06-01.md`
    - `docs/02_CLIENTE/BLOQUE_2_TARIFARIO_CONTRATO_SOCIEDAD_2026-06-01.md`
    - `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
    - `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
    - `app/Models/Tarifario.php`
    - `app/Models/Contrato.php`
    - `app/Http/Controllers/TarifarioController.php`
    - `app/Http/Controllers/Api/TrabajoController.php`
    - `app/Http/Controllers/Api/PedidoController.php`
    - `resources/js/Pages/Tarifarios/Form.jsx`
    - `resources/js/Pages/Tarifarios/Index.jsx`
    - `resources/js/Components/ui/TrabajosExcelView.jsx`
    - `resources/js/Pages/Pedidos/Form.jsx`
    - `tests/Feature/TrabajoTest.php`
    - `tests/Feature/PedidoTest.php`
    - `database/abaco_ciete_prod_dump.sql`
    - `database/seeders/private/CieteRealContratosTarifariosSeeder.php`
    - `app/Services/Importacion/RepsolExcelImporter.php`

## 2. Regla indicada por César

- Regla actual a tomar como vigente:
    - `REPSOL tiene tarifa única.`
- Esta regla es posterior a la reunión del `2026-05-19`, por lo que debe prevalecer sobre hipótesis anteriores de “tarifa por sociedad” o “varios tarifarios Repsol”.

## 3. Qué implica funcionalmente

- Repsol no debe operar como un selector normal entre varios tarifarios activos alternativos.
- El modelo general `Contrato -> Tarifario -> Trabajo -> Pedido` puede mantenerse.
- Para Repsol, la lectura correcta hoy es:
    - un único tarifario activo;
    - muchas líneas/conceptos dentro de ese tarifario;
    - los trabajos y pedidos heredan ese mismo tarifario.
- La variación funcional debe resolverse por líneas/conceptos, tipo de trabajo o documento, no por varios tarifarios Repsol activos simultáneos.

## 4. Revisión documental

- La documentación viva general ya apuntaba en varios sitios a “Repsol tarifa única”.
- La contradicción relevante no estaba en `tareasComparar.md`, sino en fuentes históricas o de análisis:
    - `docs/02_CLIENTE/BLOQUE_2_TARIFARIO_CONTRATO_SOCIEDAD_2026-06-01.md`
    - `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
    - `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- Esas fuentes sí recogen la hipótesis previa de “Repsol por sociedad” o “varios Excel/tarifarios”.
- No se borra ese histórico, pero se aclara que el correo posterior de César lo deja superado como regla vigente.
- El Excel `Tarifa Ingenieria Ciete (2).xlsx` no está localizado en el repo con ese nombre.

## 5. Revisión código/datos

- El código permite varios tarifarios en general:
    - `Tarifario` pertenece a `Contrato`.
    - `Contrato` puede tener muchos `tarifarios`.
    - `TarifarioController` solo garantiza un `es_predeterminado` por contrato, no una unicidad específica para Repsol.
- `TrabajoController` y `TrabajosExcelView.jsx` ya hacen una selección compatible con tarifa única:
    - si hay predeterminado, lo priorizan;
    - si solo hay un tarifario compatible, lo usan.
- No existe hoy una regla de código que impida crear varios tarifarios activos Repsol ni un aviso específico si eso ocurre.
- Los datos revisados actuales son compatibles con tarifa única:
    - `database/abaco_ciete_prod_dump.sql` muestra un único contrato/tarifario Repsol activo en la muestra revisada.
    - `database/seeders/private/CieteRealContratosTarifariosSeeder.php` también refleja un único tarifario Repsol.
    - `app/Services/Importacion/RepsolExcelImporter.php` importa Repsol sobre un único contrato `REPSOL-2023-2027` y un único tarifario `TARIFA 23-27 REPSOL`.
- No se detectan seeds o SQL de demo que estén creando varios tarifarios activos Repsol en la evidencia revisada.

## 6. Estado actual del ERP

- Estado actual: compatible con la regla de César, pero no blindado.
- Compatibilidad:
    - los datos e importadores actuales ya funcionan como tarifa única Repsol;
    - `Trabajos` puede autousar un único tarifario o uno predeterminado;
    - `Pedido` hereda el mismo `id_tarifario` del `Trabajo`.
- Riesgo residual:
    - si un usuario crea más tarifarios activos Repsol en maestros, el modelo no lo impedirá por sí solo.

## 7. Riesgos

- Riesgo documental:
    - seguir leyendo la reunión del `2026-05-19` como regla vigente cuando el correo posterior ya la corrige.
- Riesgo de datos:
    - que en maestros aparezcan varios tarifarios activos Repsol por decisión operativa no alineada con César.
- Riesgo de código:
    - falta aviso específico en UI para detectar la anomalía “Repsol con varios tarifarios activos”.

## 8. Ajustes aplicados, si hubo

- No se ha aplicado hotfix de código.
- Sí se ha ajustado documentación:
    - aclaración en `docs/02_CLIENTE/BLOQUE_2_TARIFARIO_CONTRATO_SOCIEDAD_2026-06-01.md`;
    - registro en `docs/02_CLIENTE/tareasComparar.md`;
    - creación de este documento de revisión específica.

## 9. Decisiones pendientes

- Copiar al repo el Excel real `Tarifa Ingenieria Ciete (2).xlsx` si se quiere contraste documental 1:1.
- Decidir si conviene un hotfix futuro y mínimo en `Trabajos`:
    - aviso suave cuando Repsol tenga más de un tarifario activo.
- Confirmar con negocio solo si aparece una excepción futura explícita:
    - si la regla “tarifa única” era por todo Repsol o por una subramificación concreta no reflejada hoy en el ERP.

## 10. Conclusión

La nueva versión puede mantener el modelo general `Contrato -> Tarifario -> Trabajo -> Pedido`, pero en Repsol debe entenderse como un único tarifario activo con muchas líneas/conceptos, no como múltiples tarifarios alternativos.

Conclusión operativa a fecha `2026-06-01`:

- no hay contradicción clara entre el ERP actual y el correo de César;
- sí hay una aclaración documental obligatoria:
    - Repsol debe leerse como tarifa única vigente;
    - la lógica genérica de múltiples tarifarios queda como capacidad del modelo, no como regla funcional actual de Repsol.
