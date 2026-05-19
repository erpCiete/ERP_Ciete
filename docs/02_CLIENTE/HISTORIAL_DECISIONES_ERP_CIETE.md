# Historial de decisiones ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`.

Estado: documento de trazabilidad historica de evolucion funcional. No normativo para implementar por si solo.

Este documento explica como han evolucionado decisiones relevantes del ERP CIETE. No borra ni invalida la historia del proyecto: registra por que ciertas interpretaciones anteriores quedan superadas por decisiones funcionales posteriores.

Jerarquia vigente a la que este historial queda subordinado:

1. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
2. `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
3. `docs/02_CLIENTE/tareasComparar.md`
4. `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` como sintesis interpretativa secundaria

Este historial no prevalece sobre la reunion/transcripcion, la auditoria vigente ni el backlog maestro.

## 1. Contexto del historial

Durante el desarrollo del ERP CIETE se generaron documentos de alcance, planes de sprint, bitacoras, contratos API y analisis funcionales. Algunos reflejan decisiones validas en su momento, pero la reunion con CIETE y las matizaciones posteriores cerraron reglas nuevas.

Este historial permite defender la evolucion sin borrar documentos anteriores.

## 2. Registro de evolucion

### H-001. TODOS como vista global operativa

1. Decision anterior

    TODOS se interpretaba en algunos puntos como una vista principalmente de consulta.

2. Problema detectado

    CIETE necesita trabajar globalmente cuando el usuario tiene permisos: ver, filtrar y editar registros existentes de varios contextos sin cambiar constantemente de cliente.

3. Nueva decision

    TODOS es vista global operativa segun permisos. Permite ver, filtrar y editar registros existentes. No permite crear registros nuevos.

4. Motivo del cambio

    Evita friccion operativa para perfiles multicontexto y mantiene seguridad al exigir contexto real para altas.

5. Documento/reunion que lo justifica

    Reunion CIETE y decision funcional posterior confirmada por Pablo.

### H-002. TODOS no es OTROS CLIENTES

1. Decision anterior

    En algunas conversaciones podia confundirse TODOS con un contexto generico o con OTROS.

2. Problema detectado

    TODOS es una vista transversal; OTROS CLIENTES es un cliente/contexto operativo real.

3. Nueva decision

    TODOS no es contexto real. OTROS CLIENTES si es contexto real y permite crear, editar y operar.

4. Motivo del cambio

    Evita mezclar datos y evita que nuevas altas caigan en un contexto incorrecto.

5. Documento/reunion que lo justifica

    Matizacion posterior de Pablo sobre contextos.

### H-003. Admin tecnico separado de Direccion

1. Decision anterior

    Admin aparecia como perfil amplio capaz de operar muchas areas.

2. Problema detectado

    En CIETE, admin no debe ser la figura operativa normal. Direccion tiene responsabilidades funcionales y admin queda para soporte/desarrollo.

3. Nueva decision

    Admin queda como rol tecnico/soporte. Direccion es rol funcional operativo y puede gestionar usuarios sin ver pantallas tecnicas innecesarias.

4. Motivo del cambio

    Alinea el ERP con la forma real de gestion de CIETE y evita ruido en la demo y en uso diario.

5. Documento/reunion que lo justifica

    Reunion CIETE y decisiones cerradas de roles.

### H-004. OTROS CLIENTES como contexto real

1. Decision anterior

    OTROS CLIENTES podia tratarse como cajon menor o futura ampliacion.

2. Problema detectado

    CIETE necesita operar clientes no MOEVE/REPSOL sin convertirlos en TODOS ni forzar estructuras ajenas.

3. Nueva decision

    OTROS CLIENTES es contexto real, con creacion, edicion y operativa completa, adaptando campos especificos.

4. Motivo del cambio

    Permite crecer sin duplicar logica ni mezclar datos.

5. Documento/reunion que lo justifica

    Matizacion posterior de Pablo sobre contextos reales.

### H-005. Codigo de estacion flexible y no global

1. Decision anterior

    Se debatieron limites estrictos de longitud y formatos por cliente.

2. Problema detectado

    Existen casos raros con letras, prefijos y codigos no perfectamente homogeneos.

3. Nueva decision

    Codigo preferente de 6 caracteres, alfanumerico y flexible. Unico dentro de contexto/cliente, no global.

4. Motivo del cambio

    Mantiene la disciplina operativa sin estrechar la base de datos ni bloquear casos reales.

5. Documento/reunion que lo justifica

    Debate de estaciones en reunion CIETE.

### H-006. Estaciones no se borran fisicamente

1. Decision anterior

    El mantenimiento de estaciones podia interpretarse como alta/baja con posible eliminacion.

2. Problema detectado

    Una estacion puede tener historico y puede reaparecer o seguir asociada a trabajos.

3. Nueva decision

    Las estaciones nunca se borran fisicamente. Se conserva fecha de baja, pero no es columna protagonista.

4. Motivo del cambio

    Protege historico y evita romper trabajos existentes.

5. Documento/reunion que lo justifica

    Reunion CIETE, apartado de estaciones e historico.

### H-007. Estado inicial de trabajo

1. Decision anterior

    Existia uso de `borrador` como estado inicial en partes del sistema.

2. Problema detectado

    En la operativa real, cuando se crea un trabajo ya nace como trabajo en curso.

3. Nueva decision

    Estado inicial funcional: `en_curso`.

4. Motivo del cambio

    Reduce ruido y alinea la pantalla con el Excel operativo de CIETE.

5. Documento/reunion que lo justifica

    Reunion CIETE y decisiones cerradas de trabajos.

### H-008. Terminado no equivale a finalizado

1. Decision anterior

    Algunos flujos usaban cierre/cerrado como final operativo.

2. Problema detectado

    CIETE separa ejecucion terminada de resolucion economica.

3. Nueva decision

    `terminado` significa ejecucion hecha. `finalizado` significa terminado y economicamente resuelto/facturado/cuadrado.

4. Motivo del cambio

    Evita cerrar trabajos antes de completar la parte economica.

5. Documento/reunion que lo justifica

    Reunion CIETE, debate de terminacion, factura y finalizacion.

### H-009. Cobros fuera del flujo principal

1. Decision anterior

    Algunos documentos y esquemas incluian cobros como parte natural del ERP operativo.

2. Problema detectado

    CIETE confirmo que el cobro lo lleva otro departamento.

3. Nueva decision

    El ERP CIETE termina en factura. Cobros quedan fuera del flujo principal.

4. Motivo del cambio

    Evita ampliar el alcance y centra la version actual en trabajos, pedidos, items y facturas.

5. Documento/reunion que lo justifica

    Reunion CIETE, bloque de facturas y cobro.

### H-010. Pedido item como unidad economica real

1. Decision anterior

    Se trabajaba en algunos puntos con pedido o factura como unidad economica principal.

2. Problema detectado

    CIETE factura conceptos/items, incluso parcialmente.

3. Nueva decision

    `pedido_item` es la unidad economica/facturable real.

4. Motivo del cambio

    Permite facturacion parcial y agrupacion real de facturas.

5. Documento/reunion que lo justifica

    Reunion CIETE, bloque de pedidos, items y facturas.

### H-011. No borrar/recrear items de pedido

1. Decision anterior

    Una sincronizacion simple podia borrar y recrear items al actualizar pedido.

2. Problema detectado

    Si un item esta vinculado a una factura, borrar/recrear rompe referencias e historico.

3. Nueva decision

    Los items deben actualizarse por ID y conservar referencias.

4. Motivo del cambio

    Protege facturacion parcial, auditoria e integridad de datos.

5. Documento/reunion que lo justifica

    Decision tecnica derivada del modelo `factura_items -> pedido_items`.

### H-012. Factura no depende de un unico trabajo

1. Decision anterior

    La factura estaba modelada o pensada como hija de trabajo en partes del sistema.

2. Problema detectado

    Una factura puede agrupar items de varios pedidos y varios trabajos.

3. Nueva decision

    Relacion principal: `factura -> factura_items -> pedido_items -> pedidos -> trabajos`.

4. Motivo del cambio

    Refleja la forma real de facturar de CIETE.

5. Documento/reunion que lo justifica

    Reunion CIETE y analisis funcional posterior.

### H-013. `facturas.id_trabajo` y `factura_pedidos` como legacy

1. Decision anterior

    `id_trabajo` y `factura_pedidos` podian actuar como flujo principal.

2. Problema detectado

    No representan correctamente facturas compuestas por items de varios trabajos.

3. Nueva decision

    `facturas.id_trabajo` queda nullable/legacy. `factura_pedidos` queda temporal/legacy.

4. Motivo del cambio

    Mantiene compatibilidad sin bloquear el modelo correcto.

5. Documento/reunion que lo justifica

    Reunion CIETE y decisiones cerradas de facturacion.

### H-014. Sociedad/CIF ligada a contrato/tarifa

1. Decision anterior

    Sociedad/CIF podia tratarse como texto o dato suelto de factura.

2. Problema detectado

    Una factura debe emitirse contra una sociedad/CIF permitida bajo contrato/tarifa/grupo.

3. Nueva decision

    Debe existir relacion de sociedades permitidas por contrato/tarifa.

4. Motivo del cambio

    Evita facturar items correctos contra entidad fiscal incorrecta.

5. Documento/reunion que lo justifica

    Reunion CIETE, bloque de sociedades, CIF, contratos y tarifas.

### H-015. Ciete Excel como operativa diaria

1. Decision anterior

    La ficha moderna podia parecer suficiente como experiencia principal.

2. Problema detectado

    CIETE trabaja de forma muy intensiva con una tabla tipo Excel.

3. Nueva decision

    Ciete Excel es la vista diaria: tabla densa, filtros, edicion por celda y guardado por campo.

4. Motivo del cambio

    Alinea ergonomia y velocidad de uso con la operativa real.

5. Documento/reunion que lo justifica

    Reunion CIETE, bloque de vista tipo Excel.

### H-016. Ciete Moderno como ficha limpia

1. Decision anterior

    Podia mezclarse la ficha moderna con la logica Excel.

2. Problema detectado

    Son experiencias distintas para necesidades distintas.

3. Nueva decision

    Ciete Moderno conserva fichas, formularios y detalle limpio. No debe convertirse en tabla Excel.

4. Motivo del cambio

    Permite revisar detalle sin perder la operativa densa de Ciete Excel.

5. Documento/reunion que lo justifica

    Decision funcional posterior sobre modos de interfaz.

### H-017. Conflictos de edicion por campo

1. Decision anterior

    Se valoraron bloqueos o avisos generales de edicion.

2. Problema detectado

    CIETE necesita trabajar desde tabla, no desde ficha bloqueada.

3. Nueva decision

    Guardado por campo con control optimista mediante `updated_at` y aviso inline si hay conflicto.

4. Motivo del cambio

    Reduce pisados silenciosos sin bloquear la operativa.

5. Documento/reunion que lo justifica

    Reunion CIETE, bloque de edicion simultanea.

### H-018. Auditoria sin ruido visual

1. Decision anterior

    Podian registrarse cambios de preferencias como actividad normal.

2. Problema detectado

    Direccion necesita auditoria operativa, no ruido cosmetico.

3. Nueva decision

    Cambios de `interface_mode`, tema, idioma o preferencias visuales no se auditan como actividad relevante.

4. Motivo del cambio

    Hace la Auditoria util para control interno.

5. Documento/reunion que lo justifica

    Matizacion posterior de Pablo sobre Auditoria.

### H-019. Exportar y limpiar Auditoria solo Direccion

1. Decision anterior

    Exportacion/limpieza podia asociarse a admin tecnico.

2. Problema detectado

    Admin no es rol operativo normal de CIETE.

3. Nueva decision

    Exportar y limpiar logs corresponde a Direccion. Admin tecnico solo interviene por soporte concreto.

4. Motivo del cambio

    Mantiene responsabilidad funcional en CIETE y separa soporte tecnico.

5. Documento/reunion que lo justifica

    Decision posterior de roles y Auditoria.

### H-020. Limpieza de logs trazada y no autodestructiva

1. Decision anterior

    La limpieza podia entenderse solo como borrado tecnico de registros.

2. Problema detectado

    Limpiar auditoria sin trazabilidad reduce confianza y control.

3. Nueva decision

    La limpieza registra usuario, fecha, filtros aplicados y numero de registros afectados. Ese registro no se borra en la misma operacion.

4. Motivo del cambio

    Conserva trazabilidad de acciones sensibles.

5. Documento/reunion que lo justifica

    Decision 50 asumida por Pablo.

### H-021. Legalizaciones como fuera del flujo critico

1. Decision anterior

    Legalizaciones podian aparecer como bloque de cierre.

2. Problema detectado

    La reunion dejo legalizaciones completas fuera de la version critica.

3. Nueva decision

    Legalizaciones completas quedan fuera del flujo critico o como informacion no bloqueante.

4. Motivo del cambio

    Evita bloquear trabajos/facturacion por un modulo que no pertenece al P0.

5. Documento/reunion que lo justifica

    Reunion CIETE y decisiones fuera de alcance.

### H-022. Presupuestos e importacion avanzada como alcance posterior

1. Decision anterior

    Presupuestos/importacion podian aparecer como prioridades relevantes.

2. Problema detectado

    La alineacion principal necesita cerrar trabajos, pedidos, items, facturas y contexto.

3. Nueva decision

    Presupuestos/hoja de pedido quedan fuera de alcance inmediato. Importacion Excel avanzada queda como P2.

4. Motivo del cambio

    Prioriza el flujo operativo y economico real antes de automatizaciones secundarias.

5. Documento/reunion que lo justifica

    Reunion CIETE y plan funcional posterior.

## 3. Documentos historicos a interpretar con este historial

- `docs/BIBLIA_DESARROLLO.md`
- `docs/03_API_ERP/Trabajos_API_Contract.md`
- `docs/02_CLIENTE/02_Requisitos_y_Acuerdos.md`
- `docs/02_CLIENTE/tareasComparar.md`
- `docs/02_CLIENTE/historicos/Analisis_Reunion_CIETE_Plan_2_Sprints_2026-05-04.md`
- `docs/05-SPRINTS/Sprint_01/PLAN_EJECUCION_SPRINT1.md`
- `docs/05-SPRINTS/Sprint_01/RESUMEN_EJECUCION_02_05_2026.md`

Estos documentos no deben borrarse. Deben leerse como contexto historico, planes de trabajo o diagnosticos previos, y alinearse progresivamente con la fuente de verdad vigente.
