<?php
/**
 * EnfermeriaModel — Gestión del sub-inventario de Enfermería
 *
 * Maneja entregas y devoluciones de insumos INTE entre el inventario
 * global y el stock de enfermería. Todas las operaciones que afectan
 * inventario se ejecutan dentro de transacciones atómicas con
 * bloqueo pesimista (SELECT ... FOR UPDATE) para prevenir
 * condiciones de carrera.
 */
class EnfermeriaModel extends BaseModel
{
    protected string $table = 'insumos_enfermeria';

    /**
     * Registra una entrega o devolución completa (cabecera + detalle)
     * y actualiza existencias en ambas tablas.
     *
     * @param array $cabecera  ['TipoOperacion', 'EntregadoPor', 'RecibidoPor', 'Observaciones']
     * @param array $detalles  [['InsumoID', 'Cantidad'], ...]
     * @return int ID de la entrega creada
     * @throws Exception Si hay error de validación o de BD
     */
    public function registrarEntrega(array $cabecera, array $detalles): int
    {
        $tipoOp = $cabecera['TipoOperacion']; // ENTREGA o DEVOLUCION

        $this->db->beginTransaction();

        try {
            // 1. Insertar cabecera
            $sqlCab = "INSERT INTO `entregas_enfermeria`
                       (`TipoOperacion`, `EntregadoPor`, `RecibidoPor`, `Observaciones`)
                       VALUES (:tipo, :entrega, :recibe, :obs)";
            $stmtCab = $this->db->prepare($sqlCab);
            $stmtCab->execute([
                'tipo'    => $tipoOp,
                'entrega' => $cabecera['EntregadoPor'],
                'recibe'  => $cabecera['RecibidoPor'],
                'obs'     => $cabecera['Observaciones'] ?? null,
            ]);
            $entregaId = (int) $this->db->lastInsertId();

            // 2. Insertar cada línea de detalle y actualizar existencias
            $sqlDetalle = "INSERT INTO `entregas_enfermeria_detalle`
                           (`EntregaID`, `InsumoID`, `Cantidad`)
                           VALUES (:entregaId, :insumoId, :cantidad)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            foreach ($detalles as $linea) {
                $insumoId = (int) $linea['InsumoID'];
                $cantidad = (float) $linea['Cantidad'];

                if ($tipoOp === 'ENTREGA') {
                    // Validar stock global con bloqueo
                    $sqlCheck = "SELECT `Existencia`, `Nombre` FROM `insumos`
                                 WHERE `ID` = :id FOR UPDATE";
                    $stmtCheck = $this->db->prepare($sqlCheck);
                    $stmtCheck->execute(['id' => $insumoId]);
                    $insumo = $stmtCheck->fetch();

                    if (!$insumo) {
                        throw new Exception("El insumo con ID {$insumoId} no existe.");
                    }

                    if (($insumo['Existencia'] - $cantidad) < 0) {
                        throw new Exception(
                            "Stock insuficiente para \"{$insumo['Nombre']}\". "
                            . "Existencia global: {$insumo['Existencia']}, "
                            . "Cantidad solicitada: {$cantidad}."
                        );
                    }

                    // Descontar del inventario global
                    $sqlGlobal = "UPDATE `insumos`
                                  SET `Existencia` = `Existencia` - :cantidad
                                  WHERE `ID` = :id";
                    $stmtGlobal = $this->db->prepare($sqlGlobal);
                    $stmtGlobal->execute([
                        'cantidad' => $cantidad,
                        'id'       => $insumoId,
                    ]);

                    // Sumar al stock de enfermería (upsert)
                    $sqlEnf = "INSERT INTO `insumos_enfermeria` (`InsumoID`, `Existencia`)
                               VALUES (:insumoId, :cantidad)
                               ON DUPLICATE KEY UPDATE
                               `Existencia` = `Existencia` + VALUES(`Existencia`),
                               `UpdatedAt` = NOW()";
                    $stmtEnf = $this->db->prepare($sqlEnf);
                    $stmtEnf->execute([
                        'insumoId' => $insumoId,
                        'cantidad' => $cantidad,
                    ]);

                } else {
                    // DEVOLUCION — Validar stock de enfermería con bloqueo
                    $sqlCheck = "SELECT ie.`Existencia`, i.`Nombre`
                                 FROM `insumos_enfermeria` ie
                                 INNER JOIN `insumos` i ON ie.`InsumoID` = i.`ID`
                                 WHERE ie.`InsumoID` = :id
                                 FOR UPDATE";
                    $stmtCheck = $this->db->prepare($sqlCheck);
                    $stmtCheck->execute(['id' => $insumoId]);
                    $stockEnf = $stmtCheck->fetch();

                    if (!$stockEnf || ($stockEnf['Existencia'] - $cantidad) < 0) {
                        $nombre = $stockEnf['Nombre'] ?? "ID {$insumoId}";
                        $existActual = $stockEnf['Existencia'] ?? 0;
                        throw new Exception(
                            "Stock insuficiente en enfermería para \"{$nombre}\". "
                            . "Existencia: {$existActual}, "
                            . "Cantidad solicitada: {$cantidad}."
                        );
                    }

                    // Descontar del stock de enfermería
                    $sqlEnf = "UPDATE `insumos_enfermeria`
                               SET `Existencia` = `Existencia` - :cantidad,
                                   `UpdatedAt` = NOW()
                               WHERE `InsumoID` = :id";
                    $stmtEnf = $this->db->prepare($sqlEnf);
                    $stmtEnf->execute([
                        'cantidad' => $cantidad,
                        'id'       => $insumoId,
                    ]);

                    // Sumar al inventario global
                    $sqlGlobal = "UPDATE `insumos`
                                  SET `Existencia` = `Existencia` + :cantidad
                                  WHERE `ID` = :id";
                    $stmtGlobal = $this->db->prepare($sqlGlobal);
                    $stmtGlobal->execute([
                        'cantidad' => $cantidad,
                        'id'       => $insumoId,
                    ]);
                }

                // Insertar línea de detalle
                $stmtDetalle->execute([
                    'entregaId' => $entregaId,
                    'insumoId'  => $insumoId,
                    'cantidad'  => $cantidad,
                ]);
            }

            $this->db->commit();
            return $entregaId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el stock actual de enfermería con filtros.
     *
     * @param array $filters  ['buscar' => string, 'estado' => 'CRITICO'|'BAJO'|'OK']
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function getStockEnfermeria(array $filters = [], int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['buscar'])) {
            $where[] = "i.`Nombre` LIKE :buscar";
            $params['buscar'] = '%' . $filters['buscar'] . '%';
        }

        $havingClause = '';
        if (!empty($filters['estado'])) {
            $havingClause = "HAVING EstadoStock = :estado";
            $params['estado'] = $filters['estado'];
        }

        $whereSql = $where ? 'AND ' . implode(' AND ', $where) : '';

        $sql = "SELECT ie.`ID`, ie.`InsumoID`, i.`Nombre`, i.`MedidaInsumo`,
                       m.`Descripcion` AS MedidaDescripcion,
                       ie.`Existencia`, i.`Minimos`, i.`Maximos`,
                       CASE
                           WHEN ie.`Existencia` <= 0            THEN 'CRITICO'
                           WHEN ie.`Existencia` <= i.`Minimos`  THEN 'BAJO'
                           ELSE 'OK'
                       END AS EstadoStock
                FROM `insumos_enfermeria` ie
                INNER JOIN `insumos` i      ON ie.`InsumoID` = i.`ID`
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE i.`TipoProducto` = 'INTE'
                  AND i.`Activo` = 1
                  AND ie.`Activo` = 1
                  {$whereSql}
                {$havingClause}
                ORDER BY i.`Nombre` ASC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el stock actual de enfermería con filtros (para paginación).
     */
    public function countStockEnfermeria(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['buscar'])) {
            $where[] = "i.`Nombre` LIKE :buscar";
            $params['buscar'] = '%' . $filters['buscar'] . '%';
        }

        $havingClause = '';
        if (!empty($filters['estado'])) {
            $havingClause = "HAVING EstadoStock = :estado";
            $params['estado'] = $filters['estado'];
        }

        $whereSql = $where ? 'AND ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM (
                    SELECT ie.`ID`,
                           CASE
                               WHEN ie.`Existencia` <= 0            THEN 'CRITICO'
                               WHEN ie.`Existencia` <= i.`Minimos`  THEN 'BAJO'
                               ELSE 'OK'
                           END AS EstadoStock
                    FROM `insumos_enfermeria` ie
                    INNER JOIN `insumos` i ON ie.`InsumoID` = i.`ID`
                    WHERE i.`TipoProducto` = 'INTE'
                      AND i.`Activo` = 1
                      AND ie.`Activo` = 1
                      {$whereSql}
                    {$havingClause}
                ) AS sub";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Historial de entregas/devoluciones con filtros.
     */
    public function getHistorial(array $filters = [], int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "e.`FechaEntrega` >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "e.`FechaEntrega` <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['tipo_operacion'])) {
            $where[] = "e.`TipoOperacion` = :tipo_op";
            $params['tipo_op'] = $filters['tipo_operacion'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT e.`ID`, e.`TipoOperacion`, e.`FechaEntrega`,
                       e.`EntregadoPor`, e.`RecibidoPor`, e.`Observaciones`,
                       COUNT(d.`ID`) AS TotalLineas,
                       SUM(d.`Cantidad`) AS TotalCantidad
                FROM `entregas_enfermeria` e
                INNER JOIN `entregas_enfermeria_detalle` d ON d.`EntregaID` = e.`ID`
                {$whereSql}
                GROUP BY e.`ID`
                ORDER BY e.`FechaEntrega` DESC, e.`ID` DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta entregas/devoluciones con filtros (para paginación).
     */
    public function countHistorial(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "e.`FechaEntrega` >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "e.`FechaEntrega` <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['tipo_operacion'])) {
            $where[] = "e.`TipoOperacion` = :tipo_op";
            $params['tipo_op'] = $filters['tipo_operacion'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(DISTINCT e.`ID`)
                FROM `entregas_enfermeria` e
                {$whereSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca una entrega/devolución por su ID en la tabla entregas_enfermeria.
     *
     * @param int $id
     * @return array|null
     */
    public function findEntregaById(int $id): ?array
    {
        $sql = "SELECT * FROM `entregas_enfermeria` WHERE `ID` = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene el detalle de una entrega/devolución específica.
     */
    public function getDetalleEntrega(int $id): array
    {
        $sql = "SELECT d.*, i.`Nombre` AS InsumoNombre,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `entregas_enfermeria_detalle` d
                INNER JOIN `insumos` i      ON d.`InsumoID` = i.`ID`
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE d.`EntregaID` = :id
                ORDER BY d.`ID` ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * KPIs del sub-inventario de enfermería.
     *
     * @return array ['total', 'critico', 'bajo']
     */
    public function getKPIs(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN ie.`Existencia` <= 0 THEN 1 ELSE 0 END) AS critico,
                    SUM(CASE WHEN ie.`Existencia` > 0 AND ie.`Existencia` <= i.`Minimos` THEN 1 ELSE 0 END) AS bajo
                FROM `insumos_enfermeria` ie
                INNER JOIN `insumos` i ON ie.`InsumoID` = i.`ID`
                WHERE i.`TipoProducto` = 'INTE'
                  AND i.`Activo` = 1
                  AND ie.`Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return [
            'total'   => (int) ($row['total'] ?? 0),
            'critico' => (int) ($row['critico'] ?? 0),
            'bajo'    => (int) ($row['bajo'] ?? 0),
        ];
    }

    /**
     * Lista insumos INTE del inventario global con existencia > 0
     * para el formulario de entrega. Soporta búsqueda por nombre.
     */
    public function getInsumosDisponibles(string $search = ''): array
    {
        $params = [];
        $searchSql = '';

        if ($search !== '') {
            $searchSql = "AND i.`Nombre` LIKE :buscar";
            $params['buscar'] = '%' . $search . '%';
        }

        $sql = "SELECT i.`ID`, i.`Nombre`, i.`Existencia`,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos` i
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE i.`TipoProducto` = 'INTE'
                  AND i.`Activo` = 1
                  AND i.`Existencia` > 0
                  {$searchSql}
                ORDER BY i.`Nombre` ASC
                LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lista insumos que enfermería tiene en stock (para devoluciones).
     * Soporta búsqueda por nombre.
     */
    public function getInsumosEnfermeria(string $search = ''): array
    {
        $params = [];
        $searchSql = '';

        if ($search !== '') {
            $searchSql = "AND i.`Nombre` LIKE :buscar";
            $params['buscar'] = '%' . $search . '%';
        }

        $sql = "SELECT i.`ID`, i.`Nombre`, ie.`Existencia`,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos_enfermeria` ie
                INNER JOIN `insumos` i      ON ie.`InsumoID` = i.`ID`
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE i.`TipoProducto` = 'INTE'
                  AND i.`Activo` = 1
                  AND ie.`Activo` = 1
                  AND ie.`Existencia` > 0
                  {$searchSql}
                ORDER BY i.`Nombre` ASC
                LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
