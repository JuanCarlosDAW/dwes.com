<?php
// EJ2.php
session_start();

// Configuración de precios
define('BASE_PRICE', 5.0);
define('NON_VEG_EXTRA', 2.0);
define('EXTRA_CHEESE_PRICE', 1.5);
define('STUFFED_CRUST_PRICE', 2.5);

// Ingredientes y precios
$ingredientes_veg = [
    'Pepino' => 1.0,
    'Calabacín' => 1.5,
    'Pimiento verde' => 1.25,
    'Pimiento rojo' => 1.75,
    'Tomate' => 1.5,
    'Aceitunas' => 3.0,
    'Cebolla' => 1.0
];

$ingredientes_noveg = [
    'Atún' => 2.0,
    'Carne picada' => 2.5,
    'Peperoni' => 1.75,
    'Morcilla' => 2.25,
    'Anchoas' => 1.5,
    'Salmón' => 3.0,
    'Gambas' => 4.0,
    'Langostinos' => 4.0,
    'Mejillones' => 2.0
];

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function fmt($n) { return number_format((float)$n, 2, ',', '.').' €'; }

// Inicializar sesión si falta
if (!isset($_SESSION['step'])) {
    $_SESSION = [
        'step' => 1,
        'data' => [
            'nombre' => '',
            'direccion' => '',
            'tlf' => '',
            'tipo' => 'vegetariana', // vegetariana | no
            'ingredientes' => [], // array of strings
            'extras' => [
                'extra_queso' => false,
                'bordes_rellenos' => false
            ]
        ]
    ];
}

// Manejar reseteo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'reiniciar') {
    session_destroy();
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// Manejo de formularios por paso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_SESSION['step'];

    if ($step === 1 && isset($_POST['accion']) && $_POST['accion'] === 'siguiente1') {
        // Guardar datos iniciales
        $_SESSION['data']['nombre'] = trim($_POST['nombre'] ?? '');
        $_SESSION['data']['direccion'] = trim($_POST['direccion'] ?? '');
        $_SESSION['data']['tlf'] = trim($_POST['tlf'] ?? '');
        $_SESSION['data']['tipo'] = ($_POST['tipo'] ?? '') === 'no' ? 'no' : 'vegetariana';
        // limpiar ingredientes previos si cambia tipo
        $_SESSION['data']['ingredientes'] = [];
        $_SESSION['step'] = 2;
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }

    if ($step === 2) {
        // Añadir ingrediente o pasar al siguiente paso
        $tipo = $_SESSION['data']['tipo'];
        $available = $tipo === 'vegetariana' ? $ingredientes_veg : $ingredientes_noveg;
        $seleccion = $_POST['ingrediente'] ?? null;
        $accion = $_POST['accion'] ?? null;

        if ($seleccion && array_key_exists($seleccion, $available) && $accion === 'otro') {
            $_SESSION['data']['ingredientes'][] = $seleccion;
            header('Location: '.$_SERVER['PHP_SELF']);
            exit;
        }

        if ($accion === 'siguiente2') {
            // Si seleccionó un ingrediente y quiere avanzar, añadirlo
            if ($seleccion && array_key_exists($seleccion, $available)) {
                $_SESSION['data']['ingredientes'][] = $seleccion;
            }
            $_SESSION['step'] = 3;
            header('Location: '.$_SERVER['PHP_SELF']);
            exit;
        }
    }

    if ($step === 3 && isset($_POST['accion']) && $_POST['accion'] === 'siguiente3') {
        // Guardar extras
        $_SESSION['data']['extras']['extra_queso'] = isset($_POST['extra_queso']);
        $_SESSION['data']['extras']['bordes_rellenos'] = isset($_POST['bordes_rellenos']);
        $_SESSION['step'] = 4;
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
}

// Renderizar pantalla según paso
$step = $_SESSION['step'];
$data = &$_SESSION['data'];

if ($step === 4) {
    // Cabeceras de caché: duración 2 días
    $maxage = 2 * 24 * 3600;
    header('Cache-Control: public, max-age='.$maxage);
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $maxage).' GMT');
}

// HTML mínimo y formularios
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Gestión Pizzas - EJ2</title>
    <style>
        body { font-family: Arial, sans-serif; max-width:800px; margin:20px auto; line-height:1.5; }
        fieldset { margin-bottom:15px; padding:10px; }
        label { display:block; margin:5px 0; }
        .small { font-size:0.9em; color:#555; }
        .ingred-list { margin:8px 0; }
    </style>
</head>
<body>
<?php if ($step === 1): ?>
    <h1>Pedir Pizza - Paso 1: Datos</h1>
    <p>Todas las pizzas incluyen tomate frito y queso como ingredientes básicos. Precio base: <?php echo fmt(BASE_PRICE); ?>.
       Las pizzas no vegetarianas tienen un incremento de <?php echo fmt(NON_VEG_EXTRA); ?>.</p>

    <form method="post">
        <fieldset>
            <label>Nombre: <input type="text" name="nombre" value="<?php echo h($data['nombre']); ?>" required></label>
            <label>Dirección: <input type="text" name="direccion" value="<?php echo h($data['direccion']); ?>" required></label>
            <label>Teléfono: <input type="tel" name="tlf" value="<?php echo h($data['tlf']); ?>" required></label>
            <div class="small">Tipo de pizza:</div>
            <label><input type="radio" name="tipo" value="vegetariana" <?php echo $data['tipo'] === 'vegetariana' ? 'checked' : ''; ?>> Vegetariana</label>
            <label><input type="radio" name="tipo" value="no" <?php echo $data['tipo'] === 'no' ? 'checked' : ''; ?>> No vegetariana (+<?php echo fmt(NON_VEG_EXTRA); ?>)</label>
        </fieldset>
        <button type="submit" name="accion" value="siguiente1">Siguiente</button>
    </form>

<?php elseif ($step === 2): ?>
    <h1>Pedir Pizza - Paso 2: Ingredientes</h1>
    <p>Seleccione ingredientes (puede añadir uno a uno con "Otro ingrediente" hasta terminar y pulsar "Siguiente").</p>

    <div class="small">Tipo elegido: <?php echo $data['tipo'] === 'vegetariana' ? 'Vegetariana' : 'No vegetariana'; ?></div>

    <div class="ingred-list">
        <strong>Ingredientes añadidos hasta ahora:</strong>
        <?php if (count($data['ingredientes']) === 0): ?>
            <div class="small">No hay ingredientes añadidos (solo tomate frito y queso incl.).</div>
        <?php else: ?>
            <ul>
                <?php
                $available_all = $data['tipo'] === 'vegetariana' ? $ingredientes_veg : $ingredientes_noveg;
                foreach ($data['ingredientes'] as $ing): ?>
                    <li><?php echo h($ing).' - '.fmt($available_all[$ing] ?? 0); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <form method="post">
        <fieldset>
            <label>Ingrediente:
                <select name="ingrediente" required>
                    <option value="">-- elegir --</option>
                    <?php
                    $list = $data['tipo'] === 'vegetariana' ? $ingredientes_veg : $ingredientes_noveg;
                    foreach ($list as $nombre => $precio): ?>
                        <option value="<?php echo h($nombre); ?>"><?php echo h($nombre).' - '.fmt($precio); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </fieldset>
        <button type="submit" name="accion" value="otro">Otro ingrediente</button>
        <button type="submit" name="accion" value="siguiente2">Siguiente</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <button type="submit" name="accion" value="reiniciar">Reiniciar pedido</button>
    </form>

<?php elseif ($step === 3): ?>
    <h1>Pedir Pizza - Paso 3: Extras</h1>
    <p>Elija si desea extras para su pizza.</p>

    <form method="post">
        <fieldset>
            <label><input type="checkbox" name="extra_queso" <?php echo $data['extras']['extra_queso'] ? 'checked' : ''; ?>> Extra de queso (<?php echo fmt(EXTRA_CHEESE_PRICE); ?>)</label>
            <label><input type="checkbox" name="bordes_rellenos" <?php echo $data['extras']['bordes_rellenos'] ? 'checked' : ''; ?>> Bordes rellenos (<?php echo fmt(STUFFED_CRUST_PRICE); ?>)</label>
        </fieldset>
        <button type="submit" name="accion" value="siguiente3">Siguiente</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <button type="submit" name="accion" value="reiniciar">Reiniciar pedido</button>
    </form>

<?php elseif ($step === 4): ?>
    <h1>Pedir Pizza - Paso 4: Resumen Final</h1>

    <?php
    // Calcular precios
    $total = 0.0;
    $detalles = [];
    // Base y tipo
    $total += BASE_PRICE;
    $detalles[] = ['concepto' => 'Base (tomate frito y queso)', 'precio' => BASE_PRICE];
    if ($data['tipo'] === 'no') {
        $total += NON_VEG_EXTRA;
        $detalles[] = ['concepto' => 'Incremento no vegetariana', 'precio' => NON_VEG_EXTRA];
    }
    // Ingredientes
    $lista_ing = $data['tipo'] === 'vegetariana' ? $ingredientes_veg : $ingredientes_noveg;
    if (!empty($data['ingredientes'])) {
        foreach ($data['ingredientes'] as $ing) {
            $p = $lista_ing[$ing] ?? 0.0;
            $total += $p;
            $detalles[] = ['concepto' => 'Ingrediente: '.$ing, 'precio' => $p];
        }
    }
    // Extras
    if (!empty($data['extras']['extra_queso'])) {
        $total += EXTRA_CHEESE_PRICE;
        $detalles[] = ['concepto' => 'Extra de queso', 'precio' => EXTRA_CHEESE_PRICE];
    }
    if (!empty($data['extras']['bordes_rellenos'])) {
        $total += STUFFED_CRUST_PRICE;
        $detalles[] = ['concepto' => 'Bordes rellenos', 'precio' => STUFFED_CRUST_PRICE];
    }
    ?>

    <fieldset>
        <legend>Datos del cliente</legend>
        <div>Nombre: <?php echo h($data['nombre']); ?></div>
        <div>Dirección: <?php echo h($data['direccion']); ?></div>
        <div>Teléfono: <?php echo h($data['tlf']); ?></div>
    </fieldset>

    <fieldset>
        <legend>Detalles de la pizza</legend>
        <div>Tipo: <?php echo $data['tipo'] === 'vegetariana' ? 'Vegetariana' : 'No vegetariana'; ?></div>
        <div>
            <strong>Ingredientes seleccionados:</strong>
            <?php if (empty($data['ingredientes'])): ?>
                <div class="small">Ninguno (solo ingredientes básicos incluidos)</div>
            <?php else: ?>
                <ul>
                    <?php foreach ($data['ingredientes'] as $ing): ?>
                        <li><?php echo h($ing).' - '.fmt($lista_ing[$ing] ?? 0); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <strong>Extras:</strong>
            <ul>
                <li>Extra de queso: <?php echo $data['extras']['extra_queso'] ? fmt(EXTRA_CHEESE_PRICE) : 'No'; ?></li>
                <li>Bordes rellenos: <?php echo $data['extras']['bordes_rellenos'] ? fmt(STUFFED_CRUST_PRICE) : 'No'; ?></li>
            </ul>
        </div>
    </fieldset>

    <fieldset>
        <legend>Precios</legend>
        <ul>
            <?php foreach ($detalles as $d): ?>
                <li><?php echo h($d['concepto']); ?>: <?php echo fmt($d['precio']); ?></li>
            <?php endforeach; ?>
        </ul>
        <div><strong>Precio total: <?php echo fmt($total); ?></strong></div>
    </fieldset>

    <form method="post">
        <button type="submit" name="accion" value="reiniciar">Comenzar de nuevo</button>
    </form>

<?php else: ?>
    <p>Paso desconocido. Reiniciando...</p>
    <?php
    session_destroy();
    header('Refresh:0');
    ?>
<?php endif; ?>

</body>
</html>