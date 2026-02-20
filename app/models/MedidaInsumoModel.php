<?php
/**
 * MedidaInsumoModel — Catálogo de Unidades de Medida
 */
class MedidaInsumoModel extends BaseModel
{
    protected string $table = 'medidainsumo';

    /**
     * Verifica si hay insumos activos usando esta unidad de medida.
     *
     * @param string $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(string $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos` WHERE `MedidaInsumo` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo las medidas activas (para selects de formularios).
     *
     * @return array
     */
    public function findActive(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`Descripcion` ASC');
    }
}
