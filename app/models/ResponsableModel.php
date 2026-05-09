<?php
/**
 * ResponsableModel — Directorio de Responsables de Activos Electrónicos
 */
class ResponsableModel extends BaseModel
{
    protected string $table = 'responsables';

    /**
     * Verifica si hay activos electrónicos activos asignados a este responsable.
     *
     * @param int $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos_electronicos`
                WHERE `ResponsableID` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo los responsables activos (para selects de formularios).
     *
     * @return array
     */
    public function findActivos(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`NombreResponsable` ASC');
    }

    /**
     * Busca responsables por nombre para filtros.
     *
     * @param string $nombre
     * @return array
     */
    public function searchByNombre(string $nombre): array
    {
        $sql = "SELECT * FROM `responsables`
                WHERE `NombreResponsable` LIKE :nombre
                ORDER BY `NombreResponsable` ASC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre' => '%' . $nombre . '%']);
        return $stmt->fetchAll();
    }
}
