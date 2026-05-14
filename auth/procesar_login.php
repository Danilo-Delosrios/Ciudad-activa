<?php
/**
 * Procesar inicio de sesión
 */

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: login.php?error=Por favor completa todos los campos');
    exit();
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.php?error=El correo no es válido');
    exit();
}

// Buscar usuario en la base de datos
$sql = 'SELECT id, nombre, email, contraseña, rol FROM usuarios WHERE email = ?';
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header('Location: login.php?error=Error en la consulta');
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: login.php?error=Usuario no encontrado');
    exit();
}

$usuario = $resultado->fetch_assoc();

// Verificar contraseña
if (!password_verify($password, $usuario['contraseña'])) {
    header('Location: login.php?error=Contraseña incorrecta');
    exit();
}

// Crear sesión
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'];

$stmt->close();
$conexion->close();

header('Location: ../dashboard/dashboard.php');
exit();
?>
