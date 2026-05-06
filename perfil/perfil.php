<?php
$titulo_pagina = 'Mi Perfil - Ciudad Activa';
$css_adicional = '../css/perfil.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$error   = null;
$success = null;

// ── Cambiar contraseña ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'cambiar_contrasena') {
        $pass_actual  = $_POST['password_actual']  ?? '';
        $pass_nueva   = $_POST['password_nueva']   ?? '';
        $pass_confirm = $_POST['password_confirm'] ?? '';

        if (empty($pass_actual) || empty($pass_nueva) || empty($pass_confirm)) {
            $error = 'Por favor completa todos los campos.';
        } elseif ($pass_nueva !== $pass_confirm) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } elseif (strlen($pass_nueva) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } else {
            $stmt = $conexion->prepare('SELECT contraseña FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $_SESSION['usuario_id']);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!password_verify($pass_actual, $usuario['contraseña'])) {
                $error = 'La contraseña actual es incorrecta.';
            } else {
                $hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare('UPDATE usuarios SET contraseña=? WHERE id=?');
                $stmt->bind_param('si', $hash, $_SESSION['usuario_id']);
                $stmt->execute();
                $stmt->close();
                $success = 'Contraseña actualizada correctamente.';
            }
        }
    }

    if ($_POST['accion'] === 'editar_perfil') {
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            $error = 'El nombre no puede estar vacío.';
        } else {
            $stmt = $conexion->prepare('UPDATE usuarios SET nombre=? WHERE id=?');
            $stmt->bind_param('si', $nombre, $_SESSION['usuario_id']);
            $stmt->execute();
            $stmt->close();
            $_SESSION['usuario_nombre'] = $nombre;
            $success = 'Perfil actualizado correctamente.';
        }
    }
}

// Obtener datos del usuario y estadísticas
$stmt = $conexion->prepare('SELECT nombre, email, fecha_creacion FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ?');
$stmt->bind_param('i', $_SESSION['usuario_id']); $stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total']; $stmt->close();

$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ? AND estado="resuelto"');
$stmt->bind_param('i', $_SESSION['usuario_id']); $stmt->execute();
$resueltos = $stmt->get_result()->fetch_assoc()['total']; $stmt->close();

$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ? AND estado IN ("pendiente","en_proceso")');
$stmt->bind_param('i', $_SESSION['usuario_id']); $stmt->execute();
$activos = $stmt->get_result()->fetch_assoc()['total']; $stmt->close();

$conexion->close();
?>

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
            <a href="../logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="perfil-grid">

            <!-- Card: avatar + datos -->
            <div class="perfil-card perfil-hero">
                <div class="avatar-grande"><?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?></div>
                <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                <p class="perfil-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p class="perfil-fecha"><i class="fas fa-calendar-alt"></i> Miembro desde <?php echo date('d \d\e F \d\e Y', strtotime($usuario['fecha_creacion'])); ?></p>

                <div class="perfil-stats-mini">
                    <div class="stat-mini">
                        <span class="stat-mini-num"><?php echo $total; ?></span>
                        <span class="stat-mini-label">Total</span>
                    </div>
                    <div class="stat-mini">
                        <span class="stat-mini-num" style="color:#16a34a"><?php echo $resueltos; ?></span>
                        <span class="stat-mini-label">Resueltos</span>
                    </div>
                    <div class="stat-mini">
                        <span class="stat-mini-num" style="color:#d97706"><?php echo $activos; ?></span>
                        <span class="stat-mini-label">Activos</span>
                    </div>
                </div>
            </div>

            <!-- Card: editar perfil -->
            <div class="perfil-card">
                <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
                <form method="POST">
                    <input type="hidden" name="accion" value="editar_perfil">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre"
                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled
                               style="background:#f8fafc; cursor:not-allowed;">
                        <small>El correo no se puede cambiar.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </form>
            </div>

            <!-- Card: cambiar contraseña -->
            <div class="perfil-card">
                <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                <form method="POST">
                    <input type="hidden" name="accion" value="cambiar_contrasena">
                    <div class="form-group">
                        <label for="password_actual">Contraseña actual</label>
                        <input type="password" id="password_actual" name="password_actual" required placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label for="password_nueva">Nueva contraseña</label>
                        <input type="password" id="password_nueva" name="password_nueva" required placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar nueva contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" required placeholder="Repite la nueva contraseña">
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
