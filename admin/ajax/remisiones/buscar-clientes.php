<?php
// ajax/remisiones/buscar-clientes.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$termino = isset($_POST['search']) ? trim($_POST['search']) : '';
$termino = addslashes($termino);

$sql = "
    SELECT id, razon_social
    FROM cat_clientes
    WHERE estatus = 1
";
if ($idEmpresa > 0) {
    $sql .= " AND id_empresa = {$idEmpresa} ";
}
if ($termino !== '') {
    $sql .= " AND razon_social LIKE '%{$termino}%'";
}
$sql .= " ORDER BY razon_social LIMIT 20";

$rs = $clsConsulta->consultaGeneral($sql);

$resultado = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $k => $row) {
        if ($k === 0) continue;
        $resultado[] = [
            'id' => (int)$row['id'],
            'text' => $row['razon_social']
        ];
    }
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
