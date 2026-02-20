<?php
/**
 * BaseModel — Modelo base con operaciones CRUD estándar
 *
 * Repository Pattern simplificado.
 * Todos los queries usan Prepared Statements.
 * Los modelos hijos definen $table y $primaryKey.
 */
abstract class BaseModel
{
    /** @var string Nombre de la tabla */
    protected string $table;

    /** @var string Clave primaria */
    protected string $primaryKey = 'ID';

    /** @var PDO Instancia de BD */
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los registros con filtros opcionales, paginación y orden.
     *
     * @param array  $filters  ['campo' => valor] o ['campo:op' => valor] donde op = LIKE
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @return array
     */
    public function findAll(array $filters = [], int $limit = 0, int $offset = 0, string $orderBy = ''): array
    {
        $where  = [];
        $params = [];

        foreach ($filters as $key => $value) {
            if (str_contains($key, ':')) {
                [$field, $op] = explode(':', $key, 2);
                if (strtoupper($op) === 'LIKE') {
                    $where[]  = "`{$field}` LIKE :f_{$field}";
                    $params["f_{$field}"] = "%{$value}%";
                }
            } else {
                $where[]  = "`{$key}` = :f_{$key}";
                $params["f_{$key}"] = $value;
            }
        }

        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta registros con filtros.
     *
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        $where  = [];
        $params = [];

        foreach ($filters as $key => $value) {
            if (str_contains($key, ':')) {
                [$field, $op] = explode(':', $key, 2);
                if (strtoupper($op) === 'LIKE') {
                    $where[]  = "`{$field}` LIKE :f_{$field}";
                    $params["f_{$field}"] = "%{$value}%";
                }
            } else {
                $where[]  = "`{$key}` = :f_{$key}";
                $params["f_{$key}"] = $value;
            }
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca un registro por su ID.
     *
     * @param mixed $id
     * @return array|null
     */
    public function findById(mixed $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crea un registro nuevo.
     *
     * @param array $data ['campo' => valor]
     * @return mixed ID insertado (o true para PKs alfanuméricas)
     */
    public function create(array $data): mixed
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        $lastId = $this->db->lastInsertId();
        return $lastId ?: true;
    }

    /**
     * Actualiza un registro por su ID.
     *
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update(mixed $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "`{$col}` = :{$col}";
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets)
             . " WHERE `{$this->primaryKey}` = :pk_id";
        $data['pk_id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Baja lógica: pone Activo = 0.
     *
     * @param mixed $id
     * @return bool
     */
    public function softDelete(mixed $id): bool
    {
        return $this->update($id, ['Activo' => 0]);
    }

    /**
     * Reactiva un registro: pone Activo = 1.
     *
     * @param mixed $id
     * @return bool
     */
    public function restore(mixed $id): bool
    {
        return $this->update($id, ['Activo' => 1]);
    }
}
