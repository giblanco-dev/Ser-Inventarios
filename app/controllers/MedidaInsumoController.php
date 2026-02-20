<?php
/**
 * MedidaInsumoController — CRUD de Unidades de Medida
 */
require_once APP_ROOT . '/app/models/MedidaInsumoModel.php';

class MedidaInsumoController
{
    private MedidaInsumoModel $model;

    public function __construct()
    {
        $this->model = new MedidaInsumoModel();
    }

    /**
     * Lista todas las unidades de medida.
     */
    public function index(): void
    {
        $filters = [];
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filters['Activo'] = (int) $_GET['activo'];
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->count($filters);
        $pagination = paginateQuery($total, $page);

        $items = $this->model->findAll($filters, $pagination['limit'], $pagination['offset'], '`Descripcion` ASC');

        renderView('medida_insumo/index', [
            'pageTitle'  => 'Unidades de Medida',
            'activeMenu' => 'medida-insumo',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
        ]);
    }

    /**
     * Muestra formulario de creación o procesa el envío.
     */
    public function create(): void
    {
        if (isPost()) {
            verifyCsrf();

            $data = [
                'ID'          => strtoupper(trim($_POST['ID'] ?? '')),
                'Descripcion' => trim($_POST['Descripcion'] ?? ''),
            ];

            $v = new Validator($data, [
                'ID'          => 'required|maxLength:10',
                'Descripcion' => 'required|maxLength:100',
            ], ['ID' => 'Clave', 'Descripcion' => 'Descripción']);

            if (!$v->passes()) {
                renderView('medida_insumo/form', [
                    'pageTitle'  => 'Nueva Unidad de Medida',
                    'activeMenu' => 'medida-insumo',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            // Verificar que el ID no exista
            if ($this->model->findById($data['ID'])) {
                renderView('medida_insumo/form', [
                    'pageTitle'  => 'Nueva Unidad de Medida',
                    'activeMenu' => 'medida-insumo',
                    'errors'     => ['ID' => 'Esta clave ya existe.'],
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Unidad de medida creada exitosamente.');
            redirect('medida-insumo');
            return;
        }

        renderView('medida_insumo/form', [
            'pageTitle'  => 'Nueva Unidad de Medida',
            'activeMenu' => 'medida-insumo',
            'errors'     => [],
            'item'       => [],
            'isEdit'     => false,
        ]);
    }

    /**
     * Muestra formulario de edición o procesa la actualización.
     */
    public function edit(string $id = ''): void
    {
        $item = $this->model->findById($id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('medida-insumo');
            return;
        }

        if (isPost()) {
            verifyCsrf();

            $data = [
                'Descripcion' => trim($_POST['Descripcion'] ?? ''),
            ];

            $v = new Validator($data, [
                'Descripcion' => 'required|maxLength:100',
            ], ['Descripcion' => 'Descripción']);

            if (!$v->passes()) {
                $item['Descripcion'] = $data['Descripcion'];
                renderView('medida_insumo/form', [
                    'pageTitle'  => 'Editar Unidad de Medida',
                    'activeMenu' => 'medida-insumo',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update($id, $data);
            flashMessage('success', 'Unidad de medida actualizada exitosamente.');
            redirect('medida-insumo');
            return;
        }

        renderView('medida_insumo/form', [
            'pageTitle'  => 'Editar Unidad de Medida',
            'activeMenu' => 'medida-insumo',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    /**
     * Inactiva o reactiva una unidad de medida.
     */
    public function delete(string $id = ''): void
    {
        if (!isPost()) {
            redirect('medida-insumo');
            return;
        }
        verifyCsrf();

        $item = $this->model->findById($id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('medida-insumo');
            return;
        }

        // Si se va a inactivar, verificar dependencias
        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies($id)) {
                flashMessage('error', 'No se puede inactivar: hay insumos activos que usan esta unidad de medida.');
                redirect('medida-insumo');
                return;
            }
            $this->model->softDelete($id);
            flashMessage('success', 'Unidad de medida inactivada.');
        } else {
            $this->model->restore($id);
            flashMessage('success', 'Unidad de medida reactivada.');
        }

        redirect('medida-insumo');
    }
}
