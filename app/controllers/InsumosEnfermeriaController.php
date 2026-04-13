<?php
/**
 * InsumosEnfermeriaController — Gestión del sub-inventario de Enfermería
 *
 * Controlador para entregas, devoluciones, historial y dashboard
 * del sub-inventario de insumos tipo INTE para el área de enfermería.
 */
require_once APP_ROOT . '/app/models/EnfermeriaModel.php';

class InsumosEnfermeriaController
{
    private EnfermeriaModel $model;

    public function __construct()
    {
        $this->model = new EnfermeriaModel();
    }

    /**
     * Dashboard de enfermería con KPIs y stock actual.
     */
    public function index(): void
    {
        $filters = [];
        if (!empty($_GET['buscar'])) {
            $filters['buscar'] = $_GET['buscar'];
        }
        if (!empty($_GET['estado'])) {
            $filters['estado'] = $_GET['estado'];
        }

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countStockEnfermeria($filters);
        $pagination = paginateQuery($total, $page);
        $items = $this->model->getStockEnfermeria($filters, $pagination['limit'], $pagination['offset']);
        $kpis  = $this->model->getKPIs();

        renderView('enfermeria/index', [
            'pageTitle'  => 'Insumos Enfermería',
            'activeMenu' => 'insumos-enfermeria',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
            'kpis'       => $kpis,
        ]);
    }

    /**
     * Historial de entregas y devoluciones.
     */
    public function historial(): void
    {
        $filters = [];
        foreach (['fecha_desde', 'fecha_hasta', 'tipo_operacion'] as $key) {
            if (!empty($_GET[$key])) {
                $filters[$key] = $_GET[$key];
            }
        }

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countHistorial($filters);
        $pagination = paginateQuery($total, $page);
        $items = $this->model->getHistorial($filters, $pagination['limit'], $pagination['offset']);

        renderView('enfermeria/historial', [
            'pageTitle'  => 'Historial — Enfermería',
            'activeMenu' => 'insumos-enfermeria',
            'items'      => $items,
            'pagination' => $pagination,
            'filters'    => $_GET,
        ]);
    }

    /**
     * Formulario de nueva entrega / procesamiento POST.
     */
    public function create(): void
    {
        if (isPost()) {
            verifyCsrf();

            $cabecera = [
                'TipoOperacion' => 'ENTREGA',
                'EntregadoPor'  => trim($_POST['EntregadoPor'] ?? ''),
                'RecibidoPor'   => trim($_POST['RecibidoPor'] ?? ''),
                'Observaciones' => trim($_POST['Observaciones'] ?? ''),
            ];

            // Validar cabecera
            $v = new Validator($cabecera, [
                'EntregadoPor' => 'required',
                'RecibidoPor'  => 'required',
            ], [
                'EntregadoPor' => 'Entregado por',
                'RecibidoPor'  => 'Recibido por',
            ]);

            $errors = $v->errors();

            // Recopilar líneas de detalle
            $detalles = [];
            $insumoIds  = $_POST['InsumoID'] ?? [];
            $cantidades = $_POST['Cantidad'] ?? [];

            if (empty($insumoIds) || !is_array($insumoIds)) {
                $errors['detalles'] = 'Debe agregar al menos un insumo a la entrega.';
            } else {
                foreach ($insumoIds as $i => $insumoId) {
                    if (empty($insumoId)) continue;

                    $cantidad = (float)($cantidades[$i] ?? 0);
                    if ($cantidad <= 0) {
                        $errors["detalle_{$i}"] = "La cantidad en la línea " . ($i + 1) . " debe ser mayor a 0.";
                        continue;
                    }

                    $detalles[] = [
                        'InsumoID' => (int)$insumoId,
                        'Cantidad' => $cantidad,
                    ];
                }

                if (empty($detalles) && empty($errors['detalles'])) {
                    $errors['detalles'] = 'Debe agregar al menos un insumo a la entrega.';
                }
            }

            if ($errors) {
                renderView('enfermeria/create', [
                    'pageTitle'  => 'Registrar Entrega — Enfermería',
                    'activeMenu' => 'insumos-enfermeria',
                    'errors'     => $errors,
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                ]);
                return;
            }

            try {
                $entregaId = $this->model->registrarEntrega($cabecera, $detalles);
                flashMessage('success', "Entrega #{$entregaId} registrada exitosamente.");
                redirect('insumos-enfermeria/show/' . $entregaId);
            } catch (\Exception $e) {
                flashMessage('error', $e->getMessage());
                renderView('enfermeria/create', [
                    'pageTitle'  => 'Registrar Entrega — Enfermería',
                    'activeMenu' => 'insumos-enfermeria',
                    'errors'     => ['general' => $e->getMessage()],
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                ]);
            }
            return;
        }

        renderView('enfermeria/create', [
            'pageTitle'  => 'Registrar Entrega — Enfermería',
            'activeMenu' => 'insumos-enfermeria',
            'errors'     => [],
            'cabecera'   => [],
            'detalles'   => [],
        ]);
    }

    /**
     * Formulario de nueva devolución / procesamiento POST.
     */
    public function devolucion(): void
    {
        if (isPost()) {
            verifyCsrf();

            $cabecera = [
                'TipoOperacion' => 'DEVOLUCION',
                'EntregadoPor'  => trim($_POST['EntregadoPor'] ?? ''),
                'RecibidoPor'   => trim($_POST['RecibidoPor'] ?? ''),
                'Observaciones' => trim($_POST['Observaciones'] ?? ''),
            ];

            // Validar cabecera
            $v = new Validator($cabecera, [
                'EntregadoPor' => 'required',
                'RecibidoPor'  => 'required',
            ], [
                'EntregadoPor' => 'Entregado por',
                'RecibidoPor'  => 'Recibido por',
            ]);

            $errors = $v->errors();

            // Recopilar líneas de detalle
            $detalles = [];
            $insumoIds  = $_POST['InsumoID'] ?? [];
            $cantidades = $_POST['Cantidad'] ?? [];

            if (empty($insumoIds) || !is_array($insumoIds)) {
                $errors['detalles'] = 'Debe agregar al menos un insumo a la devolución.';
            } else {
                foreach ($insumoIds as $i => $insumoId) {
                    if (empty($insumoId)) continue;

                    $cantidad = (float)($cantidades[$i] ?? 0);
                    if ($cantidad <= 0) {
                        $errors["detalle_{$i}"] = "La cantidad en la línea " . ($i + 1) . " debe ser mayor a 0.";
                        continue;
                    }

                    $detalles[] = [
                        'InsumoID' => (int)$insumoId,
                        'Cantidad' => $cantidad,
                    ];
                }

                if (empty($detalles) && empty($errors['detalles'])) {
                    $errors['detalles'] = 'Debe agregar al menos un insumo a la devolución.';
                }
            }

            if ($errors) {
                renderView('enfermeria/devolucion', [
                    'pageTitle'  => 'Registrar Devolución — Enfermería',
                    'activeMenu' => 'insumos-enfermeria',
                    'errors'     => $errors,
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                ]);
                return;
            }

            try {
                $entregaId = $this->model->registrarEntrega($cabecera, $detalles);
                flashMessage('success', "Devolución #{$entregaId} registrada exitosamente.");
                redirect('insumos-enfermeria/show/' . $entregaId);
            } catch (\Exception $e) {
                flashMessage('error', $e->getMessage());
                renderView('enfermeria/devolucion', [
                    'pageTitle'  => 'Registrar Devolución — Enfermería',
                    'activeMenu' => 'insumos-enfermeria',
                    'errors'     => ['general' => $e->getMessage()],
                    'cabecera'   => $cabecera,
                    'detalles'   => $detalles,
                ]);
            }
            return;
        }

        renderView('enfermeria/devolucion', [
            'pageTitle'  => 'Registrar Devolución — Enfermería',
            'activeMenu' => 'insumos-enfermeria',
            'errors'     => [],
            'cabecera'   => [],
            'detalles'   => [],
        ]);
    }

    /**
     * Detalle de una entrega/devolución específica.
     */
    public function show(string $id = ''): void
    {
        $entrega = $this->model->findEntregaById((int)$id);
        if (!$entrega) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('insumos-enfermeria/historial');
            return;
        }

        $detalles = $this->model->getDetalleEntrega((int)$id);

        renderView('enfermeria/show', [
            'pageTitle'  => ($entrega['TipoOperacion'] === 'ENTREGA' ? 'Entrega' : 'Devolución') . ' #' . $id,
            'activeMenu' => 'insumos-enfermeria',
            'entrega'    => $entrega,
            'detalles'   => $detalles,
        ]);
    }

    /**
     * Recibo imprimible (HTML sin layout master).
     */
    public function print(string $id = ''): void
    {
        $entrega = $this->model->findEntregaById((int)$id);
        if (!$entrega) {
            flashMessage('error', 'Registro no encontrado.');
            redirect('insumos-enfermeria/historial');
            return;
        }

        $detalles = $this->model->getDetalleEntrega((int)$id);

        // Renderizar sin layout (recibo autónomo)
        extract([
            'entrega'  => $entrega,
            'detalles' => $detalles,
        ]);
        require APP_ROOT . '/views/enfermeria/print.php';
    }

    /**
     * Búsqueda AJAX de insumos INTE disponibles.
     */
    public function searchInsumos(): void
    {
        $search = trim($_GET['q'] ?? '');
        $tipo   = trim($_GET['tipo'] ?? 'entrega');

        if ($tipo === 'devolucion') {
            $results = $this->model->getInsumosEnfermeria($search);
        } else {
            $results = $this->model->getInsumosDisponibles($search);
        }

        jsonResponse($results);
    }
}
