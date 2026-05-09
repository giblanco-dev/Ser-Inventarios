<?php
/**
 * PuestoController — CRUD del catálogo de Puestos de Trabajo
 */
require_once APP_ROOT . '/app/models/PuestoModel.php';

class PuestoController
{
    private PuestoModel $model;

    public function __construct()
    {
        $this->model = new PuestoModel();
    }

    /** Lista todos los puestos. */
    public function index(): void
    {
        $filters = [];
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filters['Activo'] = (int) $_GET['activo'];
        }

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $total      = $this->model->count($filters);
        $pagination = paginateQuery($total, $page);
        $items      = $this->model->findAll($filters, $pagination['limit'], $pagination['offset'], '`Descripcion` ASC');

        renderView('puesto/index', [
            'pageTitle'  => 'Puestos de Trabajo',
            'activeMenu' => 'puesto',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
        ]);
    }

    /** Formulario de creación o procesamiento POST. */
    public function create(): void
    {
        if (isPost()) {
            verifyCsrf();

            $data = ['Descripcion' => trim($_POST['Descripcion'] ?? '')];

            $v = new Validator($data, [
                'Descripcion' => 'required|maxLength:100',
            ], ['Descripcion' => 'Descripción']);

            if (!$v->passes()) {
                renderView('puesto/form', [
                    'pageTitle'  => 'Nuevo Puesto',
                    'activeMenu' => 'puesto',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Puesto creado exitosamente.');
            redirect('puesto');
            return;
        }

        renderView('puesto/form', [
            'pageTitle'  => 'Nuevo Puesto',
            'activeMenu' => 'puesto',
            'errors'     => [],
            'item'       => [],
            'isEdit'     => false,
        ]);
    }

    /** Formulario de edición o procesamiento POST. */
    public function edit(string $id = ''): void
    {
        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('puesto');
            return;
        }

        if (isPost()) {
            verifyCsrf();

            $data = ['Descripcion' => trim($_POST['Descripcion'] ?? '')];

            $v = new Validator($data, [
                'Descripcion' => 'required|maxLength:100',
            ], ['Descripcion' => 'Descripción']);

            if (!$v->passes()) {
                $item['Descripcion'] = $data['Descripcion'];
                renderView('puesto/form', [
                    'pageTitle'  => 'Editar Puesto',
                    'activeMenu' => 'puesto',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update((int)$id, $data);
            flashMessage('success', 'Puesto actualizado exitosamente.');
            redirect('puesto');
            return;
        }

        renderView('puesto/form', [
            'pageTitle'  => 'Editar Puesto',
            'activeMenu' => 'puesto',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    /** Activa o inactiva un puesto. */
    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('puesto'); return; }
        verifyCsrf();

        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('puesto');
            return;
        }

        if ($item['Activo'] == 1) {
            $this->model->softDelete((int)$id);
            flashMessage('success', 'Puesto inactivado.');
        } else {
            $this->model->restore((int)$id);
            flashMessage('success', 'Puesto reactivado.');
        }

        redirect('puesto');
    }
}
