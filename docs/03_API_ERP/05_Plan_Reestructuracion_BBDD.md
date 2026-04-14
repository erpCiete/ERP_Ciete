# Plan de Reestructuración Completa del ERP Ciete

> **Fecha:** 13 de abril de 2026
> **Versión:** 1.0
> **Stack:** Laravel 11 + Inertia.js + React 18 + MariaDB 10.4
> **Base de datos actual:** `abaco_ciete` (27 tablas, 9 migraciones)
> **Documentos fuente:** 13 archivos analizados en `docs/excelsactualizados/`

---

## Índice

1. [Diagnóstico](#1-diagnóstico)
2. [Propuesta de modelo de datos](#2-propuesta-de-modelo-de-datos)
3. [Diseño de entidades](#3-diseño-de-entidades)
4. [Estrategia para estaciones](#4-estrategia-para-estaciones)
5. [RBAC y visibilidad](#5-rbac-y-visibilidad)
6. [Propuesta de interfaz](#6-propuesta-de-interfaz)
7. [Rendimiento y escalabilidad](#7-rendimiento-y-escalabilidad)
8. [Importación de Excel](#8-importación-de-excel)
9. [Plan de migración](#9-plan-de-migración)
10. [Propuesta final recomendada](#10-propuesta-final-recomendada)
11. [Decisión técnica recomendada](#11-decisión-técnica-recomendada)

---

## 1. Diagnóstico

### 1.1 Por qué la BBDD actual se queda corta

La base de datos `abaco_ciete` tiene 27 tablas con un diseño multi-tenant basado en `id_contexto`. El concepto de multi-tenancy por contexto es correcto, pero la implementación actual presenta estos problemas concretos:

**a) La tabla `proyectos` intenta ser "trabajos" pero no lo es.**

La tabla actual tiene campos genéricos (`codigo_proyecto`, `nombre_proyecto`, `descripcion_seleccionable`) que no reflejan la realidad operativa de los Excel. En los Excel, un "trabajo" tiene:

- Nº ES (estación), Tipo de trabajo, Descripción del trabajo, Código servicio, Número tarifa, Importe unitario, Unidades, Orden de mantenimiento, Nº Aviso...

Nada de esto está en `proyectos`. La tabla actual es una abstracción genérica de "proyecto" que no encaja con ninguno de los 13 Excel analizados.

**b) La tabla `pedidos` no refleja la relación real Trabajo → Pedido.**

En la BD actual, `pedidos` tiene un `numero_pedido`, `subtotal`, `total` y poco más. En la realidad operativa:

- Un trabajo puede tener **N pedidos** (cada uno con su número SAP).
- Cada pedido tiene **N ítems** (código tarifa × unidades × precio unitario).
- Un mismo número de pedido puede aparecer en varias líneas del Excel si tiene múltiples ítems de tarifa.
- Los pedidos tienen estados propios (solicitado, recibido, en ejecución, facturado).

La tabla `pedidos_lineas` existe pero está vinculada a `id_servicio` (tabla genérica de 6 servicios), no al tarifario real de 370 líneas de Repsol.

**c) La tabla `facturas` asume 1 factura por pedido.**

La BD tiene `facturas.id_pedido` (FK directa 1:1). En la realidad:

- Repsol puede tener **2 facturas por trabajo** (1ª y 2ª factura, con fechas y números distintos).
- Moeve tiene **doble numeración** (Nº FACTURA CCP + Nº FACTURA CIETE).
- Una factura puede agrupar múltiples pedidos.
- La factura puede llegar **antes** que el pedido (descuadre temporal admitido por el negocio).

**d) El tarifario no refleja la estructura real.**

`tarifario_servicios` vincula un `id_tarifario` con un `id_servicio` (solo 6 filas operativas). El tarifario real de Repsol tiene **370 líneas** con campos como:

- Código (G-1, 1.1, 1.2, etc.)
- Actuación
- Descripción
- Tarifa base
- Factor multiplicador (0.9562)
- Tarifa CIETE resultante

La tabla `servicios` actual (6 filas genéricas como "Inspección técnica" o "Obra civil menor") no tiene nada que ver con esto.

**e) La tabla `estaciones_servicio` mezcla campos de Moeve y Repsol.**

La tabla actual tiene `cod_repsol` y `cod_cepsa` (ahora `cod_moeve`) en la misma fila, como si una estación pudiera pertenecer a ambos clientes. En realidad:

- Las estaciones de Moeve (3.583 filas, 92 columnas) y Repsol (3.275 filas, 65 columnas) son **listados completamente separados** con campos distintos.
- Moeve tiene: CONCESIÓN, Nº MÁRGENES, ESTADO, GPS, TÉCNICO GESTIÓN, COD RETAILGAS, COD SOCIEDAD, SOCIEDAD.
- Repsol tiene: C.EMP, CODIGO SOLRED, volúmenes de combustible (gasolina 95, 98, gasóleo, GLP, AdBlue), CLIENTE, GERENTE, ENCARGADO, PROVINCIAL, ventas mensuales.

**f) No hay tabla de "tipos de documento" ni "tipos de trabajo".**

Repsol organiza sus trabajos en tipos de documento (DISEÑO, EDIFICACIÓN, OBRAS, LICENCIAS, FV, ESTRUCTURAS, MTO, PUNTOS DE RECARGA), cada uno con sus propios tipos de trabajo (Rangos). Moeve usa "Categoría". Nada de esto existe en la BD actual.

**g) No hay tabla de contratos.**

Moeve trabaja con un campo "Contrato" en cada trabajo. Z10 tiene una hoja "OTROS" con contratos directos. No existe tabla `contratos` en la BD actual.

**h) No hay staging/importación.**

No existe mecanismo para importar Excel, validar datos, previsualizar y confirmar.

**i) No hay auditoría de cambios en datos.**

Solo existe `sesiones_login`. No hay log de quién modificó qué dato.

### 1.2 Por qué no sirve una tabla única plana SIN control por contexto

La primera tentación es meter todo en una sola tabla `trabajos` con todos los campos de la envolvente y no hacer nada más. **Eso sería un error**, pero no por tener una sola tabla — sino por no controlar qué campos aplican a cada cliente:

| Problema de la tabla plana SIN control             | Cómo se resuelve con parametrización                                                                                                                                                   |
| -------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 35+ columnas, muchas nullable sin significado      | Los campos específicos por cliente son solo 3-4 (`categoria`, `orden_mantenimiento`, `numero_aviso`, `zona`). El resto (~30 cols) son comunes. Son pocos nullable, no "muchos".        |
| Imposible validar correctamente                    | `TrabajoRequest` aplica reglas distintas según `contexto_activo`: Moeve exige contrato+categoría, Repsol exige tipo_documento+tipo_trabajo. Cada contexto tiene SU validación.         |
| Confusión en la interfaz: ¿qué columnas mostrar?   | El controlador pasa `columnasVisibles[]` al frontend según contexto. React renderiza SOLO esas columnas. Un gestor Moeve nunca ve "Orden Mto." y un gestor Repsol nunca ve "Contrato". |
| Riesgo de rellenar campos del cliente equivocado   | El formulario React solo muestra campos del contexto activo (`{contexto === 1 && <MoeveFields/>}`). El backend ignora campos ajenos al contexto en la validación.                      |
| Restricciones de integridad diferentes por cliente | La integridad se aplica en capa de aplicación (FormRequest + Resource), no en BD pura. Las FK comunes (estación, tipo_documento, tarifario) ya filtran por `id_contexto`.              |
| Consultas más lentas por ancho de fila             | Con ~30 columnas reales (no 100), índices compuestos `(id_contexto, campo)` y cursor pagination, el impacto es despreciable para ~40K filas.                                           |

**Conclusión:** Una tabla única de trabajos SÍ funciona si se parametriza por contexto en 3 capas: validación (FormRequest), serialización (Resource) y renderización (React). Lo que NO funciona es meterlo todo en una tabla y esperar que "se entienda solo". Ver sección 6.5 para la implementación concreta de estas 3 capas.

> **¿Por qué no tablas separadas `trabajos_moeve` y `trabajos_repsol`?** Porque el 80% de los campos y TODA la lógica de pedidos, facturas, estados, cobros y legalizaciones es idéntica. Duplicar esas relaciones y queries sería un error de mantenimiento mayor que tener 3-4 campos nullable.

### 1.3 Principales riesgos si no se rediseña bien

| Riesgo                                   | Consecuencia                                 |
| ---------------------------------------- | -------------------------------------------- |
| Trabajos sin factura que se pierden      | Pérdida económica directa                    |
| Pedidos importados sin vincular a tarifa | Importes incorrectos, cobro erróneo          |
| Mezcla de estaciones entre clientes      | Informes contaminados, facturación cruzada   |
| Falta de control de cierre               | Edición accidental de trabajos cerrados      |
| Usuarios viendo datos de otro cliente    | Violación de confidencialidad operativa      |
| Importaciones sin validación             | Duplicados, datos corruptos, inconsistencias |
| Sin auditoría                            | Imposibilidad de rastrear errores            |

---

## 2. Propuesta de modelo de datos

### 2.1 Decisión arquitectónica: modelo híbrido común + extensiones por cliente

**Descarto** polimorfismo puro (demasiado complejo para el equipo y para las queries).
**Descarto** tablas completamente separadas por cliente (duplicación de lógica imposible de mantener).

**Elijo: Modelo híbrido con tabla común de trabajos + campos envolvente con nullable + tabla de metadatos específicos por cliente.**

La justificación:

1. El 80% de los campos de un "trabajo" son comunes: estación, tipo, descripción, responsables, fechas, importes, estado.
2. El 20% restante son campos específicos: `orden_mantenimiento` (solo Repsol), `contrato` (solo Moeve), `categoria` (solo Moeve).
3. Esos campos específicos son **pocos y estables** — no cambian cada semana.
4. Crear tablas separadas `trabajos_moeve` y `trabajos_repsol` duplicaría toda la lógica de pedidos, facturas, estados y queries.

**Por tanto:** Una tabla `trabajos` con la envolvente completa (campos nullable para los específicos) + un campo `tipo_documento_id` que determina qué columnas aplican + validación en capa de aplicación según contexto.

Para **estaciones**, sí uso tablas separadas de extensión porque las diferencias son enormes (92 cols vs 65 cols, datos completamente distintos).

### 2.2 Arquitectura de tablas — Vista general

```
┌─────────────────────────────────────────────────────────┐
│                    TABLAS MAESTRAS                        │
├─────────────────────────────────────────────────────────┤
│ contextos_cliente        (Moeve, Repsol, Otro)           │
│ empresas                 (clientes, proveedores, Ciete)  │
│ contactos                (personas físicas)               │
│ contactos_empresas       (persona↔empresa)               │
│ estaciones_servicio      (base común)                    │
│ estaciones_moeve_ext     (extensión Moeve: 92 cols)      │
│ estaciones_repsol_ext    (extensión Repsol: 65 cols)     │
│ tipos_documento          (DISEÑO, OBRAS, MTO, etc.)      │
│ tipos_trabajo            (NPV, REFORMA, INDUSTRIA...)    │
│ tarifarios               (versión + vigencia)            │
│ tarifario_lineas         (370 líneas reales)             │
│ contratos                (Moeve + OTROS de Repsol)       │
│ responsables             (normalización personas)        │
│ unidades                 (ud, h, m2, ml)                 │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                  TABLAS OPERATIVAS                        │
├─────────────────────────────────────────────────────────┤
│ trabajos                 (envolvente: ~35 campos)        │
│ pedidos                  (N pedidos por trabajo)         │
│ pedido_items             (N ítems por pedido)            │
│ facturas                 (N facturas por trabajo)        │
│ factura_items            (N líneas por factura)          │
│ cobros                   (pagos recibidos)               │
│ legalizaciones           (trámites legales)              │
│ legalizaciones_contactos (personas en legalización)      │
│ comentarios              (polimórfico: trabajo/legal.)   │
│ presupuestos             (ofertas)                       │
│ presupuesto_lineas       (líneas de presupuesto)         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 TABLAS DE SEGURIDAD                       │
├─────────────────────────────────────────────────────────┤
│ usuarios                 (autenticación)                 │
│ usuario_contextos        (N contextos por usuario) ← NEW│
│ roles                    (admin, gestor, cierre...)      │
│ permisos                 (granulares)                    │
│ rol_permisos             (N:M)                           │
│ usuario_roles            (N:M)                           │
│ sesiones_login           (auditoría acceso)              │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 TABLAS DE IMPORTACIÓN                     │
├─────────────────────────────────────────────────────────┤
│ importaciones            (registro de cada carga)        │
│ importacion_filas        (staging: filas pendientes)     │
│ importacion_errores      (errores detectados)            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 TABLAS DE AUDITORÍA                       │
├─────────────────────────────────────────────────────────┤
│ audit_log                (quién cambió qué, cuándo)      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 TABLAS DE COMUNICACIÓN                    │
├─────────────────────────────────────────────────────────┤
│ direcciones              (polimórfica)                   │
│ telefonos                (polimórfica)                   │
│ emails                   (polimórfica)                   │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Diseño de entidades

### 3.1 `contextos_cliente` — SIN CAMBIOS estructurales

```sql
-- Ya existe. Solo actualizar datos seed:
-- id=1 → MOEVE (antes CEPSA)
-- id=2 → REPSOL
-- id=3 → OTRO
```

### 3.2 `tipos_documento` — NUEVA

Cada cliente organiza su trabajo por tipos de documento (cada Excel de Repsol es un tipo).

```sql
CREATE TABLE tipos_documento (
    id_tipo_documento   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,    -- FK contextos_cliente
    codigo              VARCHAR(30) NOT NULL,         -- 'DISENO','EDIFICACION','OBRAS','LICENCIAS','FV','ESTRUCTURAS','MTO','PUNTOS_RECARGA'
    nombre              VARCHAR(120) NOT NULL,         -- 'Diseño Repsol', 'Control Trabajos Moeve'
    tiene_doble_factura BOOLEAN NOT NULL DEFAULT FALSE,-- TRUE para EDIFICACION, OBRAS, ESTRUCTURAS, MTO
    tiene_orden_mto     BOOLEAN NOT NULL DEFAULT FALSE,-- TRUE si aplica ORDEN MANTENIMIENTO
    tiene_num_tarifa    BOOLEAN NOT NULL DEFAULT FALSE,-- TRUE si CÓDIGO SERVICIO + Nº Tarifa separados
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    UNIQUE KEY uq_tipo_doc_contexto_codigo (id_contexto, codigo)
);
```

**Datos iniciales Repsol:** DISENO, EDIFICACION, OBRAS, LICENCIAS, FV, ESTRUCTURAS_VERTIDOS, MTO, PUNTOS_RECARGA.
**Datos iniciales Moeve:** CONTROL_TRABAJOS (uno solo, ya que Moeve usa un único Excel).

### 3.3 `tipos_trabajo` — NUEVA

Los "Rangos" de cada Excel.

```sql
CREATE TABLE tipos_trabajo (
    id_tipo_trabajo     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_tipo_documento   BIGINT UNSIGNED NOT NULL,     -- FK tipos_documento
    codigo              VARCHAR(80) NOT NULL,
    nombre              VARCHAR(180) NOT NULL,         -- 'NPV','REFORMA GENERAL','INDUSTRIA','STARBUCKS'...
    responsable_ciete_defecto   VARCHAR(150) NULL,     -- Responsable Ciete por defecto según Rangos
    responsable_cliente_defecto VARCHAR(150) NULL,     -- Responsable Cliente por defecto según Rangos
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    UNIQUE KEY uq_tipo_trabajo_ctx_doc_codigo (id_contexto, id_tipo_documento, codigo)
);
```

### 3.4 `estaciones_servicio` — REESTRUCTURADA (base común)

Mantener la tabla base con los campos que comparten ambos clientes:

```sql
CREATE TABLE estaciones_servicio (
    id_estacion_servicio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto          BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente   BIGINT UNSIGNED NOT NULL,
    -- Campos comunes
    codigo_estacion      VARCHAR(80) NOT NULL,   -- C.EMP (Repsol) o CONCESIÓN (Moeve)
    nombre               VARCHAR(180) NOT NULL,
    direccion            VARCHAR(255) NULL,
    codigo_postal        VARCHAR(20) NULL,
    poblacion            VARCHAR(120) NULL,
    provincia            VARCHAR(120) NULL,
    pais                 VARCHAR(120) NOT NULL DEFAULT 'España',
    latitud_wgs84        DECIMAL(11,8) NULL,
    longitud_wgs84       DECIMAL(11,8) NULL,
    estado               VARCHAR(50) NULL,        -- Activa, Baja, etc.
    f_baja               DATE NULL,
    observaciones        TEXT NULL,
    activo               BOOLEAN NOT NULL DEFAULT TRUE,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL,
    UNIQUE KEY uq_estaciones_ctx_codigo (id_contexto, codigo_estacion)
);
```

### 3.5 `estaciones_moeve_ext` — NUEVA (extensión Moeve)

```sql
CREATE TABLE estaciones_moeve_ext (
    id_estacion_servicio BIGINT UNSIGNED PRIMARY KEY, -- FK estaciones_servicio (1:1)
    n_margenes           VARCHAR(20) NULL,
    tecnico_gestion      VARCHAR(150) NULL,
    telefono_tecnico     VARCHAR(30) NULL,
    email_tecnico        VARCHAR(180) NULL,
    responsable_gestor   VARCHAR(150) NULL,
    telefono_gestor      VARCHAR(30) NULL,
    telefono_oficina     VARCHAR(30) NULL,
    sede_email           VARCHAR(180) NULL,
    vinculo_1            VARCHAR(255) NULL,
    vinculo_2            VARCHAR(255) NULL,
    f_alta_modificacion  DATE NULL,
    cod_retailgas        VARCHAR(80) NULL,
    cod_sociedad         VARCHAR(80) NULL,
    sociedad             VARCHAR(180) NULL,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL
);
```

### 3.6 `estaciones_repsol_ext` — NUEVA (extensión Repsol)

```sql
CREATE TABLE estaciones_repsol_ext (
    id_estacion_servicio BIGINT UNSIGNED PRIMARY KEY, -- FK estaciones_servicio (1:1)
    codigo_solred        VARCHAR(80) NULL,
    litros_21            DECIMAL(14,0) NULL,   -- Volumen total combustible
    gnas_95_21           DECIMAL(14,0) NULL,
    gnas_98_21           DECIMAL(14,0) NULL,
    gasoleo_a_21         DECIMAL(14,0) NULL,
    eplus10_21           DECIMAL(14,0) NULL,
    glp_21               DECIMAL(14,0) NULL,
    adblue_21            DECIMAL(14,0) NULL,
    cliente_nombre       VARCHAR(180) NULL,     -- CLIENTE (operador de la estación)
    nom_encargado        VARCHAR(150) NULL,
    nom_gerente          VARCHAR(150) NULL,
    tfno_instalacion     VARCHAR(30) NULL,
    fax_instalacion      VARCHAR(30) NULL,
    tfno_movil_gerente   VARCHAR(30) NULL,
    tfno_movil_encargado VARCHAR(30) NULL,
    margen               CHAR(1) NULL,          -- D/I (Derecho/Izquierdo)
    provincial           VARCHAR(150) NULL,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL
);
```

### 3.7 `contratos` — NUEVA

```sql
CREATE TABLE contratos (
    id_contrato       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto       BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    id_tarifario      BIGINT UNSIGNED NULL,
    codigo_contrato   VARCHAR(100) NOT NULL,
    nombre            VARCHAR(180) NULL,
    tipo              ENUM('marco','directo','otro') NOT NULL DEFAULT 'marco', -- 'directo' para hoja OTROS de Z10
    fecha_inicio      DATE NULL,
    fecha_fin         DATE NULL,
    estado            ENUM('vigente','expirado','cancelado') NOT NULL DEFAULT 'vigente',
    observaciones     TEXT NULL,
    activo            BOOLEAN NOT NULL DEFAULT TRUE,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    UNIQUE KEY uq_contratos_ctx_codigo (id_contexto, codigo_contrato)
);
```

### 3.8 `tarifarios` — REESTRUCTURADA

```sql
CREATE TABLE tarifarios (
    id_tarifario         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto          BIGINT UNSIGNED NOT NULL,
    id_contrato          BIGINT UNSIGNED NULL,    -- FK contratos (puede ir ligado)
    nombre               VARCHAR(160) NOT NULL,    -- 'Tarifa CIETE 2023-2027 Repsol'
    version              VARCHAR(40) NULL,
    fecha_inicio_vigencia DATE NULL,
    fecha_fin_vigencia   DATE NULL,
    factor_multiplicador DECIMAL(6,4) NULL DEFAULT 1.0000, -- 0.9562 para Repsol
    moneda               CHAR(3) NOT NULL DEFAULT 'EUR',
    activo               BOOLEAN NOT NULL DEFAULT TRUE,
    observaciones        TEXT NULL,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL,
    UNIQUE KEY uq_tarifarios_ctx_nombre_version (id_contexto, nombre, version)
);
```

### 3.9 `tarifario_lineas` — REEMPLAZA `tarifario_servicios`

```sql
CREATE TABLE tarifario_lineas (
    id_tarifario_linea BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto        BIGINT UNSIGNED NOT NULL,
    id_tarifario       BIGINT UNSIGNED NOT NULL,
    codigo_tarifa      VARCHAR(30) NOT NULL,       -- 'G-1', '1.1', '1.2', '3012753', etc.
    grupo              VARCHAR(120) NULL,           -- 'GESTIONES CON ORGANISMOS...'
    actuacion          VARCHAR(255) NOT NULL,       -- Nombre descriptivo de la actuación
    descripcion        TEXT NULL,                   -- Detalle ('No incluye visita', etc.)
    tarifa_anterior    DECIMAL(14,2) NULL,          -- Tarifa 2019-2023 (referencia histórica)
    tarifa_base        DECIMAL(14,2) NOT NULL,      -- Tarifa base sin factor
    tarifa_aplicada    DECIMAL(14,2) NOT NULL,      -- Tarifa base × factor = precio final
    id_unidad          BIGINT UNSIGNED NULL,        -- FK unidades (ud, h, m2...)
    activo             BOOLEAN NOT NULL DEFAULT TRUE,
    created_at         TIMESTAMP NULL,
    updated_at         TIMESTAMP NULL,
    UNIQUE KEY uq_tarifa_linea_ctx_tarif_codigo (id_contexto, id_tarifario, codigo_tarifa)
);
```

### 3.10 `trabajos` — REEMPLAZA `proyectos` (tabla principal operativa)

Esta es la tabla más importante del sistema. Refleja la envolvente de todos los Excel analizados:

```sql
CREATE TABLE trabajos (
    id_trabajo          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente  BIGINT UNSIGNED NOT NULL,
    id_estacion_servicio BIGINT UNSIGNED NULL,      -- FK estaciones (puede ser NULL: trabajos sin estación)
    id_tipo_documento   BIGINT UNSIGNED NULL,        -- FK tipos_documento (DISEÑO, OBRAS, etc.)
    id_tipo_trabajo     BIGINT UNSIGNED NULL,        -- FK tipos_trabajo (NPV, REFORMA, INDUSTRIA, etc.)
    id_contrato         BIGINT UNSIGNED NULL,        -- FK contratos (Moeve siempre, Repsol solo OTROS)
    id_tarifario        BIGINT UNSIGNED NULL,        -- FK tarifarios
    id_responsable_ciete BIGINT UNSIGNED NULL,       -- FK usuarios (nullable: Puntos de Recarga no lo tiene)
    id_usuario_cierre   BIGINT UNSIGNED NULL,        -- FK usuarios (quien cerró)

    -- Identificadores
    numero_trabajo      INT UNSIGNED NOT NULL,        -- Nº correlativo dentro del contexto+tipo_doc
    numero_estacion     VARCHAR(30) NULL,             -- Nº ES del Excel (referencia rápida)
    zona                VARCHAR(10) NULL,             -- Z10, Z50, etc. (solo Repsol OBRAS)

    -- Datos del trabajo
    descripcion_trabajo TEXT NULL,                     -- DESCRIPCION DEL TRABAJO
    fecha_encargo       DATE NULL,
    fecha_terminacion   DATE NULL,
    observaciones       TEXT NULL,

    -- Campos específicos Repsol
    numero_aviso        VARCHAR(100) NULL,             -- Nº AVISO / P.KEOPS (solo Repsol)
    orden_mantenimiento VARCHAR(100) NULL,             -- ORDEN MANTEN. (solo Repsol)

    -- Campos específicos Moeve
    categoria           VARCHAR(100) NULL,             -- Categoría (solo Moeve)

    -- Responsable del cliente
    responsable_cliente VARCHAR(150) NULL,             -- RESPONSABLE MOEVE / RESPONSABLE REPSOL

    -- Estado y workflow
    estado              ENUM('borrador','en_curso','terminado','cerrado','cancelado')
                        NOT NULL DEFAULT 'borrador',
    cerrado             BOOLEAN NOT NULL DEFAULT FALSE,
    bloqueado_cierre    BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_cierre        DATETIME NULL,

    -- Auditoría
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    -- Índices
    INDEX idx_trabajos_contexto (id_contexto),
    INDEX idx_trabajos_estacion (id_estacion_servicio, id_contexto),
    INDEX idx_trabajos_tipo_doc (id_tipo_documento, id_contexto),
    INDEX idx_trabajos_estado (estado, id_contexto),
    INDEX idx_trabajos_responsable (id_responsable_ciete, id_contexto),
    INDEX idx_trabajos_fecha_encargo (fecha_encargo, id_contexto),
    UNIQUE KEY uq_trabajos_ctx_tipodoc_numero (id_contexto, id_tipo_documento, numero_trabajo)
);
```

### 3.11 `pedidos` — REESTRUCTURADA

Un trabajo puede tener N pedidos. Cada pedido tiene un número SAP (7300XXXXXX).

```sql
CREATE TABLE pedidos (
    id_pedido           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_trabajo          BIGINT UNSIGNED NOT NULL,     -- FK trabajos (relación clave)
    id_tarifario        BIGINT UNSIGNED NULL,

    numero_pedido       VARCHAR(100) NOT NULL,         -- Nº PEDIDO SAP (ej: 7300564645)
    fecha_solicitud     DATE NULL,                     -- FECHA SOLICITUD PEDIDO
    fecha_recepcion     DATE NULL,                     -- FECHA ENCARGO (cuando llega el pedido)

    -- Importes
    importe_pedido      DECIMAL(14,2) NOT NULL DEFAULT 0.00,  -- IMPORTE PEDIDO
    importe_solicitado  DECIMAL(14,2) NULL,                    -- IMPORTE SOLICITADO (solo Repsol)
    importe_facturado   DECIMAL(14,2) NULL,                    -- IMPORTE FACTURADO (calculado)

    -- Unidades
    unidades_pedido     DECIMAL(14,3) NOT NULL DEFAULT 1.000,  -- UDs DEL PEDIDO
    unidades_solicitadas DECIMAL(14,3) NULL,                   -- UDs SOLICITADAS (solo Repsol)

    -- Estado
    estado              ENUM('pendiente','solicitado','recibido','en_ejecucion','facturado_parcial','facturado','cerrado','anulado')
                        NOT NULL DEFAULT 'pendiente',

    -- Campos de control (Repsol)
    pedido_completo     BOOLEAN NULL,                  -- REV. PEDIDO COMPLETO
    tiene_mas_de_1_item BOOLEAN NULL,                  -- Pedido tiene más de 1 ítem
    facturado_completo  BOOLEAN NULL,                  -- Pedido facturado completo

    observaciones       TEXT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    INDEX idx_pedidos_trabajo (id_trabajo, id_contexto),
    INDEX idx_pedidos_numero (numero_pedido, id_contexto),
    INDEX idx_pedidos_estado (estado, id_contexto)
);
```

**Nota:** Se elimina la constraint UNIQUE en `numero_pedido` por contexto porque un mismo número de pedido puede tener múltiples líneas/ítems en diferentes trabajos.

### 3.12 `pedido_items` — REEMPLAZA `pedidos_lineas`

Cada ítem dentro de un pedido corresponde a una línea del tarifario:

```sql
CREATE TABLE pedido_items (
    id_pedido_item      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_pedido           BIGINT UNSIGNED NOT NULL,
    id_tarifario_linea  BIGINT UNSIGNED NULL,          -- FK tarifario_lineas (la línea de tarifa concreta)

    codigo_servicio     VARCHAR(30) NULL,               -- CÓDIGO SERVICIO (copia para referencia rápida)
    numero_tarifa       VARCHAR(30) NULL,               -- Número Tarifa con punto (solo Repsol, puede diferir de codigo_servicio)
    descripcion_servicio VARCHAR(255) NULL,             -- DESCRIPCION DEL SERVICIO
    precio_unitario     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    cantidad            DECIMAL(14,3) NOT NULL DEFAULT 1.000,
    total_linea         DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    INDEX idx_pedido_items_pedido (id_pedido, id_contexto)
);
```

### 3.13 `facturas` — REESTRUCTURADA

Se cambia la relación: ahora una factura va contra un trabajo (no contra un pedido). Puede haber 1 o 2 facturas por trabajo.

```sql
CREATE TABLE facturas (
    id_factura          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_trabajo          BIGINT UNSIGNED NOT NULL,      -- FK trabajos
    id_empresa_cliente  BIGINT UNSIGNED NOT NULL,

    -- Identificación
    numero_factura      VARCHAR(100) NULL,              -- Nº FACTURA (Repsol) o Nº FACTURA CIETE (Moeve)
    numero_factura_ccp  VARCHAR(100) NULL,              -- Nº FACTURA CCP (solo Moeve: doble numeración)
    serie               VARCHAR(20) NULL,
    orden_factura       TINYINT UNSIGNED NOT NULL DEFAULT 1, -- 1=1ª factura, 2=2ª factura

    -- Fechas
    fecha_solicitud     DATE NULL,                      -- FECHA SOLICITUD FACTURA
    fecha_emision       DATE NULL,
    fecha_vencimiento   DATE NULL,

    -- Importes
    importe             DECIMAL(14,2) NOT NULL DEFAULT 0.00,  -- Importe de la factura
    base_imponible      DECIMAL(14,2) NULL,
    iva                 DECIMAL(14,2) NULL,
    retencion           DECIMAL(14,2) NULL,
    total               DECIMAL(14,2) NULL,

    -- Estado
    estado              ENUM('pendiente','solicitada','emitida','enviada','cobrada_parcial','cobrada','vencida','anulada')
                        NOT NULL DEFAULT 'pendiente',
    autofactura         BOOLEAN NOT NULL DEFAULT FALSE,  -- Repsol usa autofacturación

    -- Sociedad (Moeve)
    sociedad            VARCHAR(180) NULL,               -- SOCIEDAD (solo Moeve: emisor de la factura)

    observaciones       TEXT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    INDEX idx_facturas_trabajo (id_trabajo, id_contexto),
    INDEX idx_facturas_numero (numero_factura, id_contexto),
    INDEX idx_facturas_estado (estado, id_contexto),
    INDEX idx_facturas_fecha (fecha_emision, id_contexto)
);
```

### 3.14 `factura_pedidos` — NUEVA (relación N:M)

Una factura puede cubrir varios pedidos. Un pedido puede facturarse en varias facturas.

```sql
CREATE TABLE factura_pedidos (
    id_factura_pedido   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_factura          BIGINT UNSIGNED NOT NULL,
    id_pedido           BIGINT UNSIGNED NOT NULL,
    importe_aplicado    DECIMAL(14,2) NULL,     -- Cuánto de esta factura va a este pedido
    created_at          TIMESTAMP NULL,
    UNIQUE KEY uq_factura_pedido (id_factura, id_pedido)
);
```

### 3.15 `usuario_contextos` — NUEVA (reemplaza el 1:1 actual)

El sistema actual asigna **un solo contexto** por usuario (`usuarios.id_contexto`). Pero se necesita que un usuario pueda tener acceso a Moeve, Repsol o ambos.

```sql
CREATE TABLE usuario_contextos (
    id_usuario_contexto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario          BIGINT UNSIGNED NOT NULL,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    es_contexto_principal BOOLEAN NOT NULL DEFAULT FALSE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL,
    UNIQUE KEY uq_usuario_contexto (id_usuario, id_contexto)
);
```

El campo `usuarios.id_contexto` se mantiene como **contexto activo/por defecto** pero ya no limita el acceso. La autorización real se basa en `usuario_contextos`.

### 3.16 `importaciones` — NUEVA

```sql
CREATE TABLE importaciones (
    id_importacion      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NOT NULL,
    id_usuario          BIGINT UNSIGNED NOT NULL,
    tipo                ENUM('estaciones','trabajos','tarifario','facturas') NOT NULL,
    archivo_original    VARCHAR(255) NOT NULL,     -- Nombre del archivo subido
    total_filas         INT UNSIGNED NOT NULL DEFAULT 0,
    filas_importadas    INT UNSIGNED NOT NULL DEFAULT 0,
    filas_con_error     INT UNSIGNED NOT NULL DEFAULT 0,
    filas_duplicadas    INT UNSIGNED NOT NULL DEFAULT 0,
    estado              ENUM('subido','validando','validado','importando','completado','fallido')
                        NOT NULL DEFAULT 'subido',
    version_importacion INT UNSIGNED NOT NULL DEFAULT 1, -- Para tracking de reimportaciones
    started_at          DATETIME NULL,
    finished_at         DATETIME NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL
);
```

### 3.17 `importacion_filas` — NUEVA (staging)

```sql
CREATE TABLE importacion_filas (
    id_importacion_fila BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_importacion      BIGINT UNSIGNED NOT NULL,
    numero_fila         INT UNSIGNED NOT NULL,
    datos_json          JSON NOT NULL,             -- Fila completa del Excel en JSON
    estado              ENUM('pendiente','valido','error','duplicado','importado') NOT NULL DEFAULT 'pendiente',
    mensaje_error       TEXT NULL,
    id_registro_destino BIGINT UNSIGNED NULL,      -- ID del registro creado si se importó OK
    tipo_registro_destino VARCHAR(50) NULL,        -- 'trabajo', 'estacion', etc.
    created_at          TIMESTAMP NULL,
    INDEX idx_import_filas_importacion (id_importacion, estado)
);
```

### 3.18 `audit_log` — NUEVA

```sql
CREATE TABLE audit_log (
    id_audit            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto         BIGINT UNSIGNED NULL,
    id_usuario          BIGINT UNSIGNED NULL,
    accion              ENUM('crear','editar','eliminar','cerrar','reabrir','importar') NOT NULL,
    tabla               VARCHAR(80) NOT NULL,
    registro_id         BIGINT UNSIGNED NOT NULL,
    datos_anteriores    JSON NULL,
    datos_nuevos        JSON NULL,
    ip                  VARCHAR(45) NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_tabla_registro (tabla, registro_id),
    INDEX idx_audit_usuario (id_usuario),
    INDEX idx_audit_fecha (created_at)
);
```

### 3.19 Tablas que se mantienen sin cambios

- `cache`, `cache_locks` — Framework Laravel
- `sessions` — Framework Laravel
- `jobs`, `job_batches`, `failed_jobs` — Queue Laravel
- `personal_access_tokens` — Sanctum
- `password_reset_tokens` — Auth Laravel
- `contactos` — Estructura OK
- `contactos_empresas` — Estructura OK
- `direcciones`, `telefonos`, `emails` — Estructura OK (polimórficas por FK)
- `cobros` — Estructura OK, solo cambiar FK de `id_factura` a la nueva tabla
- `comentarios_legalizaciones` → renombrar a `comentarios` con campo `commentable_type`/`commentable_id` o mantener como está.
- `legalizaciones` — Cambiar FK de `id_proyecto` a `id_trabajo`
- `legalizaciones_contactos` — Sin cambios

### 3.20 Tablas que se eliminan o reemplazan

| Tabla actual            | Acción          | Reemplazo                                                                                                                |
| ----------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `proyectos`             | **REEMPLAZAR**  | `trabajos`                                                                                                               |
| `proyectos_workplan`    | **SIMPLIFICAR** | Integrar estados en `trabajos.estado` + estados de pedido                                                                |
| `proyectos_comentarios` | **MOVER**       | Tabla `comentarios` genérica o mantener con FK a `trabajos`                                                              |
| `servicios`             | **REEMPLAZAR**  | `tarifario_lineas` (370 líneas reales vs 6 genéricas)                                                                    |
| `tarifario_servicios`   | **REEMPLAZAR**  | `tarifario_lineas`                                                                                                       |
| `pedidos_lineas`        | **REEMPLAZAR**  | `pedido_items`                                                                                                           |
| `facturas_lineas`       | **EVALUAR**     | Mantener si se quieren líneas detalladas por factura, o simplificar ya que en los Excel la factura es un importe directo |
| `presupuestos`          | **MANTENER**    | Sin cambios de estructura                                                                                                |
| `presupuestos_lineas`   | **MANTENER**    | Sin cambios                                                                                                              |

---

## 4. Estrategia para estaciones

### 4.1 Cómo guardar sin mezclar clientes

- Cada estación tiene `id_contexto` que la vincula al cliente.
- `ContextScope` global scope ya filtra automáticamente.
- Las tablas de extensión (`estaciones_moeve_ext`, `estaciones_repsol_ext`) son 1:1 con la base.
- En la interfaz, el usuario solo ve estaciones de su contexto activo.
- La búsqueda por estación siempre incluye filtro por contexto.

### 4.2 Cómo importar una sola vez los listados maestros

1. Se importa `LISTADO EESS Repsol` una sola vez → tabla `estaciones_servicio` (3.275 filas) + `estaciones_repsol_ext`.
2. Se importa `España 16-03-26` de Moeve → tabla `estaciones_servicio` (3.583 filas) + `estaciones_moeve_ext`.
3. Se importa `TARIFA 23-27` una sola vez → tabla `tarifario_lineas` (370 filas para Repsol).
4. Cada importación queda registrada en `importaciones` con su versión.

### 4.3 Cómo actualizar listados

1. El usuario sube una nueva versión del Excel de estaciones.
2. El sistema lo procesa a `importacion_filas` (staging).
3. Se compara contra maestro actual: nuevas, modificadas, de baja.
4. Se muestra previsualización de cambios al usuario.
5. Si confirma, se aplican los cambios y se incrementa la versión.
6. Las estaciones dadas de baja se marcan `activo=FALSE` (soft delete), nunca se eliminan.

### 4.4 Cómo relacionar estaciones con trabajos

`trabajos.id_estacion_servicio` → FK a `estaciones_servicio`. Ambos comparten `id_contexto`, por lo que es imposible vincular un trabajo Moeve a una estación Repsol (la FK compuesta lo impide).

### 4.5 Estación "0" o especiales

- Trabajos que no tienen estación concreta: `id_estacion_servicio = NULL`.
- Estudios, normalizaciones, etc.: se crean como tipos de trabajo donde la estación es opcional.
- No se crean estaciones ficticias (evita contaminar el maestro).

---

## 5. RBAC y visibilidad

### 5.1 Estructura de roles rediseñada

```
ROLES FUNCIONALES (qué puede hacer):
├── admin           → Todo + gestión de usuarios/roles/config
├── gestor          → CRUD trabajos, pedidos, facturas, tarifarios, estaciones, legalizaciones, cobros, reportes
├── tecnico         → Ver/editar trabajos asignados, ver pedidos, ver facturas, legalizaciones
├── consulta        → Solo lectura de todo
└── control_cierre  → Editar/reabrir trabajos cerrados + lectura general

SCOPES DE CLIENTE (qué ve):
├── usuario_contextos → N:M entre usuario y contexto
└── contexto_activo   → El contexto que el usuario tiene seleccionado en sesión
```

**Decisión: Roles funcionales + Scope por contexto, NO roles por cliente.**

No creo `gestor_moeve` ni `gestor_repsol` como roles separados. En su lugar:

- Un gestor con acceso solo a Moeve → `usuario_contextos` = [MOEVE].
- Un gestor con acceso solo a Repsol → `usuario_contextos` = [REPSOL].
- Un gestor con acceso a ambos → `usuario_contextos` = [MOEVE, REPSOL].

**Justificación:** Si mañana llega un tercer cliente, no quiero crear `gestor_cliente3`. El scope de visibilidad es ortogonal al rol funcional. Se gestionan por separado.

### 5.2 Tabla de permisos ampliada

A los 25 permisos existentes, añadir:

| Slug                        | Descripción                     |
| --------------------------- | ------------------------------- |
| `trabajos.ver`              | Ver listado de trabajos         |
| `trabajos.crear`            | Crear trabajos                  |
| `trabajos.editar`           | Editar trabajos                 |
| `trabajos.cerrar`           | Cerrar trabajos                 |
| `trabajos.reabrir`          | Reabrir trabajos                |
| `trabajos_cerrados.editar`  | Editar trabajos cerrados        |
| `trabajos_cerrados.reabrir` | Reabrir trabajos cerrados       |
| `importaciones.ejecutar`    | Ejecutar importaciones de Excel |
| `importaciones.ver`         | Ver historial de importaciones  |
| `auditoria.ver`             | Ver log de auditoría            |
| `config.gestionar`          | Gestión de configuración global |

Se eliminan los permisos de `proyectos.*` y se sustituyen por `trabajos.*`.

### 5.3 Cómo ocultar sidebar, rutas, tablas y columnas

**Backend (Laravel):**

```php
// Middleware ContextAccess — se aplica a todas las rutas auth
public function handle($request, Closure $next)
{
    $user = $request->user();
    $contextoActivo = session('contexto_activo', $user->id_contexto);

    // Verificar que el usuario tiene acceso a ese contexto
    if (!$user->contextos->contains('id_contexto', $contextoActivo)) {
        abort(403);
    }

    // Setear el contexto en el request para uso de ContextScope
    app()->instance('contexto_activo', $contextoActivo);

    return $next($request);
}
```

**Frontend (React/Inertia):**

1. El controlador pasa al frontend: `auth.user.permissions`, `auth.user.contextos`, `auth.user.contexto_activo`.
2. El layout lee el contexto activo y determina:
    - Qué ítems del sidebar mostrar (por permisos).
    - Qué columnas mostrar en tablas (por contexto: Moeve no tiene ORDEN MANTEN., Repsol no tiene CONTRATO).
    - Qué filtros mostrar (Moeve tiene Categoría, Repsol tiene Tipo de Documento).

3. **Componente `<ContextSwitcher />`:** Si el usuario tiene acceso a más de un contexto, aparece un selector en el header. Al cambiar, se hace POST a `/api/v1/auth/contexto` que actualiza la sesión, y luego `router.reload()`.

4. **No renderizar datos de otros contextos:** Los controllers SIEMPRE filtran por contexto activo usando el ContextScope. No es posible que lleguen datos Repsol a un usuario con contexto Moeve activo.

### 5.4 Matriz de visibilidad

| Módulo sidebar    | admin | gestor | tecnico             | consulta          | cierre |
| ----------------- | ----- | ------ | ------------------- | ----------------- | ------ |
| Dashboard         | ✅    | ✅     | ✅                  | ✅                | ✅     |
| Trabajos          | ✅    | ✅     | ✅ (solo asignados) | ✅ (solo lectura) | ✅     |
| Pedidos           | ✅    | ✅     | ✅ (lectura)        | ✅ (lectura)      | ✅     |
| Facturas          | ✅    | ✅     | ✅ (lectura)        | ✅ (lectura)      | ✅     |
| Cobros            | ✅    | ✅     | ❌                  | ❌                | ✅     |
| Estaciones        | ✅    | ✅     | ✅ (lectura)        | ✅ (lectura)      | ✅     |
| Tarifarios        | ✅    | ✅     | ❌                  | ✅ (lectura)      | ✅     |
| Legalizaciones    | ✅    | ✅     | ✅                  | ✅ (lectura)      | ✅     |
| Clientes/Empresas | ✅    | ✅     | ❌                  | ❌                | ✅     |
| Importaciones     | ✅    | ✅     | ❌                  | ❌                | ❌     |
| Reportes          | ✅    | ✅     | ❌                  | ✅                | ✅     |
| Cierre            | ✅    | ❌     | ❌                  | ❌                | ✅     |
| Administración    | ✅    | ❌     | ❌                  | ❌                | ❌     |

---

## 6. Propuesta de interfaz

### 6.1 Barra lateral

```
📊 Dashboard
📋 Trabajos          ← antes "Obras"
   └─ Listado
   └─ Crear trabajo
📦 Pedidos
   └─ Listado
📄 Facturas
   └─ Listado
   └─ Facturación pendiente
💰 Cobros            ← solo gestor+
🏪 Estaciones
   └─ Listado
   └─ Importar
📐 Tarifarios
   └─ Listado
   └─ Importar
⚖️ Legalizaciones
👥 Clientes          ← solo gestor+
📥 Importaciones     ← solo gestor+
📊 Reportes
🔒 Cierre            ← solo control_cierre
⚙️ Administración    ← solo admin
```

### 6.2 Cómo adaptar tablas según cliente

**Tabla de trabajos:** columnas dinámicas basadas en `contexto_activo`.

```jsx
// Configuración de columnas por contexto
const COLUMN_CONFIG = {
    MOEVE: {
        mostrar: [
            "numero",
            "estacion",
            "descripcion",
            "pedido",
            "importe",
            "facturacion_solicitada",
            "facturado",
            "fecha_encargo",
            "fecha_terminacion",
            "servicio",
            "responsable_ciete",
            "responsable_moeve",
            "observaciones",
            "factura",
            "contrato",
            "status",
            "categoria",
        ],
        ocultar: [
            "orden_mto",
            "aviso",
            "codigo_servicio",
            "numero_tarifa",
            "uds_solicitadas",
            "importe_solicitado",
            "zona",
            "2a_factura",
        ],
    },
    REPSOL: {
        mostrar: [
            "numero",
            "estacion",
            "nombre",
            "localidad",
            "provincia",
            "aviso",
            "fecha_solicitud",
            "orden_mto",
            "pedido",
            "fecha_encargo",
            "tipo_trabajo",
            "descripcion_trabajo",
            "fecha_terminacion",
            "codigo_servicio",
            "numero_tarifa",
            "servicio",
            "precio",
            "uds_pedido",
            "importe_pedido",
            "uds_solicitadas",
            "importe_solicitado",
            "importe_facturado",
            "responsable_ciete",
            "responsable_repsol",
            "observaciones",
            "factura_1",
            "factura_2",
            "status",
        ],
        ocultar: ["contrato", "categoria", "factura_ccp"],
    },
};
```

**Principio:** El frontend recibe un array `visibleColumns` del backend (basado en contexto). Solo renderiza esas columnas. El backend nunca envía campos irrelevantes para el contexto activo.

### 6.3 Cómo evitar una interfaz Frankenstein

1. **Un solo diseño.** La estructura visual (sidebar, header, tabla principal, filtros superiores, paginación inferior) es idéntica para Moeve y Repsol. Lo que cambia son las columnas y los filtros, no el layout.
2. **Identidad visual por contexto.** El header muestra el nombre del contexto activo y un color de acento sutil (azul Moeve, rojo Repsol). Nada más. No se cambia la tipografía ni la estructura.
3. **Columnas comunes primero.** Las columnas compartidas (estación, descripción, importe, fecha, estado) siempre están a la izquierda. Las específicas del cliente van a la derecha.
4. **Filtros contextuales.** Moeve muestra filtro por "Categoría". Repsol muestra filtro por "Tipo de Documento" + "Zona". Ambos comparten filtros de fecha, estado, estación, responsable.

### 6.4 Coherencia visual y rapidez de uso

- **Atajos de teclado** para navegación rápida (Ctrl+K para búsqueda global).
- **Búsqueda global** por estación, pedido, factura, responsable.
- **Filtros guardados** por usuario: el gestor puede guardar sus combinaciones de filtros habituales.
- **Tablas con virtualización** (TanStack Table + virtualización de filas) para renderizar miles de filas sin lag.
- **Sin paginación por defecto en tablas pequeñas** (< 100 filas). Paginación backend para tablas grandes (> 100).

### 6.5 Implementación concreta: cómo se parametriza por contexto en las 3 capas

La tabla `trabajos` es una sola, pero el contexto activo del usuario determina **qué se valida, qué se devuelve y qué se renderiza**. No hay bifurcación de código — hay parametrización en 3 capas.

#### Capa 1 — Validación backend (FormRequest con reglas dinámicas)

```php
// app/Http/Requests/TrabajoRequest.php
class TrabajoRequest extends FormRequest
{
    public function rules(): array
    {
        // Reglas comunes a ambos clientes
        $rules = [
            'id_estacion_servicio' => 'nullable|exists:estaciones_servicio,id_estacion_servicio',
            'descripcion_trabajo'  => 'nullable|string|max:2000',
            'fecha_encargo'        => 'nullable|date',
            'fecha_terminacion'    => 'nullable|date|after_or_equal:fecha_encargo',
            'responsable_cliente'  => 'nullable|string|max:150',
            'observaciones'        => 'nullable|string',
        ];

        $contexto = $this->user()->contexto_activo; // 1=MOEVE, 2=REPSOL

        if ($contexto === 1) { // MOEVE
            // Moeve EXIGE contrato y categoría, NO exige tipo_documento ni código servicio
            $rules['id_contrato']   = 'required|exists:contratos,id_contrato';
            $rules['categoria']     = 'required|string|max:100';
            // Campos Repsol no se validan (ignorados aunque lleguen)
        } else { // REPSOL
            // Repsol EXIGE tipo_documento y tipo_trabajo, NO exige contrato
            $rules['id_tipo_documento'] = 'required|exists:tipos_documento,id_tipo_documento';
            $rules['id_tipo_trabajo']   = 'required|exists:tipos_trabajo,id_tipo_trabajo';
            $rules['numero_aviso']      = 'nullable|string|max:100';
            $rules['orden_mantenimiento'] = 'nullable|string|max:100';
            // Campos Moeve no se validan
        }

        return $rules;
    }
}
```

#### Capa 2 — Serialización API (Resource con campos selectivos)

```php
// app/Http/Resources/TrabajoResource.php
class TrabajoResource extends JsonResource
{
    public function toArray($request): array
    {
        // Bloque común — siempre presente, independiente del contexto
        $base = [
            'id'                 => $this->id_trabajo,
            'numero'             => $this->numero_trabajo,
            'estacion'           => $this->estacion?->codigo_estacion,
            'estacion_nombre'    => $this->estacion?->nombre,
            'descripcion'        => $this->descripcion_trabajo,
            'estado'             => $this->estado,
            'importe'            => $this->pedidos->sum('importe_pedido'),
            'responsable_ciete'  => $this->responsableCiete?->nombre,
            'fecha_encargo'      => $this->fecha_encargo?->format('Y-m-d'),
            'fecha_terminacion'  => $this->fecha_terminacion?->format('Y-m-d'),
            'observaciones'      => $this->observaciones,
        ];

        // Bloque específico — solo los campos relevantes al contexto
        if ($this->id_contexto === 1) { // MOEVE
            $base['contrato']         = $this->contrato?->codigo_contrato;
            $base['categoria']        = $this->categoria;
            $base['responsable_moeve']= $this->responsable_cliente;
            $base['factura']          = $this->facturas->first()?->numero_factura;
            $base['factura_ccp']      = $this->facturas->first()?->numero_factura_ccp;
        } else { // REPSOL
            $base['tipo_documento']     = $this->tipoDocumento?->nombre;
            $base['tipo_trabajo']       = $this->tipoTrabajo?->nombre;
            $base['aviso']              = $this->numero_aviso;
            $base['orden_mto']          = $this->orden_mantenimiento;
            $base['codigo_servicio']    = $this->pedidoItems->first()?->codigo_servicio;
            $base['numero_tarifa']      = $this->pedidoItems->first()?->numero_tarifa;
            $base['responsable_repsol'] = $this->responsable_cliente;
            $base['factura_1']          = $this->facturas->where('orden_factura', 1)->first()?->numero_factura;
            $base['factura_2']          = $this->facturas->where('orden_factura', 2)->first()?->numero_factura;
        }

        return $base;
    }
}
```

#### Capa 2b — Controlador (pasa columnas visibles y filtros al frontend)

```php
// app/Http/Controllers/TrabajoController.php
class TrabajoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $contexto = $user->contexto_activo;

        $trabajos = Trabajo::with(['estacion', 'contrato', 'tipoDocumento', 'tipoTrabajo',
                                    'responsableCiete', 'pedidos', 'facturas'])
            ->when($request->estado, fn($q, $e) => $q->where('estado', $e))
            ->when($request->estacion, fn($q, $e) => $q->where('id_estacion_servicio', $e))
            ->cursorPaginate(50);

        return Inertia::render('Trabajos/Index', [
            'trabajos'  => TrabajoResource::collection($trabajos),
            'columnas'  => $this->getColumnasVisibles($contexto),
            'filtros'   => $this->getFiltrosDisponibles($contexto),
            'contexto'  => $contexto,
        ]);
    }

    /**
     * Columnas comunes + específicas según el contexto activo.
     * El frontend solo renderiza estas columnas en la tabla.
     */
    private function getColumnasVisibles(int $contexto): array
    {
        $comunes = ['numero', 'estacion', 'descripcion', 'importe', 'estado',
                    'responsable_ciete', 'fecha_encargo', 'fecha_terminacion', 'observaciones'];

        if ($contexto === 1) { // MOEVE
            return array_merge($comunes, [
                'contrato', 'categoria', 'responsable_moeve', 'factura', 'factura_ccp'
            ]);
        }

        // REPSOL
        return array_merge($comunes, [
            'tipo_documento', 'tipo_trabajo', 'aviso', 'orden_mto',
            'codigo_servicio', 'numero_tarifa', 'responsable_repsol',
            'factura_1', 'factura_2'
        ]);
    }

    /**
     * Filtros disponibles según contexto.
     * Moeve filtra por categoría/contrato. Repsol por tipo_documento/zona.
     */
    private function getFiltrosDisponibles(int $contexto): array
    {
        $comunes = ['estado', 'estacion', 'responsable_ciete', 'fecha_desde', 'fecha_hasta'];

        if ($contexto === 1) { // MOEVE
            return array_merge($comunes, ['contrato', 'categoria']);
        }

        // REPSOL
        return array_merge($comunes, ['tipo_documento', 'tipo_trabajo', 'zona']);
    }
}
```

#### Capa 3 — Frontend React (tabla con columnas dinámicas)

```jsx
// resources/js/Pages/Trabajos/Index.jsx
export default function TrabajosIndex({
    trabajos,
    columnas,
    filtros,
    contexto,
}) {
    // Diccionario completo de TODAS las columnas posibles.
    // Solo se renderizarán las que estén en el array "columnas" del backend.
    const COLUMN_DEFS = {
        // — Comunes —
        numero: { header: "Nº", accessor: "numero" },
        estacion: { header: "Estación", accessor: "estacion" },
        descripcion: { header: "Descripción", accessor: "descripcion" },
        importe: { header: "Importe", accessor: "importe", type: "currency" },
        estado: { header: "Estado", accessor: "estado", type: "badge" },
        responsable_ciete: {
            header: "Resp. CIETE",
            accessor: "responsable_ciete",
        },
        fecha_encargo: {
            header: "Fecha Encargo",
            accessor: "fecha_encargo",
            type: "date",
        },
        fecha_terminacion: {
            header: "Fecha Termin.",
            accessor: "fecha_terminacion",
            type: "date",
        },
        observaciones: { header: "Observaciones", accessor: "observaciones" },
        // — Solo Moeve —
        contrato: { header: "Contrato", accessor: "contrato" },
        categoria: { header: "Categoría", accessor: "categoria" },
        responsable_moeve: {
            header: "Resp. Moeve",
            accessor: "responsable_moeve",
        },
        factura: { header: "Factura", accessor: "factura" },
        factura_ccp: { header: "Factura CCP", accessor: "factura_ccp" },
        // — Solo Repsol —
        tipo_documento: { header: "Tipo Doc.", accessor: "tipo_documento" },
        tipo_trabajo: { header: "Tipo Trabajo", accessor: "tipo_trabajo" },
        aviso: { header: "Nº Aviso", accessor: "aviso" },
        orden_mto: { header: "Orden Mto.", accessor: "orden_mto" },
        codigo_servicio: {
            header: "Cód. Servicio",
            accessor: "codigo_servicio",
        },
        numero_tarifa: { header: "Nº Tarifa", accessor: "numero_tarifa" },
        responsable_repsol: {
            header: "Resp. Repsol",
            accessor: "responsable_repsol",
        },
        factura_1: { header: "1ª Factura", accessor: "factura_1" },
        factura_2: { header: "2ª Factura", accessor: "factura_2" },
    };

    // Solo las columnas que el backend dice que son visibles para este contexto
    const activeColumns = useMemo(
        () => columnas.map((key) => COLUMN_DEFS[key]).filter(Boolean),
        [columnas],
    );

    return (
        <AuthenticatedLayout>
            <FiltrosTrabajos filtrosDisponibles={filtros} contexto={contexto} />
            <DataTable columns={activeColumns} data={trabajos.data} />
            <Pagination links={trabajos.meta} />
        </AuthenticatedLayout>
    );
}
```

#### Capa 3b — Frontend React (formulario con campos condicionales)

```jsx
// resources/js/Pages/Trabajos/Form.jsx
export default function TrabajoForm({
    contexto,
    trabajo,
    estaciones,
    tiposDocumento,
    tiposTrabajo,
    contratos,
}) {
    const { data, setData, post, put, errors } = useForm({
        id_estacion_servicio: trabajo?.estacion ?? "",
        descripcion_trabajo: trabajo?.descripcion ?? "",
        fecha_encargo: trabajo?.fecha_encargo ?? "",
        // Moeve
        id_contrato: trabajo?.contrato ?? "",
        categoria: trabajo?.categoria ?? "",
        // Repsol
        id_tipo_documento: trabajo?.tipo_documento ?? "",
        id_tipo_trabajo: trabajo?.tipo_trabajo ?? "",
        numero_aviso: trabajo?.aviso ?? "",
        orden_mantenimiento: trabajo?.orden_mto ?? "",
    });

    return (
        <form onSubmit={handleSubmit}>
            {/* ─── Campos comunes — visibles SIEMPRE ─── */}
            <EstacionSelect
                options={estaciones}
                value={data.id_estacion_servicio}
                onChange={(v) => setData("id_estacion_servicio", v)}
                error={errors.id_estacion_servicio}
            />
            <TextArea
                label="Descripción del trabajo"
                value={data.descripcion_trabajo}
                onChange={(v) => setData("descripcion_trabajo", v)}
                error={errors.descripcion_trabajo}
            />
            <DatePicker
                label="Fecha encargo"
                value={data.fecha_encargo}
                onChange={(v) => setData("fecha_encargo", v)}
                error={errors.fecha_encargo}
            />

            {/* ─── Campos MOEVE — solo si contexto es MOEVE ─── */}
            {contexto === 1 && (
                <>
                    <Select
                        label="Contrato"
                        options={contratos}
                        value={data.id_contrato}
                        onChange={(v) => setData("id_contrato", v)}
                        error={errors.id_contrato}
                        required
                    />
                    <Input
                        label="Categoría"
                        value={data.categoria}
                        onChange={(v) => setData("categoria", v)}
                        error={errors.categoria}
                        required
                    />
                </>
            )}

            {/* ─── Campos REPSOL — solo si contexto es REPSOL ─── */}
            {contexto === 2 && (
                <>
                    <Select
                        label="Tipo de Documento"
                        options={tiposDocumento}
                        value={data.id_tipo_documento}
                        onChange={(v) => setData("id_tipo_documento", v)}
                        error={errors.id_tipo_documento}
                        required
                    />
                    <Select
                        label="Tipo de Trabajo"
                        options={tiposTrabajo}
                        value={data.id_tipo_trabajo}
                        onChange={(v) => setData("id_tipo_trabajo", v)}
                        error={errors.id_tipo_trabajo}
                        required
                    />
                    <Input
                        label="Nº Aviso / P.KEOPS"
                        value={data.numero_aviso}
                        onChange={(v) => setData("numero_aviso", v)}
                        error={errors.numero_aviso}
                    />
                    <Input
                        label="Orden Mantenimiento"
                        value={data.orden_mantenimiento}
                        onChange={(v) => setData("orden_mantenimiento", v)}
                        error={errors.orden_mantenimiento}
                    />
                </>
            )}

            <SubmitButton />
        </form>
    );
}
```

### 6.6 Diagrama de flujo: cómo el contexto parametriza las 3 capas

```
                    BACKEND                              FRONTEND
                ┌───────────────────┐               ┌──────────────────────┐
                │ TrabajoRequest    │               │ Trabajos/Index.jsx    │
   Moeve user → │ Validate:         │               │ Columnas visibles:    │
                │  contrato ✅       │   Resource    │  contrato ✅           │
                │  categoria ✅      │ → filtra   → │  categoria ✅          │
                │  tipo_doc ❌(skip) │   campos      │  aviso ❌              │
                │  aviso ❌(skip)    │   por ctx     │  orden_mto ❌          │
                └───────────────────┘               └──────────────────────┘

                ┌───────────────────┐               ┌──────────────────────┐
                │ TrabajoRequest    │               │ Trabajos/Index.jsx    │
  Repsol user → │ Validate:         │               │ Columnas visibles:    │
                │  contrato ❌(skip) │   Resource    │  contrato ❌           │
                │  categoria ❌(skip)│ → filtra   → │  tipo_doc ✅           │
                │  tipo_doc ✅       │   campos      │  aviso ✅              │
                │  tipo_trab ✅      │   por ctx     │  orden_mto ✅          │
                └───────────────────┘               └──────────────────────┘

                          ↓ Ambos escriben en la misma tabla ↓

                     ┌──────────────────────────────────┐
                     │         tabla `trabajos`          │
                     │  (campos específicos son nullable │
                     │   — no rompen FK, no rompen nada) │
                     └──────────────────────────────────┘
```

**Resumen:** Un solo modelo Eloquent → un solo controlador → un solo componente React. El contexto activo determina:

1. **Qué valida** el FormRequest (campos required vs ignorados)
2. **Qué devuelve** el Resource (campos relevantes vs omitidos)
3. **Qué renderiza** el componente (columnas en tabla + campos en formulario)

No hay duplicación de código. No hay bifurcación de rutas. No hay componentes separados por cliente.

---

## 7. Rendimiento y escalabilidad

### 7.1 Cómo evitar renderizados innecesarios

1. **Backend:** Las API siempre filtran por `id_contexto`. Un endpoint `/api/v1/trabajos` con contexto Moeve NUNCA devuelve filas Repsol. No hay filtrado en frontend.
2. **Frontend:** React con `useMemo` para listas de columnas. `useCallback` para handlers. El `AuthenticatedLayout` usa `React.memo` para no re-renderizar sidebar al cambiar contenido.
3. **Inertia `only`:** Usar `router.reload({ only: ['trabajos'] })` para actualizar solo la tabla al cambiar filtros, sin recargar layout completo.

### 7.2 Paginación e indexación

**Índices compuestos:** Todas las queries principales van por `(id_contexto, campo_filtro)`. Los índices ya propuestos en la tabla `trabajos` cubren:

- Listado por contexto → `idx_trabajos_contexto`
- Búsqueda por estación → `idx_trabajos_estacion`
- Filtro por tipo de documento → `idx_trabajos_tipo_doc`
- Filtro por estado → `idx_trabajos_estado`
- Filtro por responsable → `idx_trabajos_responsable`
- Ordenar por fecha → `idx_trabajos_fecha_encargo`

**Paginación:**

- Tabla de trabajos: paginación server-side con cursor pagination (más eficiente que offset para tablas grandes).
- 50 filas por página por defecto, configurable.
- Conteo total en background (`COUNT(*)` cacheado 30 segundos).

### 7.3 Búsquedas

```
GET /api/v1/busqueda?q=33785&tipo=estacion
GET /api/v1/busqueda?q=7300564645&tipo=pedido
GET /api/v1/busqueda?q=R24110&tipo=factura
```

Búsqueda global que busca en estaciones (código, nombre), pedidos (número), facturas (número) y trabajos (descripción). Siempre filtrado por contexto activo.

**Índice fulltext** opcional para `trabajos.descripcion_trabajo` y `estaciones_servicio.nombre`.

### 7.4 Carga de contexto

- Al hacer login, se carga el contexto principal del usuario.
- Se almacena en sesión (`session('contexto_activo')`).
- Todas las queries usan el ContextScope global que filtra por ese valor.
- Al cambiar contexto (para usuarios multi-contexto), se actualiza sesión y se hace recarga completa de datos.
- La sidebar y los filtros se regeneran en el servidor al cambiar contexto (no hay caché stale).

---

## 8. Importación de Excel

### 8.1 Flujo de importación

```
1. SUBIDA        → Usuario sube Excel → se guarda en storage/app/imports/
2. PARSING       → Job async parsea Excel con openpyxl-php (PhpSpreadsheet)
                    → cada fila → importacion_filas (JSON)
3. VALIDACIÓN    → Job valida cada fila:
                    - ¿Existe estación? ¿Código correcto?
                    - ¿Existe código de tarifa?
                    - ¿Es un trabajo duplicado (mismo Nº + estación + tipo)?
                    - Marca filas como 'valido', 'error' o 'duplicado'
4. PREVISUALIZACIÓN → Pantalla de revisión:
                    - Filas válidas (verde)
                    - Filas con errores (rojo) → con mensaje explicativo
                    - Filas duplicadas (amarillo)
                    - Botón "Importar N filas válidas"
5. IMPORTACIÓN   → Job async crea registros en tablas finales
                    → actualiza importacion_filas.id_registro_destino
6. CONFIRMACIÓN  → Pantalla de resumen final
```

### 8.2 Qué entra a staging y qué a tablas finales

| Dato                      | Staging primero    | Directo a tabla final        |
| ------------------------- | ------------------ | ---------------------------- |
| Trabajos                  | ✅ Siempre staging | ❌                           |
| Estaciones (maestro)      | ✅ Siempre staging | ❌                           |
| Tarifario (maestro)       | ✅ Siempre staging | ❌                           |
| Tipos de trabajo (Rangos) | ❌                 | ✅ (pocos, se crean directo) |

### 8.3 Validación según cliente

| Validación                | Moeve          | Repsol                            |
| ------------------------- | -------------- | --------------------------------- |
| Estación requerida        | Sí (por Nº ES) | Sí (por Nº ES = C.EMP)            |
| Código servicio requerido | No             | Sí                                |
| Número tarifa requerido   | No             | Depende del tipo de documento     |
| Contrato requerido        | Sí             | No                                |
| Categoría requerida       | Sí             | No                                |
| Tipo de trabajo requerido | No             | Sí                                |
| Nº Aviso / Orden Mto.     | No             | Opcional (algunos lo dejan vacío) |

### 8.4 Cómo evitar duplicados

- **Clave de deduplicación para trabajos:** `(id_contexto, id_tipo_documento, numero_trabajo)`.
- **Clave de deduplicación para estaciones:** `(id_contexto, codigo_estacion)`.
- **Clave de deduplicación para pedidos:** `(id_contexto, numero_pedido, codigo_servicio)` — porque un mismo pedido puede tener múltiples líneas de tarifa.

### 8.5 Versionado de importaciones

- Cada importación genera un registro en `importaciones` con `version_importacion` auto-incrementado.
- Se puede consultar el historial completo: "¿Cuándo se importó la última versión del listado de estaciones?"
- Si se reimporta un Excel actualizado, se comparan filas staging contra BD actual y se muestran solo los cambios.

### 8.6 Maestros vs operativos

| Tipo de Excel                | Clasificación  | Frecuencia de importación                           |
| ---------------------------- | -------------- | --------------------------------------------------- |
| LISTADO EESS Moeve           | **Maestro**    | Cuando llega nueva versión (trimestral aprox.)      |
| LISTADO EESS Repsol          | **Maestro**    | Cuando llega nueva versión                          |
| TARIFA 23-27                 | **Maestro**    | Cada renovación de contrato (cada 4 años)           |
| Rangos (Tipos de trabajo)    | **Maestro**    | Rara vez (se crean manualmente o en 1ª importación) |
| Control Trabajos Moeve       | **Operativo**  | Importación inicial + importaciones incrementales   |
| Control Trabajos Repsol (×9) | **Operativo**  | Importación inicial + importaciones incrementales   |
| FACTURAS EMITIDAS Moeve      | **Operativo**  | Para cargar histórico                               |
| Email Cesar / Mapeo          | **Referencia** | No se importan, solo documentación                  |

---

## 9. Plan de implementación

> **NOTA IMPORTANTE:** La BD actual solo tiene datos de desarrollo/demo. No hay producción ni datos reales. Estamos en local (XAMPP). **Podemos hacer borrón y cuenta nueva** sin preocuparnos por migrar datos existentes. Esto simplifica enormemente el plan.

### 9.1 Estrategia: `migrate:fresh` con esquema limpio

En lugar de migraciones incrementales que alteren tablas existentes, vamos a:

1. **Reescribir las migraciones desde cero** con el esquema nuevo.
2. **Borrar las migraciones antiguas** que ya no aplican.
3. **Ejecutar `php artisan migrate:fresh --seed`** para recrear la BD limpia.
4. **No hay datos que preservar** — los seeders cargarán los datos base necesarios.

### 9.2 Migraciones (orden de ejecución)

```
database/migrations/
├── 2026_03_24_000001_create_framework_support_tables.php    ← SIN CAMBIOS (cache, sessions, jobs)
├── 2026_03_24_000002_create_personal_access_tokens_table.php ← SIN CAMBIOS
├── 2026_03_24_000010_create_security_core_tables.php         ← REESCRIBIR (contextos, roles, permisos + usuario_contextos)
├── 2026_03_24_000015_create_maestros_tables.php              ← NUEVA (tipos_documento, tipos_trabajo, contratos, unidades)
├── 2026_03_24_000020_create_empresas_contactos_base_tables.php ← SIN CAMBIOS
├── 2026_03_24_000025_create_estaciones_tables.php            ← NUEVA (estaciones_servicio + ext moeve + ext repsol)
├── 2026_03_24_000030_create_security_users_tables.php        ← REESCRIBIR (usuarios + usuario_roles + usuario_contextos + sesiones_login)
├── 2026_03_24_000035_create_tarifarios_tables.php            ← NUEVA (tarifarios + tarifario_lineas)
├── 2026_03_24_000040_create_comunicacion_operativa_base_tables.php ← SIN CAMBIOS (direcciones, telefonos, emails)
├── 2026_03_24_000050_create_trabajos_operativa_tables.php    ← NUEVA (trabajos, pedidos, pedido_items, facturas, factura_pedidos, cobros)
├── 2026_03_24_000055_create_presupuestos_tables.php          ← NUEVA (presupuestos + presupuestos_lineas)
├── 2026_03_24_000060_create_legalizaciones_tables.php        ← REESCRIBIR (FK a trabajos en vez de proyectos)
├── 2026_03_24_000070_create_importacion_tables.php           ← NUEVA (importaciones + importacion_filas)
├── 2026_03_24_000080_create_audit_log_table.php              ← NUEVA
```

**Migraciones a ELIMINAR (ya no aplican):**

- `000050_create_flujo_negocio_tables.php` (proyectos, servicios, tarifario_servicios → reemplazadas)
- `001000_add_avatar_key_to_usuarios_table.php` (se integra en la nueva migración de usuarios)
- Migración de rename Cepsa→Moeve (ya no hace falta: el esquema nuevo ya dice MOEVE)

### 9.3 Modelos Eloquent (crear/reescribir)

| Modelo              | Acción                                                    |
| ------------------- | --------------------------------------------------------- |
| `Trabajo`           | **NUEVO** (reemplaza Proyecto)                            |
| `TipoDocumento`     | **NUEVO**                                                 |
| `TipoTrabajo`       | **NUEVO**                                                 |
| `Contrato`          | **NUEVO**                                                 |
| `TarifarioLinea`    | **NUEVO**                                                 |
| `Pedido`            | **REESCRIBIR** (FK a trabajo, no a proyecto)              |
| `PedidoItem`        | **NUEVO** (reemplaza PedidoLinea)                         |
| `Factura`           | **REESCRIBIR** (FK a trabajo, doble factura, factura_ccp) |
| `FacturaPedido`     | **NUEVO** (pivot N:M)                                     |
| `EstacionServicio`  | **REESCRIBIR** (base común + relación con ext)            |
| `EstacionMoeveExt`  | **NUEVO**                                                 |
| `EstacionRepsolExt` | **NUEVO**                                                 |
| `UsuarioContexto`   | **NUEVO**                                                 |
| `Importacion`       | **NUEVO**                                                 |
| `ImportacionFila`   | **NUEVO**                                                 |
| `AuditLog`          | **NUEVO**                                                 |

### 9.4 Seeders (orden)

```
database/seeders/
├── ContextosClienteSeeder.php     ← MOEVE, REPSOL, OTRO
├── RolesSeeder.php                ← 5 roles funcionales
├── PermisosSeeder.php             ← 32 permisos (trabajos.*, importaciones.*, auditoria.*)
├── RolPermisosSeeder.php          ← Matriz roles×permisos
├── UnidadesSeeder.php             ← ud, h, m2, ml
├── TiposDocumentoSeeder.php       ← NUEVA: DISEÑO, EDIFICACION, OBRAS, etc.
├── TiposTrabajoSeeder.php         ← NUEVA: NPV, REFORMA, INDUSTRIA, etc. (Rangos)
├── EmpresasSeeder.php             ← MOEVE, REPSOL, CIETE
├── UsuariosInicialesSeeder.php    ← Admin + usuarios test + usuario_contextos
└── DatabaseSeeder.php             ← Orquesta todo en orden
```

### 9.5 Pasos de ejecución

```bash
# 1. Reescribir migraciones, modelos, seeders (implementación de código)
# 2. Borrar la BD y recrear limpia
php artisan migrate:fresh --seed

# 3. Verificar esquema
php artisan migrate:status

# 4. Importar maestros reales vía script o seeder:
#    - Estaciones Moeve (3.583)
#    - Estaciones Repsol (3.275)
#    - Tarifario 370 líneas
#    - Tipos de trabajo (Rangos)

# 5. Actualizar modelos, rutas, controladores, frontend

# 6. Importar datos operativos históricos (Excel → staging → tablas finales)
```

### 9.6 Qué pasa con los datos de los Excel

Los datos operativos de los 13 Excel NO se cargan en seeders — se cargarán mediante el módulo de importación (sección 8) una vez que el esquema y la UI estén listos. Los seeders solo cargan datos de configuración (roles, permisos, tipos, unidades, empresas, usuarios test).

### 9.2 Cómo migrar el naming "Cepsa" → "Moeve"

**Ya hecho en sesiones anteriores:**

- `cod_cepsa` → `cod_moeve` en migraciones.
- Datos seed de `contextos_cliente` y `empresas` actualizados.
- Textos en frontend (i18n) actualizados.
- CSS class names actualizados.

**Pendiente adicional para esta reestructuración:**

- Al crear `estaciones_servicio` nueva, NO habrá campo `cod_cepsa` ni `cod_moeve`. Solo `codigo_estacion`.
- El contexto `id=1` se llamará `MOEVE` (ya hecho).

### 9.3 Cómo eliminar TEO sin romper histórico

- TEO **no existe** en la BD actual (nunca se llegó a crear como columna).
- En los Excel de Moeve existe como columna pero el documento Mapeo dice "No se incluirá".
- Decisión: **No se importa.** Los datos históricos de TEO quedan en los Excel originales como archivo. No se pierde nada porque el campo no aporta valor operativo.

---

## 10. Propuesta final recomendada

### Arquitectura elegida

**Laravel 11 + Inertia.js + React 18 + MariaDB 10.4** (sin cambios de stack).

**Modelo de datos:** Tabla única `trabajos` con envolvente completa + campos nullable por cliente + tablas de extensión para estaciones + maestros reales de tarifario.

**Multi-tenancy:** `id_contexto` en todas las tablas operativas + `ContextScope` global scope + nueva tabla `usuario_contextos` para soporte multi-cliente por usuario.

**RBAC:** 5 roles funcionales (admin, gestor, tecnico, consulta, control_cierre) × N contextos. Sin roles por cliente.

### Tablas a crear (12 nuevas)

| Tabla                   | Propósito                                          |
| ----------------------- | -------------------------------------------------- |
| `trabajos`              | Entidad principal operativa (reemplaza proyectos)  |
| `tipos_documento`       | DISEÑO, EDIFICACIÓN, OBRAS, etc.                   |
| `tipos_trabajo`         | NPV, REFORMA, INDUSTRIA, etc. (por tipo_documento) |
| `contratos`             | Marco contractual (Moeve + OTROS Repsol)           |
| `tarifario_lineas`      | 370 líneas reales de tarifario                     |
| `pedido_items`          | Ítems de pedido con código tarifa                  |
| `factura_pedidos`       | Relación N:M factura↔pedido                        |
| `estaciones_moeve_ext`  | Extensión Moeve para estaciones                    |
| `estaciones_repsol_ext` | Extensión Repsol para estaciones                   |
| `usuario_contextos`     | Multi-contexto por usuario                         |
| `importaciones`         | Registro de importaciones                          |
| `importacion_filas`     | Staging de importación                             |
| `audit_log`             | Auditoría de cambios                               |

### Tablas a modificar (6)

| Tabla                 | Cambio                                                                 |
| --------------------- | ---------------------------------------------------------------------- |
| `estaciones_servicio` | Simplificar a base común, eliminar campos duplicados                   |
| `tarifarios`          | Añadir `id_contrato`, `factor_multiplicador`                           |
| `pedidos`             | Añadir `id_trabajo`, campos de control Repsol                          |
| `facturas`            | Añadir `id_trabajo`, `orden_factura`, `numero_factura_ccp`, `sociedad` |
| `legalizaciones`      | FK a `id_trabajo` en lugar de `id_proyecto`                            |
| `permisos`            | Añadir permisos de `trabajos.*`, `importaciones.*`, `auditoria.*`      |

### Tablas a eliminar (5, en Fase 5)

`proyectos`, `proyectos_workplan`, `proyectos_comentarios`, `servicios`, `tarifario_servicios`

### Permisos finales (32)

Los 25 actuales (renombrando `proyectos.* → trabajos.*`) + 7 nuevos: `trabajos.cerrar`, `trabajos.reabrir`, `trabajos_cerrados.editar`, `trabajos_cerrados.reabrir`, `importaciones.ejecutar`, `importaciones.ver`, `auditoria.ver`.

---

## 11. Decisión técnica recomendada

### Resumen ejecutivo

La base de datos actual `abaco_ciete` fue diseñada como una abstracción genérica de ERP. No encaja con la realidad operativa de los Excel de Moeve y Repsol. Necesita una reestructuración profunda pero controlada.

**Mi decisión concreta:**

1. **Tabla `trabajos` envolvente única** — con campos nullable para los específicos de cada cliente. No creo tablas separadas `trabajos_moeve`/`trabajos_repsol` porque el 80% de la lógica es compartida y duplicarla sería un error de mantenimiento.

2. **Tablas de extensión solo para estaciones** — porque la diferencia entre Moeve (92 cols) y Repsol (65 cols) es demasiado grande para una sola tabla. Se usa patrón 1:1 con tabla base común.

3. **RBAC funcional + scope contextual** — roles que definen qué puede hacer el usuario (gestor, técnico, consulta, cierre, admin) y scopes que definen qué puede ver (MOEVE, REPSOL, ambos). Son dimensiones independientes. Esto es más escalable que crear roles por cliente.

4. **Tarifario real de 370 líneas** — reemplazar los 6 servicios ficticios por las 370 líneas reales del tarifario Repsol y su equivalente Moeve. Es la columna vertebral de la facturación.

5. **Importación con staging obligatorio** — todo dato que entre desde Excel pasa por staging, validación, previsualización y confirmación. Sin excepciones. Es la única forma de mantener la integridad.

6. **Auditoría desde el día 1** — cada create/update/delete deja registro en `audit_log`. En un ERP de facturación, la trazabilidad no es opcional.

7. **Migración en 5 fases** — sin big bang. Cada fase es desplegable independientemente. Las tablas antiguas no se eliminan hasta la fase final.

8. **Frontend contextual por datos, no por diseño** — un solo layout, un solo diseño, un solo flujo. Lo que cambia son las columnas, los filtros y los datos. Nada de bifurcar la UI.

**Lo que NO haría:**

- No crearía microservicios. Es un ERP interno para ~20 usuarios. Un monolito bien estructurado es la decisión correcta.
- No crearía una API GraphQL. REST con filtros por query string cubre todo lo necesario.
- No usaría CQRS ni Event Sourcing. Overkill para este volumen. El audit_log cubre la trazabilidad.
- No migraría a PostgreSQL. MariaDB 10.4 es suficiente para este volumen de datos (~40K trabajos + ~7K estaciones).
- No crearía una tabla de "campos dinámicos" (EAV). Los campos son estables y conocidos. Meter datos en JSON o EAV es un antipatrón para búsquedas y reportes.

**Esta propuesta está lista para bajar a migraciones, modelos, seeders y controladores Laravel.**
