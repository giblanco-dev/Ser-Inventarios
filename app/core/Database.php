<?php
/**
 * Database — Singleton PDO para SER Inventarios
 *
 * Atributos:
 *  - ERRMODE_EXCEPTION
 *  - FETCH_ASSOC por defecto
 *  - EMULATE_PREPARES false (prepared statements reales)
 *  - Charset utf8mb4
 */
class Database
{
    /** @var PDO|null Instancia única */
    private static ?PDO $instance = null;

    /** Impide instanciación externa */
    private function __construct() {}

    /** Impide clonación */
    private function __clone() {}

    /**
     * Obtiene la instancia PDO singleton.
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Forzar charset
            self::$instance->exec("SET NAMES '" . DB_CHARSET . "'");
        }

        return self::$instance;
    }
}
