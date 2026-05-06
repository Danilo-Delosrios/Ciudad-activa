<?php
$titulo_pagina = 'Iniciar Sesión - Ciudad Activa';
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
            <img src="../img/logo_Ciudad_activa.png" alt="Ciudad Activa" class="logo-img">
            <h1>Ciudad Activa</h1>
            <p>Portal Ciudadano</p>
        </div>

        <h2>Iniciar Sesión</h2>

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

        <form method="POST" action="procesar_login.php">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required
                       placeholder="tu@correo.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <div class="auth-links">
            <a href="registro.php">¿No tienes cuenta? <strong>Regístrate</strong></a>
            <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
