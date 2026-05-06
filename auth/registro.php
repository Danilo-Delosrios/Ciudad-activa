<?php
$titulo_pagina = 'Crear Cuenta - Ciudad Activa';
$css_adicional = '../css/auth.css';
require_once '../includes/header.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-logo">
            <span class="logo-icon">🏙️</span>
            <h1>Ciudad Activa</h1>
            <p>Portal Ciudadano</p>
        </div>

        <h2>Crear Cuenta</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="procesar_registro.php">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" required
                       placeholder="Juan García" autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required
                       placeholder="tu@correo.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required
                       placeholder="Mínimo 8 caracteres" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       placeholder="Repite tu contraseña" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Crear Cuenta
            </button>
        </form>

        <div class="auth-links">
            <a href="login.php">¿Ya tienes cuenta? <strong>Inicia sesión</strong></a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
