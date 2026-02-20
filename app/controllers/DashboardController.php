<?php
/**
 * DashboardController — Pantalla principal con KPIs y alertas
 */
require_once APP_ROOT . '/app/models/DashboardModel.php';

class DashboardController
{
    private DashboardModel $model;

    public function __construct()
    {
        $this->model = new DashboardModel();
    }

    /**
     * Muestra el dashboard principal.
     */
    public function index(): void
    {
        $data = [
            'pageTitle'          => 'Dashboard',
            'activeMenu'         => 'dashboard',
            'totalActivos'       => $this->model->totalInsumosActivos(),
            'totalCritico'       => $this->model->totalStockCritico(),
            'totalBajo'          => $this->model->totalStockBajo(),
            'totalVencimientos'  => $this->model->totalVencimientosProximos(),
            'stockCritico'       => $this->model->topStockCritico(),
            'alertasVencimiento' => $this->model->alertasVencimiento(),
            'actividadReciente'  => $this->model->actividadReciente(),
        ];

        renderView('dashboard/index', $data);
    }
}
