<?php
// Rellenar la matriz con valores aleatorios
$filas = 10;
$columnas = 20;
$matriz = array();
for ($i = 0; $i < $filas; $i++) {
    for ($j = 0; $j < $columnas; $j++) {
        $matriz[$i][$j] = rand(1, 100);
    }
}
// Generar un número aleatorio para buscar
$numeroBuscado = rand(1, 100);
$encontrado = false;
$posiciones = array();
// Buscar el número en la matriz
for ($i = 0; $i < $filas; $i++) {
    for ($j = 0; $j < $columnas; $j++) {
        if ($matriz[$i][$j] == $numeroBuscado) {
            $encontrado = true;
            $posiciones[] = "Fila: $i, Columna: $j";
        }
    }
}
// Mostrar resultados
echo "Número buscado: $numeroBuscado<br>";
if ($encontrado) {
    echo "Número encontrado en las siguientes posiciones:<br>";
    foreach ($posiciones as $posicion) {
        echo $posicion . "<br>";
    }
} else {
    echo "Número no encontrad en la matriz.";
}
?>


