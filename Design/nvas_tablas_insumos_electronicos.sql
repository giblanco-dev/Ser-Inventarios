
USE ser_inventario;

-- ============================================================
-- TABLA: ubicaciones
-- Catálogo de áreas/espacios físicos de la clínica.
-- PK: ID INT — clave numérica (se respetan IDs del Excel).
-- ============================================================
CREATE TABLE IF NOT EXISTS ubicaciones (
    ID               INT          NOT NULL,
    Descripcion      VARCHAR(100) NOT NULL,
    Activo           TINYINT(1)   NOT NULL DEFAULT 1,
    UsuarioCreacion  VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaCreacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UsuarioActualiza VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaActualiza   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_ubicaciones PRIMARY KEY (ID)
) ENGINE=InnoDB
  AUTO_INCREMENT = 12
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Catálogo de ubicaciones físicas de la clínica';


-- ============================================================
-- TABLA: tipo_dispositivo
-- Catálogo de tipos de equipos electrónicos.
-- PK: ID INT — clave numérica.
-- ============================================================
CREATE TABLE IF NOT EXISTS tipo_dispositivo (
    ID               INT          NOT NULL,
    Descripcion      VARCHAR(100) NOT NULL,
    Activo           TINYINT(1)   NOT NULL DEFAULT 1,
    UsuarioCreacion  VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaCreacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UsuarioActualiza VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaActualiza   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_tipo_dispositivo PRIMARY KEY (ID)
) ENGINE=InnoDB
  AUTO_INCREMENT = 12
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Catálogo de tipos de dispositivo electrónico';


-- ============================================================
-- TABLA: responsables
-- Directorio del personal que puede ser asignado a activos.
-- PK: ID INT — clave numérica.
-- ============================================================
CREATE TABLE IF NOT EXISTS responsables (
    ID                 INT          NOT NULL,
    NombreResponsable  VARCHAR(200) NOT NULL,
    Puesto             VARCHAR(100) NOT NULL,
    Activo             TINYINT(1)   NOT NULL DEFAULT 1,
    UsuarioCreacion    VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaCreacion      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UsuarioActualiza   VARCHAR(50)  NOT NULL DEFAULT 'gblanco',
    FechaActualiza     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_responsables PRIMARY KEY (ID),
    INDEX idx_resp_puesto (Puesto)
) ENGINE=InnoDB
  AUTO_INCREMENT = 29
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Directorio de responsables de activos electrónicos';


-- ============================================================
-- TABLA: insumos_electronicos
-- Registro de activos TI (computadoras, impresoras, etc.).
-- PK: ID INT UNSIGNED AUTO_INCREMENT.
-- FK: UbicacionID     → ubicaciones(ID)
-- FK: ResponsableID   → responsables(ID)   [NULLABLE]
-- FK: TipoDispositivo → tipo_dispositivo(ID) [NULLABLE]
--
-- Almacenamiento y RAM en GB (valores enteros en los datos).
-- ConexionRemota ENUM: SI | NO | PENDIENTE.
-- Pass, PassAdmin, PassConexionRemota: ver advertencia arriba.
-- ============================================================
CREATE TABLE `insumos_electronicos` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UbicacionID` int NOT NULL,
  `NoSerie` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Modelo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Marca` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Almacenamiento` decimal(10,2) DEFAULT NULL COMMENT 'GB de almacenamiento en disco',
  `RAM` decimal(10,2) DEFAULT NULL COMMENT 'GB de memoria RAM',
  `Procesador` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Usuario` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario del sistema operativo',
  `Pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contraseña SO — cifrar en app antes de persistir',
  `PassAdmin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contraseña admin — cifrar en app antes de persistir',
  `ResponsableID` int DEFAULT NULL,
  `SO` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sistema operativo instalado',
  `ConexionRemota` enum('SI','NO','PENDIENTE') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `ID_Conexion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID o IP para acceso remoto',
  `PassConexionRemota` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contraseña conexión remota — cifrar en app',
  `TipoDispositivoID` int DEFAULT NULL,
  `Descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT '1',
  `UsuarioCreacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gblanco',
  `FechaCreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UsuariaActualiza` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gblanco',
  `FechaActualiza` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_baja` datetime DEFAULT NULL,
  `comentarios_baja` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_ie_noserie` (`NoSerie`),
  KEY `idx_ie_ubicacion` (`UbicacionID`),
  KEY `idx_ie_tipo_dispositivo` (`TipoDispositivoID`),
  KEY `idx_ie_responsable` (`ResponsableID`),
  CONSTRAINT `fk_ie_responsable` FOREIGN KEY (`ResponsableID`) REFERENCES `responsables` (`ID`),
  CONSTRAINT `fk_ie_tipo_dispositivo` FOREIGN KEY (`TipoDispositivoID`) REFERENCES `tipo_dispositivo` (`ID`),
  CONSTRAINT `fk_ie_ubicacion` FOREIGN KEY (`UbicacionID`) REFERENCES `ubicaciones` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de activos de tecnología (equipos electrónicos)'


-- ============================================================
-- DATOS: ubicaciones (11 registros)
-- NOTA: ID=11 'Almacén' agregado — no estaba en el Excel
--       pero sí en los datos de insumos_electronicos.
-- ============================================================
INSERT INTO ubicaciones (ID, Descripcion) VALUES
    (1, 'Recepcion'),
    (2, 'Vigilancia'),
    (3, 'Oficina Dra Mónica'),
    (4, 'Caja'),
    (5, 'Consultorio Dr León'),
    (6, 'Consultorio Dr Enrique'),
    (7, 'Farmacia'),
    (8, 'Signos Vitales'),
    (9, 'Oficina Administración'),
    (10, 'Enfermería'),
    (11, 'Almacén');

-- ============================================================
-- DATOS: tipo_dispositivo (11 registros)
-- ============================================================
INSERT INTO tipo_dispositivo (ID, Descripcion) VALUES
    (1, 'Laptop'),
    (2, 'PC Escritorio'),
    (3, 'Servidor'),
    (4, 'Mouse'),
    (5, 'Teclado'),
    (6, 'Impresora Laser'),
    (7, 'Tablet'),
    (8, 'Celular'),
    (9, 'Lector Códigos'),
    (10, 'Impresora Inyección'),
    (11, 'Impresora Térmica');

-- ============================================================
-- DATOS: responsables (28 registros)
-- ============================================================
INSERT INTO responsables (ID, NombreResponsable, Puesto) VALUES
    (1, 'DELIA ALEJANDRA SANTIAGO ROJAS', 'Responsable de farmacia'),
    (2, 'JESSICA HERNANDEZ FLORES', 'Enfermeras auxiliares'),
    (3, 'SOCORRO RAMÍREZ MURILLO', 'Enfermeras auxiliares'),
    (4, 'ELIZABETH VILLICAÑA HERNÁNDEZ', 'Enfermeras auxiliares'),
    (5, 'SILVIA MEJIA ESPINOSA', 'Enfermeras auxiliares'),
    (6, 'NOHEMI MUÑOZ CORNEJO', 'Responsable de farmacia'),
    (7, 'FELÍCITAS MENDEZ LUCAS', 'Enfermeras auxiliares'),
    (8, 'IRMA ARACELI CASTAÑEDA', 'Enfermeras auxiliares'),
    (9, 'LILIANA QUINARD GARCIA', 'Enfermeras auxiliares'),
    (10, 'ROSA GLORIA RAMÍREZ MURILLO', 'Enfermeras auxiliares'),
    (11, 'LETICIA AMBROSIO RAMOS', 'Recepción'),
    (12, 'MARIA DEL CARMEN MARTINEZ CORRAL', 'Enfermeras auxiliares'),
    (13, 'ANGELICA CECILIA CRUZ CERVANTES', 'Enfermeras auxiliares'),
    (14, 'JANET YAZMIN REYES ANTONIO', 'Recepción'),
    (15, 'CYNTHIA LUCIA HERNANDEZ MELO', 'Pendiente'),
    (16, 'ARIADNA DE LA ROSA RAMIREZ', 'Pendiente'),
    (17, 'ABIGAIL GARCIA LINARES', 'Caja'),
    (18, 'BERTHA ANGELICA MOSQUEDA HERNANDEZ', 'Médico'),
    (19, 'DR. GUILLERMO', 'Médico'),
    (20, 'Asistente Dental', 'Pendiente'),
    (21, 'PRUEBA INTEDENCIA UPDATE', 'Pendiente'),
    (22, 'MORALES BLANCO IVÁN', 'Supervisor'),
    (23, 'MONICA MARTINEZ', 'Supervisor'),
    (24, 'ANALI ROCIO REYES FLORES', 'Enfermeras auxiliares'),
    (25, 'ANA ROSARIO ALFARO', 'Enfermeras auxiliares'),
    (26, 'KAREN TORRES CORTES', 'Enfermeras auxiliares'),
    (27, 'JUANA SANCHEZ ROMERO', 'Responsable de farmacia'),
    (28, 'COLABORADOR NUEVO PRUEBAS', 'Pendiente');

-- ============================================================
-- DATOS: insumos_electronicos (4 registros)
-- ============================================================
INSERT INTO insumos_electronicos
    (UbicacionID, NoSerie, Modelo, Marca, Almacenamiento, RAM,
     Procesador, Usuario, Pass, PassAdmin, ResponsableID,
     SO, ConexionRemota, ID_Conexion, PassConexionRemota,
     TipoDispositivoID, Descripcion)
VALUES
    (1, '76P6342', 'INSPIRON 20 MODEL 3043', 'DELL',
     1000.00, 4.00,
     'INTEL PENTIUM N3540', 'Ser Recepción', '2024 pin',
     'qwerty_2610', NULL,
     'W8 SINGLE L', 'SI',
     '130742912', '2f33k',
     NULL, NULL),
    (11, 'MXX3450SS3', '20-b350la', 'HP',
     512.00, 4.00,
     'INTEL PENTIUM G2030', 'ALMACEN 1', 'csa.admin',
     'Trini', NULL,
     'W10 SINGLE L', 'SI',
     '1216231395', '5t6hb2',
     6, NULL),
    (3, 'H5HK902', 'INSPIRON 3477 AIO', 'DELL',
     100.00, 4.00,
     'INTEL CORE i7', 'MONI_', NULL,
     'qwerty_2610', NULL,
     'W10 SINGLE L', 'PENDIENTE',
     NULL, NULL,
     NULL, NULL),
    (4, 'VS30227588', 'LENOVO C200', 'LENOVO',
     112.00, 4.00,
     'INTEL ATOM', 'CAJA', NULL,
     'mon', NULL,
     'W7 PROFESSIONAL', 'SI',
     '991 804 216', '6193',
     10, NULL);

-- ============================================================
-- VISTA: v_activos_electronicos
-- Panel completo de activos TI con todos sus catálogos
-- resueltos. Usada para listados, búsquedas y reportes.
-- Los campos de contraseña se EXCLUYEN intencionalmente
-- por seguridad: consultarlos solo cuando sea necesario
-- con SELECT directo a la tabla base.
-- ============================================================
CREATE OR REPLACE VIEW v_activos_electronicos AS
SELECT
    ie.ID,
    ie.NoSerie,
    ie.Modelo,
    ie.Marca,
    ie.Procesador,
    ie.Almacenamiento,
    ie.RAM,
    ie.SO,
    ie.Usuario,
    ie.ConexionRemota,
    ie.ID_Conexion,
    ie.Descripcion,
    u.ID                AS UbicacionID,
    u.Descripcion       AS Ubicacion,
    td.ID               AS TipoDispositivoID,
    td.Descripcion      AS TipoDispositivo,
    r.ID                AS ResponsableID,
    r.NombreResponsable AS Responsable,
    r.Puesto,
    ie.Activo,
    ie.FechaCreacion,
    ie.FechaActualiza
    ie.fecha_baja,
    ie.comentarios_baja
FROM insumos_electronicos ie
JOIN  ubicaciones     u   ON u.ID  = ie.UbicacionID
LEFT JOIN tipo_dispositivo td  ON td.ID = ie.TipoDispositivoID
LEFT JOIN responsables     r   ON r.ID  = ie.ResponsableID;