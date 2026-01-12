<?php
// ajax/remisiones/detalle.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;



echo '<style>
#tablaDetalle td, #tablaDetalle th {
    padding: 5px 10px; 
}
</style>';

$con = "SELECT    
    mov_remisiones.precio_unitario,
    mov_remisiones.cantidad AS cantidad_remision,
    cat_productos.nombre,
    cat_productos.clave,
    cat_productos.descripcion
FROM
    mov_remisiones
    INNER JOIN cat_productos 
        ON mov_remisiones.id_producto = cat_productos.id_producto
WHERE mov_remisiones.id_remision = $id";

$rs = $clsConsulta->consultaGeneral($con);
// var_dump($_POST);
// var_dump($rs);
// echo '<br><br>' . $con . '<br>';
echo '<table id="tablaDetalle" class="table table-bordered table-striped">
<thead class="bg-info">
<tr>
    <th class="text-center text-white">Cantidad</th>
    <th class="text-center text-white">Clave</th>
    <th class="text-center text-white">Nombre</th>
    <th class="text-center text-white">Precio</th>
    <th class="text-center text-white">Total</th>
</tr>
<tbody>';
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        $total = 0;
        $precio = $val['precio_unitario'];
        $cantidad = $val['cantidad_remision'];
        echo '<tr>';
        echo '<td>' . $cantidad . '</td>';
        echo '<td>' . $val['clave'] . '</td>';
        echo '<td>' . $val['nombre'] . '</td>';
        echo '<td class="text-end">$' . number_format($precio, 2, ".", ",") . '</td>';
        $total = $cantidad * $precio;
        echo '<td class="text-end">$' . number_format($total, 2, ".", ",") . '</td>';
        echo '</tr>';
    }
}
echo '</tbody>
</table>';
