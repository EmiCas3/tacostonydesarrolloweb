-- =====================================================================
-- Tacos Tony Inventario Móvil — Esquema Completo Final (Etapa 4 · v2.0)
-- Alumno: Fernando León Del Río (ID: 3478788)
-- BD compartida: uzfjkpte_equipo4  |  Prefijo físico: 3478788_
-- Alineado con Contrato Técnico v2.0 (RF-01 a RF-17, RNF-05, RNF-06)
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
    `categoria`     ENUM('Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables', 'Otros') NOT NULL DEFAULT 'Carnes',
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

-- 7. Libro Mayor Inmutable de Movimientos de Inventario (RF-06, RF-07, RF-10, RF-13, RF-17, RNF-06)
CREATE TABLE IF NOT EXISTS `3478788_movimientos_inventario` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `insumo_id`       INT UNSIGNED  NOT NULL,
    `usuario_id`      INT UNSIGNED  NOT NULL,
    `tipo_movimiento` ENUM('VENTA', 'ENTRADA', 'MERMA', 'AJUSTE', 'RECEPCION') NOT NULL,
    `cantidad`        DECIMAL(10,3) NOT NULL COMMENT 'Negativa en VENTA/MERMA/AJUSTE faltante, positiva en ENTRADA/RECEPCION/AJUSTE sobrante',
    `stock_previo`    DECIMAL(10,3) NOT NULL,
    `stock_posterior` DECIMAL(10,3) NOT NULL,
    `motivo`          ENUM('caducidad', 'dano', 'preparacion', 'otro', 'conteo_fisico') DEFAULT NULL,
    `observaciones`   VARCHAR(255)  DEFAULT NULL,
    `referencia_id`   INT UNSIGNED  DEFAULT NULL COMMENT 'ID de venta, conteo o pedido asociado',
    `fecha_hora`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mov_insumo_fecha` (`insumo_id`, `fecha_hora`),
    KEY `idx_mov_tipo_fecha` (`tipo_movimiento`, `fecha_hora`),
    CONSTRAINT `fk_3478788_mov_ins` FOREIGN KEY (`insumo_id`)  REFERENCES `3478788_insumos` (`id`)  ON DELETE RESTRICT,
    CONSTRAINT `fk_3478788_mov_usr` FOREIGN KEY (`usuario_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Conteos Físicos con Conciliación (RF-17 — Cambio del cliente Equipo 4)
CREATE TABLE IF NOT EXISTS `3478788_conteos_fisicos` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_captura_id` INT UNSIGNED NOT NULL,
    `usuario_aprueba_id` INT UNSIGNED DEFAULT NULL,
    `estado`             ENUM('PENDIENTE', 'CONFIRMADO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    `observaciones`      VARCHAR(255) DEFAULT NULL,
    `fecha_captura`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_confirmacion` DATETIME     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_conteos_estado_fecha` (`estado`, `fecha_captura`),
    CONSTRAINT `fk_3478788_conteo_cap` FOREIGN KEY (`usuario_captura_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_3478788_conteo_apr` FOREIGN KEY (`usuario_aprueba_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Detalles por Insumo de cada Conteo Físico (RF-17)
CREATE TABLE IF NOT EXISTS `3478788_conteo_detalles` (
    `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `conteo_id`         INT UNSIGNED  NOT NULL,
    `insumo_id`         INT UNSIGNED  NOT NULL,
    `stock_sistema`     DECIMAL(10,3) NOT NULL,
    `stock_contado`     DECIMAL(10,3) NOT NULL,
    `diferencia`        DECIMAL(10,3) NOT NULL COMMENT 'stock_contado - stock_sistema',
    `estado_resultante` ENUM('normal', 'bajo', 'discrepancia') NOT NULL DEFAULT 'normal',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_conteo_insumo` (`conteo_id`, `insumo_id`),
    CONSTRAINT `fk_3478788_cdet_conteo` FOREIGN KEY (`conteo_id`) REFERENCES `3478788_conteos_fisicos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_3478788_cdet_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `3478788_insumos` (`id`)         ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Pedidos de Reabastecimiento (RF-11, RF-12, RF-13)
CREATE TABLE IF NOT EXISTS `3478788_pedidos` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_crea_id`   INT UNSIGNED NOT NULL,
    `usuario_recibe_id` INT UNSIGNED DEFAULT NULL,
    `dias_cobertura`    INT UNSIGNED NOT NULL DEFAULT 7,
    `estado`            ENUM('PENDIENTE', 'RECIBIDO', 'CANCELADO') NOT NULL DEFAULT 'PENDIENTE',
    `observaciones`     VARCHAR(255) DEFAULT NULL,
    `fecha_creacion`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_recepcion`   DATETIME     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pedidos_estado_fecha` (`estado`, `fecha_creacion`),
    CONSTRAINT `fk_3478788_ped_crea`   FOREIGN KEY (`usuario_crea_id`)   REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_3478788_ped_recibe` FOREIGN KEY (`usuario_recibe_id`) REFERENCES `3478788_usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Detalles de Pedidos de Reabastecimiento (RF-12, RF-13)
CREATE TABLE IF NOT EXISTS `3478788_pedido_detalles` (
    `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `pedido_id`         INT UNSIGNED  NOT NULL,
    `insumo_id`         INT UNSIGNED  NOT NULL,
    `cantidad_pedida`   DECIMAL(10,3) NOT NULL,
    `cantidad_recibida` DECIMAL(10,3) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pedido_insumo` (`pedido_id`, `insumo_id`),
    CONSTRAINT `fk_3478788_pdet_ped` FOREIGN KEY (`pedido_id`) REFERENCES `3478788_pedidos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_3478788_pdet_ins` FOREIGN KEY (`insumo_id`) REFERENCES `3478788_insumos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Catálogo de Proveedores del Sistema Original (t_proovedores — RF-06)
CREATE TABLE IF NOT EXISTS `3478788_proveedores` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`          VARCHAR(80)  NOT NULL,
    `numero_telefono` VARCHAR(45)  NOT NULL,
    `correo`          VARCHAR(80)  NOT NULL,
    `activo`          TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_prov_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
