<?php

// Archivo: /var/www/dwes.com/RA2/Mantenimiento de sesiones.php
// Aplicación simple de descarga de archivos (3 pantallas en un solo script)

// Helpers
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function format_size($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    $units = ['KB','MB','GB','TB'];
    $u = -1;
    do {
        $bytes /= 1024;
        $u++;
    } while ($bytes >= 1024 && $u < count($units)-1);
    return round($bytes, 2) . ' ' . $units[$u];
}
function safe_realpath($path) {
    $rp = @realpath($path);
    return $rp === false ? false : $rp;
}

// Obtener parámetros (permitimos POST o GET para flexibilidad)
$dir_input = $_REQUEST['dir'] ?? '';
$file_input = $_REQUEST['file'] ?? '';
$action = $_REQUEST['action'] ?? '';

// Normalizar input
$dir_input = trim($dir_input);
$file_input = trim($file_input);

// Si se pide descargar, enviamos el archivo (pantalla "descarga real")
if ($action === 'download' && $dir_input !== '' && $file_input !== '') {
    $dirReal = safe_realpath($dir_input);
    if ($dirReal === false || !is_dir($dirReal) || !is_readable($dirReal)) {
        http_response_code(404);
        echo "Directorio no válido o no legible.";
        exit;
    }
    // Garantizar que el archivo está dentro del directorio
    $filePath = $dirReal . DIRECTORY_SEPARATOR . $file_input;
    $fileReal = safe_realpath($filePath);
    if ($fileReal === false || strpos($fileReal, $dirReal) !== 0 || !is_file($fileReal) || !is_readable($fileReal)) {
        http_response_code(404);
        echo "Archivo no válido o no legible.";
        exit;
    }

    // Enviar cabeceras y contenido del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileReal) ?: 'application/octet-stream';
    finfo_close($finfo);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($fileReal) . '"');
    header('Content-Length: ' . filesize($fileReal));
    header('Cache-Control: private');
    // Leer el archivo
    readfile($fileReal);
    exit;
}

// Si se envió un directorio pero no archivo: mostrar lista de archivos (pantalla b)
if ($dir_input !== '' && $file_input === '') {
    $dirReal = safe_realpath($dir_input);
    if ($dirReal === false || !is_dir($dirReal) || !is_readable($dirReal)) {
        $error = "Directorio no válido o no legible por el servidor.";
        $files = [];
    } else {
        // Leer ficheros (solo archivos, sin directorios)
        $all = scandir($dirReal);
        $files = [];
        foreach ($all as $f) {
            if ($f === '.' || $f === '..') continue;
            $p = $dirReal . DIRECTORY_SEPARATOR . $f;
            if (is_file($p) && is_readable($p)) $files[] = $f;
        }
        sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    }

    ?>
    <!doctype html>
    <html lang="es">
    <head><meta charset="utf-8"><title>Listado de archivos</title></head>
    <body>
    <h2>Listado de archivos en: <?php echo h($dirReal ?? $dir_input); ?></h2>
    <?php if (!empty($error)) { echo '<p style="color:red">'.h($error).'</p>'; } ?>

    <?php if (empty($files)): ?>
        <p>No hay archivos legibles en ese directorio.</p>
        <p><a href="<?php echo h($_SERVER['PHP_SELF']); ?>">Volver a pantalla inicial</a></p>
    <?php else: ?>
        <form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="dir" value="<?php echo h($dirReal); ?>">
            <label for="file">Seleccione un archivo:</label><br>
            <select name="file" id="file" size="10" required>
                <?php foreach ($files as $f): ?>
                    <option value="<?php echo h($f); ?>"><?php echo h($f); ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <button type="submit" name="action" value="info">Ver información</button>
        </form>
        <p><a href="<?php echo h($_SERVER['PHP_SELF']); ?>">Volver a pantalla inicial</a></p>
    <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

// Si se envió archivo para info (pantalla c) o venimos desde el botón "Ver información"
if ($dir_input !== '' && $file_input !== '' && ($action === 'info' || $action === '')) {
    $dirReal = safe_realpath($dir_input);
    if ($dirReal === false || !is_dir($dirReal) || !is_readable($dirReal)) {
        $error = "Directorio no válido o no legible por el servidor.";
    } else {
        $filePath = $dirReal . DIRECTORY_SEPARATOR . $file_input;
        $fileReal = safe_realpath($filePath);
        if ($fileReal === false || strpos($fileReal, $dirReal) !== 0 || !is_file($fileReal) || !is_readable($fileReal)) {
            $error = "Archivo no válido o no legible.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileReal) ?: 'application/octet-stream';
            finfo_close($finfo);
            $size = filesize($fileReal);
            $created = date('Y-m-d H:i:s', filectime($fileReal));
            $modified = date('Y-m-d H:i:s', filemtime($fileReal));
            $absPath = $fileReal;
        }
    }

    ?>
    <!doctype html>
    <html lang="es">
    <head><meta charset="utf-8"><title>Información de archivo</title></head>
    <body>
    <h2>Información del archivo seleccionado</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red"><?php echo h($error); ?></p>
        <p><a href="<?php echo h($_SERVER['PHP_SELF']); ?>">Volver a pantalla inicial</a></p>
    <?php else: ?>
        <table>
            <tr><td>Nombre:</td><td><?php echo h(basename($fileReal)); ?></td></tr>
            <tr><td>Tipo MIME:</td><td><?php echo h($mime); ?></td></tr>
            <tr><td>Tamaño:</td><td><?php echo h(format_size($size)); ?> (<?php echo h($size); ?> bytes)</td></tr>
            <tr><td>Ubicación:</td><td><?php echo h($absPath); ?></td></tr>
            <tr><td>Fecha de creación:</td><td><?php echo h($created); ?></td></tr>
            <tr><td>Fecha de modificación:</td><td><?php echo h($modified); ?></td></tr>
        </table>

        <p>
            <!-- Enlace para descargar: accion 'download' -->
            <a href="<?php echo h($_SERVER['PHP_SELF']) . '?action=download&dir=' . rawurlencode($dirReal) . '&file=' . rawurlencode(basename($fileReal)); ?>">Descargar fichero</a>
        </p>

        <p><a href="<?php echo h($_SERVER['PHP_SELF']); ?>">Volver a pantalla inicial</a></p>
    <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

// Pantalla inicial (a)
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Pantalla inicial - Selección de directorio</title></head>
<body>
<h2>Elegir directorio para listar archivos</h2>
<p>Introduzca la ruta del directorio que desea listar. Puede ser absoluta o relativa al servidor.</p>
<form method="post" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
    <label for="dir">Directorio:</label><br>
    <input type="text" id="dir" name="dir" size="60" required placeholder="/var/www"><br><br>
    <button type="submit">Listar archivos</button>
</form>
</body>
</html>