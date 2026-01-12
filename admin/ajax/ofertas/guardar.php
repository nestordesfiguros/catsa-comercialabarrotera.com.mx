<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolección de datos
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $tipo_oferta = $_POST['tipo_oferta'];
    $fecha_inicio_fecha = $_POST['fecha_inicio_fecha'] ?? null;
    $fecha_fin_fecha = $_POST['fecha_fin_fecha'] ?? null;
    $productos = $_POST['productos'] ?? [];

    $valor_oferta = $_POST['valor_oferta'] ?? [];
    $cantidad_minima = $_POST['cantidad_minima'] ?? [];
    $tipo_producto = $_POST['tipo_producto'] ?? [];

    $id_usuario = $_SESSION['id_user'];

    // Formatear fechas
    $fecha_inicio = ($fecha_inicio_fecha) ? $fecha_inicio_fecha . ' 00:00:00' : null;
    $fecha_fin = ($fecha_fin_fecha) ? $fecha_fin_fecha . ' 23:59:59' : null;

    // Construcción de query manual (IMPORTANTE: comillas en strings y fechas)
    $query = "INSERT INTO cat_ofertas (nombre, descripcion, fecha_inicio, fecha_fin, tipo_oferta, estatus, id_usuario) VALUES (
        '" . addslashes($nombre) . "',
        '" . addslashes($descripcion) . "',
        '" . $fecha_inicio . "',
        '" . $fecha_fin . "',
        '" . $tipo_oferta . "',
        1,
        $id_usuario
    )";

    $clsConsulta->guardarGeneral($query);
    $id_oferta = $clsConsulta->ultimoid;

    // Insertar productos relacionados
    foreach ($productos as $i => $id_producto) {
        $id_producto = (int)$id_producto;
        $valor = isset($valor_oferta[$i]) ? (float)$valor_oferta[$i] : 0;
        $minima = isset($cantidad_minima[$i]) ? (int)$cantidad_minima[$i] : 1;
        $es_bonus = (isset($tipo_producto[$i]) && $tipo_producto[$i] === 'bonus') ? 1 : 0;

        $queryProducto = "INSERT INTO mov_ofertas_productos (id_oferta, id_producto";

        $valores = "VALUES ($id_oferta, $id_producto";

        if ($tipo_oferta === 'porcentaje' || $tipo_oferta === 'monto_fijo') {
            $queryProducto .= ", valor_oferta)";
            $valores .= ", $valor)";
        } elseif ($tipo_oferta === 'cantidad') {
            $queryProducto .= ", valor_oferta, cantidad_minima)";
            $valores .= ", $valor, $minima)";
        } elseif ($tipo_oferta === 'combo') {
            $queryProducto .= ", valor_oferta, producto_bonus)";
            $valores .= ", $valor, $es_bonus)";
        }

        $clsConsulta->guardarGeneral($queryProducto . " " . $valores);
    }

    echo 0; // Éxito
} else {
    echo 1; // Error de método
}
