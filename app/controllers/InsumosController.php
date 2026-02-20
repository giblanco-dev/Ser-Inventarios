<?php
/**
 * InsumosController — CRUD del Catálogo Maestro de Insumos
 */
require_once APP_ROOT . '/app/models/InsumoModel.php';
require_once APP_ROOT . '/app/models/TipoInsumoModel.php';
require_once APP_ROOT . '/app/models/MedidaInsumoModel.php';

class InsumosController
{
    private InsumoModel $model;
    private TipoInsumoModel $tipoModel;
    private MedidaInsumoModel $medidaModel;

    public function __construct()
    {
        $this->model       = new InsumoModel();
        $this->tipoModel   = new TipoInsumoModel();
        $this->medidaModel = new MedidaInsumoModel();
    }

    /**
     * Lista de insumos con filtros y paginación.
     */
    public function index(): void
    {
        $filters = [];
        foreach (['busqueda', 'tipo', 'medida', 'activo', 'caducidad', 'estado_stock'] as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $filters[$key] = $_GET[$key];
            }
        }

        // Default: mostrar solo activos
        if (!isset($filters['activo'])) {
            $filters['activo'] = '1';
        }

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countWithFilters($filters);
        $pagination = paginateQuery($total, $page);
        $items = $this->model->findAllWithDetails($filters, $pagination['limit'], $pagination['offset']);

        renderView('insumos/index', [
            'pageTitle'  => 'Insumos',
            'activeMenu' => 'insumos',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => array_merge(['activo' => '1'], $_GET),
            'tipos'      => $this->tipoModel->findActive(),
            'medidas'    => $this->medidaModel->findActive(),
        ]);
    }

    /**
     * Formulario de creación / procesamiento.
     */
    public function create(): void
    {
        if (isPost()) {
            verifyCsrf();

            $data = $this->extractFormData();
            $errors = $this->validateFormData($data);

            if ($errors) {
                $this->renderForm('Nuevo Insumo', $data, $errors, false);
                return;
            }

            // Limpiar DiasPreviosAviso si no aplica
            if (empty($data['Caducidad'])) {
                $data['DiasPreviosAviso'] = null;
            }
            $data['Caducidad'] = !empty($data['Caducidad']) ? 1 : 0;

            $this->model->create($data);
            flashMessage('success', 'Insumo creado exitosamente.');
            redirect('insumos');
            return;
        }

        $this->renderForm('Nuevo Insumo', [], [], false);
    }

    /**
     * Formulario de edición / procesamiento.
     */
    public function edit(string $id = ''): void
    {
        $item = $this->model->findByIdWithDetails((int)$id);
        if (!$item) {
            flashMessage('error', 'Insumo no encontrado.');
            redirect('insumos');
            return;
        }

        if (isPost()) {
            verifyCsrf();

            $data = $this->extractFormData();
            $errors = $this->validateFormData($data);

            if ($errors) {
                $data['ID'] = $id;
                $data['Existencia'] = $item['Existencia']; // siempre solo lectura
                $this->renderForm('Editar Insumo', $data, $errors, true);
                return;
            }

            if (empty($data['Caducidad'])) {
                $data['DiasPreviosAviso'] = null;
            }
            $data['Caducidad'] = !empty($data['Caducidad']) ? 1 : 0;

            // No actualizar Existencia directamente
            unset($data['Existencia']);

            $this->model->update((int)$id, $data);
            flashMessage('success', 'Insumo actualizado exitosamente.');
            redirect('insumos');
            return;
        }

        $this->renderForm('Editar Insumo', $item, [], true);
    }

    /**
     * Vista de detalle de un insumo.
     */
    public function show(string $id = ''): void
    {
        $item = $this->model->findByIdWithDetails((int)$id);
        if (!$item) {
            flashMessage('error', 'Insumo no encontrado.');
            redirect('insumos');
            return;
        }

        renderView('insumos/show', [
            'pageTitle'  => $item['Nombre'],
            'activeMenu' => 'insumos',
            'item'       => $item,
        ]);
    }

    /**
     * Inactivar / Reactivar insumo.
     */
    public function delete(string $id = ''): void
    {
        if (!isPost()) { redirect('insumos'); return; }
        verifyCsrf();

        $item = $this->model->findById((int)$id);
        if (!$item) {
            flashMessage('error', 'Insumo no encontrado.');
            redirect('insumos');
            return;
        }

        if ($item['Activo'] == 1) {
            $this->model->softDelete((int)$id);
            flashMessage('success', 'Insumo inactivado.');
        } else {
            $this->model->restore((int)$id);
            flashMessage('success', 'Insumo reactivado.');
        }

        redirect('insumos');
    }

    /**
     * API de búsqueda para AJAX (movimientos).
     */
    public function search(): void
    {
        $term = trim($_GET['q'] ?? '');
        if (strlen($term) < 2) {
            jsonResponse([]);
            return;
        }

        $results = $this->model->searchByName($term);
        jsonResponse($results);
    }

    /**
     * API de búsqueda por código de barra (AJAX).
     */
    public function searchBarcode(): void
    {
        $code = trim($_GET['code'] ?? '');
        if (empty($code)) {
            jsonResponse(['error' => 'Código vacío'], 400);
            return;
        }

        $result = $this->model->findByBarcode($code);
        if ($result) {
            jsonResponse($result);
        } else {
            jsonResponse(['error' => 'Insumo no encontrado para este código'], 404);
        }
    }

    // ── Métodos privados ──────────────────────────────────────

    private function extractFormData(): array
    {
        return [
            'Nombre'           => strtoupper(trim($_POST['Nombre'] ?? '')),
            'TipoProducto'     => trim($_POST['TipoProducto'] ?? ''),
            'MedidaInsumo'     => trim($_POST['MedidaInsumo'] ?? ''),
            'Minimos'          => (int)($_POST['Minimos'] ?? 0),
            'Maximos'          => (int)($_POST['Maximos'] ?? 0),
            'Caducidad'        => isset($_POST['Caducidad']) ? 1 : 0,
            'DiasPreviosAviso' => !empty($_POST['DiasPreviosAviso']) ? (int)$_POST['DiasPreviosAviso'] : null,
        ];
    }

    private function validateFormData(array $data): array
    {
        $rules  = [
            'Nombre'       => 'required|maxLength:255',
            'TipoProducto' => 'required',
            'MedidaInsumo' => 'required',
            'Minimos'      => 'required|integer|min:0',
            'Maximos'      => 'required|integer|min:0',
        ];
        $labels = [
            'Nombre'       => 'Nombre',
            'TipoProducto' => 'Tipo de Producto',
            'MedidaInsumo' => 'Unidad de Medida',
            'Minimos'      => 'Mínimos',
            'Maximos'      => 'Máximos',
        ];

        if (!empty($data['Caducidad'])) {
            $rules['DiasPreviosAviso']  = 'required|integer|min:1';
            $labels['DiasPreviosAviso'] = 'Días Previos de Aviso';
        }

        $v = new Validator($data, $rules, $labels);
        $errors = $v->errors();

        // Validar Maximos >= Minimos
        if (empty($errors['Maximos']) && $data['Maximos'] < $data['Minimos']) {
            $errors['Maximos'] = 'El máximo debe ser mayor o igual al mínimo.';
        }

        return $errors;
    }

    private function renderForm(string $title, array $item, array $errors, bool $isEdit): void
    {
        renderView('insumos/form', [
            'pageTitle'  => $title,
            'activeMenu' => 'insumos',
            'errors'     => $errors,
            'item'       => $item,
            'isEdit'     => $isEdit,
            'tipos'      => $this->tipoModel->findActive(),
            'medidas'    => $this->medidaModel->findActive(),
        ]);
    }
}
