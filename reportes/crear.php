<?php
$titulo_pagina = 'Crear Reporte - Ciudad Activa';
$css_adicional = '../css/reportes.css';
require_once '../includes/header.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="dashboard">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="reportes-header">
            <h2><i class="fas fa-plus-circle"></i> Crear Nuevo Reporte</h2>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-reporte">
            <form method="POST" action="guardar_reporte.php" enctype="multipart/form-data" id="form-reporte">

                <div class="form-group">
                    <label for="titulo">Título del Reporte *</label>
                    <input type="text" id="titulo" name="titulo" required
                           placeholder="Describe brevemente el problema">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción Detallada *</label>
                    <textarea id="descripcion" name="descripcion" required
                              placeholder="Proporciona más detalles sobre el problema"></textarea>
                </div>

                <div class="form-grupo-inline">
                    <div class="form-group">
                        <label for="categoria">Categoría *</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">-- Selecciona una categoría --</option>
                            <option value="infraestructura">🏗️ Infraestructura</option>
                            <option value="limpieza">🧹 Limpieza</option>
                            <option value="seguridad">🚨 Seguridad</option>
                            <option value="transito">🚦 Tránsito</option>
                            <option value="otros">📌 Otros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">Dirección / Referencia *</label>
                        <input type="text" id="ubicacion" name="ubicacion" required
                               placeholder="Ej: Calle 72 con Av. Caracas">
                    </div>
                </div>

                <!-- Mapa para seleccionar ubicación exacta -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-pin"></i>
                        Ubicación exacta en el mapa
                        <small style="font-weight:normal; color:#6b7280;">— Opcional: haz clic en el mapa para marcar el punto exacto</small>
                    </label>

                    <div class="mapa-selector-wrapper">
                        <div id="mapa-selector" class="mapa-selector"></div>
                        <div id="mapa-info" class="mapa-info oculto">
                            <i class="fas fa-check-circle" style="color:#10b981"></i>
                            Ubicación seleccionada: <span id="coords-texto"></span>
                        </div>
                    </div>

                    <!-- Campos ocultos con coordenadas -->
                    <input type="hidden" id="latitud"  name="latitud"  value="">
                    <input type="hidden" id="longitud" name="longitud" value="">
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen (Opcional)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small>Formatos permitidos: JPG, PNG, GIF. Máximo 5MB</small>
                </div>

                <div id="aviso-mapa" class="alert alert-warning" style="display:none;">
                    <i class="fas fa-map"></i>
                    No seleccionaste una ubicación en el mapa. El reporte se guardará sin coordenadas.
                </div>

                <div class="form-group form-acciones">
                    <button type="submit" class="btn btn-success" id="btn-enviar">
                        <i class="fas fa-paper-plane"></i> Enviar Reporte
                    </button>
                    <a href="mis_reportes.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
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
const mapaSelector = L.map('mapa-selector', {
    center: [4.7110, -74.0721],
    zoom: 13
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(mapaSelector);

let marcadorSeleccionado = null;

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

// Click en el mapa para colocar marcador
mapaSelector.on('click', function(e) {
    const { lat, lng } = e.latlng;

    // Remover marcador anterior
    if (marcadorSeleccionado) {
        mapaSelector.removeLayer(marcadorSeleccionado);
    }

    // Colocar nuevo marcador
    marcadorSeleccionado = L.marker([lat, lng], { icon: iconoSeleccion, draggable: true })
        .addTo(mapaSelector)
        .bindPopup('<b>📍 Ubicación seleccionada</b><br>Puedes arrastrarme para ajustar.')
        .openPopup();

    // Al arrastrar el marcador, actualizar coordenadas
    marcadorSeleccionado.on('dragend', function(event) {
        const pos = event.target.getLatLng();
        actualizarCoordenadas(pos.lat, pos.lng);
    });

    actualizarCoordenadas(lat, lng);
});

function actualizarCoordenadas(lat, lng) {
    document.getElementById('latitud').value  = lat.toFixed(7);
    document.getElementById('longitud').value = lng.toFixed(7);
    document.getElementById('coords-texto').textContent =
        lat.toFixed(5) + ', ' + lng.toFixed(5);
    document.getElementById('mapa-info').classList.remove('oculto');
}

// Mapa opcional: avisa pero no bloquea el envío
document.getElementById('form-reporte').addEventListener('submit', function() {
    const lat = document.getElementById('latitud').value;
    const lng = document.getElementById('longitud').value;
    if (!lat || !lng) {
        // Mostrar aviso suave (sin bloquear)
        const aviso = document.getElementById('aviso-mapa');
        if (aviso) aviso.style.display = 'flex';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
