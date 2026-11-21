<?php
/**
 * Router — Front Controller simplificado
 *
 * Mapea: /{controller}/{action}/{id?}
 * Controlador por defecto: DashboardController::index()
 */
class Router
{
    /**
     * Despacha la petición al controlador y acción correspondientes.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $uri = $this->parseUri();
        $segments = $this->extractSegments($uri);

        $controllerName = $this->resolveController($segments[0] ?? '');
        $action         = $this->resolveAction($segments[1] ?? '');
        $id             = $segments[2] ?? null;

        if ($controllerName !== 'AuthController' && empty($_SESSION['auth'])) {
            $this->abort(401, 'Acceso no autorizado. Ingresa al módulo desde el ERP.');
            return;
        }

        $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->abort(404, 'Módulo no encontrado');
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->abort(404, 'Controlador no encontrado');
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->abort(404, 'Acción no encontrada');
            return;
        }

        // Llamar acción con o sin parámetro ID
        if ($id !== null) {
            $controller->$action($id);
        } else {
            $controller->$action();
        }
    }

    /**
     * Parsea la URI quitando BASE_URL y query string.
     *
     * @return string
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        // Quitar BASE_URL del inicio
        $base = rtrim(BASE_URL, '/');
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        return trim($uri, '/');
    }

    /**
     * Extrae los segmentos de la URI.
     *
     * @param string $uri
     * @return array
     */
    private function extractSegments(string $uri): array
    {
        if ($uri === '') {
            return [];
        }
        return explode('/', $uri);
    }

    /**
     * Resuelve el nombre de la clase controlador.
     *
     * @param string $segment
     * @return string
     */
    private function resolveController(string $segment): string
    {
        if ($segment === '') {
            return 'DashboardController';
        }

        // kebab-case → PascalCase + Controller
        $parts = explode('-', $segment);
        $name = implode('', array_map('ucfirst', $parts));
        return $name . 'Controller';
    }

    /**
     * Resuelve el nombre del método de acción.
     *
     * @param string $segment
     * @return string
     */
    private function resolveAction(string $segment): string
    {
        if ($segment === '') {
            return 'index';
        }

        // kebab-case → camelCase
        $parts = explode('-', $segment);
        $first = array_shift($parts);
        return $first . implode('', array_map('ucfirst', $parts));
    }

    /**
     * Aborta con código HTTP y mensaje de error.
     *
     * @param int    $code
     * @param string $message
     * @return void
     */
    private function abort(int $code, string $message): void
    {
        http_response_code($code);

        if ($this->isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        } else {
            // Renderizar vista de error genérica
            $pageTitle = "Error {$code}";
            $errorMessage = $message;
            $errorCode = $code;
            $content = APP_ROOT . '/views/errors/error.php';
            if (file_exists(APP_ROOT . '/views/layouts/master.php')) {
                require APP_ROOT . '/views/layouts/master.php';
            } else {
                echo "<h1>{$code}</h1><p>{$message}</p>";
            }
        }
    }

    /**
     * Determina si la petición es AJAX.
     *
     * @return bool
     */
    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT'])
                && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
