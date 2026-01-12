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

    // Formatear fechas
    $fecha_inicio = ($fecha_inicio_fecha) ? $fecha_inicio_fecha . ' 00:00:00' : null;
    $fecha_fin = ($fecha_fin_fecha) ? $fecha_fin_fecha . ' 23:59:59' : null;

    // Insertar en cat_ofertas
    $campos = [
        "nombre" => $nombre,
        "descripcion" => $descripcion,
        "fecha_inicio" => $fecha_inicio,
        "fecha_fin" => $fecha_fin,
        "tipo_oferta" => $tipo_oferta,
        "estatus" => 1,
        "id_usuario" => $_SESSION['id_usuario']
    ];

    $clsConsulta->guardarGeneral("cat_ofertas", $campos);
    $id_oferta = $clsConsulta->ultimoid;

    // Insertar en mov_ofertas_productos
    foreach ($productos as $i => $id_producto) {
        $registro = [
            "id_oferta" => $id_oferta,
            "id_producto" => $id_producto
        ];

        switch ($tipo_oferta) {
            case 'porcentaje':
            case 'monto_fijo':
                $registro["valor_oferta"] = isset($valor_oferta[$i]) ? $valor_oferta[$i] : 0;
                break;

            case 'cantidad':
                $registro["valor_oferta"] = isset($valor_oferta[$i]) ? $valor_oferta[$i] : 0;
                $registro["cantidad_minima"] = isset($cantidad_minima[$i]) ? $cantidad_minima[$i] : 1;
                break;

            case 'combo':
                $registro["valor_oferta"] = isset($valor_oferta[$i]) ? $valor_oferta[$i] : 0;
                $registro["producto_bonus"] = (isset($tipo_producto[$i]) && $tipo_producto[$i] === 'bonus') ? 1 : 0;
                break;
        }

        $clsConsulta->guardarGeneral("mov_ofertas_productos", $registro);
    }

    echo 0; // Éxito
} else {
    echo 1; // Error en método
}
