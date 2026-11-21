<?php
/**
 * Front Controller — Punto de entrada único
 * SER Inventarios · Consultorio de Medicina Alternativa Ser S.C.
 *
 * Todas las peticiones HTTP pasan por este archivo gracias al .htaccess.
 */

// ── Cargar configuración ──────────────────────────────────────
require_once __DIR__ . '/../app/config/config.php';

// ── Iniciar sesión (aislada de la del ERP) ────────────────────
// Nombre y path propios: aunque ERP y este módulo compartan dominio,
// nunca deben compartir cookie de sesión ni pisarse entre sí.
session_name('SERINV_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => rtrim(BASE_URL, '/') . '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

// ── Cargar dependencias ────────────────────────────────────────
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/BaseModel.php';
require_once __DIR__ . '/../app/helpers/helpers.php';

// ── Despachar la petición ────────────────────────────────────
$router = new Router();
$router->dispatch();
