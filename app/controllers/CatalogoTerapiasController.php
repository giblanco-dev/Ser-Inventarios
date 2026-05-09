<?php
/**
 * CatalogoTerapiasController — Controlador para el Módulo de Terapias
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
     * Formulario para nueva terapia
     */
    public function create()
    {
        // Insumos disponibles (Solo INTE)
        $insumosDisponibles = $this->insumoModel->findAllWithDetails(['tipo' => 'INTE', 'activo' => 1], 1000);

        renderView('catalogo_terapias/form', [
            'pageTitle'          => 'Nueva Terapia',
            'activeMenu'         => 'catalogo-terapias',
            'action'             => url('catalogo-terapias/store'),
            'terapia'            => null,
            'detalles'           => [],
            'insumosDisponibles' => $insumosDisponibles
        ]);
    }

    /**
     * Guarda la nueva terapia
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-terapias');
        }

        $nom_terapia   = sanitize($_POST['nom_terapia']);
        $id_terapiaERP = (int) $_POST['id_terapiaERP'];

        if (empty($nom_terapia) || empty($id_terapiaERP)) {
            flashMessage('error', 'El Nombre de la terapia y el ID ERP son obligatorios.');
            redirect('catalogo-terapias/create');
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

        $result = $this->terapiaModel->createTerapia(
            ['id_terapiaERP' => $id_terapiaERP, 'nom_terapia' => $nom_terapia],
            $insumosDetalle
        );

        if ($result) {
            flashMessage('success', 'Terapia creada exitosamente.');
            redirect('catalogo-terapias');
        } else {
            flashMessage('error', 'Ocurrió un error al guardar o el ERP ID ya existe.');
            redirect('catalogo-terapias/create');
        }
    }

    /**
     * Formulario para editar terapia existente
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
            'pageTitle'          => 'Editar Terapia',
            'activeMenu'         => 'catalogo-terapias',
            'action'             => url('catalogo-terapias/update/' . $id),
            'terapia'            => $terapia,
            'detalles'           => $detalles,
            'insumosDisponibles' => $insumosDisponibles
        ]);
    }

    /**
     * Actualizar terapia
     */
    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('catalogo-terapias');
        }

        $nom_terapia   = sanitize($_POST['nom_terapia']);
        $id_terapiaERP = (int) $_POST['id_terapiaERP'];

        if (empty($nom_terapia) || empty($id_terapiaERP)) {
            flashMessage('error', 'El Nombre de la terapia y el ID ERP son obligatorios.');
            redirect('catalogo-terapias/edit/' . $id);
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

        $result = $this->terapiaModel->updateTerapia(
            $id,
            ['id_terapiaERP' => $id_terapiaERP, 'nom_terapia' => $nom_terapia],
            $insumosDetalle
        );

        if ($result) {
            flashMessage('success', 'Terapia actualizada exitosamente.');
            redirect('catalogo-terapias');
        } else {
            flashMessage('error', 'Ocurrió un error al actualizar la terapia.');
            redirect('catalogo-terapias/edit/' . $id);
        }
    }

    /**
     * Borrado lógico
     */
    public function delete(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deleted = $this->terapiaModel->deactivate($id);
            if ($deleted) {
                flashMessage('success', 'La terapia ha sido desactivada correctamente.');
            } else {
                flashMessage('error', 'No se pudo desactivar la terapia.');
            }
        }
        redirect('catalogo-terapias');
    }
}
