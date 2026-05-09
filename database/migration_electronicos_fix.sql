-- ============================================================
-- SER Inventarios — Migración: Correcciones módulo Electrónicos
-- Motor: MySQL 8.0 / InnoDB
-- Charset: utf8mb4
-- ============================================================

USE `ser_inventario`;

-- ------------------------------------------------------------
-- 1. Agregar AUTO_INCREMENT a los catálogos de Electrónicos
--    (las PKs fueron asignadas manualmente en el seed data)
-- ------------------------------------------------------------
ALTER TABLE `ubicaciones`
    MODIFY COLUMN `ID` INT NOT NULL AUTO_INCREMENT;

ALTER TABLE `tipo_dispositivo`
    MODIFY COLUMN `ID` INT NOT NULL AUTO_INCREMENT;

ALTER TABLE `responsables`
    MODIFY COLUMN `ID` INT NOT NULL AUTO_INCREMENT;

-- ------------------------------------------------------------
-- 2. Nueva tabla: puestos
--    Catálogo de puestos de trabajo para el combo de Responsable
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `puestos` (
    `ID`               INT           NOT NULL AUTO_INCREMENT,
    `Descripcion`      VARCHAR(100)  NOT NULL,
    `Activo`           TINYINT(1)    NOT NULL DEFAULT 1,
    `UsuarioCreacion`  VARCHAR(50)   NOT NULL DEFAULT 'sistema',
    `FechaCreacion`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `UsuarioActualiza` VARCHAR(50)   NOT NULL DEFAULT 'sistema',
    `FechaActualiza`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_puestos PRIMARY KEY (`ID`)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Catálogo de puestos de trabajo';

-- Seed: puestos observados en los datos existentes
INSERT INTO `puestos` (`Descripcion`) VALUES
    ('Responsable de farmacia'),
    ('Enfermeras auxiliares'),
    ('Recepción'),
    ('Médico'),
    ('Caja'),
    ('Supervisor'),
    ('Intendencia'),
    ('Pendiente')
ON DUPLICATE KEY UPDATE `Descripcion` = `Descripcion`;

-- ------------------------------------------------------------
-- 3. Corregir vista v_activos_electronicos
--    Error: faltaba la coma entre FechaActualiza y fecha_baja
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW `v_activos_electronicos` AS
SELECT
    ie.`ID`,
    ie.`NoSerie`,
    ie.`Modelo`,
    ie.`Marca`,
    ie.`Procesador`,
    ie.`Almacenamiento`,
    ie.`RAM`,
    ie.`SO`,
    ie.`Usuario`,
    ie.`ConexionRemota`,
    ie.`ID_Conexion`,
    ie.`Descripcion`,
    u.`ID`                AS UbicacionID,
    u.`Descripcion`       AS Ubicacion,
    td.`ID`               AS TipoDispositivoID,
    td.`Descripcion`      AS TipoDispositivo,
    r.`ID`                AS ResponsableID,
    r.`NombreResponsable` AS Responsable,
    r.`Puesto`,
    ie.`Activo`,
    ie.`FechaCreacion`,
    ie.`FechaActualiza`,
    ie.`fecha_baja`,
    ie.`comentarios_baja`
FROM `insumos_electronicos` ie
JOIN  `ubicaciones`      u  ON u.`ID`  = ie.`UbicacionID`
LEFT JOIN `tipo_dispositivo` td ON td.`ID` = ie.`TipoDispositivoID`
LEFT JOIN `responsables`     r  ON r.`ID`  = ie.`ResponsableID`;
