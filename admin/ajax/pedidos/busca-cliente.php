<?php
// ajax/pedidos/busca-cliente.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idCliente = isset($_GET['idCliente']) ? (int)$_GET['idCliente'] : 0;
$idAlmacen = isset($_GET['id_almacen']) ? (int)$_GET['id_almacen'] : 0; // opcional (solo para compat, no limita catálogo)

if ($idEmpresa <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'Sesión inválida (empresa)']);
    exit;
}
if ($idCliente <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'ID de cliente inválido']);
    exit;
}

$sql = "
    SELECT 
        c.*,
        m.nombre AS nmunicipio,
        e.nombre AS nestado
    FROM cat_clientes c
    LEFT JOIN municipios m ON c.id_municipio = m.id
    LEFT JOIN estados e    ON c.id_estado    = e.id
    WHERE 
        c.id = {$idCliente}
        AND c.estatus = 1
        AND c.id_empresa = {$idEmpresa}
    LIMIT 1
";

$rs = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows < 1 || !isset($rs[1])) {
    echo json_encode(['error' => true, 'mensaje' => 'Cliente no encontrado o no pertenece a la empresa actual']);
    exit;
}

$cliente = $rs[1];

// lista 1..5 (default 1)
$lista = isset($cliente['lista_precios']) ? (int)$cliente['lista_precios'] : 0;
if ($lista < 1 || $lista > 5) $lista = 1;

$precioCol = 'precio0' . $lista;
$nombreCol = 'precio0' . $lista . '_nombre';

// nombre default
$listaNombre = 'Lista ' . $lista;

// Tomamos el nombre de lista desde algún producto del catálogo de la empresa (tolerante a legado),
// pero cuidando que NO use textos genéricos tipo "precio01".
$sqlLista = "
    SELECT NULLIF(TRIM(p.{$nombreCol}), '') AS nombre_lista
    FROM cat_productos p
    WHERE
        p.estatus = 1
        AND (
            p.id_empresa = {$idEmpresa}
            OR EXISTS (SELECT 1 FROM inventarios i WHERE i.id_empresa = {$idEmpresa} AND i.id_producto = p.id_producto)
            OR EXISTS (SELECT 1 FROM cat_almacenes a WHERE a.id = p.id_almacen AND a.id_empresa = {$idEmpresa})
        )
    ORDER BY p.id_producto ASC
    LIMIT 1
";

$rsLista = $clsConsulta->consultaGeneral($sqlLista);
if ($clsConsulta->numrows > 0 && isset($rsLista[1]['nombre_lista']) && $rsLista[1]['nombre_lista'] !== null) {
    $tmpName = trim((string)$rsLista[1]['nombre_lista']);

    // si el nombre es el default "precio01" etc, usamos "Lista X"
    if ($tmpName !== '' && stripos($tmpName, 'precio0' . $lista) === false) {
        $listaNombre = $tmpName;
    }
}

$cliente['error'] = false;
$cliente['lista_precios'] = $lista;                 // 1..5
$cliente['lista_precios_nombre'] = $listaNombre;    // "Mayoreo", etc. o "Lista X"
$cliente['precio_columna'] = $precioCol;            // "precio01".."precio05"

echo json_encode($cliente);
