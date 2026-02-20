<?php
/**
 * MovimientoModel — Registro transaccional de movimientos de inventario
 *
 * La lógica de negocio central: registrar movimientos y actualizar existencias
 * dentro de una transacción atómica. Los movimientos son inmutables una vez registrados.
 */
class MovimientoModel extends BaseModel
{
    protected string $table = 'movimientos';

    /**
     * Registra un movimiento completo (cabecera + detalle) y actualiza existencias.
     *
     * @param array $cabecera  ['ConceptoID', 'Referencia', 'Observaciones']
     * @param array $detalles  [['InsumoID', 'Cantidad', 'NumeroLote'?, 'FechaVencimiento'?], ...]
     * @return int ID del movimiento creado
     * @throws Exception Si hay error de validación o de BD
     */
    public function registrar(array $cabecera, array $detalles): int
    {
        // Obtener la dirección del concepto
        $sqlConcepto = "SELECT `TipoOperacion` FROM `conceptomovimiento` WHERE `ID` = :id AND `Activo` = 1";
        $stmt = $this->db->prepare($sqlConcepto);
        $stmt->execute(['id' => $cabecera['ConceptoID']]);
        $concepto = $stmt->fetch();

        if (!$concepto) {
            throw new Exception('El concepto de movimiento no es válido o está inactivo.');
        }

        $tipoOp = $concepto['TipoOperacion']; // ENTRADA o SALIDA

        // Validar que no quede stock negativo en salidas
        if ($tipoOp === 'SALIDA') {
            foreach ($detalles as $linea) {
                $sqlStock = "SELECT `Existencia`, `Nombre` FROM `insumos` WHERE `ID` = :id FOR UPDATE";
                // Esta validación se hace dentro de la transacción abajo
            }
        }

        $this->db->beginTransaction();

        try {
            // 1. Insertar cabecera del movimiento
            $sqlMov = "INSERT INTO `movimientos` (`ConceptoID`, `Referencia`, `Observaciones`, `UsuarioRegistra`)
                       VALUES (:conceptoId, :referencia, :observaciones, :usuario)";
            $stmtMov = $this->db->prepare($sqlMov);
            $stmtMov->execute([
                'conceptoId'    => $cabecera['ConceptoID'],
                'referencia'    => $cabecera['Referencia'] ?? null,
                'observaciones' => $cabecera['Observaciones'] ?? null,
                'usuario'       => $cabecera['UsuarioRegistra'] ?? 'Sistema',
            ]);
            $movimientoId = (int) $this->db->lastInsertId();

            // 2. Insertar cada línea de detalle y actualizar existencia
            $sqlDetalle = "INSERT INTO `movimientodetalle`
                           (`MovimientoID`, `InsumoID`, `Cantidad`, `NumeroLote`, `FechaVencimiento`)
                           VALUES (:movId, :insumoId, :cantidad, :lote, :vencimiento)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            foreach ($detalles as $linea) {
                $insumoId = (int) $linea['InsumoID'];
                $cantidad = (float) $linea['Cantidad'];

                // Validar stock para salidas (con bloqueo FOR UPDATE)
                if ($tipoOp === 'SALIDA') {
                    $sqlCheck = "SELECT `Existencia`, `Nombre` FROM `insumos` WHERE `ID` = :id FOR UPDATE";
                    $stmtCheck = $this->db->prepare($sqlCheck);
                    $stmtCheck->execute(['id' => $insumoId]);
                    $insumo = $stmtCheck->fetch();

                    if (!$insumo) {
                        throw new Exception("El insumo con ID {$insumoId} no existe.");
                    }

                    if (($insumo['Existencia'] - $cantidad) < 0) {
                        throw new Exception(
                            "Stock insuficiente para \"{$insumo['Nombre']}\". "
                            . "Existencia actual: {$insumo['Existencia']}, Cantidad solicitada: {$cantidad}."
                        );
                    }
                }

                // Insertar línea de detalle
                $stmtDetalle->execute([
                    'movId'       => $movimientoId,
                    'insumoId'    => $insumoId,
                    'cantidad'    => $cantidad,
                    'lote'        => $linea['NumeroLote'] ?? null,
                    'vencimiento' => $linea['FechaVencimiento'] ?? null,
                ]);

                // Actualizar existencia del insumo
                $op = ($tipoOp === 'ENTRADA') ? '+' : '-';
                $sqlUpdate = "UPDATE `insumos` SET `Existencia` = `Existencia` {$op} :cantidad WHERE `ID` = :id";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    'cantidad' => $cantidad,
                    'id'       => $insumoId,
                ]);
            }

            $this->db->commit();
            return $movimientoId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el historial de movimientos con filtros.
     */
    public function getHistorial(array $filters = [], int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "m.`FechaMovimiento` >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "m.`FechaMovimiento` <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['concepto'])) {
            $where[] = "m.`ConceptoID` = :concepto";
            $params['concepto'] = $filters['concepto'];
        }
        if (!empty($filters['tipo_operacion'])) {
            $where[] = "cm.`TipoOperacion` = :tipo_op";
            $params['tipo_op'] = $filters['tipo_operacion'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT m.`ID`, m.`FechaMovimiento`, m.`ConceptoID`,
                       cm.`Descripcion` AS ConceptoDescripcion,
                       cm.`TipoOperacion`, m.`Referencia`, m.`Observaciones`,
                       m.`UsuarioRegistra`,
                       COUNT(md.`ID`) AS TotalLineas,
                       SUM(md.`Cantidad`) AS TotalCantidad
                FROM `movimientos` m
                INNER JOIN `conceptomovimiento` cm ON m.`ConceptoID` = cm.`ID`
                INNER JOIN `movimientodetalle` md ON md.`MovimientoID` = m.`ID`
                {$whereSql}
                GROUP BY m.`ID`
                ORDER BY m.`FechaMovimiento` DESC, m.`ID` DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta movimientos con filtros.
     */
    public function countHistorial(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "m.`FechaMovimiento` >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "m.`FechaMovimiento` <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['concepto'])) {
            $where[] = "m.`ConceptoID` = :concepto";
            $params['concepto'] = $filters['concepto'];
        }
        if (!empty($filters['tipo_operacion'])) {
            $where[] = "cm.`TipoOperacion` = :tipo_op";
            $params['tipo_op'] = $filters['tipo_operacion'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(DISTINCT m.`ID`)
                FROM `movimientos` m
                INNER JOIN `conceptomovimiento` cm ON m.`ConceptoID` = cm.`ID`
                {$whereSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene el detalle completo de un movimiento.
     */
    public function getDetalleMovimiento(int $movimientoId): array
    {
        $sql = "SELECT md.*, i.`Nombre` AS InsumoNombre, me.`Descripcion` AS MedidaDescripcion
                FROM `movimientodetalle` md
                INNER JOIN `insumos` i ON md.`InsumoID` = i.`ID`
                INNER JOIN `medidainsumo` me ON i.`MedidaInsumo` = me.`ID`
                WHERE md.`MovimientoID` = :movId
                ORDER BY md.`ID` ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['movId' => $movimientoId]);
        return $stmt->fetchAll();
    }
}
