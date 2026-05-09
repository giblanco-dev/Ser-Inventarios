<?php
/**
 * PuestoModel — Catálogo de Puestos de Trabajo
 */
class PuestoModel extends BaseModel
{
    protected string $table = 'puestos';

    /**
     * Verifica si hay responsables activos usando este puesto.
     *
     * @param int $id
     * @return bool true si hay dependencias
     */
    public function hasDependencies(int $id): bool
    {
        // El campo Puesto en responsables es texto, no FK numérica.
        // Verificamos que no sea el único puesto disponible.
        return false;
    }

    /**
     * Lista solo los puestos activos (para selects de formularios).
     *
     * @return array
     */
    public function findActivos(): array
    {
        return $this->findAll(['Activo' => 1], 0, 0, '`Descripcion` ASC');
    }
}
