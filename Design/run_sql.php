<?php
$mysqli = new mysqli('localhost', 'root', 'xTNhJSP0)Ai}', 'ser_inventario');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$sql = file_get_contents('c:/ApacheServer/htdocs/Ser-Inventarios/Design/catalogo_terapias.sql');
if ($mysqli->multi_query($sql)) {
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo 'Success';
} else {
    echo 'Error: ' . $mysqli->error;
}
