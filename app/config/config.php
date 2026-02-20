<?php
/**
 * SER Inventarios — Configuración General
 * Consultorio de Medicina Alternativa Ser S.C.
 *
 * Este archivo define las constantes de entorno, conexión a BD,
 * y parámetros de la aplicación. NUNCA exponer al document root.
 */

// ── Entorno ──────────────────────────────────────────────────
define('APP_ENV', 'development'); // 'development' | 'production'

// ── Aplicación ───────────────────────────────────────────────
define('APP_NAME', 'SER Inventarios');
define('APP_VERSION', '1.0.0');
define('APP_CLINIC_NAME', 'Consultorio de Medicina Alternativa Ser S.C.');

// ── Rutas ────────────────────────────────────────────────────
define('BASE_URL', '/Ser-Inventarios/');
define('APP_ROOT', dirname(__DIR__, 2));

// ── Base de Datos ────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'ser_inventario');
define('DB_USER', 'root');
define('DB_PASS', 'xTNhJSP0)Ai}');
define('DB_CHARSET', 'utf8mb4');

// ── Paginación ───────────────────────────────────────────────
define('ITEMS_PER_PAGE', 15);

// ── Errores (solo en desarrollo) ─────────────────────────────
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', APP_ROOT . '/logs/error.log');
}
