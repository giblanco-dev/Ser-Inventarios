-- ============================================================
-- SER Inventarios — Migración: SSO desde el ERP
-- Motor: MySQL 8.0 / InnoDB
-- Charset: utf8mb4
-- ============================================================

USE `ser_inventario`;

-- ------------------------------------------------------------
-- Tabla de intercambio: tokens de un solo uso generados por el
-- ERP para abrir sesión en este módulo sin volver a loguearse.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sso_tokens` (
    `ID`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `token`           CHAR(64)     NOT NULL,
    `erp_user_id`     VARCHAR(50)  NOT NULL,
    `username`        VARCHAR(100) NOT NULL,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `rol`             VARCHAR(50)  NOT NULL,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`      DATETIME     NOT NULL,
    `used_at`         DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_sso_token` (`token`)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Tokens de un solo uso para SSO ERP -> Ser-Inventarios';
