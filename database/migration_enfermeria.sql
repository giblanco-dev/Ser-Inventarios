-- ============================================================
-- SER Inventarios — Migración: Sub-Inventario de Enfermería
-- Motor: MySQL 8.0 / InnoDB
-- Charset: utf8mb4
-- ============================================================

USE `ser_inventario`;

-- ------------------------------------------------------------
-- 1. Stock actual de enfermería por insumo
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `insumos_enfermeria` (
    `ID`          INT            NOT NULL AUTO_INCREMENT,
    `InsumoID`    INT            NOT NULL,
    `Existencia`  DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    `Activo`      TINYINT(1)     NOT NULL DEFAULT 1,
    `CreatedAt`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_insumo_enfermeria` (`InsumoID`),
    CONSTRAINT `fk_enfermeria_insumo`
        FOREIGN KEY (`InsumoID`) REFERENCES `insumos`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Cabecera de entregas / devoluciones
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `entregas_enfermeria` (
    `ID`             INT                               NOT NULL AUTO_INCREMENT,
    `TipoOperacion`  ENUM('ENTREGA','DEVOLUCION')      NOT NULL,
    `FechaEntrega`   DATETIME                          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `EntregadoPor`   VARCHAR(100)                      NOT NULL,
    `RecibidoPor`    VARCHAR(100)                      NOT NULL,
    `Observaciones`  TEXT                              NULL,
    `CreatedAt`      DATETIME                          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX `idx_entrega_fecha` (`FechaEntrega`),
    INDEX `idx_entrega_tipo` (`TipoOperacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Detalle por entrega / devolución
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `entregas_enfermeria_detalle` (
    `ID`          INT            NOT NULL AUTO_INCREMENT,
    `EntregaID`   INT            NOT NULL,
    `InsumoID`    INT            NOT NULL,
    `Cantidad`    DECIMAL(12,2)  NOT NULL,
    `CreatedAt`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX `idx_detalle_entrega` (`EntregaID`),
    INDEX `idx_detalle_insumo`  (`InsumoID`),
    CONSTRAINT `fk_detalle_entrega`
        FOREIGN KEY (`EntregaID`) REFERENCES `entregas_enfermeria`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_detalle_enf_insumo`
        FOREIGN KEY (`InsumoID`) REFERENCES `insumos`(`ID`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Vista: Stock de enfermería con semáforo
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW `v_stock_enfermeria` AS
SELECT
    ie.`ID`,
    ie.`InsumoID`,
    i.`Nombre`,
    i.`MedidaInsumo`,
    m.`Descripcion` AS MedidaDescripcion,
    ie.`Existencia`,
    i.`Minimos`,
    i.`Maximos`,
    CASE
        WHEN ie.`Existencia` <= 0          THEN 'CRITICO'
        WHEN ie.`Existencia` <= i.`Minimos` THEN 'BAJO'
        ELSE 'OK'
    END AS EstadoStock
FROM `insumos_enfermeria` ie
INNER JOIN `insumos` i      ON ie.`InsumoID` = i.`ID`
INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
WHERE i.`TipoProducto` = 'INTE'
  AND i.`Activo` = 1
  AND ie.`Activo` = 1;
