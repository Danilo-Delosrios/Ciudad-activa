<?php
/**
 * Archivo de conexión a la base de datos
 * Utiliza MySQLi con manejo de errores
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ciudad_activa');

// Crear conexión
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}

// Establecer el charset a utf8
$conexion->set_charset('utf8');

// Función para ejecutar consultas de forma segura
function ejecutarConsulta($conexion, $sql, $tipos = '', $parametros = array()) {
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }
    
    if ($stmt->execute()) {
        return $stmt;
    } else {
        return false;
    }
}

// Función para obtener los resultados como array
function obtenerResultados($stmt) {
    $resultado = $stmt->get_result();
    $datos = array();
    
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
    
    return $datos;
}

// Función para obtener una sola fila
function obtenerFila($stmt) {
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

?>
