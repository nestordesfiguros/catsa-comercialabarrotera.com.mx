<?php
// ajax/almacen-salidas/tabla-salidas.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

$idAlmacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

$sql = "
    SELECT
        s.id,
        s.fecha,
        s.estatus,
        s.id_almacen,
        a.almacen,
        s.id_cliente,
        COALESCE(cl.razon_social, s.destino_libre, '') AS destino,
        COALESCE(SUM(ms.cantidad),0) AS piezas
    FROM cab_salidas_almacen s
    INNER JOIN cat_almacenes a ON a.id = s.id_almacen
    LEFT JOIN cat_clientes cl ON cl.id = s.id_cliente
    LEFT JOIN mov_salidas_almacen ms ON ms.id_salida = s.id
    WHERE s.id_empresa = {$idEmpresa}
      AND a.id_empresa = {$idEmpresa}
";

if ($idAlmacen > 0) {
    $sql .= " AND s.id_almacen = {$idAlmacen} ";
}

$sql .= "
    GROUP BY s.id, s.fecha, s.estatus, s.id_almacen, a.almacen, s.id_cliente, destino
    ORDER BY s.id DESC
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $row) {
        $id = (int)$row['id'];
        $fecha = htmlspecialchars($row['fecha']);
        $almacen = htmlspecialchars($row['almacen']);
        $destino = htmlspecialchars($row['destino']);
        $piezasNum = (float)$row['piezas'];
        $piezas = number_format($piezasNum, 2, ".", ",");

        $estatusTxt = (string)$row['estatus'];
        $estatusInt = 1;
        $cls = 'secondary';

        if ($estatusTxt === 'pendiente') {
            $estatusInt = 1;
            $cls = 'warning';
        }
        if ($estatusTxt === 'procesada') {
            $estatusInt = 2;
            $cls = 'success';
        }
        if ($estatusTxt === 'cancelada') {
            $estatusInt = 3;
            $cls = 'danger';
        }

        $colId = '<div class="text-end">' . $id . '</div>';
        $colFecha = '<div class="text-center">' . $fecha . '</div>';
        $colAlmacen = '<div>' . $almacen . '</div>';
        $colDestino = '<div>' . $destino . '</div>';
        $colPiezas = '<div class="text-end">' . $piezas . '</div>';

        $valoresJs = $id . ", '" . addslashes($fecha) . "', '" . addslashes($almacen) . "', '" . addslashes($destino) . "', '" . $piezas . "', '" . addslashes($estatusTxt) . "'";

        $detalle = '<div class="text-center">
            <i class="far fa-list-alt fa-lg text-info"
               onclick="detalle(' . $valoresJs . ');"
               data-bs-toggle="modal"
               data-bs-target="#detalleModal"
               style="cursor:pointer"></i>
        </div>';

        $pdf = '<div class="text-center">
            <a href="pdf/ver-salida.php?id=' . $id . '" target="_blank" title="Ver PDF">
                <i class="fas fa-file-pdf fa-lg text-danger"></i>
            </a>
        </div>';

        $estatus = '<div class="text-center">
            <span class="badge bg-' . $cls . '"
                  style="cursor:pointer;"
                  onclick="fnMostrarEstatus(' . $id . ',' . $estatusInt . ');">' . $estatusTxt . '</span>
        </div>';

        $data[] = [$colId, $colFecha, $colAlmacen, $colDestino, $colPiezas, $detalle, $pdf, $estatus];
    }
}

echo json_encode(["data" => $data]);
