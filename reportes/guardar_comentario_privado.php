<?php
require_once '../includes/conexion.php';
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['funcionario', 'admin'])) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporte_id = isset($_POST['reporte_id']) ? (int)$_POST['reporte_id'] : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    if ($reporte_id > 0 && !empty($comentario)) {
        $stmt = $conexion->prepare('INSERT INTO comentarios_privados (reporte_id, usuario_id, comentario) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $reporte_id, $_SESSION['usuario_id'], $comentario);
        
        if ($stmt->execute()) {
            header("Location: ver_reporte.php?id=$reporte_id&success=" . urlencode("Nota interna guardada correctamente."));
        } else {
            header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("Error al guardar la nota."));
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
