<?php
// ajax/almacen-entradas/tabla-entradas.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

$idAlmacen = isset($_POST['id_almacen']) ? (int) $_POST['id_almacen'] : 0;

$data_array = [];

$con = "SELECT
    e.id,
    e.fecha,
    e.id_proveedor,
    e.id_almacen,
    e.pedido_incompleto,
    e.estatus,
    p.razon_social,
    p.nombre_Comercial,
    a.almacen
FROM cab_entradas_almacen e
INNER JOIN cat_proveedores p ON e.id_proveedor = p.id
INNER JOIN cat_almacenes a ON e.id_almacen = a.id
WHERE e.id_empresa = {$idEmpresa}
  AND a.id_empresa = {$idEmpresa}
  AND p.id_empresa = {$idEmpresa}";

if ($idAlmacen > 0) {
    $con .= " AND e.id_almacen = {$idAlmacen}";
}

$con .= " ORDER BY e.id DESC";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        $idProveedor = (int)$val['id_proveedor'];

        $id = '<div class="text-end">' . (int)$val['id'] . '</div>';
        $fecha = '<div class="text-center">' . htmlspecialchars($val['fecha']) . '</div>';
        $razon_social = '<div>' . htmlspecialchars($val['razon_social']) . ' / ' . htmlspecialchars($val['nombre_Comercial']) . '</div>';

        $valores = (int)$val['id'] . ", '" . addslashes($val['fecha']) . "', '" . addslashes($val['razon_social'] . ' / ' . $val['nombre_Comercial']) . "'";

        $detalle = '<div class="text-center">
            <i class="far fa-list-alt fa-lg text-info"
               onclick="detalle(' . $valores . ');"
               data-bs-toggle="modal"
               data-bs-target="#detalleModal"
               style="cursor:pointer"></i>
        </div>';

        $completa = ((int)$val['pedido_incompleto'] === 1)
            ? '<div class="text-center text-danger">Incompleta</div>'
            : '<div class="text-center text-success">Completa</div>';

        $data_array[] = array(
            $id,
            $fecha,
            $razon_social,
            $completa,
            $detalle
        );
    }
}

echo json_encode(["data" => $data_array]);
