<?php
// ajax/remisiones/obtener-remision.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Empresa
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$whereEmpresa = "";
if ($idEmpresa > 0) $whereEmpresa = " AND r.id_empresa = {$idEmpresa} ";

// Cabecera (incluye validación de cliente por empresa)
$sqlR = "
    SELECT
        r.id,
        r.fecha,
        r.id_cliente,
        c.razon_social AS cliente_nombre,
        r.direccion_envio,
        LOWER(r.estatus) AS estatus,
        r.total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c 
        ON c.id = r.id_cliente
       " . ($idEmpresa > 0 ? " AND c.id_empresa = {$idEmpresa} " : "") . "
    WHERE r.id = {$id}
    {$whereEmpresa}
    LIMIT 1
";
$rem = $clsConsulta->consultaGeneral($sqlR);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Remisión no encontrada o no autorizada']);
    exit;
}
$r = $rem[1];

// Partidas (productos visibles para la empresa)
$whereEmpresaProd = "";
if ($idEmpresa > 0) $whereEmpresaProd = " AND (p.id_empresa = {$idEmpresa} OR p.id_empresa IS NULL OR p.id_empresa = 0) ";

$sqlP = "
    SELECT
        mr.id_producto,
        mr.cantidad,
        mr.precio_unitario,
        p.clave,
        p.nombre,
        p.excento
    FROM mov_remisiones mr
    INNER JOIN cat_productos p ON p.id_producto = mr.id_producto
    WHERE mr.id_remision = {$id}
    {$whereEmpresaProd}
    ORDER BY mr.id ASC
";
$prods = $clsConsulta->consultaGeneral($sqlP);

$productos = [];
if (is_array($prods) && $clsConsulta->numrows > 0) {
    foreach ($prods as $k => $p) {
        if ($k === 0) continue;
        $productos[] = [
            'id_producto'     => (int)$p['id_producto'],
            'clave'           => $p['clave'] ?? '',
            'nombre'          => $p['nombre'] ?? '',
            'cantidad'        => (float)$p['cantidad'],
            'precio_unitario' => (float)$p['precio_unitario'],
            'excento'         => (int)($p['excento'] ?? 0),
        ];
    }
}

echo json_encode([
    'success'  => true,
    'remision' => [
        'id'              => (int)$r['id'],
        'fecha'           => $r['fecha'],
        'id_cliente'      => (int)$r['id_cliente'],
        'cliente_nombre'  => $r['cliente_nombre'] ?? '',
        'direccion_envio' => $r['direccion_envio'] ?? '',
        'estatus'         => $r['estatus'] ?? '',
        'total'           => (float)($r['total'] ?? 0),
    ],
    'productos' => $productos
], JSON_UNESCAPED_UNICODE);
