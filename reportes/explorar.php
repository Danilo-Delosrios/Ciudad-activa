<?php
$titulo_pagina = 'Explorar Reportes - Ciudad Activa';
$css_adicional = '../css/reportes.css';
require_once '../includes/header.php';
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$filtro_ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';

// Obtener todas las ciudades para el filtro
$sql_ciudades = 'SELECT DISTINCT ciudad FROM reportes WHERE ciudad IS NOT NULL AND ciudad != "" ORDER BY ciudad';
$res_ciudades = $conexion->query($sql_ciudades);
$todas_ciudades = [];
if ($res_ciudades) {
    while ($row = $res_ciudades->fetch_assoc()) {
        $todas_ciudades[] = $row['ciudad'];
    }
}

// Obtener todos los reportes (con filtro si aplica)
$sql = 'SELECT r.id, r.titulo, r.descripcion, r.categoria, r.estado, r.ubicacion, r.ciudad, r.fecha_creacion, u.nombre AS autor_nombre
        FROM reportes r
        JOIN usuarios u ON r.usuario_id = u.id';

if ($filtro_ciudad !== '') {
    $sql .= ' WHERE r.ciudad = ?';
}

$sql .= ' ORDER BY r.fecha_creacion DESC';

$stmt = $conexion->prepare($sql);
if ($filtro_ciudad !== '') {
    $stmt->bind_param('s', $filtro_ciudad);
}
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
            <h1><i class="fas fa-search"></i> Explorar Reportes</h1>
            <p style="color: #64748b;">Mira lo que está pasando en tu ciudad y participa con tus comentarios.</p>
        </div>

        <!-- Filtros -->
        <div class="card" style="margin-bottom: 24px; padding: 15px 20px;">
            <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <label for="ciudad" style="font-size: 0.8rem; margin-bottom: 5px;">Filtrar por Ciudad</label>
                    <select name="ciudad" id="ciudad" style="width: 100%; padding: 8px;">
                        <option value="">Todas las ciudades</option>
                        <?php foreach ($todas_ciudades as $ciu): ?>
                            <option value="<?php echo htmlspecialchars($ciu); ?>" <?php echo $filtro_ciudad === $ciu ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ciu); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if ($filtro_ciudad !== ''): ?>
                    <a href="explorar.php" class="btn btn-secondary btn-sm" style="height: 38px; display: flex; align-items: center;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Lista de reportes -->
        <?php if (empty($reportes)): ?>
            <div class="card text-center" style="padding: 48px 24px;">
                <div style="font-size:3rem; margin-bottom:16px;">🌍</div>
                <h3 style="color:#64748b; font-weight:500;">No hay reportes públicos aún</h3>
                <p style="color:#94a3b8; margin-bottom:20px;">Sé el primero en informar sobre un problema en tu comunidad.</p>
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Crear el primer reporte
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
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($reporte['autor_nombre']); ?></span>
                                    <span><i class="fas fa-tag"></i> <?php echo $etiquetas_cat[$reporte['categoria']] ?? ucfirst($reporte['categoria']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($reporte['ciudad']); ?></span>
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
                                <i class="fas fa-eye"></i> Ver Reporte y Comentarios
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
