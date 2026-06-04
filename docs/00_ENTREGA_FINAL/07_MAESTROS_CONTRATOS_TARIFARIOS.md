# Maestros: contratos, tarifarios y estaciones

## Árbol de maestros

```
Empresa (ej: MOEVE Energy S.A., CIF A28003119)
  └── Sociedad/CIF facturadora (puede haber varias por empresa)
      └── Contrato (ej: 772 - Contrato 772 MOEVE)
            ├── Datos generales: código, nombre, tipo, estado, vigencia
            ├── Datos ARIBA: Cta. Mayor, Propuesta Inversión, Acción gasto, Proveedor, Sociedad
            └── Tarifario (ej: Tarifario 772 MOEVE v2026-demo)
                  ├── Predeterminado: sí/no (uno por contrato)
                  └── Línea de tarifa
                        ├── código_tarifa (ej: 165023)
                        ├── descripción (ej: TOMA DE DATOS SIMPLE)
                        ├── tarifa_aplicada (ej: 510,00 €/ud)
                        └── unidad (ej: ud, hora, m²)
```

## Acceso

- Dirección y Admin técnico pueden ver y editar maestros.
- Ejecución no tiene acceso a maestros.
- Contabilidad no tiene acceso a maestros.

Ruta: **Menú lateral → Maestros** o `/maestros`

---

## Contratos

### Datos generales
- **Código de contrato**: identificador operativo (ej: 772). Se usa en las exportaciones ARIBA.
- **Nombre**: nombre descriptivo (ej: Contrato 772 MOEVE).
- **Tipo**: marco / directo / otro.
- **Estado**: vigente / expirado / cancelado.
- **Activo**: determina si está disponible para crear nuevos trabajos.

### Datos ARIBA / Solicitud Moeve
Estos campos se configuran en la edición del contrato y se usan en las exportaciones PDF, CSV y cuadro ARIBA:

| Campo | Ejemplo | Uso |
|-------|---------|-----|
| Cta. de Mayor | `6330001` | Columna 12 del CSV ARIBA |
| Propuesta de Inversión / Opex | `OPEX-MOEVE-2026` | Cabecera ARIBA |
| Acción de gasto AC | `AC-MOEVE-2026` | Cabecera ARIBA |
| Nombre proveedor / Contrato | `CIETE INGENIEROS S.A.` | Columna Proveedor/Contrato |
| Sociedad (fallback) | `MOEVE Energy S.A.` | Si la estación no tiene cod_sociedad |

**Si estos campos están vacíos**, la exportación mostrará "Pendiente de parametrizar". César debe actualizarlos con los valores reales de Moeve antes de usar en producción.

---

## Tarifarios

### Predeterminado
Cada contrato puede tener varios tarifarios, pero solo uno es el **predeterminado**. Cuando se crea un trabajo en una estación, el sistema precarga automáticamente el tarifario predeterminado del contrato asociado.

Para cambiar el predeterminado: Maestros → Contratos y tarifas → botón "Marcar" en el tarifario deseado.

### Tarifa Repsol — regla de negocio

**Repsol trabaja con tarifa única vigente.**

César García confirmó: *"La tarifa de Repsol. Es única."*

El archivo "Tarifa Ingenieria Ciete (3).xlsx" (hoja "Adjud. 2023-2027") corresponde a un único tarifario con múltiples líneas/conceptos, no a varios tarifarios separados por tipología.

- Un contrato principal Repsol.
- Un único tarifario activo y predeterminado: **TARIFA 23-27 REPSOL** (versión 2023-2027).
- Las tipologías de Repsol (obras, tiendas, NPV, etc.) son categorías/tipos de trabajo, no tarifarios separados.
- La exportación PDF/CSV/ARIBA es exclusiva de MOEVE y no aplica a Repsol.

Si en Maestros aparece más de un tarifario Repsol activo, revisar y desactivar el que no tenga pedidos asociados.

### Líneas de tarifa
Cada línea tiene:
- **Código de tarifa** (ej: `165023`): código MOEVE que aparece en el CSV y ARIBA.
- **Descripción**: nombre del servicio (ej: TOMA DE DATOS SIMPLE).
- **Tarifa aplicada**: precio unitario.
- **Unidad**: ud, hora, m², etc.

### Vista árbol unificada
La pantalla `/maestros/contratos-tarifas` muestra el árbol completo: empresa → sociedad → contrato → tarifarios → líneas, con filtros y búsqueda.

---

## Estaciones de servicio

### Datos
- **Código de estación**: identificador único por cliente (ej: `33450`). Alfanumérico, máx. 6-8 caracteres.
- **Nombre**: nombre comercial de la estación.
- **Municipio y provincia**: para búsquedas geográficas.

### Política de cambio de código/nombre
Cambiar el código o nombre de una estación afecta al histórico de trabajos. El formulario de edición muestra un **aviso en rojo** cuando el usuario modifica estos campos y requiere marcar un checkbox de confirmación antes de guardar.

Solo el admin técnico debería hacer este tipo de cambio, y solo si hay una razón real (reasignación de estación, corrección de error).

### Datos MOEVE extendidos
Las estaciones de contexto MOEVE tienen datos adicionales en `estaciones_moeve_ext`:
- `cod_sociedad`: código de sociedad MOEVE (fuente primaria del campo "Sociedad" en ARIBA).
- `tecnico_gestion`, `responsable_gestor`: datos de contacto MOEVE.

Si una estación MOEVE no tiene `cod_sociedad`, el sistema usa el campo `ariba_sociedad` del contrato como fallback.

---

## Flujo de alta de nueva tarifa

Cuando MOEVE actualiza sus tarifas (normalmente anual):

1. Crear nuevo tarifario en el contrato existente (no modificar el actual).
2. Añadir las líneas nuevas con sus códigos y precios.
3. Marcar el nuevo tarifario como predeterminado.
4. El tarifario antiguo queda inactivo para nuevos trabajos pero conserva el histórico.

**No borres ni edites tarifarios con pedidos asociados.**
