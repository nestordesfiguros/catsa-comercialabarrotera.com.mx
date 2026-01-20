<?php
// admin/ajax/pedidos/tabla-productos-pedido.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

date_default_timezone_set('America/Mexico_City');

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idAlmacen = isset($_GET['id_almacen']) ? (int)$_GET['id_almacen'] : 0;
$idCliente = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;

$searchValue = '';
if (isset($_GET['search']) && is_array($_GET['search'])) {
    $searchValue = trim((string)($_GET['search']['value'] ?? ''));
}

if ($length < 1) $length = 10;
if ($length > 100) $length = 100;
if ($start < 0) $start = 0;

if ($idEmpresa <= 0 || $idAlmacen <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// validar almacén pertenece a empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id_empresa = {$idEmpresa} AND id = {$idAlmacen} AND estatus = 1 LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// obtener lista del cliente (default 1)
$lista = 1;
if ($idCliente > 0) {
    $rc = $clsConsulta->consultaGeneral("SELECT lista_precios FROM cat_clientes WHERE id = {$idCliente} AND id_empresa = {$idEmpresa} AND estatus = 1 LIMIT 1");
    if ($clsConsulta->numrows > 0 && isset($rc[1]['lista_precios'])) {
        $tmp = (int)$rc[1]['lista_precios'];
        if ($tmp >= 1 && $tmp <= 5) $lista = $tmp;
    }
}
$precioCol = 'precio0' . $lista;

// order mapping (DataTable columns: 0 img,1 clave,2 nombre,3 precio,4 existencia,5 btn)
$orderColIdx = 2;
$orderDir = 'ASC';
if (isset($_GET['order'][0]['column'])) {
    $orderColIdx = (int)$_GET['order'][0]['column'];
}
if (isset($_GET['order'][0]['dir'])) {
    $tmpDir = strtoupper((string)$_GET['order'][0]['dir']);
    $orderDir = ($tmpDir === 'DESC') ? 'DESC' : 'ASC';
}

$orderBy = "p.nombre {$orderDir}";
switch ($orderColIdx) {
    case 1:
        $orderBy = "p.clave {$orderDir}";
        break;
    case 2:
        $orderBy = "p.nombre {$orderDir}";
        break;
    case 3:
        $orderBy = "precio_lista {$orderDir}";
        break;
    case 4:
        $orderBy = "existencia {$orderDir}";
        break;
    default:
        $orderBy = "p.nombre {$orderDir}";
        break;
}

// catálogo por almacén
$whereBase = "
    p.estatus = 1
    AND p.id_almacen = {$idAlmacen}
";

$whereSearch = "";
if ($searchValue !== '') {
    $sv = addslashes($searchValue);
    $whereSearch = " AND (p.nombre LIKE '%{$sv}%' OR p.clave LIKE '%{$sv}%') ";
}

// total catálogo (sin search)
$sqlTotal = "SELECT COUNT(*) AS c FROM cat_productos p WHERE {$whereBase}";
$rt = $clsConsulta->consultaGeneral($sqlTotal);
$recordsTotal = (int)($rt[1]['c'] ?? 0);

// total filtrado (con search)
$recordsFiltered = $recordsTotal;
if ($whereSearch !== '') {
    $sqlFilt = "SELECT COUNT(*) AS c FROM cat_productos p WHERE {$whereBase} {$whereSearch}";
    $rf = $clsConsulta->consultaGeneral($sqlFilt);
    $recordsFiltered = (int)($rf[1]['c'] ?? 0);
}

// existencia disponible (inventarios - reservas)
$sql = "
    SELECT
        p.id_producto,
        p.clave,
        p.nombre,
        p.imagen,
        IFNULL(inv.cantidad, 0) AS inv_total,
        IFNULL(res.reservado, 0) AS reservado,
        (IFNULL(inv.cantidad, 0) - IFNULL(res.reservado, 0)) AS existencia,
        IFNULL(p.{$precioCol}, 0) AS precio_lista,
        IFNULL(p.precio01, 0) AS precio_lista1,
        IFNULL(p.precio02, 0) AS precio_lista2,
        IFNULL(p.precio03, 0) AS precio_lista3,
        IFNULL(p.precio04, 0) AS precio_lista4,
        IFNULL(p.precio05, 0) AS precio_lista5
    FROM cat_productos p
    LEFT JOIN (
        SELECT id_producto, SUM(cantidad) AS cantidad
        FROM inventarios
        WHERE id_empresa = {$idEmpresa}
          AND id_almacen = {$idAlmacen}
        GROUP BY id_producto
    ) inv ON inv.id_producto = p.id_producto
    LEFT JOIN (
        SELECT id_producto, SUM(cantidad) AS reservado
        FROM inventarios_reservas
        WHERE id_empresa = {$idEmpresa}
          AND id_almacen = {$idAlmacen}
          AND estatus = 'reservado'
          AND (vence_at IS NULL OR vence_at > NOW())
        GROUP BY id_producto
    ) res ON res.id_producto = p.id_producto
    WHERE {$whereBase} {$whereSearch}
    ORDER BY {$orderBy}
    LIMIT {$start}, {$length}
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];

if (is_array($rs) && $clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        if (!is_array($val) || !isset($val['id_producto'])) continue;

        $idProducto  = (int)$val['id_producto'];
        $clave       = (string)($val['clave'] ?? '');
        $nombre      = (string)($val['nombre'] ?? '');
        $existencia  = (float)($val['existencia'] ?? 0);
        $precioLista = (float)($val['precio_lista'] ?? 0);
        $imagen      = (string)($val['imagen'] ?? '');

        $p1 = (float)($val['precio_lista1'] ?? 0);
        $p2 = (float)($val['precio_lista2'] ?? 0);
        $p3 = (float)($val['precio_lista3'] ?? 0);
        $p4 = (float)($val['precio_lista4'] ?? 0);
        $p5 = (float)($val['precio_lista5'] ?? 0);

        // min/max ignorando ceros
        $preciosValidos = [];
        if ($p1 > 0) $preciosValidos[] = $p1;
        if ($p2 > 0) $preciosValidos[] = $p2;
        if ($p3 > 0) $preciosValidos[] = $p3;
        if ($p4 > 0) $preciosValidos[] = $p4;
        if ($p5 > 0) $preciosValidos[] = $p5;

        $precioMinimo = (count($preciosValidos) > 0) ? min($preciosValidos) : 0;
        $precioMaximo = (count($preciosValidos) > 0) ? max($preciosValidos) : 0;

        $foto = '<div class="text-center"><i class="fas fa-image fa-lg text-muted"></i></div>';
        if ($imagen !== '') {
            $imgEsc = htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8');
            $foto = '<div class="text-center">
                        <img src="../img/productos/mini-' . $imgEsc . '" alt="' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '" style="height:32px;">
                     </div>';
        }

        $colClave  = '<div class="text-nowrap">' . htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') . '</div>';
        $colNombre = '<div>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</div>';
        $colPrecio = '<div class="text-end">' . number_format($precioLista, 2, '.', ',') . '</div>';
        $colExist  = '<div class="text-end">' . number_format($existencia, 0, '.', ',') . '</div>';

        if ($existencia > 0) {
            $btn = '<div class="text-center">
                <button type="button" class="btn btn-sm btn-primary"
                    onclick="agregarProducto('
                . $idProducto . ', '
                . '\'' . addslashes($clave) . '\', '
                . '\'' . addslashes($nombre) . '\', '
                . $precioLista . ', '
                . $precioMinimo . ', '
                . $precioMaximo . ', '
                . $p1 . ', ' . $p2 . ', ' . $p3 . ', ' . $p4 . ', ' . $p5 .
                ');">
                    Agregar
                </button>
            </div>';
        } else {
            $btn = '<div class="text-center">
                        <button type="button" class="btn btn-sm btn-secondary" disabled>Sin existencia</button>
                    </div>';
        }

        $data[] = [$foto, $colClave, $colNombre, $colPrecio, $colExist, $btn];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
