<?php
/**
 * InsumosElectronicosController — Gestión de Activos Electrónicos / TI
 *
 * Alta, edición, baja lógica documentada y visualización de activos TI.
 */
require_once APP_ROOT . '/app/models/InsumosElectronicosModel.php';

class InsumosElectronicosController
{
    private InsumosElectronicosModel $model;

    public function __construct()
    {
        $this->model = new InsumosElectronicosModel();
    }

    // ── Listado ───────────────────────────────────────────────

    /**
     * Lista paginada de activos con filtros.
     */
    public function index(): void
    {
        $filters = [];
        if (!empty($_GET['buscar']))          $filters['buscar']           = $_GET['buscar'];
        if (!empty($_GET['TipoDispositivoID'])) $filters['TipoDispositivoID'] = (int)$_GET['TipoDispositivoID'];
        if (!empty($_GET['UbicacionID']))     $filters['UbicacionID']      = (int)$_GET['UbicacionID'];
        if (!empty($_GET['ResponsableID']))   $filters['ResponsableID']    = (int)$_GET['ResponsableID'];
        if (isset($_GET['Activo']) && $_GET['Activo'] !== '') {
            $filters['Activo'] = (int)$_GET['Activo'];
        } else {
            $filters['Activo'] = 1; // Por defecto: solo activos
        }

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $total      = $this->model->countActivos($filters);
        $pagination = paginateQuery($total, $page);
        $items      = $this->model->findAllActivos($filters, $pagination['limit'], $pagination['offset']);
        $catalogos  = $this->model->getCatalogos();

        renderView('insumos_electronicos/index', [
            'pageTitle'  => 'Activos Electrónicos / TI',
            'activeMenu' => 'insumos-electronicos',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
            'catalogos'  => $catalogos,
        ]);
    }

    // ── Alta ──────────────────────────────────────────────────

    /**
     * Formulario de alta / procesamiento POST.
     */
    public function create(): void
    {
        $catalogos = $this->model->getCatalogos();

        if (isPost()) {
            verifyCsrf();

            $data = $this->extractFormData();

            $errors = $this->validateFormData($data);

            // Verificar unicidad de NoSerie
            if (empty($errors['NoSerie']) && $this->model->noSerieExists($data['NoSerie'])) {
                $errors['NoSerie'] = 'Este número de serie ya está registrado en el sistema.';
            }

            if ($errors) {
                renderView('insumos_electronicos/form', [
                    'pageTitle'  => 'Nuevo Activo Electrónico',
                    'activeMenu' => 'insumos-electronicos',
                    'errors'     => $errors,
                    'item'       => $data,
                    'catalogos'  => $catalogos,
                    'isEdit'     => false,
                ]);
                return;
            }

            try {
                $id = $this->model->registrar($data);
                flashMessage('success', "Activo #{$id} registrado exitosamente.");
                redirect('insumos-electronicos/show/' . $id);
            } catch (Exception $e) {
                error_log('InsumosElectronicosController::create — ' . $e->getMessage());
                renderView('insumos_electronicos/form', [
                    'pageTitle'  => 'Nuevo Activo Electrónico',
                    'activeMenu' => 'insumos-electronicos',
                    'errors'     => ['general' => 'Error al guardar el registro. Intente de nuevo.'],
                    'item'       => $data,
                    'catalogos'  => $catalogos,
                    'isEdit'     => false,
                ]);
            }
            return;
        }

        renderView('insumos_electronicos/form', [
            'pageTitle'  => 'Nuevo Activo Electrónico',
            'activeMenu' => 'insumos-electronicos',
            'errors'     => [],
            'item'       => [],
            'catalogos'  => $catalogos,
            'isEdit'     => false,
        ]);
    }

    // ── Detalle ───────────────────────────────────────────────

    /**
     * Ficha completa del activo (con contraseñas ocultas en la vista).
     */
    public function show(string $id = ''): void
    {
        $item = $this->model->findByIdFull((int)$id);
        if (!$item) {
            flashMessage('error', 'Activo no encontrado.');
            redirect('insumos-electronicos');
            return;
        }

        renderView('insumos_electronicos/show', [
            'pageTitle'  => 'Activo: ' . e($item['Modelo']) . ' — ' . e($item['NoSerie']),
            'activeMenu' => 'insumos-electronicos',
            'item'       => $item,
        ]);
    }

    // ── Edición ───────────────────────────────────────────────

    /**
     * Formulario de edición / procesamiento POST.
     * NoSerie NO se puede modificar.
     */
    public function edit(string $id = ''): void
    {
        $item      = $this->model->findByIdFull((int)$id);
        $catalogos = $this->model->getCatalogos();

        if (!$item) {
            flashMessage('error', 'Activo no encontrado.');
            redirect('insumos-electronicos');
            return;
        }

        if ($item['Activo'] == 0) {
            flashMessage('error', 'No se puede editar un activo dado de baja.');
            redirect('insumos-electronicos/show/' . $id);
            return;
        }

        if (isPost()) {
            verifyCsrf();

            $data = $this->extractFormData();
            // En edición, NoSerie se toma del registro existente (no del POST)
            $data['NoSerie'] = $item['NoSerie'];

            $errors = $this->validateFormData($data, isEdit: true);

            if ($errors) {
                renderView('insumos_electronicos/form', [
                    'pageTitle'  => 'Editar Activo — ' . e($item['NoSerie']),
                    'activeMenu' => 'insumos-electronicos',
                    'errors'     => $errors,
                    'item'       => array_merge($item, $data),
                    'catalogos'  => $catalogos,
                    'isEdit'     => true,
                ]);
                return;
            }

            try {
                $this->model->actualizar((int)$id, $data);
                flashMessage('success', 'Activo actualizado exitosamente.');
                redirect('insumos-electronicos/show/' . $id);
            } catch (Exception $e) {
                error_log('InsumosElectronicosController::edit — ' . $e->getMessage());
                flashMessage('error', 'Error al actualizar el registro. Intente de nuevo.');
                redirect('insumos-electronicos/edit/' . $id);
            }
            return;
        }

        renderView('insumos_electronicos/form', [
            'pageTitle'  => 'Editar Activo — ' . e($item['NoSerie']),
            'activeMenu' => 'insumos-electronicos',
            'errors'     => [],
            'item'       => $item,
            'catalogos'  => $catalogos,
            'isEdit'     => true,
        ]);
    }

    // ── Baja Lógica ───────────────────────────────────────────

    /**
     * Procesa la baja lógica de un activo con comentario obligatorio.
     */
    public function baja(string $id = ''): void
    {
        if (!isPost()) {
            redirect('insumos-electronicos');
            return;
        }
        verifyCsrf();

        $item = $this->model->findByIdFull((int)$id);
        if (!$item) {
            flashMessage('error', 'Activo no encontrado.');
            redirect('insumos-electronicos');
            return;
        }

        if ($item['Activo'] == 0) {
            flashMessage('error', 'Este activo ya está dado de baja.');
            redirect('insumos-electronicos/show/' . $id);
            return;
        }

        $comentario = trim($_POST['comentarios_baja'] ?? '');
        if (empty($comentario)) {
            flashMessage('error', 'Debe ingresar un comentario para dar de baja el activo.');
            redirect('insumos-electronicos/show/' . $id);
            return;
        }

        $this->model->darDeBaja((int)$id, $comentario);
        flashMessage('success', 'Activo dado de baja correctamente.');
        redirect('insumos-electronicos/show/' . $id);
    }

    // ── Helpers privados ──────────────────────────────────────

    /**
     * Extrae y sanea los datos del formulario POST.
     */
    private function extractFormData(): array
    {
        return [
            'NoSerie'           => strtoupper(trim($_POST['NoSerie'] ?? '')),
            'Modelo'            => strtoupper(trim($_POST['Modelo'] ?? '')),
            'Marca'             => strtoupper(trim($_POST['Marca'] ?? '')),
            'UbicacionID'       => (int)($_POST['UbicacionID'] ?? 0),
            'TipoDispositivoID' => (int)($_POST['TipoDispositivoID'] ?? 0),
            'ResponsableID'     => (int)($_POST['ResponsableID'] ?? 0),
            'Procesador'        => strtoupper(trim($_POST['Procesador'] ?? '')),
            'Almacenamiento'    => trim($_POST['Almacenamiento'] ?? ''),
            'RAM'               => trim($_POST['RAM'] ?? ''),
            'SO'                => trim($_POST['SO'] ?? ''),
            'Usuario'           => trim($_POST['Usuario'] ?? ''),
            'Pass'              => $_POST['Pass'] ?? '',
            'PassAdmin'         => $_POST['PassAdmin'] ?? '',
            'ConexionRemota'    => $_POST['ConexionRemota'] ?? 'PENDIENTE',
            'ID_Conexion'       => trim($_POST['ID_Conexion'] ?? ''),
            'PassConexionRemota'=> $_POST['PassConexionRemota'] ?? '',
            'Descripcion'       => trim($_POST['Descripcion'] ?? ''),
        ];
    }

    /**
     * Valida los datos del formulario. Retorna array de errores (vacío si válido).
     *
     * @param array $data
     * @param bool  $isEdit En edición, NoSerie no se valida (ya viene del registro)
     * @return array
     */
    private function validateFormData(array $data, bool $isEdit = false): array
    {
        $rules  = [
            'Modelo'      => 'required|maxLength:200',
            'Marca'       => 'required|maxLength:100',
            'ConexionRemota' => 'required',
        ];
        $labels = [
            'Modelo'         => 'Modelo',
            'Marca'          => 'Marca',
            'ConexionRemota' => 'Conexión Remota',
        ];

        if (!$isEdit) {
            $rules['NoSerie']  = 'required|maxLength:100';
            $labels['NoSerie'] = 'No. de Serie';
        }

        $v = new Validator($data, $rules, $labels);
        $errors = $v->errors();

        // Validación manual de UbicacionID (es INT)
        if (empty($data['UbicacionID'])) {
            $errors['UbicacionID'] = 'La ubicación es obligatoria.';
        }

        // Validar ConexionRemota sea un valor válido
        if (!in_array($data['ConexionRemota'], ['SI', 'NO', 'PENDIENTE'])) {
            $errors['ConexionRemota'] = 'Valor de conexión remota no válido.';
        }

        return $errors;
    }
}
