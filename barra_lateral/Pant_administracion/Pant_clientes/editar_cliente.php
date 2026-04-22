<?php include("../../../seguridad.php");
include("../../../conex.php");
$link = Conectarse();

// Fetch all clients for the list
$clientes = [];
$res = mysqli_query($link, "SELECT id, nombre, correo, numero_telefono, rfc, razon_social, codigo_postal, calle, colonia, estado FROM t_clientes ORDER BY nombre ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $clientes[] = $row;
    }
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../../../Imagenes/TTlogomini.png">
    <title>Tacos Tony - Administración - Editar Cliente</title>
    <link rel="stylesheet" href="../../../estilos/estilogenerico.css">
    <style>
        /* ── Search bar ── */
        .ec-search-wrap {
            margin-bottom: 20px;
        }
        .ec-search-wrap input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            color: #333;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        .ec-search-wrap input:focus {
            border-color: #f6821f;
        }

        /* ── Client list ── */
        .ec-lista {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
        }
        .ec-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.15s;
        }
        .ec-item:last-child { border-bottom: none; }
        .ec-item:hover { background: #fff5ec; }
        .ec-item-info { display: flex; flex-direction: column; gap: 2px; }
        .ec-item-nombre { font-weight: bold; font-size: 15px; color: #333; }
        .ec-item-sub { font-size: 12px; color: #888; }
        .ec-item-btn {
            background: #f6821f;
            color: #000;
            border: none;
            padding: 7px 18px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 1px 1px 5px rgba(0,0,0,.3);
            transition: background 0.2s;
            white-space: nowrap;
        }
        .ec-item-btn:hover { background: #DC7B3C; }
        .ec-empty { padding: 20px; text-align: center; color: #aaa; font-size: 14px; }

        /* ── Modal overlay ── */
        .ec-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .ec-modal-overlay.activo { display: flex !important; }
        .ec-modal {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 620px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 4px 4px 20px rgba(0,0,0,0.4);
            position: relative;
        }
        .ec-modal-titulo {
            background: #f6821f;
            color: #000;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 28px;
            box-shadow: 2px 2px 10px rgba(0,0,0,.5);
        }
        .ec-modal-close {
            position: absolute;
            top: 16px;
            right: 20px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #555;
            line-height: 1;
        }
        .ec-modal-close:hover { color: #f6821f; }

        /* Greyed-out read-only fields */
        .input-grupo input[readonly] {
            background-color: #f0f0f0;
            color: #888;
            cursor: not-allowed;
        }

        .ec-modal-botones {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
        }
        .ec-modal-botones .btn-accion {
            padding: 12px 40px;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div align="center">
            <a href="../../Dashboard.php">
                <img src="../../../Imagenes/Tacos_tony_logo.png" width="200" alt="Logo">
            </a>
        </div>
        <a href="../../Dashboard.php" class="menu-item">
            <img src="../../../Imagenes/icon-dash.png" width="25"> Dashboard
        </a>
        <a href="../../Inventario.php" class="menu-item">
            <img src="../../../Imagenes/icon-inv.png" width="25"> Inventario
        </a>
        <a href="../../Movimientos.php" class="menu-item">
            <img src="../../../Imagenes/icon-mov.png" width="25"> Movimientos
        </a>
        <a href="../../Reportes.php" class="menu-item">
            <img src="../../../Imagenes/icon-repo.png" width="25"> Reportes
        </a>
        <a href="../../Administracion.php" class="menu-item activo">
            <img src="../../../Imagenes/icon-admin.png" width="25"> Administración
        </a>
        <a href="../../Catalogo.php" class="menu-item">
            <img src="../../../Imagenes/icon-catalogo.png" width="25"> Catálogo
        </a>
        <div class="menu-dropdown">
            <a class="menu-item">
                <img src="../../../Imagenes/icon-config.png" width="25"> Configuración
            </a>
            <div class="submenu">
                <a href="../../Configuracion.php" class="submenu-item">Editar Perfil</a>
                <a href="../../Pant_Ajustes/AjustesSitio.php" class="submenu-item">Ajustes del Sitio</a>
                <a href="../../../salir.php" class="submenu-item">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="formulario-card">

            <div class="titulo-caja">
                EDITAR CLIENTES
            </div>

            <div class="ec-search-wrap">
                <input type="text" id="buscarCliente" placeholder="Buscar por nombre..." oninput="filtrarClientes()">
            </div>

            <div class="ec-lista" id="listaClientes">
                <?php if (empty($clientes)): ?>
                    <div class="ec-empty">No hay clientes registrados.</div>
                <?php else: ?>
                    <?php foreach ($clientes as $c): ?>
                        <div class="ec-item"
                             data-nombre="<?php echo strtolower(htmlspecialchars($c['nombre'])); ?>"
                             data-id="<?php echo (int)$c['id']; ?>">
                            <div class="ec-item-info">
                                <span class="ec-item-nombre"><?php echo htmlspecialchars($c['nombre']); ?></span>
                                <span class="ec-item-sub">
                                    <?php echo htmlspecialchars($c['correo']); ?> &nbsp;|&nbsp;
                                    <?php echo htmlspecialchars($c['numero_telefono']); ?>
                                </span>
                            </div>
                            <button class="ec-item-btn"
                                data-id="<?php echo (int)$c['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($c['nombre'], ENT_QUOTES); ?>"
                                data-correo="<?php echo htmlspecialchars($c['correo'], ENT_QUOTES); ?>"
                                data-telefono="<?php echo htmlspecialchars($c['numero_telefono'], ENT_QUOTES); ?>"
                                data-rfc="<?php echo htmlspecialchars($c['rfc'] ?? '', ENT_QUOTES); ?>"
                                data-razon="<?php echo htmlspecialchars($c['razon_social'] ?? '', ENT_QUOTES); ?>"
                                data-cp="<?php echo htmlspecialchars($c['codigo_postal'] ?? '', ENT_QUOTES); ?>"
                                data-calle="<?php echo htmlspecialchars($c['calle'] ?? '', ENT_QUOTES); ?>"
                                data-colonia="<?php echo htmlspecialchars($c['colonia'] ?? '', ENT_QUOTES); ?>"
                                data-estado="<?php echo htmlspecialchars($c['estado'] ?? '', ENT_QUOTES); ?>"
                                onclick="abrirModal(this)">Editar</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="botones-bottom">
                <a class="btn-secundario" href="../../Administracion.php">CANCELAR</a>
                <a class="btn-accion" href="Clientes.php">REGISTRAR NUEVO</a>
            </div>

        </div>
    </div>

    <div class="ec-modal-overlay" id="modalOverlay" onclick="cerrarModalFuera(event)">
        <div class="ec-modal">
            <button class="ec-modal-close" onclick="cerrarModal()">&#10005;</button>
            <div class="ec-modal-titulo" id="modalTitulo">EDITAR CLIENTE</div>

            <form id="formEditar" method="post" action="actualizar_cliente.php">
                <input type="hidden" name="id" id="modal_id">

                <div class="form-grid">
                    <!-- Read-only: the 3 obligatory fields -->
                    <div class="input-grupo">
                        <label>Nombre Cliente</label>
                        <input type="text" id="modal_nombre" readonly>
                    </div>
                    <div class="input-grupo">
                        <label>Correo Electrónico</label>
                        <input type="text" id="modal_correo" readonly>
                    </div>
                    <div class="input-grupo">
                        <label>Número de Teléfono</label>
                        <input type="text" id="modal_telefono" readonly>
                    </div>

                    <!-- Editable optional fields -->
                    <div class="input-grupo">
                        <label>RFC</label>
                        <input type="text" id="modal_rfc" name="rfc" placeholder="Ingrese el RFC">
                    </div>
                    <div class="input-grupo">
                        <label>Razón Social</label>
                        <input type="text" id="modal_razon_social" name="razon_social" placeholder="Ingrese la razón social">
                    </div>
                    <div class="input-grupo">
                        <label>Código Postal</label>
                        <input type="number" id="modal_codigo_postal" name="codigo_postal" placeholder="Ingrese el CP">
                    </div>
                    <div class="input-grupo">
                        <label>Calle</label>
                        <input type="text" id="modal_calle" name="calle" placeholder="Ingrese la calle">
                    </div>
                    <div class="input-grupo">
                        <label>Colonia</label>
                        <input type="text" id="modal_colonia" name="colonia" placeholder="Ingrese la colonia">
                    </div>
                    <div class="input-grupo">
                        <label>Estado</label>
                        <input type="text" id="modal_estado" name="estado" placeholder="Ingrese el estado">
                    </div>
                </div>

                <div class="ec-modal-botones">
                    <button type="button" class="btn-accion" onclick="guardarCambios()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filtrarClientes() {
            var q = document.getElementById('buscarCliente').value.toLowerCase();
            var items = document.querySelectorAll('#listaClientes .ec-item');
            var hayResultados = false;
            items.forEach(function(item) {
                var nombre = item.getAttribute('data-nombre');
                if (nombre.includes(q)) {
                    item.style.display = '';
                    hayResultados = true;
                } else {
                    item.style.display = 'none';
                }
            });
            var empty = document.querySelector('#listaClientes .ec-empty');
            if (empty) empty.style.display = hayResultados ? 'none' : 'block';
        }

        function abrirModal(btn) {
            var d = btn.dataset;
            document.getElementById('modal_id').value            = d.id;
            document.getElementById('modal_nombre').value        = d.nombre;
            document.getElementById('modal_correo').value        = d.correo;
            document.getElementById('modal_telefono').value      = d.telefono;
            document.getElementById('modal_rfc').value           = d.rfc;
            document.getElementById('modal_razon_social').value  = d.razon;
            document.getElementById('modal_codigo_postal').value = d.cp;
            document.getElementById('modal_calle').value         = d.calle;
            document.getElementById('modal_colonia').value       = d.colonia;
            document.getElementById('modal_estado').value        = d.estado;
            document.getElementById('modalTitulo').textContent   = 'EDITAR: ' + d.nombre.toUpperCase();
            document.getElementById('modalOverlay').classList.add('activo');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.remove('activo');
        }
        function cerrarModalFuera(e) {
            if (e.target === document.getElementById('modalOverlay')) cerrarModal();
        }

        function guardarCambios() {
            document.getElementById('formEditar').submit();
        }
    </script>
</body>
</html>