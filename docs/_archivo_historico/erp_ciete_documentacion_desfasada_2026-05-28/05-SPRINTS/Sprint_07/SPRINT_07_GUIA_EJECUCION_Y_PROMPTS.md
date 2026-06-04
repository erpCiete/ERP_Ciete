# Sprint 07 · Guia de ejecucion y prompts

Periodo: 2026-05-10 a 2026-05-17

Documento base relacionado:

- [SPRINT_07_PLAN.md](./SPRINT_07_PLAN.md)

## Objetivo de esta guia

Convertir el plan del sprint en una guia operativa que permita ejecutar las mejoras con orden, sin romper el comportamiento actual y con prompts listos para usar en analisis, implementacion, QA y code review.

## Como usar esta guia

1. Trabajar un bloque a la vez y cerrar su validacion antes de abrir el siguiente.
2. No mezclar cambios de datos reales, aislamiento de contexto y mejoras visuales en una sola tanda si no estan directamente conectados.
3. Antes de editar, localizar siempre el punto exacto donde se decide el comportamiento.
4. Despues de la primera edicion, validar de inmediato el slice tocado.
5. Documentar en sprint o bitacora cualquier decision funcional o bloqueo real.

## Superficies reales del repo que afectan este sprint

- `app/Http/Controllers/Api/TrabajoController.php`
- `routes/web.php`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Pages/Pedidos/Index.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`
- `resources/js/Components/Modal.jsx`
- `resources/js/Components/ui/ItemsTable.jsx`
- `app/Models/Scopes/ContextScope.php`

## Secuencia obligatoria de trabajo

1. Depurar importaciones reales con cliente y cerrar criterio de tratamiento.
2. Consolidar maestros reales para que las pruebas ya se hagan con datos definitivos.
3. Habilitar OTROS CLIENTES garantizando aislamiento por contexto.
4. Revisar, validar e integrar las mejoras ya hechas en `fix/version2`.
5. Implementar el selector FK de numero de pedido en la vista Excel de trabajos.
6. Dejar el warning de tipos de hora solo en fase de definicion hasta aclarar modelo y alcance.

## Fase 0 · Preparacion obligatoria

### Entrada minima

- Acceso al branch actual `Version2`.
- Referencia de integracion contra `develop`.
- Listado de mejoras ya implementadas en `fix/version2`.
- Excel reales o evidencia de incidencias para importaciones.
- Confirmacion funcional del comportamiento esperado por contexto.

### Paso a paso

1. Revisar el plan de sprint y separar lo comprometido, lo pendiente y lo bloqueado.
2. Confirmar que el trabajo de esta semana no mezcla alcance nuevo con arreglos no planificados.
3. Identificar archivos de entrada por cada bloque antes de tocar codigo.
4. Definir para cada cambio una validacion minima ejecutable o funcional.
5. Registrar riesgos de no regresion antes de empezar.

### Checklist de arranque

- [ ] El alcance actual esta separado entre P0, P1, P2 y bloqueado.
- [ ] El equipo sabe que `ContextScope` no se puede puentear.
- [ ] Se conocen las vistas reales implicadas en trabajos y pedidos.
- [ ] Hay criterio de aceptacion para cada mejora antes de editar.

## Bloque P0.1 · Depuracion de importaciones con cliente

### Objetivo

Reducir errores operativos y dejar clasificado el motivo de cada fila con aviso o ignorada en la importacion real.

### Entrada necesaria

- Excel reales usados por CIETE.
- Listado de filas con aviso, ignoradas o rechazadas.
- Criterio de negocio para saber si un dato es obligatorio, recuperable o descartable.

### Paso a paso

1. Localizar el flujo de importacion que marca avisos, ignorados y errores.
2. Extraer ejemplos reales por cada tipo de incidencia.
3. Clasificar cada caso en una matriz minima: error de origen, dato incompleto, regla demasiado estricta, dato recuperable, fila que debe ignorarse.
4. Revisar con cliente solo los casos frontera o ambiguos.
5. Ajustar reglas, mensajes o tolerancias solo donde exista evidencia funcional.
6. Reimportar una muestra pequena y verificar que bajan avisos falsos sin aumentar errores silenciosos.
7. Documentar criterio final para siguientes cargas.

### Entregables

- Catalogo de incidencias clasificado.
- Lista de reglas corregidas o confirmadas.
- Evidencia de una reimportacion de prueba aceptable.

### Checklist de cierre

- [ ] Cada aviso frecuente tiene explicacion funcional.
- [ ] No se han relajado validaciones criticas sin aprobacion.
- [ ] La reimportacion de muestra mejora el resultado respecto al estado anterior.

### Prompt de descubrimiento

```text
Actua como ingeniero senior en Laravel. En este repo necesito localizar el flujo real de importacion que decide cuando una fila queda con aviso, ignorada o rechazada. Quiero que identifiques el controlador, servicio, request, rule o clase responsable, y que me devuelvas un mapa minimo del flujo con los archivos exactos a revisar primero. No cambies codigo todavia. Prioriza el camino que realmente clasifica la fila y no los wrappers.
```

### Prompt de implementacion

```text
Necesito corregir el flujo de importacion de ERP_Ciete para reducir avisos falsos sin romper validaciones criticas. Trabaja solo sobre el slice que clasifica filas con aviso o ignoradas. Primero confirma la regla actual, luego aplica el cambio minimo necesario y despues valida con la prueba mas cercana disponible. No abras alcance a otras mejoras del sprint.
```

### Prompt de QA

```text
Revisa los cambios de importacion y dime si existe riesgo de haber convertido errores reales en avisos silenciosos o de haber permitido filas inconsistentes. Quiero findings concretos, ordenados por severidad, con foco en regresiones funcionales.
```

## Bloque P0.2 · Consolidacion de maestros reales

### Objetivo

Dejar cargados y consistentes contratos, sociedades facturadoras, tarifarios, lineas de tarifario, usuarios y roles para trabajar sobre base productiva.

### Paso a paso

1. Cerrar primero el criterio de importacion para evitar retrabajo.
2. Cargar contratos y sociedades facturadoras antes de tarifarios.
3. Cargar tarifarios y lineas solo cuando las relaciones maestras anteriores esten validadas.
4. Cargar usuarios y roles con verificacion de permisos y contextos.
5. Revisar relaciones criticas: contrato -> empresa facturadora, contrato -> tarifario, usuario -> contexto, usuario -> rol.
6. Probar desde UI que los maestros cargados aparecen en formularios y vistas operativas.

### Checklist de cierre

- [ ] No quedan referencias huérfanas entre maestros.
- [ ] Los usuarios reales tienen contexto y rol coherentes.
- [ ] Los tarifarios cargados aparecen donde deben aparecer.
- [ ] La carga no depende ya de datos demo.

### Prompt operativo

```text
Quiero revisar la consistencia funcional de los maestros reales en ERP_Ciete. Necesito que enumeres las relaciones criticas entre contratos, sociedades facturadoras, tarifarios, lineas, usuarios y roles, y que me indiques en que pantallas o consultas se consumen para poder validar la carga real sin revisar todo el sistema.
```

## Bloque P0.3 · Habilitacion de OTROS CLIENTES

### Objetivo

Permitir operativa real de OTROS CLIENTES sin fuga de datos frente a Repsol y Moeve.

### Restriccion no negociable

Toda consulta y toda escritura debe respetar `ContextScope` o una validacion equivalente por contexto. No se permite bypass de seguridad por comodidad en controladores, resources, closures o componentes.

### Superficies a revisar primero

- `app/Models/Scopes/ContextScope.php`
- Modelos que usan `HasContext`
- `app/Http/Controllers/Api/TrabajoController.php`
- `routes/web.php` en cierres que hidratan Inertia
- Resources y consultas que alimentan tablas Excel

### Paso a paso

1. Confirmar como entra hoy el contexto activo del usuario.
2. Localizar consultas que filtran por `id_contexto` de forma explicita y las que dependen de `ContextScope`.
3. Revisar si OTROS CLIENTES necesita catalogos propios o solo extension del filtro actual.
4. Verificar que vistas globales y pantallas Excel no mezclan datos entre contextos.
5. Verificar escritura: crear, editar, importar y emitir eventos solo dentro de contextos permitidos.
6. Probar como usuario Moeve, Repsol y OTROS CLIENTES que cada uno ve y muta solo su dominio.
7. Documentar cualquier query que necesite endurecimiento adicional.

### Checklist de cierre

- [ ] Ninguna consulta relevante devuelve datos de contexto ajeno.
- [ ] Ninguna mutacion permite grabar sobre otro contexto.
- [ ] Los catalogos Inertia respetan el contexto activo.
- [ ] La vista TODOS sigue siendo operativa sin perder aislamiento interno.

### Prompt de descubrimiento

```text
Analiza ERP_Ciete con foco exclusivo en aislamiento por contexto para habilitar OTROS CLIENTES. Quiero que localices el flujo real de scoping por contexto, empezando por app/Models/Scopes/ContextScope.php y siguiendo por controladores o closures que hidratan Inertia. Dime donde existe riesgo de bypass y que archivos deberia revisar primero. No implementes aun.
```

### Prompt de implementacion

```text
Necesito habilitar OTROS CLIENTES en ERP_Ciete sin permitir fugas de datos entre contextos. Trabaja solo sobre el slice necesario para que consultas, catalogos y escritura respeten el contexto activo. Usa el mecanismo existente de ContextScope o la proteccion equivalente del repo. No rompas la vista global ni la operativa actual de Moeve y Repsol. Despues valida con la comprobacion mas estrecha posible.
```

### Prompt de auditoria

```text
Haz una revision de seguridad funcional sobre el aislamiento por contexto en ERP_Ciete. Busca consultas sin filtro de contexto, closures en routes/web.php que puedan saltarse el scope, y props de Inertia que mezclen datos de varios contextos. Devuelveme findings concretos con severidad.
```

## Bloque P1 · Integracion segura de mejoras ya implementadas en fix/version2

### Objetivo

Pasar a `develop` solo mejoras ya terminadas y validadas, sin meter cambios laterales.

### Archivos de referencia ya localizados

- `resources/js/Components/Modal.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`
- `resources/js/Components/ui/ItemsTable.jsx`
- `resources/js/Components/ui/PedidosExcelView.jsx`
- `app/Http/Controllers/Api/TrabajoController.php`

### Paso a paso general

1. Comparar diff de `fix/version2` contra `develop` solo para las mejoras comprometidas.
2. Revisar cada mejora por separado, no como un paquete opaco.
3. Validar visual o funcionalmente cada mejora antes de integrarla.
4. Confirmar que no se ha arrastrado codigo experimental de otras tareas.
5. Integrar solo cuando el alcance este limpio y documentado.

### Subbloque 1 · Modal de observaciones

Paso a paso:

1. Confirmar que el cambio real esta en `resources/js/Components/Modal.jsx`.
2. Verificar `pointer-events-none` en backdrop y `relative` en panel.
3. Probar apertura, foco y click en botones del modal.
4. Confirmar que no se rompen otros modales que reutilicen el componente.

### Subbloque 2 · Gestion hibrida de estaciones

Paso a paso:

1. Revisar en `TrabajosExcelView.jsx` donde se resuelve la estacion.
2. Verificar que el catalogo de estaciones llega desde backend.
3. Confirmar que la seleccion actualiza `id_estacion_servicio` y no solo texto visible.
4. Confirmar que nombre, municipio y provincia quedan como solo lectura.
5. Probar con estaciones de distintos contextos si aplica.

### Subbloque 3 · Categorizacion dinamica por contexto

Paso a paso:

1. Confirmar en `TrabajosExcelView.jsx` la rama por contexto.
2. Validar que Repsol usa selector estricto por tipo de documento.
3. Validar que Moeve y OTROS CLIENTES usan texto libre en categoria.
4. Probar en vista global para evitar condicion mal resuelta.

### Subbloque 4 · Casteo numerico

Paso a paso:

1. Revisar `ItemsTable.jsx` y `PedidosExcelView.jsx`.
2. Confirmar que `step="1"` se aplica donde corresponde.
3. Verificar que cantidades e importes no muestran decimales en UI.
4. Revisar que backend no reintroduce decimales por casteo o formato.

### Checklist antes de integrar

- [ ] Cada mejora se ha validado por separado.
- [ ] No hay cambios colaterales fuera del alcance.
- [ ] El diff final es pequeno y entendible.
- [ ] El sprint documenta lo integrado y lo que sigue pendiente.

### Prompt de code review

```text
Haz code review de las mejoras de fix/version2 que deben integrarse a develop en ERP_Ciete. El foco es: Modal.jsx, gestion de estaciones, categoria por contexto y casteo numerico. Quiero findings por severidad, con riesgo de regresion funcional y archivos concretos. Ignora cambios no relacionados salvo que afecten directamente a estas mejoras.
```

### Prompt de smoke test

```text
Necesito una lista de smoke tests funcionales, corta pero suficiente, para validar antes de integrar a develop estas mejoras de ERP_Ciete: modal de observaciones, selector de estacion por codigo, categoria por contexto y numericos sin decimales. Priorizalos por riesgo.
```

## Bloque P2 · Selector FK de numero de pedido en vista Excel de trabajos

### Objetivo

Eliminar edicion libre del numero de pedido en la vista Excel de trabajos y sustituirla por seleccion controlada de una FK real.

### Hallazgo base ya confirmado

Hoy el numero de pedido en la vista Excel de trabajos se esta pintando como texto en `resources/js/Components/ui/TrabajosExcelView.jsx`, usando `trabajo.numero_pedido_principal`. El cambio debe aterrizar ahi y en el backend que alimenta esa vista.

### Superficies a tocar primero

- `app/Http/Controllers/Api/TrabajoController.php`
- `resources/js/Pages/Trabajos/Index.jsx`
- `resources/js/Components/ui/TrabajosExcelView.jsx`

### Estrategia recomendada

1. Localizar como se resuelven hoy las celdas editables en `TrabajosExcelView.jsx`.
2. Decidir si el catalogo de pedidos entra como prop independiente o dentro de `creationCatalogs`.
3. Construir el catalogo en backend filtrado por contextos activos del usuario.
4. Pasar ese catalogo a `Trabajos/Index` y de ahi a `TrabajosExcelView`.
5. Sustituir la celda de texto por un selector controlado solo cuando la fila sea editable.
6. Persistir la FK real del pedido, no una etiqueta libre.
7. Mantener visualizacion de solo lectura cuando la fila no sea editable o no haya permiso.
8. Revalidar importe pedido, fecha solicitud y datos derivados si dependen de ese vinculo.

### Paso a paso detallado

1. Leer en `TrabajoController@index` como se construyen hoy `creationCatalogs`.
2. Localizar el punto de `Trabajos/Index.jsx` donde se monta `TrabajosExcelView`.
3. Revisar en `TrabajosExcelView.jsx` la celda actual donde se renderiza `trabajo.numero_pedido_principal`.
4. Reutilizar el patron ya existente de celdas editables siempre que sea posible.
5. Definir formato de opcion de pedido legible para usuario pero persistiendo el id real.
6. Bloquear escritura manual arbitraria en la columna.
7. Validar que el selector no muestre pedidos de otro contexto.
8. Validar que la columna sigue funcionando en vista global sin fuga de datos.

### Checklist de cierre

- [ ] El numero de pedido deja de ser texto libre editable.
- [ ] La persistencia usa FK valida.
- [ ] El catalogo solo contiene pedidos del contexto permitido.
- [ ] La fila no editable sigue viendose bien.
- [ ] No se rompe el resto de columnas derivadas del pedido.

### Prompt de analisis

```text
En ERP_Ciete necesito convertir la columna de numero de pedido de la vista Excel de trabajos en un selector FK real. Ya se que la vista vive en resources/js/Components/ui/TrabajosExcelView.jsx y que el backend de entrada pasa por app/Http/Controllers/Api/TrabajoController.php. Analiza el camino minimo para inyectar un catalogo de pedidos por contexto y sustituir la celda actual basada en trabajo.numero_pedido_principal por un selector controlado. Devuelveme el plan tecnico concreto antes de editar.
```

### Prompt de implementacion

```text
Implementa en ERP_Ciete la conversion de la columna numero de pedido de la vista Excel de trabajos a un selector FK controlado. Usa app/Http/Controllers/Api/TrabajoController.php, resources/js/Pages/Trabajos/Index.jsx y resources/js/Components/ui/TrabajosExcelView.jsx como puntos de entrada iniciales. Restricciones: no permitir edicion libre, no mezclar pedidos de otros contextos, no romper la vista global y validar el slice tocado antes de seguir.
```

### Prompt de QA

```text
Revisa la implementacion del selector FK de numero de pedido en TrabajosExcelView y busca regresiones de permisos, contexto, persistencia y columnas derivadas. Quiero findings concretos, no resumen general.
```

## Bloque bloqueado · Warning de tipos de hora

### Estado correcto para esta semana

No implementar aun. Solo preparar definicion, preguntas abiertas y arquitectura tentativa.

### Lo que si se puede hacer

1. Identificar si `tipos de hora` existe realmente como modelo, campo o concepto de negocio en el codigo.
2. Confirmar en que tabla y en que pantalla deberia aparecer el warning.
3. Definir que usuario ve el aviso, cuando se considera fila sucia y que accion limpia el estado.
4. Confirmar si la primera version necesita realtime de verdad o solo warning local.
5. Solo si todo esto queda claro, partir luego la implementacion en backend, hook y UI.

### Arquitectura propuesta para cuando se desbloquee

- Backend: crear `routes/channels.php`, registrar canal de presencia y validar por `usuario_contextos`.
- Realtime: usar Laravel Reverb y eventos whisper cliente a cliente.
- Frontend: crear `usePresenceTable` con debounce y estado local de cambios.
- UI: renderizar `React.Fragment` por fila e insertar un `tr` expandible con `colspan` total.
- Temporizador: cambiar severidad visual segun tiempo sin guardar.
- Seguridad: separar totalmente emisiones y escucha por `id_contexto`.

### Prompt para desbloqueo funcional

```text
Necesito desbloquear el requerimiento de warning de seguridad en tipos de hora en ERP_Ciete, pero aun no quiero implementar codigo. Quiero que localices si el concepto existe en el repo, en que pantalla podria vivir y que preguntas funcionales faltan por responder para definir una version 1 realista. Devuelveme solo descubrimiento y lista de dudas criticas.
```

## Definicion de terminado global del sprint

1. Los datos reales pueden trabajarse con menos ruido operativo.
2. OTROS CLIENTES queda cubierto sin degradar aislamiento.
3. Las mejoras de `fix/version2` pueden integrarse con validacion suficiente.
4. El siguiente desarrollo claro y acotado es el selector FK de numero de pedido.
5. El warning de tipos de hora queda bien delimitado, no mezclado dentro del alcance comprometido.

## Checklist global antes de merge o cierre parcial

- [ ] Codigo probado en el slice afectado.
- [ ] No hay bypass de `ContextScope`.
- [ ] No hay edicion libre en campos que deban ser FK.
- [ ] La vista global no mezcla contextos.
- [ ] Lo implementado y lo bloqueado estan documentados de forma separada.
- [ ] El diff final no arrastra cambios experimentales.

## Prompt maestro para trabajar este sprint con IA

```text
Trabaja sobre ERP_Ciete en el sprint de la semana 2026-05-10 a 2026-05-17. Quiero ejecutar el trabajo en este orden: 1) depuracion de importaciones reales, 2) consolidacion de maestros reales, 3) habilitacion segura de OTROS CLIENTES, 4) code review e integracion de mejoras ya hechas en fix/version2, 5) selector FK de numero de pedido en la vista Excel de trabajos. Restricciones criticas: no romper la vista actual, no permitir fugas entre contextos, toda query y logica sensible debe respetar ContextScope o la proteccion equivalente del repo, y tras cada edicion debes validar el slice mas cercano posible antes de seguir. Si una tarea sigue bloqueada, documenta el bloqueo y no inventes alcance.
```

## Prompt maestro para review final

```text
Haz una revision final de los cambios del sprint 07 en ERP_Ciete. Prioriza bugs, riesgos de regresion, fugas de contexto, errores de permisos, problemas de FK y diferencias entre lo comprometido y lo realmente implementado. Presenta findings primero y deja el resumen para el final.
```
