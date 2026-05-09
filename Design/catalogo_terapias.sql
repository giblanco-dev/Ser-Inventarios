-- ============================================================
--  MÓDULO: CATÁLOGO DE TERAPIAS
--  Origen: Tablas_Catalogo_Terapias.xlsx
--  Tablas: catalogo_terapias, detalle_terapias
--
--  Observaciones de datos:
--    - id_terapiaINV tiene gaps intencionales (IDs del ERP
--      origen); los INSERTs son explícitos para respetarlos.
--    - AUTO_INCREMENT se fija en 84 para que nuevos
--      registros no colisionen con los IDs del ERP.
--    - id_terapiaERP se conserva para sincronización futura
--      con el sistema externo; hoy coincide con id_terapiaINV.
--    - detalle_terapias agrega PK surrogate (ID) para
--      escalabilidad (paginación, edición por línea, etc.).
--    - CantidadInsumo permite 0: insumo 82 (CUARZOS) aparece
--      con cantidad 0 en terapias 8 y 11, interpretado como
--      "incluido sin cantidad fija / uso condicional".
--    - UNIQUE (id_terapiaINV, idInsumo) en detalle evita
--      duplicar el mismo insumo en la misma terapia.
-- ============================================================

USE ser_inventario;

-- ============================================================
-- TABLA: catalogo_terapias
-- Catálogo maestro de terapias / servicios del sistema.
-- PK: id_terapiaINV INT  — clave numérica del sistema INV.
-- ============================================================
CREATE TABLE IF NOT EXISTS catalogo_terapias (
    id_terapiaINV    INT          NOT NULL,
    id_terapiaERP    INT          NOT NULL            COMMENT 'ID en sistema ERP externo; puede divergir en el futuro',
    nom_terapia      VARCHAR(200) NOT NULL,
    Activo           TINYINT(1)   NOT NULL DEFAULT 1,
    UsuarioCreacion  VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaCreacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UsuarioActualiza VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaActualiza   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_catalogo_terapias  PRIMARY KEY (id_terapiaINV),
    INDEX idx_cat_erp    (id_terapiaERP),
    INDEX idx_cat_activo (Activo)
) ENGINE=InnoDB
  AUTO_INCREMENT = 84
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Catálogo maestro de terapias';


-- ============================================================
-- TABLA: detalle_terapias
-- Insumos necesarios para cada terapia y sus cantidades.
-- PK surrogate: ID AUTO_INCREMENT.
-- FK id_terapiaINV → catalogo_terapias(id_terapiaINV)
-- FK idInsumo      → insumos(ID)
-- ============================================================
CREATE TABLE IF NOT EXISTS detalle_terapias (
    ID               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_terapiaINV    INT             NOT NULL,
    id_terapiaERP    INT             NOT NULL            COMMENT 'Desnormalización intencional para reportes sin JOIN',
    idInsumo         INT             NOT NULL,
    CantidadInsumo   DECIMAL(10,2)   NOT NULL DEFAULT 0  COMMENT '0 = insumo incluido sin cantidad fija / uso condicional',

    CONSTRAINT pk_detalle_terapias       PRIMARY KEY (ID),
    CONSTRAINT fk_det_terapia            FOREIGN KEY (id_terapiaINV)
        REFERENCES catalogo_terapias(id_terapiaINV)
        ON UPDATE CASCADE,
    CONSTRAINT fk_det_insumo             FOREIGN KEY (idInsumo)
        REFERENCES insumos(ID),
    CONSTRAINT uq_terapia_insumo         UNIQUE (id_terapiaINV, idInsumo),

    INDEX idx_det_terapia (id_terapiaINV),
    INDEX idx_det_insumo  (idInsumo)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Insumos requeridos por terapia con cantidad estimada';


-- ============================================================
-- DATOS: catalogo_terapias (44 registros)
-- ============================================================
INSERT INTO catalogo_terapias (id_terapiaINV, id_terapiaERP, nom_terapia) VALUES
    (2, 2, 'NUVERAZA APLIC 1ML'),
    (3, 3, 'NUVERAZA APLIC 2ML'),
    (4, 4, 'NUVERAZA APLIC 3ML'),
    (5, 5, 'Aplicacion IntraMusc'),
    (6, 6, 'APLICACION O1'),
    (7, 7, 'Aplicacion O2'),
    (8, 8, 'Aplicacion O3'),
    (9, 9, 'APLICACION O4  10 MIN'),
    (10, 10, 'APLICACION O4 20 MIN'),
    (11, 11, 'aplicacion O5'),
    (12, 12, 'Aplicacion O4  15 MIN'),
    (13, 13, 'Autovacuna'),
    (16, 16, 'Biodetox'),
    (17, 17, 'BMT'),
    (18, 18, 'Cama Coreana'),
    (19, 19, 'Cama electromagnetica'),
    (20, 20, 'Cama HHP'),
    (21, 21, 'CARSILAZA FCO 25ML'),
    (25, 25, 'FC CABEZA'),
    (26, 26, 'FC Facial'),
    (27, 27, 'FC RODILLAS'),
    (28, 28, 'Fototerapia'),
    (30, 30, 'Martillo Neurologico'),
    (32, 32, 'msaje facial/'),
    (35, 35, 'Neural'),
    (37, 37, 'O3 BOLSA'),
    (38, 38, 'Pares magneticos'),
    (40, 40, 'Presoterapia'),
    (41, 41, 'scaner'),
    (42, 42, 'Terapia de Oidos'),
    (43, 43, 'Vacuna procaina'),
    (65, 65, 'O6 Burbujas'),
    (70, 70, 'Dieta Carsilaza'),
    (71, 71, 'NES'),
    (72, 72, 'Barras'),
    (74, 74, 'TANATOLOGIA'),
    (75, 75, 'Condral'),
    (76, 76, 'Aplic Intramusc Gluthation 3ml250'),
    (77, 77, 'Condral /Fact de Crecimiebnto'),
    (78, 78, 'FACTOR C. EPIDERMICO'),
    (79, 79, 'CEL DERMICA'),
    (81, 81, 'CONSTELACION FLUVIAL1'),
    (82, 82, 'TN Doble'),
    (83, 83, 'COBO OLIVA');


-- ============================================================
-- DATOS: detalle_terapias (16 registros)
-- ============================================================
INSERT INTO detalle_terapias (id_terapiaINV, id_terapiaERP, idInsumo, CantidadInsumo) VALUES
    (6, 6, 95, 2.00),
    (6, 6, 76, 4.00),
    (7, 7, 92, 1.00),
    (7, 7, 90, 1.00),
    (7, 7, 16, 2.00),
    (8, 8, 95, 2.00),
    (8, 8, 93, 1.00),
    (8, 8, 55, 1.00),
    (8, 8, 96, 1.00),
    (8, 8, 82, 0.00),
    (11, 11, 95, 2.00),
    (11, 11, 93, 1.00),
    (11, 11, 55, 1.00),
    (11, 11, 96, 1.00),
    (11, 11, 82, 0.00),
    (11, 11, 46, 1.00);


-- ============================================================
-- VISTA: v_terapias_con_insumos
-- Lista cada terapia con sus insumos expandidos.
-- Útil para la pantalla de detalle de terapia y para
-- calcular el stock disponible antes de agendar una sesión.
-- ============================================================
CREATE OR REPLACE VIEW v_terapias_con_insumos AS
SELECT
    ct.id_terapiaINV,
    ct.id_terapiaERP,
    ct.nom_terapia,
    dt.idInsumo,
    i.Nombre                        AS NombreInsumo,
    dt.CantidadInsumo,
    m.ID                            AS ClaveUnidad,
    m.Descripcion                   AS Unidad,
    i.Existencia                    AS StockActual,
    CASE
        WHEN dt.CantidadInsumo = 0          THEN 'CONDICIONAL'
        WHEN i.Existencia >= dt.CantidadInsumo THEN 'DISPONIBLE'
        ELSE 'SIN STOCK'
    END                             AS DisponibilidadInsumo,
    i.Caducidad
FROM catalogo_terapias  ct
JOIN detalle_terapias   dt ON dt.id_terapiaINV = ct.id_terapiaINV
JOIN insumos            i  ON i.ID             = dt.idInsumo
JOIN medidaInsumo       m  ON m.ID             = i.MedidaInsumo
WHERE ct.Activo = 1
ORDER BY ct.nom_terapia, i.Nombre;
