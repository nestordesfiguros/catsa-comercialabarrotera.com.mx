<?php
// ajax/pedidos/tabla-pedidos.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function safe($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$data_array = [];

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

$estatus = isset($_GET['estatus']) && $_GET['estatus'] !== '' ? addslashes($_GET['estatus']) : 'pendiente';
$id_vendedor = isset($_GET['id_vendedor']) && $_GET['id_vendedor'] !== '' ? intval($_GET['id_vendedor']) : null;

$con = "SELECT
    cab_pedidos.*,
    cab_pedidos.id as idPedido,
    cat_clientes.id as idCliente,
    cat_clientes.razon_social,
    cat_clientes.nombre_comercial,
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) as nombre_vendedor
FROM
    cab_pedidos
    INNER JOIN cat_clientes ON (cab_pedidos.id_cliente = cat_clientes.id)
    LEFT JOIN cat_vendedores v ON cab_pedidos.id_vendedor = v.id
WHERE cab_pedidos.id_empresa = {$idEmpresa}
  AND cat_clientes.id_empresa = {$idEmpresa} ";

if ($estatus !== '') {
    $con .= " AND cab_pedidos.estatus = '" . addslashes($estatus) . "' ";
}
if (!is_null($id_vendedor)) {
    $con .= " AND cab_pedidos.id_vendedor = " . $id_vendedor . " ";
}

$con .= " ORDER BY cab_pedidos.fecha DESC";

$rs = $clsConsulta->consultaGeneral($con);

if (is_array($rs) && $clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $idPedido = (int)$val['idPedido'];
        $idCliente = (int)$val['idCliente'];
        $fecha = $val['fecha'];
        $fecha_solo = '<div class="text-center">' . date('Y-m-d', strtotime($fecha)) . '</div>';

        $pedidoNo = '<div class="text-end">' . $idPedido . '</div>';
        $rsnc = safe($val['razon_social']) . ' / ' . safe($val['nombre_comercial']);
        $razon_social = '<div>' . $rsnc . '</div>';
        $vendedor = '<div>' . safe($val['nombre_vendedor'] ?? '-') . '</div>';

        $total = isset($val['total']) ? number_format((float)$val['total'], 2, ".", ",") : "0.00";
        $valores = $idPedido . ", '" . addslashes($fecha) . "', '" . addslashes($rsnc) . "', '" . $total . "'";

        $imprimir = '<div class="text-center">
            <i class="far fa-list-alt fa-lg text-info" onclick="detalle(' . $valores . ');" data-bs-toggle="modal" data-bs-target="#detalleModal" style="cursor:pointer"></i>
        </div>';

        $pdf = '<div class="text-center"><a href="pdf/pdf-pedidos.php?id=' . $idPedido . '&idc=' . $idCliente . '" target="_blank"><i class="fas fa-file-pdf fa-lg text-danger"></i></a></div>';

        $editar = '<div class="text-center">';
        if (($val['estatus'] ?? '') === 'pendiente') {
            $editar .= '<i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar(' . $idPedido . ');" ></i>';
        } else {
            $editar .= '<i class="fas fa-edit fa-lg text-secondary"></i>';
        }
        $editar .= '</div>';

        $estatusVal = strtolower($val['estatus'] ?? '');
        if ($estatusVal === 'pendiente') {
            $estatusHtml = '<div class="text-center text-danger" onclick="fnMostrarEstatus(' . $idPedido . ', \'pendiente\');" style="cursor:pointer;">Pendiente</div>';
        } elseif ($estatusVal === 'procesado') {
            $estatusHtml = '<div class="text-center text-success">Procesado</div>';
        } elseif ($estatusVal === 'cancelado') {
            $estatusHtml = '<div class="text-center text-secondary">Cancelado</div>';
        } else {
            $estatusHtml = '<div class="text-center">-</div>';
        }

        $data_array[] = [
            $pedidoNo,
            $fecha_solo,
            $razon_social,
            $vendedor,
            $editar,
            $imprimir,
            $pdf,
            $estatusHtml
        ];
    }
}

echo json_encode(["data" => $data_array]);
