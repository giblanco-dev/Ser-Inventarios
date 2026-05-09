<?php
/**
 * UbicacionModel — Catálogo de Ubicaciones Físicas
 */
class UbicacionModel extends BaseModel
{
    protected string $table = 'ubicaciones';

    /**
     * Verifica si hay activos electrónicos activos asociados a esta ubicación.
     *
     * @param int $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos_electronicos`
                WHERE `UbicacionID` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo las ubicaciones activas (para selects de formularios).
     *
     * @return array
     */
    public function findActivas(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`Descripcion` ASC');
    }
}
