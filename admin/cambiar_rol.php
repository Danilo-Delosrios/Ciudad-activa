<?php
require_once '../includes/conexion.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    $nuevo_rol = isset($_POST['nuevo_rol']) ? trim($_POST['nuevo_rol']) : '';

    $roles_permitidos = ['usuario', 'funcionario', 'admin'];

    // Validaciones básicas
    if ($target_user_id <= 0 || !in_array($nuevo_rol, $roles_permitidos)) {
        header("Location: roles.php?error=" . urlencode("Datos inválidos."));
        exit();
    }

    if ($target_user_id === $_SESSION['usuario_id']) {
        header("Location: roles.php?error=" . urlencode("No puedes cambiar tu propio rol aquí."));
        exit();
    }

    $stmt = $conexion->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
    $stmt->bind_param('si', $nuevo_rol, $target_user_id);

    if ($stmt->execute()) {
        header("Location: roles.php?success=" . urlencode("Rol actualizado correctamente."));
    } else {
        header("Location: roles.php?error=" . urlencode("Error al actualizar el rol: " . $conexion->error));
    }

    $stmt->close();
} else {
    header('Location: roles.php');
}

$conexion->close();
?>
