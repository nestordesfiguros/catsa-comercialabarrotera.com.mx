<?php
// ajax/reportes/buscar-proveedores.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$search = isset($_GET['q']) ? $_GET['q'] : '';

try {
    $sql = "SELECT id, CONCAT(razon_social, ' - ', nombre_Comercial) as text 
            FROM cat_proveedores 
            WHERE estatus = 1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (razon_social LIKE ? OR nombre_Comercial LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params = [$searchTerm, $searchTerm];
        $types = "ss";
    }

    $sql .= " ORDER BY razon_social LIMIT 50";

    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    $proveedores = [];
    if ($resultados) {
        foreach ($resultados as $row) {
            $proveedores[] = [
                'id' => $row['id'],
                'text' => $row['text']
            ];
        }
    }

    echo json_encode($proveedores);
} catch (Exception $e) {
    echo json_encode([]);
}
