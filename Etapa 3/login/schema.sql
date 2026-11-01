-- =====================================================================
-- Práctica Login / Tacos Tony Inventario Móvil - Alumno 3478788
-- BD compartida: uzfjkpte_equipo4  |  Tabla con prefijo: 3478788_usuarios
-- Alineado con Contrato Técnico v1.1 (RF-01, ADR-02, RNF-05)
-- =====================================================================

USE `uzfjkpte_equipo4`;

CREATE TABLE IF NOT EXISTS `3478788_usuarios` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `usuario`       VARCHAR(50)   NOT NULL,
    `password_hash` VARCHAR(255)  NOT NULL,
    `nombre`        VARCHAR(100)  NOT NULL,
    `rol`           ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE',
    `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
    `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- NOTA DE SEGURIDAD (Requisito de Entrega):
-- No se insertan contraseñas en texto plano aquí.
-- Para registrar al usuario de prueba (fer.leon_05 / 1234567890):
-- 1. Abre en tu navegador: https://equipo4.centrofuyuan.com/3478788/login/setup.php
-- 2. Selecciona rol "GERENTE", ingresa contraseña y pulsa "Crear usuario".
-- 3. setup.php aplica password_hash() con bcrypt (costo 10).
-- 4. Elimina setup.php del servidor una vez creado.
-- ---------------------------------------------------------------------
