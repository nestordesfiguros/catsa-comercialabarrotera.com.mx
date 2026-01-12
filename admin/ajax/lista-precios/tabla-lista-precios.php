<?php
// admin/ajax/lista-precios/tabla-lista-precios.php
session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

// Empresa (desde sesión)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

$searchValue = '';
if (isset($_POST['search']['value'])) $searchValue = trim($_POST['search']['value']);

$lista = isset($_POST['lista']) ? (int)$_POST['lista'] : 1;
if ($lista < 1 || $lista > 5) $lista = 1;

$idAlmacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

if ($idEmpresa <= 0 || $idAlmacen <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// Campo precio por lista
$precioField = 'precio01';
if ($lista === 2) $precioField = 'precio02';
if ($lista === 3) $precioField = 'precio03';
if ($lista === 4) $precioField = 'precio04';
if ($lista === 5) $precioField = 'precio05';

// Ordenamiento (columnas: 0 chk, 1 foto, 2 clave, 3 nombre, 4 precio, 5 editar)
$orderCol = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 2;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
$orderDir = (strtolower($orderDir) === 'desc') ? 'DESC' : 'ASC';

$columnsMap = [
    2 => 'p.clave',
    3 => 'p.nombre',
    4 => "p.$precioField"
];
$orderBy = isset($columnsMap[$orderCol]) ? $columnsMap[$orderCol] : 'p.clave';

// WHERE base: empresa real por almacén, y filtro por almacén seleccionado
$where = "WHERE p.estatus = 1
          AND p.id_almacen = {$idAlmacen}
          AND a.estatus = 1
          AND a.id_empresa = {$idEmpresa}";

// Búsqueda
$whereSearch = '';
if ($searchValue !== '') {
    // escape simple para LIKE
    $sv = addslashes($searchValue);
    $whereSearch = " AND (p.clave LIKE '%{$sv}%' OR p.nombre LIKE '%{$sv}%')";
}

// Total (sin search)
$sqlTotal = "SELECT COUNT(*) AS total
             FROM cat_productos p
             INNER JOIN cat_almacenes a ON a.id = p.id_almacen
             {$where}";
$rsTotal = $clsConsulta->consultaGeneral($sqlTotal);
$recordsTotal = ($clsConsulta->numrows > 0) ? (int)$rsTotal[1]['total'] : 0;

// Filtrados (con search)
$sqlFiltered = "SELECT COUNT(*) AS total
                FROM cat_productos p
                INNER JOIN cat_almacenes a ON a.id = p.id_almacen
                {$where} {$whereSearch}";
$rsFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
$recordsFiltered = ($clsConsulta->numrows > 0) ? (int)$rsFiltered[1]['total'] : 0;

// Data
$sqlData = "SELECT
                p.id_producto,
                p.clave,
                p.nombre,
                p.imagen,
                COALESCE(p.$precioField, 0) AS precio
            FROM cat_productos p
            INNER JOIN cat_almacenes a ON a.id = p.id_almacen
            {$where} {$whereSearch}
            ORDER BY {$orderBy} {$orderDir}
            LIMIT {$start}, {$length}";
$rs = $clsConsulta->consultaGeneral($sqlData);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        $idp = (int)$val['id_producto'];
        $clave  = $val['clave'] !== null ? htmlspecialchars($val['clave'], ENT_QUOTES) : '';
        $nombre = $val['nombre'] !== null ? htmlspecialchars($val['nombre'], ENT_QUOTES) : '';
        $imagen = $val['imagen'] !== null ? htmlspecialchars($val['imagen'], ENT_QUOTES) : '';

        $precioNum = is_numeric($val['precio']) ? (float)$val['precio'] : 0.00;
        $precioFmt = number_format($precioNum, 2);

        // Checkbox (centrado)
        $chk = '<div class="text-center">
                    <input type="checkbox" class="form-check-input chkProd" value="' . $idp . '">
                </div>';

        // Foto (centrada)
        $fotoHtml = '<div class="text-center"><i class="fas fa-image fa-lg text-muted" title="Sin imagen"></i></div>';
        if ($imagen !== '') {
            $fotoHtml = '<div class="text-center">
                <img
                    src="../img/productos/mini-' . $imagen . '"
                    class="img-fluid rounded btn-ver-foto"
                    style="height:22px; cursor:pointer"
                    title="Ver foto"
                    data-nombre="' . $nombre . '"
                    data-imagen="' . $imagen . '"
                >
            </div>';
        }

        // Precio (derecha + $)
        $precioHtml = '<div class="text-end">$ ' . $precioFmt . '</div>';

        // Editar (centrado) -> data-* para modal
        $btnEditar = '<div class="text-center">
            <i class="fas fa-edit fa-lg text-info btn-editar-precio"
               style="cursor:pointer"
               title="Editar precio"
               data-id="' . $idp . '"
               data-clave="' . $clave . '"
               data-nombre="' . $nombre . '"
               data-precio="' . $precioFmt . '"></i>
        </div>';

        $data[] = [$chk, $fotoHtml, $clave, $nombre, $precioHtml, $btnEditar];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
