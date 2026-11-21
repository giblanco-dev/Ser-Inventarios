<?php
/**
 * CatalogoSuerosModel — Modelo para el catálogo de Sueros y su detalle
 *
 * catalogo_sueros es un espejo LOCAL de ser.sueros (ERP): el alta y baja
 * del suero en sí se gobierna desde el ERP y llega aquí vía
 * sincronizarDesdeErp() (solo lectura sobre el schema `ser`). Este modelo
 * solo gestiona localmente qué insumos consume cada suero.
 */
class CatalogoSuerosModel extends BaseModel
{
    protected string $table = 'catalogo_sueros';
    protected string $primaryKey = 'id_suero';

    /**
     * Obtiene el listado de sueros activos
     */
    public function findAllActives(int $limit = 1000, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE Activo = 1 ORDER BY nom_suero ASC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un suero activo por ID
     */
    public function findById(mixed $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_suero = :id AND Activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene el detalle de insumos de un suero
     */
    public function getDetalle(int $id_suero): array
    {
        $sql = "SELECT ds.*, i.Nombre as InsumoNombre, m.Descripcion as MedidaDescripcion
                FROM detalle_sueros ds
                INNER JOIN insumos i ON ds.idInsumo = i.ID
                INNER JOIN medidainsumo m ON i.MedidaInsumo = m.ID
                WHERE ds.id_suero = :id
                ORDER BY i.Nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_suero]);
        return $stmt->fetchAll();
    }

    /**
     * Sincroniza catalogo_sueros con la tabla real ser.sueros (solo lectura
     * sobre el schema del ERP). Da de alta/actualiza los sueros vigentes y
     * desactiva (baja lógica) los que ya no existan en el ERP.
     *
     * @return array{altas: int, actualizados: int, bajas: int}
     */
    public function sincronizarDesdeErp(): array
    {
        $usuario = $_SESSION['username'] ?? 'admin';
        $resumen = ['altas' => 0, 'actualizados' => 0, 'bajas' => 0];

        try {
            $this->db->beginTransaction();

            $sqlErp = "SELECT id_suero, nom_suero FROM " . ERP_DB_SCHEMA . ".sueros";
            $sueros = $this->db->query($sqlErp)->fetchAll();

            $idsErp = [];
            $sqlUpsert = "INSERT INTO catalogo_sueros (id_suero, nom_suero, UsuarioCreacion, UsuarioActualiza)
                          VALUES (:id, :nombre, :usr_creacion, :usr_actualiza)
                          ON DUPLICATE KEY UPDATE
                              nom_suero = VALUES(nom_suero),
                              Activo = 1,
                              UsuarioActualiza = VALUES(UsuarioActualiza)";
            $stmtUpsert = $this->db->prepare($sqlUpsert);

            foreach ($sueros as $suero) {
                $idsErp[] = (int) $suero['id_suero'];
                $stmtUpsert->execute([
                    'id'            => $suero['id_suero'],
                    'nombre'        => $suero['nom_suero'],
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
                $sqlBaja = "UPDATE catalogo_sueros SET Activo = 0, UsuarioActualiza = ?
                            WHERE Activo = 1 AND id_suero NOT IN ({$placeholders})";
                $stmtBaja = $this->db->prepare($sqlBaja);
                $stmtBaja->execute([$usuario, ...$idsErp]);
                $resumen['bajas'] = $stmtBaja->rowCount();
            }

            $this->db->commit();
            return $resumen;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al sincronizar sueros desde el ERP: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Regenera el detalle de insumos de un suero (delete + reinsert transaccional)
     *
     * @param int   $id_suero
     * @param array $insumos Array de arrays con ['idInsumo' => X, 'cantidad' => Y]
     */
    public function updateDetalle(int $id_suero, array $insumos): bool
    {
        try {
            $this->db->beginTransaction();

            $sqlDelete = "DELETE FROM detalle_sueros WHERE id_suero = :id_suero";
            $stmtDel = $this->db->prepare($sqlDelete);
            $stmtDel->execute(['id_suero' => $id_suero]);

            if (!empty($insumos)) {
                $sqlDetail = "INSERT INTO detalle_sueros (id_suero, idInsumo, CantidadInsumo)
                              VALUES (:id_suero, :id_insumo, :cantidad)";
                $stmtDetail = $this->db->prepare($sqlDetail);
                foreach ($insumos as $insumo) {
                    $stmtDetail->execute([
                        'id_suero'  => $id_suero,
                        'id_insumo' => $insumo['idInsumo'],
                        'cantidad'  => $insumo['cantidad'],
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar insumos del suero: " . $e->getMessage());
            return false;
        }
    }
}
