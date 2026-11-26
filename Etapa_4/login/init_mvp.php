<?php
/**
 * Inicializador automático del esquema y datos semilla del MVP (Etapa 3)
 * Proyecto: Tacos Tony Inventario Móvil · Alumno: 3478788
 * Garantiza que las 7 tablas con prefijo 3478788_ existan y tengan los insumos,
 * productos reales e imágenes oficiales de Tacos Tony Camino Real.
 */

require_once __DIR__ . '/config.php';

/**
 * Normaliza cadenas para empatar catálogos sin sensibilidad a mayúsculas ni acentos
 */
function normalizarClaveCatalogo(string $texto): string
{
    $norm = mb_strtolower(trim($texto), 'UTF-8');
    $reemplazos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
    ];
    return strtr($norm, $reemplazos);
}

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
        `categoria`     ENUM('Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables', 'Otros') NOT NULL DEFAULT 'Carnes',
        `stock_actual`  DECIMAL(10,3) NOT NULL DEFAULT 0.000,
        `stock_minimo`  DECIMAL(10,3) NOT NULL DEFAULT 1.000,
        `estado`        ENUM('normal', 'bajo', 'discrepancia') NOT NULL DEFAULT 'normal',
        `activo`        TINYINT(1)    NOT NULL DEFAULT 1,
        `creado_en`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_insumos_estado_activo` (`activo`, `estado`),
        KEY `idx_insumos_categoria` (`categoria`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Migración idempotente: incluir categoría 'Otros' en instalaciones previas
    $colCatIns = $pdo->query("SHOW COLUMNS FROM " . TABLA_INSUMOS . " LIKE 'categoria'")->fetch();
    if ($colCatIns && !str_contains($colCatIns['Type'], "'Otros'")) {
        $pdo->exec("ALTER TABLE " . TABLA_INSUMOS . " MODIFY COLUMN `categoria` ENUM('Carnes', 'Tortillas', 'Verduras', 'Salsas', 'Bebidas', 'Desechables', 'Otros') NOT NULL DEFAULT 'Carnes'");
    }

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

    // 7. Libro Mayor Inmutable de Movimientos de Inventario (RF-06, RF-07, RF-10, RF-13, RF-17, RNF-06)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_MOVIMIENTOS . " (
        `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `insumo_id`       INT UNSIGNED  NOT NULL,
        `usuario_id`      INT UNSIGNED  NOT NULL,
        `tipo_movimiento` ENUM('VENTA', 'ENTRADA', 'MERMA', 'AJUSTE', 'RECEPCION') NOT NULL,
        `cantidad`        DECIMAL(10,3) NOT NULL,
        `stock_previo`    DECIMAL(10,3) NOT NULL,
        `stock_posterior` DECIMAL(10,3) NOT NULL,
        `motivo`          ENUM('caducidad', 'dano', 'preparacion', 'otro', 'conteo_fisico') DEFAULT NULL,
        `observaciones`   VARCHAR(255)  DEFAULT NULL,
        `referencia_id`   INT UNSIGNED  DEFAULT NULL,
        `fecha_hora`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_mov_insumo_fecha` (`insumo_id`, `fecha_hora`),
        KEY `idx_mov_tipo_fecha` (`tipo_movimiento`, `fecha_hora`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Migración idempotente para bases de datos existentes de Etapa 3 (agregar AJUSTE, RECEPCION y conteo_fisico)
    $colTipoMov = $pdo->query("SHOW COLUMNS FROM " . TABLA_MOVIMIENTOS . " LIKE 'tipo_movimiento'")->fetch();
    if ($colTipoMov && (!str_contains($colTipoMov['Type'], "'AJUSTE'") || !str_contains($colTipoMov['Type'], "'RECEPCION'"))) {
        $pdo->exec("ALTER TABLE " . TABLA_MOVIMIENTOS . " MODIFY COLUMN `tipo_movimiento` ENUM('VENTA', 'ENTRADA', 'MERMA', 'AJUSTE', 'RECEPCION') NOT NULL");
    }
    $colMotivoMov = $pdo->query("SHOW COLUMNS FROM " . TABLA_MOVIMIENTOS . " LIKE 'motivo'")->fetch();
    if ($colMotivoMov && !str_contains($colMotivoMov['Type'], "'conteo_fisico'")) {
        $pdo->exec("ALTER TABLE " . TABLA_MOVIMIENTOS . " MODIFY COLUMN `motivo` ENUM('caducidad', 'dano', 'preparacion', 'otro', 'conteo_fisico') DEFAULT NULL");
    }

    // 8. Tabla de Conteos Físicos con Conciliación (RF-17)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_CONTEOS . " (
        `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `usuario_captura_id` INT UNSIGNED NOT NULL,
        `usuario_aprueba_id` INT UNSIGNED DEFAULT NULL,
        `estado`             ENUM('PENDIENTE', 'CONFIRMADO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
        `observaciones`      VARCHAR(255) DEFAULT NULL,
        `fecha_captura`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `fecha_confirmacion` DATETIME     DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_conteos_estado_fecha` (`estado`, `fecha_captura`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 9. Detalles de Conteo Físico (RF-17)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_CONTEO_DETALLES . " (
        `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `conteo_id`         INT UNSIGNED  NOT NULL,
        `insumo_id`         INT UNSIGNED  NOT NULL,
        `stock_sistema`     DECIMAL(10,3) NOT NULL,
        `stock_contado`     DECIMAL(10,3) NOT NULL,
        `diferencia`        DECIMAL(10,3) NOT NULL,
        `estado_resultante` ENUM('normal', 'bajo', 'discrepancia') NOT NULL DEFAULT 'normal',
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_conteo_insumo` (`conteo_id`, `insumo_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 10. Tabla de Pedidos de Reabastecimiento (RF-11, RF-12, RF-13)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_PEDIDOS . " (
        `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `usuario_crea_id`   INT UNSIGNED NOT NULL,
        `usuario_recibe_id` INT UNSIGNED DEFAULT NULL,
        `dias_cobertura`    INT UNSIGNED NOT NULL DEFAULT 7,
        `estado`            ENUM('PENDIENTE', 'RECIBIDO', 'CANCELADO') NOT NULL DEFAULT 'PENDIENTE',
        `observaciones`     VARCHAR(255) DEFAULT NULL,
        `fecha_creacion`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `fecha_recepcion`   DATETIME     DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_pedidos_estado_fecha` (`estado`, `fecha_creacion`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 11. Detalles de Pedidos de Reabastecimiento (RF-12, RF-13)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_PEDIDO_DETALLES . " (
        `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `pedido_id`         INT UNSIGNED  NOT NULL,
        `insumo_id`         INT UNSIGNED  NOT NULL,
        `cantidad_pedida`   DECIMAL(10,3) NOT NULL,
        `cantidad_recibida` DECIMAL(10,3) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_pedido_insumo` (`pedido_id`, `insumo_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 12. Tabla de Proveedores del Sistema Original (t_proovedores — RF-06)
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . TABLA_PROVEEDORES . " (
        `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`          VARCHAR(80)  NOT NULL,
        `numero_telefono` VARCHAR(45)  NOT NULL,
        `correo`          VARCHAR(80)  NOT NULL,
        `activo`          TINYINT(1)   NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_prov_nombre` (`nombre`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Sembrar los 8 proveedores exactos del sistema original de forma idempotente
    $proveedoresOriginales = [
        [1, 'Carnes Selectas Don Chucho',      '5578943210', 'contacto@donchucho.com'],
        [2, 'Tortillas El Maiz Dorado',        '8145678912', 'ventas@maizdorado.mx'],
        [3, 'Verduras Frescas La Huerta',      '3399887766', 'pedidos@lahuertafresca.com'],
        [4, 'Refrescos y Bebidas La Burbuja', '8122334455', 'pedidos@laburbuja.com'],
        [5, 'Tienda General San Miguel',       '3366778899', 'contacto@quesossanmiguel.mx'],
        [6, 'Desechables y Empaques Rapidos',  '5511223344', 'ventas@empaquesrapidos.com'],
        [7, 'Matriz',                          '2222222222', 'matriz@taqueria.com'],
        [8, 'Carbon El Tren',                  '5551122334', 'ventas@eltren.mx'],
    ];
    $stmtProv = $pdo->prepare(
        "INSERT IGNORE INTO " . TABLA_PROVEEDORES . " (id, nombre, numero_telefono, correo, activo)
         VALUES (:id, :nom, :tel, :cor, 1)"
    );
    foreach ($proveedoresOriginales as [$pid, $pnom, $ptel, $pcor]) {
        $stmtProv->execute([
            ':id'  => $pid,
            ':nom' => $pnom,
            ':tel' => $ptel,
            ':cor' => $pcor,
        ]);
    }

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

    // Sincronizar de forma idempotente TODOS los insumos del sistema original (31 de t_materiales + Salsa Chipotle)
    $catalogoInsumosCompleto = [
        // [clave, nombre, unidad, categoria, stock_inicial, stock_minimo, aliases]
        ['carne',           'Carne de Cerdo Árabe (Trompo)', 'kg',    'Carnes',      18.500,  8.000, ['carne de cerdo']],
        ['pan_arabe',       'Pan Árabe (Pita)',              'pieza', 'Tortillas',   45.000, 20.000, ['pan arabe']],
        ['tortilla_maiz',   'Tortilla de Maíz',              'pieza', 'Tortillas',  100.000, 30.000, ['tortillas de maiz']],
        ['pan_torta',       'Pan de Torta (Telera)',         'pieza', 'Tortillas',   24.000, 10.000, ['pan de torta']],
        ['pan_cemita',      'Pan de Cemita Poblana',         'pieza', 'Tortillas',   18.000, 10.000, ['pan de cemita']],
        ['queso_gouda',     'Queso Gouda Rallado',           'kg',    'Carnes',       1.850,  2.500, ['queso gouda']],
        ['cebolla',         'Cebolla Blanca Fileteada',      'kg',    'Verduras',     6.400,  3.000, ['cebolla']],
        ['cilantro',        'Cilantro Fresco',               'kg',    'Verduras',     0.450,  0.800, ['cilantro']],
        ['limones',         'Limones Verdes',                'kg',    'Verduras',     5.200,  2.000, ['limones']],
        ['salsa_chipotle',  'Salsa de Chipotle Tony',        'L',     'Salsas',       4.500,  2.000, []],
        ['jocoque_cremoso', 'Jocoque Cremoso',               'pieza', 'Salsas',       1.200,  1.500, []],
        ['falafel',         'Falafel',                       'pieza', 'Verduras',    21.000,  5.000, ['Masa de Falafel (Garbanzo)', 'falafel']],
        ['agua',            'Agua Embotellada 600ml',        'pieza', 'Bebidas',     36.000, 12.000, ['agua embotellada']],
        ['refresco',        'Refresco en Lata 355ml',        'pieza', 'Bebidas',     42.000, 15.000, ['refrescos en lata']],
        ['servilletas',     'Servilletas y Desechables',     'pieza', 'Desechables',150.000, 50.000, ['servilletas']],
        ['cremita',         'Cremita (Postre)',              'pieza', 'Otros',       18.000,  5.000, ['cremita']],
        ['envases',         'Envases Desechables',           'pieza', 'Desechables', 50.000, 15.000, []],
        ['vasos',           'Vasos Desechables',             'pieza', 'Desechables', 50.000, 15.000, []],
        ['camaron',         'Camarón',                       'kg',    'Carnes',       2.450,  1.000, []],
        ['perejil',         'Perejil Fresco',                'kg',    'Verduras',     2.950,  0.800, ['perejil']],
        ['gine',            'Gine (Salsa Árabe)',            'L',     'Salsas',       9.650,  2.000, ['gine']],
        ['jocoque_seco',    'Jocoque Seco',                  'pieza', 'Salsas',      39.000,  5.000, []],
        ['papas',           'Papas a la Francesa',           'kg',    'Verduras',     0.800,  2.000, []],
        ['catsup',          'Catsup',                        'L',     'Salsas',       4.000,  1.000, []],
        ['salsa_jitomate',  'Salsa de Jitomate',             'L',     'Salsas',       4.500,  1.500, []],
        ['cerveza',         'Cerveza',                       'pieza', 'Bebidas',     48.000, 12.000, []],
        ['cafe',            'Café Soluble',                  'kg',    'Bebidas',      5.510,  1.000, []],
        ['flan',            'Flan',                          'pieza', 'Otros',       39.000, 10.000, []],
        ['paleta',          'Paleta Helada',                 'pieza', 'Otros',       50.000, 10.000, []],
        ['chorizo',         'Chorizo',                       'kg',    'Carnes',       6.000,  2.000, []],
        ['carbon',          'Carbón',                        'kg',    'Otros',      220.000, 40.000, []],
        ['dedo_novia',      'Dedo de Novia',                 'pieza', 'Otros',       54.000, 10.000, []],
    ];

    $existentesIns = $pdo->query("SELECT id, nombre FROM " . TABLA_INSUMOS)->fetchAll();
    $porNombreIns  = [];
    foreach ($existentesIns as $row) {
        $porNombreIns[normalizarClaveCatalogo($row['nombre'])] = (int)$row['id'];
    }

    $stmtAddIns = $pdo->prepare(
        "INSERT INTO " . TABLA_INSUMOS . "
         (nombre, unidad_medida, categoria, stock_actual, stock_minimo, estado, activo)
         VALUES (:nombre, :unidad, :cat, :actual, :minimo, :estado, 1)"
    );

    $mapaInsumos = [];
    foreach ($catalogoInsumosCompleto as [$clave, $nombre, $unidad, $cat, $actual, $minimo, $aliases]) {
        $idEncontrado = $porNombreIns[normalizarClaveCatalogo($nombre)] ?? null;
        if ($idEncontrado === null) {
            foreach ($aliases as $al) {
                if (isset($porNombreIns[normalizarClaveCatalogo($al)])) {
                    $idEncontrado = $porNombreIns[normalizarClaveCatalogo($al)];
                    break;
                }
            }
        }
        if ($idEncontrado === null) {
            $estado = calcularEstadoInsumo((float)$actual, (float)$minimo);
            $stmtAddIns->execute([
                ':nombre' => $nombre,
                ':unidad' => $unidad,
                ':cat'    => $cat,
                ':actual' => number_format((float)$actual, 3, '.', ''),
                ':minimo' => number_format((float)$minimo, 3, '.', ''),
                ':estado' => $estado,
            ]);
            $idEncontrado = (int)$pdo->lastInsertId();
            $porNombreIns[normalizarClaveCatalogo($nombre)] = $idEncontrado;
        } elseif (in_array($clave, ['jocoque_cremoso', 'jocoque_seco', 'falafel'], true)) {
            // Homologación solicitada: asegurar unidad 'pieza' y nombre exacto 'Falafel'
            $stmtHomologIns = $pdo->prepare(
                "UPDATE " . TABLA_INSUMOS . "
                 SET nombre = :nom, unidad_medida = :uni
                 WHERE id = :id AND (unidad_medida <> :uni2 OR nombre <> :nom2)"
            );
            $stmtHomologIns->execute([
                ':nom'  => $nombre,
                ':uni'  => $unidad,
                ':id'   => $idEncontrado,
                ':uni2' => $unidad,
                ':nom2' => $nombre,
            ]);
        }
        $mapaInsumos[$clave] = $idEncontrado;
    }

    // Sincronizar TODOS los 38 productos del sistema original (t_productos) y sus recetas
    $catalogoProductosCompleto = [
        ['Taco Arabe',                'Especialidades Árabes',  39.00, 'assets/img/productos/TacoArabe.png',     ['Taco Árabe Tradicional'], [
            ['carne', 0.110], ['pan_arabe', 1.000], ['cebolla', 0.020], ['salsa_chipotle', 0.015], ['servilletas', 2.000],
        ]],
        ['Torta Arabe',               'Tortas y Cemitas',       39.00, 'assets/img/productos/TortaArabe.png',    ['Torta Árabe Especial'], [
            ['carne', 0.150], ['pan_torta', 1.000], ['cebolla', 0.030], ['salsa_chipotle', 0.025],
        ]],
        ['Tony Especial Arabe',       'Especialidades Árabes',  41.00, 'assets/img/productos/TonyEArabe.png',    [], [
            ['carne', 0.200], ['pan_arabe', 1.000], ['queso_gouda', 0.150], ['gine', 0.050],
        ]],
        ['Tony Especial Torta',       'Tortas y Cemitas',       41.00, 'assets/img/productos/TortaEArabe.png',   [], [
            ['carne', 0.200], ['pan_torta', 1.000], ['queso_gouda', 0.100], ['gine', 0.050],
        ]],
        ['Cemita con Carne Arabe',    'Tortas y Cemitas',       40.00, 'assets/img/productos/CemitaArabe.png',   [], [
            ['carne', 0.200], ['pan_cemita', 1.000], ['cebolla', 0.025],
        ]],
        ['Taco Oriental',             'Especialidades Árabes',  24.00, 'assets/img/productos/TacoOriental.png',  ['Taco Oriental (Maíz)'], [
            ['carne', 0.080], ['tortilla_maiz', 2.000], ['cebolla', 0.015], ['cilantro', 0.008], ['salsa_chipotle', 0.015],
        ]],
        ['Cremita',                   'Postres',                37.00, 'assets/img/productos/Cremita.png',       [], [
            ['cremita', 1.000],
        ]],
        ['Tony Especial Oriental',    'Especialidades Árabes',  26.00, 'assets/img/productos/TonyEOrien.png',    [], [
            ['carne', 0.150], ['tortilla_maiz', 2.000], ['queso_gouda', 0.100], ['gine', 0.050],
        ]],
        ['Falafel',                   'Complementos Libaneses', 39.00, 'assets/img/productos/Falafel.png',       ['Orden de Falafel (5 pzas)'], [
            ['falafel', 1.000], ['pan_arabe', 1.000],
        ]],
        ['Kilo de Carne Arabe',       'Por Kilo',              400.00, 'assets/img/productos/Carne.png',         [], [
            ['carne', 1.000], ['pan_arabe', 10.000], ['tortilla_maiz', 10.000], ['cebolla', 0.150], ['cilantro', 0.050], ['limones', 0.100], ['envases', 1.000],
        ]],
        ['1/2 de Kilo de Arabe',      'Por Kilo',              200.00, 'assets/img/productos/Carne.png',         [], [
            ['carne', 0.500], ['pan_arabe', 5.000], ['tortilla_maiz', 5.000], ['cebolla', 0.080], ['cilantro', 0.030], ['limones', 0.050], ['envases', 1.000],
        ]],
        ['Salsa / Gine 100 ml',       'Salsas y Extras',        30.00, 'assets/img/productos/Salsa.jpeg',        [], [
            ['gine', 0.100], ['envases', 1.000],
        ]],
        ['Salsa / Gine 250 ml',       'Salsas y Extras',        40.00, 'assets/img/productos/Gine.jpeg',         [], [
            ['gine', 0.250], ['envases', 1.000],
        ]],
        ['Queso Fundido',             'Con Queso Gouda',       115.00, 'assets/img/productos/QuesoFundido.png',  [], [
            ['queso_gouda', 0.250], ['pan_arabe', 2.000],
        ]],
        ['Queso Fundido Preparado',   'Con Queso Gouda',       120.00, 'assets/img/productos/QuesoPrepa.png',    [], [
            ['queso_gouda', 0.200], ['carne', 0.100], ['pan_arabe', 2.000],
        ]],
        ['Quesadilla',                'Con Queso Gouda',        45.00, 'assets/img/productos/Quesadilla.png',    [], [
            ['pan_arabe', 1.000], ['queso_gouda', 0.150],
        ]],
        ['Quesadilla Arabe con Gine', 'Con Queso Gouda',        67.00, 'assets/img/productos/QuesaAGine.png',    [], [
            ['carne', 0.100], ['pan_arabe', 1.000], ['queso_gouda', 0.150], ['gine', 0.050],
        ]],
        ['Torta Arabe con Queso',     'Tortas y Cemitas',       65.00, 'assets/img/productos/TortaAQueso.png',   [], [
            ['carne', 0.150], ['pan_torta', 1.000], ['queso_gouda', 0.100], ['cebolla', 0.025],
        ]],
        ['Cemita Arabe con Queso',    'Tortas y Cemitas',       65.00, 'assets/img/productos/CemitaArabeQ.png',  ['Cemita Árabe con Queso'], [
            ['carne', 0.140], ['pan_cemita', 1.000], ['queso_gouda', 0.050], ['cebolla', 0.025],
        ]],
        ['Quesadilla Arabe',          'Con Queso Gouda',        65.00, 'assets/img/productos/QuesaArabe.png',    ['Quesadilla Árabe'], [
            ['carne', 0.090], ['pan_arabe', 1.000], ['queso_gouda', 0.065], ['salsa_chipotle', 0.015],
        ]],
        ['Queso fundido con Carne',   'Con Queso Gouda',       120.00, 'assets/img/productos/QuesoFCarne.png',   ['Queso Fundido con Carne'], [
            ['carne', 0.120], ['queso_gouda', 0.180], ['pan_arabe', 2.000],
        ]],
        ['Queso Tony Especial',       'Con Queso Gouda',       140.00, 'assets/img/productos/QuesoTonyE.png',    [], [
            ['carne', 0.100], ['chorizo', 0.100], ['queso_gouda', 0.200], ['pan_arabe', 2.000],
        ]],
        ['Quesadilla Maiz',           'Con Queso Gouda',        35.00, 'assets/img/productos/QuesaMaiz.png',     [], [
            ['tortilla_maiz', 2.000], ['queso_gouda', 0.150],
        ]],
        ['Quesadilla Oriental',       'Con Queso Gouda',        40.00, 'assets/img/productos/QuesaOriental.png', [], [
            ['carne', 0.100], ['tortilla_maiz', 2.000], ['queso_gouda', 0.150],
        ]],
        ['Cebollitas',                'Salsas y Extras',        28.00, 'assets/img/productos/Cebollitas.png',    [], [
            ['cebolla', 0.200], ['limones', 0.050],
        ]],
        ['Caldo de Camaron',          'Complementos Libaneses', 38.00, 'assets/img/productos/CaldoCam.png',      [], [
            ['camaron', 0.050], ['perejil', 0.020], ['salsa_jitomate', 0.050],
        ]],
        ['Jocoque Seco',              'Complementos Libaneses', 55.00, 'assets/img/productos/JocoSeco.png',      [], [
            ['jocoque_seco', 1.000], ['pan_arabe', 1.000],
        ]],
        ['Papas a la Francesa',       'Complementos Libaneses', 45.00, 'assets/img/productos/PapasFran.png',     [], [
            ['papas', 0.200], ['catsup', 0.030],
        ]],
        ['Jocoque Cremoso',           'Complementos Libaneses', 48.00, 'assets/img/productos/JocoCrem.png',      ['Porción Jocoque Cremoso'], [
            ['jocoque_cremoso', 1.000], ['pan_arabe', 1.000],
        ]],
        ['Flan Horneado',             'Postres',                34.00, 'assets/img/productos/FlanHorn.png',      [], [
            ['flan', 1.000],
        ]],
        ['Paleta Helada',             'Postres',                27.00, 'assets/img/productos/Paleta.png',        [], [
            ['paleta', 1.000],
        ]],
        ['Agua Embotellada',          'Bebidas',                29.00, 'assets/img/productos/Agua.png',          ['Agua Embotellada Natural'], [
            ['agua', 1.000],
        ]],
        ['Cemita Especial',           'Tortas y Cemitas',       58.00, 'assets/img/productos/CemitaArabe.png',   [], [
            ['carne', 0.150], ['pan_cemita', 1.000], ['queso_gouda', 0.100], ['gine', 0.040],
        ]],
        ['Dedo de Novia',             'Postres',                40.00, 'assets/img/productos/DedoNovia.png',     [], [
            ['dedo_novia', 1.000],
        ]],
        ['Refresco Lata',             'Bebidas',                29.00, 'assets/img/productos/Refersco.png',      [], [
            ['refresco', 1.000],
        ]],
        ['Cerveza',                   'Bebidas',                45.00, 'assets/img/productos/Cerveza.png',       [], [
            ['cerveza', 1.000],
        ]],
        ['Cafe Soluble',              'Bebidas',                25.00, 'assets/img/productos/Cafe.png',          [], [
            ['cafe', 0.070], ['vasos', 1.000],
        ]],
        ['Pizza Arabe',               'Con Queso Gouda',        65.00, 'assets/img/productos/PizzaArabe.png',    ['Pizza Árabe Familiar'], [
            ['carne', 0.250], ['pan_arabe', 2.000], ['queso_gouda', 0.220], ['cebolla', 0.040],
        ]],
    ];

    $existentesProd = $pdo->query("SELECT id, nombre FROM " . TABLA_PRODUCTOS)->fetchAll();
    $porNombreProd  = [];
    foreach ($existentesProd as $p) {
        $porNombreProd[normalizarClaveCatalogo($p['nombre'])] = (int)$p['id'];
    }

    $stmtAddProd = $pdo->prepare(
        "INSERT INTO " . TABLA_PRODUCTOS . " (nombre, categoria, precio, imagen, activo)
         VALUES (:nombre, :cat, :precio, :img, 1)"
    );
    $stmtUpdProd = $pdo->prepare(
        "UPDATE " . TABLA_PRODUCTOS . " SET nombre = :nombre, categoria = :cat, precio = :precio, imagen = :img WHERE id = :id"
    );
    $stmtCountRec = $pdo->prepare("SELECT COUNT(*) FROM " . TABLA_RECETAS . " WHERE producto_id = :pid");
    $stmtAddRec   = $pdo->prepare(
        "INSERT IGNORE INTO " . TABLA_RECETAS . " (producto_id, insumo_id, cantidad)
         VALUES (:pid, :iid, :cant)"
    );

    foreach ($catalogoProductosCompleto as [$nombre, $cat, $precio, $img, $aliases, $recetaDef]) {
        $pid = $porNombreProd[normalizarClaveCatalogo($nombre)] ?? null;
        if ($pid === null) {
            foreach ($aliases as $al) {
                $claveAl = normalizarClaveCatalogo($al);
                if (isset($porNombreProd[$claveAl])) {
                    $pid = $porNombreProd[$claveAl];
                    // Alinear nombre/precio/imagen oficial del catálogo original conservando ID histórico
                    $stmtUpdProd->execute([
                        ':nombre' => $nombre,
                        ':cat'    => $cat,
                        ':precio' => number_format((float)$precio, 2, '.', ''),
                        ':img'    => $img,
                        ':id'     => $pid,
                    ]);
                    unset($porNombreProd[$claveAl]);
                    $porNombreProd[normalizarClaveCatalogo($nombre)] = $pid;
                    break;
                }
            }
        }
        if ($pid === null) {
            $stmtAddProd->execute([
                ':nombre' => $nombre,
                ':cat'    => $cat,
                ':precio' => number_format((float)$precio, 2, '.', ''),
                ':img'    => $img,
            ]);
            $pid = (int)$pdo->lastInsertId();
            $porNombreProd[normalizarClaveCatalogo($nombre)] = $pid;
        }

        // Asegurar receta si el producto aún no tiene ingredientes configurados
        $stmtCountRec->execute([':pid' => $pid]);
        if ((int)$stmtCountRec->fetchColumn() === 0) {
            foreach ($recetaDef as [$claveIns, $cant]) {
                if (isset($mapaInsumos[$claveIns])) {
                    $stmtAddRec->execute([
                        ':pid'  => $pid,
                        ':iid'  => $mapaInsumos[$claveIns],
                        ':cant' => number_format((float)$cant, 3, '.', ''),
                    ]);
                }
            }
        }
    }

    // Ajustar recetas heredadas en kg (0.180, 0.200, 0.150) de Falafel, Jocoque Seco y Jocoque Cremoso a 1.000 pieza
    $pFalafel  = $porNombreProd[normalizarClaveCatalogo('Falafel')] ?? null;
    $pJocoSeco = $porNombreProd[normalizarClaveCatalogo('Jocoque Seco')] ?? null;
    $pJocoCrem = $porNombreProd[normalizarClaveCatalogo('Jocoque Cremoso')] ?? null;
    $stmtFixRec = $pdo->prepare(
        "UPDATE " . TABLA_RECETAS . " SET cantidad = 1.000 WHERE producto_id = :pid AND insumo_id = :iid AND cantidad = :oldCant"
    );
    if ($pFalafel && isset($mapaInsumos['falafel'])) {
        $stmtFixRec->execute([':pid' => $pFalafel, ':iid' => $mapaInsumos['falafel'], ':oldCant' => '0.180']);
        if (isset($mapaInsumos['jocoque_cremoso'])) {
            $pdo->prepare("DELETE FROM " . TABLA_RECETAS . " WHERE producto_id = :pid AND insumo_id = :iid AND cantidad = 0.050")
                ->execute([':pid' => $pFalafel, ':iid' => $mapaInsumos['jocoque_cremoso']]);
        }
    }
    if ($pJocoSeco && isset($mapaInsumos['jocoque_seco'])) {
        $stmtFixRec->execute([':pid' => $pJocoSeco, ':iid' => $mapaInsumos['jocoque_seco'], ':oldCant' => '0.200']);
    }
    if ($pJocoCrem && isset($mapaInsumos['jocoque_cremoso'])) {
        $stmtFixRec->execute([':pid' => $pJocoCrem, ':iid' => $mapaInsumos['jocoque_cremoso'], ':oldCant' => '0.150']);
    }

    // Sembrar movimientos iniciales de auditoría si la bitácora está vacía (RF-10)
    $totalMovs = (int)$pdo->query("SELECT COUNT(*) FROM " . TABLA_MOVIMIENTOS)->fetchColumn();
    if ($totalMovs === 0) {
        $gerenteId = (int)$pdo->query("SELECT id FROM " . TABLA_USUARIOS . " ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($gerenteId > 0) {
            $stmtMov = $pdo->prepare(
                "INSERT INTO " . TABLA_MOVIMIENTOS . "
                 (insumo_id, usuario_id, tipo_movimiento, cantidad, stock_previo, stock_posterior, motivo, observaciones)
                 VALUES (:ins, :usr, :tipo, :cant, :prev, :post, :mot, :obs)"
            );
            $stmtMov->execute([
                ':ins'  => $mapaInsumos['carne'],
                ':usr'  => $gerenteId,
                ':tipo' => 'ENTRADA',
                ':cant' => 20.000,
                ':prev' => 0.000,
                ':post' => 20.000,
                ':mot'  => null,
                ':obs'  => 'Proveedor: Carnes Selectas Don Chucho · Carga inicial de trompo',
            ]);
            $stmtMov->execute([
                ':ins'  => $mapaInsumos['carne'],
                ':usr'  => $gerenteId,
                ':tipo' => 'VENTA',
                ':cant' => -1.500,
                ':prev' => 20.000,
                ':post' => 18.500,
                ':mot'  => null,
                ':obs'  => 'Consumo de ventas turno matutino',
            ]);
            $stmtMov->execute([
                ':ins'  => $mapaInsumos['cilantro'],
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
