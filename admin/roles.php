<?php
$titulo_pagina = 'Panel de Administración - Roles';
$css_adicional = '../css/dashboard.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Si no es admin, lo redirigimos al dashboard
    header('Location: ../dashboard/dashboard.php?error=' . urlencode('No tienes permisos para acceder al panel de administración.'));
    exit();
}

// Obtener lista de usuarios
$sql = 'SELECT id, nombre, email, rol, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC';
$resultado = $conexion->query($sql);
$usuarios = $resultado->fetch_all(MYSQLI_ASSOC);

$conexion->close();
?>

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Gestión de Roles</h1>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card" style="margin-top: 20px;">
            <h3>Lista de Usuarios</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                            <th style="padding: 12px; font-weight: 600; color: #475569;">ID</th>
                            <th style="padding: 12px; font-weight: 600; color: #475569;">Nombre</th>
                            <th style="padding: 12px; font-weight: 600; color: #475569;">Email</th>
                            <th style="padding: 12px; font-weight: 600; color: #475569;">Rol Actual</th>
                            <th style="padding: 12px; font-weight: 600; color: #475569;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px;"><?php echo $u['id']; ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($u['nombre']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="padding: 12px;">
                                <?php if ($u['rol'] === 'admin'): ?>
                                    <span style="background: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Admin</span>
                                <?php elseif ($u['rol'] === 'funcionario'): ?>
                                    <span style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Funcionario</span>
                                <?php else: ?>
                                    <span style="background: #64748b; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Usuario</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($u['id'] !== $_SESSION['usuario_id']): // No se puede cambiar el rol a sí mismo ?>
                                    <form action="cambiar_rol.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                        <select name="nuevo_rol" style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                                            <option value="usuario" <?php echo $u['rol'] === 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                                            <option value="funcionario" <?php echo $u['rol'] === 'funcionario' ? 'selected' : ''; ?>>Funcionario</option>
                                            <option value="admin" <?php echo $u['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" style="margin-left: 5px;">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-style: italic;">Tu cuenta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" style="padding: 15px; text-align: center; color: #94a3b8;">No hay usuarios registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
