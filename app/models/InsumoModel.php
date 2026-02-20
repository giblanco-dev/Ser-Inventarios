<?php
/**
 * InsumoModel — Modelo central de Insumos
 *
 * Gestiona el catálogo maestro. El campo Existencia se actualiza
 * exclusivamente a través de MovimientoModel::registrar().
 */
class InsumoModel extends BaseModel
{
    protected string $table = 'insumos';

    /**
     * Lista insumos con datos de tipo y medida (JOIN).
     * Soporta filtros por tipo, medida, estado, caducidad y búsqueda.
     *
     * @param array $filters Filtros de la UI
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function findAllWithDetails(array $filters = [], int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        // Filtro por búsqueda de nombre
        if (!empty($filters['busqueda'])) {
            $where[] = "i.`Nombre` LIKE :busqueda";
            $params['busqueda'] = "%{$filters['busqueda']}%";
        }

        // Filtro por tipo
        if (!empty($filters['tipo'])) {
            $where[] = "i.`TipoProducto` = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        // Filtro por medida
        if (!empty($filters['medida'])) {
            $where[] = "i.`MedidaInsumo` = :medida";
            $params['medida'] = $filters['medida'];
        }

        // Filtro por estado activo
        if (isset($filters['activo']) && $filters['activo'] !== '') {
            $where[] = "i.`Activo` = :activo";
            $params['activo'] = (int)$filters['activo'];
        }

        // Filtro por caducidad
        if (isset($filters['caducidad']) && $filters['caducidad'] !== '') {
            $where[] = "i.`Caducidad` = :caducidad";
            $params['caducidad'] = (int)$filters['caducidad'];
        }

        // Filtro por estado de stock
        if (!empty($filters['estado_stock'])) {
            switch ($filters['estado_stock']) {
                case 'CRITICO':
                    $where[] = "i.`Existencia` <= 0";
                    break;
                case 'BAJO':
                    $where[] = "(i.`Existencia` > 0 AND i.`Existencia` <= i.`Minimos`)";
                    break;
                case 'OK':
                    $where[] = "i.`Existencia` > i.`Minimos`";
                    break;
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT i.*, t.`Descripcion` AS TipoDescripcion,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos` i
                INNER JOIN `tipoinsumo` t   ON i.`TipoProducto` = t.`ID`
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                {$whereSql}
                ORDER BY i.`Nombre` ASC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta insumos con los mismos filtros.
     */
    public function countWithFilters(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['busqueda'])) {
            $where[] = "i.`Nombre` LIKE :busqueda";
            $params['busqueda'] = "%{$filters['busqueda']}%";
        }
        if (!empty($filters['tipo'])) {
            $where[] = "i.`TipoProducto` = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['medida'])) {
            $where[] = "i.`MedidaInsumo` = :medida";
            $params['medida'] = $filters['medida'];
        }
        if (isset($filters['activo']) && $filters['activo'] !== '') {
            $where[] = "i.`Activo` = :activo";
            $params['activo'] = (int)$filters['activo'];
        }
        if (isset($filters['caducidad']) && $filters['caducidad'] !== '') {
            $where[] = "i.`Caducidad` = :caducidad";
            $params['caducidad'] = (int)$filters['caducidad'];
        }
        if (!empty($filters['estado_stock'])) {
            switch ($filters['estado_stock']) {
                case 'CRITICO':
                    $where[] = "i.`Existencia` <= 0";
                    break;
                case 'BAJO':
                    $where[] = "(i.`Existencia` > 0 AND i.`Existencia` <= i.`Minimos`)";
                    break;
                case 'OK':
                    $where[] = "i.`Existencia` > i.`Minimos`";
                    break;
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM `insumos` i {$whereSql}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene un insumo con sus datos de tipo y medida.
     */
    public function findByIdWithDetails(int $id): ?array
    {
        $sql = "SELECT i.*, t.`Descripcion` AS TipoDescripcion,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos` i
                INNER JOIN `tipoinsumo` t   ON i.`TipoProducto` = t.`ID`
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE i.`ID` = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca insumos por nombre (para búsqueda AJAX en movimientos).
     *
     * @param string $term
     * @param int    $limit
     * @return array
     */
    public function searchByName(string $term, int $limit = 10): array
    {
        $sql = "SELECT i.`ID`, i.`Nombre`, i.`Existencia`, i.`Caducidad`,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos` i
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                WHERE i.`Activo` = 1 AND i.`Nombre` LIKE :term
                ORDER BY i.`Nombre` ASC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('term', "%{$term}%", PDO::PARAM_STR);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca insumo por código de barra.
     */
    public function findByBarcode(string $code): ?array
    {
        $sql = "SELECT i.`ID`, i.`Nombre`, i.`Existencia`, i.`Caducidad`,
                       m.`Descripcion` AS MedidaDescripcion
                FROM `insumos` i
                INNER JOIN `medidainsumo` m ON i.`MedidaInsumo` = m.`ID`
                INNER JOIN `insumocodigobarra` cb ON cb.`InsumoID` = i.`ID`
                WHERE cb.`CodigoBarra` = :code AND cb.`Activo` = 1 AND i.`Activo` = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
