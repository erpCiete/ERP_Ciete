# Bitácora · Daniel Lopez · Frontend
# Sprint 03 · ERP Ciete · Semana 13–17 abril 2026
# Ruta: docs/05-SPRINTS/Sprint_03/Bitacora/FRONT/daniel_lopez.md

---

## Lunes 13/04/2026

### Qué he hecho
- Coordinación general del sprint 03 frontend.
- Revisión del plan de sprint general de Pablo y elaboración del plan de ejecución frontend.
- Distribución de tareas al equipo: Carlos con i18n, Jimmy con BadgeTrabajo, D. Bascope con useTrabajos, Sebas con TrabajosColumnas.
- Añadidas rutas de trabajos e importaciones en routes/web.php dentro del grupo middleware('auth').
- Verificado con php artisan route:list que las rutas están activas.

### Archivos tocados
- `routes/web.php` (HOT-H1 — avisado en Discord antes y después)
- `docs/05-SPRINTS/Sprint_03/FRONT_Plan_Ejecucion.md` (plan del sprint)

### Bloqueos
- TrabajoController del backend no disponible aún — trabajos.index devuelve error hasta que back lo implemente.

### Qué queda pendiente
- Trabajos/Index.jsx (tarea del martes)
- Trabajos/Form.jsx (tarea del miércoles)

### Riesgo para mañana
Bajo — el Index.jsx puede construirse con los props tipados mientras el back no está listo.

---

## Martes 14/04/2026

### Qué he hecho
- Desarrollado `Trabajos/Index.jsx` completo — listado de trabajos con columnas
  condicionales MOEVE/REPSOL siguiendo el patrón visual de `Clientes/Index.jsx`.
- Integrado `BadgeTrabajo` (Jimmy) para el estado de cada fila.
- Integrado `TrabajosColumnas` (Sebas) para las columnas dinámicas MOEVE/REPSOL
  sin conflictos de archivo — el sub-componente de Sebas se importa en línea 391.
- Integrado `useTrabajos` (D. Bascope) para la navegación y eliminación.
- Filtros implementados con `router.get` de Inertia (server-side):
  búsqueda con debounce 400ms, select de estado, rango de fechas desde/hasta.
- Paginación conectada con los props `meta.pagination` del backend.
- Estados UI cubiertos: cargando (skeleton animado), error (+ reintentar), vacío, datos.
- Modal de confirmación antes de eliminar reutilizando `ModalConfirmacion.jsx`.
- colSpan dinámico calculado en `totalCols` para que vacío/error/skeleton
  cuadren siempre con el número real de columnas (varía según contexto).
- `npm run build` pasa sin errores.

### Archivos tocados
- `resources/js/Pages/Trabajos/Index.jsx` (creado)

### Bloqueos
- `BadgeTrabajo.jsx` necesario para que el componente compile — entregado
  por Jimmy hoy, pendiente de colocar en `resources/js/Components/ui/`.
- `TrabajoController@index` del backend aún no disponible — el componente
  compila pero muestra estado vacío hasta que back entregue los props reales.
- `TrabajosColumnas` de Sebas entregado e integrado en línea 391.

### Qué queda pendiente
- Colocar `BadgeTrabajo.jsx` en `resources/js/Components/ui/BadgeTrabajo.jsx`.
- Probar el flujo real cuando back entregue el seeder (previsto miércoles 15).
- Mañana miércoles: `Trabajos/Form.jsx` con campos condicionales MOEVE/REPSOL.

### Riesgo para mañana
Bajo — Form.jsx puede construirse en paralelo al backend.
Único riesgo: que el contrato API cambie nombres de campo.
Confirmar con back en checkpoint 13:00 del miércoles.

---

## Miércoles 15/04/2026

### Qué he hecho
- Desarrollado `Trabajos/Form.jsx` completo con todos los campos del TrabajoResource:
  campos comunes (numero_trabajo, descripcion_trabajo, id_estacion_servicio,
  fecha_encargo, fecha_terminado, estado, observaciones), campos MOEVE
  (id_contrato, categoria) y campos REPSOL (id_tipo_documento, id_tipo_trabajo,
  numero_aviso).
- Formulario organizado en 5 secciones con fieldset: datos principales, fechas,
  MOEVE (borde azul), REPSOL (borde rojo) y observaciones.
- Validaciones en cliente implementadas: campos obligatorios comunes, obligatorios
  condicionales por contexto (id_contrato si MOEVE, id_tipo_documento e
  id_tipo_trabajo si REPSOL), formato y longitud en descripción.
- Errores 422 del backend pintados campo a campo via serverErrors.
- Submit con axios a /api/v1/trabajos — el Form recibe el objeto trabajo completo
  como prop desde Inertia (no trabajoId separado).
- Detectado y corregido error 500 en la ruta /trabajos: el TrabajoController
  tenía namespace `App\Http\Controllers\Api` pero web.php importa
  `App\Http\Controllers\TrabajoController` — dos clases distintas.
- Corregido en TrabajoController: namespace → `App\Http\Controllers`,
  import resource → `App\Http\Resources\Api\TrabajoResource`,
  prop `filtros` → `filters` (el Index.jsx espera filters en inglés),
  añadido `canCreate` a los props del index.
- Añadidos helpers privados en el controller para cargar catálogos
  (contratos, tiposDocumento, tiposTrabajo) con try/catch — devuelven []
  si el modelo no existe aún sin romper el formulario.
- `npm run build` pasa sin errores tras las correcciones.

### Archivos tocados
- `resources/js/Pages/Trabajos/Form.jsx` (creado)
- `app/Http/Controllers/TrabajoController.php` (corregido — namespace y props)

### Bloqueos
- Los catálogos (contratos, tiposDocumento, tiposTrabajo) aún no tienen modelos
  en el backend — el formulario muestra input de texto libre como fallback.
  Cuando back implemente los modelos, los selects se cargan automáticamente.
- Seeders con datos demo aún no disponibles — no se puede probar el flujo
  de edición con datos reales.

### Qué queda pendiente
- Probar el flujo de creación real cuando el seeder esté disponible.
- Jueves: coordinar con Sebas el Upload.jsx de importaciones.
- Viernes: activar sidebar Obras + Dashboard con datos reales.

### Riesgo para mañana
Bajo — Upload.jsx es responsabilidad de Sebas, mi rol es revisión y coordinación.

---

## Jueves 16/04/2026

### Qué he hecho
- Revisión del Upload.jsx de Sebas y Jimmy — feedback dado en Discord.
- Coordinación con back en checkpoint 13:00 para confirmar estructura
  del endpoint de preview de importaciones.
- Detectado segundo error en el flujo de obras: al acceder al listado
  el controller pasaba `filtros` (español) pero Index.jsx destructura `filters`
  (inglés) — los filtros no se restauraban al navegar. Corregido en el controller
  del miércoles (ya incluido en el commit de ayer).
- Revisión del PR de D. Bascope (useTrabajos) — aprobado y mergeado a develop.
- Revisión del PR de Jimmy (BadgeTrabajo) — aprobado y mergeado.
- Revisión del PR de Sebas (TrabajosColumnas) — aprobado con comentarios menores,
  mergeado tras corrección.
- Verificado que `php artisan route:list` muestra correctamente todas las rutas
  de trabajos e importaciones dentro del grupo auth.
- `npm run build` verificado sin errores tras todos los merges.

### Archivos tocados
- Revisión de PRs (sin edición directa de archivos)
- `routes/web.php` — verificación de rutas (sin cambios)

### Bloqueos
- ImportacionController del backend no disponible — Upload.jsx usa mock de preview.
- Dashboard props reales pendientes para el viernes.

### Qué queda pendiente
- Viernes: activar sidebar + Dashboard + cierre documental del sprint.

### Riesgo para mañana
Bajo — el sidebar ya tiene el enlace de Obras implementado desde el martes
(AuthenticatedLayout.jsx). Solo confirmar que el estado activo funciona.

---

## Viernes 17/04/2026

### Qué he hecho
- Verificado que el enlace "Obras" del sidebar navega correctamente a /trabajos
  y marca el estado activo — ya estaba implementado desde el martes, sin cambios
  adicionales necesarios.
- Coordinación con back en checkpoint 13:00 — Dashboard props reales no disponibles,
  documentado como bloqueo.
- Dashboard mantenido con datos de placeholder (obras_en_curso: 0, obras_recientes: [])
  según protocolo del plan cuando el back no entrega los props a tiempo.
- Coordinación con Carlos para cierre documental: revisión de bitácoras del equipo,
  capturas de pantalla y documento de cierre del sprint.
- QA funcional revisado junto con Carlos — checklist verificado según el plan.
- `npm run build` verificado sin errores — criterio técnico GO cumplido.

### Archivos tocados
- Sin cambios de código — día de revisión, QA y cierre documental.

### Bloqueos
- Dashboard con datos reales bloqueado por back — no entregaron props
  `obras_en_curso` y `obras_recientes` antes del cierre del sprint.
  Queda pendiente para Sprint 04.
- ImportacionController no entregado — Upload.jsx cierra el sprint con mock de preview.
  Queda pendiente para Sprint 04.

### Qué queda pendiente para Sprint 04
- Dashboard con datos reales de obras en curso y recientes.
- Importaciones: conectar Upload.jsx con el endpoint real de preview y confirmación.
- Catálogos del formulario de obras (contratos, tiposDocumento, tiposTrabajo)
  cuando back implemente los modelos.
- Prueba de flujo completo de edición de trabajos con datos del seeder.

### Criterio GO frontend: SI / NO

- [x] Trabajos/Index.jsx funcional con columnas condicionales MOEVE/REPSOL
- [x] BadgeTrabajo muestra estado correcto en cada fila
- [x] Filtros de búsqueda y estado funcionando server-side
- [x] Trabajos/Form.jsx con todos los campos del TrabajoResource
- [x] Validaciones en cliente bloquean submit si hay errores
- [x] Errores 422 del backend visibles campo a campo
- [x] Sidebar enlace "Obras" activo y con estado de navegación correcto
- [x] npm run build pasa sin errores
- [x] Sin errores críticos en consola del navegador
- [ ] Datos reales del seeder en el listado — PENDIENTE (back no entregó seeders)
- [ ] Dashboard con obras_en_curso real — BLOQUEADO por back
- [ ] Importaciones/Upload.jsx conectado con endpoint real — BLOQUEADO por back
- [x] Bitácoras completas — Carlos confirma
