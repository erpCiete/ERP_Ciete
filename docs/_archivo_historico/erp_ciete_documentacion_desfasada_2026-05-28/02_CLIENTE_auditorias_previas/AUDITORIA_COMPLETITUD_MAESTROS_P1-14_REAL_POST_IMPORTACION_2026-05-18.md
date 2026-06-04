# Auditoria de completitud de maestros P1-14 real post-importacion 2026-05-18

> **Documento vivo.**  
> Fuente funcional principal: `docs/02_CLIENTE/reunionCieteCompletaFormato.txt`  
> Traduccion operativa vigente: `docs/02_CLIENTE/AUDITORIA_ALINEACION_FUNCIONAL_ERP_CIETE_2026-05-17.md`  
> Backlog maestro vinculado: `docs/02_CLIENTE/tareasComparar.md`  
> Estado: bloqueada; la base real post-importacion P1-12 no pudo reconstruirse porque el dry-run no encontro los Excel reales en `--path=excelsactualizados`.

## 1. Entorno auditado

Comprobaciones previas ejecutadas antes de tocar la base local:

| Comprobacion | Resultado |
| ------------ | --------- |
| `php artisan env` | `local` |
| `APP_ENV` | `local` |
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | `abaco_ciete` |
| `php artisan migrate:status` | migraciones aplicadas |
| `git status --short` | worktree ya tenia cambios previos no relacionados |

La condicion de seguridad se cumplia: `APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_DATABASE=abaco_ciete`.

Antes del reset se creo backup SQL local de la base demo reducida:

- `database/backups/abaco_ciete_demo_reducida_pre_p1_14_real_20260518_002707.sql`

Nota tecnica: `mysqldump.exe` fallo desde WSL con `UtilBindVsockAnyPort`; el backup se genero por lectura via Laravel/PDO contra la base local.

## 2. Reconstruccion intentada

Procedimiento ejecutado:

1. `php artisan migrate:fresh --seed`
2. `php artisan ciete:import-excels-actualizados --path=excelsactualizados --dry-run`

El reset local/demo finalizo correctamente. El dry-run no persistio datos y devolvio error:

| Metrica | Valor |
| ------- | ----: |
| filas_leidas | 0 |
| filas_importadas | 0 |
| filas_ignoradas | 0 |
| filas_con_aviso | 0 |
| filas_con_error | 1 |
| archivos | 1 |

Error reportado:

```text
Error excelsactualizados :: - fila 0: No se encontraron Excel .xlsx para importar.
```

Por la regla de seguridad de la tarea, no se ejecuto `--commit`.

## 3. Conteos finales tras el bloqueo

Despues del reset y del dry-run fallido, la base local queda sembrada pero no post-importacion real:

| Dato | Conteo final |
| ---- | -----------: |
| Contextos | 3 |
| Trabajos | 0 |
| Pedidos | 0 |
| `pedido_items` | 0 |
| Facturas | 0 |
| `factura_items` | 0 |
| Importaciones registradas | 0 |
| Ultima importacion | no existe |

Estos conteos no coinciden con P1-12:

| Dato | P1-12 esperado |
| ---- | -------------: |
| Trabajos | 11.614 |
| Pedidos | 9.685 |
| `pedido_items` | 9.701 |
| Facturas | 2.733 |
| `factura_items` | 9.194 |
| Importaciones registradas | 11 |

## 4. Auditoria P1-14 real

No se repite la auditoria funcional de completitud de maestros sobre base real porque la base post-importacion P1-12 no quedo cargada.

No se deben usar los conteos de este intento como verdad de negocio. Tampoco sustituyen al informe P1-14 demo, que sigue siendo valido solo para la muestra local/demo reducida auditada anteriormente.

## 5. Dictamen

| Contexto | Dictamen |
| -------- | -------- |
| MOEVE | bloqueado por importacion no cargada |
| REPSOL | bloqueado por importacion no cargada |
| OTROS CLIENTES | bloqueado por importacion no cargada |
| TODOS | solo vista global; no es contexto de alta |

## 6. Comparacion contra P1-14 demo

- Se mantiene la conclusion metodologica: P1-14 demo no debe usarse como verdad final de negocio.
- Cambia la expectativa operativa: no se ha podido reemplazar la muestra demo por la base real P1-12 en esta ejecucion.
- Quedan invalidados como base final todos los conteos locales actuales posteriores al reset porque no contienen importacion real.
- El problema detectado no es un maestro incompleto, sino ausencia de fuente Excel real en la ruta usada por el comando documentado.

## 7. Accion requerida antes de P1-15

Restaurar o ubicar la fuente real de Excel actualizados para que el comando de importacion encuentre los `.xlsx`, o cargar una base local que ya corresponda exactamente a P1-12.

Hasta entonces, P1-15 queda bloqueada: no procede implementar diagnosticos accionables basados en una base que no contiene los datos reales post-importacion.
