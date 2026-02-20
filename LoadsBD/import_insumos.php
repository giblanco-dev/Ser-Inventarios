<?php
/**
 * Script para importar insumos desde CSV
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$csvFile = __DIR__ . '/insumos.csv';

if (!file_exists($csvFile)) {
    die("Error: No se encontró el archivo $csvFile\n");
}

$db = Database::getInstance();

// Abrir el archivo CSV
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Leer la cabecera
    $headers = fgetcsv($handle, 1000, ",");
    
    $inserted = 0;
    $updated = 0;
    $errors = 0;

    $db->beginTransaction();

    try {
        $stmt = $db->prepare("
            INSERT INTO `insumos` (
                `ID`, `Nombre`, `TipoProducto`, `MedidaInsumo`, 
                `Existencia`, `Minimos`, `Maximos`, `Caducidad`, `DiasPreviosAviso`
            ) VALUES (
                :id, :nombre, :tipo, :medida, 
                :existencia, :minimos, :maximos, :caducidad, :dias_aviso
            )
            ON DUPLICATE KEY UPDATE 
                `Nombre` = VALUES(`Nombre`),
                `TipoProducto` = VALUES(`TipoProducto`),
                `MedidaInsumo` = VALUES(`MedidaInsumo`),
                `Existencia` = VALUES(`Existencia`),
                `Minimos` = VALUES(`Minimos`),
                `Maximos` = VALUES(`Maximos`),
                `Caducidad` = VALUES(`Caducidad`),
                `DiasPreviosAviso` = VALUES(`DiasPreviosAviso`)
        ");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Validar que la fila tenga al menos 9 columnas
            if (count($data) < 9) continue;

            $id = trim($data[0]);
            $nombre = trim($data[1]);
            $tipo = trim($data[2]);
            $medida = trim($data[3]);
            $existencia = trim($data[4]);
            $minimos = trim($data[5]);
            $maximos = trim($data[6]);
            $caducidad = trim($data[7]);
            $dias_aviso = trim($data[8]);

            // Saltar filas sin ID válido (ej. líneas en blanco o errores)
            if (!is_numeric($id)) continue;

            $stmt->execute([
                ':id' => $id,
                ':nombre' => $nombre,
                ':tipo' => $tipo,
                ':medida' => $medida,
                ':existencia' => $existencia !== '' ? (float)$existencia : 0,
                ':minimos' => $minimos !== '' ? (int)$minimos : 0,
                ':maximos' => $maximos !== '' ? (int)$maximos : 0,
                ':caducidad' => $caducidad === '1' ? 1 : 0,
                ':dias_aviso' => $dias_aviso !== '' ? (int)$dias_aviso : null
            ]);

            if ($stmt->rowCount() == 1) {
                $inserted++;
            } else {
                // rowCount() es 2 en un UPDATE (ON DUPLICATE KEY)
                $updated++;
            }
        }

        $db->commit();
        echo "Importación completada con éxito.\n";
        echo "Registros procesados/insertados: $inserted\n";
        echo "Registros actualizados: $updated\n";

    } catch (Exception $e) {
        $db->rollBack();
        echo "Error durante la importación: " . $e->getMessage() . "\n";
    }

    fclose($handle);
} else {
    echo "Error al abrir el archivo CSV.\n";
}
