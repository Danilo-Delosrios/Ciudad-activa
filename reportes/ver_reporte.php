<?php
$titulo_pagina = 'Ver Reporte - Ciudad Activa';
$css_adicional = '../css/reportes.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: mis_reportes.php');
    exit();
}

// Obtener el reporte (solo del usuario actual)
$stmt = $conexion->prepare(
    'SELECT r.*, u.nombre AS autor_nombre
     FROM reportes r
     JOIN usuarios u ON r.usuario_id = u.id
     WHERE r.id = ? AND r.usuario_id = ?'
);
$stmt->bind_param('ii', $id, $_SESSION['usuario_id']);
$stmt->execute();
$reporte = $stmt->get_result()->fetch_assoc();

if (!$reporte) {
    header('Location: mis_reportes.php?error=' . urlencode('Reporte no encontrado'));
    exit();
}

$stmt->close();
$conexion->close();

$etiquetas_estado = [
    'pendiente'  => ['label' => 'Pendiente',   'class' => 'estado-pendiente'],
    'en_proceso' => ['label' => 'En Proceso',  'class' => 'estado-en_proceso'],
    'resuelto'   => ['label' => 'Resuelto',    'class' => 'estado-resuelto'],
    'rechazado'  => ['label' => 'Rechazado',   'class' => 'estado-rechazado'],
];

$etiquetas_cat = [
    'infraestructura' => '🏗️ Infraestructura',
    'limpieza'        => '🧹 Limpieza',
    'seguridad'       => '🚨 Seguridad',
    'transito'        => '🚦 Tránsito',
    'otros'           => '📌 Otros',
];

$estado_info = $etiquetas_estado[$reporte['estado']] ?? ['label' => ucfirst($reporte['estado']), 'class' => ''];
?>

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Detalle del Reporte</h1>
            <div class="user-info">
                <a href="mis_reportes.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="ver-reporte-grid">
            <!-- Card principal -->
            <div class="card">
                <div class="card-reporte-header">
                    <div>
                        <h2 class="reporte-titulo-grande"><?php echo htmlspecialchars($reporte['titulo']); ?></h2>
                        <div class="reporte-meta-row">
                            <span class="reporte-estado <?php echo $estado_info['class']; ?>">
                                <?php echo $estado_info['label']; ?>
                            </span>
                            <span class="meta-sep">·</span>
                            <span class="meta-text">
                                <i class="fas fa-tag"></i>
                                <?php echo $etiquetas_cat[$reporte['categoria']] ?? ucfirst($reporte['categoria']); ?>
                            </span>
                            <span class="meta-sep">·</span>
                            <span class="meta-text">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d \d\e F \d\e Y', strtotime($reporte['fecha_creacion'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="acciones-reporte">
                        <a href="editar_reporte.php?id=<?php echo $reporte['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="eliminar_reporte.php?id=<?php echo $reporte['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Eliminar este reporte? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </div>

                <div class="reporte-descripcion-bloque">
                    <h4>Descripción</h4>
                    <p><?php echo nl2br(htmlspecialchars($reporte['descripcion'])); ?></p>
                </div>

                <div class="reporte-info-grid">
                    <div class="info-bloque">
                        <span class="info-label"><i class="fas fa-map-pin"></i> Ubicación</span>
                        <span class="info-valor"><?php echo htmlspecialchars($reporte['ubicacion']); ?></span>
                    </div>
                    <?php if ($reporte['latitud'] && $reporte['longitud']): ?>
                    <div class="info-bloque">
                        <span class="info-label"><i class="fas fa-crosshairs"></i> Coordenadas</span>
                        <span class="info-valor"><?php echo $reporte['latitud']; ?>, <?php echo $reporte['longitud']; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-bloque">
                        <span class="info-label"><i class="fas fa-user"></i> Reportado por</span>
                        <span class="info-valor"><?php echo htmlspecialchars($reporte['autor_nombre']); ?></span>
                    </div>
                    <div class="info-bloque">
                        <span class="info-label"><i class="fas fa-clock"></i> Última actualización</span>
                        <span class="info-valor"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_actualizacion'])); ?></span>
                    </div>
                </div>

                <?php if ($reporte['imagen']): ?>
                <div class="reporte-imagen">
                    <h4>Imagen adjunta</h4>
                    <img src="../uploads/reportes/<?php echo htmlspecialchars($reporte['imagen']); ?>"
                         alt="Imagen del reporte"
                         style="max-width:100%; border-radius:8px; margin-top:10px;">
                </div>
                <?php endif; ?>
            </div>

            <!-- Mapa mini (si tiene coordenadas) -->
            <?php if ($reporte['latitud'] && $reporte['longitud']): ?>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
            <div class="card">
                <h4 style="margin-bottom:12px;"><i class="fas fa-map"></i> Ubicación en el mapa</h4>
                <div id="mapa-ver" style="height:280px; border-radius:8px; overflow:hidden;"></div>
            </div>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                const lat = <?php echo (float)$reporte['latitud']; ?>;
                const lng = <?php echo (float)$reporte['longitud']; ?>;
                const mapaVer = L.map('mapa-ver').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(mapaVer);
                L.marker([lat, lng]).addTo(mapaVer)
                    .bindPopup('<b><?php echo addslashes(htmlspecialchars($reporte['titulo'])); ?></b>')
                    .openPopup();
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
