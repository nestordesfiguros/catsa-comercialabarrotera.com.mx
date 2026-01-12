<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$total = 0;
$remisiones = $_SESSION['cartas_porte_remisiones'] ?? [];

if (empty($remisiones)) {
    echo '<tr><td colspan="5" class="text-center">No hay remisiones agregadas</td></tr>';
} else {
    foreach ($remisiones as $id_remision) {
        // Consulta para obtener datos de la remisión
        $sql = "SELECT r.id, r.folio, r.fecha, r.total, c.nombre AS cliente
                FROM cab_remisiones r
                INNER JOIN cat_clientes c ON r.id_cliente = c.id
                WHERE r.id = " . intval($id_remision) . " 
                LIMIT 1";

        $res = $clsConsulta->aplicaQuery($sql);

        if ($clsConsulta->numrows($res) > 0) {
            foreach ($res as $row) {
                $total += $row['total'];
                echo '<tr>
                        <td>' . $row['folio'] . '</td>
                        <td>' . $row['cliente'] . '</td>
                        <td>' . $row['fecha'] . '</td>
                        <td class="text-end">' . number_format($row['total'], 2) . '</td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm btnQuitarRemision" data-id="' . $row['id'] . '">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>';
            }
        }
    }
}
?>
<script>
    $("#totalGeneral").text("<?= number_format($total, 2) ?>");
</script>