<?php
// ajax/reportes/buscar-categorias.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

try {
    $sql = "SELECT id_categoria as id, nombre_categoria as nombre 
            FROM cat_categorias 
            ORDER BY nombre_categoria";

    $resultados = $clsConsulta->consultaGeneral($sql);

    $categorias = [];
    if ($clsConsulta->numrows > 0) {
        foreach ($resultados as $key => $row) {
            if ($key === 0) continue; // Saltar el índice 0
            $categorias[] = [
                'id' => $row['id_categoria'],
                'nombre' => $row['nombre_categoria']
            ];
        }
    }

    echo json_encode($categorias);
} catch (Exception $e) {
    echo json_encode([]);
}
