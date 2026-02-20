<?php
/**
 * ConceptoMovimientoController — CRUD de Conceptos de Movimiento
 */
require_once APP_ROOT . '/app/models/ConceptoMovimientoModel.php';

class ConceptoMovimientoController
{
    private ConceptoMovimientoModel $model;

    public function __construct()
    {
        $this->model = new ConceptoMovimientoModel();
    }

    public function index(): void
    {
        $filters = [];
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filters['Activo'] = (int) $_GET['activo'];
        }
        if (!empty($_GET['tipo_operacion'])) {
            $filters['TipoOperacion'] = $_GET['tipo_operacion'];
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->count($filters);
        $pagination = paginateQuery($total, $page);
        $items = $this->model->findAll($filters, $pagination['limit'], $pagination['offset'], '`TipoOperacion` ASC, `Descripcion` ASC');

        renderView('concepto_movimiento/index', [
            'pageTitle'  => 'Conceptos de Movimiento',
            'activeMenu' => 'concepto-movimiento',
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
                'ID'             => strtoupper(trim($_POST['ID'] ?? '')),
                'Descripcion'    => trim($_POST['Descripcion'] ?? ''),
                'TipoOperacion'  => strtoupper(trim($_POST['TipoOperacion'] ?? '')),
            ];

            $v = new Validator($data, [
                'ID'            => 'required|maxLength:15',
                'Descripcion'   => 'required|maxLength:100',
                'TipoOperacion' => 'required|enum:ENTRADA,SALIDA',
            ], ['ID' => 'Clave', 'Descripcion' => 'Descripción', 'TipoOperacion' => 'Tipo de Operación']);

            if (!$v->passes()) {
                renderView('concepto_movimiento/form', [
                    'pageTitle'  => 'Nuevo Concepto de Movimiento',
                    'activeMenu' => 'concepto-movimiento',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            if ($this->model->findById($data['ID'])) {
                renderView('concepto_movimiento/form', [
                    'pageTitle'  => 'Nuevo Concepto de Movimiento',
                    'activeMenu' => 'concepto-movimiento',
                    'errors'     => ['ID' => 'Esta clave ya existe.'],
                    'item'       => $data,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Concepto de movimiento creado exitosamente.');
            redirect('concepto-movimiento');
            return;
        }

        renderView('concepto_movimiento/form', [
            'pageTitle'  => 'Nuevo Concepto de Movimiento',
            'activeMenu' => 'concepto-movimiento',
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
            redirect('concepto-movimiento');
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
                renderView('concepto_movimiento/form', [
                    'pageTitle'  => 'Editar Concepto de Movimiento',
                    'activeMenu' => 'concepto-movimiento',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'isEdit'     => true,
                ]);
                return;
            }

            // TipoOperacion es inmutable tras crear
            $this->model->update($id, $data);
            flashMessage('success', 'Concepto de movimiento actualizado exitosamente.');
            redirect('concepto-movimiento');
            return;
        }

        renderView('concepto_movimiento/form', [
            'pageTitle'  => 'Editar Concepto de Movimiento',
            'activeMenu' => 'concepto-movimiento',
            'errors'     => [],
            'item'       => $item,
            'isEdit'     => true,
        ]);
    }

    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('concepto-movimiento'); return; }
        verifyCsrf();

        $item = $this->model->findById($id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('concepto-movimiento');
            return;
        }

        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies($id)) {
                flashMessage('error', 'No se puede inactivar: existen movimientos registrados con este concepto.');
                redirect('concepto-movimiento');
                return;
            }
            $this->model->softDelete($id);
            flashMessage('success', 'Concepto de movimiento inactivado.');
        } else {
            $this->model->restore($id);
            flashMessage('success', 'Concepto de movimiento reactivado.');
        }

        redirect('concepto-movimiento');
    }
}
