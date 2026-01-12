<?php
// ajax/gastos/guardar-categoria.php
session_start();
require_once '../../lib/clsConsultas.php';

$cls        = new Consultas();
$idEmpresa  = intval($_SESSION['id_empresa'] ?? 0);

$nombre = trim($_POST['nombreCategoria'] ?? '');
$desc   = trim($_POST['descripcionCategoria'] ?? '');

if ($idEmpresa <= 0) {
    echo json_encode(["ok" => false, "msg" => "Empresa inválida"]);
    exit;
}
if ($nombre === '') {
    echo json_encode(["ok" => false, "msg" => "Nombre requerido"]);
    exit;
}

$sqlExiste = "SELECT id_categoria FROM cat_gastos_categorias
              WHERE id_empresa={$idEmpresa} AND nombre=" . $cls->sanitizar($nombre) . " LIMIT 1";
$rs = $cls->aplicaQuery($sqlExiste);
if ($cls->numrows > 0) {
    $row = mysqli_fetch_assoc($rs);
    echo json_encode(["ok" => true, "id_categoria" => intval($row['id_categoria']), "msg" => "Ya existía, reutilizado"]);
    exit;
}

$sql = "INSERT INTO cat_gastos_categorias
        (id_empresa, nombre, descripcion, activo, fecha_creacion)
        VALUES ({$idEmpresa}, " . $cls->sanitizar($nombre) . ",
                " . ($desc === '' ? 'NULL' : $cls->sanitizar($desc)) . ",
                1, NOW())";
$ok = $cls->guardarGeneral($sql);

echo json_encode($ok ? ["ok" => true, "id_categoria" => intval($cls->ultimoid)]
    : ["ok" => false, "msg" => "No se pudo guardar la categoría"]);
