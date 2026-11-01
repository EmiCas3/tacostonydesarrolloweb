-- =====================================================================
-- Tacos Tony Inventario Móvil — Esquema Completo del MVP (Etapa 3)
-- Alumno: Fernando León Del Río (ID: 3478788)
-- BD compartida: uzfjkpte_equipo4  |  Prefijo físico: 3478788_
-- Alineado con Contrato Técnico v1.1 (Sección 3.1, RF-01 a RF-10, RNF-05, RNF-06)
-- =====================================================================

USE `uzfjkpte_equipo4`;

-- 1. Tabla de Usuarios (RF-01, RNF-05)
CREATE TABLE IF NOT EXISTS `3478788_usuarios` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `usuario`       VARCHAR(50)   NOT NULL,
    `correo`        VARCHAR(100)  DEFAULT NULL,
    `password_hash` VARCHAR(255)  NOT NULL,
    `nombre`        VARCHAR(100)  NOT NULL,
    `rol`           ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE',
    `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
    `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usuario` (`usuario`),
    UNIQUE KEY `uk_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Insumos / Materias Primas (RF-02, RF-08, RF-09)
CREATE TABLE IF NOT EXISTS `3478788_insumos` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(100)  NOT NULL,
    `unidad_medida` ENUM('kg', 'g', 'L', 'ml', 'pieza') NOT NULL DEFAULT 'kg',
    `categoria`     ENUM('Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables') NOT NULL DEFAULT 'Carnes',
    `stock_actual`  DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `stock_minimo`  DECIMAL(10,3) NOT NULL DEFAULT 1.000,
    `estado`        ENUM('normal', 'bajo', 'discrepancia') NOT NULL DEFAULT 'normal',
    `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
    `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_insumos_estado_activo` (`activo`, `estado`),
    KEY `idx_insumos_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Productos del Menú (RF-03, RF-04)
CREATE TABLE IF NOT EXISTS `3478788_productos` (
    `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`    VARCHAR(100)  NOT NULL,
    `categoria` VARCHAR(50)   NOT NULL DEFAULT 'Especialidades Árabes',
    `precio`    DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
    `imagen`    VARCHAR(150)  DEFAULT NULL,
    `activo`    TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Recetas por Producto en DECIMAL(10,3) (RF-03, RF-05)
CREATE TABLE IF NOT EXISTS `3478788_recetas` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `producto_id` INT UNSIGNED  NOT NULL,
    `insumo_id`   INT UNSIGNED  NOT NULL,
    `cantidad`    DECIMAL(10,3) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_producto_insumo` (`producto_id`, `insumo_id`),
    CONSTRAINT `fk_3478788_receta_prod` FOREIGN KEY (`producto_id`) REFERENCES `3478788_productos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_3478788_receta_ins`  FOREIGN KEY (`insumo_id`)   REFERENCES `3478788_insumos` (`id`)   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla Maestra de Ventas (RF-04, RF-05)
CREATE TABLE IF NOT EXISTS `3478788_ventas` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED  NOT NULL,
    `fecha_hora` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    KEY `idx_ventas_fecha` (`fecha_hora`),
    CONSTRAINT `fk_3478788_venta_usr` FOREIGN KEY (`usuario_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de Detalles de Venta (RF-04)
CREATE TABLE IF NOT EXISTS `3478788_venta_detalles` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `venta_id`        INT UNSIGNED  NOT NULL,
    `producto_id`     INT UNSIGNED  NOT NULL,
    `cantidad`        INT UNSIGNED  NOT NULL,
    `precio_unitario` DECIMAL(8,2)  NOT NULL,
    `subtotal`        DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_3478788_vdet_venta` FOREIGN KEY (`venta_id`)    REFERENCES `3478788_ventas` (`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_3478788_vdet_prod`  FOREIGN KEY (`producto_id`) REFERENCES `3478788_productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Libro Mayor Inmutable de Movimientos de Inventario (RF-06, RF-07, RF-10, RNF-06)
CREATE TABLE IF NOT EXISTS `3478788_movimientos_inventario` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `insumo_id`       INT UNSIGNED  NOT NULL,
    `usuario_id`      INT UNSIGNED  NOT NULL,
    `tipo_movimiento` ENUM('VENTA', 'ENTRADA', 'MERMA') NOT NULL,
    `cantidad`        DECIMAL(10,3) NOT NULL COMMENT 'Negativa en VENTA/MERMA, positiva en ENTRADA',
    `stock_previo`    DECIMAL(10,3) NOT NULL,
    `stock_posterior` DECIMAL(10,3) NOT NULL,
    `motivo`          ENUM('caducidad', 'dano', 'preparacion', 'otro') DEFAULT NULL,
    `observaciones`   VARCHAR(255)  DEFAULT NULL,
    `referencia_id`   INT UNSIGNED  DEFAULT NULL COMMENT 'ID de venta si tipo = VENTA',
    `fecha_hora`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mov_insumo_fecha` (`insumo_id`, `fecha_hora`),
    KEY `idx_mov_tipo_fecha` (`tipo_movimiento`, `fecha_hora`),
    CONSTRAINT `fk_3478788_mov_ins` FOREIGN KEY (`insumo_id`)  REFERENCES `3478788_insumos` (`id`)  ON DELETE RESTRICT,
    CONSTRAINT `fk_3478788_mov_usr` FOREIGN KEY (`usuario_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
