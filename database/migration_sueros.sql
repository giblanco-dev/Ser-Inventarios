-- ============================================================
--  MÓDULO: CATÁLOGO DE SUEROS
--  Tablas: catalogo_sueros, detalle_sueros
--
--  Observaciones de diseño:
--    - catalogo_sueros es un espejo LOCAL de ser.sueros (ERP),
--      actualizado por CatalogoSueroModel::sincronizarDesdeErp().
--      Este módulo nunca escribe en el schema `ser` del ERP.
--    - id_suero NO tiene AUTO_INCREMENT: siempre proviene del
--      ERP, que es dueño de la numeración.
--    - No se replican tablas/IDs de catalogo_terapias: 15 de los
--      16 id_suero reales colisionan con id_terapiaINV existentes
--      apuntando a terapias distintas, por lo que se usa un
--      namespace de IDs propio en tablas separadas.
--    - No se guarda `precio`: en Inventarios solo interesa el
--      nombre del suero y sus insumos asociados.
-- ============================================================

USE ser_inventario;

CREATE TABLE IF NOT EXISTS catalogo_sueros (
    id_suero         INT          NOT NULL,
    nom_suero        VARCHAR(50)  NOT NULL,
    Activo           TINYINT(1)   NOT NULL DEFAULT 1,
    UsuarioCreacion  VARCHAR(50)  NOT NULL DEFAULT 'sync-erp',
    FechaCreacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UsuarioActualiza VARCHAR(50)  NOT NULL DEFAULT 'sync-erp',
    FechaActualiza   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_catalogo_sueros PRIMARY KEY (id_suero),
    INDEX idx_sueros_activo (Activo)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Catálogo espejo de ser.sueros (ERP), sincronizado manualmente';


CREATE TABLE IF NOT EXISTS detalle_sueros (
    ID              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    id_suero        INT           NOT NULL,
    idInsumo        INT           NOT NULL,
    CantidadInsumo  DECIMAL(10,2) NOT NULL DEFAULT 0,

    CONSTRAINT pk_detalle_sueros    PRIMARY KEY (ID),
    CONSTRAINT fk_det_suero         FOREIGN KEY (id_suero)
        REFERENCES catalogo_sueros(id_suero)
        ON UPDATE CASCADE,
    CONSTRAINT fk_det_suero_insumo  FOREIGN KEY (idInsumo)
        REFERENCES insumos(ID),
    CONSTRAINT uq_suero_insumo      UNIQUE (id_suero, idInsumo),

    INDEX idx_det_suero        (id_suero),
    INDEX idx_det_suero_insumo (idInsumo)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Insumos requeridos por suero con cantidad estimada';
