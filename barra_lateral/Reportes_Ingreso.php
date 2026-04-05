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
<title>Reporte Ingresos</title>

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

.menu-item.activo {
    background-color: #F6821F;
}

.menu-item:hover:not(.activo) {
    background-color: #F9D864;
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
}

.menu-dropdown:hover .submenu {
    display: flex;
}

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
    width: 600px;
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

.tabla-scroll {
    max-height: 220px; /* ~6 rows */
    overflow-y: auto;
    background-color: #E6E6E6;
    border-radius: 10px;
}

.tabla-scroll::-webkit-scrollbar {
    width: 6px;
}

.tabla-scroll::-webkit-scrollbar-thumb {
    background-color: #A0A0A0;
    border-radius: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td, th {
    padding: 10px;
    text-align: center;
    font-weight: bold;
}

th {
    background-color: #F6821F;
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

<div class="main">

<div class="box">

<div class="header-box">REPORTE INGRESOS</div>

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

<?php
$qTotal = "SELECT SUM(vp.subtotal) as total
FROM t_vender_general vg
JOIN t_vender_particular vp ON vg.id = vp.id_vg
WHERE MONTH(vg.fecha)=$mes AND YEAR(vg.fecha)=$anio";

$rTotal = mysqli_query($link, $qTotal);
$total = mysqli_fetch_array($rTotal);
?>

<h3>Total: $<?php echo number_format($total['total'],2); ?></h3>

<div class="tabla-scroll">
<table>
<tr>
    <th>Día</th>
    <th>Total</th>
</tr>

<?php
$q = "SELECT DAY(vg.fecha) as dia, SUM(vp.subtotal) as total
FROM t_vender_general vg
JOIN t_vender_particular vp ON vg.id = vp.id_vg
WHERE MONTH(vg.fecha)=$mes AND YEAR(vg.fecha)=$anio
GROUP BY DAY(vg.fecha)
ORDER BY dia";

$r = mysqli_query($link, $q);

if(mysqli_num_rows($r)>0){
    while($row=mysqli_fetch_array($r)){
        echo "<tr>";
        echo "<td>".$row['dia']."</td>";
        echo "<td>$".number_format($row['total'],2)."</td>";
        echo "</tr>";
    }
}else{
    echo "<tr><td colspan='2'>Sin datos</td></tr>";
}
?>
</table>
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