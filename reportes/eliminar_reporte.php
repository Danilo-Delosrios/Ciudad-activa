<?php
/**
 * Eliminar reporte — solo el propietario puede eliminar su reporte
 */
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: mis_reportes.php');
    exit();
}

// Verificar que el reporte pertenece al usuario actual
$stmt = $conexion->prepare('SELECT id, imagen FROM reportes WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('ii', $id, $_SESSION['usuario_id']);
$stmt->execute();
$reporte = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reporte) {
    $conexion->close();
    header('Location: mis_reportes.php?error=' . urlencode('Reporte no encontrado o no tienes permiso para eliminarlo'));
    exit();
}

// Eliminar imagen adjunta si existe
if ($reporte['imagen']) {
    $ruta_imagen = '../uploads/reportes/' . $reporte['imagen'];
    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen);
    }
}

// Eliminar el reporte
$stmt = $conexion->prepare('DELETE FROM reportes WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('ii', $id, $_SESSION['usuario_id']);
$exito = $stmt->execute();
$stmt->close();
$conexion->close();

if ($exito) {
    header('Location: mis_reportes.php?success=' . urlencode('Reporte eliminado correctamente'));
} else {
    header('Location: mis_reportes.php?error=' . urlencode('No se pudo eliminar el reporte'));
}
exit();
?>
