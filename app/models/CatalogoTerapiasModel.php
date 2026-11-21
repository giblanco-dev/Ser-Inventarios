<?php
/**
 * CatalogoTerapiasModel — Modelo para el catálogo de Terapias y su detalle
 *
 * catalogo_terapias es un espejo LOCAL de ser.terapias (ERP): el alta y baja
 * de la terapia en sí se gobierna desde el ERP y llega aquí vía
 * sincronizarDesdeErp() (solo lectura sobre el schema `ser`). Este modelo
 * solo gestiona localmente qué insumos consume cada terapia.
 */
class CatalogoTerapiasModel extends BaseModel
{
    protected string $table = 'catalogo_terapias';
    protected string $primaryKey = 'id_terapiaINV';

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
     * Sincroniza catalogo_terapias con la tabla real ser.terapias (solo
     * lectura sobre el schema del ERP). Da de alta/actualiza las terapias
     * vigentes y refleja el estado Activo/Inactivo que tiene cada una en
     * el ERP.
     *
     * @return array{altas: int, actualizados: int, bajas: int}
     */
    public function sincronizarDesdeErp(): array
    {
        $usuario = $_SESSION['username'] ?? 'admin';
        $resumen = ['altas' => 0, 'actualizados' => 0, 'bajas' => 0];

        try {
            $this->db->beginTransaction();

            $sqlErp = "SELECT id_terapia, nom_terapia, activo FROM " . ERP_DB_SCHEMA . ".terapias";
            $terapias = $this->db->query($sqlErp)->fetchAll();

            $idsErp = [];
            $sqlUpsert = "INSERT INTO catalogo_terapias (id_terapiaINV, id_terapiaERP, nom_terapia, Activo, UsuarioCreacion, UsuarioActualiza)
                          VALUES (:id_inv, :id_erp, :nombre, :activo, :usr_creacion, :usr_actualiza)
                          ON DUPLICATE KEY UPDATE
                              id_terapiaERP = VALUES(id_terapiaERP),
                              nom_terapia = VALUES(nom_terapia),
                              Activo = VALUES(Activo),
                              UsuarioActualiza = VALUES(UsuarioActualiza)";
            $stmtUpsert = $this->db->prepare($sqlUpsert);

            foreach ($terapias as $terapia) {
                $idsErp[] = (int) $terapia['id_terapia'];
                $stmtUpsert->execute([
                    'id_inv'        => $terapia['id_terapia'],
                    'id_erp'        => $terapia['id_terapia'],
                    'nombre'        => $terapia['nom_terapia'],
                    'activo'        => (int) $terapia['activo'],
                    'usr_creacion'  => $usuario,
                    'usr_actualiza' => $usuario,
                ]);
                if ($stmtUpsert->rowCount() === 1) {
                    $resumen['altas']++;
                } elseif ($stmtUpsert->rowCount() === 2) {
                    $resumen['actualizados']++;
                }
            }

            if (!empty($idsErp)) {
                $placeholders = implode(',', array_fill(0, count($idsErp), '?'));
                $sqlBaja = "UPDATE catalogo_terapias SET Activo = 0, UsuarioActualiza = ?
                            WHERE Activo = 1 AND id_terapiaINV NOT IN ({$placeholders})";
                $stmtBaja = $this->db->prepare($sqlBaja);
                $stmtBaja->execute([$usuario, ...$idsErp]);
                $resumen['bajas'] = $stmtBaja->rowCount();
            }

            $this->db->commit();
            return $resumen;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al sincronizar terapias desde el ERP: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Regenera el detalle de insumos de una terapia (delete + reinsert transaccional)
     *
     * @param int   $id_terapiaINV
     * @param array $insumos Array de arrays con ['idInsumo' => X, 'cantidad' => Y]
     */
    public function updateDetalle(int $id_terapiaINV, array $insumos): bool
    {
        try {
            $this->db->beginTransaction();

            $sqlDelete = "DELETE FROM detalle_terapias WHERE id_terapiaINV = :id_inv";
            $stmtDel = $this->db->prepare($sqlDelete);
            $stmtDel->execute(['id_inv' => $id_terapiaINV]);

            if (!empty($insumos)) {
                $sqlDetail = "INSERT INTO detalle_terapias (id_terapiaINV, id_terapiaERP, idInsumo, CantidadInsumo)
                              VALUES (:id_inv, :id_erp, :id_insumo, :cantidad)";
                $stmtDetail = $this->db->prepare($sqlDetail);
                foreach ($insumos as $insumo) {
                    $stmtDetail->execute([
                        'id_inv'    => $id_terapiaINV,
                        'id_erp'    => $id_terapiaINV,
                        'id_insumo' => $insumo['idInsumo'],
                        'cantidad'  => $insumo['cantidad'],
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar insumos de la terapia: " . $e->getMessage());
            return false;
        }
    }
}
