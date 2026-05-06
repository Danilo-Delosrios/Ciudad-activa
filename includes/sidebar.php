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
        <h3>🏙️ Ciudad Activa</h3>
        <p>Portal Ciudadano</p>
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
                <a href="../reportes/crear.php"
                   class="<?php echo $pagina_actual === 'crear.php' ? 'active' : ''; ?>">
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
            <li>
                <a href="../logout.php" style="color:#f87171;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>
</aside>
