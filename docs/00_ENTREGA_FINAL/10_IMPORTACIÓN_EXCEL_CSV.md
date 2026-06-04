# Importación Excel y CSV

## Estado actual

La pantalla de importación existe y es accesible para el Admin técnico en `/importaciones`.

| Característica | Estado |
|---------------|--------|
| Pantalla UI | ✅ Funcional |
| Importación XLSX | ✅ Funcional |
| Importación XLS | ✅ Funcional |
| Importación CSV | ⚠️ P1 — el parser falla con CSV (solo soporta Excel) |
| Preview/dry-run | ✅ Funcional (paso intermedio antes de confirmar) |
| Historial de importaciones | ✅ Funcional |
| Tipos disponibles | Estaciones MOEVE, Estaciones REPSOL, Trabajos, Tarifario |

## Flujo de importación

1. Ir a `/importaciones`
2. Seleccionar el tipo (Estaciones MOEVE / Repsol, Trabajos, Tarifario)
3. Subir el archivo Excel (.xlsx)
4. El sistema muestra un **preview** de las primeras filas
5. Revisar errores y advertencias por fila
6. Si es correcto, confirmar la importación

El archivo se guarda en `storage/app/importaciones/temp/` durante el proceso. Se borra al confirmar o cancelar.

## Archivos de prueba disponibles

En `docs/02_CLIENTE/importacion_pruebas_minimas/`:
- `QA_IMPORT_MOEVE_2_FILAS_20260604.csv`
- `QA_IMPORT_REPSOL_2_FILAS_20260604.csv`

**Nota:** Estos archivos CSV no procesará el parser actual (P1). Para probar, usar un XLSX.

## Formato esperado

El parser lee columnas A-E:

| Columna | Campo |
|---------|-------|
| A | numero_trabajo |
| B | descripcion_trabajo |
| C | codigo_estacion |
| D | fecha_encargo |
| E | observaciones |

## Qué no hacer

- No importar archivos de miles de filas sin hacer primero el dry-run.
- No importar sobre la base demo sin un backup previo.
- No usar `migrate:fresh` para limpiar — usa los scripts de reset.

## Fix pendiente (P1)

El `ExcelParserService` usa PhpSpreadsheet configurado para Excel. Para soportar CSV correctamente habría que configurar el lector CSV de PhpSpreadsheet con el delimitador adecuado. No bloquea la demo porque el flujo manual de trabajos/pedidos es completamente funcional.

## Tarifa Repsol — cómo se importa

El archivo "Tarifa Ingenieria Ciete (3).xlsx" recibido de Ciete contiene la tarifa Repsol vigente (hoja "Adjud. 2023-2027").

**Interpretación correcta:**
- Es un único tarifario Repsol con múltiples líneas/conceptos.
- No debe tratarse como varios tarifarios por tipología (obras, tiendas, NPV, etc.).
- Las tipologías son categorías de trabajo, no tarifarios separados.

**Si se importa este Excel**, el tipo correcto en el formulario de importación es `tarifario`, asociando todas las líneas al tarifario "TARIFA 23-27 REPSOL" existente.

**Conclusión documental:** Repsol trabaja con tarifa única vigente. El Excel recibido se interpreta como líneas de un único tarifario Repsol, no como varios tarifarios ni como exportación de pedido. La exportación PDF/CSV/ARIBA es específica de Moeve.

---

## Importación masiva original

Los datos demo actuales se cargaron desde 11 Excels operativos reales de Ciete (con prefijo `[p1-12-excel-real]`). Si César envía un nuevo Excel operativo, el flujo es:

1. Hacer backup de la BD actual
2. Usar `/importaciones` → tipo correspondiente → subir XLSX
3. Revisar el preview
4. Confirmar
