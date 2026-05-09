<?php
/**
 * UbicacionController — CRUD del catálogo de Ubicaciones Físicas
 */
require_once APP_ROOT . '/app/models/UbicacionModel.php';

class UbicacionController
{
    private UbicacionModel $model;

    public function __construct()
    {
        $this->model = new UbicacionModel();
    }

    /** Lista todas las ubicaciones. */
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

        renderView('ubicacion/index', [
            'pageTitle'  => 'Ubicaciones',
            'activeMenu' => 'ubicacion',
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
                renderView('ubicacion/form', [
                    'pageTitle'  => 'Nueva Ubicación',
                    'activeMenu' => 'ubicacion',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Ubicación creada exitosamente.');
            redirect('ubicacion');
            return;
        }

        renderView('ubicacion/form', [
            'pageTitle'  => 'Nueva Ubicación',
            'activeMenu' => 'ubicacion',
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
            redirect('ubicacion');
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
                renderView('ubicacion/form', [
                    'pageTitle'  => 'Editar Ubicación',
                    'activeMenu' => 'ubicacion',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update((int)$id, $data);
            flashMessage('success', 'Ubicación actualizada exitosamente.');
            redirect('ubicacion');
            return;
        }

        renderView('ubicacion/form', [
            'pageTitle'  => 'Editar Ubicación',
            'activeMenu' => 'ubicacion',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    /** Activa o inactiva una ubicación (con validación de dependencia). */
    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('ubicacion'); return; }
        verifyCsrf();

        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('ubicacion');
            return;
        }

        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies((int)$id)) {
                flashMessage('error', 'No se puede inactivar: hay activos electrónicos activos en esta ubicación.');
                redirect('ubicacion');
                return;
            }
            $this->model->softDelete((int)$id);
            flashMessage('success', 'Ubicación inactivada.');
        } else {
            $this->model->restore((int)$id);
            flashMessage('success', 'Ubicación reactivada.');
        }

        redirect('ubicacion');
    }
}
