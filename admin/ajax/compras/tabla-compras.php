<?php
// ajax/compras/tabla-compras.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

$data_array = [];

$con = "SELECT
          c.id,
          c.fecha,
          c.id_proveedor,
          c.estatus,
          COALESCE(SUM(mc.cantidad * mc.precio),0) AS total_calculado,
          p.razon_social,
          p.nombre_Comercial
        FROM cab_compras c
        INNER JOIN cat_proveedores p  ON p.id = c.id_proveedor
        LEFT JOIN mov_compras mc      ON mc.id_orden_compra = c.id
        WHERE c.id_empresa = {$idEmpresa}
          AND p.id_empresa = {$idEmpresa}
        GROUP BY
          c.id, c.fecha, c.id_proveedor, c.estatus,
          p.razon_social, p.nombre_Comercial
        ORDER BY c.id DESC";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        $idProveedor = (int)$val['id_proveedor'];

        $id     = '<div class="text-end">' . (int)$val['id'] . '</div>';
        $fecha  = '<div class="text-center">' . htmlspecialchars($val['fecha']) . '</div>';
        $rz     = '<div>' . htmlspecialchars($val['nombre_Comercial']) . ' / ' . htmlspecialchars($val['razon_social']) .  '</div>';

        $totalCalc = (float)$val['total_calculado'];
        $total = '<div class="text-end">$' . number_format($totalCalc, 2, ".", ",") . '</div>';

        $valores = (int)$val['id'] . ", '" . addslashes($val['fecha']) . "', '" . addslashes($val['razon_social'] . ' / ' . $val['nombre_Comercial']) . "', '" . number_format($totalCalc, 2, ".", ",") . "'";

        $documento = '<div class="text-center">
                <i class="far fa-list-alt fa-lg text-info" onclick="detalle(' . $valores . ');" data-bs-toggle="modal" data-bs-target="#detalleModal" style="cursor:pointer"></i>
              </div>';

        $pdf = '<div class="text-center"><a href="pdf/ver-pdf.php?id=' . (int)$val['id'] . '&idp=' . $idProveedor . '" target="_blank"><i class="fas fa-file-pdf fa-lg text-danger"></i></a></div>';

        switch ($val['estatus']) {
            case 'pendiente':
                $estado = 'Pendiente';
                $idEstado = 1;
                $styleEstado = 'danger';
                break;
            case 'procesada':
                $estado = 'Procesada';
                $idEstado = 2;
                $styleEstado = 'success';
                break;
            case 'cancelada':
                $estado = 'Cancelada';
                $idEstado = 3;
                $styleEstado = 'secondary';
                break;
            default:
                $estado = 'Pendiente';
                $idEstado = 1;
                $styleEstado = 'danger';
        }

        $estatus = '<div class="text-center text-' . $styleEstado . '" onclick="fnMostrarEstatus(' . (int)$val['id'] . ',' . (int)$idEstado . ');" style="cursor:pointer;">' . $estado . '</div>';

        $data_array[] = array($id, $fecha, $rz, $total, $documento, $pdf, $estatus);
    }
}

echo json_encode(["data" => $data_array]);
