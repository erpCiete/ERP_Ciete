-- ERP CIETE - ESQUEMA BASE (MySQL 8+, utf8mb4)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS usuario_permisos;
DROP TABLE IF EXISTS usuario_roles;
DROP TABLE IF EXISTS rol_permisos;
DROP TABLE IF EXISTS proyecto_historial_estados;
DROP TABLE IF EXISTS auditoria_cambios;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS cobros;
DROP TABLE IF EXISTS factura_lineas;
DROP TABLE IF EXISTS facturas;
DROP TABLE IF EXISTS legalizacion_comentarios;
DROP TABLE IF EXISTS legalizacion_contactos;
DROP TABLE IF EXISTS legalizaciones;
DROP TABLE IF EXISTS proyecto_comentarios;
DROP TABLE IF EXISTS pedidos_lineas;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS proyectos;
DROP TABLE IF EXISTS presupuesto_lineas;
DROP TABLE IF EXISTS presupuestos;
DROP TABLE IF EXISTS tarifario_servicios;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS tarifarios;
DROP TABLE IF EXISTS estaciones_servicio;
DROP TABLE IF EXISTS emails;
DROP TABLE IF EXISTS telefonos;
DROP TABLE IF EXISTS direcciones;
DROP TABLE IF EXISTS empresa_contactos;
DROP TABLE IF EXISTS contactos;
DROP TABLE IF EXISTS empresas;

SET FOREIGN_KEY_CHECKS = 1;

-- 1) Empresas
CREATE TABLE empresas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    cif VARCHAR(20) NOT NULL,
    tipo_empresa ENUM('cliente', 'interna', 'entidad_publica', 'otro') NOT NULL DEFAULT 'cliente',
    es_cliente_principal BOOLEAN NOT NULL DEFAULT FALSE,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresas_cif (cif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Contactos
CREATE TABLE contactos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100) NULL,
    notas TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Relación empresa-contacto
CREATE TABLE empresa_contactos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    contacto_id BIGINT UNSIGNED NOT NULL,
    puesto VARCHAR(100) NULL,
    categoria VARCHAR(100) NULL,
    es_usuario BOOLEAN NOT NULL DEFAULT FALSE,
    es_principal BOOLEAN NOT NULL DEFAULT FALSE,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_empresa_contactos_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_empresa_contactos_contacto
        FOREIGN KEY (contacto_id) REFERENCES contactos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY uq_empresa_contacto (empresa_id, contacto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Direcciones (empresa o contacto)
CREATE TABLE direcciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NULL,
    contacto_id BIGINT UNSIGNED NULL,
    linea1 VARCHAR(255) NOT NULL,
    linea2 VARCHAR(255) NULL,
    codigo_postal VARCHAR(15) NULL,
    localidad VARCHAR(120) NULL,
    provincia VARCHAR(120) NULL,
    pais VARCHAR(120) NOT NULL DEFAULT 'España',
    tipo_direccion ENUM('fiscal', 'social', 'delegacion', 'principal', 'obra', 'otra') NOT NULL DEFAULT 'principal',
    descripcion VARCHAR(255) NULL,
    es_principal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_direcciones_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_direcciones_contacto
        FOREIGN KEY (contacto_id) REFERENCES contactos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_direcciones_owner
        CHECK (
            (empresa_id IS NOT NULL AND contacto_id IS NULL)
            OR
            (empresa_id IS NULL AND contacto_id IS NOT NULL)
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Teléfonos
CREATE TABLE telefonos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NULL,
    contacto_id BIGINT UNSIGNED NULL,
    prefijo VARCHAR(10) NULL,
    numero VARCHAR(30) NOT NULL,
    tipo_telefono ENUM('fijo', 'movil', 'otro') NOT NULL DEFAULT 'movil',
    descripcion VARCHAR(255) NULL,
    es_principal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_telefonos_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_telefonos_contacto
        FOREIGN KEY (contacto_id) REFERENCES contactos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_telefonos_owner
        CHECK (
            (empresa_id IS NOT NULL AND contacto_id IS NULL)
            OR
            (empresa_id IS NULL AND contacto_id IS NOT NULL)
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Emails
CREATE TABLE emails (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NULL,
    contacto_id BIGINT UNSIGNED NULL,
    email VARCHAR(190) NOT NULL,
    tipo_email ENUM('profesional', 'personal', 'otro') NOT NULL DEFAULT 'profesional',
    descripcion VARCHAR(255) NULL,
    es_principal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_emails_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_emails_contacto
        FOREIGN KEY (contacto_id) REFERENCES contactos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_emails_owner
        CHECK (
            (empresa_id IS NOT NULL AND contacto_id IS NULL)
            OR
            (empresa_id IS NULL AND contacto_id IS NOT NULL)
        ),

    UNIQUE KEY uq_emails_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) Estaciones de servicio
CREATE TABLE estaciones_servicio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    fecha_baja DATE NULL,
    fecha_alta_modificacion DATE NULL,
    codigo_retailgas VARCHAR(50) NULL,
    codigo_sociedad VARCHAR(50) NULL,
    concesion VARCHAR(150) NULL,
    tipo_estacion VARCHAR(100) NULL,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(255) NULL,
    codigo_postal VARCHAR(15) NULL,
    poblacion VARCHAR(120) NULL,
    provincia VARCHAR(120) NULL,
    ccaa VARCHAR(120) NULL,
    numero_margenes INT NULL,
    y_wgs84 DECIMAL(10,7) NULL,
    x_wgs84 DECIMAL(10,7) NULL,
    vinculo VARCHAR(255) NULL,
    vinculo_2 VARCHAR(255) NULL,
    delegacion VARCHAR(120) NULL,
    delegado VARCHAR(120) NULL,
    tecnico_gestion VARCHAR(150) NULL,
    telefono_tecnico_gestion VARCHAR(30) NULL,
    email_tecnico_gestion VARCHAR(190) NULL,
    responsable_gestor VARCHAR(150) NULL,
    telefono_movil VARCHAR(30) NULL,
    telefono_oficina VARCHAR(30) NULL,
    sede_email VARCHAR(190) NULL,
    tipo_mantenimiento VARCHAR(120) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_estaciones_servicio_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    UNIQUE KEY uq_estaciones_servicio_codigo_cliente (empresa_cliente_id, codigo_retailgas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8) Tarifarios
CREATE TABLE tarifarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    fecha_tarifario DATE NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tarifarios_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9) Servicios
CREATE TABLE servicios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    unidad_medida VARCHAR(50) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_servicios_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Relación tarifario-servicio
CREATE TABLE tarifario_servicios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarifario_id BIGINT UNSIGNED NOT NULL,
    servicio_id BIGINT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tarifario_servicios_tarifario
        FOREIGN KEY (tarifario_id) REFERENCES tarifarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_tarifario_servicios_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    UNIQUE KEY uq_tarifario_servicio (tarifario_id, servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11) Presupuestos
CREATE TABLE presupuestos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_presupuesto VARCHAR(50) NULL,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    empresa_contacto_id BIGINT UNSIGNED NULL,
    estacion_servicio_id BIGINT UNSIGNED NULL,
    tarifario_id BIGINT UNSIGNED NULL,
    fecha_presupuesto DATE NOT NULL,
    estado ENUM('borrador', 'enviado', 'aceptado', 'rechazado', 'caducado', 'cancelado') NOT NULL DEFAULT 'borrador',
    fecha_aceptacion DATE NULL,
    importe_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_presupuestos_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_presupuestos_empresa_contacto
        FOREIGN KEY (empresa_contacto_id) REFERENCES empresa_contactos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_presupuestos_estacion_servicio
        FOREIGN KEY (estacion_servicio_id) REFERENCES estaciones_servicio(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_presupuestos_tarifario
        FOREIGN KEY (tarifario_id) REFERENCES tarifarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    UNIQUE KEY uq_presupuestos_codigo (codigo_presupuesto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12) Líneas de presupuesto
CREATE TABLE presupuesto_lineas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presupuesto_id BIGINT UNSIGNED NOT NULL,
    servicio_id BIGINT UNSIGNED NOT NULL,
    descripcion VARCHAR(255) NULL,
    unidades DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    precio_unitario DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    orden INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_presupuesto_lineas_presupuesto
        FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_presupuesto_lineas_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13) Proyectos
CREATE TABLE proyectos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presupuesto_id BIGINT UNSIGNED NULL,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    responsable_ciete_id BIGINT UNSIGNED NULL,
    responsable_cliente_id BIGINT UNSIGNED NULL,
    tecnico_obra_cliente_id BIGINT UNSIGNED NULL,
    estacion_servicio_id BIGINT UNSIGNED NULL,
    tarifario_id BIGINT UNSIGNED NULL,
    numero_proyecto_ciete VARCHAR(50) NOT NULL,
    fecha_encargo DATE NOT NULL,
    trabajo_terminado BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_fin DATE NULL,
    descripcion_libre TEXT NULL,
    descripcion_seleccionable ENUM('mantenimiento', 'tiendas', 'imagen_y_obras', 'legalizacion', 'proyecto_oficial', 'estudio', 'otro') NULL,
    estado ENUM('pendiente', 'en_curso', 'terminado', 'facturado', 'cerrado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_proyectos_presupuesto
        FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_responsable_ciete
        FOREIGN KEY (responsable_ciete_id) REFERENCES empresa_contactos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_responsable_cliente
        FOREIGN KEY (responsable_cliente_id) REFERENCES empresa_contactos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_tecnico_obra_cliente
        FOREIGN KEY (tecnico_obra_cliente_id) REFERENCES empresa_contactos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_estacion_servicio
        FOREIGN KEY (estacion_servicio_id) REFERENCES estaciones_servicio(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyectos_tarifario
        FOREIGN KEY (tarifario_id) REFERENCES tarifarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    UNIQUE KEY uq_proyectos_numero_proyecto_ciete (numero_proyecto_ciete)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14) Pedidos
CREATE TABLE pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presupuesto_id BIGINT UNSIGNED NULL,
    proyecto_id BIGINT UNSIGNED NULL,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    estacion_servicio_id BIGINT UNSIGNED NULL,
    tarifario_id BIGINT UNSIGNED NULL,
    numero_aviso VARCHAR(50) NULL,
    numero_pedido VARCHAR(100) NULL,
    fecha_solicitud_pedido DATE NULL,
    fecha_solicitud_autofactura DATE NULL,
    fecha_recepcion_pedido DATE NULL,
    estado ENUM('pendiente', 'solicitado', 'recibido', 'parcial', 'cerrado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    importe_pedido DECIMAL(12,2) NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pedidos_presupuesto
        FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedidos_proyecto
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedidos_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedidos_estacion_servicio
        FOREIGN KEY (estacion_servicio_id) REFERENCES estaciones_servicio(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedidos_tarifario
        FOREIGN KEY (tarifario_id) REFERENCES tarifarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    UNIQUE KEY uq_pedidos_numero_pedido (numero_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15) Líneas de pedido
CREATE TABLE pedidos_lineas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NOT NULL,
    servicio_id BIGINT UNSIGNED NOT NULL,
    descripcion VARCHAR(255) NULL,
    unidades DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    precio_unitario DECIMAL(12,2) NULL,
    subtotal DECIMAL(12,2) NULL,
    orden INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pedidos_lineas_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedidos_lineas_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16) Comentarios de proyecto
CREATE TABLE proyecto_comentarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id BIGINT UNSIGNED NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_proyecto_comentarios_proyecto
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17) Legalizaciones
CREATE TABLE legalizaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id BIGINT UNSIGNED NOT NULL,
    tipologia VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    estado ENUM('pendiente', 'en_tramite', 'resuelta', 'cancelada') NOT NULL DEFAULT 'pendiente',
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_legalizaciones_proyecto
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18) Contactos de legalización
CREATE TABLE legalizacion_contactos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legalizacion_id BIGINT UNSIGNED NOT NULL,
    empresa_contacto_id BIGINT UNSIGNED NOT NULL,
    rol VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_legalizacion_contactos_legalizacion
        FOREIGN KEY (legalizacion_id) REFERENCES legalizaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_legalizacion_contactos_empresa_contacto
        FOREIGN KEY (empresa_contacto_id) REFERENCES empresa_contactos(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    UNIQUE KEY uq_legalizacion_contacto (legalizacion_id, empresa_contacto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19) Comentarios de legalización
CREATE TABLE legalizacion_comentarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legalizacion_id BIGINT UNSIGNED NOT NULL,
    fecha_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comentario TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_legalizacion_comentarios_legalizacion
        FOREIGN KEY (legalizacion_id) REFERENCES legalizaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20) Facturas
CREATE TABLE facturas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NULL,
    proyecto_id BIGINT UNSIGNED NULL,
    empresa_cliente_id BIGINT UNSIGNED NOT NULL,
    estacion_servicio_id BIGINT UNSIGNED NULL,
    tarifario_id BIGINT UNSIGNED NULL,
    direccion_facturacion_id BIGINT UNSIGNED NULL,
    numero_factura VARCHAR(100) NOT NULL,
    fecha_factura DATE NOT NULL,
    tipo_emision ENUM('emitida_por_ciete', 'autofactura_cliente') NOT NULL DEFAULT 'emitida_por_ciete',
    estado ENUM('borrador', 'emitida', 'cobrada_parcial', 'cobrada_total', 'anulada') NOT NULL DEFAULT 'emitida',
    importe_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_facturas_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_facturas_proyecto
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_facturas_empresa_cliente
        FOREIGN KEY (empresa_cliente_id) REFERENCES empresas(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_facturas_estacion_servicio
        FOREIGN KEY (estacion_servicio_id) REFERENCES estaciones_servicio(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_facturas_tarifario
        FOREIGN KEY (tarifario_id) REFERENCES tarifarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_facturas_direccion_facturacion
        FOREIGN KEY (direccion_facturacion_id) REFERENCES direcciones(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    UNIQUE KEY uq_facturas_numero_factura (numero_factura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21) Líneas de factura
CREATE TABLE factura_lineas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED NOT NULL,
    servicio_id BIGINT UNSIGNED NOT NULL,
    descripcion VARCHAR(255) NULL,
    unidades DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    precio_unitario DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    orden INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_factura_lineas_factura
        FOREIGN KEY (factura_id) REFERENCES facturas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_factura_lineas_servicio
        FOREIGN KEY (servicio_id) REFERENCES servicios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22) Cobros
CREATE TABLE cobros (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED NOT NULL,
    importe DECIMAL(12,2) NOT NULL,
    fecha_cobro DATE NOT NULL,
    tipo_cobro ENUM('transferencia', 'giro', 'efectivo', 'confirming', 'otro') NOT NULL DEFAULT 'transferencia',
    cuenta_bancaria VARCHAR(100) NULL,
    referencia VARCHAR(100) NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_cobros_factura
        FOREIGN KEY (factura_id) REFERENCES facturas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23) Usuarios (compatibilidad inicial + RBAC)
CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_contacto_id BIGINT UNSIGNED NOT NULL,
    empresa_contexto_id BIGINT UNSIGNED NULL,
    nombre_usuario VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol_legacy ENUM('admin', 'user', 'gestion', 'tecnico', 'consulta') NOT NULL DEFAULT 'user',
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_login_at DATETIME NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_empresa_contacto
        FOREIGN KEY (empresa_contacto_id) REFERENCES empresa_contactos(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuarios_empresa_contexto
        FOREIGN KEY (empresa_contexto_id) REFERENCES empresas(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario),
    UNIQUE KEY uq_usuarios_empresa_contacto (empresa_contacto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24) Roles
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    es_sistema BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25) Permisos
CREATE TABLE permisos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(120) NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    modulo VARCHAR(80) NOT NULL,
    accion VARCHAR(80) NOT NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permisos_codigo (codigo),
    UNIQUE KEY uq_permisos_modulo_accion (modulo, accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26) Relación rol-permiso
CREATE TABLE rol_permisos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id BIGINT UNSIGNED NOT NULL,
    permiso_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rol_permisos_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_rol_permisos_permiso
        FOREIGN KEY (permiso_id) REFERENCES permisos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY uq_rol_permiso (rol_id, permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27) Relación usuario-rol (con alcance por empresa opcional)
CREATE TABLE usuario_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    rol_id BIGINT UNSIGNED NOT NULL,
    empresa_contexto_id BIGINT UNSIGNED NULL,
    es_principal BOOLEAN NOT NULL DEFAULT FALSE,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_roles_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_roles_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_roles_empresa_contexto
        FOREIGN KEY (empresa_contexto_id) REFERENCES empresas(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28) Permisos directos por usuario (allow/deny)
CREATE TABLE usuario_permisos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    permiso_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('allow', 'deny') NOT NULL DEFAULT 'allow',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_permisos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_permisos_permiso
        FOREIGN KEY (permiso_id) REFERENCES permisos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY uq_usuario_permiso (usuario_id, permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29) Historial de estados de proyecto
CREATE TABLE proyecto_historial_estados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id BIGINT UNSIGNED NOT NULL,
    estado_anterior ENUM('pendiente', 'en_curso', 'terminado', 'facturado', 'cerrado', 'cancelado') NULL,
    estado_nuevo ENUM('pendiente', 'en_curso', 'terminado', 'facturado', 'cerrado', 'cancelado') NOT NULL,
    fecha_cambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id BIGINT UNSIGNED NULL,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_proyecto_historial_estados_proyecto
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_proyecto_historial_estados_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30) Auditoría de cambios
CREATE TABLE auditoria_cambios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidad VARCHAR(100) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,
    accion ENUM('create', 'update', 'delete', 'status_change', 'login', 'logout', 'other') NOT NULL DEFAULT 'other',
    usuario_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    detalle_json JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_cambios_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31) Sesiones
CREATE TABLE sesiones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_hora_login DATETIME NOT NULL,
    fecha_hora_logout DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_sesiones_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices recomendados
CREATE INDEX idx_estaciones_servicio_nombre ON estaciones_servicio(nombre);
CREATE INDEX idx_estaciones_servicio_cliente_nombre ON estaciones_servicio(empresa_cliente_id, nombre);
CREATE INDEX idx_presupuestos_fecha_estado ON presupuestos(fecha_presupuesto, estado);
CREATE INDEX idx_proyectos_fecha_estado ON proyectos(fecha_encargo, estado);
CREATE INDEX idx_proyectos_cliente_estado_fecha ON proyectos(empresa_cliente_id, estado, fecha_encargo);
CREATE INDEX idx_pedidos_numero_aviso ON pedidos(numero_aviso);
CREATE INDEX idx_pedidos_cliente_estado ON pedidos(empresa_cliente_id, estado);
CREATE INDEX idx_facturas_fecha_estado ON facturas(fecha_factura, estado);
CREATE INDEX idx_facturas_cliente_estado_fecha ON facturas(empresa_cliente_id, estado, fecha_factura);
CREATE INDEX idx_cobros_fecha ON cobros(fecha_cobro);
CREATE INDEX idx_usuario_roles_usuario_activo ON usuario_roles(usuario_id, activo);
CREATE INDEX idx_proyecto_historial_estados_proyecto_fecha ON proyecto_historial_estados(proyecto_id, fecha_cambio);
CREATE INDEX idx_auditoria_cambios_entidad_fecha ON auditoria_cambios(entidad, created_at);
CREATE INDEX idx_auditoria_cambios_usuario_fecha ON auditoria_cambios(usuario_id, created_at);

-- Semilla RBAC inicial
INSERT INTO roles (codigo, nombre, descripcion, es_sistema) VALUES
('admin', 'Administrador', 'Acceso completo a todos los módulos', TRUE),
('gestion', 'Gestión', 'Operación completa de negocio sin permisos de sistema', TRUE),
('tecnico', 'Técnico', 'Operativa técnica de proyectos y legalizaciones', TRUE),
('consulta', 'Consulta', 'Acceso de solo lectura a datos e informes', TRUE),
('control_cierre', 'Control de cierre', 'Rol especial para edición de trabajos cerrados', TRUE)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    es_sistema = VALUES(es_sistema);

INSERT INTO permisos (codigo, nombre, modulo, accion, descripcion) VALUES
('proyecto_editar', 'Editar proyecto', 'proyectos', 'editar', 'Permite edición general de proyectos'),
('proyecto_cerrar', 'Cerrar proyecto', 'proyectos', 'cerrar', 'Permite cambiar estado a cerrado'),
('proyecto_editar_cerrado', 'Editar proyecto cerrado', 'proyectos', 'editar_cerrado', 'Permite editar proyectos en estado cerrado'),
('proyecto_reabrir_cerrado', 'Reabrir proyecto cerrado', 'proyectos', 'reabrir', 'Permite reabrir proyectos cerrados'),
('pedido_gestionar', 'Gestionar pedidos', 'pedidos', 'gestionar', 'Alta y edición de pedidos'),
('factura_gestionar', 'Gestionar facturas', 'facturas', 'gestionar', 'Alta y edición de facturas'),
('legalizacion_gestionar', 'Gestionar legalizaciones', 'legalizaciones', 'gestionar', 'Alta y edición de legalizaciones'),
('informe_ver', 'Ver informes', 'informes', 'ver', 'Consulta de informes operativos')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    modulo = VALUES(modulo),
    accion = VALUES(accion),
    descripcion = VALUES(descripcion);

INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p
WHERE
    r.codigo = 'admin'
    OR (r.codigo = 'gestion' AND p.codigo IN (
        'proyecto_editar', 'proyecto_cerrar', 'pedido_gestionar', 'factura_gestionar', 'legalizacion_gestionar', 'informe_ver'
    ))
    OR (r.codigo = 'tecnico' AND p.codigo IN (
        'proyecto_editar', 'legalizacion_gestionar', 'informe_ver'
    ))
    OR (r.codigo = 'consulta' AND p.codigo IN (
        'informe_ver'
    ))
    OR (r.codigo = 'control_cierre' AND p.codigo IN (
        'proyecto_editar_cerrado', 'proyecto_reabrir_cerrado', 'proyecto_cerrar'
    ))
ON DUPLICATE KEY UPDATE
    permiso_id = VALUES(permiso_id);

-- Regla cliente (ejemplo de asignación):
-- Para cumplir "solo César puede modificar trabajos cerrados", asignar a su usuario el rol control_cierre:
-- INSERT INTO usuario_roles (usuario_id, rol_id, es_principal, activo)
-- SELECT u.id, r.id, FALSE, TRUE
-- FROM usuarios u
-- JOIN roles r ON r.codigo = 'control_cierre'
-- WHERE u.nombre_usuario = 'cesar';
