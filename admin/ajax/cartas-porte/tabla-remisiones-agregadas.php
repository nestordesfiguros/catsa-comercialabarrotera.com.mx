<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$remisiones = $_SESSION['remisiones_cartaporte'] ?? [];

if (empty($remisiones)) {
    echo '<tr><td colspan="5" class="text-center">No hay remisiones agregadas</td></tr>';
    exit;
}

$ids = implode(',', array_map('intval', $remisiones));

$sql = "
    SELECT 
        r.id,
        CONCAT('R-', LPAD(r.id,6,'0')) AS folio,
        c.razon_social AS cliente,
        DATE(r.fecha) AS fecha,
        IFNULL(SUM(m.cantidad*m.precio),0) AS total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c ON r.id_cliente = c.id
    LEFT JOIN mov_remisiones m ON m.id_remision = r.id
    WHERE r.id IN ($ids)
    GROUP BY r.id
    ORDER BY r.id DESC
";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    foreach ($res as $row) {
        echo '<tr>
                <td>' . $row['folio'] . '</td>
                <td>' . $row['cliente'] . '</td>
                <td>' . $row['fecha'] . '</td>
                <td>$' . number_format($row['total'], 2) . '</td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm btnQuitarRemision" data-id="' . $row['id'] . '">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-center">No hay remisiones agregadas</td></tr>';
}
