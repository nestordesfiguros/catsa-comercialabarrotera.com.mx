<?php
// Incluimos la clase de consultas
include('../../../lib/clsConsultas.php');

// Creamos la instancia de la clase Consultas
$clsConsulta = new Consultas();

// Inicializamos las variables
$vendedor_id = isset($_GET['vendedor_id']) ? $_GET['vendedor_id'] : null;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Preparamos la consulta SQL
$query = "SELECT v.id AS id_vendedor, CONCAT(v.nombre, ' ', v.apellido1, ' ', v.apellido2) AS nombre_completo 
          FROM cat_vendedores v 
          WHERE v.estatus = 1"; // Filtramos solo vendedores activos

// Si hay un vendedor seleccionado, agregamos el filtro
if ($vendedor_id) {
    $query .= " AND v.id = " . intval($vendedor_id);
}

// Ejecutamos la consulta
$consulta_vendedores = $clsConsulta->consultaGeneral($query);

// Retornamos los resultados en formato JSON
echo json_encode($consulta_vendedores);
