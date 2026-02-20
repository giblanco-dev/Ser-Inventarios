<?php
/**
 * ConceptoMovimientoModel — Catálogo de Conceptos de Movimiento
 */
class ConceptoMovimientoModel extends BaseModel
{
    protected string $table = 'conceptomovimiento';

    /**
     * Verifica si existen movimientos registrados con este concepto.
     *
     * @param string $id
     * @return bool true si hay movimientos
     */
    public function hasDependencies(string $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `movimientos` WHERE `ConceptoID` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo los conceptos activos.
     */
    public function findActive(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`TipoOperacion` ASC, `Descripcion` ASC');
    }
}
