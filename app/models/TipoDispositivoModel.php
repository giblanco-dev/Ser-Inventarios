<?php
/**
 * TipoDispositivoModel — Catálogo de Tipos de Dispositivo
 */
class TipoDispositivoModel extends BaseModel
{
    protected string $table = 'tipo_dispositivo';

    /**
     * Verifica si hay activos electrónicos activos con este tipo de dispositivo.
     *
     * @param int $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM `insumos_electronicos`
                WHERE `TipoDispositivoID` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lista solo los tipos activos (para selects de formularios).
     *
     * @return array
     */
    public function findActivos(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`Descripcion` ASC');
    }
}
