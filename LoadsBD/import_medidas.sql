USE `ser_inventario`;
SET NAMES utf8mb4;

INSERT INTO `medidainsumo` (`ID`, `Descripcion`, `Activo`) VALUES
('ML', 'Mililitros', 1),
('PZA', 'Pieza', 1),
('TABLETA', 'Tableta', 1),
('LT', 'Litro', 1),
('CM', 'Centímetro', 1),
('AMPOLLETA', 'Ampolleta', 1),
('FCO', 'Frasco', 1),
('PQT', 'Paquete', 1),
('GR', 'Gramos', 1),
('PAR', 'Par', 1),
('CJ100', 'Caja 100 piezas', 1),
('BOLSA', 'Bolsa', 1),
('BOTELLA', 'Botella', 1),
('CAJA', 'Caja', 1),
('ROLLO', 'Rollo', 1),
('KG', 'Kilogramos', 1),
('PQT100', 'Paquete 100 piezas', 1)
ON DUPLICATE KEY UPDATE 
    `Descripcion` = VALUES(`Descripcion`), 
    `Activo` = VALUES(`Activo`);
