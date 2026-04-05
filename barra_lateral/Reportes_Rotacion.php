<?php
include("../conex.php");
$link = Conectarse();

$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
$mes  = isset($_GET['mes'])  ? $_GET['mes']  : date('m');
?>

<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<title>Rotación Inventario</title>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #E5E5E5;
    margin: 0;
    padding: 20px;
    display: flex;
}

.menu-item {
    padding: 15px 20px;
    color: #000000;
    font-weight: bold;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-item.activo { background-color: #F6821F; }

.menu-item:hover:not(.activo) { background-color: #F9D864; }

.menu-dropdown { position: relative; }

.submenu {
    display: none;
    flex-direction: column;
    background-color: #f9f9f9;
    border-left: 4px solid #F6821F;
    margin-left: 20px;
    margin-right: 20px;
    margin-top: -5px;
}

.menu-dropdown:hover .submenu { display: flex; }

.submenu-item {
    padding: 12px 20px;
    color: #333333;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
}

.submenu-item:hover {
    color: #F6821F;
    background-color: #E5E5E5;
}

.main {
    flex-grow: 1;
    padding-left: 30px;
}

.box {
    background-color: #FFFFFF;
    border-radius: 10px;
    padding: 20px;
    width: 650px;
    box-shadow: 2px 2px 10px rgba(0,0,0,.5);
}

.header-box {
    background-color: #F6821F;
    font-weight: bold;
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 15px;
}

input, select {
    padding: 8px;
    margin: 5px;
}

button {
    padding: 8px 15px;
    background: #F6821F;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.tabla {
    margin-top: 15px;
    border-radius: 10px;
    overflow: hidden;
}

.tabla-header {
    background-color: #F6821F;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    padding: 10px;
    font-weight: bold;
}

.tabla-body {
    background-color: #E6E6E6;
    max-height: 240px; /* 🔥 ~6 rows */
    overflow-y: auto;
}

.fila {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    padding: 8px 10px;
    border-bottom: 1px solid #ccc;
    font-weight: bold;
}

.sin-datos {
    padding: 10px;
    text-align: center;
    color: #888;
}
</style>
</head>

<body>

<div style="background-color: #FFFFFF; width: 250px; border-radius: 10px; padding-top: 20px; padding-bottom: 20px; margin-right: 30px; box-shadow: 2px 2px 10px rgba(0, 0, 0, .5); height: 100%">
    <div align="center">
        <a href="Dashboard.php">
            <img src="../Imagenes/Tacos_tony_logo.png" width="200">
        </a>
    </div>

    <a href="Dashboard.php" class="menu-item">
        <img src="../Imagenes/icon-dash.png" width="25"> Dashboard
    </a>
    <a href="Inventario.php" class="menu-item">
        <img src="../Imagenes/icon-inv.png" width="25"> Inventario
    </a>
    <a href="Movimientos.html" class="menu-item">
        <img src="../Imagenes/icon-mov.png" width="25"> Movimientos
    </a>
    <a href="Reportes.html" class="menu-item activo">
        <img src="../Imagenes/icon-repo.png" width="25"> Reportes
    </a>
    <a href="Administracion.html" class="menu-item">
        <img src="../Imagenes/icon-admin.png" width="25"> Administración
    </a>
    <a href="Catalogo.html" class="menu-item">
        <img src="../Imagenes/icon-catalogo.png" width="25"> Catálogo
    </a>

    <div class="menu-dropdown">
        <a class="menu-item">
            <img src="../Imagenes/icon-config.png" width="25"> Configuración
        </a>
        <div class="submenu">
            <a href="Configuracion.html" class="submenu-item">Editar Perfil</a>
            <a href="Pant_Ajustes/AjustesSitio.html" class="submenu-item">Ajustes del Sitio</a>
            <a href="../Login.php" class="submenu-item">Cerrar Sesión</a>
        </div>
    </div>
</div>

<!-- MAIN -->
<div class="main">

<div class="box">

<div class="header-box">ROTACIÓN DE INVENTARIO</div>

<label>Mes:</label>
<select id="mes">
<?php
for ($i=1; $i<=12; $i++){
    $sel = ($i==$mes) ? "selected" : "";
    echo "<option value='$i' $sel>$i</option>";
}
?>
</select>

<label>Año:</label>
<input type="number" id="anio" value="<?php echo $anio; ?>">

<button onclick="filtrar()">FILTRAR</button>

<div class="tabla">

<div class="tabla-header">
    <span>Material</span>
    <span>Consumido</span>
    <span>Stock</span>
</div>

<div class="tabla-body">
<?php
$q = "SELECT m.nombre,
             SUM(np.cantidad * vp.cantidad) AS consumido,
             m.existencias
      FROM t_materiales m
      JOIN t_necesitar_particular np ON np.id_material = m.id
      JOIN t_necesitar_general ng ON ng.id_ng = np.id_ng
      JOIN t_vender_particular vp ON vp.id_producto = ng.id_producto
      JOIN t_vender_general vg ON vg.id = vp.id_vg
      WHERE MONTH(vg.fecha)=$mes AND YEAR(vg.fecha)=$anio
      GROUP BY m.nombre, m.existencias
      ORDER BY consumido DESC";

$r = mysqli_query($link,$q);

if($r && mysqli_num_rows($r)>0){
    while($row=mysqli_fetch_array($r)){
        echo "<div class='fila'>";
        echo "<span>".htmlspecialchars($row['nombre'])."</span>";
        echo "<span>".number_format($row['consumido'],2)."</span>";
        echo "<span>".number_format($row['existencias'],2)."</span>";
        echo "</div>";
    }
}else{
    echo "<div class='sin-datos'>Sin datos</div>";
}
?>
</div>

</div>

</div>
</div>

<script>
function filtrar(){
    var mes = document.getElementById('mes').value;
    var anio = document.getElementById('anio').value;

    if(anio==""){
        alert("Ingresa año");
        return;
    }

    window.location.href = "?mes="+mes+"&anio="+anio;
}
</script>

</body>
</html>