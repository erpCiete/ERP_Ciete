# Fase B.1 - Importacion real desde Excel CIETE/MOEVE/REPSOL

> Ejecucion realizada el 2026-05-19 en entorno local. El nombre del documento conserva la fecha operativa solicitada para la fase.

## 1. Objetivo

Construir una base local real post-importacion en `abaco_ciete` a partir de los Excel reales de CIETE/MOEVE/REPSOL, usando el importador existente `ciete:import-excels-actualizados`.

Reglas aplicadas:

- No se tocaron produccion, GitHub, ramas, exportaciones ni Fase B.2.
- No se hizo commit.
- No se metieron datos a mano ni se inventaron datos.
- No se uso la muestra FD26 como verdad final.
- La muestra FD26/demo se limpio solo despues de backup y solo porque bloqueaba el dry-run real.

## 2. Entorno

| Comprobacion | Resultado |
| --- | --- |
| `php artisan env` | `local` |
| `APP_ENV` | `local` |
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | `abaco_ciete` |
| `DB_USERNAME` | `root` |
| `git status --short` | worktree ya estaba sucio antes de B.1; no se hizo reset/clean/checkout |
| `git diff --stat` | inventariado antes de tocar datos; sin commit |

## 3. Backup creado

| Dato | Valor |
| --- | --- |
| Ruta | `database/backups/abaco_ciete_pre_fase_b_import_real_20260519_032311.sql` |
| Tamano | 138.838 bytes (~136 KB) |
| Metodo | `mysqldump.exe` |
| Base | `abaco_ciete` local |

El backup se creo antes de ejecutar dry-run real, limpieza demo o importacion con `--commit`.

## 4. Excel localizados

Fuente real usada por Artisan: `C:/Users/kampe/Documents/Abaco/excelsactualizados`.

| Archivo | Tipo estimado | Tamano | Fecha mod. | Hojas detectadas | Fuente real | Importar | Motivo |
| --- | --- | ---: | --- | --- | --- | --- | --- |
| `Mapeo_Moeve_Repsol_Envolvente.xlsx` | OTROS / mapeo auxiliar | 10.927 B | 2026-04-10 05:42 | `Hoja1` | Si | No | Documento auxiliar sin contexto operativo claro. |
| `Moeve/01 Control de Trabajos Moeve.xlsx` | MOEVE / control trabajos + facturas | 1.249.004 B | 2026-04-07 10:52 | `Trabajos`, `Hoja2`, `FACTURAS EMITIDAS`, `Control`, `Hoja1` | Si | Si | Fuente operativa MOEVE. |
| `Moeve/02 Listado EESS España y Portugal 16-03-26.xlsx` | MOEVE / estaciones | 3.842.122 B | 2026-03-16 08:18 | `España 16-03-26`, `España 25-08-2025`, `España y Portugal 26-06-2024`, `Hoja1`, `Hoja3` | Si | Si | Fuente de estaciones MOEVE. |
| `Repsol/01 Control Trabajos DISEÑO REPSOL.xlsx` | REPSOL / control trabajos | 1.866.236 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `ALFONSO PCN`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/02 Control Trabajos EDIFICACIÓN.xlsx` | REPSOL / control trabajos | 2.040.637 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/03 Control Trabajos OBRAS REPSOL Z10.xlsx` | REPSOL / control trabajos | 4.070.224 B | 2026-04-07 14:07 | `AUTOFACTURACION REPSOL`, `OTROS`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/03 Control Trabajos OBRAS REPSOL Z50.xlsx` | REPSOL / control trabajos | 2.086.344 B | 2026-04-07 14:06 | `Rangos`, `AUTOFACTURACION`, `LISTADO EESS`, `TARIFA 23-27`, `Adjud. 2023-2027` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/05 Control Trabajos LICENCIAS REPSOL.xlsx` | REPSOL / control trabajos | 1.800.493 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/09 Control Trabajos FV REPSOL.xlsx` | REPSOL / control trabajos | 3.900.040 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/10 Control Trabajos ESTRUCTURAS Y VERTIDOS REPSOL.xlsx` | REPSOL / control trabajos | 1.802.591 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Hoja1`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/12 Control Trabajos MTO REPSOL.xlsx` | REPSOL / control trabajos | 1.885.354 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Rangos`, `LISTADO EESS`, `TARIFA 23-27` | Si | Si | Fuente operativa REPSOL. |
| `Repsol/13 Control Trabajos PUNTOS DE RECARGA.xlsx` | REPSOL / control trabajos | 1.892.712 B | 2026-04-07 14:06 | `AUTOFACTURACION`, `Rangos`, `TARIFA 23-27`, `LISTADO EESS` | Si | Si | Fuente operativa REPSOL. |

Material complementario usado por el importador:

- `docs/02_CLIENTE/materiales/Contrato 772 MOEVE - Tarifario.xlsx`, hoja `TARIFARIO`.

## 5. Revision del comando/importador

Comando usado:

```bash
/mnt/c/xampp/php/php.exe artisan ciete:import-excels-actualizados --path="C:/Users/kampe/Documents/Abaco/excelsactualizados" --dry-run
/mnt/c/xampp/php/php.exe artisan ciete:import-excels-actualizados --path="C:/Users/kampe/Documents/Abaco/excelsactualizados" --commit
```

Opciones confirmadas por `artisan help`:

- `--path`: carpeta o archivo Excel.
- `--context`: filtro MOEVE/REPSOL.
- `--file`: filtro por parte del nombre.
- `--limit`: limita archivos.
- `--dry-run`: lectura/mapeo sin persistencia final.
- `--commit`: persiste en base local/demo.

El importador registra `importaciones` solo para archivos operativos MOEVE/REPSOL; el mapeo auxiliar queda inventariado pero no genera importacion registrada.

## 6. Dry-run

Primer dry-run tras backup:

- Resultado: bloqueado por contaminacion FD26/demo.
- Error: clave unica `uq_empresas_contexto_cif`, duplicado `1-A28003119`.
- Causa: empresa demo `FD26 MOEVE CLIENTE` con CIF real de MOEVE `A28003119`.
- Accion: limpieza controlada de datos demo tras backup.

Dry-run final tras limpieza:

| Metrica | Valor |
| --- | ---: |
| Archivos inventariados | 12 |
| Importaciones operativas esperadas | 11 |
| Filas leidas | 53.055 |
| Filas importadas/actualizadas | 51.288 |
| Filas ignoradas | 1.767 |
| Filas con aviso | 3.726 |
| Filas con error | 0 |
| Hojas con columnas desconocidas | 36 |

Entidades previstas por dry-run:

| Entidad | Creadas/importadas |
| --- | ---: |
| Contextos | 0 |
| Empresas/clientes | 2 |
| Estaciones | 6.584 |
| Contratos | 4 |
| Tarifarios | 5 |
| Lineas tarifarias | 425 |
| Trabajos | 11.614 |
| Pedidos | 9.685 |
| `pedido_items` | 9.701 |
| Facturas | 2.733 |
| `factura_items` | 9.194 |
| `contrato_empresas_facturadoras` | 4 |

## 7. Limpieza previa FD26/demo

La base local contenia muestra FD26/demo antes de la carga real:

| Tabla | Antes | Despues | Borradas |
| --- | ---: | ---: | ---: |
| `factura_items` | 4 | 0 | 4 |
| `facturas` | 4 | 0 | 4 |
| `pedido_items` | 10 | 0 | 10 |
| `pedidos` | 9 | 0 | 9 |
| `contrato_empresas_facturadoras` | 4 | 0 | 4 |
| `trabajos` | 14 | 0 | 14 |
| `tarifario_lineas` | 8 | 0 | 8 |
| `tarifarios` | 5 | 0 | 5 |
| `contratos` | 5 | 0 | 5 |
| `estaciones_servicio` | 5 | 0 | 5 |
| `empresas` demo FD26 | 8 | 0 | 8 |

Preservado:

| Tabla | Conteo preservado |
| --- | ---: |
| `contextos_cliente` | 3 |
| `usuarios` | 6 |
| `roles` | 6 |
| `permisos` | 64 |

No se ejecuto `migrate:fresh`.

## 8. Importacion real

Resultado de `--commit`:

| Metrica | Valor |
| --- | ---: |
| Archivos inventariados | 12 |
| Importaciones registradas | 11 |
| Filas leidas | 53.055 |
| Filas importadas/actualizadas | 51.288 |
| Filas ignoradas | 1.767 |
| Filas con aviso | 3.726 |
| Filas con error | 0 |
| Hojas con columnas desconocidas | 36 |

Entidades persistidas por el importador:

| Entidad | Creadas/importadas |
| --- | ---: |
| Empresas/clientes | 2 |
| Estaciones | 6.584 |
| Contratos | 4 |
| Tarifarios | 5 |
| Lineas tarifarias | 425 |
| Trabajos | 11.614 |
| Pedidos | 9.685 |
| `pedido_items` | 9.701 |
| Facturas | 2.733 |
| `factura_items` | 9.194 |
| `contrato_empresas_facturadoras` | 4 |

## 9. Conteos finales

| Tabla | Conteo final |
| --- | ---: |
| `contextos_cliente` | 3 |
| `usuarios` | 6 |
| `empresas` | 2 |
| `estaciones_servicio` | 6.584 |
| `contratos` | 4 |
| `tarifarios` | 5 |
| `tarifario_lineas` | 425 |
| `trabajos` | 11.614 |
| `pedidos` | 9.685 |
| `pedido_items` | 9.701 |
| `facturas` | 2.733 |
| `factura_items` | 9.194 |
| `importaciones` | 11 |
| `importacion_filas` | 3.761 |

Por contexto:

| Entidad | MOEVE | REPSOL |
| --- | ---: | ---: |
| Estaciones | 3.237 | 3.347 |
| Trabajos | 6.685 | 4.929 |
| Pedidos | 6.147 | 3.538 |
| Facturas | 1.502 | 1.231 |

Ultima tanda de importaciones: IDs `12` a `22`, estado `completado`, `filas_con_error=0` en los 11 archivos operativos.

## 10. Comparacion contra P1-12

| Dato | P1-12 esperado | Final B.1 | Coincide |
| --- | ---: | ---: | --- |
| Trabajos | 11.614 | 11.614 | Si |
| Pedidos | 9.685 | 9.685 | Si |
| `pedido_items` | 9.701 | 9.701 | Si |
| Facturas | 2.733 | 2.733 | Si |
| `factura_items` | 9.194 | 9.194 | Si |
| Importaciones registradas | 11 | 11 | Si |

La base local queda reconciliada contra las cifras clave P1-12 indicadas para B.1.

## 11. Integridad basica

| Comprobacion | Resultado |
| --- | ---: |
| Trabajos sin contexto | 0 |
| Trabajos sin estacion | 1.356 |
| Pedidos sin trabajo | 0 |
| `pedido_items` sin pedido | 0 |
| Facturas sin items | 0 |
| Facturas sin trabajo | 0 |
| `factura_items` sin factura | 0 |
| `factura_items` sin `pedido_item` | 0 |
| Estaciones sin contexto | 0 |
| Contratos sin contexto | 0 |
| Contratos sin cliente | 0 |
| Tarifarios sin contrato | 0 |
| Lineas tarifarias sin tarifario | 0 |
| Codigos de estacion duplicados dentro del mismo contexto | 0 |
| Mismo codigo de estacion en contexto distinto | 29, permitido por regla B.1 |
| Pedidos con importes negativos | 2 |
| `pedido_items` con importes negativos | 2 |
| Facturas con importes negativos | 0 |
| `factura_items` con importes negativos | 0 |
| Fechas imposibles en trabajos | 6 |
| Fechas imposibles en pedidos | 4 |
| Fechas imposibles en facturas | 0 |
| Estados desconocidos en trabajos | 0 |
| Estados desconocidos en pedidos | 0 |
| Estados desconocidos en facturas | 0 |
| Contextos mezclados pedido/trabajo | 0 |
| Contextos mezclados factura/trabajo | 0 |
| Contextos mezclados factura/contrato | 0 |
| Contextos mezclados factura_items/pedido | 0 |

Detalle de incidencias no corregidas:

- `trabajos_sin_estacion`: 1.356 total, MOEVE 3 y REPSOL 1.353. Se reporta; no se enlaza por aproximacion.
- Importes negativos: MOEVE trabajo `1223`, pedido `400381870`, importe `-252.00`; MOEVE trabajo `1358`, pedido `400376007`, importe `-5115.00`.
- Fechas imposibles: ejemplos REPSOL con anos `0202`, `0205` y `2525` procedentes del Excel. Se reporta; no se corrige masivamente.
- Estados: todos los valores cargados son validos contra `Trabajo::ESTADOS_FUNCIONALES`, estados permitidos de pedidos y `Factura::ESTADOS_FUNCIONALES`.

## 12. Avisos y errores

Errores fatales: 0.

Principales avisos registrados en `importacion_filas`:

| Codigo | Clasificacion | Total |
| --- | --- | ---: |
| `historical_invoice_without_items` | `do_not_invent` | 1.183 |
| `work_amount_without_order` | `functional_decision` | 989 |
| `invoice_number_normalized` | `technical_improvement` | 969 |
| `tariff_line_unusable` | `acceptable` | 552 |
| `work_missing_number` | `acceptable` | 26 |
| `unknown_columns` | varias | 36 |
| `invoice_missing_number` | `acceptable` | 4 |
| `station_missing_code` | `acceptable` | 2 |

Interpretacion:

- Hay facturas historicas MOEVE sin items enlazables; no se inventa relacion.
- Hay trabajos con importe sin numero de pedido; se importa trabajo y se avisa sin crear `pedido_item`.
- Hay facturas sin numero explicito; se normaliza identificador interno para conciliacion local.
- Hay lineas tarifarias no interpretables y columnas auxiliares sin destino operativo claro.

## 13. Validacion tecnica minima

| Comando | Resultado |
| --- | --- |
| `php artisan test --filter=ImportacionesAccessTest` | PASS, 4 tests, 40 assertions |
| `php artisan test --filter=ClientesEstacionesApiTest` | PASS, 13 tests, 71 assertions |
| `php artisan test --filter=MaestrosTest` | PASS, 9 tests, 55 assertions |
| `php artisan test --filter=TrabajoTest` | PASS, 19 tests, 96 assertions |
| `php artisan test --filter=PedidoTest` | PASS, 12 tests, 47 assertions |
| `php artisan test --filter=FacturaTest` | PASS, 20 tests, 74 assertions |
| `npm run build` | PASS; warning no bloqueante de timing del plugin `laravel` |

Total tests minimos: 77 tests, 383 assertions, todos PASS.

## 14. Estado final

**B.1 completada.**

La importacion real local queda cargada y reconciliada contra P1-12. Hay incidencias de calidad de datos para revisar con CIETE o en B.2, pero no bloquean la construccion de la base local real porque no hay errores fatales, los conteos clave coinciden y no hay huerfanos ni mezcla de contextos.

Recomendacion:

- Pasar a Fase B.2 para validacion funcional completa sobre base real cargada.
- Llevar a revision de negocio los trabajos sin estacion, importes negativos, fechas imposibles y avisos de facturas historicas sin items.

Confirmacion:

- Base real cargada y lista para validacion funcional completa.
- No se toco produccion.
- No se hizo commit.
- No se tocaron exportaciones.
- No se ejecuto Fase B.2.
