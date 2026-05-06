<?php
$titulo_pagina = 'Mis Reportes - Ciudad Activa';
$css_adicional = '../css/reportes.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$sql = 'SELECT id, titulo, descripcion, categoria, estado, ubicacion, fecha_creacion
        FROM reportes WHERE usuario_id = ? ORDER BY fecha_creacion DESC';
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$reportes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();

$etiquetas_cat = [
    'infraestructura' => '🏗️ Infraestructura',
    'limpieza'        => '🧹 Limpieza',
    'seguridad'       => '🚨 Seguridad',
    'transito'        => '🚦 Tránsito',
    'otros'           => '📌 Otros',
];
?>

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-list"></i> Mis Reportes</h1>
            <a href="crear.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Reporte
            </a>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Lista de reportes -->
        <?php if (empty($reportes)): ?>
            <div class="card text-center" style="padding: 48px 24px;">
                <div style="font-size:3rem; margin-bottom:16px;">📋</div>
                <h3 style="color:#64748b; font-weight:500;">No tienes reportes aún</h3>
                <p style="color:#94a3b8; margin-bottom:20px;">Crea tu primer reporte para ayudar a mejorar tu ciudad.</p>
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Crear mi primer reporte
                </a>
            </div>
        <?php else: ?>
            <div class="reportes-list">
                <?php foreach ($reportes as $reporte): ?>
                    <div class="reporte-item">
                        <div class="reporte-header">
                            <div>
                                <h4 class="reporte-titulo"><?php echo htmlspecialchars($reporte['titulo']); ?></h4>
                                <div class="reporte-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo $etiquetas_cat[$reporte['categoria']] ?? ucfirst($reporte['categoria']); ?></span>
                                    <span><i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($reporte['ubicacion']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($reporte['fecha_creacion'])); ?></span>
                                </div>
                            </div>
                            <span class="reporte-estado estado-<?php echo $reporte['estado']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $reporte['estado'])); ?>
                            </span>
                        </div>

                        <p class="reporte-descripcion">
                            <?php echo htmlspecialchars(mb_substr($reporte['descripcion'], 0, 160)); ?>
                            <?php echo mb_strlen($reporte['descripcion']) > 160 ? '…' : ''; ?>
                        </p>

                        <div class="reporte-acciones">
                            <a href="ver_reporte.php?id=<?php echo $reporte['id']; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                            <a href="editar_reporte.php?id=<?php echo $reporte['id']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eliminar_reporte.php?id=<?php echo $reporte['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Estás seguro de eliminar este reporte? No se puede deshacer.')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
