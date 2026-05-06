<?php
/**
 * Header reutilizable para todas las páginas
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) : 'Ciudad Activa'; ?></title>
    <meta name="description" content="Ciudad Activa — Plataforma ciudadana para reportar y gestionar problemas urbanos">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS Global -->
    <link rel="stylesheet" href="<?php echo isset($ruta_css) ? $ruta_css : ''; ?>../css/estilos.css">

    <!-- CSS Específico -->
    <?php if (isset($css_adicional)): ?>
        <link rel="stylesheet" href="<?php echo $css_adicional; ?>">
    <?php endif; ?>

    <!-- CSS adicional extra (para páginas con Leaflet, etc.) -->
    <?php if (isset($css_extra)): ?>
        <?php foreach ((array) $css_extra as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>