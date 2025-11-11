/*Escribir un script PHP que rellena con números aleatorios una matriz de 20 filas por
50 columnas y rellene un vector de 50 elementos con la suma de las 50 columnas. Al
final debe visualizar este vector.*/
<?php
// Crear una matriz de 20 filas por 50 columnas
$filas = 20;
$columnas = 50;
$matriz = array();
$sumaColumnas = array_fill(0, $columnas, 0); // Inicializar el vector de sumas
// Rellenar la matriz con números aleatorios y calcular la suma de las columnas
for ($i = 0; $i < $filas; $i++) {
    for ($j = 0; $j < $columnas; $j++) {
        $numeroAleatorio = rand(1, 100); // Generar un número aleatorio entre 1 y 100
        $matriz[$i][$j] = $numeroAleatorio;
        $sumaColumnas[$j] += $numeroAleatorio; // Sumar el número a la columna correspondiente
    }
}
// Visualizar la matriz
echo "<h2>Matriz de Números Aleatorios</h2>";
echo "<table border='1'>";
for ($i = 0; $i < $filas; $i++) {
    echo "<tr>";
    for ($j = 0; $j < $columnas; $j++) {
        echo "<td>" . $matriz[$i][$j] . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
// Visualizar el vector de sumas de columnas
echo "<h2>Suma de Columnas</h2>";
echo "<table border='1'><tr>";
for ($j = 0; $j < $columnas; $j++) {
    echo "<td>" . $sumaColumnas[$j] . "</td>";
}
echo "</tr></table>";
?>