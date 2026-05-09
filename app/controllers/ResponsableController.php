<?php
/**
 * ResponsableController — CRUD del catálogo de Responsables de Activos
 */
require_once APP_ROOT . '/app/models/ResponsableModel.php';
require_once APP_ROOT . '/app/models/PuestoModel.php';

class ResponsableController
{
    private ResponsableModel $model;
    private PuestoModel      $puestoModel;

    public function __construct()
    {
        $this->model       = new ResponsableModel();
        $this->puestoModel = new PuestoModel();
    }

    /** Lista todos los responsables con filtros. */
    public function index(): void
    {
        $filters = [];
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filters['Activo'] = (int) $_GET['activo'];
        }
        if (!empty($_GET['buscar'])) {
            $filters['NombreResponsable:LIKE'] = $_GET['buscar'];
        }

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $total      = $this->model->count($filters);
        $pagination = paginateQuery($total, $page);
        $items      = $this->model->findAll($filters, $pagination['limit'], $pagination['offset'], '`NombreResponsable` ASC');

        renderView('responsable/index', [
            'pageTitle'  => 'Responsables',
            'activeMenu' => 'responsable',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
        ]);
    }

    /** Formulario de creación o procesamiento POST. */
    public function create(): void
    {
        $puestos = $this->puestoModel->findActivos();

        if (isPost()) {
            verifyCsrf();

            $data = [
                'NombreResponsable' => strtoupper(trim($_POST['NombreResponsable'] ?? '')),
                'Puesto'            => trim($_POST['Puesto'] ?? ''),
            ];

            $v = new Validator($data, [
                'NombreResponsable' => 'required|maxLength:200',
                'Puesto'            => 'required|maxLength:100',
            ], [
                'NombreResponsable' => 'Nombre del Responsable',
                'Puesto'            => 'Puesto',
            ]);

            if (!$v->passes()) {
                renderView('responsable/form', [
                    'pageTitle'  => 'Nuevo Responsable',
                    'activeMenu' => 'responsable',
                    'errors'     => $v->errors(),
                    'item'       => $data,
                    'puestos'    => $puestos,
                    'isEdit'     => false,
                ]);
                return;
            }

            $this->model->create($data);
            flashMessage('success', 'Responsable creado exitosamente.');
            redirect('responsable');
            return;
        }

        renderView('responsable/form', [
            'pageTitle'  => 'Nuevo Responsable',
            'activeMenu' => 'responsable',
            'errors'     => [],
            'item'       => [],
            'puestos'    => $puestos,
            'isEdit'     => false,
        ]);
    }

    /** Formulario de edición o procesamiento POST. */
    public function edit(string $id = ''): void
    {
        $item    = $this->model->findById((int)$id);
        $puestos = $this->puestoModel->findActivos();

        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('responsable');
            return;
        }

        if (isPost()) {
            verifyCsrf();

            $data = [
                'NombreResponsable' => strtoupper(trim($_POST['NombreResponsable'] ?? '')),
                'Puesto'            => trim($_POST['Puesto'] ?? ''),
            ];

            $v = new Validator($data, [
                'NombreResponsable' => 'required|maxLength:200',
                'Puesto'            => 'required|maxLength:100',
            ], [
                'NombreResponsable' => 'Nombre del Responsable',
                'Puesto'            => 'Puesto',
            ]);

            if (!$v->passes()) {
                $item = array_merge($item, $data);
                renderView('responsable/form', [
                    'pageTitle'  => 'Editar Responsable',
                    'activeMenu' => 'responsable',
                    'errors'     => $v->errors(),
                    'item'       => $item,
                    'puestos'    => $puestos,
                    'isEdit'     => true,
                ]);
                return;
            }

            $this->model->update((int)$id, $data);
            flashMessage('success', 'Responsable actualizado exitosamente.');
            redirect('responsable');
            return;
        }

        renderView('responsable/form', [
            'pageTitle'  => 'Editar Responsable',
            'activeMenu' => 'responsable',
            'errors'     => [],
            'item'       => $item,
            'puestos'    => $puestos,
            'isEdit'     => true,
        ]);
    }

    /** Activa o inactiva un responsable (con validación de dependencia). */
    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('responsable'); return; }
        verifyCsrf();

        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('responsable');
            return;
        }

        if ($item['Activo'] == 1) {
            if ($this->model->hasDependencies((int)$id)) {
                flashMessage('error', 'No se puede inactivar: hay activos electrónicos activos asignados a este responsable.');
                redirect('responsable');
                return;
            }
            $this->model->softDelete((int)$id);
            flashMessage('success', 'Responsable inactivado.');
        } else {
            $this->model->restore((int)$id);
            flashMessage('success', 'Responsable reactivado.');
        }

        redirect('responsable');
    }
}
