<?php
require_once '../includes/conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporte_id = isset($_POST['reporte_id']) ? (int)$_POST['reporte_id'] : 0;
    $nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';

    // Validar estados permitidos
    $estados_permitidos = ['pendiente', 'en_proceso', 'resuelto', 'rechazado'];
    
    if ($reporte_id > 0 && in_array($nuevo_estado, $estados_permitidos)) {
        // Verificar propiedad del reporte
        $stmt = $conexion->prepare('UPDATE reportes SET estado = ?, fecha_actualizacion = NOW() WHERE id = ? AND usuario_id = ?');
        $stmt->bind_param('sii', $nuevo_estado, $reporte_id, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: ver_reporte.php?id=$reporte_id&success=" . urlencode("Estado actualizado a " . ucfirst(str_replace('_', ' ', $nuevo_estado))));
            } else {
                header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("No se realizaron cambios o no tienes permiso."));
            }
        } else {
            header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("Error al actualizar el estado: " . $conexion->error));
        }
        $stmt->close();
    } else {
        header("Location: mis_reportes.php?error=" . urlencode("Datos de actualización inválidos."));
    }
} else {
    header('Location: mis_reportes.php');
}

$conexion->close();
?>
