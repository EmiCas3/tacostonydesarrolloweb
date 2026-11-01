<?php
/**
 * Inicializador automático del esquema y datos semilla del MVP (Etapa 3)
 * Proyecto: Tacos Tony Inventario Móvil · Alumno: 3478788
 * Garantiza que las 7 tablas con prefijo 3478788_ existan y tengan los insumos,
 * productos reales e imágenes oficiales de Tacos Tony Camino Real.
 */

require_once __DIR__ . '/config.php';

function inicializarEsquemaMvp(PDO $pdo): void
{
    static $inicializado = false;
    if ($inicializado) {
        return;
    }

    // 1. Tabla de Usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_USUARIOS . " (
        `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `usuario`       VARCHAR(50)   NOT NULL,
        `correo`        VARCHAR(100)  DEFAULT NULL,
        `password_hash` VARCHAR(255)  NOT NULL,
        `nombre`        VARCHAR(100)  NOT NULL,
        `rol`           ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE',
        `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
        `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_usuario` (`usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Asegurar columnas de retrocompatibilidad en usuarios
    $colsUsr = $pdo->query("SHOW COLUMNS FROM " . TABLA_USUARIOS)->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('correo', $colsUsr, true)) {
        $pdo->exec("ALTER TABLE " . TABLA_USUARIOS . " ADD COLUMN `correo` VARCHAR(100) DEFAULT NULL AFTER `usuario`");
    }
    if (!in_array('rol', $colsUsr, true)) {
        $pdo->exec("ALTER TABLE " . TABLA_USUARIOS . " ADD COLUMN `rol` ENUM('GERENTE', 'COLABORADOR') NOT NULL DEFAULT 'GERENTE' AFTER `nombre`");
    }
    if (!in_array('activo', $colsUsr, true)) {
        $pdo->exec("ALTER TABLE " . TABLA_USUARIOS . " ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `rol`");
    }

    // 2. Tabla de Insumos (RF-02, RF-08, RF-09)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_INSUMOS . " (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Tabla de Productos del Menú (RF-03, RF-04)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_PRODUCTOS . " (
        `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `nombre`    VARCHAR(100)  NOT NULL,
        `categoria` VARCHAR(50)   NOT NULL DEFAULT 'Especialidades Árabes',
        `precio`    DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
        `imagen`    VARCHAR(150)  DEFAULT NULL,
        `activo`    TINYINT(1)    NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 4. Tabla de Recetas (RF-03, RF-05)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_RECETAS . " (
        `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `producto_id` INT UNSIGNED  NOT NULL,
        `insumo_id`   INT UNSIGNED  NOT NULL,
        `cantidad`    DECIMAL(10,3) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_producto_insumo` (`producto_id`, `insumo_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Tabla de Ventas (RF-04, RF-05)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_VENTAS . " (
        `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `usuario_id` INT UNSIGNED  NOT NULL,
        `fecha_hora` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `total`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (`id`),
        KEY `idx_ventas_fecha` (`fecha_hora`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 6. Tabla de Detalles de Venta (RF-04)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_VENTA_DETALLES . " (
        `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `venta_id`        INT UNSIGNED  NOT NULL,
        `producto_id`     INT UNSIGNED  NOT NULL,
        `cantidad`        INT UNSIGNED  NOT NULL,
        `precio_unitario` DECIMAL(8,2)  NOT NULL,
        `subtotal`        DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 7. Libro Mayor Inmutable de Movimientos de Inventario (RF-06, RF-07, RF-10, RNF-06)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_MOVIMIENTOS . " (
        `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `insumo_id`       INT UNSIGNED  NOT NULL,
        `usuario_id`      INT UNSIGNED  NOT NULL,
        `tipo_movimiento` ENUM('VENTA', 'ENTRADA', 'MERMA') NOT NULL,
        `cantidad`        DECIMAL(10,3) NOT NULL,
        `stock_previo`    DECIMAL(10,3) NOT NULL,
        `stock_posterior` DECIMAL(10,3) NOT NULL,
        `motivo`          ENUM('caducidad', 'dano', 'preparacion', 'otro') DEFAULT NULL,
        `observaciones`   VARCHAR(255)  DEFAULT NULL,
        `referencia_id`   INT UNSIGNED  DEFAULT NULL,
        `fecha_hora`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_mov_insumo_fecha` (`insumo_id`, `fecha_hora`),
        KEY `idx_mov_tipo_fecha` (`tipo_movimiento`, `fecha_hora`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Sembrar usuarios de prueba (GERENTE y COLABORADOR) si no existen
    $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_USUARIOS)->fetchColumn();
    if ($totalUsuarios === 0) {
        $hashDemo = password_hash('1234567890', PASSWORD_BCRYPT, ['cost' => 10]);
        $stmtUsr  = $pdo->prepare(
            "INSERT INTO " . TABLA_USUARIOS . " (usuario, correo, password_hash, nombre, rol, activo)
             VALUES (:u, :c, :h, :n, :r, 1)"
        );
        $stmtUsr->execute([
            ':u' => 'fer.leon_05',
            ':c' => 'gerente@tacostony.com',
            ':h' => $hashDemo,
            ':n' => 'Fernando León (Gerente)',
            ':r' => 'GERENTE',
        ]);
        $stmtUsr->execute([
            ':u' => 'colab.tony',
            ':c' => 'colaborador@tacostony.com',
            ':h' => $hashDemo,
            ':n' => 'Beto Ramírez (Colaborador)',
            ':r' => 'COLABORADOR',
        ]);
    } else {
        // Asegurar que exista al menos una cuenta COLABORADOR para probar RF-01 / CA-01.1 / CA-02.2
        $existeColab = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_USUARIOS . " WHERE rol = 'COLABORADOR'")->fetchColumn();
        if ($existeColab === 0) {
            $hashColab = password_hash('1234567890', PASSWORD_BCRYPT, ['cost' => 10]);
            $stmtColab = $pdo->prepare(
                "INSERT IGNORE INTO " . TABLA_USUARIOS . " (usuario, correo, password_hash, nombre, rol, activo)
                 VALUES ('colab.tony', 'colaborador@tacostony.com', :h, 'Beto Ramírez (Colaborador)', 'COLABORADOR', 1)"
            );
            $stmtColab->execute([':h' => $hashColab]);
        }
    }

    // Sembrar insumos reales de Tacos Tony si la tabla está vacía
    $totalInsumos = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_INSUMOS)->fetchColumn();
    if ($totalInsumos === 0) {
        $insumosIniciales = [
            // [id, nombre, unidad, categoria, stock_actual, stock_minimo]
            [1,  'Carne de Cerdo Árabe (Trompo)', 'kg',    'Carnes',      18.500, 8.000],
            [2,  'Pan Árabe (Pita)',              'pieza', 'Tortillas',   45.000, 20.000],
            [3,  'Tortilla de Maíz',              'pieza', 'Tortillas',  100.000, 30.000],
            [4,  'Pan de Torta (Telera)',         'pieza', 'Tortillas',   24.000, 10.000],
            [5,  'Pan de Cemita Poblana',         'pieza', 'Tortillas',   18.000, 10.000],
            [6,  'Queso Gouda Rallado',           'kg',    'Carnes',       1.850, 2.500], // En estado BAJO para mostrar alerta RF-09
            [7,  'Cebolla Blanca Fileteada',      'kg',    'Verduras',     6.400, 3.000],
            [8,  'Cilantro Fresco',               'kg',    'Verduras',     0.450, 0.800], // En estado BAJO para mostrar alerta RF-09
            [9,  'Limones Verdes',                'kg',    'Verduras',     5.200, 2.000],
            [10, 'Salsa de Chipotle Tony',        'L',     'Salsas',       4.500, 2.000],
            [11, 'Jocoque Cremoso',               'kg',    'Salsas',       1.200, 1.500], // En estado BAJO
            [12, 'Masa de Falafel (Garbanzo)',    'kg',    'Verduras',     3.500, 1.500],
            [13, 'Agua Embotellada 600ml',        'pieza', 'Bebidas',     36.000, 12.000],
            [14, 'Refresco en Lata 355ml',        'pieza', 'Bebidas',     42.000, 15.000],
            [15, 'Servilletas y Desechables',     'pieza', 'Desechables',150.000, 50.000],
        ];

        $stmtIns = $pdo->prepare(
            "INSERT INTO " . TABLA_INSUMOS . "
             (id, nombre, unidad_medida, categoria, stock_actual, stock_minimo, estado, activo)
             VALUES (:id, :nombre, :unidad, :cat, :actual, :minimo, :estado, 1)"
        );

        foreach ($insumosIniciales as [$id, $nombre, $unidad, $cat, $actual, $minimo]) {
            $estado = calcularEstadoInsumo((float)$actual, (float)$minimo);
            $stmtIns->execute([
                ':id'     => $id,
                ':nombre' => $nombre,
                ':unidad' => $unidad,
                ':cat'    => $cat,
                ':actual' => $actual,
                ':minimo' => $minimo,
                ':estado' => $estado,
            ]);
        }
    }

    // Sembrar productos reales del menú de Tacos Tony con sus imágenes reales
    $totalProductos = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_PRODUCTOS)->fetchColumn();
    if ($totalProductos === 0) {
        $productosIniciales = [
            [1, 'Taco Árabe Tradicional',   'Especialidades Árabes', 45.00,  'assets/img/productos/TacoArabe.png'],
            [2, 'Taco Oriental (Maíz)',     'Especialidades Árabes', 28.00,  'assets/img/productos/TacoOriental.png'],
            [3, 'Torta Árabe Especial',     'Tortas y Cemitas',      72.00,  'assets/img/productos/TortaArabe.png'],
            [4, 'Cemita Árabe con Queso',   'Tortas y Cemitas',      78.00,  'assets/img/productos/CemitaArabe.png'],
            [5, 'Quesadilla Árabe',         'Con Queso Gouda',       62.00,  'assets/img/productos/QuesaArabe.png'],
            [6, 'Queso Fundido con Carne',  'Con Queso Gouda',       95.00,  'assets/img/productos/QuesoFCarne.png'],
            [7, 'Pizza Árabe Familiar',     'Con Queso Gouda',      145.00,  'assets/img/productos/PizzaArabe.png'],
            [8, 'Orden de Falafel (5 pzas)','Complementos Libaneses',65.00,  'assets/img/productos/Falafel.png'],
            [9, 'Porción Jocoque Cremoso',  'Complementos Libaneses',55.00,  'assets/img/productos/JocoCrem.png'],
            [10,'Agua Embotellada Natural', 'Bebidas',               22.00,  'assets/img/productos/Agua.png'],
        ];

        $stmtProd = $pdo->prepare(
            "INSERT INTO " . TABLA_PRODUCTOS . " (id, nombre, categoria, precio, imagen, activo)
             VALUES (:id, :nombre, :cat, :precio, :img, 1)"
        );

        foreach ($productosIniciales as [$id, $nombre, $cat, $precio, $img]) {
            $stmtProd->execute([
                ':id'     => $id,
                ':nombre' => $nombre,
                ':cat'    => $cat,
                ':precio' => $precio,
                ':img'    => $img,
            ]);
        }

        // Recetas en DECIMAL(10,3) (RF-03)
        $recetasIniciales = [
            // Taco Árabe Tradicional: 0.110 kg Carne, 1 Pan Árabe, 0.020 kg Cebolla, 0.015 L Salsa, 2 Servilletas
            [1, 1, 0.110], [1, 2, 1.000], [1, 7, 0.020], [1, 10, 0.015], [1, 15, 2.000],
            // Taco Oriental (Maíz): 0.080 kg Carne, 2 Tortillas Maíz, 0.015 kg Cebolla, 0.008 kg Cilantro, 0.015 L Salsa
            [2, 1, 0.080], [2, 3, 2.000], [2, 7, 0.015], [2, 8,  0.008], [2, 10, 0.015],
            // Torta Árabe Especial: 0.150 kg Carne, 1 Pan Torta, 0.030 kg Cebolla, 0.025 L Salsa
            [3, 1, 0.150], [3, 4, 1.000], [3, 7, 0.030], [3, 10, 0.025],
            // Cemita Árabe con Queso: 0.140 kg Carne, 1 Pan Cemita, 0.050 kg Queso Gouda, 0.025 kg Cebolla
            [4, 1, 0.140], [4, 5, 1.000], [4, 6, 0.050], [4, 7,  0.025],
            // Quesadilla Árabe: 0.090 kg Carne, 1 Pan Árabe, 0.065 kg Queso Gouda, 0.015 L Salsa
            [5, 1, 0.090], [5, 2, 1.000], [5, 6, 0.065], [5, 10, 0.015],
            // Queso Fundido con Carne: 0.120 kg Carne, 0.180 kg Queso Gouda, 2 Pan Árabe
            [6, 1, 0.120], [6, 6, 0.180], [6, 2, 2.000],
            // Pizza Árabe Familiar: 0.250 kg Carne, 2 Pan Árabe, 0.220 kg Queso Gouda, 0.040 kg Cebolla
            [7, 1, 0.250], [7, 2, 2.000], [7, 6, 0.220], [7, 7,  0.040],
            // Orden de Falafel: 0.180 kg Masa Falafel, 1 Pan Árabe, 0.050 kg Jocoque
            [8, 12, 0.180], [8, 2, 1.000], [8, 11, 0.050],
            // Porción Jocoque Cremoso: 0.150 kg Jocoque, 1 Pan Árabe
            [9, 11, 0.150], [9, 2, 1.000],
            // Agua Embotellada Natural: 1 pieza Agua Embotellada
            [10, 13, 1.000],
        ];

        $stmtRec = $pdo->prepare(
            "INSERT INTO " . TABLA_RECETAS . " (producto_id, insumo_id, cantidad)
             VALUES (:prod, :ins, :cant)"
        );
        foreach ($recetasIniciales as [$prodId, $insId, $cant]) {
            $stmtRec->execute([':prod' => $prodId, ':ins' => $insId, ':cant' => $cant]);
        }

        // Sembrar movimientos iniciales de auditoría (RF-10)
        $gerenteId = (int)$pdo->query("SELECT id FROM " . TABLA_USUARIOS . " ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($gerenteId > 0) {
            $stmtMov = $pdo->prepare(
                "INSERT INTO " . TABLA_MOVIMIENTOS . "
                 (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, motivo, observaciones)
                 VALUES (:ins, :usr, :tipo, :cant, :prev, :post, :mot, :obs)"
            );
            $stmtMov->execute([
                ':ins'  => 1,
                ':usr'  => $gerenteId,
                ':tipo' => 'ENTRADA',
                ':cant' => 20.000,
                ':prev' => 0.000,
                ':post' => 20.000,
                ':mot'  => null,
                ':obs'  => 'Carga inicial de trompo de carne árabe',
            ]);
            $stmtMov->execute([
                ':ins'  => 1,
                ':usr'  => $gerenteId,
                ':tipo' => 'VENTA',
                ':cant' => -1.500,
                ':prev' => 20.000,
                ':post' => 18.500,
                ':mot'  => null,
                ':obs'  => 'Consumo de ventas turno matutino',
            ]);
            $stmtMov->execute([
                ':ins'  => 8,
                ':usr'  => $gerenteId,
                ':tipo' => 'MERMA',
                ':cant' => -0.350,
                ':prev' => 0.800,
                ':post' => 0.450,
                ':mot'  => 'caducidad',
                ':obs'  => 'Cilantro oxidado en cámara de refrigeración',
            ]);
        }
    }

    $inicializado = true;
}
