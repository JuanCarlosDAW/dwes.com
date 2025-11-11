<?php
// Función que verifica si un número es primo
function esPrimo($numero) {
    if ($numero <= 1) {
        return false;
    }
    for ($i = 2; $i <= sqrt($numero); $i++) {
        if ($numero % $i == 0) {
            return false;
        }
    }
    return true;
}

// Mostrar todos los números primos menores que 1000
for ($i = 2; $i < 1000; $i++) {
    if (esPrimo($i)) {
        echo $i . "\n";
    }
}
?>
