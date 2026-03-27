# Alcance y no alcance · ERP Ciete

## 1. Objetivo del documento
Definir qué entra y qué no entra en la fase inicial del ERP Ciete para alinear expectativas de cliente, coordinación y equipo técnico.

## 2. Contexto de partida
CIETE trabaja actualmente con Excels para controlar obras, pedidos, estaciones, legalizaciones y facturación, con especial volumen en Repsol y Cepsa.

Problemas detectados:
- Edición simultánea limitada y bloqueos.
- Falta de trazabilidad de cambios.
- Dificultad de informes fiables.
- Riesgo de descuadres entre trabajo, pedido y factura.
- Riesgo de realizar trabajos sin correcto encaje en cobro.

## 3. Alcance de la fase inicial

### 3.1 Núcleo funcional
- Gestión de clientes con separación obligatoria de Repsol y Cepsa.
- Gestión de estaciones por cliente.
- Gestión de obras/trabajos como entidad central.
- Gestión de pedidos y aviso previo cuando aplique.
- Gestión de estados de obra (workplan).
- Gestión de legalizaciones como relación 1:N con obra.
- Gestión de facturación asociada a obra/pedido.
- Listados, filtros y búsquedas (incluyendo búsqueda por estación).

### 3.2 Reglas críticas incluidas
- No mezclar datos ni reporting entre Repsol y Cepsa.
- Registrar fecha de encargo y fecha real de terminación.
- Bloquear modificación de trabajos cerrados según rol/permisos.
- Mantener trazabilidad mínima de cambios relevantes.
- Soportar operación multiusuario.

### 3.3 Escalabilidad y operación
- Preparación para varios miles de registros anuales.
- Estructura relacional de datos para crecimiento funcional.
- Base preparada para informes operativos por cliente, tipo de trabajo y estado.

## 4. No alcance de la fase inicial
- Integraciones externas no confirmadas por cliente.
- Automatizaciones avanzadas no priorizadas en requisitos base.
- Sustitución de todos los procesos secundarios desde el día 1.
- Desarrollo de módulos no trazados en requisitos aprobados.
- Réplica literal de todos los Excels sin rediseño de proceso.

## 5. Criterio de priorización
Se prioriza todo lo que reduzca riesgo operativo y financiero:
- Control de obra, pedido y facturación.
- Separación estricta por cliente.
- Trazabilidad y control de cambios.
- Flujo de estados y bloqueo de cierre.

## 6. Resultado esperado de esta fase
Disponer de una aplicación web interna, multiusuario y trazable que reemplace la operativa crítica en Excel y reduzca el riesgo de trabajos mal cerrados o no cobrados.
