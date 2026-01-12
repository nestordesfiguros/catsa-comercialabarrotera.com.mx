<?php
// ajax/compras/productos.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode([]);
    exit;
}

// Catálogo tolerante por empresa
$whereBase = "p.estatus=1 AND (
    p.id_empresa={$idEmpresa}
    OR EXISTS (SELECT 1 FROM inventarios i WHERE i.id_empresa={$idEmpresa} AND i.id_producto=p.id_producto)
    OR EXISTS (SELECT 1 FROM cat_almacenes a WHERE a.id=p.id_almacen AND a.id_empresa={$idEmpresa})
)";

$con = "SELECT p.id_producto, p.clave, p.nombre, p.precio_venta
        FROM cat_productos p
        WHERE {$whereBase}
        ORDER BY p.nombre ASC";

$rs = $clsConsulta->consultaGeneral($con);

$productos = [];
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        $productos[] = [
            'id_producto' => (int)$val['id_producto'],
            'clave' => $val['clave'],
            'nombre' => $val['nombre'],
            'precio_venta' => $val['precio_venta'],
        ];
    }
}

echo json_encode($productos);
