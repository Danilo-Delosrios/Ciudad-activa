<?php
/**
 * Guardar nuevo reporte en la base de datos
 */
require_once '../includes/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crear.php');
    exit();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$titulo      = trim($_POST['titulo']      ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria   = trim($_POST['categoria']   ?? '');
$ubicacion   = trim($_POST['ubicacion']   ?? '');
$ciudad      = trim($_POST['ciudad']      ?? '');
$usuario_id  = (int) $_SESSION['usuario_id'];

// Coordenadas — opcionales
$latitud  = (isset($_POST['latitud'])  && $_POST['latitud']  !== '') ? $_POST['latitud']  : null;
$longitud = (isset($_POST['longitud']) && $_POST['longitud'] !== '') ? $_POST['longitud'] : null;

// Validar campos requeridos
if (empty($titulo) || empty($descripcion) || empty($categoria) || empty($ubicacion) || empty($ciudad)) {
    header('Location: crear.php?error=' . urlencode('Por favor completa todos los campos requeridos'));
    exit();
}

// Validar coordenadas si se enviaron
if ($latitud !== null) {
    $lat_f = (float) $latitud;
    if ($lat_f < -90 || $lat_f > 90) {
        header('Location: crear.php?error=' . urlencode('Latitud inválida'));
        exit();
    }
    $latitud = (string) $lat_f;   // MySQL acepta string decimal -> DECIMAL
}
if ($longitud !== null) {
    $lng_f = (float) $longitud;
    if ($lng_f < -180 || $lng_f > 180) {
        header('Location: crear.php?error=' . urlencode('Longitud inválida'));
        exit();
    }
    $longitud = (string) $lng_f;
}

// Procesar imagen si se cargó
$imagen_nombre = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $archivo          = $_FILES['imagen'];
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tamano_maximo    = 5 * 1024 * 1024; // 5 MB

    if (!in_array($archivo['type'], $tipos_permitidos)) {
        header('Location: crear.php?error=' . urlencode('Tipo de imagen no permitido (JPG, PNG, GIF, WEBP)'));
        exit();
    }
    if ($archivo['size'] > $tamano_maximo) {
        header('Location: crear.php?error=' . urlencode('La imagen supera el límite de 5 MB'));
        exit();
    }

    $directorio = '../uploads/reportes/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $extension    = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_unico = uniqid('rep_', true) . '.' . strtolower($extension);
    $ruta_archivo = $directorio . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
        $imagen_nombre = $nombre_unico;
    }
}

// ── Insertar reporte ────────────────────────────────────────────────────────
// Usamos 's' para TODOS los parámetros incluyendo lat/lng
// MySQL convierte strings numéricos a DECIMAL correctamente
// y PHP MySQLi envía NULL SQL cuando el valor PHP es null con tipo 's'
$sql = 'INSERT INTO reportes
            (usuario_id, titulo, descripcion, categoria, ubicacion, ciudad, latitud, longitud, imagen, estado, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "reportado", NOW())';

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header('Location: crear.php?error=' . urlencode('Error preparando consulta: ' . $conexion->error));
    exit();
}

// Todos como 's' excepto usuario_id ('i') — PHP envía NULL correctamente con 's'
$stmt->bind_param('issssssss',
    $usuario_id,
    $titulo,
    $descripcion,
    $categoria,
    $ubicacion,
    $ciudad,
    $latitud,
    $longitud,
    $imagen_nombre
);

if ($stmt->execute()) {
    $stmt->close();
    $conexion->close();
    header('Location: mis_reportes.php?success=' . urlencode('¡Reporte enviado correctamente!'));
    exit();
} else {
    $err = $stmt->error;
    $stmt->close();
    $conexion->close();
    header('Location: crear.php?error=' . urlencode('Error al guardar: ' . $err));
    exit();
}
?>
