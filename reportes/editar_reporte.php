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
    $estado      = trim($_POST['estado']      ?? '');

    if (empty($titulo) || empty($descripcion) || empty($categoria) || empty($ubicacion) || empty($ciudad) || empty($estado)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } else {
        $stmt = $conexion->prepare(
            'UPDATE reportes SET titulo=?, descripcion=?, categoria=?, ubicacion=?, ciudad=?, estado=?, fecha_actualizacion=NOW()
             WHERE id=? AND usuario_id=?'
        );
        $stmt->bind_param('ssssssii', $titulo, $descripcion, $categoria, $ubicacion, $ciudad, $estado, $id, $_SESSION['usuario_id']);

        if ($stmt->execute()) {
            $reporte['titulo']      = $titulo;
            $reporte['descripcion'] = $descripcion;
            $reporte['categoria']   = $categoria;
            $reporte['ubicacion']   = $ubicacion;
            $reporte['ciudad']      = $ciudad;
            $reporte['estado']      = $estado;
            $success = 'Reporte actualizado correctamente.';
        } else {
            $error = 'Error al actualizar el reporte: ' . $stmt->error;
        }
        $stmt->close();
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
            <form method="POST" id="form-editar">

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

                <div class="form-group">
                    <label for="estado">Estado del Reporte *</label>
                    <select id="estado" name="estado" required>
                        <?php
                        $estados = [
                            'pendiente'  => '⏳ Pendiente',
                            'en_proceso' => '⚙️ En Proceso',
                            'resuelto'   => '✅ Resuelto',
                            'rechazado'  => '❌ Rechazado',
                        ];
                        foreach ($estados as $val => $label):
                            $sel = $reporte['estado'] === $val ? 'selected' : '';
                        ?>
                            <option value="<?php echo $val; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
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

<?php require_once '../includes/footer.php'; ?>
