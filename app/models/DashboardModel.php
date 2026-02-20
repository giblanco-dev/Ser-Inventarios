<?php
/**
 * DashboardModel — Consultas KPI y alertas del Dashboard
 */
class DashboardModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Total de insumos activos.
     */
    public function totalInsumosActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `insumos` WHERE `Activo` = 1");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Insumos con stock CRÍTICO (existencia <= 0).
     */
    public function totalStockCritico(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `insumos` WHERE `Activo` = 1 AND `Existencia` <= 0");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Insumos con stock BAJO (existencia > 0 Y <= Minimos).
     */
    public function totalStockBajo(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `insumos` WHERE `Activo` = 1 AND `Existencia` > 0 AND `Existencia` <= `Minimos`");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Total de vencimientos próximos.
     */
    public function totalVencimientosProximos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `v_alertas_vencimiento`");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Top insumos con stock CRÍTICO.
     */
    public function topStockCritico(int $limit = 15): array
    {
        $sql = "SELECT `ID`, `Nombre`, `Existencia`, `Minimos`, `EstadoStock`,
                       `TipoDescripcion`, `MedidaDescripcion`
                FROM `v_stock_actual`
                WHERE `EstadoStock` IN ('CRITICO','BAJO')
                ORDER BY `Existencia` ASC, `Nombre` ASC
                LIMIT {$limit}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lotes próximos a vencer.
     */
    public function alertasVencimiento(int $limit = 15): array
    {
        $sql = "SELECT * FROM `v_alertas_vencimiento` LIMIT {$limit}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Últimos movimientos registrados.
     */
    public function actividadReciente(int $limit = 10): array
    {
        $sql = "SELECT m.`ID`, m.`FechaMovimiento`,
                       cm.`Descripcion` AS ConceptoDescripcion,
                       cm.`TipoOperacion`,
                       m.`Referencia`,
                       m.`UsuarioRegistra`,
                       COUNT(md.`ID`) AS TotalLineas
                FROM `movimientos` m
                INNER JOIN `conceptomovimiento` cm ON m.`ConceptoID` = cm.`ID`
                INNER JOIN `movimientodetalle` md ON md.`MovimientoID` = m.`ID`
                GROUP BY m.`ID`
                ORDER BY m.`FechaMovimiento` DESC
                LIMIT {$limit}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
