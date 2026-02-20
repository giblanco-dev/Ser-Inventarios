<?php
/**
 * MovimientosController — Registro y consulta de movimientos de inventario
 */
require_once APP_ROOT . '/app/models/MovimientoModel.php';
require_once APP_ROOT . '/app/models/ConceptoMovimientoModel.php';
require_once APP_ROOT . '/app/models/InsumoModel.php';

class MovimientosController
{
    private MovimientoModel $model;
    private ConceptoMovimientoModel $conceptoModel;
    private InsumoModel $insumoModel;

    public function __construct()
    {
        $this->model         = new MovimientoModel();
        $this->conceptoModel = new ConceptoMovimientoModel();
        $this->insumoModel   = new InsumoModel();
    }

    /**
     * Historial de movimientos con filtros.
     */
    public function index(): void
    {
        $filters = [];
        foreach (['fecha_desde', 'fecha_hasta', 'concepto', 'tipo_operacion'] as $key) {
            if (!empty($_GET[$key])) {
                $filters[$key] = $_GET[$key];
            }
        }

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countHistorial($filters);
        $pagination = paginateQuery($total, $page);
        $items = $this->model->getHistorial($filters, $pagination['limit'], $pagination['offset']);

        renderView('movimientos/index', [
            'pageTitle'  => 'Historial de Movimientos',
            'activeMenu' => 'movimientos',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
            'conceptos'  => $this->conceptoModel->findActive(),
        ]);
    }

    /**
     * Formulario de registro de movimiento / procesamiento.
     */
    public function create(): void
    {
        if (isPost()) {
            verifyCsrf();

            $cabecera = [
                'ConceptoID'      => trim($_POST['ConceptoID'] ?? ''),
                'Referencia'      => trim($_POST['Referencia'] ?? ''),
                'Observaciones'   => trim($_POST['Observaciones'] ?? ''),
                'UsuarioRegistra' => 'Sistema', // TODO: sistema de usuarios v1.3
            ];

            // Validar cabecera
            $v = new Validator($cabecera, [
                'ConceptoID' => 'required',
            ], ['ConceptoID' => 'Concepto de Movimiento']);

            $errors = $v->errors();

            // Recopilar líneas de detalle
            $detalles = [];
            $insumoIds  = $_POST['InsumoID'] ?? [];
            $cantidades = $_POST['Cantidad'] ?? [];
            $lotes      = $_POST['NumeroLote'] ?? [];
            $vencimientos = $_POST['FechaVencimiento'] ?? [];

            if (empty($insumoIds) || !is_array($insumoIds)) {
                $errors['detalles'] = 'Debe agregar al menos un insumo al movimiento.';
            } else {
                foreach ($insumoIds as $i => $insumoId) {
                    if (empty($insumoId)) continue;

                    $cantidad = (float)($cantidades[$i] ?? 0);
                    if ($cantidad <= 0) {
                        $errors["detalle_{$i}"] = "La cantidad del insumo en la línea " . ($i + 1) . " debe ser mayor a 0.";
                        continue;
                    }

                    $linea = [
                        'InsumoID' => (int)$insumoId,
                        'Cantidad' => $cantidad,
                        'NumeroLote'       => !empty($lotes[$i]) ? trim($lotes[$i]) : null,
                        'FechaVencimiento' => !empty($vencimientos[$i]) ? trim($vencimientos[$i]) : null,
                    ];

                    // Validar lote/vencimiento si el insumo tiene caducidad
                    $insumo = $this->insumoModel->findById((int)$insumoId);
                    if ($insumo && $insumo['Caducidad'] == 1) {
                        if (empty($linea['NumeroLote'])) {
                            $errors["lote_{$i}"] = "El número de lote es obligatorio para \"{$insumo['Nombre']}\" (línea " . ($i + 1) . ").";
                        }
                        if (empty($linea['FechaVencimiento'])) {
                            $errors["vencimiento_{$i}"] = "La fecha de vencimiento es obligatoria para \"{$insumo['Nombre']}\" (línea " . ($i + 1) . ").";
                        }
                    }

                    $detalles[] = $linea;
                }

                if (empty($detalles) && empty($errors['detalles'])) {
                    $errors['detalles'] = 'Debe agregar al menos un insumo al movimiento.';
                }
            }

            if ($errors) {
                renderView('movimientos/create', [
                    'pageTitle'  => 'Registrar Movimiento',
                    'activeMenu' => 'movimientos',
                    'errors'     => $errors,
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                    'conceptos'  => $this->conceptoModel->findActive(),
                ]);
                return;
            }

            try {
                $movId = $this->model->registrar($cabecera, $detalles);
                flashMessage('success', "Movimiento #{$movId} registrado exitosamente.");
                redirect('movimientos');
            } catch (\Exception $e) {
                flashMessage('error', $e->getMessage());
                renderView('movimientos/create', [
                    'pageTitle'  => 'Registrar Movimiento',
                    'activeMenu' => 'movimientos',
                    'errors'     => ['general' => $e->getMessage()],
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                    'conceptos'  => $this->conceptoModel->findActive(),
                ]);
            }
            return;
        }

        renderView('movimientos/create', [
            'pageTitle'  => 'Registrar Movimiento',
            'activeMenu' => 'movimientos',
            'errors'     => [],
            'cabecera'   => [],
            'detalles'   => [],
            'conceptos'  => $this->conceptoModel->findActive(),
        ]);
    }

    /**
     * Detalle de un movimiento específico.
     */
    public function show(string $id = ''): void
    {
        $movimiento = $this->model->findById((int)$id);
        if (!$movimiento) {
            flashMessage('error', 'Movimiento no encontrado.');
            redirect('movimientos');
            return;
        }

        $concepto = $this->conceptoModel->findById($movimiento['ConceptoID']);
        $detalles = $this->model->getDetalleMovimiento((int)$id);

        renderView('movimientos/show', [
            'pageTitle'  => 'Movimiento #' . $id,
            'activeMenu' => 'movimientos',
            'movimiento' => $movimiento,
            'concepto'   => $concepto,
            'detalles'   => $detalles,
        ]);
    }
}
