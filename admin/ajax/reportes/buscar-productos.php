<?php
// ajax/reportes/buscar-productos.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$search = isset($_GET['q']) ? $_GET['q'] : '';

try {
    $sql = "SELECT id_producto as id, CONCAT(clave, ' - ', nombre) as text 
            FROM cat_productos 
            WHERE estatus = 1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (clave LIKE ? OR nombre LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params = [$searchTerm, $searchTerm];
        $types = "ss";
    }

    $sql .= " ORDER BY nombre LIMIT 50";

    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    $productos = [];
    if ($resultados) {
        foreach ($resultados as $row) {
            $productos[] = [
                'id' => $row['id'],
                'text' => $row['text']
            ];
        }
    }

    echo json_encode($productos);
} catch (Exception $e) {
    echo json_encode([]);
}
