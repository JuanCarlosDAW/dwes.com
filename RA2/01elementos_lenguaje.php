<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Elementos del lenguaje</h1>
    <h2>Entrada y Salida</h2>
    <p>La entrada de datos en php es con un formulario o enlace. La salida siempre se produce con la forma echo y su forma abreviada, y la funcion print.
        Ademas tebemos con la funcion echo, y su forma abreviada, y la funcion print
    </p>

    <h3>Funcion echo</h3>
<?php
echo"<p>La funcion echo emite el resultado de una expresion a la salida(del servidor al cliente web). Se pueede usar como funcion o como contruccion del lenguaje</p>";
echo"<p>Esto es un parrafo HTML enviado con echo</p>";
$nombre="Juan";

echo "<p>Hola, $nombre, como estas?</p>";
echo "<p>Hola, $nombre, como estas?</p>";

//Quiero un salto de linea al final de la linea
echo "<p>Hola, esta linea acaba en un salto\n</p>";
echo "Supuestamente esta linea es la siguiente a la anterior \n y esta va despues"
?>

<!--Uso habitual de echo abreciado es en los formularios-->
<input type="text" size="30" name="nombre" id="nombre" value="<?=$nombre?>">
<input type="checkbox" name="portatil" id="portatil" <?=$tiene_portatil ? "checked" : ""?>>

<h2>Conversiones de tipos</h2>
<?php
$cadena ="25";
$numero=8;
$resultado= $cadena + $numero +$boleano;
echo "<p>El resultado es $resultado</p>"
?>
</body>
</html>