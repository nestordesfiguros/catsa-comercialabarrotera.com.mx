<?php
// admin/ajax/productos/guardar.php

session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$clave        = isset($_POST['clave']) ? trim($_POST['clave']) : '';
$nombre       = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$descripcion  = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$unidad_medida = isset($_POST['unidad_medida']) ? (int)$_POST['unidad_medida'] : 0;
$excento      = isset($_POST['excento']) ? (int)$_POST['excento'] : 0;

if ($clave === '' || $nombre === '' || $unidad_medida <= 0) {
    echo "error_datos";
    exit;
}

$clave_sql       = $clsConsulta->sanitizar($clave);
$nombre_sql      = $clsConsulta->sanitizar($nombre);
$descripcion_sql = $clsConsulta->sanitizar($descripcion);

// Nota: aquí NO se capturan precios (se dejan en 0.00 para compatibilidad)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$sql = "
INSERT INTO cat_productos (
    clave,
    nombre,
    descripcion,
    unidad_medida,
    excento,

    precio_venta,
    precio_minimo_venta,
    precio_sugerido,
    precio_maximo_venta,

    precio01,
    precio02,
    precio03,
    precio04,
    precio05,

    id_empresa,
    estado,
    estatus
) VALUES (
    {$clave_sql},
    {$nombre_sql},
    {$descripcion_sql},
    {$unidad_medida},
    {$excento},

    0.00,
    0.00,
    0.00,
    0.00,

    0.00,
    0.00,
    0.00,
    0.00,
    0.00,

    {$idEmpresa},
    'activo',
    1
)
";

try {
    $ok = $clsConsulta->guardarGeneral($sql);
    echo $ok ? "success" : "error";
} catch (Exception $e) {
    error_log("Error guardar.php: " . $e->getMessage());
    echo "error";
}
