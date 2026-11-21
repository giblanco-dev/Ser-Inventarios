<?php
/**
 * CatalogoSuerosController — Controlador para el Módulo de Sueros
 *
 * El alta/baja del suero en sí se gobierna desde el ERP (ser.sueros) y
 * llega aquí vía sincronización manual. Este controlador solo gestiona
 * la sincronización y qué insumos consume cada suero.
 */
require_once APP_ROOT . '/app/models/CatalogoSuerosModel.php';
require_once APP_ROOT . '/app/models/InsumoModel.php';

class CatalogoSuerosController
{
    private CatalogoSuerosModel $sueroModel;
    private InsumoModel $insumoModel;

    public function __construct()
    {
        $this->sueroModel = new CatalogoSuerosModel();
        $this->insumoModel = new InsumoModel();
    }

    /**
     * Listado de Sueros
     */
    public function index()
    {
        $sueros = $this->sueroModel->findAllActives();

        renderView('catalogo_sueros/index', [
            'pageTitle'  => 'Catálogo de Sueros',
            'activeMenu' => 'catalogo-sueros',
            'sueros'     => $sueros,
        ]);
    }

    /**
     * Sincroniza el catálogo local con ser.sueros (ERP)
     */
    public function sync()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-sueros');
        }
        verifyCsrf();

        try {
            $resumen = $this->sueroModel->sincronizarDesdeErp();
            flashMessage('success', sprintf(
                'Sincronización completada: %d altas, %d actualizados, %d bajas.',
                $resumen['altas'],
                $resumen['actualizados'],
                $resumen['bajas']
            ));
        } catch (Exception $e) {
            flashMessage('error', 'Ocurrió un error al sincronizar con el ERP.');
        }

        redirect('catalogo-sueros');
    }

    /**
     * Formulario para editar los insumos de un suero
     */
    public function edit(int $id)
    {
        $suero = $this->sueroModel->findById($id);
        if (!$suero) {
            flashMessage('error', 'Suero no encontrado.');
            redirect('catalogo-sueros');
        }

        $detalles = $this->sueroModel->getDetalle($id);
        $insumosDisponibles = $this->insumoModel->findAllWithDetails(['tipo' => 'INTE', 'activo' => 1], 1000);

        renderView('catalogo_sueros/form', [
            'pageTitle'          => 'Insumos del Suero',
            'activeMenu'         => 'catalogo-sueros',
            'action'             => url('catalogo-sueros/update/' . $id),
            'suero'              => $suero,
            'detalles'           => $detalles,
            'insumosDisponibles' => $insumosDisponibles,
        ]);
    }

    /**
     * Guarda los insumos asociados al suero
     */
    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-sueros');
        }
        verifyCsrf();

        $suero = $this->sueroModel->findById($id);
        if (!$suero) {
            flashMessage('error', 'Suero no encontrado.');
            redirect('catalogo-sueros');
        }

        $insumosIds = $_POST['insumo_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];

        $insumosDetalle = [];
        foreach ($insumosIds as $index => $id_ins) {
            $id_ins = (int) $id_ins;
            $cant = (float) ($cantidades[$index] ?? 0);

            if ($id_ins > 0 && $cant > 0) {
                $exists = false;
                foreach ($insumosDetalle as $det) {
                    if ($det['idInsumo'] === $id_ins) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $insumosDetalle[] = [
                        'idInsumo' => $id_ins,
                        'cantidad' => $cant,
                    ];
                }
            }
        }

        $result = $this->sueroModel->updateDetalle($id, $insumosDetalle);

        if ($result) {
            flashMessage('success', 'Insumos del suero actualizados exitosamente.');
            redirect('catalogo-sueros');
        } else {
            flashMessage('error', 'Ocurrió un error al actualizar los insumos del suero.');
            redirect('catalogo-sueros/edit/' . $id);
        }
    }
}
