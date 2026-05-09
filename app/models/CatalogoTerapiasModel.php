<?php
/**
 * CatalogoTerapiasModel — Modelo para el catálogo de Terapias y su detalle
 *
 * Gestiona inserciones/ediciones con transacciones para asegurar
 * integridad entre catalogo_terapias y detalle_terapias.
 */
class CatalogoTerapiasModel extends BaseModel
{
    protected string $table = 'catalogo_terapias';

    /**
     * Obtiene el listado de terapias activas
     */
    public function findAllActives(int $limit = 1000, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE Activo = 1 ORDER BY nom_terapia ASC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una terapia por ID
     */
    public function findById(mixed $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_terapiaINV = :id AND Activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene el detalle de insumos de una terapia
     */
    public function getDetalle(int $id_terapia): array
    {
        $sql = "SELECT dt.*, i.Nombre as InsumoNombre, m.Descripcion as MedidaDescripcion
                FROM detalle_terapias dt
                INNER JOIN insumos i ON dt.idInsumo = i.ID
                INNER JOIN medidainsumo m ON i.MedidaInsumo = m.ID
                WHERE dt.id_terapiaINV = :id
                ORDER BY i.Nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_terapia]);
        return $stmt->fetchAll();
    }

    /**
     * Guarda (crea) una terapia y sus detalles
     * 
     * @param array $terapiaData Datos de la cabecera
     * @param array $insumos Array de arrays con ['idInsumo' => X, 'cantidad' => Y]
     * @return int|bool El ID de la terapia generada o false en caso de error
     */
    public function createTerapia(array $terapiaData, array $insumos)
    {
        try {
            $this->db->beginTransaction();

            $sqlMaster = "INSERT INTO catalogo_terapias (id_terapiaERP, nom_terapia, UsuarioCreacion, UsuarioActualiza) 
                          VALUES (:id_erp, :nombre, :usrC, :usrA)";
            $stmtMaster = $this->db->prepare($sqlMaster);
            $stmtMaster->execute([
                'id_erp' => $terapiaData['id_terapiaERP'],
                'nombre' => $terapiaData['nom_terapia'],
                'usrC'   => $_SESSION['username'] ?? 'admin',
                'usrA'   => $_SESSION['username'] ?? 'admin'
            ]);

            $id_terapiaINV = (int) $this->db->lastInsertId();

            if (!empty($insumos)) {
                $sqlDetail = "INSERT INTO detalle_terapias (id_terapiaINV, id_terapiaERP, idInsumo, CantidadInsumo) 
                              VALUES (:id_inv, :id_erp, :id_insumo, :cantidad)";
                $stmtDetail = $this->db->prepare($sqlDetail);
                foreach ($insumos as $insumo) {
                    $stmtDetail->execute([
                        'id_inv'    => $id_terapiaINV,
                        'id_erp'    => $terapiaData['id_terapiaERP'],
                        'id_insumo' => $insumo['idInsumo'],
                        'cantidad'  => $insumo['cantidad']
                    ]);
                }
            }

            $this->db->commit();
            return $id_terapiaINV;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear terapia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una terapia existente y regenera su detalle
     */
    public function updateTerapia(int $id_terapiaINV, array $terapiaData, array $insumos)
    {
        try {
            $this->db->beginTransaction();

            $sqlMaster = "UPDATE catalogo_terapias 
                          SET id_terapiaERP = :id_erp, nom_terapia = :nombre, UsuarioActualiza = :usrA 
                          WHERE id_terapiaINV = :id_inv";
            $stmtMaster = $this->db->prepare($sqlMaster);
            $stmtMaster->execute([
                'id_erp' => $terapiaData['id_terapiaERP'],
                'nombre' => $terapiaData['nom_terapia'],
                'usrA'   => $_SESSION['username'] ?? 'admin',
                'id_inv' => $id_terapiaINV
            ]);

            // Borrar detalles antiguos
            $sqlDelete = "DELETE FROM detalle_terapias WHERE id_terapiaINV = :id_inv";
            $stmtDel = $this->db->prepare($sqlDelete);
            $stmtDel->execute(['id_inv' => $id_terapiaINV]);

            // Insertar nuevos detalles
            if (!empty($insumos)) {
                $sqlDetail = "INSERT INTO detalle_terapias (id_terapiaINV, id_terapiaERP, idInsumo, CantidadInsumo) 
                              VALUES (:id_inv, :id_erp, :id_insumo, :cantidad)";
                $stmtDetail = $this->db->prepare($sqlDetail);
                foreach ($insumos as $insumo) {
                    $stmtDetail->execute([
                        'id_inv'    => $id_terapiaINV,
                        'id_erp'    => $terapiaData['id_terapiaERP'],
                        'id_insumo' => $insumo['idInsumo'],
                        'cantidad'  => $insumo['cantidad']
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar terapia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Borrado lógico
     */
    public function deactivate(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET Activo = 0, UsuarioActualiza = :usr WHERE id_terapiaINV = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usr' => $_SESSION['username'] ?? 'admin',
            'id'  => $id
        ]);
    }
}
