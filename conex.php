<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conectarse</title>
</head>
<body>
    <?php
    function Conectarse(){
        if(!($link=mysqli_connect("localhost","miuser","mipass","2doAvance"))){//Crear usuarios y conectar BDD
            echo "Error conectando a la base de datos.";
            exit();
        }
        return $link;
    }
    ?>
    
</body>
</html>