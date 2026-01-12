<?php
// ajax/almacen-salidas/productos-por-almacen.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idAlmacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

if ($idEmpresa <= 0 || $idAlmacen <= 0) {
    echo json_encode(["data" => []]);
    exit;
}

// Validar almacén pertenece a empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id={$idAlmacen} AND id_empresa={$idEmpresa} AND estatus=1 LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo json_encode(["data" => []]);
    exit;
}

$sql = "
    SELECT
        i.id_producto,
        p.clave,
        p.nombre,
        i.cantidad AS existencia,
        u.nombre_unidad AS unidad
    FROM inventarios i
    INNER JOIN cat_productos p ON p.id_producto = i.id_producto
    LEFT JOIN cat_unidades_de_medida u ON u.id_unidad = p.unidad_medida
    WHERE i.id_empresa = {$idEmpresa}
      AND i.id_almacen = {$idAlmacen}
      AND i.cantidad > 0
      AND p.estatus = 1
    ORDER BY p.nombre ASC
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $row) {
        $data[] = [
            "id_producto" => (int)$row["id_producto"],
            "clave" => $row["clave"],
            "nombre" => $row["nombre"],
            "existencia" => number_format((float)$row["existencia"], 2, ".", ","),
            "unidad" => $row["unidad"] ?: "PZA"
        ];
    }
}

echo json_encode(["data" => $data]);
