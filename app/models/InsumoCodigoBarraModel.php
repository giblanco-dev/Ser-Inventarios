<?php
/**
 * InsumoCodigoBarraModel — Códigos de Barra por Insumo
 */
class InsumoCodigoBarraModel extends BaseModel
{
    protected string $table = 'insumocodigobarra';

    /**
     * Lista todos los códigos de un insumo específico.
     */
    public function findByInsumo(int $insumoId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `InsumoID` = :insumoId ORDER BY `Activo` DESC, `CreatedAt` DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['insumoId' => $insumoId]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica unicidad global del código de barra.
     *
     * @param string   $codigo
     * @param int|null $excludeId ID a excluir (para edición)
     * @return bool true si ya existe
     */
    public function codigoExists(string $codigo, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE `CodigoBarra` = :codigo";
        $params = ['codigo' => $codigo];

        if ($excludeId !== null) {
            $sql .= " AND `ID` != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
