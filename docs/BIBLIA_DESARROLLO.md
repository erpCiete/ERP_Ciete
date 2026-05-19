# Biblia de desarrollo ERP CIETE

> **Documento vivo.**  
> Este documento debe mantenerse alineado con la fuente de verdad funcional vigente del ERP CIETE.  
> Fuente principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`.

Estado: guia tecnica viva compactada.

Jerarquia funcional obligatoria:

1. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
2. `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
3. `docs/02_CLIENTE/tareasComparar.md`
4. `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` como sintesis interpretativa secundaria
5. Contratos API y documentacion tecnica viva solo si no contradicen 1-4

Backlog maestro vigente:

- `docs/02_CLIENTE/tareasComparar.md`

Contrato tecnico vivo:

- `docs/03_API_ERP/Trabajos_API_Contract.md`

## 1. Principio rector

El ERP CIETE no se implementa desde documentos antiguos si contradicen la jerarquia documental vigente. La reunion/transcripcion manda sobre cualquier interpretacion posterior. `DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` debe usarse solo como sintesis interpretativa secundaria de esa reunion, no como sustituto de la voz del cliente cuando haya conflicto. Los planes de sprint, bitacoras, manuales anteriores y contratos API antiguos son contexto historico.

## 2. Stack

- Backend: Laravel.
- Frontend: Inertia + React.
- Base de datos: MySQL/MariaDB.
- UI operativa: Ciete Excel y Ciete Moderno.

## 3. Modelo funcional base

```text
contextos_cliente
usuarios
roles
permisos
empresas/sociedades/CIF
contratos
tarifarios
tarifario_lineas
estaciones_servicio
trabajos
pedidos
pedido_items
facturas
factura_items
audit_log
```

Relacion economica correcta:

```text
factura -> factura_items -> pedido_items -> pedidos -> trabajos
```

`facturas.id_trabajo` y `factura_pedidos` son legacy/compatibilidad, no flujo principal.

## 4. Contextos

- MOEVE, REPSOL y OTROS CLIENTES son contextos reales.
- TODOS es vista global operativa segun permisos.
- Desde TODOS se puede ver, filtrar y editar registros existentes.
- Desde TODOS no se puede crear.
- Crear desde TODOS debe bloquearse en backend y explicarse en UI.
- OTROS CLIENTES no es TODOS.

## 5. Roles

Roles funcionales:

- Direccion.
- Ejecucion.
- Ejecucion MOEVE.
- Ejecucion REPSOL.
- Contabilidad.
- Soporte/admin tecnico.

Reglas:

- Admin es rol tecnico/soporte.
- Direccion es rol funcional operativo.
- Direccion y admin tecnico pueden gestionar usuarios.
- Direccion no debe ver pantallas tecnicas innecesarias.

## 6. Trabajos

- Estado inicial funcional: `en_curso`.
- Estados funcionales:
    - `en_curso`
    - `terminado`
    - `pendiente_facturar`
    - `facturado`
    - `finalizado`
    - `cancelado`
- Un trabajo puede existir sin pedido.
- Un trabajo puede tener varios pedidos.
- `terminado` significa ejecucion hecha.
- `finalizado` significa ejecucion y economia resueltas.
- Cancelados visibles, filtrables y al final.

## 7. Pedidos e items

- Pedido pertenece a un solo trabajo.
- Pedido tiene varios items.
- `pedido_item` es la unidad economica real.
- Los items se actualizan por ID.
- No borrar/recrear items facturables.
- Un item puede facturarse parcialmente.

## 8. Facturacion

- La factura agrupa items.
- Una factura puede incluir items de varios pedidos y trabajos.
- Todos los items deben compartir contrato/tarifa compatible.
- La sociedad/CIF debe estar permitida para el contrato/tarifa/grupo.
- Listado plano:
    - numero factura
    - sociedad
    - CIF
    - fecha
    - importe
    - importe asignado
    - diferencia
    - estado de cuadre
- Cobros fuera del flujo principal.

## 9. Estaciones

- Codigo estacion es columna principal.
- Codigo preferente: 6 caracteres.
- Codigo flexible y alfanumerico.
- Unico por contexto/cliente, no global.
- Columnas principales:
    - codigo
    - nombre
    - municipio
    - provincia
- Fecha de baja se conserva, pero no es columna protagonista.
- No borrar estaciones fisicamente.

## 10. Ciete Excel y Ciete Moderno

Ciete Excel:

- Tabla densa.
- Filtros visibles.
- Edicion por celda.
- Guardado por campo.
- Observaciones en modal.
- Control optimista por campo con `updated_at`.
- Conflicto inline, no toast perdido ni sobrescritura silenciosa.

Ciete Moderno:

- Fichas.
- Formularios.
- Detalle limpio.
- No debe convertirse en tabla Excel.

## 11. Auditoria

- Nombre visible: Auditoria.
- Por defecto muestra actividad operativa relevante.
- No auditar como relevante:
    - `interface_mode`
    - tema
    - idioma
    - preferencias visuales
- Exportar/limpiar logs: Direccion.
- La limpieza de logs debe dejar registro no borrado en la misma operacion.

## 12. Fuera de alcance inmediato

- Cobros.
- Presupuestos/hoja de pedido.
- Legalizaciones completas.
- Importacion Excel avanzada.
- Actualizacion automatica de estaciones.
- Costes e imputacion avanzada.

## 13. Reglas de implementacion

- No romper datos existentes.
- No borrar historico.
- No proponer reescrituras completas si basta una transicion compatible.
- No crear desde TODOS.
- No tratar documentos historicos como verdad funcional.
- No duplicar decisiones funcionales fuera de la fuente de verdad.
- Mantener bitacoras intactas.

## 14. Comandos prohibidos salvo autorizacion expresa

- `php artisan migrate:fresh`
- `php artisan migrate:refresh`
- `php artisan db:wipe`
- Borrado masivo de datos.
- Eliminacion fisica de historico.

## 15. Documentos historicos

Los documentos antiguos se conservan para trazabilidad. Si contradicen esta Biblia compactada o cualquier documento historico/operativo no normativo, prevalece este orden:

1. `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`
2. `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`
3. `docs/02_CLIENTE/tareasComparar.md`
4. `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md` como sintesis interpretativa secundaria
5. `docs/03_API_ERP/Trabajos_API_Contract.md` y documentacion tecnica viva solo si no contradicen 1-4

Los sprints, planes historicos, revisiones documentales antiguas, memorias y bitacoras no son fuente funcional para implementar.
