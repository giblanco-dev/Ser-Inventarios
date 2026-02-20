<?php
/**
 * TipoInsumoModel — Catálogo de Tipos de Insumo
 */
class TipoInsumoModel extends BaseModel
{
    protected string $table = 'tipoinsumo';

    /**
     * Verifica si hay insumos activos usando este tipo.
     *
     * @param string $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(string $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos` WHERE `TipoProducto` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo los tipos activos.
     */
    public function findActive(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`Descripcion` ASC');
    }
}
