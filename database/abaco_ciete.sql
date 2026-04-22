CREATE DATABASE IF NOT EXISTS abaco_ciete CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE abaco_ciete;

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comentarios_legalizaciones;

DROP TABLE IF EXISTS legalizaciones_contactos;

DROP TABLE IF EXISTS legalizaciones;

DROP TABLE IF EXISTS cobros;

DROP TABLE IF EXISTS facturas_lineas;

DROP TABLE IF EXISTS facturas;

DROP TABLE IF EXISTS pedidos_lineas;

DROP TABLE IF EXISTS pedidos;

DROP TABLE IF EXISTS presupuestos_lineas;

DROP TABLE IF EXISTS presupuestos;

DROP TABLE IF EXISTS proyectos_comentarios;

DROP TABLE IF EXISTS proyectos_workplan;

DROP TABLE IF EXISTS proyectos;

DROP TABLE IF EXISTS tarifario_servicios;

DROP TABLE IF EXISTS tarifarios;

DROP TABLE IF EXISTS servicios;

DROP TABLE IF EXISTS unidades;

DROP TABLE IF EXISTS estaciones_servicio;

DROP TABLE IF EXISTS emails;

DROP TABLE IF EXISTS telefonos;

DROP TABLE IF EXISTS direcciones;

DROP TABLE IF EXISTS usuario_roles;

DROP TABLE IF EXISTS usuarios;

DROP TABLE IF EXISTS contactos_empresas;

DROP TABLE IF EXISTS contactos;

DROP TABLE IF EXISTS empresas;

DROP TABLE IF EXISTS rol_permisos;

DROP TABLE IF EXISTS permisos;

DROP TABLE IF EXISTS roles;

DROP TABLE IF EXISTS contextos_cliente;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS contextos_cliente (
    id_contexto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_contextos_cliente_nombre (nombre),
    UNIQUE KEY uq_contextos_cliente_codigo (codigo)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id_rol BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_nombre (nombre),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permisos (
    id_permiso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permisos_slug (slug)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permisos (
    id_rol_permiso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol BIGINT UNSIGNED NOT NULL,
    id_permiso BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rol_permisos (id_rol, id_permiso),
    KEY idx_rol_permisos_permiso (id_permiso),
    CONSTRAINT fk_rol_permisos_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rol_permisos_permiso FOREIGN KEY (id_permiso) REFERENCES permisos (id_permiso) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresas (
    id_empresa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    empresa_padre_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(180) NOT NULL,
    nombre_comercial VARCHAR(180) NULL,
    razon_social VARCHAR(220) NULL,
    cif VARCHAR(20) NULL,
    tipo_empresa ENUM(
        'cliente',
        'proveedor',
        'cliente_proveedor',
        'interna',
        'otra'
    ) NOT NULL DEFAULT 'cliente',
    web VARCHAR(255) NULL,
    observaciones TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresas_contexto_nombre (id_contexto, nombre),
    UNIQUE KEY uq_empresas_contexto_cif (id_contexto, cif),
    KEY idx_empresas_contexto (id_contexto),
    KEY idx_empresas_id_contexto (id_empresa, id_contexto),
    KEY idx_empresas_padre_contexto (empresa_padre_id, id_contexto),
    CONSTRAINT fk_empresas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_empresas_padre_contexto FOREIGN KEY (empresa_padre_id, id_contexto) REFERENCES empresas (id_empresa, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contactos (
    id_contacto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NULL,
    dni VARCHAR(20) NULL,
    cargo_general VARCHAR(150) NULL,
    observaciones TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_contactos_dni (dni)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contactos_empresas (
    id_contacto_empresa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa BIGINT UNSIGNED NOT NULL,
    id_contacto BIGINT UNSIGNED NOT NULL,
    puesto VARCHAR(150) NULL,
    categoria VARCHAR(100) NULL,
    es_responsable_principal TINYINT(1) NOT NULL DEFAULT 0,
    recibe_avisos TINYINT(1) NOT NULL DEFAULT 0,
    recibe_presupuestos TINYINT(1) NOT NULL DEFAULT 0,
    recibe_facturas TINYINT(1) NOT NULL DEFAULT 0,
    es_usuario TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_contactos_empresas_contexto_empresa_contacto (
        id_contexto,
        id_empresa,
        id_contacto
    ),
    KEY idx_contactos_empresas_contexto (id_contexto),
    KEY idx_contactos_empresas_contacto (id_contacto),
    KEY idx_contactos_empresas_id_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    KEY idx_contactos_empresas_empresa_contexto (id_empresa, id_contexto),
    CONSTRAINT fk_contactos_empresas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_contactos_empresas_empresa_contexto FOREIGN KEY (id_empresa, id_contexto) REFERENCES empresas (id_empresa, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_contactos_empresas_contacto FOREIGN KEY (id_contacto) REFERENCES contactos (id_contacto) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa BIGINT UNSIGNED NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NULL,
    nombre_usuario VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,
    email_verificado_at DATETIME NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(30) NULL,
    remember_token VARCHAR(100) NULL,
    ultimo_login_at DATETIME NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_contexto_nombre_usuario (id_contexto, nombre_usuario),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_contexto (id_contexto),
    KEY idx_usuarios_id_contexto (id_usuario, id_contexto),
    KEY idx_usuarios_contacto_empresa_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    CONSTRAINT fk_usuarios_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_usuarios_contacto_empresa_contexto FOREIGN KEY (
        id_contacto_empresa,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_roles (
    id_usuario_rol BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario BIGINT UNSIGNED NOT NULL,
    id_rol BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_roles_usuario_rol (id_usuario, id_rol),
    KEY idx_usuario_roles_rol (id_rol),
    CONSTRAINT fk_usuario_roles_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_usuario_roles_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS direcciones (
    id_direccion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa BIGINT UNSIGNED NULL,
    id_contacto BIGINT UNSIGNED NULL,
    id_contacto_empresa BIGINT UNSIGNED NULL,
    id_usuario BIGINT UNSIGNED NULL,
    tipo ENUM(
        'fiscal',
        'social',
        'principal',
        'obra',
        'facturacion',
        'delegacion',
        'otra'
    ) NOT NULL DEFAULT 'principal',
    linea1 VARCHAR(255) NOT NULL,
    linea2 VARCHAR(255) NULL,
    codigo_postal VARCHAR(20) NULL,
    localidad VARCHAR(120) NULL,
    provincia VARCHAR(120) NULL,
    pais VARCHAR(120) NOT NULL DEFAULT 'Espana',
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    descripcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_direcciones_contexto (id_contexto),
    KEY idx_direcciones_empresa_contexto (id_empresa, id_contexto),
    KEY idx_direcciones_contacto (id_contacto),
    KEY idx_direcciones_contacto_empresa_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    KEY idx_direcciones_usuario_contexto (id_usuario, id_contexto),
    CONSTRAINT chk_direcciones_propietario CHECK (
        id_empresa IS NOT NULL
        OR id_contacto IS NOT NULL
        OR id_contacto_empresa IS NOT NULL
        OR id_usuario IS NOT NULL
    ),
    CONSTRAINT fk_direcciones_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_direcciones_empresa_contexto FOREIGN KEY (id_empresa, id_contexto) REFERENCES empresas (id_empresa, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_direcciones_contacto FOREIGN KEY (id_contacto) REFERENCES contactos (id_contacto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_direcciones_contacto_empresa_contexto FOREIGN KEY (
        id_contacto_empresa,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_direcciones_usuario_contexto FOREIGN KEY (id_usuario, id_contexto) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS telefonos (
    id_telefono BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa BIGINT UNSIGNED NULL,
    id_contacto BIGINT UNSIGNED NULL,
    id_contacto_empresa BIGINT UNSIGNED NULL,
    id_usuario BIGINT UNSIGNED NULL,
    prefijo VARCHAR(10) NULL,
    numero VARCHAR(30) NOT NULL,
    tipo ENUM(
        'fijo',
        'movil',
        'oficina',
        'personal',
        'urgencias',
        'otro'
    ) NOT NULL DEFAULT 'movil',
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    descripcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_telefonos_contexto (id_contexto),
    KEY idx_telefonos_empresa_contexto (id_empresa, id_contexto),
    KEY idx_telefonos_contacto (id_contacto),
    KEY idx_telefonos_contacto_empresa_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    KEY idx_telefonos_usuario_contexto (id_usuario, id_contexto),
    CONSTRAINT chk_telefonos_propietario CHECK (
        id_empresa IS NOT NULL
        OR id_contacto IS NOT NULL
        OR id_contacto_empresa IS NOT NULL
        OR id_usuario IS NOT NULL
    ),
    CONSTRAINT fk_telefonos_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_telefonos_empresa_contexto FOREIGN KEY (id_empresa, id_contexto) REFERENCES empresas (id_empresa, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_telefonos_contacto FOREIGN KEY (id_contacto) REFERENCES contactos (id_contacto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_telefonos_contacto_empresa_contexto FOREIGN KEY (
        id_contacto_empresa,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_telefonos_usuario_contexto FOREIGN KEY (id_usuario, id_contexto) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emails (
    id_email BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa BIGINT UNSIGNED NULL,
    id_contacto BIGINT UNSIGNED NULL,
    id_contacto_empresa BIGINT UNSIGNED NULL,
    id_usuario BIGINT UNSIGNED NULL,
    email VARCHAR(180) NOT NULL,
    tipo ENUM(
        'personal',
        'profesional',
        'facturacion',
        'avisos',
        'tecnico',
        'otro'
    ) NOT NULL DEFAULT 'profesional',
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    descripcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_emails_contexto (id_contexto),
    KEY idx_emails_empresa_contexto (id_empresa, id_contexto),
    KEY idx_emails_contacto (id_contacto),
    KEY idx_emails_contacto_empresa_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    KEY idx_emails_usuario_contexto (id_usuario, id_contexto),
    CONSTRAINT chk_emails_propietario CHECK (
        id_empresa IS NOT NULL
        OR id_contacto IS NOT NULL
        OR id_contacto_empresa IS NOT NULL
        OR id_usuario IS NOT NULL
    ),
    CONSTRAINT fk_emails_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_emails_empresa_contexto FOREIGN KEY (id_empresa, id_contexto) REFERENCES empresas (id_empresa, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_emails_contacto FOREIGN KEY (id_contacto) REFERENCES contactos (id_contacto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_emails_contacto_empresa_contexto FOREIGN KEY (
        id_contacto_empresa,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_emails_usuario_contexto FOREIGN KEY (id_usuario, id_contexto) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estaciones_servicio (
    id_estacion_servicio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    codigo_estacion_interno VARCHAR(80) NULL,
    cod_repsol VARCHAR(80) NULL,
    cod_cepsa VARCHAR(80) NULL,
    concesion VARCHAR(120) NULL,
    tipo VARCHAR(120) NULL,
    direccion VARCHAR(255) NULL,
    codigo_postal VARCHAR(20) NULL,
    poblacion VARCHAR(120) NULL,
    provincia VARCHAR(120) NULL,
    pais VARCHAR(120) NOT NULL DEFAULT 'Espana',
    latitud_wgs84 DECIMAL(11, 8) NULL,
    longitud_wgs84 DECIMAL(11, 8) NULL,
    delegacion VARCHAR(150) NULL,
    delegado VARCHAR(150) NULL,
    tecnico_gestion VARCHAR(150) NULL,
    telefono_tecnico_gestion VARCHAR(30) NULL,
    email_tecnico_gestion VARCHAR(180) NULL,
    responsable_es_gestor VARCHAR(150) NULL,
    telefono_movil VARCHAR(30) NULL,
    telefono_oficina VARCHAR(30) NULL,
    sede VARCHAR(150) NULL,
    tipo_mantenimiento VARCHAR(150) NULL,
    f_baja DATE NULL,
    razon_modificacion VARCHAR(255) NULL,
    observaciones TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_estaciones_contexto_cod_repsol (id_contexto, cod_repsol),
    UNIQUE KEY uq_estaciones_contexto_cod_cepsa (id_contexto, cod_cepsa),
    UNIQUE KEY uq_estaciones_contexto_codigo_interno (
        id_contexto,
        codigo_estacion_interno
    ),
    KEY idx_estaciones_empresa_contexto (
        id_empresa_cliente,
        id_contexto
    ),
    KEY idx_estaciones_id_contexto (
        id_estacion_servicio,
        id_contexto
    ),
    CONSTRAINT fk_estaciones_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_estaciones_empresa_contexto FOREIGN KEY (
        id_empresa_cliente,
        id_contexto
    ) REFERENCES empresas (id_empresa, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidades (
    id_unidad BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    abreviatura VARCHAR(20) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_unidades_nombre (nombre),
    UNIQUE KEY uq_unidades_abreviatura (abreviatura)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicios (
    id_servicio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    id_unidad BIGINT UNSIGNED NOT NULL,
    tipo_servicio ENUM(
        'mantenimiento',
        'obra',
        'legalizacion',
        'inspeccion',
        'otro'
    ) NOT NULL DEFAULT 'otro',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_servicios_codigo (codigo),
    KEY idx_servicios_unidad (id_unidad),
    CONSTRAINT fk_servicios_unidad FOREIGN KEY (id_unidad) REFERENCES unidades (id_unidad) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifarios (
    id_tarifario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    version VARCHAR(40) NULL,
    fecha_inicio_vigencia DATE NULL,
    fecha_fin_vigencia DATE NULL,
    moneda CHAR(3) NOT NULL DEFAULT 'EUR',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tarifarios_contexto_nombre_version (id_contexto, nombre, version),
    KEY idx_tarifarios_contexto (id_contexto),
    KEY idx_tarifarios_id_contexto (id_tarifario, id_contexto),
    CONSTRAINT fk_tarifarios_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifario_servicios (
    id_tarifario_servicio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_tarifario BIGINT UNSIGNED NOT NULL,
    id_servicio BIGINT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5, 2) NOT NULL DEFAULT 21.00,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    observaciones VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tarifario_servicios_contexto_tarifario_servicio (
        id_contexto,
        id_tarifario,
        id_servicio
    ),
    KEY idx_tarifario_servicios_contexto (id_contexto),
    KEY idx_tarifario_servicios_servicio (id_servicio),
    KEY idx_tarifario_servicios_tarifario_contexto (id_tarifario, id_contexto),
    KEY idx_tarifario_servicios_id_contexto (
        id_tarifario_servicio,
        id_contexto
    ),
    CONSTRAINT fk_tarifario_servicios_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_tarifario_servicios_tarifario_contexto FOREIGN KEY (id_tarifario, id_contexto) REFERENCES tarifarios (id_tarifario, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tarifario_servicios_servicio FOREIGN KEY (id_servicio) REFERENCES servicios (id_servicio) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proyectos (
    id_proyecto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa_cliente BIGINT UNSIGNED NULL,
    id_contacto_empresa_ciete BIGINT UNSIGNED NULL,
    id_estacion_servicio BIGINT UNSIGNED NULL,
    id_tarifario BIGINT UNSIGNED NULL,
    id_usuario_responsable BIGINT UNSIGNED NULL,
    id_usuario_cierre BIGINT UNSIGNED NULL,
    codigo_proyecto VARCHAR(100) NOT NULL,
    nombre_proyecto VARCHAR(180) NOT NULL,
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    workplan_resumen TEXT NULL,
    numero_aviso VARCHAR(100) NULL,
    fecha_encargo DATE NULL,
    fecha_inicio_prevista DATE NULL,
    fecha_inicio_real DATE NULL,
    fecha_fin_prevista DATE NULL,
    fecha_fin_real DATE NULL,
    estado_general ENUM(
        'borrador',
        'en_curso',
        'pausado',
        'terminado',
        'cerrado',
        'cancelado'
    ) NOT NULL DEFAULT 'borrador',
    estado_workplan ENUM(
        'pendiente',
        'encargo_recibido',
        'solicitado_pedido',
        'pedido_recibido',
        'inicio_sin_pedido',
        'inicio_con_pedido',
        'en_ejecucion',
        'pendiente_factura',
        'facturado',
        'cobrado',
        'cerrado'
    ) NOT NULL DEFAULT 'pendiente',
    trabajo_terminado TINYINT(1) NOT NULL DEFAULT 0,
    cerrado TINYINT(1) NOT NULL DEFAULT 0,
    bloqueado_cierre TINYINT(1) NOT NULL DEFAULT 0,
    fecha_cierre DATETIME NULL,
    observaciones_operativas TEXT NULL,
    observaciones_facturacion TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_proyectos_contexto_codigo (id_contexto, codigo_proyecto),
    KEY idx_proyectos_contexto (id_contexto),
    KEY idx_proyectos_id_contexto (id_proyecto, id_contexto),
    KEY idx_proyectos_empresa_contexto (
        id_empresa_cliente,
        id_contexto
    ),
    KEY idx_proyectos_contacto_cliente_contexto (
        id_contacto_empresa_cliente,
        id_contexto
    ),
    KEY idx_proyectos_contacto_ciete_contexto (
        id_contacto_empresa_ciete,
        id_contexto
    ),
    KEY idx_proyectos_estacion_contexto (
        id_estacion_servicio,
        id_contexto
    ),
    KEY idx_proyectos_tarifario_contexto (id_tarifario, id_contexto),
    KEY idx_proyectos_usuario_responsable_contexto (
        id_usuario_responsable,
        id_contexto
    ),
    KEY idx_proyectos_usuario_cierre_contexto (
        id_usuario_cierre,
        id_contexto
    ),
    CONSTRAINT chk_proyectos_cierre_datos CHECK (
        cerrado = 0
        OR (
            id_usuario_cierre IS NOT NULL
            AND fecha_cierre IS NOT NULL
        )
    ),
    CONSTRAINT chk_proyectos_fin_real CHECK (
        fecha_fin_real IS NULL
        OR fecha_inicio_real IS NULL
        OR fecha_fin_real >= fecha_inicio_real
    ),
    CONSTRAINT fk_proyectos_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_empresa_contexto FOREIGN KEY (
        id_empresa_cliente,
        id_contexto
    ) REFERENCES empresas (id_empresa, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_contacto_cliente_contexto FOREIGN KEY (
        id_contacto_empresa_cliente,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_contacto_ciete_contexto FOREIGN KEY (
        id_contacto_empresa_ciete,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_estacion_contexto FOREIGN KEY (
        id_estacion_servicio,
        id_contexto
    ) REFERENCES estaciones_servicio (
        id_estacion_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_tarifario_contexto FOREIGN KEY (id_tarifario, id_contexto) REFERENCES tarifarios (id_tarifario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_usuario_responsable_contexto FOREIGN KEY (
        id_usuario_responsable,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_usuario_cierre_contexto FOREIGN KEY (
        id_usuario_cierre,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proyectos_workplan (
    id_proyecto_workplan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_proyecto BIGINT UNSIGNED NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    fase ENUM(
        'encargo',
        'pedido',
        'ejecucion',
        'facturacion',
        'cobro',
        'cierre',
        'otro'
    ) NOT NULL DEFAULT 'otro',
    estado ENUM(
        'pendiente',
        'en_curso',
        'completado',
        'bloqueado',
        'cancelado'
    ) NOT NULL DEFAULT 'pendiente',
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    fecha_prevista DATE NULL,
    fecha_real DATE NULL,
    id_usuario_responsable BIGINT UNSIGNED NULL,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_proyectos_workplan_contexto_proyecto_orden (
        id_contexto,
        id_proyecto,
        orden
    ),
    KEY idx_proyectos_workplan_contexto (id_contexto),
    KEY idx_proyectos_workplan_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_proyectos_workplan_usuario_contexto (
        id_usuario_responsable,
        id_contexto
    ),
    CONSTRAINT fk_proyectos_workplan_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_workplan_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_workplan_usuario_contexto FOREIGN KEY (
        id_usuario_responsable,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proyectos_comentarios (
    id_comentario_proyecto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_proyecto BIGINT UNSIGNED NOT NULL,
    id_usuario BIGINT UNSIGNED NULL,
    fecha_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comentario TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_proyectos_comentarios_contexto (id_contexto),
    KEY idx_proyectos_comentarios_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_proyectos_comentarios_usuario_contexto (id_usuario, id_contexto),
    CONSTRAINT fk_proyectos_comentarios_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_comentarios_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_proyectos_comentarios_usuario_contexto FOREIGN KEY (id_usuario, id_contexto) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuestos (
    id_presupuesto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_proyecto BIGINT UNSIGNED NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa_cliente BIGINT UNSIGNED NULL,
    id_estacion_servicio BIGINT UNSIGNED NULL,
    id_tarifario BIGINT UNSIGNED NULL,
    id_usuario_creador BIGINT UNSIGNED NULL,
    numero_presupuesto VARCHAR(100) NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_validez_hasta DATE NULL,
    estado ENUM(
        'borrador',
        'enviado',
        'aceptado',
        'rechazado',
        'caducado',
        'convertido',
        'anulado'
    ) NOT NULL DEFAULT 'borrador',
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    subtotal DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    descuento_total DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    base_imponible DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_presupuestos_contexto_numero (
        id_contexto,
        numero_presupuesto
    ),
    KEY idx_presupuestos_contexto (id_contexto),
    KEY idx_presupuestos_id_contexto (id_presupuesto, id_contexto),
    KEY idx_presupuestos_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_presupuestos_empresa_contexto (
        id_empresa_cliente,
        id_contexto
    ),
    KEY idx_presupuestos_contacto_contexto (
        id_contacto_empresa_cliente,
        id_contexto
    ),
    KEY idx_presupuestos_estacion_contexto (
        id_estacion_servicio,
        id_contexto
    ),
    KEY idx_presupuestos_tarifario_contexto (id_tarifario, id_contexto),
    KEY idx_presupuestos_usuario_contexto (
        id_usuario_creador,
        id_contexto
    ),
    CONSTRAINT chk_presupuestos_total CHECK (total >= 0),
    CONSTRAINT fk_presupuestos_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_empresa_contexto FOREIGN KEY (
        id_empresa_cliente,
        id_contexto
    ) REFERENCES empresas (id_empresa, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_contacto_contexto FOREIGN KEY (
        id_contacto_empresa_cliente,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_estacion_contexto FOREIGN KEY (
        id_estacion_servicio,
        id_contexto
    ) REFERENCES estaciones_servicio (
        id_estacion_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_tarifario_contexto FOREIGN KEY (id_tarifario, id_contexto) REFERENCES tarifarios (id_tarifario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_usuario_contexto FOREIGN KEY (
        id_usuario_creador,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuestos_lineas (
    id_linea_presupuesto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_presupuesto BIGINT UNSIGNED NOT NULL,
    id_tarifario_servicio BIGINT UNSIGNED NULL,
    id_servicio BIGINT UNSIGNED NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    concepto_seleccionable VARCHAR(255) NULL,
    concepto_libre TEXT NULL,
    cantidad DECIMAL(14, 3) NOT NULL DEFAULT 1.000,
    precio_unitario DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    descuento_porcentaje DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5, 2) NOT NULL DEFAULT 21.00,
    total_linea DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_presupuestos_lineas_contexto_presupuesto_orden (
        id_contexto,
        id_presupuesto,
        orden
    ),
    KEY idx_presupuestos_lineas_contexto (id_contexto),
    KEY idx_presupuestos_lineas_presupuesto_contexto (id_presupuesto, id_contexto),
    KEY idx_presupuestos_lineas_tarifario_servicio_contexto (
        id_tarifario_servicio,
        id_contexto
    ),
    KEY idx_presupuestos_lineas_servicio (id_servicio),
    CONSTRAINT fk_presupuestos_lineas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_lineas_presupuesto_contexto FOREIGN KEY (id_presupuesto, id_contexto) REFERENCES presupuestos (id_presupuesto, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_lineas_tarifario_servicio_contexto FOREIGN KEY (
        id_tarifario_servicio,
        id_contexto
    ) REFERENCES tarifario_servicios (
        id_tarifario_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_presupuestos_lineas_servicio FOREIGN KEY (id_servicio) REFERENCES servicios (id_servicio) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_presupuesto BIGINT UNSIGNED NULL,
    id_proyecto BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa_cliente BIGINT UNSIGNED NULL,
    id_estacion_servicio BIGINT UNSIGNED NULL,
    id_tarifario BIGINT UNSIGNED NULL,
    id_usuario_responsable BIGINT UNSIGNED NULL,
    numero_pedido VARCHAR(100) NOT NULL,
    numero_aviso VARCHAR(100) NULL,
    fecha_solicitud_pedido DATE NULL,
    fecha_recepcion_pedido DATE NULL,
    fecha_solicitud_factura DATE NULL,
    estado ENUM(
        'pendiente',
        'solicitado',
        'recibido',
        'en_ejecucion',
        'cerrado',
        'anulado'
    ) NOT NULL DEFAULT 'pendiente',
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    subtotal DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pedidos_contexto_numero (id_contexto, numero_pedido),
    KEY idx_pedidos_contexto (id_contexto),
    KEY idx_pedidos_id_contexto (id_pedido, id_contexto),
    KEY idx_pedidos_presupuesto_contexto (id_presupuesto, id_contexto),
    KEY idx_pedidos_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_pedidos_empresa_contexto (
        id_empresa_cliente,
        id_contexto
    ),
    KEY idx_pedidos_contacto_contexto (
        id_contacto_empresa_cliente,
        id_contexto
    ),
    KEY idx_pedidos_estacion_contexto (
        id_estacion_servicio,
        id_contexto
    ),
    KEY idx_pedidos_tarifario_contexto (id_tarifario, id_contexto),
    KEY idx_pedidos_usuario_contexto (
        id_usuario_responsable,
        id_contexto
    ),
    CONSTRAINT fk_pedidos_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_presupuesto_contexto FOREIGN KEY (id_presupuesto, id_contexto) REFERENCES presupuestos (id_presupuesto, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_empresa_contexto FOREIGN KEY (
        id_empresa_cliente,
        id_contexto
    ) REFERENCES empresas (id_empresa, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_contacto_contexto FOREIGN KEY (
        id_contacto_empresa_cliente,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_estacion_contexto FOREIGN KEY (
        id_estacion_servicio,
        id_contexto
    ) REFERENCES estaciones_servicio (
        id_estacion_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_tarifario_contexto FOREIGN KEY (id_tarifario, id_contexto) REFERENCES tarifarios (id_tarifario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_usuario_contexto FOREIGN KEY (
        id_usuario_responsable,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos_lineas (
    id_linea_pedido BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_pedido BIGINT UNSIGNED NOT NULL,
    id_tarifario_servicio BIGINT UNSIGNED NULL,
    id_servicio BIGINT UNSIGNED NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    concepto_seleccionable VARCHAR(255) NULL,
    concepto_libre TEXT NULL,
    cantidad DECIMAL(14, 3) NOT NULL DEFAULT 1.000,
    precio_unitario DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5, 2) NOT NULL DEFAULT 21.00,
    total_linea DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pedidos_lineas_contexto_pedido_orden (id_contexto, id_pedido, orden),
    KEY idx_pedidos_lineas_contexto (id_contexto),
    KEY idx_pedidos_lineas_pedido_contexto (id_pedido, id_contexto),
    KEY idx_pedidos_lineas_tarifario_servicio_contexto (
        id_tarifario_servicio,
        id_contexto
    ),
    KEY idx_pedidos_lineas_servicio (id_servicio),
    CONSTRAINT fk_pedidos_lineas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_lineas_pedido_contexto FOREIGN KEY (id_pedido, id_contexto) REFERENCES pedidos (id_pedido, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_lineas_tarifario_servicio_contexto FOREIGN KEY (
        id_tarifario_servicio,
        id_contexto
    ) REFERENCES tarifario_servicios (
        id_tarifario_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_lineas_servicio FOREIGN KEY (id_servicio) REFERENCES servicios (id_servicio) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS facturas (
    id_factura BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_pedido BIGINT UNSIGNED NULL,
    id_presupuesto BIGINT UNSIGNED NULL,
    id_proyecto BIGINT UNSIGNED NOT NULL,
    id_empresa_cliente BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa_cliente BIGINT UNSIGNED NULL,
    id_estacion_servicio BIGINT UNSIGNED NULL,
    id_tarifario BIGINT UNSIGNED NULL,
    id_usuario_emisor BIGINT UNSIGNED NULL,
    numero_factura VARCHAR(100) NOT NULL,
    serie VARCHAR(20) NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    estado ENUM(
        'emitida',
        'enviada',
        'cobrada_parcial',
        'cobrada',
        'vencida',
        'anulada'
    ) NOT NULL DEFAULT 'emitida',
    autofactura TINYINT(1) NOT NULL DEFAULT 0,
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    base_imponible DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    retencion DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_facturas_contexto_numero (id_contexto, numero_factura),
    KEY idx_facturas_contexto (id_contexto),
    KEY idx_facturas_id_contexto (id_factura, id_contexto),
    KEY idx_facturas_pedido_contexto (id_pedido, id_contexto),
    KEY idx_facturas_presupuesto_contexto (id_presupuesto, id_contexto),
    KEY idx_facturas_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_facturas_empresa_contexto (
        id_empresa_cliente,
        id_contexto
    ),
    KEY idx_facturas_contacto_contexto (
        id_contacto_empresa_cliente,
        id_contexto
    ),
    KEY idx_facturas_estacion_contexto (
        id_estacion_servicio,
        id_contexto
    ),
    KEY idx_facturas_tarifario_contexto (id_tarifario, id_contexto),
    KEY idx_facturas_usuario_contexto (
        id_usuario_emisor,
        id_contexto
    ),
    CONSTRAINT chk_facturas_total CHECK (total >= 0),
    CONSTRAINT fk_facturas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_pedido_contexto FOREIGN KEY (id_pedido, id_contexto) REFERENCES pedidos (id_pedido, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_presupuesto_contexto FOREIGN KEY (id_presupuesto, id_contexto) REFERENCES presupuestos (id_presupuesto, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_empresa_contexto FOREIGN KEY (
        id_empresa_cliente,
        id_contexto
    ) REFERENCES empresas (id_empresa, id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_contacto_contexto FOREIGN KEY (
        id_contacto_empresa_cliente,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_estacion_contexto FOREIGN KEY (
        id_estacion_servicio,
        id_contexto
    ) REFERENCES estaciones_servicio (
        id_estacion_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_tarifario_contexto FOREIGN KEY (id_tarifario, id_contexto) REFERENCES tarifarios (id_tarifario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_usuario_contexto FOREIGN KEY (
        id_usuario_emisor,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS facturas_lineas (
    id_linea_factura BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_factura BIGINT UNSIGNED NOT NULL,
    id_tarifario_servicio BIGINT UNSIGNED NULL,
    id_servicio BIGINT UNSIGNED NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    concepto_seleccionable VARCHAR(255) NULL,
    concepto_libre TEXT NULL,
    cantidad DECIMAL(14, 3) NOT NULL DEFAULT 1.000,
    precio_unitario DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    iva_porcentaje DECIMAL(5, 2) NOT NULL DEFAULT 21.00,
    total_linea DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_facturas_lineas_contexto_factura_orden (
        id_contexto,
        id_factura,
        orden
    ),
    KEY idx_facturas_lineas_contexto (id_contexto),
    KEY idx_facturas_lineas_factura_contexto (id_factura, id_contexto),
    KEY idx_facturas_lineas_tarifario_servicio_contexto (
        id_tarifario_servicio,
        id_contexto
    ),
    KEY idx_facturas_lineas_servicio (id_servicio),
    CONSTRAINT fk_facturas_lineas_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_lineas_factura_contexto FOREIGN KEY (id_factura, id_contexto) REFERENCES facturas (id_factura, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_lineas_tarifario_servicio_contexto FOREIGN KEY (
        id_tarifario_servicio,
        id_contexto
    ) REFERENCES tarifario_servicios (
        id_tarifario_servicio,
        id_contexto
    ) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_facturas_lineas_servicio FOREIGN KEY (id_servicio) REFERENCES servicios (id_servicio) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cobros (
    id_cobro BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_factura BIGINT UNSIGNED NOT NULL,
    id_usuario_registro BIGINT UNSIGNED NULL,
    fecha_cobro DATE NOT NULL,
    importe DECIMAL(14, 2) NOT NULL,
    metodo_cobro ENUM(
        'transferencia',
        'giro',
        'efectivo',
        'confirming',
        'otro'
    ) NOT NULL DEFAULT 'transferencia',
    referencia VARCHAR(120) NULL,
    estado ENUM(
        'pendiente',
        'recibido',
        'conciliado',
        'devuelto'
    ) NOT NULL DEFAULT 'pendiente',
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cobros_contexto (id_contexto),
    KEY idx_cobros_factura_contexto (id_factura, id_contexto),
    KEY idx_cobros_usuario_contexto (
        id_usuario_registro,
        id_contexto
    ),
    CONSTRAINT chk_cobros_importe CHECK (importe > 0),
    CONSTRAINT fk_cobros_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cobros_factura_contexto FOREIGN KEY (id_factura, id_contexto) REFERENCES facturas (id_factura, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cobros_usuario_contexto FOREIGN KEY (
        id_usuario_registro,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legalizaciones (
    id_legalizacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_proyecto BIGINT UNSIGNED NOT NULL,
    id_usuario_responsable BIGINT UNSIGNED NULL,
    tipo_legalizacion VARCHAR(150) NOT NULL,
    numero_expediente VARCHAR(120) NULL,
    organismo VARCHAR(180) NULL,
    estado ENUM(
        'pendiente',
        'en_tramite',
        'resuelta',
        'cancelada'
    ) NOT NULL DEFAULT 'pendiente',
    descripcion_seleccionable VARCHAR(255) NULL,
    descripcion_libre TEXT NULL,
    fecha_inicio DATE NULL,
    fecha_limite DATE NULL,
    fecha_resolucion DATE NULL,
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_legalizaciones_contexto_num_expediente (
        id_contexto,
        numero_expediente
    ),
    KEY idx_legalizaciones_contexto (id_contexto),
    KEY idx_legalizaciones_id_contexto (id_legalizacion, id_contexto),
    KEY idx_legalizaciones_proyecto_contexto (id_proyecto, id_contexto),
    KEY idx_legalizaciones_usuario_contexto (
        id_usuario_responsable,
        id_contexto
    ),
    CONSTRAINT fk_legalizaciones_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_legalizaciones_proyecto_contexto FOREIGN KEY (id_proyecto, id_contexto) REFERENCES proyectos (id_proyecto, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_legalizaciones_usuario_contexto FOREIGN KEY (
        id_usuario_responsable,
        id_contexto
    ) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legalizaciones_contactos (
    id_legalizacion_contacto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_legalizacion BIGINT UNSIGNED NOT NULL,
    id_contacto_empresa BIGINT UNSIGNED NOT NULL,
    rol_en_legalizacion VARCHAR(150) NULL,
    principal TINYINT(1) NOT NULL DEFAULT 0,
    observaciones VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_legalizaciones_contactos (
        id_contexto,
        id_legalizacion,
        id_contacto_empresa
    ),
    KEY idx_legalizaciones_contactos_contexto (id_contexto),
    KEY idx_legalizaciones_contactos_legalizacion_contexto (id_legalizacion, id_contexto),
    KEY idx_legalizaciones_contactos_contacto_contexto (
        id_contacto_empresa,
        id_contexto
    ),
    CONSTRAINT fk_legalizaciones_contactos_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_legalizaciones_contactos_legalizacion_contexto FOREIGN KEY (id_legalizacion, id_contexto) REFERENCES legalizaciones (id_legalizacion, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_legalizaciones_contactos_contacto_contexto FOREIGN KEY (
        id_contacto_empresa,
        id_contexto
    ) REFERENCES contactos_empresas (
        id_contacto_empresa,
        id_contexto
    ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comentarios_legalizaciones (
    id_comentario_legalizacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_contexto BIGINT UNSIGNED NOT NULL,
    id_legalizacion BIGINT UNSIGNED NOT NULL,
    id_usuario BIGINT UNSIGNED NULL,
    fecha_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comentario TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_comentarios_legalizaciones_contexto (id_contexto),
    KEY idx_comentarios_legalizaciones_legalizacion_contexto (id_legalizacion, id_contexto),
    KEY idx_comentarios_legalizaciones_usuario_contexto (id_usuario, id_contexto),
    CONSTRAINT fk_comentarios_legalizaciones_contexto FOREIGN KEY (id_contexto) REFERENCES contextos_cliente (id_contexto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_comentarios_legalizaciones_legalizacion_contexto FOREIGN KEY (id_legalizacion, id_contexto) REFERENCES legalizaciones (id_legalizacion, id_contexto) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comentarios_legalizaciones_usuario_contexto FOREIGN KEY (id_usuario, id_contexto) REFERENCES usuarios (id_usuario, id_contexto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    contextos_cliente (
        id_contexto,
        nombre,
        codigo,
        descripcion,
        activo
    )
VALUES (
        1,
        'CEPSA',
        'CEPSA',
        'Contexto operativo CEPSA',
        1
    ),
    (
        2,
        'REPSOL',
        'REPSOL',
        'Contexto operativo REPSOL',
        1
    ),
    (
        3,
        'OTRO',
        'OTRO',
        'Contexto general u otros clientes',
        1
    )
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);

INSERT INTO
    roles (
        id_rol,
        nombre,
        slug,
        descripcion,
        activo
    )
VALUES (
        1,
        'admin',
        'admin',
        'Acceso total al ERP',
        1
    ),
    (
        2,
        'gestor',
        'gestor',
        'Gestion operativa de negocio',
        1
    ),
    (
        3,
        'tecnico',
        'tecnico',
        'Trabajo tecnico y seguimiento',
        1
    ),
    (
        4,
        'consulta',
        'consulta',
        'Solo lectura',
        1
    ),
    (
        5,
        'control_cierre',
        'control_cierre',
        'Control de proyectos cerrados y bloqueados',
        1
    )
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);

INSERT INTO
    permisos (
        id_permiso,
        nombre,
        slug,
        descripcion,
        activo
    )
VALUES (
        1,
        'Ver usuarios',
        'usuarios.ver',
        'Consulta de usuarios',
        1
    ),
    (
        2,
        'Gestionar usuarios',
        'usuarios.gestionar',
        'Alta, baja y edicion de usuarios',
        1
    ),
    (
        3,
        'Gestionar roles',
        'roles.gestionar',
        'Gestion de roles y permisos',
        1
    ),
    (
        4,
        'Ver proyectos',
        'proyectos.ver',
        'Consulta de proyectos',
        1
    ),
    (
        5,
        'Crear proyectos',
        'proyectos.crear',
        'Creacion de proyectos',
        1
    ),
    (
        6,
        'Editar proyectos',
        'proyectos.editar',
        'Edicion de proyectos',
        1
    ),
    (
        7,
        'Cerrar proyectos',
        'proyectos.cerrar',
        'Cierre de proyectos',
        1
    ),
    (
        8,
        'Reabrir proyectos',
        'proyectos.reabrir',
        'Reapertura de proyectos',
        1
    ),
    (
        9,
        'Editar proyectos cerrados',
        'proyectos_cerrados.editar',
        'Edicion de proyectos cerrados',
        1
    ),
    (
        10,
        'Reabrir proyectos cerrados',
        'proyectos_cerrados.reabrir',
        'Reapertura de proyectos cerrados',
        1
    ),
    (
        11,
        'Ver presupuestos',
        'presupuestos.ver',
        'Consulta de presupuestos',
        1
    ),
    (
        12,
        'Gestionar presupuestos',
        'presupuestos.gestionar',
        'Creacion y edicion de presupuestos',
        1
    ),
    (
        13,
        'Ver pedidos',
        'pedidos.ver',
        'Consulta de pedidos',
        1
    ),
    (
        14,
        'Gestionar pedidos',
        'pedidos.gestionar',
        'Creacion y edicion de pedidos',
        1
    ),
    (
        15,
        'Ver facturas',
        'facturas.ver',
        'Consulta de facturas',
        1
    ),
    (
        16,
        'Gestionar facturas',
        'facturas.gestionar',
        'Creacion y edicion de facturas',
        1
    ),
    (
        17,
        'Gestionar cobros',
        'cobros.gestionar',
        'Registro y conciliacion de cobros',
        1
    ),
    (
        18,
        'Ver legalizaciones',
        'legalizaciones.ver',
        'Consulta de legalizaciones',
        1
    ),
    (
        19,
        'Gestionar legalizaciones',
        'legalizaciones.gestionar',
        'Gestion de legalizaciones',
        1
    ),
    (
        20,
        'Ver estaciones',
        'estaciones.ver',
        'Consulta de estaciones de servicio',
        1
    ),
    (
        21,
        'Gestionar estaciones',
        'estaciones.gestionar',
        'Gestion de estaciones',
        1
    ),
    (
        22,
        'Ver tarifarios',
        'tarifarios.ver',
        'Consulta de tarifarios',
        1
    ),
    (
        23,
        'Gestionar tarifarios',
        'tarifarios.gestionar',
        'Gestion de tarifarios',
        1
    ),
    (
        24,
        'Gestionar empresas y contactos',
        'empresas_contactos.gestionar',
        'Gestion de empresas y contactos',
        1
    ),
    (
        25,
        'Ver reportes',
        'reportes.ver',
        'Consulta de reportes',
        1
    )
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);

INSERT IGNORE INTO
    rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
    JOIN permisos p
WHERE
    r.slug = 'admin';

INSERT IGNORE INTO
    rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
    JOIN permisos p
WHERE
    r.slug = 'control_cierre'
    AND p.slug IN (
        'proyectos.ver',
        'proyectos.cerrar',
        'proyectos.reabrir',
        'proyectos_cerrados.editar',
        'proyectos_cerrados.reabrir'
    );

INSERT IGNORE INTO
    rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
    JOIN permisos p
WHERE
    r.slug = 'consulta'
    AND p.slug IN (
        'proyectos.ver',
        'presupuestos.ver',
        'pedidos.ver',
        'facturas.ver',
        'legalizaciones.ver',
        'estaciones.ver',
        'tarifarios.ver',
        'reportes.ver'
    );

INSERT IGNORE INTO
    rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
    JOIN permisos p
WHERE
    r.slug = 'tecnico'
    AND p.slug IN (
        'proyectos.ver',
        'proyectos.editar',
        'presupuestos.ver',
        'pedidos.ver',
        'facturas.ver',
        'legalizaciones.ver',
        'legalizaciones.gestionar',
        'estaciones.ver'
    );

INSERT IGNORE INTO
    rol_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
    JOIN permisos p
WHERE
    r.slug = 'gestor'
    AND p.slug IN (
        'proyectos.ver',
        'proyectos.crear',
        'proyectos.editar',
        'proyectos.cerrar',
        'proyectos.reabrir',
        'presupuestos.ver',
        'presupuestos.gestionar',
        'pedidos.ver',
        'pedidos.gestionar',
        'facturas.ver',
        'facturas.gestionar',
        'cobros.gestionar',
        'legalizaciones.ver',
        'legalizaciones.gestionar',
        'estaciones.ver',
        'estaciones.gestionar',
        'tarifarios.ver',
        'tarifarios.gestionar',
        'empresas_contactos.gestionar',
        'reportes.ver'
    );

INSERT INTO
    empresas (
        id_empresa,
        id_contexto,
        nombre,
        nombre_comercial,
        razon_social,
        cif,
        tipo_empresa,
        activo
    )
VALUES (
        1,
        1,
        'CEPSA',
        'CEPSA',
        'Compania Espanola de Petroleos S.A.',
        'A28003119',
        'cliente',
        1
    ),
    (
        2,
        2,
        'REPSOL',
        'REPSOL',
        'Repsol S.A.',
        'A78374725',
        'cliente',
        1
    ),
    (
        3,
        3,
        'CIETE INGENIEROS SA',
        'Ciete',
        'Ciete Ingenieros S.A.',
        'A12345678',
        'interna',
        1
    )
ON DUPLICATE KEY UPDATE
    nombre_comercial = VALUES(nombre_comercial),
    razon_social = VALUES(razon_social),
    tipo_empresa = VALUES(tipo_empresa),
    activo = VALUES(activo);

INSERT INTO
    contactos (
        id_contacto,
        nombre,
        apellidos,
        dni,
        cargo_general,
        activo
    )
VALUES (
        1,
        'Administrador',
        'Sistema',
        NULL,
        'Administrador ERP',
        1
    )
ON DUPLICATE KEY UPDATE
    apellidos = VALUES(apellidos),
    cargo_general = VALUES(cargo_general),
    activo = VALUES(activo);

INSERT INTO
    contactos_empresas (
        id_contacto_empresa,
        id_contexto,
        id_empresa,
        id_contacto,
        puesto,
        categoria,
        es_responsable_principal,
        recibe_avisos,
        recibe_presupuestos,
        recibe_facturas,
        es_usuario,
        activo
    )
VALUES (
        1,
        3,
        3,
        1,
        'Administrador ERP',
        'interno',
        1,
        1,
        1,
        1,
        1,
        1
    )
ON DUPLICATE KEY UPDATE
    puesto = VALUES(puesto),
    es_usuario = VALUES(es_usuario),
    activo = VALUES(activo);

INSERT INTO
    usuarios (
        id_usuario,
        id_contexto,
        id_contacto_empresa,
        nombre,
        apellidos,
        nombre_usuario,
        email,
        email_verificado_at,
        password,
        telefono,
        remember_token,
        ultimo_login_at,
        activo
    )
VALUES (
        1,
        3,
        1,
        'Admin',
        'ERP Ciete',
        'admin',
        'admin@erp-ciete.local',
        NOW(),
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        NULL,
        NULL,
        NULL,
        1
    )
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    apellidos = VALUES(apellidos),
    email = VALUES(email),
    activo = VALUES(activo);

INSERT IGNORE INTO
    usuario_roles (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol
FROM usuarios u
    JOIN roles r
WHERE
    u.nombre_usuario = 'admin'
    AND r.slug = 'admin';

INSERT INTO
    unidades (
        id_unidad,
        nombre,
        abreviatura,
        descripcion,
        activo
    )
VALUES (
        1,
        'Unidad',
        'ud',
        'Unidad general',
        1
    ),
    (
        2,
        'Hora',
        'h',
        'Hora de trabajo',
        1
    ),
    (
        3,
        'Metro cuadrado',
        'm2',
        'Superficie',
        1
    ),
    (
        4,
        'Metro lineal',
        'ml',
        'Longitud',
        1
    )
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);

INSERT INTO
    servicios (
        id_servicio,
        codigo,
        nombre,
        descripcion_seleccionable,
        descripcion_libre,
        id_unidad,
        tipo_servicio,
        activo
    )
VALUES (
        1,
        'SERV-INS-001',
        'Inspeccion tecnica',
        'Inspeccion',
        'Inspeccion tecnica inicial en estacion',
        2,
        'inspeccion',
        1
    ),
    (
        2,
        'SERV-MPR-001',
        'Mantenimiento preventivo',
        'Mantenimiento preventivo',
        'Mantenimiento preventivo periodico',
        2,
        'mantenimiento',
        1
    ),
    (
        3,
        'SERV-MCO-001',
        'Mantenimiento correctivo',
        'Mantenimiento correctivo',
        'Actuacion correctiva por incidencia',
        2,
        'mantenimiento',
        1
    ),
    (
        4,
        'SERV-OBR-001',
        'Obra civil menor',
        'Obra civil',
        'Adecuacion de instalaciones',
        3,
        'obra',
        1
    ),
    (
        5,
        'SERV-LEG-001',
        'Gestion de legalizacion',
        'Legalizacion',
        'Gestion documental y seguimiento legalizacion',
        1,
        'legalizacion',
        1
    ),
    (
        6,
        'SERV-TEC-001',
        'Soporte tecnico',
        'Soporte tecnico',
        'Asistencia tecnica especializada',
        2,
        'otro',
        1
    )
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion_seleccionable = VALUES(descripcion_seleccionable),
    descripcion_libre = VALUES(descripcion_libre),
    id_unidad = VALUES(id_unidad),
    tipo_servicio = VALUES(tipo_servicio),
    activo = VALUES(activo);

INSERT INTO
    tarifarios (
        id_tarifario,
        id_contexto,
        nombre,
        version,
        fecha_inicio_vigencia,
        fecha_fin_vigencia,
        moneda,
        activo
    )
VALUES (
        1,
        1,
        'Tarifario Base CEPSA',
        '2026.1',
        '2026-01-01',
        NULL,
        'EUR',
        1
    ),
    (
        2,
        2,
        'Tarifario Base REPSOL',
        '2026.1',
        '2026-01-01',
        NULL,
        'EUR',
        1
    ),
    (
        3,
        3,
        'Tarifario Base OTRO',
        '2026.1',
        '2026-01-01',
        NULL,
        'EUR',
        1
    )
ON DUPLICATE KEY UPDATE
    fecha_inicio_vigencia = VALUES(fecha_inicio_vigencia),
    fecha_fin_vigencia = VALUES(fecha_fin_vigencia),
    activo = VALUES(activo);

INSERT INTO
    tarifario_servicios (
        id_tarifario_servicio,
        id_contexto,
        id_tarifario,
        id_servicio,
        precio_unitario,
        iva_porcentaje,
        activo
    )
VALUES (1, 1, 1, 1, 95.00, 21.00, 1),
    (2, 1, 1, 2, 38.00, 21.00, 1),
    (3, 1, 1, 3, 52.00, 21.00, 1),
    (4, 1, 1, 4, 27.00, 21.00, 1),
    (5, 1, 1, 5, 180.00, 21.00, 1),
    (6, 1, 1, 6, 60.00, 21.00, 1),
    (7, 2, 2, 1, 98.00, 21.00, 1),
    (8, 2, 2, 2, 40.00, 21.00, 1),
    (9, 2, 2, 3, 55.00, 21.00, 1),
    (10, 2, 2, 4, 29.00, 21.00, 1),
    (11, 2, 2, 5, 185.00, 21.00, 1),
    (12, 2, 2, 6, 62.00, 21.00, 1),
    (13, 3, 3, 1, 90.00, 21.00, 1),
    (14, 3, 3, 2, 35.00, 21.00, 1),
    (15, 3, 3, 3, 50.00, 21.00, 1),
    (16, 3, 3, 4, 25.00, 21.00, 1),
    (17, 3, 3, 5, 170.00, 21.00, 1),
    (18, 3, 3, 6, 58.00, 21.00, 1)
ON DUPLICATE KEY UPDATE
    precio_unitario = VALUES(precio_unitario),
    iva_porcentaje = VALUES(iva_porcentaje),
    activo = VALUES(activo);

INSERT INTO
    estaciones_servicio (
        id_estacion_servicio,
        id_contexto,
        id_empresa_cliente,
        nombre,
        codigo_estacion_interno,
        cod_repsol,
        cod_cepsa,
        concesion,
        tipo,
        direccion,
        codigo_postal,
        poblacion,
        provincia,
        pais,
        latitud_wgs84,
        longitud_wgs84,
        delegacion,
        delegado,
        tecnico_gestion,
        telefono_tecnico_gestion,
        email_tecnico_gestion,
        activo
    )
VALUES (
        1,
        1,
        1,
        'Estacion CEPSA Demo 01',
        'CEPSA-EST-001',
        NULL,
        'CEPSA-0001',
        'Concesion CEPSA 1',
        'Abanderada',
        'Calle Energia 1',
        '41001',
        'Sevilla',
        'Sevilla',
        'Espana',
        37.38910000,
        -5.98450000,
        'Andalucia',
        'Delegado CEPSA',
        'Tecnico CEPSA',
        '600000001',
        'tecnico.cepsa@demo.local',
        1
    ),
    (
        2,
        2,
        2,
        'Estacion REPSOL Demo 01',
        'REPSOL-EST-001',
        'REPSOL-0001',
        NULL,
        'Concesion REPSOL 1',
        'Abanderada',
        'Avenida Industria 2',
        '28001',
        'Madrid',
        'Madrid',
        'Espana',
        40.41680000,
        -3.70380000,
        'Centro',
        'Delegado REPSOL',
        'Tecnico REPSOL',
        '600000002',
        'tecnico.repsol@demo.local',
        1
    )
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    direccion = VALUES(direccion),
    activo = VALUES(activo);