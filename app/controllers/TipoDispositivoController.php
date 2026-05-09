<?php
/**
 * TipoDispositivoController — CRUD del catálogo de Tipos de Dispositivo
 */
require_once APP_ROOT . '/app/models/TipoDispositivoModel.php';

class TipoDispositivoController
{
    private TipoDispositivoModel $model;

    public function __construct()
    {
        $this->model = new TipoDispositivoModel();
    }

    /** Lista todos los tipos de dispositivo. */
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

        renderView('tipo_dispositivo/index', [
            'pageTitle'  => 'Tipos de Dispositivo',
            'activeMenu' => 'tipo-dispositivo',
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
                renderView('tipo_dispositivo/form', [
                    'pageTitle'  => 'Nuevo Tipo de Dispositivo',
                    'activeMenu' => 'tipo-dispositivo',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Tipo de dispositivo creado exitosamente.');
            redirect('tipo-dispositivo');
            return;
        }

        renderView('tipo_dispositivo/form', [
            'pageTitle'  => 'Nuevo Tipo de Dispositivo',
            'activeMenu' => 'tipo-dispositivo',
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
            redirect('tipo-dispositivo');
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
                renderView('tipo_dispositivo/form', [
                    'pageTitle'  => 'Editar Tipo de Dispositivo',
                    'activeMenu' => 'tipo-dispositivo',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update((int)$id, $data);
            flashMessage('success', 'Tipo de dispositivo actualizado exitosamente.');
            redirect('tipo-dispositivo');
            return;
        }

        renderView('tipo_dispositivo/form', [
            'pageTitle'  => 'Editar Tipo de Dispositivo',
            'activeMenu' => 'tipo-dispositivo',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    /** Activa o inactiva un tipo de dispositivo (con validación de dependencia). */
    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('tipo-dispositivo'); return; }
        verifyCsrf();

        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('tipo-dispositivo');
            return;
        }

        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies((int)$id)) {
                flashMessage('error', 'No se puede inactivar: hay activos electrónicos activos con este tipo de dispositivo.');
                redirect('tipo-dispositivo');
                return;
            }
            $this->model->softDelete((int)$id);
            flashMessage('success', 'Tipo de dispositivo inactivado.');
        } else {
            $this->model->restore((int)$id);
            flashMessage('success', 'Tipo de dispositivo reactivado.');
        }

        redirect('tipo-dispositivo');
    }
}
