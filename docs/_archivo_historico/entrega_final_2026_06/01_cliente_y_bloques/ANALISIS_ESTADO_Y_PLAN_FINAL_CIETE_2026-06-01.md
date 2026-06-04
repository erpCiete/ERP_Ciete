# Analisis estado y plan final CIETE - 2026-06-01

## 1. Alcance y criterio

Este documento se ha elaborado solo a partir de las fuentes vivas obligatorias:

- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/listado_exhaustivo_reunion_cesar_amaya_ciete_2026-05-19.md`
- `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
- `docs/02_CLIENTE/ESTADO_Y_BLOQUES_RESTANTES_REUNION_CIETE_2026-05-29.md`
- `docs/02_CLIENTE/ESPECIFICACION_COLUMNAS_TRABAJOS_2026-05-29.md`

Criterio usado:

- tomar la reunión del `2026-05-19` como fuente de lo pedido;
- tomar `tareasComparar.md` como fuente viva más actual del estado real;
- resolver contradicciones por fecha, dando prioridad a la evidencia más reciente.

## 2. Qué se pidió en la reunión

La reunión pide, en términos operativos, siete cosas:

1. Llevar el día a día a `Trabajos` como pantalla principal tipo Excel.
2. Poder crear y consultar pedidos desde el propio trabajo, manteniendo como caso normal `1 trabajo -> 1 pedido`, pero permitiendo varios pedidos como excepción.
3. Seleccionar el `Tarifario` correcto en el flujo real de trabajo y cerrar la relación con contrato y sociedad facturadora.
4. Construir líneas de pedido desde tarifario, con buscador por código y descripción y con soporte decimal.
5. Derivar mejor los estados del trabajo y conectar pedido, solicitado, facturado, terminado, finalizado y cancelado.
6. Mantener trazabilidad: auditoría, control de concurrencia por campo, roles finos y maestros sin borrado físico.
7. Generar salida operativa desde pedido: PDF, CSV y cuadro ARIBA/Moeve.

## 3. Qué está hecho a 2026-06-01

Tomando como referencia la última evidencia fechada en `tareasComparar.md`, a fecha `2026-06-01` queda implementado en código y validado técnicamente lo siguiente:

- `Trabajos` queda fijado como pantalla operativa principal.
- La vista tipo Excel está compactada, con buscador avanzado, filtros, chips y ordenación por columnas.
- La nomenclatura visible de la tabla se ha alineado con lo hablado con CIETE.
- Se puede crear pedido desde trabajo y mantener la relación operativa trabajo/pedido.
- `Tarifario` predeterminado queda modelado en maestros y se consume desde `Trabajos` y `Pedidos`.
- Las líneas de pedido ya salen de tarifario, con buscador por código y descripción y soporte decimal.
- La exportación Moeve queda resuelta técnicamente en tres salidas:
    - HTML imprimible/PDF inicial;
    - CSV;
    - cuadro ARIBA.
- Estados, facturación y cierre secundario ya dependen de datos reales y no de edición manual aislada.
- Bloque 6 queda cerrado en su parte crítica:
    - permisos finos;
    - auditoría de cierre;
    - avisos al desactivar contratos/tarifarios usados.

## 4. Qué falta realmente

Lo que falta ya no es implementación base del flujo principal, sino evidencia funcional final fuera de tests/build:

- validación visual/manual real en navegador del flujo completo;
- contraste con negocio del formato operativo de exportación Moeve;
- confirmación final de supuestos de tarifario predeterminado cuando exista casuística por sociedad facturadora;
- revisión de remates P1 no bloqueantes en maestros y superficie legacy.

## 5. Estado general

Estado general: flujo principal técnicamente cerrado; cierre funcional total todavía condicionado por validación manual real y contraste final con negocio.

Lectura consolidada de las fuentes y de la ejecución del `Prompt 1`:

- la cadena `Trabajo -> Tarifario -> Pedido -> Líneas -> Exportación -> Facturación -> Cierre -> Auditoría` ya tiene cobertura técnica;
- el build final está en verde;
- las suites PHP principales están en verde;
- no hay fallo bloqueante de código detectado;
- la validación manual de navegador no pudo ejecutarse desde esta sesión por falta de GUI/browser.

## 6. Porcentaje real aproximado

Porcentaje actualizado de la ola funcional pedida en la reunión, sin inflar la parte visual pendiente:

- Implementado en código: **95%**
- Validado técnicamente: **90%**
- Validado visualmente: **55%**
- Pendiente de negocio: **10%**

Lectura de esos porcentajes:

- el núcleo funcional ya existe y ha pasado build + tests principales;
- la cifra visual no sube más porque el cierre transversal completo en navegador no se ha podido repetir desde este entorno;
- el pendiente de negocio se concentra en validación de formato/exportación y casuística fina de tarifario/sociedad, no en rehacer bloques completos.

## 7. Bloques restantes

### Bloque G - Validación final y cierre funcional real

El único bloque realmente abierto ya no es de desarrollo principal, sino de validación final:

- validación visual/manual de navegador;
- validación funcional con negocio;
- cierre de remates menores no bloqueantes que puedan surgir de esa revisión.

Estado final por bloques:

- Bloque A: completo.
- Bloque 1/B: completo técnico.
- Bloque 2: completo técnico.
- Bloque 3: completo técnico.
- Bloque 4: completo técnico.
- Bloque 5: completo técnico.
- Bloque 6 crítico: completo técnico.
- Bloque G: cierre técnico/documental ejecutado; pendiente validación manual navegador + negocio.

## 8. Plan inmediato recomendado

Plan inmediato a partir de este estado:

### Sesión 1 - Navegador

- abrir `Trabajos` y recorrer flujo completo con datos reales;
- validar pedido, líneas, exportación, facturación y cierre;
- revisar mensajes de desactivación en contratos/tarifarios.

### Sesión 2 - Negocio

- revisar con CIETE si el HTML imprimible/PDF inicial, CSV y cuadro ARIBA son aceptables como formato operativo;
- confirmar si el tarifario predeterminado necesita excepción por sociedad facturadora.

### Sesión 3 - Cierre

- si no aparecen fallos reales, cerrar la versión como funcionalmente apta para demo/revisión;
- si aparece desviación menor, tratarla como remate puntual sin reabrir arquitectura.

## 9. Riesgos y dependencias

### Riesgos principales

- la ausencia de validación visual/manual desde esta sesión impide declarar cierre funcional total;
- el formato actual de “PDF Moeve” es HTML imprimible inicial y puede requerir ajuste documental si negocio exige otro layout;
- el worktree local ya venía muy sucio por cambios/documentación ajenos al cierre y conviene no mezclar limpieza histórica con validación final.

### Dependencias reales

- hace falta al menos una comprobación real en navegador con datos operativos;
- hace falta confirmación de negocio sobre formatos de exportación y casuística fina de tarifario/sociedad;
- si el servidor productivo no tiene aplicada la migración `tarifarios.es_predeterminado`, debe mantenerse el hotfix de compatibilidad ya introducido en `/trabajos`.

## 10. Conclusión operativa

CIETE deja de estar en fase de construcción principal y pasa a fase de validación final.

El tramo crítico pedido en reunión:

`Trabajo -> Tarifario correcto -> Pedido con líneas -> Exportación -> Facturación -> Cierre`

queda ya implementado en código y validado técnicamente. Lo que falta para cierre funcional total no es otro bloque de desarrollo, sino evidencia manual/visual y contraste final con negocio sobre los supuestos operativos todavía abiertos.

## 11. Siguiente acción real

- No abrir desarrollo nuevo.
- Ejecutar validación manual con caso Moeve y Repsol.
- Si no aparecen fallos, preparar demo/revisión con CIETE.
- Si aparecen fallos, tratarlos como hotfix puntual, no como reapertura de bloques.
