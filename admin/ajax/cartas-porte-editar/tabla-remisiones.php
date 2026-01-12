<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idCarta = intval($_POST['id'] ?? 0);
$html = '';
$totalGeneral = 0;

// Recuperar remisiones de sesión
$idsSesion = $_SESSION['remisiones_cartaporte'] ?? [];
$idsSesionStr = empty($idsSesion) ? '0' : implode(',', array_map('intval', $idsSesion));

$con = "
    SELECT 
        r.id,
        CONCAT('R-', LPAD(r.id,6,'0')) AS folio,
        c.razon_social AS cliente,
        DATE(r.fecha) AS fecha,
        IFNULL(SUM(m.cantidad*m.precio),0) AS total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c ON r.id_cliente = c.id
    LEFT JOIN mov_remisiones m ON m.id_remision = r.id
    WHERE r.id IN ($idsSesionStr)
    GROUP BY r.id
    ORDER BY r.id DESC
";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $html .= '<tr>
            <td>' . $val['folio'] . '</td>
            <td>' . $val['cliente'] . '</td>
            <td>' . $val['fecha'] . '</td>
            <td class="text-end">$' . number_format($val['total'], 2) . '</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btnQuitarRemision" data-id="' . $val['id'] . '">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>';
        $totalGeneral += floatval($val['total']);
    }
} else {
    $html = '<tr><td colspan="5" class="text-center">No hay remisiones agregadas</td></tr>';
}

echo $html;
