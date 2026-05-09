<?php
/**
 * InsumosElectronicosModel — Gestión de Activos Electrónicos / TI
 *
 * Maneja el ciclo de vida de equipos: alta, edición y baja lógica documentada.
 * Las contraseñas se almacenan en texto plano (requerimiento de negocio).
 * Las consultas de listado usan la vista v_activos_electronicos (sin contraseñas).
 * Las consultas de detalle usan SELECT directo a la tabla base.
 */
class InsumosElectronicosModel extends BaseModel
{
    protected string $table = 'insumos_electronicos';

    // ── Listado / Búsqueda ────────────────────────────────────

    /**
     * Obtiene la lista de activos desde la vista, con filtros opcionales.
     *
     * @param array $filters  Claves: buscar, TipoDispositivoID, UbicacionID, ResponsableID, Activo
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function findAllActivos(array $filters = [], int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhereFromFilters($filters);

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM `v_activos_electronicos`
                {$whereSql}
                ORDER BY `Modelo` ASC, `Marca` ASC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta registros con los mismos filtros (para paginación).
     *
     * @param array $filters
     * @return int
     */
    public function countActivos(array $filters = []): int
    {
        [$where, $params] = $this->buildWhereFromFilters($filters);

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM `v_activos_electronicos` {$whereSql}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Construye la cláusula WHERE para los filtros del listado.
     *
     * @param array $filters
     * @return array [where_clauses[], params[]]
     */
    private function buildWhereFromFilters(array $filters): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['buscar'])) {
            $where[] = "(`NoSerie` LIKE :buscar OR `Modelo` LIKE :buscar OR `Marca` LIKE :buscar)";
            $params['buscar'] = '%' . $filters['buscar'] . '%';
        }
        if (!empty($filters['TipoDispositivoID'])) {
            $where[] = "`TipoDispositivoID` = :tipoID";
            $params['tipoID'] = (int) $filters['TipoDispositivoID'];
        }
        if (!empty($filters['UbicacionID'])) {
            $where[] = "`UbicacionID` = :ubicID";
            $params['ubicID'] = (int) $filters['UbicacionID'];
        }
        if (!empty($filters['ResponsableID'])) {
            $where[] = "`ResponsableID` = :respID";
            $params['respID'] = (int) $filters['ResponsableID'];
        }
        if (isset($filters['Activo']) && $filters['Activo'] !== '') {
            $where[] = "`Activo` = :activo";
            $params['activo'] = (int) $filters['Activo'];
        }

        return [$where, $params];
    }

    // ── Detalle ───────────────────────────────────────────────

    /**
     * Retorna un activo por ID incluyendo todos sus campos (con contraseñas).
     * Usa SELECT directo a la tabla base, no la vista.
     *
     * @param int $id
     * @return array|null
     */
    public function findByIdFull(int $id): ?array
    {
        $sql = "SELECT ie.*,
                       u.`Descripcion`       AS Ubicacion,
                       td.`Descripcion`      AS TipoDispositivo,
                       r.`NombreResponsable` AS Responsable,
                       r.`Puesto`
                FROM `insumos_electronicos` ie
                JOIN  `ubicaciones`      u  ON u.`ID`  = ie.`UbicacionID`
                LEFT JOIN `tipo_dispositivo` td ON td.`ID` = ie.`TipoDispositivoID`
                LEFT JOIN `responsables`     r  ON r.`ID`  = ie.`ResponsableID`
                WHERE ie.`ID` = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Verifica si un NoSerie ya existe en la tabla (para validación de unicidad).
     *
     * @param string $noSerie
     * @param int    $excludeId ID a excluir (en edición)
     * @return bool
     */
    public function noSerieExists(string $noSerie, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos_electronicos`
                WHERE `NoSerie` = :noserie AND `ID` != :excludeId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['noserie' => $noSerie, 'excludeId' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ── Alta ──────────────────────────────────────────────────

    /**
     * Registra un nuevo activo electrónico.
     *
     * @param array $data Datos del formulario ya saneados
     * @return int ID del registro creado
     */
    public function registrar(array $data): int
    {
        $sql = "INSERT INTO `insumos_electronicos`
                    (`UbicacionID`, `NoSerie`, `Modelo`, `Marca`,
                     `TipoDispositivoID`, `Almacenamiento`, `RAM`, `Procesador`,
                     `SO`, `Usuario`, `Pass`, `PassAdmin`,
                     `ResponsableID`, `ConexionRemota`, `ID_Conexion`,
                     `PassConexionRemota`, `Descripcion`, `UsuarioCreacion`, `UsuariaActualiza`)
                VALUES
                    (:UbicacionID, :NoSerie, :Modelo, :Marca,
                     :TipoDispositivoID, :Almacenamiento, :RAM, :Procesador,
                     :SO, :Usuario, :Pass, :PassAdmin,
                     :ResponsableID, :ConexionRemota, :ID_Conexion,
                     :PassConexionRemota, :Descripcion, :usuario_creacion, :usuario_actualiza)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'UbicacionID'       => $data['UbicacionID'],
            'NoSerie'           => strtoupper(trim($data['NoSerie'])),
            'Modelo'            => strtoupper(trim($data['Modelo'])),
            'Marca'             => strtoupper(trim($data['Marca'])),
            'TipoDispositivoID' => $data['TipoDispositivoID'] ?: null,
            'Almacenamiento'    => $data['Almacenamiento'] !== '' ? $data['Almacenamiento'] : null,
            'RAM'               => $data['RAM'] !== '' ? $data['RAM'] : null,
            'Procesador'        => $data['Procesador'] !== '' ? strtoupper(trim($data['Procesador'])) : null,
            'SO'                => $data['SO'] !== '' ? $data['SO'] : null,
            'Usuario'           => $data['Usuario'] !== '' ? $data['Usuario'] : null,
            'Pass'              => $data['Pass'] !== '' ? $data['Pass'] : null,
            'PassAdmin'         => $data['PassAdmin'] !== '' ? $data['PassAdmin'] : null,
            'ResponsableID'     => $data['ResponsableID'] ?: null,
            'ConexionRemota'    => $data['ConexionRemota'],
            'ID_Conexion'       => $data['ID_Conexion'] !== '' ? $data['ID_Conexion'] : null,
            'PassConexionRemota'=> $data['PassConexionRemota'] !== '' ? $data['PassConexionRemota'] : null,
            'Descripcion'       => $data['Descripcion'] !== '' ? $data['Descripcion'] : null,
            'usuario_creacion'  => $_SESSION['username'] ?? 'sistema',
            'usuario_actualiza' => $_SESSION['username'] ?? 'sistema',
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ── Edición ───────────────────────────────────────────────

    /**
     * Actualiza un activo electrónico. NoSerie NUNCA se modifica.
     * Si un campo de contraseña viene vacío, se mantiene el valor en BD.
     *
     * @param int   $id
     * @param array $data Datos del formulario ya saneados
     * @return bool
     */
    public function actualizar(int $id, array $data): bool
    {
        // Campos que SIEMPRE se actualizan (excluyendo NoSerie y contraseñas)
        $sets   = [];
        $params = ['pk_id' => $id, 'usuario_sys' => $_SESSION['username'] ?? 'sistema'];

        $camposDirectos = [
            'UbicacionID', 'Modelo', 'Marca', 'TipoDispositivoID',
            'Almacenamiento', 'RAM', 'Procesador', 'SO', 'Usuario',
            'ResponsableID', 'ConexionRemota', 'ID_Conexion', 'Descripcion',
        ];

        foreach ($camposDirectos as $campo) {
            $sets[]         = "`{$campo}` = :{$campo}";
            $params[$campo] = isset($data[$campo]) && $data[$campo] !== '' ? $data[$campo] : null;
        }

        // Campos numéricos que pueden ser null
        $params['UbicacionID']       = $data['UbicacionID'] ?: null;
        $params['TipoDispositivoID'] = $data['TipoDispositivoID'] ?: null;
        $params['ResponsableID']     = $data['ResponsableID'] ?: null;
        $params['Almacenamiento']    = $data['Almacenamiento'] !== '' ? $data['Almacenamiento'] : null;
        $params['RAM']               = $data['RAM'] !== '' ? $data['RAM'] : null;

        // Contraseñas: solo actualizar si se proporcionó un valor
        foreach (['Pass', 'PassAdmin', 'PassConexionRemota'] as $passField) {
            if (!empty($data[$passField])) {
                $sets[]               = "`{$passField}` = :{$passField}";
                $params[$passField]   = $data[$passField];
            }
        }

        $sets[] = "`UsuariaActualiza` = :usuario_sys";

        $sql = "UPDATE `insumos_electronicos`
                SET " . implode(', ', $sets) . "
                WHERE `ID` = :pk_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // ── Baja Lógica ───────────────────────────────────────────

    /**
     * Da de baja un activo electrónico con comentario obligatorio.
     *
     * @param int    $id
     * @param string $comentario Razón de la baja (obligatorio)
     * @return bool
     */
    public function darDeBaja(int $id, string $comentario): bool
    {
        $sql = "UPDATE `insumos_electronicos`
                SET `Activo` = 0,
                    `fecha_baja` = NOW(),
                    `comentarios_baja` = :comentario,
                    `UsuariaActualiza` = :usuario
                WHERE `ID` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'comentario' => $comentario,
            'usuario'    => $_SESSION['username'] ?? 'sistema',
            'id'         => $id,
        ]);
    }

    // ── Catálogos auxiliares ──────────────────────────────────

    /**
     * Retorna en un solo llamado todos los combos necesarios para el formulario.
     *
     * @return array ['ubicaciones' => [...], 'tipos' => [...], 'responsables' => [...]]
     */
    public function getCatalogos(): array
    {
        $ubicaciones = $this->db->query(
            "SELECT `ID`, `Descripcion` FROM `ubicaciones` WHERE `Activo` = 1 ORDER BY `Descripcion` ASC"
        )->fetchAll();

        $tipos = $this->db->query(
            "SELECT `ID`, `Descripcion` FROM `tipo_dispositivo` WHERE `Activo` = 1 ORDER BY `Descripcion` ASC"
        )->fetchAll();

        $responsables = $this->db->query(
            "SELECT `ID`, `NombreResponsable`, `Puesto` FROM `responsables` WHERE `Activo` = 1 ORDER BY `NombreResponsable` ASC"
        )->fetchAll();

        return compact('ubicaciones', 'tipos', 'responsables');
    }
}
