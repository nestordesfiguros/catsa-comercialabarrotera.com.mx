<?php
// admin/ajax/lista-precios/actualizar-nombre-lista.php

session_start();
include '../../lib/clsConsultas.php';

$clsConsulta = new Consultas();

$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$lista  = isset($_POST['lista']) ? (int)$_POST['lista'] : 1;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($idEmpresa <= 0) {
    echo 'error';
    exit;
}
if ($lista < 1 || $lista > 5) $lista = 1;
if (strlen($nombre) < 2) {
    echo 'error';
    exit;
}

$field = "precio01_nombre";
if ($lista === 2) $field = "precio02_nombre";
if ($lista === 3) $field = "precio03_nombre";
if ($lista === 4) $field = "precio04_nombre";
if ($lista === 5) $field = "precio05_nombre";

// Actualiza TODOS los productos de almacenes de la empresa
$sql = "UPDATE cat_productos p
        INNER JOIN cat_almacenes a ON a.id = p.id_almacen
        SET p.{$field} = ?
        WHERE p.estatus = 1
          AND a.id_empresa = ?
          AND a.estatus = 1";

$ok = $clsConsulta->consultaPreparada($sql, [$nombre, $idEmpresa], "si");
echo ($ok !== false) ? 'success' : 'error';
