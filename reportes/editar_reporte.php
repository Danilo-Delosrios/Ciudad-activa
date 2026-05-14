<?php
$titulo_pagina = 'Editar Reporte - Ciudad Activa';
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
$stmt = $conexion->prepare('SELECT * FROM reportes WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('ii', $id, $_SESSION['usuario_id']);
$stmt->execute();
$reporte = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reporte) {
    $conexion->close();
    header('Location: mis_reportes.php?error=' . urlencode('Reporte no encontrado'));
    exit();
}

// Procesar actualización
$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['titulo']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria   = trim($_POST['categoria']   ?? '');
    $ubicacion   = trim($_POST['ubicacion']   ?? '');
    $ciudad      = trim($_POST['ciudad']      ?? '');
    $latitud     = $_POST['latitud'] ?? null;
    $longitud    = $_POST['longitud'] ?? null;

    if ($latitud === '') $latitud = null;
    if ($longitud === '') $longitud = null;

    if (empty($titulo) || empty($descripcion) || empty($categoria) || empty($ubicacion) || empty($ciudad)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } else {
        $nombre_imagen = $reporte['imagen'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $tipos_permitidos)) {
                    $directorio = '../uploads/reportes/';
                    if (!is_dir($directorio)) {
                        mkdir($directorio, 0755, true);
                    }
                    
                    $nuevo_nombre = uniqid('rep_', true) . '.' . $extension;
                    $ruta_destino = $directorio . $nuevo_nombre;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                        if ($reporte['imagen'] && file_exists('../uploads/reportes/' . $reporte['imagen'])) {
                            unlink('../uploads/reportes/' . $reporte['imagen']);
                        }
                        $nombre_imagen = $nuevo_nombre;
                    } else {
                        $error = 'Error al guardar la imagen en el servidor.';
                    }
                } else {
                    $error = 'Formato de imagen no permitido.';
                }
            } else {
                $error = 'Ocurrió un error en la subida de la imagen.';
            }
        }

        if (!$error) {
            $stmt = $conexion->prepare(
                'UPDATE reportes SET titulo=?, descripcion=?, categoria=?, ubicacion=?, ciudad=?, latitud=?, longitud=?, imagen=?, fecha_actualizacion=NOW()
                 WHERE id=? AND usuario_id=?'
            );
            $stmt->bind_param('ssssssssii', $titulo, $descripcion, $categoria, $ubicacion, $ciudad, $latitud, $longitud, $nombre_imagen, $id, $_SESSION['usuario_id']);

            if ($stmt->execute()) {
                $reporte['titulo']      = $titulo;
                $reporte['descripcion'] = $descripcion;
                $reporte['categoria']   = $categoria;
                $reporte['ubicacion']   = $ubicacion;
                $reporte['ciudad']      = $ciudad;
                $reporte['latitud']     = $latitud;
                $reporte['longitud']    = $longitud;
                $reporte['imagen']      = $nombre_imagen;
                $success = 'Reporte actualizado correctamente.';
            } else {
                $error = 'Error al actualizar el reporte: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conexion->close();
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Editar Reporte</h1>
            <a href="ver_reporte.php?id=<?php echo $id; ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="form-reporte">
            <form method="POST" id="form-editar" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="titulo">Título del Reporte *</label>
                    <input type="text" id="titulo" name="titulo" required
                           value="<?php echo htmlspecialchars($reporte['titulo']); ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción Detallada *</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($reporte['descripcion']); ?></textarea>
                </div>

                <div class="form-grupo-inline">
                    <div class="form-group">
                        <label for="categoria">Categoría *</label>
                        <select id="categoria" name="categoria" required>
                            <?php
                            $categorias = [
                                'infraestructura' => '🏗️ Infraestructura',
                                'limpieza'        => '🧹 Limpieza',
                                'seguridad'       => '🚨 Seguridad',
                                'transito'        => '🚦 Tránsito',
                                'otros'           => '📌 Otros',
                            ];
                            foreach ($categorias as $val => $label):
                                $sel = $reporte['categoria'] === $val ? 'selected' : '';
                            ?>
                                <option value="<?php echo $val; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">Dirección / Referencia *</label>
                        <input type="text" id="ubicacion" name="ubicacion" required
                               value="<?php echo htmlspecialchars($reporte['ubicacion']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="ciudad">Ciudad *</label>
                        <select id="ciudad" name="ciudad" required>
                            <?php
                            $ciudades = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena', 'Bucaramanga', 'Pereira', 'Santa Marta', 'Otras'];
                            foreach ($ciudades as $ciu):
                                $sel = $reporte['ciudad'] === $ciu ? 'selected' : '';
                            ?>
                                <option value="<?php echo $ciu; ?>" <?php echo $sel; ?>><?php echo $ciu; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Mapa para seleccionar ubicación exacta -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-pin"></i>
                        Ubicación exacta en el mapa
                        <small style="font-weight:normal; color:#6b7280;">— Opcional: haz clic en el mapa para ajustar el punto</small>
                    </label>

                    <div class="mapa-selector-wrapper">
                        <div id="mapa-selector" class="mapa-selector" style="height: 300px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 10px;"></div>
                        <div id="mapa-info" class="mapa-info <?php echo ($reporte['latitud'] && $reporte['longitud']) ? '' : 'oculto'; ?>">
                            <i class="fas fa-check-circle" style="color:#10b981"></i>
                            Ubicación seleccionada: <span id="coords-texto"><?php echo $reporte['latitud'] ? $reporte['latitud'].', '.$reporte['longitud'] : ''; ?></span>
                        </div>
                    </div>

                    <!-- Campos ocultos con coordenadas -->
                    <input type="hidden" id="latitud"  name="latitud"  value="<?php echo htmlspecialchars($reporte['latitud'] ?? ''); ?>">
                    <input type="hidden" id="longitud" name="longitud" value="<?php echo htmlspecialchars($reporte['longitud'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen Adjunta</label>
                    <?php if ($reporte['imagen']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../uploads/reportes/<?php echo htmlspecialchars($reporte['imagen']); ?>" style="max-height: 100px; border-radius: 8px;">
                            <br><small>Imagen actual</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small>Sube una nueva foto para reemplazar la actual. Formatos permitidos: JPG, PNG, GIF. Máximo 5MB</small>
                </div>

                <div class="form-group form-acciones">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="ver_reporte.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a href="eliminar_reporte.php?id=<?php echo $id; ?>"
                       class="btn btn-danger"
                       onclick="return confirm('¿Eliminar este reporte? Esta acción no se puede deshacer.')">
                        <i class="fas fa-trash"></i> Eliminar Reporte
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ── Mapa selector de ubicación ──────────────────────────────────────────────
let startLat = <?php echo $reporte['latitud'] ? $reporte['latitud'] : '4.5709'; ?>;
let startLng = <?php echo $reporte['longitud'] ? $reporte['longitud'] : '-74.2973'; ?>;
let defaultZoom = <?php echo ($reporte['latitud'] && $reporte['longitud']) ? '15' : '6'; ?>;

const mapaSelector = L.map('mapa-selector').setView([startLat, startLng], defaultZoom);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(mapaSelector);

// Icono del marcador de selección
const iconoSeleccion = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="44" viewBox="0 0 32 40">
               <path d="M16 0C7.164 0 0 7.163 0 16c0 9.895 14.059 22.88 15.281 23.97a1 1 0 0 0 1.438 0C17.941 38.88 32 25.895 32 16 32 7.163 24.836 0 16 0z"
                     fill="#0066cc" stroke="white" stroke-width="1.5"/>
               <circle cx="16" cy="16" r="7" fill="white" opacity="0.95"/>
               <circle cx="16" cy="16" r="4" fill="#0066cc"/>
           </svg>`,
    iconSize: [36, 44],
    iconAnchor: [18, 44],
    popupAnchor: [0, -46],
    className: ''
});

let marcadorSeleccionado = null;

// Si ya había coordenadas, colocar marcador
<?php if ($reporte['latitud'] && $reporte['longitud']): ?>
marcadorSeleccionado = L.marker([startLat, startLng], { icon: iconoSeleccion, draggable: true })
    .addTo(mapaSelector)
    .bindPopup('<b>📍 Ubicación actual</b><br>Puedes arrastrarme para ajustar.')
    .openPopup();

marcadorSeleccionado.on('dragend', function(event) {
    const pos = event.target.getLatLng();
    actualizarCoordenadas(pos.lat, pos.lng);
});
<?php endif; ?>

// Click en el mapa para colocar marcador
mapaSelector.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    if (marcadorSeleccionado) {
        mapaSelector.removeLayer(marcadorSeleccionado);
    }

    marcadorSeleccionado = L.marker([lat, lng], { icon: iconoSeleccion, draggable: true })
        .addTo(mapaSelector)
        .bindPopup('<b>📍 Ubicación seleccionada</b><br>Puedes arrastrarme para ajustar.')
        .openPopup();

    marcadorSeleccionado.on('dragend', function(event) {
        const pos = event.target.getLatLng();
        actualizarCoordenadas(pos.lat, pos.lng);
    });

    actualizarCoordenadas(lat, lng);
});

function actualizarCoordenadas(lat, lng) {
    document.getElementById('latitud').value  = lat.toFixed(7);
    document.getElementById('longitud').value = lng.toFixed(7);
    document.getElementById('coords-texto').textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
    document.getElementById('mapa-info').classList.remove('oculto');
}
</script>

<?php require_once '../includes/footer.php'; ?>
