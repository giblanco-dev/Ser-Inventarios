<?php
/**
 * TipoInsumoController — CRUD de Tipos de Insumo
 */
require_once APP_ROOT . '/app/models/TipoInsumoModel.php';

class TipoInsumoController
{
    private TipoInsumoModel $model;

    public function __construct()
    {
        $this->model = new TipoInsumoModel();
    }

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

        renderView('tipo_insumo/index', [
            'pageTitle'  => 'Tipos de Insumo',
            'activeMenu' => 'tipo-insumo',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
        ]);
    }

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
                renderView('tipo_insumo/form', [
                    'pageTitle'  => 'Nuevo Tipo de Insumo',
                    'activeMenu' => 'tipo-insumo',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            if ($this->model->findById($data['ID'])) {
                renderView('tipo_insumo/form', [
                    'pageTitle'  => 'Nuevo Tipo de Insumo',
                    'activeMenu' => 'tipo-insumo',
                    'errors'     => ['ID' => 'Esta clave ya existe.'],
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Tipo de insumo creado exitosamente.');
            redirect('tipo-insumo');
            return;
        }

        renderView('tipo_insumo/form', [
            'pageTitle'  => 'Nuevo Tipo de Insumo',
            'activeMenu' => 'tipo-insumo',
            'errors'     => [],
            'item'       => [],
            'isEdit'     => false,
        ]);
    }

    public function edit(string $id = ''): void
    {
        $item = $this->model->findById($id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('tipo-insumo');
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
                renderView('tipo_insumo/form', [
                    'pageTitle'  => 'Editar Tipo de Insumo',
                    'activeMenu' => 'tipo-insumo',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update($id, $data);
            flashMessage('success', 'Tipo de insumo actualizado exitosamente.');
            redirect('tipo-insumo');
            return;
        }

        renderView('tipo_insumo/form', [
            'pageTitle'  => 'Editar Tipo de Insumo',
            'activeMenu' => 'tipo-insumo',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('tipo-insumo'); return; }
        verifyCsrf();

        $item = $this->model->findById($id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('tipo-insumo');
            return;
        }

        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies($id)) {
                flashMessage('error', 'No se puede inactivar: hay insumos activos que usan este tipo.');
                redirect('tipo-insumo');
                return;
            }
            $this->model->softDelete($id);
            flashMessage('success', 'Tipo de insumo inactivado.');
        } else {
            $this->model->restore($id);
            flashMessage('success', 'Tipo de insumo reactivado.');
        }

        redirect('tipo-insumo');
    }
}
