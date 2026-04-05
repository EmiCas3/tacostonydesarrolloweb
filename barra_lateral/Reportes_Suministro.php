<?php include("../conex.php");
$link = Conectarse();
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../Imagenes/TTlogomini.png">
<title>Tacos Tony - Reporte Suministro</title>

<style>
body {
    font-family: Arial, sans-serif;
    background:#E5E5E5;
    margin:0;
    padding:20px;
    display:flex;
    min-height:100vh; 
}

.menu-item {
    padding:15px 20px;
    color:#000;
    font-weight:bold;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:15px;
}
.menu-item.activo { background:#f6821f; }
.menu-item:hover:not(.activo){ background:#F9D864; }

.main-content {
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
}

.formulario-card {
    background:#fff;
    border-radius:15px;
    padding:50px;
    width:100%;
    max-width:700px;
    box-shadow:2px 2px 10px rgba(0,0,0,.5);
}

.titulo-caja {
    background:#f6821f;
    padding:20px;
    text-align:center;
    font-weight:bold;
    border-radius:12px;
    margin-bottom:40px;
}

.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.input-grupo {
    display:flex;
    flex-direction:column;
    gap:8px;
}

.input-grupo input, .input-grupo select {
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

.btn-accion {
    background:#f6821f;
    border:none;
    padding:12px 40px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.tabla-contenedor {
    margin-top:30px;
    box-shadow:2px 2px 10px rgba(0,0,0,.5);
}

.tabla-header {
    background:#f6821f;
    display:grid;
    grid-template-columns:2fr 1fr 1fr;
    padding:10px;
    font-weight:bold;
}

.tabla-body {
    background:#E6E6E6;
    max-height:240px;
    overflow-y:auto;
}

.fila {
    display:grid;
    grid-template-columns:2fr 1fr 1fr;
    padding:10px;
    border-bottom:1px solid #ccc;
}

.sin-datos {
    padding:20px;
    text-align:center;
    color:#777;
}

.menu-dropdown { position: relative; }

.submenu {
    display: none;
    flex-direction: column;
    background-color: #f9f9f9;
    border-left: 4px solid #F6821F;
    margin-left: 20px;
    margin-right: 20px;
    margin-top: -5px;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.menu-dropdown:hover .submenu {
    display: flex;
}

.submenu-item {
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
}

.submenu-item:hover {
    color: #F6821F;
    background-color: #E5E5E5;
}
</style>
</head>

<body>

<div style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%">
    <div align="center"><a href="Dashboard.php"><img src="../Imagenes/Tacos_tony_logo.png" width="200"></a></div>

    <a href="Dashboard.php" class="menu-item"><img src="../Imagenes/icon-dash.png" width="25"> Dashboard</a>
    <a href="Inventario.php" class="menu-item"><img src="../Imagenes/icon-inv.png" width="25"> Inventario</a>
    <a href="Movimientos.html" class="menu-item"><img src="../Imagenes/icon-mov.png" width="25"> Movimientos</a>
    <a href="Reportes.html" class="menu-item activo"><img src="../Imagenes/icon-repo.png" width="25"> Reportes</a>
    <a href="Administracion.html" class="menu-item"><img src="../Imagenes/icon-admin.png" width="25"> Administración</a>
    <a href="Catalogo.html" class="menu-item"><img src="../Imagenes/icon-catalogo.png" width="25"> Catálogo</a>

    <div class="menu-dropdown">
        <a class="menu-item"><img src="../Imagenes/icon-config.png" width="25"> Configuración</a>
        <div class="submenu">
            <a href="Configuracion.html" class="submenu-item">Editar Perfil</a>
            <a href="Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
            <a href="../Login.php" class="submenu-item">Cerrar Sesión</a>
        </div>
    </div>
</div>

<div class="main-content">
<div class="formulario-card">

<div class="titulo-caja">REPORTE DE SUMINISTRO</div>

<div class="form-grid">
    <div class="input-grupo">
        <label>Mes</label>
        <select id="mes">
        <?php
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        for ($i=1;$i<=12;$i++){
            $sel = ($i==$mes_sel)?'selected':'';
            echo "<option value='$i' $sel>{$meses[$i]}</option>";
        }
        ?>
        </select>
    </div>

    <div class="input-grupo">
        <label>Año</label>
        <input type="number" id="anio" value="<?php echo $anio_sel; ?>">
    </div>
</div>

<div style="text-align:right; margin-top:20px;">
    <button class="btn-accion" onclick="filtrar()">FILTRAR</button>
</div>

<div class="tabla-contenedor">

<div class="tabla-header">
<span>Proveedor</span><span>Órdenes</span><span>Total</span>
</div>

<div class="tabla-body">
<?php
$q = "SELECT p.nombre, COUNT(DISTINCT pg.id) AS ordenes,
      SUM(pp.costo * pp.cantidad) AS total
      FROM t_proporcionar_general pg
      JOIN t_proporcionar_particular pp ON pg.id = pp.id_pg
      JOIN t_proovedores p ON pg.id_provedoor = p.id
      WHERE MONTH(pg.fecha)=$mes_sel AND YEAR(pg.fecha)=$anio_sel
      GROUP BY p.id ORDER BY total DESC";

$r = mysqli_query($link,$q);

if ($r && mysqli_num_rows($r)>0){
    while($row=mysqli_fetch_array($r)){
        echo "<div class='fila'>
                <span>{$row['nombre']}</span>
                <span>{$row['ordenes']}</span>
                <span>$".number_format($row['total'],2)."</span>
              </div>";
    }
}else{
    echo "<div class='sin-datos'>Sin compras este mes</div>";
}
?>
</div>

</div>
</div>
</div>

<script>
function filtrar(){
    let mes = document.getElementById('mes').value;
    let anio = document.getElementById('anio').value;

    if(!anio){ alert('Ingrese año'); return; }

    window.location.href = 'Reportes_Suministro.php?mes='+mes+'&anio='+anio;
}
</script>

</body>
</html>