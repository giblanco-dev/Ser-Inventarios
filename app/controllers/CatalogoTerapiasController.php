<?php
/**
 * CatalogoTerapiasController — Controlador para el Módulo de Terapias
 *
 * El alta/baja de la terapia en sí se gobierna desde el ERP (ser.terapias)
 * y llega aquí vía sincronización manual. Este controlador solo gestiona
 * la sincronización y qué insumos consume cada terapia.
 */
require_once APP_ROOT . '/app/models/CatalogoTerapiasModel.php';
require_once APP_ROOT . '/app/models/InsumoModel.php';

class CatalogoTerapiasController
{
    private CatalogoTerapiasModel $terapiaModel;
    private InsumoModel $insumoModel;

    public function __construct()
    {
        $this->terapiaModel = new CatalogoTerapiasModel();
        $this->insumoModel = new InsumoModel();
    }

    /**
     * Listado de Terapias
     */
    public function index()
    {
        $terapias = $this->terapiaModel->findAllActives();

        renderView('catalogo_terapias/index', [
            'pageTitle'  => 'Catálogo de Terapias',
            'activeMenu' => 'catalogo-terapias',
            'terapias'   => $terapias
        ]);
    }

    /**
     * Sincroniza el catálogo local con ser.terapias (ERP)
     */
    public function sync()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-terapias');
        }
        verifyCsrf();

        try {
            $resumen = $this->terapiaModel->sincronizarDesdeErp();
            flashMessage('success', sprintf(
                'Sincronización completada: %d altas, %d actualizados, %d bajas.',
                $resumen['altas'],
                $resumen['actualizados'],
                $resumen['bajas']
            ));
        } catch (Exception $e) {
            flashMessage('error', 'Ocurrió un error al sincronizar con el ERP.');
        }

        redirect('catalogo-terapias');
    }

    /**
     * Formulario para editar los insumos de una terapia
     */
    public function edit(int $id)
    {
        $terapia = $this->terapiaModel->findById($id);
        if (!$terapia) {
            flashMessage('error', 'Terapia no encontrada.');
            redirect('catalogo-terapias');
        }

        $detalles = $this->terapiaModel->getDetalle($id);
        $insumosDisponibles = $this->insumoModel->findAllWithDetails(['tipo' => 'INTE', 'activo' => 1], 1000);

        renderView('catalogo_terapias/form', [
            'pageTitle'          => 'Insumos de la Terapia',
            'activeMenu'         => 'catalogo-terapias',
            'action'             => url('catalogo-terapias/update/' . $id),
            'terapia'            => $terapia,
            'detalles'           => $detalles,
            'insumosDisponibles' => $insumosDisponibles
        ]);
    }

    /**
     * Guarda los insumos asociados a la terapia
     */
    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-terapias');
        }
        verifyCsrf();

        $terapia = $this->terapiaModel->findById($id);
        if (!$terapia) {
            flashMessage('error', 'Terapia no encontrada.');
            redirect('catalogo-terapias');
        }

        $insumosIds = $_POST['insumo_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];

        $insumosDetalle = [];
        foreach ($insumosIds as $index => $id_ins) {
            $id_ins = (int) $id_ins;
            $cant = (float) ($cantidades[$index] ?? 0);

            if ($id_ins > 0 && $cant > 0) {
                // Verificar duplicados en la misma solicitud
                $exists = false;
                foreach ($insumosDetalle as $det) {
                    if ($det['idInsumo'] === $id_ins) {
                        $exists = true; break;
                    }
                }
                if (!$exists) {
                    $insumosDetalle[] = [
                        'idInsumo' => $id_ins,
                        'cantidad' => $cant
                    ];
                }
            }
        }

        $result = $this->terapiaModel->updateDetalle($id, $insumosDetalle);

        if ($result) {
            flashMessage('success', 'Insumos de la terapia actualizados exitosamente.');
            redirect('catalogo-terapias');
        } else {
            flashMessage('error', 'Ocurrió un error al actualizar los insumos de la terapia.');
            redirect('catalogo-terapias/edit/' . $id);
        }
    }
}
