<?php
// ajax/gastos/catalogos.php
session_start();
require_once '../../lib/clsConsultas.php';

$consulta  = new Consultas();
$idEmpresa = $_SESSION['id_empresa'];

$sqlCat = "SELECT id_categoria, nombre
           FROM cat_gastos_categorias
           WHERE id_empresa = " . intval($idEmpresa) . " AND activo=1
           ORDER BY nombre";
$rsCat = $consulta->aplicaQuery($sqlCat);
$categorias = [];
while ($r = mysqli_fetch_assoc($rsCat)) {
    $categorias[] = $r;
}

$sqlMet = "SELECT id AS id_metodo, descripcion AS nombre
           FROM cat_metodos_pago
           ORDER BY id";
$rsMet = $consulta->aplicaQuery($sqlMet);
$metodos = [];
while ($m = mysqli_fetch_assoc($rsMet)) {
    $metodos[] = $m;
}

echo json_encode(["ok" => true, "categorias" => $categorias, "metodos" => $metodos]);
