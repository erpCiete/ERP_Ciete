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
-

### Archivos tocados
-

### Bloqueos
-

### Qué queda pendiente
-

### Riesgo para mañana
-

---

## Jueves 16/04/2026

### Qué he hecho
-

### Archivos tocados
-

### Bloqueos
-

### Qué queda pendiente
-

### Riesgo para mañana
-

---

## Viernes 17/04/2026

### Qué he hecho
-

### Archivos tocados
-

### Bloqueos
-

### Qué queda pendiente
-

### Criterio GO frontend: SI / NO

- [ ] Trabajos/Index.jsx funcional con datos reales del seeder
- [ ] Columnas condicionales MOEVE/REPSOL correctas por contexto
- [ ] Trabajos/Form.jsx: crear trabajo MOEVE y REPSOL con validaciones
- [ ] Importaciones/Upload.jsx: subida con validación de tipo y tamaño
- [ ] Sidebar enlace "Obras" activo
- [ ] npm run build sin errores
- [ ] Bitácoras completas — Carlos confirma
