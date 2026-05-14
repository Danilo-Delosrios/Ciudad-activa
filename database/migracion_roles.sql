-- 1. Añadir roles a usuarios
ALTER TABLE usuarios ADD COLUMN rol ENUM('usuario', 'funcionario', 'admin') DEFAULT 'usuario' NOT NULL;

-- 2. Actualizar el estado 'pendiente' a 'reportado' en los reportes existentes
-- Primero ampliamos el enum para que acepte el nuevo estado
ALTER TABLE reportes MODIFY COLUMN estado ENUM('pendiente', 'reportado', 'en_revision', 'en_proceso', 'resuelto', 'rechazado') DEFAULT 'reportado';

-- Luego actualizamos los datos existentes
UPDATE reportes SET estado = 'reportado' WHERE estado = 'pendiente';

-- Finalmente dejamos el ENUM limpio solo con los estados nuevos
ALTER TABLE reportes MODIFY COLUMN estado ENUM('reportado', 'en_revision', 'en_proceso', 'resuelto', 'rechazado') DEFAULT 'reportado';

-- 3. Crear tabla de comentarios privados
CREATE TABLE IF NOT EXISTS comentarios_privados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crear tabla de historial de cambios de estado
CREATE TABLE IF NOT EXISTS historial_cambios_estado (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    estado_anterior VARCHAR(50) NOT NULL,
    estado_nuevo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
