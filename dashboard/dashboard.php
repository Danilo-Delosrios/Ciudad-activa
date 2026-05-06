<?php
$titulo_pagina = 'Dashboard - Ciudad Activa';
$css_adicional = '../css/dashboard.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Obtener estadísticas
$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ?');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$total_reportes = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ? AND estado = "resuelto"');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$reportes_resueltos = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conexion->prepare('SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ? AND estado = "pendiente"');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$reportes_pendientes = $stmt->get_result()->fetch_assoc()['total'];

// Obtener todos los reportes con coordenadas para el mapa
$stmt = $conexion->prepare(
    'SELECT r.id, r.titulo, r.descripcion, r.categoria, r.estado, r.ubicacion,
            r.latitud, r.longitud, r.fecha_creacion, u.nombre AS autor
     FROM reportes r
     JOIN usuarios u ON r.usuario_id = u.id
     WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL
     ORDER BY r.fecha_creacion DESC
     LIMIT 100'
);
$stmt->execute();
$reportes_mapa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener reportes recientes del usuario
$stmt = $conexion->prepare(
    'SELECT id, titulo, estado, fecha_creacion FROM reportes
     WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 5'
);
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$reportes_recientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-city"></i> Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?></div>
                <span><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card stat-total">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <h3>Mis Reportes</h3>
                <div class="stat-number"><?php echo $total_reportes; ?></div>
            </div>
            <div class="stat-card stat-resuelto">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <h3>Resueltos</h3>
                <div class="stat-number"><?php echo $reportes_resueltos; ?></div>
            </div>
            <div class="stat-card stat-pendiente">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <h3>Pendientes</h3>
                <div class="stat-number"><?php echo $reportes_pendientes; ?></div>
            </div>
        </div>

        <!-- Mapa Interactivo -->
        <div class="map-section">
            <div class="map-section-header">
                <h2><i class="fas fa-map-marked-alt"></i> Mapa de Reportes Ciudadanos</h2>
                <div class="map-legend">
                    <span class="legend-item"><span class="legend-dot dot-pendiente"></span> Pendiente</span>
                    <span class="legend-item"><span class="legend-dot dot-en_proceso"></span> En proceso</span>
                    <span class="legend-item"><span class="legend-dot dot-resuelto"></span> Resuelto</span>
                    <span class="legend-item"><span class="legend-dot dot-rechazado"></span> Rechazado</span>
                </div>
            </div>
            <div id="mapa-dashboard" class="leaflet-map"></div>
        </div>

        <!-- Reportes Recientes -->
        <div class="recent-reports">
            <h3><i class="fas fa-history"></i> Mis Reportes Recientes</h3>
            <?php if (!empty($reportes_recientes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportes_recientes as $reporte): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reporte['titulo']); ?></td>
                                <td>
                                    <span class="reporte-estado estado-<?php echo $reporte['estado']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $reporte['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($reporte['fecha_creacion'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No tienes reportes aún. <a href="../reportes/crear.php">¡Crea uno ahora!</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Datos de reportes desde PHP
const reportesMapa = <?php echo json_encode($reportes_mapa, JSON_UNESCAPED_UNICODE); ?>;

// Inicializar mapa centrado en Colombia (ajusta según tu ciudad)
const map = L.map('mapa-dashboard', {
    center: [4.7110, -74.0721],
    zoom: 12,
    zoomControl: true
});

// Capa de mapa (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Colores por estado
const coloresEstado = {
    pendiente:  '#f59e0b',
    en_proceso: '#3b82f6',
    resuelto:   '#10b981',
    rechazado:  '#ef4444'
};

// Iconos SVG personalizados por estado
function crearIcono(estado) {
    const color = coloresEstado[estado] || '#6b7280';
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
            <path d="M16 0C7.164 0 0 7.163 0 16c0 9.895 14.059 22.88 15.281 23.97a1 1 0 0 0 1.438 0C17.941 38.88 32 25.895 32 16 32 7.163 24.836 0 16 0z"
                  fill="${color}" stroke="white" stroke-width="1.5"/>
            <circle cx="16" cy="16" r="6" fill="white" opacity="0.9"/>
        </svg>`;
    return L.divIcon({
        html: svg,
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        popupAnchor: [0, -42],
        className: ''
    });
}

// Categorías con emojis
const emojiCategoria = {
    infraestructura: '🏗️',
    limpieza:        '🧹',
    seguridad:       '🚨',
    transito:        '🚦',
    otros:           '📌'
};

// Agregar marcadores al mapa
if (reportesMapa.length > 0) {
    const bounds = [];

    reportesMapa.forEach(function(r) {
        const lat = parseFloat(r.latitud);
        const lng = parseFloat(r.longitud);
        bounds.push([lat, lng]);

        const emoji = emojiCategoria[r.categoria] || '📌';
        const fecha = new Date(r.fecha_creacion).toLocaleDateString('es-CO', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        const popupHTML = `
            <div class="popup-reporte">
                <div class="popup-header popup-${r.estado}">
                    ${emoji} ${r.categoria.charAt(0).toUpperCase() + r.categoria.slice(1)}
                </div>
                <h4>${r.titulo}</h4>
                <p class="popup-desc">${r.descripcion.substring(0, 120)}${r.descripcion.length > 120 ? '…' : ''}</p>
                <div class="popup-meta">
                    <span class="popup-estado popup-badge-${r.estado}">${r.estado.replace('_', ' ')}</span>
                    <span class="popup-fecha">${fecha}</span>
                </div>
                <p class="popup-autor"><i>👤 ${r.autor}</i></p>
            </div>`;

        L.marker([lat, lng], { icon: crearIcono(r.estado) })
            .addTo(map)
            .bindPopup(popupHTML, { maxWidth: 280 });
    });

    // Ajustar el mapa para mostrar todos los marcadores
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
} else {
    // Sin reportes: mostrar mensaje centrado en el mapa
    const noReportesControl = L.control({ position: 'topright' });
    noReportesControl.onAdd = function() {
        const div = L.DomUtil.create('div', 'map-no-data');
        div.innerHTML = '📍 Aún no hay reportes con ubicación en el mapa';
        return div;
    };
    noReportesControl.addTo(map);
}
</script>

<?php require_once '../includes/footer.php'; ?>
