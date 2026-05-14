<?php
/**
 * Barra lateral de navegación
 */
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/logo_Ciudad_activa.png" alt="Ciudad Activa" class="sidebar-logo">
        <div class="sidebar-header-text">
            <h3>Ciudad Activa</h3>
            <p>Portal Ciudadano</p>
            <div style="margin-top: 8px;">
                <span style="background: rgba(255,255,255,0.15); color: #f8fafc; padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-user-tag"></i> <?php echo isset($_SESSION['usuario_rol']) ? htmlspecialchars($_SESSION['usuario_rol']) : 'USUARIO'; ?>
                </span>
            </div>
        </div>
    </div>

    <nav>
        <ul class="sidebar-menu">
            <li>
                <a href="../dashboard/dashboard.php"
                   class="<?php echo $pagina_actual === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../reportes/explorar.php"
                   class="<?php echo $pagina_actual === 'explorar.php' ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> Explorar Reportes
                </a>
            </li>
            <li>
                <a href="../reportes/crear.php"
                   class="sidebar-crear-reporte <?php echo $pagina_actual === 'crear.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Crear Reporte
                </a>
            </li>
            <li>
                <a href="../reportes/mis_reportes.php"
                   class="<?php echo $pagina_actual === 'mis_reportes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Mis Reportes
                </a>
            </li>
            <li>
                <a href="../perfil/perfil.php"
                   class="<?php echo $pagina_actual === 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Mi Perfil
                </a>
            </li>
            <li class="menu-divider"></li>
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
            <li>
                <a href="../admin/roles.php"
                   class="<?php echo strpos($pagina_actual, 'roles.php') !== false ? 'active' : ''; ?>" style="color: #3b82f6;">
                    <i class="fas fa-users-cog"></i> Panel Admin
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="../logout.php" style="color:#f87171;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>
</aside>
