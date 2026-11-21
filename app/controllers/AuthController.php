<?php
/**
 * AuthController — SSO desde el ERP y cierre de sesión local
 *
 * Este módulo no tiene login propio: la sesión se abre exclusivamente
 * mediante un token de un solo uso generado por el ERP (ver
 * database/migration_sso.sql). Cerrar sesión aquí solo destruye la
 * cookie de este módulo (SERINV_SESSID) — la sesión del ERP no se toca.
 */
require_once APP_ROOT . '/app/models/SsoTokenModel.php';

class AuthController
{
    private SsoTokenModel $model;

    public function __construct()
    {
        $this->model = new SsoTokenModel();
    }

    /** Recibe el token del ERP, abre sesión local si es válido. */
    public function sso(string $token = ''): void
    {
        // Acepta el token tanto por query string (?token=...) como por
        // segmento de ruta (/auth/sso/{token}).
        $token = $_GET['token'] ?? $token;

        $row = $token !== '' ? $this->model->consumir($token) : null;

        if (!$row || !in_array($row['rol'], SSO_ROLES_PERMITIDOS, true)) {
            require APP_ROOT . '/views/auth/error.php';
            return;
        }

        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'erp_user_id' => $row['erp_user_id'],
            'username'    => $row['username'],
            'nombre'      => $row['nombre_completo'],
            'rol'         => $row['rol'],
            'login_at'    => time(),
        ];
        // Compatibilidad con modelos existentes que ya leen $_SESSION['username']
        $_SESSION['username'] = $row['username'];

        redirect('');
    }

    /** Cierra la sesión de este módulo únicamente. */
    public function logout(): void
    {
        if (!isPost()) {
            redirect('');
            return;
        }
        verifyCsrf();

        $_SESSION = [];
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        require APP_ROOT . '/views/auth/logged_out.php';
    }
}
