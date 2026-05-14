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
    $estados_permitidos = ['reportado', 'en_revision', 'en_proceso', 'resuelto', 'rechazado'];
    
    // Validar que sea funcionario o admin
    if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], ['funcionario', 'admin'])) {
        header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("No tienes permiso para cambiar el estado."));
        exit();
    }

    if ($reporte_id > 0 && in_array($nuevo_estado, $estados_permitidos)) {
        // Obtener estado anterior
        $stmt_estado = $conexion->prepare('SELECT estado FROM reportes WHERE id = ?');
        $stmt_estado->bind_param('i', $reporte_id);
        $stmt_estado->execute();
        $res_estado = $stmt_estado->get_result();
        $estado_anterior = '';
        if ($res_estado->num_rows > 0) {
            $estado_anterior = $res_estado->fetch_assoc()['estado'];
        }
        $stmt_estado->close();

        // Actualizar estado
        $stmt = $conexion->prepare('UPDATE reportes SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?');
        $stmt->bind_param('si', $nuevo_estado, $reporte_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Registrar en historial
                $stmt_historial = $conexion->prepare('INSERT INTO historial_cambios_estado (reporte_id, estado_anterior, estado_nuevo, usuario_id) VALUES (?, ?, ?, ?)');
                $stmt_historial->bind_param('issi', $reporte_id, $estado_anterior, $nuevo_estado, $_SESSION['usuario_id']);
                $stmt_historial->execute();
                $stmt_historial->close();

                header("Location: ver_reporte.php?id=$reporte_id&success=" . urlencode("Estado actualizado a " . ucfirst(str_replace('_', ' ', $nuevo_estado))));
            } else {
                header("Location: ver_reporte.php?id=$reporte_id&error=" . urlencode("No se realizaron cambios. Es posible que el reporte ya estuviera en ese estado."));
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
