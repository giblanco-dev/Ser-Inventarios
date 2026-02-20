-- ============================================================
-- SER Inventarios — Esquema de Base de Datos v1.0
-- Consultorio de Medicina Alternativa Ser S.C.
-- Motor: MySQL 8.0 / InnoDB
-- Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ser_inventario`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ser_inventario`;

-- ------------------------------------------------------------
-- 1. Catálogo: Unidades de Medida
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `medidainsumo` (
    `ID`          VARCHAR(10)   NOT NULL,
    `Descripcion` VARCHAR(100)  NOT NULL,
    `Activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `CreatedAt`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Catálogo: Tipos de Insumo
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipoinsumo` (
    `ID`          VARCHAR(10)   NOT NULL,
    `Descripcion` VARCHAR(100)  NOT NULL,
    `Activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `CreatedAt`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Catálogo: Conceptos de Movimiento
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conceptomovimiento` (
    `ID`             VARCHAR(15)                    NOT NULL,
    `Descripcion`    VARCHAR(100)                   NOT NULL,
    `TipoOperacion`  ENUM('ENTRADA','SALIDA')       NOT NULL,
    `Activo`         TINYINT(1)                     NOT NULL DEFAULT 1,
    `CreatedAt`      DATETIME                       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`      DATETIME                       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Tabla principal: Insumos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `insumos` (
    `ID`                INT             NOT NULL AUTO_INCREMENT,
    `Nombre`            VARCHAR(255)    NOT NULL,
    `TipoProducto`      VARCHAR(10)     NOT NULL,
    `MedidaInsumo`      VARCHAR(10)     NOT NULL,
    `Existencia`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `Minimos`           INT             NOT NULL DEFAULT 0,
    `Maximos`           INT             NOT NULL DEFAULT 0,
    `Caducidad`         TINYINT(1)      NOT NULL DEFAULT 0,
    `DiasPreviosAviso`  INT             NULL DEFAULT NULL,
    `Activo`            TINYINT(1)      NOT NULL DEFAULT 1,
    `CreatedAt`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX `idx_insumos_tipo` (`TipoProducto`),
    INDEX `idx_insumos_medida` (`MedidaInsumo`),
    INDEX `idx_insumos_activo` (`Activo`),
    CONSTRAINT `fk_insumos_tipo`
        FOREIGN KEY (`TipoProducto`) REFERENCES `tipoinsumo`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_insumos_medida`
        FOREIGN KEY (`MedidaInsumo`) REFERENCES `medidainsumo`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Códigos de barra por insumo
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `insumocodigobarra` (
    `ID`           INT            NOT NULL AUTO_INCREMENT,
    `InsumoID`     INT            NOT NULL,
    `CodigoBarra`  VARCHAR(100)   NOT NULL,
    `TipoCodigo`   ENUM('EAN13','EAN8','UPC_A','CODE128','CODE39','QR','INTERNO') NOT NULL DEFAULT 'INTERNO',
    `Descripcion`  VARCHAR(255)   NULL DEFAULT NULL,
    `Activo`       TINYINT(1)     NOT NULL DEFAULT 1,
    `CreatedAt`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_codigo_barra` (`CodigoBarra`),
    INDEX `idx_codigo_insumo` (`InsumoID`),
    CONSTRAINT `fk_codigo_insumo`
        FOREIGN KEY (`InsumoID`) REFERENCES `insumos`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Cabecera de movimientos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimientos` (
    `ID`              INT            NOT NULL AUTO_INCREMENT,
    `ConceptoID`      VARCHAR(15)    NOT NULL,
    `FechaMovimiento` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Referencia`      VARCHAR(100)   NULL DEFAULT NULL,
    `Observaciones`   TEXT           NULL,
    `UsuarioRegistra` VARCHAR(100)   NULL DEFAULT NULL,
    `CreatedAt`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX `idx_mov_concepto` (`ConceptoID`),
    INDEX `idx_mov_fecha` (`FechaMovimiento`),
    CONSTRAINT `fk_mov_concepto`
        FOREIGN KEY (`ConceptoID`) REFERENCES `conceptomovimiento`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. Detalle de movimientos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimientodetalle` (
    `ID`               INT            NOT NULL AUTO_INCREMENT,
    `MovimientoID`     INT            NOT NULL,
    `InsumoID`         INT            NOT NULL,
    `Cantidad`         DECIMAL(12,2)  NOT NULL,
    `NumeroLote`       VARCHAR(50)    NULL DEFAULT NULL,
    `FechaVencimiento` DATE           NULL DEFAULT NULL,
    `CreatedAt`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX `idx_detalle_mov` (`MovimientoID`),
    INDEX `idx_detalle_insumo` (`InsumoID`),
    INDEX `idx_detalle_vencimiento` (`FechaVencimiento`),
    CONSTRAINT `fk_detalle_movimiento`
        FOREIGN KEY (`MovimientoID`) REFERENCES `movimientos`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_detalle_insumo`
        FOREIGN KEY (`InsumoID`) REFERENCES `insumos`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VISTAS
-- ============================================================

-- Vista: Stock actual con semáforo
CREATE OR REPLACE VIEW `v_stock_actual` AS
SELECT
    i.`ID`,
    i.`Nombre`,
    t.`Descripcion`  AS TipoDescripcion,
    t.`ID`           AS TipoID,
    m.`Descripcion`  AS MedidaDescripcion,
    m.`ID`           AS MedidaID,
    i.`Existencia`,
    i.`Minimos`,
    i.`Maximos`,
    i.`Caducidad`,
    i.`Activo`,
    CASE
        WHEN i.`Existencia` <= 0              THEN 'CRITICO'
        WHEN i.`Existencia` <= i.`Minimos`    THEN 'BAJO'
        ELSE 'OK'
    END AS EstadoStock
FROM `insumos` i
INNER JOIN `tipoinsumo` t   ON i.`TipoProducto` = t.`ID`
INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
WHERE i.`Activo` = 1;

-- Vista: Alertas de vencimiento
CREATE OR REPLACE VIEW `v_alertas_vencimiento` AS
SELECT
    i.`ID`              AS InsumoID,
    i.`Nombre`,
    md.`NumeroLote`,
    md.`FechaVencimiento`,
    i.`DiasPreviosAviso`,
    DATEDIFF(md.`FechaVencimiento`, CURDATE()) AS DiasRestantes
FROM `movimientodetalle` md
INNER JOIN `insumos` i ON md.`InsumoID` = i.`ID`
WHERE i.`Caducidad` = 1
  AND i.`Activo` = 1
  AND md.`FechaVencimiento` IS NOT NULL
  AND DATEDIFF(md.`FechaVencimiento`, CURDATE()) <= i.`DiasPreviosAviso`
  AND md.`FechaVencimiento` >= CURDATE()
ORDER BY DiasRestantes ASC;

-- Vista: Historial completo de movimientos
CREATE OR REPLACE VIEW `v_historial_movimientos` AS
SELECT
    m.`ID`               AS MovimientoID,
    m.`FechaMovimiento`,
    cm.`ID`              AS ConceptoID,
    cm.`Descripcion`     AS ConceptoDescripcion,
    cm.`TipoOperacion`,
    m.`Referencia`,
    m.`Observaciones`,
    m.`UsuarioRegistra`,
    md.`ID`              AS DetalleID,
    md.`InsumoID`,
    i.`Nombre`           AS InsumoNombre,
    md.`Cantidad`,
    md.`NumeroLote`,
    md.`FechaVencimiento`
FROM `movimientos` m
INNER JOIN `conceptomovimiento` cm ON m.`ConceptoID` = cm.`ID`
INNER JOIN `movimientodetalle` md  ON md.`MovimientoID` = m.`ID`
INNER JOIN `insumos` i             ON md.`InsumoID` = i.`ID`
ORDER BY m.`FechaMovimiento` DESC, m.`ID` DESC;

-- Vista: Búsqueda por código de barra
CREATE OR REPLACE VIEW `v_busqueda_codigo_barra` AS
SELECT
    cb.`CodigoBarra`,
    cb.`TipoCodigo`,
    cb.`Descripcion`  AS CodigoDescripcion,
    i.`ID`            AS InsumoID,
    i.`Nombre`        AS InsumoNombre,
    i.`Existencia`,
    i.`Activo`        AS InsumoActivo,
    cb.`Activo`       AS CodigoActivo
FROM `insumocodigobarra` cb
INNER JOIN `insumos` i ON cb.`InsumoID` = i.`ID`;

-- ============================================================
-- DATOS SEMILLA (SEED DATA)
-- ============================================================

-- Unidades de medida
INSERT INTO `medidainsumo` (`ID`, `Descripcion`) VALUES
('PZA', 'Pieza'),
('ML',  'Mililitro'),
('LT',  'Litro'),
('GR',  'Gramo'),
('KG',  'Kilogramo'),
('CJA', 'Caja'),
('PAQ', 'Paquete'),
('ROL', 'Rollo'),
('FRC', 'Frasco'),
('TBO', 'Tubo')
ON DUPLICATE KEY UPDATE `Descripcion` = VALUES(`Descripcion`);

-- Tipos de insumo
INSERT INTO `tipoinsumo` (`ID`, `Descripcion`) VALUES
('INTE', 'Insumo Interno / Clínico'),
('VTA',  'Producto para Venta'),
('PAP',  'Papelería y Oficina'),
('LIM',  'Limpieza e Higiene'),
('FORM', 'Fórmula / Preparado')
ON DUPLICATE KEY UPDATE `Descripcion` = VALUES(`Descripcion`);

-- Conceptos de movimiento
INSERT INTO `conceptomovimiento` (`ID`, `Descripcion`, `TipoOperacion`) VALUES
('COMPRA',      'Compra a proveedor',         'ENTRADA'),
('DONACION_ENT','Donación recibida',           'ENTRADA'),
('AJUSTE_ENT',  'Ajuste de inventario (+)',    'ENTRADA'),
('DEV_CLIENTE', 'Devolución de cliente',       'ENTRADA'),
('VENTA',       'Venta a paciente',            'SALIDA'),
('USO_INTERNO', 'Uso interno en consultorio',  'SALIDA'),
('MERMA',       'Merma / Caducidad',           'SALIDA'),
('AJUSTE_SAL',  'Ajuste de inventario (-)',    'SALIDA'),
('DEV_PROV',    'Devolución a proveedor',      'SALIDA')
ON DUPLICATE KEY UPDATE `Descripcion` = VALUES(`Descripcion`);
