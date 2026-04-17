# Contrato API: modulo de Trabajos

Estado: contrato v2 — alineado con BD MariaDB y migraciones reales  
Fecha auditoria: 2026-04-17  
Fuente de verdad: dump MariaDB `abaco_ciete` + migraciones Laravel del repo

## 1. Estado real

| Area                 | Existe en BD                                                                                                                           | Notas                                          |
| -------------------- | -------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| Entidad de trabajo   | `trabajos` (PK `id_trabajo`)                                                                                                           | Tabla definitiva, no hay `proyectos`           |
| Catalogos trabajo    | `tipos_documento`, `tipos_trabajo`                                                                                                     | FK desde `trabajos`                            |
| Contratos/tarifarios | `contratos`, `tarifarios`, `tarifario_lineas`, `unidades`                                                                              | Completo                                       |
| Pedidos              | `pedidos` (FK `id_trabajo`), `pedido_items`                                                                                            | Items sin campo `orden`                        |
| Facturas             | `facturas` (FK `id_trabajo`), `factura_pedidos` (pivot)                                                                                | No hay tabla `facturas_lineas`                 |
| Cobros               | `cobros` (FK `id_factura`)                                                                                                             | Completo                                       |
| Legalizaciones       | `legalizaciones`, `legalizaciones_contactos`, `comentarios_legalizaciones`                                                             | FK a `trabajos`                                |
| Presupuestos         | `presupuestos`, `presupuesto_lineas`                                                                                                   | FK a `trabajos`                                |
| Trazabilidad         | `audit_log`                                                                                                                            | Tabla existe, sin registros aun                |
| Importacion          | `importaciones`, `importacion_filas`                                                                                                   | Staging creado, sin registros aun              |
| Permisos seed        | `trabajos.*`, `trabajos_cerrados.*`, `pedidos.*`, `facturas.*`, `cobros.*`, `legalizaciones.*`, `importaciones.*`, `auditoria.ver`     | 31 permisos totales                            |
| Modelos Laravel      | `Trabajo`, `Pedido`, `PedidoItem`, `Factura`, `Cobro`, `Presupuesto`, `Legalizacion`, `Contrato`, `TipoDocumento`, `TipoTrabajo`, etc. | Completos con relaciones                       |
| API actual del repo  | No hay endpoints de trabajos/importaciones                                                                                             | Faltan controllers, requests, resources, rutas |

Resumen: la BD ya usa `trabajos` como tabla definitiva. Los permisos seed ya son `trabajos.*`. La API debe exponer `/api/v1/trabajos` usando los nombres reales de columna (`id_trabajo`, `numero_trabajo`, etc.).

## 2. Alcance del modulo

Un `Trabajo` es un registro de la tabla `trabajos`:

| Relacion           | Cardinalidad | Tabla/campo                                    |
| ------------------ | -----------: | ---------------------------------------------- |
| Contexto/cliente   |          N:1 | `trabajos.id_contexto` → `contextos_cliente`   |
| Empresa cliente    |          N:1 | `id_empresa_cliente` → `empresas`              |
| Estacion           | N:1 opcional | `id_estacion_servicio` → `estaciones_servicio` |
| Tipo documento     | N:1 opcional | `id_tipo_documento` → `tipos_documento`        |
| Tipo trabajo       | N:1 opcional | `id_tipo_trabajo` → `tipos_trabajo`            |
| Contrato           | N:1 opcional | `id_contrato` → `contratos`                    |
| Tarifario          | N:1 opcional | `id_tarifario` → `tarifarios`                  |
| Responsable Ciete  | N:1 opcional | `id_responsable_ciete` → `usuarios`            |
| Responsable cierre | N:1 opcional | `id_usuario_cierre` → `usuarios`               |
| Pedidos            |          1:N | `pedidos.id_trabajo`                           |
| Facturas           |          1:N | `facturas.id_trabajo`                          |
| Presupuestos       |          1:N | `presupuestos.id_trabajo`                      |
| Legalizaciones     |          1:N | `legalizaciones.id_trabajo`                    |

Nota: no existen tablas `workplan` ni `comentarios` para trabajos. Solo `comentarios_legalizaciones` para legalizaciones.

## 3. Convenciones API

| Aspecto          | Contrato                                                                                                                                             |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| Prefijo          | `/api/v1`                                                                                                                                            |
| Auth             | Sesion Laravel actual (`web` + `auth`). Axios same-origin con CSRF.                                                                                  |
| Contexto         | Siempre server-side desde usuario/contexto activo. No confiar en `id_contexto` recibido.                                                             |
| Permisos         | `trabajos.ver`, `trabajos.crear`, `trabajos.editar`, `trabajos.cerrar`, `trabajos.reabrir`, `trabajos_cerrados.editar`, `trabajos_cerrados.reabrir`. |
| Formato exito    | `{"success":true,"message":"...","data":...,"meta":{"timestamp":"..."}}`                                                                             |
| Formato paginado | `meta.pagination.total,count,per_page,current_page,total_pages`.                                                                                     |
| Formato error    | `{"success":false,"message":"...","error_code":"...","errors":{...},"meta":{"timestamp":"..."}}`                                                     |
| Fechas           | `YYYY-MM-DD`; datetimes ISO 8601.                                                                                                                    |
| Decimales        | String decimal en responses para evitar errores de precision.                                                                                        |

## 4. Endpoints

| Metodo | Ruta                                            | Finalidad                           | Permiso                                                        | Estado                |
| ------ | ----------------------------------------------- | ----------------------------------- | -------------------------------------------------------------- | --------------------- |
| GET    | `/api/v1/trabajos`                              | Listado paginado/filtrado           | `trabajos.ver`                                                 | Pendiente implementar |
| POST   | `/api/v1/trabajos`                              | Crear trabajo                       | `trabajos.crear`                                               | Pendiente implementar |
| GET    | `/api/v1/trabajos/{trabajo}`                    | Detalle                             | `trabajos.ver`                                                 | Pendiente implementar |
| PATCH  | `/api/v1/trabajos/{trabajo}`                    | Editar                              | `trabajos.editar`; cerrado requiere `trabajos_cerrados.editar` | Pendiente implementar |
| POST   | `/api/v1/trabajos/{trabajo}/cerrar`             | Cerrar                              | `trabajos.cerrar`                                              | Pendiente implementar |
| POST   | `/api/v1/trabajos/{trabajo}/reabrir`            | Reabrir                             | `trabajos.reabrir` o `trabajos_cerrados.reabrir`               | Pendiente implementar |
| GET    | `/api/v1/trabajos/catalogos`                    | Auxiliares para formularios/filtros | `trabajos.ver`                                                 | Pendiente implementar |
| POST   | `/api/v1/importaciones/trabajos`                | Subir Excel                         | `importaciones.ejecutar`                                       | Pendiente implementar |
| GET    | `/api/v1/importaciones/{importacion}/preview`   | Preview validado                    | `importaciones.ver`                                            | Pendiente implementar |
| POST   | `/api/v1/importaciones/{importacion}/validar`   | Revalidar                           | `importaciones.ejecutar`                                       | Pendiente implementar |
| POST   | `/api/v1/importaciones/{importacion}/confirmar` | Persistir                           | `importaciones.ejecutar`                                       | Pendiente implementar |
| GET    | `/api/v1/importaciones/{importacion}/errores`   | Errores exportables                 | `importaciones.ver`                                            | Pendiente implementar |

No definir DELETE en v1. El dominio tiene cierre/reapertura; borrar trabajos rompe trazabilidad.

## 5. GET `/api/v1/trabajos`

| Campo                   | Contrato                                                                                                                              |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| Finalidad               | Tabla principal.                                                                                                                      |
| Query params            | `page`, `per_page`, filtros de seccion 12, `sort`.                                                                                    |
| Eager-load              | `empresa`, `estacion`, `tipoDocumento`, `tipoTrabajo`, `tarifario`, `responsableCiete`; agregados de pedidos/facturas/legalizaciones. |
| No devolver por defecto | Items de pedido, presupuestos completos, legalizaciones completas.                                                                    |
| Success                 | 200 con `TrabajoTableItem[]`.                                                                                                         |

Ejemplo:

```json
{
    "success": true,
    "message": "Listado de trabajos obtenido",
    "data": [
        {
            "id_trabajo": 1,
            "numero_trabajo": 1001,
            "numero_estacion": "MOEVE-EST-001",
            "zona": "SUR",
            "descripcion_trabajo": "NPV Estacion MOEVE Sevilla — nueva propuesta de valor",
            "id_contexto": 1,
            "cliente": {
                "id_empresa": 1,
                "nombre": "MOEVE",
                "codigo_contexto": "MOEVE"
            },
            "estacion": {
                "id_estacion_servicio": 1,
                "codigo_estacion": "MOEVE-EST-001",
                "nombre": "Estacion MOEVE Demo 01"
            },
            "tipo_documento": {
                "id_tipo_documento": 1,
                "codigo": "CONTROL_TRABAJOS",
                "nombre": "Control de Trabajos Moeve"
            },
            "tipo_trabajo": {
                "id_tipo_trabajo": 1,
                "codigo": "NPV",
                "nombre": "Nueva Propuesta de Valor"
            },
            "numero_aviso": null,
            "estado": "en_curso",
            "cerrado": false,
            "bloqueado_cierre": false,
            "fecha_encargo": "2026-02-01",
            "fecha_terminacion": null,
            "responsable_ciete": null,
            "responsable_cliente": "Carlos Martinez",
            "pedido_resumen": { "total": 1, "importe_total": "950.00" },
            "factura_resumen": { "total": 1, "importe_total": "1149.50" },
            "legalizaciones_count": 1,
            "can": {
                "view": true,
                "update": true,
                "close": false,
                "reopen": false
            }
        }
    ],
    "meta": {
        "timestamp": "2026-04-17T10:00:00+02:00",
        "pagination": {
            "total": 4,
            "count": 4,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 1
        }
    }
}
```

## 6. GET `/api/v1/trabajos/{trabajo}`

| Campo        | Contrato                                                      |
| ------------ | ------------------------------------------------------------- |
| Finalidad    | Detalle/edit.                                                 |
| Query params | `include=pedidos.items,facturas,legalizaciones,presupuestos`. |
| Success      | 200 con `TrabajoDetail`.                                      |
| Errores      | 401, 403, 404.                                                |

Ejemplo:

```json
{
    "success": true,
    "message": "Detalle de trabajo obtenido",
    "data": {
        "id_trabajo": 1,
        "id_contexto": 1,
        "id_empresa_cliente": 1,
        "id_estacion_servicio": 1,
        "id_tipo_documento": 1,
        "id_tipo_trabajo": 1,
        "id_contrato": 1,
        "id_tarifario": 1,
        "id_responsable_ciete": null,
        "id_usuario_cierre": null,
        "numero_trabajo": 1001,
        "numero_estacion": "MOEVE-EST-001",
        "zona": "SUR",
        "descripcion_trabajo": "NPV Estacion MOEVE Sevilla — nueva propuesta de valor",
        "fecha_encargo": "2026-02-01",
        "fecha_terminacion": null,
        "observaciones": null,
        "numero_aviso": null,
        "orden_mantenimiento": null,
        "categoria": null,
        "responsable_cliente": "Carlos Martinez",
        "estado": "en_curso",
        "cerrado": false,
        "bloqueado_cierre": false,
        "fecha_cierre": null,
        "empresa": { "id_empresa": 1, "nombre": "MOEVE" },
        "estacion": {
            "id_estacion_servicio": 1,
            "codigo_estacion": "MOEVE-EST-001",
            "nombre": "Estacion MOEVE Demo 01"
        },
        "tipo_documento": {
            "id_tipo_documento": 1,
            "codigo": "CONTROL_TRABAJOS",
            "nombre": "Control de Trabajos Moeve"
        },
        "tipo_trabajo": {
            "id_tipo_trabajo": 1,
            "codigo": "NPV",
            "nombre": "Nueva Propuesta de Valor"
        },
        "contrato": {
            "id_contrato": 1,
            "codigo_contrato": "CTR-MOEVE-2026",
            "nombre": "Contrato Marco MOEVE 2026"
        },
        "tarifario": {
            "id_tarifario": 1,
            "nombre": "Tarifario Base MOEVE",
            "version": "2026.1"
        },
        "pedidos": [
            {
                "id_pedido": 1,
                "numero_pedido": "PED-M-001",
                "estado": "facturado",
                "fecha_solicitud": "2026-02-05",
                "fecha_recepcion": "2026-02-10",
                "importe_pedido": "950.00",
                "importe_solicitado": "950.00",
                "importe_facturado": "950.00",
                "unidades_pedido": "10.000",
                "pedido_completo": true,
                "facturado_completo": true,
                "items": [
                    {
                        "id_pedido_item": 1,
                        "id_tarifario_linea": 1,
                        "codigo_servicio": "T001",
                        "numero_tarifa": null,
                        "descripcion_servicio": "Inspeccion tecnica inicial",
                        "precio_unitario": "95.00",
                        "cantidad": "10.000",
                        "total_linea": "950.00"
                    }
                ]
            }
        ],
        "facturas": [
            {
                "id_factura": 1,
                "numero_factura": "F-2026-001",
                "numero_factura_ccp": null,
                "serie": "M",
                "orden_factura": 1,
                "fecha_solicitud": "2026-02-15",
                "fecha_emision": "2026-02-20",
                "fecha_vencimiento": "2026-04-20",
                "importe": "950.00",
                "base_imponible": "950.00",
                "iva": "199.50",
                "retencion": null,
                "total": "1149.50",
                "estado": "emitida",
                "autofactura": false,
                "sociedad": null
            }
        ],
        "legalizaciones": [
            {
                "id_legalizacion": 1,
                "tipo_legalizacion": "Licencia de apertura",
                "numero_expediente": "EXP-M-001",
                "organismo": "Ayuntamiento de Sevilla",
                "estado": "en_tramite"
            }
        ],
        "presupuestos": [
            {
                "id_presupuesto": 1,
                "codigo_presupuesto": "PRES-M-001",
                "nombre_presupuesto": "Presupuesto NPV Moeve Sevilla",
                "estado": "aprobado",
                "total": "1379.40"
            }
        ],
        "can": { "view": true, "update": true, "close": false, "reopen": false }
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

## 7. POST `/api/v1/trabajos`

Body minimo:

```json
{
    "id_empresa_cliente": 1,
    "id_estacion_servicio": 1,
    "id_tipo_documento": 1,
    "id_tipo_trabajo": 1,
    "id_contrato": 1,
    "id_tarifario": 1,
    "id_responsable_ciete": null,
    "numero_trabajo": 1003,
    "numero_estacion": "MOEVE-EST-001",
    "zona": "SUR",
    "descripcion_trabajo": "Reforma marquesina estacion MOEVE Sevilla",
    "fecha_encargo": "2026-04-17",
    "fecha_terminacion": null,
    "observaciones": null,
    "numero_aviso": null,
    "orden_mantenimiento": null,
    "categoria": null,
    "responsable_cliente": "Carlos Martinez",
    "estado": "borrador"
}
```

Success: 201 con `TrabajoDetail` compacto.  
Errores: 422 validacion, 409 duplicado `(id_contexto, id_tipo_documento, numero_trabajo)`.

## 8. PATCH `/api/v1/trabajos/{trabajo}`

Body parcial:

```json
{
    "estado": "terminado",
    "fecha_terminacion": "2026-04-20",
    "observaciones": "Trabajo validado por responsable"
}
```

Reglas:

| Caso                        | Resultado                            |
| --------------------------- | ------------------------------------ |
| Trabajo abierto             | Requiere `trabajos.editar`.          |
| Trabajo cerrado             | Requiere `trabajos_cerrados.editar`. |
| Cierre                      | Usar `/cerrar`, no PATCH generico.   |
| Cambio FK fuera de contexto | 422.                                 |

## 9. Cierre y reapertura

POST `/api/v1/trabajos/{trabajo}/cerrar`

```json
{
    "motivo": "Pedidos y facturas revisados",
    "fecha_cierre": "2026-04-17T10:00:00"
}
```

Efecto:

- `cerrado=true`
- `estado='cerrado'`
- `id_usuario_cierre=auth.id_usuario`
- `fecha_cierre` informada (datetime)

POST `/api/v1/trabajos/{trabajo}/reabrir`

```json
{
    "motivo": "Correccion de factura pendiente"
}
```

Efecto:

- `cerrado=false`
- `fecha_cierre=null`
- `id_usuario_cierre=null`
- `estado='en_curso'`

## 10. Importacion

Las tablas `importaciones` e `importacion_filas` existen en BD y soportan el flujo de staging.

POST `/api/v1/importaciones/trabajos`

```json
{
    "tipo": "trabajos",
    "archivo": "<multipart-file>"
}
```

El contexto se obtiene server-side del usuario autenticado.

Preview:

```json
{
    "success": true,
    "message": "Archivo leido y validado",
    "data": {
        "id_importacion": 1,
        "tipo": "trabajos",
        "estado": "validado",
        "total_filas": 2775,
        "filas_importadas": 0,
        "filas_con_error": 44,
        "filas_duplicadas": 130,
        "filas": [
            {
                "id_importacion_fila": 18,
                "numero_fila": 18,
                "estado": "error",
                "datos_json": {
                    "numero_trabajo": 278,
                    "numero_estacion": "12345",
                    "numero_pedido": "4500123456"
                },
                "mensaje_error": "No se encontro estacion con codigo 12345 en el contexto activo."
            }
        ]
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

Confirm:

```json
{
    "success": true,
    "message": "Importacion confirmada",
    "data": {
        "id_importacion": 1,
        "estado": "completado",
        "total_filas": 2775,
        "filas_importadas": 2601,
        "filas_con_error": 44,
        "filas_duplicadas": 130
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

## 11. Validaciones por campo

### Campos de `trabajos`

| Campo                  | Tipo BD               | Regla                                                                                   |
| ---------------------- | --------------------- | --------------------------------------------------------------------------------------- |
| `id_empresa_cliente`   | FK bigint             | Requerido. Debe existir en `empresas` y mismo `id_contexto`.                            |
| `id_estacion_servicio` | FK bigint nullable    | Debe existir en `estaciones_servicio` del mismo contexto.                               |
| `id_tipo_documento`    | FK bigint nullable    | Debe existir en `tipos_documento` del mismo contexto.                                   |
| `id_tipo_trabajo`      | FK bigint nullable    | Debe existir en `tipos_trabajo` del mismo contexto y coincidir con `id_tipo_documento`. |
| `id_contrato`          | FK bigint nullable    | Debe existir en `contratos` del mismo contexto.                                         |
| `id_tarifario`         | FK bigint nullable    | Debe existir en `tarifarios` del mismo contexto.                                        |
| `id_responsable_ciete` | FK bigint nullable    | Debe existir en `usuarios`.                                                             |
| `numero_trabajo`       | unsigned int          | Requerido. Unico por `(id_contexto, id_tipo_documento)`.                                |
| `numero_estacion`      | varchar(30) nullable  | Codigo de estacion textual.                                                             |
| `zona`                 | varchar(10) nullable  | Zona operativa.                                                                         |
| `descripcion_trabajo`  | text nullable         | Texto libre.                                                                            |
| `fecha_encargo`        | date nullable         | Fecha de encargo.                                                                       |
| `fecha_terminacion`    | date nullable         | `fecha_terminacion >= fecha_encargo` si ambas existen.                                  |
| `observaciones`        | text nullable         | Texto libre.                                                                            |
| `numero_aviso`         | varchar(100) nullable | Especifico Repsol. No numerico estricto.                                                |
| `orden_mantenimiento`  | varchar(100) nullable | Especifico Repsol MTO.                                                                  |
| `categoria`            | varchar(100) nullable | Especifico Moeve.                                                                       |
| `responsable_cliente`  | varchar(150) nullable | Nombre textual del responsable del cliente.                                             |
| `estado`               | enum                  | `borrador`, `en_curso`, `terminado`, `cerrado`, `cancelado`.                            |
| `cerrado`              | bool                  | Si true, exige `id_usuario_cierre` y `fecha_cierre`.                                    |
| `bloqueado_cierre`     | bool                  | Lo marca validacion de negocio; impide cierre normal.                                   |
| `fecha_cierre`         | datetime nullable     | Fecha/hora de cierre.                                                                   |

### Validaciones relacionadas

| Recurso        | Regla                                                                                |
| -------------- | ------------------------------------------------------------------------------------ |
| Pedido         | `numero_pedido` requerido. `id_trabajo` requerido.                                   |
| Pedido item    | `cantidad > 0`, `precio_unitario >= 0`, `total_linea >= 0`.                          |
| Factura        | `numero_factura` nullable. `id_trabajo` y `id_empresa_cliente` requeridos.           |
| Factura-pedido | Pivot `factura_pedidos`: `id_factura` + `id_pedido` unique.                          |
| Legalizacion   | `tipo_legalizacion` requerido. `numero_expediente` unico por contexto si no es null. |
| Presupuesto    | `codigo_presupuesto` unico por contexto. `id_trabajo` requerido.                     |

## 12. Filtros y rendimiento

Query params soportados:

| Param                           | Campo/relacion                                                                                                     |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `q`                             | `numero_trabajo`, `descripcion_trabajo`, `numero_aviso`, `numero_estacion`, estacion nombre/codigo, pedido numero. |
| `id_empresa_cliente`            | `trabajos.id_empresa_cliente` dentro de contexto activo.                                                           |
| `id_estacion_servicio`          | `trabajos.id_estacion_servicio`.                                                                                   |
| `id_tipo_documento`             | `trabajos.id_tipo_documento`.                                                                                      |
| `id_tipo_trabajo`               | `trabajos.id_tipo_trabajo`.                                                                                        |
| `id_responsable_ciete`          | `trabajos.id_responsable_ciete`.                                                                                   |
| `estado`                        | `trabajos.estado`.                                                                                                 |
| `cerrado`                       | `trabajos.cerrado`.                                                                                                |
| `bloqueado_cierre`              | `trabajos.bloqueado_cierre`.                                                                                       |
| `zona`                          | `trabajos.zona`.                                                                                                   |
| `fecha_encargo_desde/hasta`     | `trabajos.fecha_encargo`.                                                                                          |
| `fecha_terminacion_desde/hasta` | `trabajos.fecha_terminacion`.                                                                                      |
| `numero_aviso`                  | `trabajos.numero_aviso`.                                                                                           |
| `numero_pedido`                 | `pedidos.numero_pedido`.                                                                                           |
| `numero_factura`                | `facturas.numero_factura`.                                                                                         |
| `id_tarifario`                  | `trabajos.id_tarifario`.                                                                                           |
| `id_contrato`                   | `trabajos.id_contrato`.                                                                                            |

Indices existentes:

| Tabla            | Indices                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `trabajos`       | `idx_trabajos_contexto`, `idx_trabajos_id_contexto (id_trabajo,id_contexto)`, `idx_trabajos_empresa_contexto (id_empresa_cliente,id_contexto)`, `idx_trabajos_estacion_contexto (id_estacion_servicio,id_contexto)`, `idx_trabajos_tipo_doc_contexto (id_tipo_documento,id_contexto)`, `idx_trabajos_tipo_trab_contexto (id_tipo_trabajo,id_contexto)`, `idx_trabajos_contrato_contexto (id_contrato,id_contexto)`, `idx_trabajos_tarifario_contexto (id_tarifario,id_contexto)`, `idx_trabajos_estado_contexto (estado,id_contexto)`, `idx_trabajos_responsable (id_responsable_ciete)`, `idx_trabajos_fecha_encargo_contexto (fecha_encargo,id_contexto)`, `uq_trabajos_ctx_tipodoc_numero (id_contexto,id_tipo_documento,numero_trabajo)` unique |
| `pedidos`        | `idx_pedidos_contexto`, `idx_pedidos_id_contexto (id_pedido,id_contexto)`, `idx_pedidos_trabajo_contexto (id_trabajo,id_contexto)`, `idx_pedidos_numero_contexto (numero_pedido,id_contexto)`, `idx_pedidos_estado_contexto (estado,id_contexto)`, `idx_pedidos_tarifario_contexto (id_tarifario,id_contexto)`                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| `facturas`       | `idx_facturas_contexto`, `idx_facturas_id_contexto (id_factura,id_contexto)`, `idx_facturas_trabajo_contexto (id_trabajo,id_contexto)`, `idx_facturas_empresa_contexto (id_empresa_cliente,id_contexto)`, `idx_facturas_numero_contexto (numero_factura,id_contexto)`, `idx_facturas_estado_contexto (estado,id_contexto)`, `idx_facturas_fecha_contexto (fecha_emision,id_contexto)`                                                                                                                                                                                                                                                                                                                                                               |
| `legalizaciones` | `idx_legalizaciones_contexto`, `idx_legalizaciones_trabajo_contexto (id_trabajo,id_contexto)`, `uq_legalizaciones_contexto_num_expediente (id_contexto,numero_expediente)` unique                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| `presupuestos`   | `idx_presupuestos_trabajo_contexto (id_trabajo,id_contexto)`, `uq_presupuestos_contexto_codigo (id_contexto,codigo_presupuesto)` unique                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |

Indices recomendados a futuro si el volumen crece:

| Tabla      | Indice                           |
| ---------- | -------------------------------- |
| `trabajos` | `(id_contexto, estado, cerrado)` |
| `trabajos` | `(id_contexto, numero_aviso)`    |

Paginacion:

- Default `per_page=20`, max `100`.
- Listado usa offset pagination.
- Import preview usa paginacion por fila (`page/per_page`) y filtros `estado=error|valido|duplicado|pendiente`.

## 13. Estados y transiciones

`estado` (campo real en BD):

| Estado      | Uso                                                 |
| ----------- | --------------------------------------------------- |
| `borrador`  | Creado/importado sin validacion operativa completa. |
| `en_curso`  | Trabajo activo.                                     |
| `terminado` | Ejecucion finalizada, pendiente cierre/facturacion. |
| `cerrado`   | Cerrado funcionalmente.                             |
| `cancelado` | Baja logica.                                        |

Nota: no existe estado `pausado` en el enum de BD.

Transiciones:

| De                           | A           | Permiso                                          | Restriccion                      |
| ---------------------------- | ----------- | ------------------------------------------------ | -------------------------------- |
| `borrador`                   | `en_curso`  | `trabajos.editar`                                | Datos minimos completos.         |
| `en_curso`                   | `terminado` | `trabajos.editar`                                | `fecha_terminacion` recomendada. |
| `terminado`                  | `cerrado`   | `trabajos.cerrar`                                | No bloqueado y cierre coherente. |
| `cerrado`                    | `en_curso`  | `trabajos.reabrir` o `trabajos_cerrados.reabrir` | Motivo requerido.                |
| Cualquiera excepto `cerrado` | `cancelado` | `trabajos.editar`                                | Motivo requerido; no borrar.     |

Trabajo cerrado:

- No editable con permiso normal.
- Requiere `trabajos_cerrados.editar`.
- Debe conservar `id_usuario_cierre` y `fecha_cierre`.
- Todo cambio posterior debe quedar registrado en `audit_log`.

## 14. Errores

Validation error 422:

```json
{
    "success": false,
    "message": "Datos invalidos",
    "error_code": "VALIDATION_ERROR",
    "errors": {
        "numero_trabajo": [
            "Ya existe un trabajo con este numero para el tipo de documento en el contexto activo."
        ],
        "id_estacion_servicio": ["La estacion no pertenece al contexto activo."]
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

Not found 404:

```json
{
    "success": false,
    "message": "Trabajo no encontrado",
    "error_code": "NOT_FOUND",
    "errors": {},
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

Forbidden 403:

```json
{
    "success": false,
    "message": "No tienes permiso para editar trabajos cerrados",
    "error_code": "FORBIDDEN",
    "errors": {},
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

Conflict 409:

```json
{
    "success": false,
    "message": "Conflicto de datos",
    "error_code": "CONFLICT",
    "errors": {
        "numero_trabajo": [
            "El registro fue modificado por otra importacion o usuario."
        ]
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

Import error:

```json
{
    "success": false,
    "message": "La importacion tiene errores bloqueantes",
    "error_code": "IMPORT_VALIDATION_ERROR",
    "errors": {
        "fila_18.id_estacion_servicio": [
            "No se encontro estacion con codigo 12345 en el contexto."
        ],
        "fila_25.numero_factura": [
            "numero_factura es obligatorio para facturas emitidas."
        ]
    },
    "meta": { "timestamp": "2026-04-17T10:00:00+02:00" }
}
```

## 15. Contrato para FRONT

Tabla `TrabajoTableItem`:

```json
{
    "id_trabajo": 1,
    "numero_trabajo": 1001,
    "numero_estacion": "MOEVE-EST-001",
    "zona": "SUR",
    "descripcion_trabajo": "NPV Estacion MOEVE Sevilla — nueva propuesta de valor",
    "cliente": {
        "id_empresa": 1,
        "nombre": "MOEVE",
        "codigo_contexto": "MOEVE"
    },
    "estacion": {
        "id_estacion_servicio": 1,
        "codigo_estacion": "MOEVE-EST-001",
        "nombre": "Estacion MOEVE Demo 01"
    },
    "tipo_documento": {
        "id_tipo_documento": 1,
        "codigo": "CONTROL_TRABAJOS",
        "nombre": "Control de Trabajos Moeve"
    },
    "tipo_trabajo": {
        "id_tipo_trabajo": 1,
        "codigo": "NPV",
        "nombre": "Nueva Propuesta de Valor"
    },
    "numero_aviso": null,
    "estado": "en_curso",
    "cerrado": false,
    "bloqueado_cierre": false,
    "fecha_encargo": "2026-02-01",
    "fecha_terminacion": null,
    "responsable_cliente": "Carlos Martinez",
    "pedido_resumen": { "total": 1, "importe_total": "950.00" },
    "factura_resumen": { "total": 1, "importe_total": "1149.50" },
    "legalizaciones_count": 1,
    "can": { "view": true, "update": true, "close": false, "reopen": false }
}
```

Formulario create/edit:

```json
{
    "id_empresa_cliente": null,
    "id_estacion_servicio": null,
    "id_tipo_documento": null,
    "id_tipo_trabajo": null,
    "id_contrato": null,
    "id_tarifario": null,
    "id_responsable_ciete": null,
    "numero_trabajo": null,
    "numero_estacion": "",
    "zona": "",
    "descripcion_trabajo": "",
    "fecha_encargo": null,
    "fecha_terminacion": null,
    "observaciones": "",
    "numero_aviso": "",
    "orden_mantenimiento": "",
    "categoria": "",
    "responsable_cliente": "",
    "estado": "borrador"
}
```

Catalogos necesarios:

| Catalogo                    | Fuente                                                                       |
| --------------------------- | ---------------------------------------------------------------------------- |
| Empresas cliente            | `empresas` filtradas por contexto activo del usuario                         |
| Estaciones                  | `estaciones_servicio` por contexto                                           |
| Tipos documento             | `tipos_documento` por contexto                                               |
| Tipos trabajo               | `tipos_trabajo` por contexto, filtrados por `id_tipo_documento` seleccionado |
| Contratos                   | `contratos` por contexto                                                     |
| Tarifarios                  | `tarifarios` por contexto                                                    |
| Usuarios responsables Ciete | `usuarios` accesibles                                                        |
| Estados                     | Enums de este contrato                                                       |
| Permisos `can`              | Calculados server-side                                                       |

Hooks/cache recomendados:

| Hook                      | Key                                        |
| ------------------------- | ------------------------------------------ |
| `useTrabajos(filters)`    | `['trabajos', contextoActivo, filters]`    |
| `useTrabajo(id, include)` | `['trabajo', contextoActivo, id, include]` |
| `useTrabajoCatalogos()`   | `['trabajos-catalogos', contextoActivo]`   |
| `useImportacion(id)`      | `['importacion', contextoActivo, id]`      |

Nombres estables:

- Usar `id_trabajo` como id real (PK de la tabla `trabajos`).
- Usar `numero_trabajo` como identificador numerico humano dentro de tipo_documento+contexto.
- El label de UI es "Trabajo"; el contrato de datos tambien usa `trabajo`.

## 16. Esquema completo de tablas del modulo

### `trabajos`

| Columna                | Tipo            | Nullable | Default        | Notas                                                              |
| ---------------------- | --------------- | -------- | -------------- | ------------------------------------------------------------------ |
| `id_trabajo`           | bigint unsigned | NO       | AUTO_INCREMENT | PK                                                                 |
| `id_contexto`          | bigint unsigned | NO       | —              | FK → `contextos_cliente`                                           |
| `id_empresa_cliente`   | bigint unsigned | NO       | —              | FK compuesta `(id_empresa_cliente, id_contexto)` → `empresas`      |
| `id_estacion_servicio` | bigint unsigned | SI       | NULL           | FK compuesta → `estaciones_servicio`                               |
| `id_tipo_documento`    | bigint unsigned | SI       | NULL           | FK compuesta → `tipos_documento`                                   |
| `id_tipo_trabajo`      | bigint unsigned | SI       | NULL           | FK compuesta → `tipos_trabajo`                                     |
| `id_contrato`          | bigint unsigned | SI       | NULL           | FK compuesta → `contratos`                                         |
| `id_tarifario`         | bigint unsigned | SI       | NULL           | FK compuesta → `tarifarios`                                        |
| `id_responsable_ciete` | bigint unsigned | SI       | NULL           | FK simple → `usuarios.id_usuario`                                  |
| `id_usuario_cierre`    | bigint unsigned | SI       | NULL           | FK simple → `usuarios.id_usuario`                                  |
| `numero_trabajo`       | unsigned int    | NO       | —              | Parte de unique `(id_contexto, id_tipo_documento, numero_trabajo)` |
| `numero_estacion`      | varchar(30)     | SI       | NULL           |                                                                    |
| `zona`                 | varchar(10)     | SI       | NULL           |                                                                    |
| `descripcion_trabajo`  | text            | SI       | NULL           |                                                                    |
| `fecha_encargo`        | date            | SI       | NULL           |                                                                    |
| `fecha_terminacion`    | date            | SI       | NULL           |                                                                    |
| `observaciones`        | text            | SI       | NULL           |                                                                    |
| `numero_aviso`         | varchar(100)    | SI       | NULL           | Repsol                                                             |
| `orden_mantenimiento`  | varchar(100)    | SI       | NULL           | Repsol MTO                                                         |
| `categoria`            | varchar(100)    | SI       | NULL           | Moeve                                                              |
| `responsable_cliente`  | varchar(150)    | SI       | NULL           |                                                                    |
| `estado`               | enum            | NO       | 'borrador'     | borrador, en_curso, terminado, cerrado, cancelado                  |
| `cerrado`              | tinyint(1)      | NO       | 0              |                                                                    |
| `bloqueado_cierre`     | tinyint(1)      | NO       | 0              |                                                                    |
| `fecha_cierre`         | datetime        | SI       | NULL           |                                                                    |
| `created_at`           | timestamp       | SI       | NULL           |                                                                    |
| `updated_at`           | timestamp       | SI       | NULL           |                                                                    |

### `pedidos`

| Columna                | Tipo            | Nullable | Default        | Notas                                                                                         |
| ---------------------- | --------------- | -------- | -------------- | --------------------------------------------------------------------------------------------- |
| `id_pedido`            | bigint unsigned | NO       | AUTO_INCREMENT | PK                                                                                            |
| `id_contexto`          | bigint unsigned | NO       | —              | FK → `contextos_cliente`                                                                      |
| `id_trabajo`           | bigint unsigned | NO       | —              | FK compuesta `(id_trabajo, id_contexto)` → `trabajos`                                         |
| `id_tarifario`         | bigint unsigned | SI       | NULL           | FK compuesta → `tarifarios`                                                                   |
| `numero_pedido`        | varchar(100)    | NO       | —              |                                                                                               |
| `fecha_solicitud`      | date            | SI       | NULL           |                                                                                               |
| `fecha_recepcion`      | date            | SI       | NULL           |                                                                                               |
| `importe_pedido`       | decimal(14,2)   | NO       | 0.00           |                                                                                               |
| `importe_solicitado`   | decimal(14,2)   | SI       | NULL           |                                                                                               |
| `importe_facturado`    | decimal(14,2)   | SI       | NULL           |                                                                                               |
| `unidades_pedido`      | decimal(14,3)   | NO       | 1.000          |                                                                                               |
| `unidades_solicitadas` | decimal(14,3)   | SI       | NULL           |                                                                                               |
| `estado`               | enum            | NO       | 'pendiente'    | pendiente, solicitado, recibido, en_ejecucion, facturado_parcial, facturado, cerrado, anulado |
| `pedido_completo`      | tinyint(1)      | SI       | NULL           |                                                                                               |
| `tiene_mas_de_1_item`  | tinyint(1)      | SI       | NULL           |                                                                                               |
| `facturado_completo`   | tinyint(1)      | SI       | NULL           |                                                                                               |
| `observaciones`        | text            | SI       | NULL           |                                                                                               |

### `pedido_items`

| Columna                | Tipo            | Nullable | Default        | Notas                                               |
| ---------------------- | --------------- | -------- | -------------- | --------------------------------------------------- |
| `id_pedido_item`       | bigint unsigned | NO       | AUTO_INCREMENT | PK                                                  |
| `id_contexto`          | bigint unsigned | NO       | —              | FK → `contextos_cliente`                            |
| `id_pedido`            | bigint unsigned | NO       | —              | FK compuesta `(id_pedido, id_contexto)` → `pedidos` |
| `id_tarifario_linea`   | bigint unsigned | SI       | NULL           | FK compuesta → `tarifario_lineas`                   |
| `codigo_servicio`      | varchar(30)     | SI       | NULL           |                                                     |
| `numero_tarifa`        | varchar(30)     | SI       | NULL           |                                                     |
| `descripcion_servicio` | varchar(255)    | SI       | NULL           |                                                     |
| `precio_unitario`      | decimal(14,2)   | NO       | 0.00           |                                                     |
| `cantidad`             | decimal(14,3)   | NO       | 1.000          |                                                     |
| `total_linea`          | decimal(14,2)   | NO       | 0.00           |                                                     |

### `facturas`

| Columna              | Tipo             | Nullable | Default        | Notas                                                                               |
| -------------------- | ---------------- | -------- | -------------- | ----------------------------------------------------------------------------------- |
| `id_factura`         | bigint unsigned  | NO       | AUTO_INCREMENT | PK                                                                                  |
| `id_contexto`        | bigint unsigned  | NO       | —              | FK → `contextos_cliente`                                                            |
| `id_trabajo`         | bigint unsigned  | NO       | —              | FK compuesta `(id_trabajo, id_contexto)` → `trabajos`                               |
| `id_empresa_cliente` | bigint unsigned  | NO       | —              | FK compuesta → `empresas`                                                           |
| `numero_factura`     | varchar(100)     | SI       | NULL           |                                                                                     |
| `numero_factura_ccp` | varchar(100)     | SI       | NULL           | Segunda factura (Repsol doble factura)                                              |
| `serie`              | varchar(20)      | SI       | NULL           |                                                                                     |
| `orden_factura`      | tinyint unsigned | NO       | 1              | 1=primera, 2=segunda factura                                                        |
| `fecha_solicitud`    | date             | SI       | NULL           |                                                                                     |
| `fecha_emision`      | date             | SI       | NULL           |                                                                                     |
| `fecha_vencimiento`  | date             | SI       | NULL           |                                                                                     |
| `importe`            | decimal(14,2)    | NO       | 0.00           |                                                                                     |
| `base_imponible`     | decimal(14,2)    | SI       | NULL           |                                                                                     |
| `iva`                | decimal(14,2)    | SI       | NULL           |                                                                                     |
| `retencion`          | decimal(14,2)    | SI       | NULL           |                                                                                     |
| `total`              | decimal(14,2)    | SI       | NULL           |                                                                                     |
| `estado`             | enum             | NO       | 'pendiente'    | pendiente, solicitada, emitida, enviada, cobrada_parcial, cobrada, vencida, anulada |
| `autofactura`        | tinyint(1)       | NO       | 0              |                                                                                     |
| `sociedad`           | varchar(180)     | SI       | NULL           | Moeve                                                                               |
| `observaciones`      | text             | SI       | NULL           |                                                                                     |

### `factura_pedidos` (pivot)

| Columna             | Tipo            | Nullable | Default        | Notas                                                      |
| ------------------- | --------------- | -------- | -------------- | ---------------------------------------------------------- |
| `id_factura_pedido` | bigint unsigned | NO       | AUTO_INCREMENT | PK                                                         |
| `id_factura`        | bigint unsigned | NO       | —              | FK → `facturas`, parte de unique `(id_factura, id_pedido)` |
| `id_pedido`         | bigint unsigned | NO       | —              | FK → `pedidos`                                             |
| `importe_aplicado`  | decimal(14,2)   | SI       | NULL           |                                                            |

### `cobros`

| Columna               | Tipo            | Nullable | Default         | Notas                                                 |
| --------------------- | --------------- | -------- | --------------- | ----------------------------------------------------- |
| `id_cobro`            | bigint unsigned | NO       | AUTO_INCREMENT  | PK                                                    |
| `id_contexto`         | bigint unsigned | NO       | —               | FK → `contextos_cliente`                              |
| `id_factura`          | bigint unsigned | NO       | —               | FK compuesta `(id_factura, id_contexto)` → `facturas` |
| `id_usuario_registro` | bigint unsigned | SI       | NULL            | FK → `usuarios`                                       |
| `fecha_cobro`         | date            | NO       | —               |                                                       |
| `importe`             | decimal(14,2)   | NO       | —               |                                                       |
| `metodo_cobro`        | enum            | NO       | 'transferencia' | transferencia, giro, efectivo, confirming, otro       |
| `referencia`          | varchar(120)    | SI       | NULL            |                                                       |
| `estado`              | enum            | NO       | 'pendiente'     | pendiente, recibido, conciliado, devuelto             |
| `observaciones`       | text            | SI       | NULL            |                                                       |

## 17. Pendientes tecnicos antes de implementar

| Pendiente                                                                                 | Bloquea                                    |
| ----------------------------------------------------------------------------------------- | ------------------------------------------ |
| Crear `TrabajoController` con endpoints CRUD, cierre/reapertura                           | Toda la API de trabajos                    |
| Crear `TrabajoRequest` (store/update) con validaciones                                    | Validacion server-side                     |
| Crear `TrabajoResource` y `TrabajoCollection`                                             | Formato de respuesta                       |
| Registrar rutas en `routes/api.php`                                                       | Acceso a endpoints                         |
| Implementar filtros y paginacion en el listado                                            | Tabla principal del front                  |
| Implementar logica de importacion (upload, validacion, confirmacion)                      | Modulo de importacion                      |
| Decidir si `numero_pedido` debe ser unique por contexto o por `(id_contexto, id_trabajo)` | Import con pedidos repetidos cross-trabajo |
| Definir si se necesita tabla `facturas_lineas` para desglose de factura                   | Facturacion detallada                      |
| Implementar escritura en `audit_log` en cambios de estado y edicion                       | Trazabilidad                               |
