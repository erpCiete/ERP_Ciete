# Bloque 5 - Estados automáticos, facturación y cierre secundario

## 1. Objetivo

Cerrar el Bloque 5 sin reabrir Bloques 2, 3 o 4:

- estados operativos derivados en `Trabajo`;
- sincronización real desde `Factura` hacia `Pedido` y `Trabajo`;
- cierre secundario coherente con facturación completa;
- UI compacta en `Trabajos`;
- validación técnica completa.

Antes de abrir este bloque se corrigió el `500` de `/trabajos`, por lo que la implementación arranca con la vista estabilizada.

## 2. Regla aplicada

El estado principal de `Trabajo` se sigue guardando en base de datos, pero ya no queda al arbitrio de valores manuales inconsistentes cuando existen datos operativos reales.

Reglas derivadas:

- `cancelado`:
    - se preserva como estado terminal manual.
- `finalizado`:
    - se preserva como estado terminal de cierre;
    - no lo activa la facturación por sí sola.
- `en_curso`:
    - trabajo sin fin técnico confirmado.
- `terminado`:
    - fin técnico confirmado, pero sin pedido facturable real.
- `pendiente_facturar`:
    - trabajo técnicamente terminado y con pedido facturable, pero sin facturación completa.
- `facturado`:
    - trabajo técnicamente terminado y completamente facturado;
    - sigue pendiente de cierre secundario hasta que dirección lo cierre.

Reglas de `Pedido`:

- `facturado_parcial` cuando hay facturación real parcial;
- `facturado` cuando la facturación real cubre el importe del pedido;
- si una factura se anula, el pedido vuelve a su estado operativo previo (`recibido`/`pendiente`) y pierde el cuadre de facturación.

Regla de cierre secundario:

- el dashboard de cierre ya no permite cerrar una obra solo por estar terminada;
- exige además facturación completa real;
- `finalizado` sigue siendo el cierre operativo terminal, no un efecto colateral automático de crear facturas.

## 3. Implementación

### Backend

Se crea `app/Services/TrabajoStateService.php` para centralizar:

- recálculo de `importe_facturado` real del pedido desde `factura_items`;
- exclusión de facturas `anulada` del cálculo;
- promoción/degradación de estado de `Pedido`;
- derivación del estado principal de `Trabajo`;
- snapshot reutilizable de facturación y cierre.

Se conecta el servicio a:

- `PedidoController`:
    - tras crear, actualizar, cancelar o reasignar impacto económico.
- `FacturaController`:
    - tras crear, actualizar o anular factura;
    - la validación de pendiente ya no cuenta líneas pertenecientes a facturas anuladas.
- `TrabajoController`:
    - tras crear/editar trabajo;
    - el inline patch de `estado` solo permite `en_curso`, `terminado` y `cancelado`;
    - al volver a `en_curso` se limpia `fecha_terminacion`.
- `ClosureDashboardService`:
    - el checklist incorpora `billingComplete`;
    - `close` exige facturación completa real.

### API / recursos

`TrabajoResource` expone ahora:

- `estado_facturacion`;
- `facturacion_completa`;
- `cierre_secundario`;
- `listo_para_cierre`.

Esto permite a frontend pintar información secundaria sin duplicar reglas.

### Frontend

En `TrabajosExcelView.jsx`:

- el editor inline de estado deja de ofrecer estados derivados;
- se mantienen badges en una sola línea;
- se añade chip secundario compacto solo cuando aporta valor:
    - `Facturación parcial`;
    - `Cierre pendiente`;
    - `Cierre bloqueado`.

No se añaden columnas nuevas.

En `Trabajos/Form.jsx`:

- el selector completo de estado también queda alineado con los estados manuales;
- si el trabajo ya está en un estado derivado (`pendiente_facturar`, `facturado`, `finalizado`), el formulario lo muestra como estado actual calculado;
- al guardar una edición sin cambiar ese estado derivado, no se fuerza de nuevo en el payload;
- dirección sigue pudiendo editar un trabajo finalizado sin romper su estado actual.

## 4. Tests ejecutados

Con PHP de Windows/XAMPP, en secuencial:

- `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=TrabajoTest`
    - OK, `35` tests y `278` aserciones.
- `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=PedidoTest`
    - OK, `20` tests y `76` aserciones.
- `cmd.exe /C "C:\xampp\php\php.exe" artisan test --filter=Factura`
    - OK, `36` tests y `171` aserciones.
    - aquí también quedaron correctos los casos relacionados de `ClosureDashboardTest`, `FacturaExportTest`, `MaestrosTest`, `PedidoTest`, `PermissionRoutesTest` y `TrabajoTest` que coinciden por patrón de filtro.

Cobertura añadida o ajustada:

- `TrabajoRequestTest` para bloquear estados derivados en el formulario completo;
- trabajo terminado sin pedido;
- trabajo con pedido sin facturar;
- trabajo con facturación parcial;
- trabajo facturado completo;
- trabajo facturado y listo para cierre;
- trabajo finalizado estable;
- cierre bloqueado hasta facturación completa;
- anulación de factura recalculando pedido y trabajo.

## 5. Validación frontend/build

- `cmd.exe /C npm run build`
    - OK.
- `git diff --check`
    - OK.
    - solo avisos CRLF en ficheros ajenos al alcance.

## 6. Estado final

- `Bloque 5`: validado técnicamente.
- `Resultado`: completo.

Queda fuera de esta fase:

- cualquier despliegue o migración destructiva;
- Bloque 6;
- cambios en Moeve/ARIBA;
- cambios de roles/permisos.

## 7. Riesgos y pendientes

- no se ejecutó validación manual visual con navegador desde esta sesión.

## 8. Siguiente paso recomendado

Si se acepta este cierre técnico:

- siguiente paso: `Bloque 6` solo cuando el cliente lo pida explícitamente.

Si se exige evidencia visual adicional:

- revisar manualmente `/trabajos` y el dashboard de cierre con escenarios de facturación parcial/completa antes de abrir otro bloque.
