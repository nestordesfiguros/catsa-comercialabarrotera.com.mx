<?php
// admin/ajax/productos/busca-clave.php

session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

// Inputs
$clave = isset($_POST['clave']) ? trim($_POST['clave']) : '';
$id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$id_almacen_post = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

// Empresa actual (si existe en sesión)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

if ($clave === '') {
    echo json_encode(['existe' => false]);
    exit;
}

// Normalizar clave (por si viene con espacios raros)
$clave = trim($clave);

// Escapar clave para SQL
$claveEsc = $clsConsulta->real_escape_string($clave);

// Determinar almacén a validar
$id_almacen = 0;

// Si estamos en edición, tomamos el almacén REAL del producto (no dependemos del JS)
if ($id_producto > 0) {
    $qAlm = "SELECT id_almacen FROM cat_productos WHERE id_producto = {$id_producto} LIMIT 1";
    $rAlm = $clsConsulta->consultaGeneral($qAlm);
    if ($clsConsulta->numrows > 0 && isset($rAlm[1]['id_almacen'])) {
        $id_almacen = (int)$rAlm[1]['id_almacen'];
    }
} else {
    // Alta: si mandan id_almacen, úsalo
    if ($id_almacen_post > 0) {
        $id_almacen = $id_almacen_post;
    }
}

// Construir WHERE
$where = "p.clave = '{$claveEsc}'";

if ($id_producto > 0) {
    // Excluir el mismo producto
    $where .= " AND p.id_producto <> {$id_producto}";
}

if ($id_almacen > 0) {
    // Validación por almacén
    $where .= " AND p.id_almacen = {$id_almacen}";
}

// Scope empresa (opcional pero recomendado si hay sesión)
$joinEmpresa = "";
if ($idEmpresa > 0) {
    $joinEmpresa = " INNER JOIN cat_almacenes a ON a.id = p.id_almacen AND a.id_empresa = {$idEmpresa}";
} else {
    // sin empresa, no forzamos join
    $joinEmpresa = "";
}

// Consulta
$sql = "SELECT COUNT(*) AS total
        FROM cat_productos p
        {$joinEmpresa}
        WHERE {$where}";

$res = $clsConsulta->consultaGeneral($sql);

$existe = false;
if ($clsConsulta->numrows > 0 && isset($res[1]['total'])) {
    $existe = ((int)$res[1]['total'] > 0);
}

echo json_encode(['existe' => $existe]);
