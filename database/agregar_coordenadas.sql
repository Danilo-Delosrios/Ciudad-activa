-- Migración: Agregar coordenadas geográficas a la tabla reportes
USE ciudad_activa;

ALTER TABLE reportes
    ADD COLUMN latitud DECIMAL(10, 8) NULL AFTER ubicacion,
    ADD COLUMN longitud DECIMAL(11, 8) NULL AFTER latitud;

-- Índice para búsquedas geoespaciales
CREATE INDEX idx_reportes_coords ON reportes(latitud, longitud);
