# Sprint 07 Plan

> **Documento operativo no normativo.**
> No usar como fuente funcional para implementar.
> Jerarquia obligatoria: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt` -> `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md` -> `docs/02_CLIENTE/tareasComparar.md` -> `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` como sintesis secundaria.

Periodo: 2026-05-10 a 2026-05-17

## Objetivo de la semana

Estabilizar la operativa con datos reales, consolidar el contexto OTROS CLIENTES con aislamiento estricto por contexto e integrar a `develop` las mejoras ya terminadas en `fix/version2` sin introducir regresiones en la vista Excel ni en los flujos actuales de pedidos y trabajos.

## Documento de ejecucion

Para implementar este sprint como guia paso a paso y usar prompts reutilizables de analisis, implementacion y QA, usar tambien:

- [SPRINT_07_GUIA_EJECUCION_Y_PROMPTS.md](./SPRINT_07_GUIA_EJECUCION_Y_PROMPTS.md)

## Prioridad P0 · Backlog critico

### 1. Depuracion de importaciones con cliente

Objetivo:
Revisar junto con CIETE las filas marcadas con aviso o ignoradas durante la importacion de los Excel reales para dejar identificado si cada caso es error de origen, regla valida o dato recuperable.

Resultado esperado:

- Catalogo de incidencias depurado y clasificado.
- Criterio de tratamiento acordado con cliente para avisos, ignorados y correcciones manuales.
- Reduccion del ruido operativo antes de nuevas cargas masivas.

### 2. Consolidacion de maestros reales

Objetivo:
Completar la carga de datos definitivos en el panel de administracion para contratos, sociedades facturadoras, tarifarios, lineas de tarifario, usuarios y roles.

Resultado esperado:

- Maestros productivos cargados y consistentes.
- Validacion funcional minima sobre relaciones criticas entre contratos, empresas facturadoras y tarifarios.
- Preparacion del sistema para operativa real sin depender de datos de prueba.

### 3. Habilitacion del contexto OTROS CLIENTES

Objetivo:
Preparar la carga y el gobierno de datos reales para OTROS CLIENTES manteniendo aislamiento absoluto frente a Repsol y Moeve.

Restriccion critica:

- Toda query y toda logica de inyeccion deben pasar por `ContextScope` en backend.
- No se admite acceso cruzado de datos entre contextos.
- Cualquier vista global debe seguir respetando la separacion por `id_contexto` al consultar, editar y emitir eventos.

Resultado esperado:

- Contexto OTROS CLIENTES operativo para carga real.
- Validacion de aislamiento a nivel de consultas y escritura.
- Base lista para crecimiento sin romper la segregacion actual.

## Prioridad P1 · Integracion segura de mejoras ya implementadas

Estas mejoras ya estan desarrolladas en `fix/version2`. El trabajo de esta semana es su code review, validacion funcional e integracion controlada a `develop`.

### 1. Modal de observaciones

Estado:
Implementado.

Alcance integrado:

- Correccion del problema de stacking del modal.
- Ajuste de backdrop para no interceptar clicks con `pointer-events-none`.
- Ajuste del panel con `relative` para recuperar la jerarquia visual correcta.
- Recuperacion completa de la interaccion del usuario.

### 2. Gestion hibrida de estaciones

Estado:
Implementado.

Alcance integrado:

- Columna `Codigo estacion` convertida en desplegable con formato tipo `G-001 - Nombre estacion`.
- Actualizacion de `id_estacion_servicio` desde seleccion controlada.
- Autocompletado de nombre, municipio y provincia desde la relacion.
- Campos derivados protegidos como solo lectura.

### 3. Categorizacion dinamica por contexto

Estado:
Implementado.

Alcance integrado:

- Repsol: selector estricto de `tipos_trabajo` filtrado por tipo de documento.
- Moeve y OTROS CLIENTES: categoria como texto libre.
- Compatibilidad mantenida en vista global con multiples contextos.

### 4. Casteo numerico en cantidades e importes

Estado:
Implementado.

Alcance integrado:

- Cantidades sin decimales en `ItemsTable`.
- Importes sin decimales en `PedidosExcelView` y en la vista Excel de trabajos.
- Normalizacion de inputs con `step="1"` donde aplica.

## Prioridad P2 · Desarrollo pendiente con dependencia de backend + frontend

### Selector FK para numero de pedido

Objetivo:
Eliminar la edicion libre en la FK de numero de pedido y convertir la columna en un selector controlado.

Trabajo necesario:

- Pasar el catalogo de pedidos desde el controlador Laravel a la vista Excel en React.
- Transformar la columna `Nº de pedido` en menu desplegable.
- Mantener la persistencia de la FK sin permitir entradas manuales invalidas.

Resultado esperado:

- Integridad referencial en frontend.
- Menor error humano en la captura.
- Comportamiento alineado con el resto de selectores de entidades relacionadas.

## Bloqueado · Requiere aclaracion funcional y definicion tecnica final

### Warning de seguridad en tipos de hora

Estado:
Bloqueado.

Motivo del bloqueo:

- El concepto `tipos de hora` no esta identificado de forma explicita en el codigo actual.
- La parte visual y colaborativa del requerimiento depende de confirmar primero el modelo funcional exacto.

Cuando se desbloquee, la implementacion propuesta es:

#### Backend

- Registrar canal de presencia en `routes/channels.php`.
- Validar acceso por `usuario_contextos`.
- Asegurar segmentacion estricta por `id_contexto`.
- Utilizar Laravel Reverb con canales de presencia para colaboracion en tiempo real.

#### Frontend

- Crear hook `usePresenceTable` para estado local de cambios.
- Emitir y escuchar eventos whisper con debounce.
- Mantener la colaboracion cliente a cliente sin cargar la base de datos para este flujo.
- Capturar campo modificado y timestamp de edicion.
- Evaluar con temporizador local si la fila supera el umbral de 1 o 2 horas sin guardarse.

#### UI

- Modificar la tabla principal, por ejemplo `TrabajosExcelView.jsx`, para renderizar un `React.Fragment` por registro.
- Renderizar una fila adicional `tr` justo debajo de la fila editada.
- Usar `colspan` completo para no romper la semantica de la tabla.
- Mantener la fila expandible como indicador pasivo constante mientras existan cambios sin guardar.
- Cambiar el estado visual del warning segun el tiempo transcurrido sin guardar, por ejemplo de amarillo a rojo intermitente al superar el umbral.

#### Regla critica

- Un usuario de Moeve no puede recibir eventos de una edicion en Repsol u OTROS CLIENTES, y viceversa.

## Orden de ejecucion recomendado

1. Depurar importaciones con cliente y cerrar criterio de tratamiento.
2. Consolidar maestros reales para evitar retrabajo sobre datos provisionales.
3. Habilitar OTROS CLIENTES validando aislamiento con `ContextScope`.
4. Ejecutar code review y merge controlado de las mejoras ya implementadas en `fix/version2`.
5. Abordar el selector FK de numero de pedido una vez estabilizada la capa de datos.
6. Mantener el warning de tipos de hora fuera de implementacion hasta aclarar el concepto funcional.

## Criterios de no regresion

- No romper el comportamiento ya validado en modal, estaciones, categorias por contexto y casteo numerico.
- No introducir edicion libre en campos que deban quedar gobernados por FK.
- No permitir bypass de `ContextScope` en consultas ni escritura.
- No abrir canales realtime sin segmentacion estricta por contexto.

## Definicion operativa del sprint

Comprometido esta semana:

- Backlog critico con cliente y datos reales.
- Integracion segura de mejoras ya terminadas.
- Preparacion del desarrollo pendiente de FK numero de pedido.

Fuera de implementacion hasta aclaracion:

- Warning colaborativo de tipos de hora con presencia realtime.
