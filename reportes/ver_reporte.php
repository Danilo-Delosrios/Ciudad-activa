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

// Obtener el reporte (cualquier usuario puede ver cualquier reporte)
$stmt = $conexion->prepare(
    'SELECT r.*, u.nombre AS autor_nombre
     FROM reportes r
     JOIN usuarios u ON r.usuario_id = u.id
     WHERE r.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$reporte = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reporte) {
    header('Location: mis_reportes.php?error=' . urlencode('Reporte no encontrado'));
    exit();
}

// Obtener comentarios
$stmt = $conexion->prepare(
    'SELECT c.*, u.nombre AS usuario_nombre
     FROM comentarios c
     JOIN usuarios u ON c.usuario_id = u.id
     WHERE c.reporte_id = ?
     ORDER BY c.fecha_creacion ASC'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

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
                        <?php if ($reporte['usuario_id'] == $_SESSION['usuario_id']): ?>
                            <a href="editar_reporte.php?id=<?php echo $reporte['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eliminar_reporte.php?id=<?php echo $reporte['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar este reporte? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="reporte-descripcion-bloque">
                    <h4>Descripción</h4>
                    <p><?php echo nl2br(htmlspecialchars($reporte['descripcion'])); ?></p>
                </div>

                <div class="reporte-info-grid">
                    <div class="info-bloque">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Ciudad</span>
                        <span class="info-valor"><?php echo htmlspecialchars($reporte['ciudad']); ?></span>
                    </div>
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

                <!-- Gestión de Estado (Solo dueño) -->
                <?php if ($reporte['usuario_id'] == $_SESSION['usuario_id']): ?>
                <div class="reporte-gestion-estado" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                    <h4 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 15px;">
                        Cambiar Estado Rápidamente
                    </h4>
                    <div class="estado-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php
                        $opciones_estado = [
                            'pendiente'  => ['label' => 'Pendiente',   'class' => 'btn-warning', 'icon' => 'fa-clock'],
                            'en_proceso' => ['label' => 'En Proceso',  'class' => 'btn-info',    'icon' => 'fa-spinner'],
                            'resuelto'   => ['label' => 'Resuelto',    'class' => 'btn-success', 'icon' => 'fa-check'],
                            'rechazado'  => ['label' => 'Rechazado',   'class' => 'btn-danger',  'icon' => 'fa-times'],
                        ];

                        foreach ($opciones_estado as $slug => $data):
                            $is_current = ($reporte['estado'] === $slug);
                        ?>
                            <form action="actualizar_estado.php" method="POST" style="display: inline;">
                                <input type="hidden" name="reporte_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="nuevo_estado" value="<?php echo $slug; ?>">
                                <button type="submit" class="btn <?php echo $data['class']; ?> btn-sm" 
                                        <?php echo $is_current ? 'disabled' : ''; ?>
                                        style="<?php echo $is_current ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                                    <i class="fas <?php echo $data['icon']; ?>"></i> <?php echo $data['label']; ?>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sección de Comentarios -->
                <div class="reporte-comentarios" style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #f1f5f9;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 20px;"><i class="fas fa-comments"></i> Comentarios (<?php echo count($comentarios); ?>)</h3>
                    
                    <div class="lista-comentarios" style="margin-bottom: 25px;">
                        <?php if (empty($comentarios)): ?>
                            <p style="color: #94a3b8; font-style: italic;">No hay comentarios todavía. ¡Sé el primero en comentar!</p>
                        <?php else: ?>
                            <?php foreach ($comentarios as $comentario): ?>
                                <div class="comentario-item" style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid #cbd5e1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($comentario['usuario_nombre']); ?>
                                        </span>
                                        <span style="font-size: 0.75rem; color: #94a3b8;">
                                            <?php echo date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])); ?>
                                        </span>
                                    </div>
                                    <p style="font-size: 0.88rem; color: #475569; margin: 0; line-height: 1.5;">
                                        <?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="nuevo-comentario">
                        <form action="guardar_comentario.php" method="POST">
                            <input type="hidden" name="reporte_id" value="<?php echo $id; ?>">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <textarea name="contenido" rows="3" placeholder="Escribe un comentario..." required 
                                          style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; resize: vertical; font-family: inherit; font-size: 0.9rem;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Publicar Comentario
                            </button>
                        </form>
                    </div>
                </div>
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
