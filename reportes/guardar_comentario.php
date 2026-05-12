<?php
require_once '../includes/conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporte_id = isset($_POST['reporte_id']) ? (int)$_POST['reporte_id'] : 0;
    $contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';

    if ($reporte_id > 0 && !empty($contenido)) {
        $stmt = $conexion->prepare('INSERT INTO comentarios (reporte_id, usuario_id, contenido) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $reporte_id, $_SESSION['usuario_id'], $contenido);
        
        if ($stmt->execute()) {
            header("Location: ver_reporte.php?id=$reporte_id&success=" . urlencode("Comentario publicado correctamente."));
        } else {
            header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("Error al publicar el comentario: " . $conexion->error));
        }
        $stmt->close();
    } else {
        header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("El comentario no puede estar vacío."));
    }
} else {
    header('Location: mis_reportes.php');
}

$conexion->close();
?>
