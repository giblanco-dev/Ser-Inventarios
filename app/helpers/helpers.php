<?php
/**
 * Helpers reutilizables — SER Inventarios
 *
 * Funciones globales para formateo, seguridad, paginación,
 * mensajes flash, y utilidades de la UI.
 */

// ── Seguridad: Escape de salida a HTML ───────────────────────
/**
 * Escapa una cadena para salida HTML segura (previene XSS).
 *
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza una entrada de texto: trim + strip_tags + htmlspecialchars.
 *
 * @param string $input
 * @return string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ── CSRF ─────────────────────────────────────────────────────
/**
 * Genera y retorna un campo hidden HTML con el token CSRF.
 *
 * @return string HTML <input type="hidden">
 */
function csrfField(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['_csrf_token'];
    return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

/**
 * Verifica el token CSRF contra la sesión.
 * Aborta con 403 si es inválido.
 *
 * @return void
 */
function verifyCsrf(): void
{
    $token = $_POST['_csrf_token'] ?? '';
    if (empty($_SESSION['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $token)) {
        http_response_code(403);
        die('Token CSRF inválido. Recarga la página e intenta de nuevo.');
    }
    // Rotar token tras uso exitoso
    unset($_SESSION['_csrf_token']);
}

// ── Autenticación (SSO desde el ERP) ─────────────────────────
/**
 * Retorna los datos del usuario autenticado en este módulo, o null.
 *
 * @return array|null
 */
function currentUser(): ?array
{
    return $_SESSION['auth'] ?? null;
}

/**
 * Verifica si el usuario autenticado tiene el rol indicado.
 *
 * @param string $role
 * @return bool
 */
function hasRole(string $role): bool
{
    return ($_SESSION['auth']['rol'] ?? null) === $role;
}

// ── Flash Messages ──────────────────────────────────────────
/**
 * Almacena un mensaje flash en la sesión.
 *
 * @param string $type    'success' | 'error' | 'warning' | 'info'
 * @param string $message Texto del mensaje
 */
function flashMessage(string $type, string $message): void
{
    $_SESSION['_flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Recupera y elimina el mensaje flash de la sesión.
 *
 * @return array|null ['type' => '...', 'message' => '...'] o null
 */
function getFlash(): ?array
{
    if (isset($_SESSION['_flash'])) {
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $flash;
    }
    return null;
}

// ── Redirección ─────────────────────────────────────────────
/**
 * Redirige a una URL relativa a BASE_URL.
 *
 * @param string $path Ruta relativa (ej: 'insumos', 'insumos/edit/5')
 */
function redirect(string $path = ''): void
{
    $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit();
}

// ── Formateo de Fechas ──────────────────────────────────────
/**
 * Formatea una fecha MySQL al formato de la UI.
 *
 * @param string|null $date Fecha en formato MySQL (Y-m-d o Y-m-d H:i:s)
 * @param string      $fmt  Formato de salida
 * @return string Fecha formateada o cadena vacía
 */
function formatDate(?string $date, string $fmt = 'd/m/Y'): string
{
    if (empty($date)) {
        return '';
    }
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date)
       ?: DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format($fmt) : $date;
}

/**
 * Formatea fecha y hora.
 */
function formatDateTime(?string $date): string
{
    return formatDate($date, 'd/m/Y H:i');
}

// ── Paginación ──────────────────────────────────────────────
/**
 * Calcula metadatos de paginación.
 *
 * @param int $total   Total de registros
 * @param int $page    Página actual (1-indexed)
 * @param int $limit   Registros por página
 * @return array ['page', 'limit', 'offset', 'totalPages', 'total']
 */
function paginateQuery(int $total, int $page = 1, int $limit = ITEMS_PER_PAGE): array
{
    $page = max(1, $page);
    $totalPages = (int) ceil($total / $limit) ?: 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;

    return [
        'page'       => $page,
        'limit'      => $limit,
        'offset'     => $offset,
        'totalPages' => $totalPages,
        'total'      => $total,
    ];
}

// ── Stock Badge ─────────────────────────────────────────────
/**
 * Retorna la clase CSS del badge de stock.
 *
 * @param int|float $existencia
 * @param int       $minimos
 * @return string 'ok' | 'bajo' | 'critico'
 */
function stockBadge(int|float $existencia, int $minimos): string
{
    if ($existencia <= 0) {
        return 'critico';
    }
    if ($existencia <= $minimos) {
        return 'bajo';
    }
    return 'ok';
}

/**
 * Retorna la etiqueta del badge de stock.
 */
function stockLabel(string $badge): string
{
    return match ($badge) {
        'critico' => 'CRÍTICO',
        'bajo'    => 'BAJO',
        default   => 'OK',
    };
}

// ── URL helpers ─────────────────────────────────────────────
/**
 * Genera una URL completa relativa a BASE_URL.
 */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Genera una URL a un asset público.
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

// ── Request helpers ────────────────────────────────────────
/**
 * Verifica si la petición actual es POST.
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica si la petición es AJAX.
 */
function isAjax(): bool
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT'])
            && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
}

/**
 * Envía respuesta JSON y termina.
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ── Render View ─────────────────────────────────────────────
/**
 * Renderiza una vista dentro del layout master.
 *
 * @param string $view     Ruta relativa a /views/ sin extensión (ej: 'insumos/index')
 * @param array  $data     Variables disponibles en la vista
 * @param string $layout   Layout a usar
 */
function renderView(string $view, array $data = [], string $layout = 'layouts/master'): void
{
    // Extraer variables para la vista
    extract($data);

    // Capturar contenido de la vista
    $content = APP_ROOT . '/views/' . $view . '.php';

    if (!file_exists($content)) {
        http_response_code(500);
        die("Vista no encontrada: {$view}");
    }

    // Incluir el layout que a su vez incluirá $content
    require APP_ROOT . '/views/' . $layout . '.php';
}
