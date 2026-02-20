<?php
/**
 * CodigoBarraController — Gestión de Códigos de Barra por Insumo
 */
require_once APP_ROOT . '/app/models/InsumoCodigoBarraModel.php';
require_once APP_ROOT . '/app/models/InsumoModel.php';

class CodigoBarraController
{
    private InsumoCodigoBarraModel $model;
    private InsumoModel $insumoModel;

    public function __construct()
    {
        $this->model       = new InsumoCodigoBarraModel();
        $this->insumoModel = new InsumoModel();
    }

    /**
     * Lista y gestión de códigos para un insumo.
     */
    public function index(string $insumoId = ''): void
    {
        $insumo = $this->insumoModel->findById((int)$insumoId);
        if (!$insumo) {
            flashMessage('error', 'Insumo no encontrado.');
            redirect('insumos');
            return;
        }

        $codigos = $this->model->findByInsumo((int)$insumoId);

        renderView('insumos/codigos_barra', [
            'pageTitle'  => 'Códigos de Barra — ' . $insumo['Nombre'],
            'activeMenu' => 'insumos',
            'insumo'     => $insumo,
            'codigos'    => $codigos,
            'errors'     => [],
            'newCodigo'  => [],
        ]);
    }

    /**
     * Agrega un nuevo código de barra.
     */
    public function create(string $insumoId = ''): void
    {
        if (!isPost()) { redirect('codigo-barra/index/' . $insumoId); return; }
        verifyCsrf();

        $insumo = $this->insumoModel->findById((int)$insumoId);
        if (!$insumo) {
            flashMessage('error', 'Insumo no encontrado.');
            redirect('insumos');
            return;
        }

        $data = [
            'InsumoID'    => (int)$insumoId,
            'CodigoBarra' => trim($_POST['CodigoBarra'] ?? ''),
            'TipoCodigo'  => trim($_POST['TipoCodigo'] ?? 'INTERNO'),
            'Descripcion' => trim($_POST['Descripcion'] ?? ''),
        ];

        $v = new Validator($data, [
            'CodigoBarra' => 'required|maxLength:100',
            'TipoCodigo'  => 'required|enum:EAN13,EAN8,UPC_A,CODE128,CODE39,QR,INTERNO',
        ], ['CodigoBarra' => 'Código de Barra', 'TipoCodigo' => 'Tipo de Código']);

        $errors = $v->errors();

        // Unicidad global
        if (empty($errors['CodigoBarra']) && $this->model->codigoExists($data['CodigoBarra'])) {
            $errors['CodigoBarra'] = 'Este código de barra ya existe en el sistema.';
        }

        if ($errors) {
            $codigos = $this->model->findByInsumo((int)$insumoId);
            renderView('insumos/codigos_barra', [
                'pageTitle'  => 'Códigos de Barra — ' . $insumo['Nombre'],
                'activeMenu' => 'insumos',
                'insumo'     => $insumo,
                'codigos'    => $codigos,
                'errors'     => $errors,
                'newCodigo'  => $data,
            ]);
            return;
        }

        if (empty($data['Descripcion'])) {
            $data['Descripcion'] = null;
        }

        $this->model->create($data);
        flashMessage('success', 'Código de barra agregado exitosamente.');
        redirect('codigo-barra/index/' . $insumoId);
    }

    /**
     * Edita un código existente (descripción y tipo).
     */
    public function edit(string $codigoId = ''): void
    {
        if (!isPost()) { redirect('insumos'); return; }
        verifyCsrf();

        $codigo = $this->model->findById((int)$codigoId);
        if (!$codigo) {
            flashMessage('error', 'Código no encontrado.');
            redirect('insumos');
            return;
        }

        $data = [
            'TipoCodigo'  => trim($_POST['TipoCodigo'] ?? $codigo['TipoCodigo']),
            'Descripcion' => trim($_POST['Descripcion'] ?? ''),
        ];

        $this->model->update((int)$codigoId, $data);
        flashMessage('success', 'Código de barra actualizado.');
        redirect('codigo-barra/index/' . $codigo['InsumoID']);
    }

    /**
     * Inactivar / Reactivar código.
     */
    public function delete(string $codigoId = ''): void
    {
        if (!isPost()) { redirect('insumos'); return; }
        verifyCsrf();

        $codigo = $this->model->findById((int)$codigoId);
        if (!$codigo) {
            flashMessage('error', 'Código no encontrado.');
            redirect('insumos');
            return;
        }

        if ($codigo['Activo'] == 1) {
            $this->model->softDelete((int)$codigoId);
            flashMessage('success', 'Código de barra inactivado.');
        } else {
            $this->model->restore((int)$codigoId);
            flashMessage('success', 'Código de barra reactivado.');
        }

        redirect('codigo-barra/index/' . $codigo['InsumoID']);
    }
}
