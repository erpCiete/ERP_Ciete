# Bitacora BACK · Carlos Puchol

## General
### Objetivo de la semana
- Apoyar en el desarrollo core del módulo de Trabajos (Obras), garantizando que la salida de datos de la API respete estrictamente la regla de negocio de aislamiento entre los clientes MOEVE y REPSOL.

## 2026-04-13
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-14
### Objetivo del dia
- Construir la capa de presentación API (Resource) para la entidad Trabajos, actuando como firewall de datos para que el Frontend reciba un JSON limpio, ligero y dependiente del contexto.

### Tareas realizadas
- [B03-03] Creación e implementación de `TrabajoResource.php`.
- Estandarización de salida JSON aplicando condicionales lógicos (`mergeWhen`) para inyectar bloques de datos de MOEVE (Contratos) o REPSOL (Avisos) según el `id_contexto`.
- Implementación de `whenLoaded()` en las relaciones (Empresa, Estación) para blindar la API contra el problema de rendimiento de consultas N+1.
- Inyección de metadatos de autorización (ACL) en el array `can` para que el frontend habilite o deshabilite dinámicamente los botones de acción.

### Archivos tocados
- `app/Http/Resources/TrabajoResource.php`

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
| 10:30 | La respuesta de la API enviaba llaves con valor `null` de campos que no pertenecían al operador (ej. enviaba `"id_contrato": null` a usuarios de Repsol). | Medio | Cambiar el enfoque de mapeo manual a condicionales de bloque utilizando `$this->mergeWhen()`. | Cerrado |
| 12:45 | Error N+1 detectado al consultar listados grandes por falta de carga de la relación del tarifario/contrato. | Alto | Envolver todas las llamadas a sub-recursos con `$this->whenLoaded('nombre_relacion')`. | Cerrado |

### Decisiones tomadas
- **Limpieza Estricta:** Se decide que si una columna no pertenece al contexto activo, no se envía con valor nulo; directamente se omite la llave en el JSON devuelto.
- **Delegación de Lógica:** Se decide enviar el cálculo booleano de permisos (`can.update`, `can.delete`, `can.restore`) precalculado desde el backend para quitarle peso de procesamiento al cliente React.

### Pendiente para mañana
- Estar disponible para ajustes rápidos de llaves del JSON si el equipo Frontend (Daniel/Jimmy) encuentra discrepancias al conectar el Resource con la tabla de la interfaz (`Trabajos/Index.jsx`).
- Preparar entorno para dar apoyo a Eduardo en la infraestructura de `phpspreadsheet` (Importaciones).

### Handoff
- Notificado a **Daniel López** (Lead Front): El contrato de la API ya está implementado en la capa Resource. Al hacer un `GET`, los campos `contrato` o `numero_aviso` llegarán o desaparecerán de forma automática según la sesión del usuario.

## 2026-04-15
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-16
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 

## 2026-04-17
### Objetivo del dia
- 

### Tareas realizadas
- 

### Archivos tocados
- 

### Errores / bloqueos
| Hora | Error/Bloqueo | Impacto (Alto/Medio/Bajo) | Accion tomada | Estado (Abierto/Cerrado) |
|---|---|---|---|---|
|  |  |  |  |  |

### Decisiones tomadas
- 

### Pendiente para mañana
- 

### Handoff
- 
