<?php
// ajax/remisiones/tabla-remisiones.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$data_array = [];

// Empresa
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

if ($idEmpresa <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

// Filtros
$estatus   = isset($_POST['estatus']) ? trim((string)$_POST['estatus']) : 'pendiente';
$id_cliente  = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
$id_vendedor = isset($_POST['id_vendedor']) ? (int)$_POST['id_vendedor'] : 0;

$where = "WHERE r.id_empresa = {$idEmpresa} ";
if ($estatus !== '') {
    $estatusEsc = mysqli_real_escape_string($clsConsulta->getConexion(), $estatus);
    $where .= " AND LOWER(r.estatus) = '{$estatusEsc}' ";
}
if ($id_cliente > 0) {
    $where .= " AND r.id_cliente = {$id_cliente} ";
}
if ($id_vendedor > 0) {
    $where .= " AND r.id_vendedor = {$id_vendedor} ";
}

$con = "SELECT 
    r.*,
    r.id AS idRemision,
    c.razon_social,
    c.nombre_comercial,
    COALESCE(totales.monto, 0) AS monto
FROM cab_remisiones r
INNER JOIN cat_clientes c 
    ON r.id_cliente = c.id
   AND c.id_empresa = {$idEmpresa}
LEFT JOIN (
    SELECT id_remision, COALESCE(SUM(cantidad * precio_unitario), 0) AS monto
    FROM mov_remisiones
    GROUP BY id_remision
) totales ON r.id = totales.id_remision
{$where}
ORDER BY r.fecha DESC";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        if ($v === 0) continue;

        $id = (int)$val['idRemision'];
        $fecha = $val['fecha'];
        $monto_raw = $val['monto'] ?? 0;

        $fecha_solo = '<div class="text-center">' . date('Y-m-d', strtotime($fecha)) . '</div>';
        $estatusActual = '<div class="text-center">' . formatearEstatus(strtolower($val['estatus'] ?? ''), $id) . '</div>';

        $monto_display = '<div class="text-end">$' . number_format((float)$monto_raw, 2, ".", ",") . '</div>';
        $idHtml = '<div class="text-end">' . $id . '</div>';
        $razon_social = '<div>' . htmlspecialchars($val['razon_social'] ?? '', ENT_QUOTES) . ' / ' . htmlspecialchars($val['nombre_comercial'] ?? '', ENT_QUOTES) . '</div>';

        $tipoVenta = '<div class="text-center">' . strtoupper($val['tipo_venta'] ?? '') . '</div>';

        $valores_detalle = $id . ", '" . addslashes($val['fecha'] ?? '') . "', '" . addslashes(($val['razon_social'] ?? '') . ' / ' . ($val['nombre_comercial'] ?? '')) . "', '" . number_format((float)$monto_raw, 2, ".", ",") . "'";

        $detalles = '<div class="text-center">
            <i class="far fa-list-alt fa-lg text-info" onclick="detalle(' . $valores_detalle . ')" data-bs-toggle="modal" data-bs-target="#detalleModal" style="cursor:pointer;"></i>
        </div>';

        $pdf = '<div class="text-center"><a href="pdf/pdf-remisiones.php?id=' . $id . '&idc=' . (int)$val['id_cliente'] . '" target="_blank"><i class="fas fa-file-pdf fa-lg text-danger"></i></a></div>';

        $data_array[] = [
            $idHtml,
            $fecha_solo,
            $razon_social,
            $tipoVenta,
            $monto_display,
            $detalles,
            $pdf,
            $estatusActual
        ];
    }
}

echo json_encode(["data" => $data_array]);

function formatearEstatus($estatus, $id)
{
    $clases = [
        'pendiente' => 'badge bg-warning',
        'procesada' => 'badge bg-success',
        'cancelada' => 'badge bg-danger'
    ];

    $texto = ucfirst($estatus);
    $clase = $clases[$estatus] ?? 'badge bg-secondary';

    if ($estatus === 'pendiente') {
        return '<span class="' . $clase . '" style="cursor:pointer;" onclick="parent.cambiarEstatus(' . $id . ', \'' . $estatus . '\')">' . $texto . '</span>';
    }

    return '<span class="' . $clase . '">' . $texto . '</span>';
}
