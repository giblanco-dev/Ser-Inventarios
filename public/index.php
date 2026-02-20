<?php
/**
 * Front Controller — Punto de entrada único
 * SER Inventarios · Consultorio de Medicina Alternativa Ser S.C.
 *
 * Todas las peticiones HTTP pasan por este archivo gracias al .htaccess.
 */

// ── Iniciar sesión ───────────────────────────────────────────
session_start();

// ── Cargar configuración y dependencias ──────────────────────
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/BaseModel.php';
require_once __DIR__ . '/../app/helpers/helpers.php';

// ── Despachar la petición ────────────────────────────────────
$router = new Router();
$router->dispatch();
