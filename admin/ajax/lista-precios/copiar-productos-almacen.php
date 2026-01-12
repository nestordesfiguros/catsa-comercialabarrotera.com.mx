<?php
// admin/ajax/lista-precios/copiar-productos-almacen.php
session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

// Empresa desde sesión
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

if ($idEmpresa <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Empresa inválida en sesión.']);
    exit;
}

$origen  = isset($_POST['origen']) ? (int)$_POST['origen'] : 0;
$destino = isset($_POST['destino']) ? (int)$_POST['destino'] : 0;
$productos = $_POST['productos'] ?? [];

if ($origen <= 0 || $destino <= 0 || $origen === $destino) {
    echo json_encode(['ok' => false, 'msg' => 'Origen/destino inválidos.']);
    exit;
}

if (!is_array($productos) || count($productos) === 0) {
    echo json_encode(['ok' => false, 'msg' => 'No hay productos seleccionados.']);
    exit;
}

// Validar que ambos almacenes pertenezcan a la empresa
$sqlAlm = "SELECT id
           FROM cat_almacenes
           WHERE estatus = 1
             AND id_empresa = {$idEmpresa}
             AND id IN ({$origen}, {$destino})";
$rsAlm = $clsConsulta->consultaGeneral($sqlAlm);

if ($clsConsulta->numrows < 2) {
    echo json_encode(['ok' => false, 'msg' => 'Almacén origen/destino no pertenece a la empresa.']);
    exit;
}

$copiados = 0;
$omitidos = 0;
$errores  = [];

foreach ($productos as $pid) {
    $idProducto = (int)$pid;
    if ($idProducto <= 0) continue;

    // Traer el producto desde el ORIGEN (valida que pertenece a ese almacén)
    $sqlP = "SELECT *
             FROM cat_productos
             WHERE id_producto = {$idProducto}
               AND estatus = 1
               AND id_almacen = {$origen}
             LIMIT 1";
    $rsP = $clsConsulta->consultaGeneral($sqlP);

    if ($clsConsulta->numrows <= 0) {
        $omitidos++;
        $errores[] = "Producto {$idProducto} no existe en el almacén origen.";
        continue;
    }

    $p = $rsP[1];
    $clave = isset($p['clave']) ? addslashes($p['clave']) : '';

    if ($clave === '') {
        $omitidos++;
        $errores[] = "Producto {$idProducto} sin clave, no se puede copiar.";
        continue;
    }

    // Validar si ya existe en DESTINO por CLAVE + id_almacen
    $sqlExiste = "SELECT id_producto
                  FROM cat_productos
                  WHERE estatus = 1
                    AND id_almacen = {$destino}
                    AND clave = '{$clave}'
                  LIMIT 1";
    $rsE = $clsConsulta->consultaGeneral($sqlExiste);

    if ($clsConsulta->numrows > 0) {
        $omitidos++;
        continue; // ya existe, omitimos sin error
    }

    // ⚠️ Nota: tu tabla tiene id_empaque con índice UNIQUE.
    // Si copias el mismo id_empaque a otro almacén, puede fallar por duplicado.
    // Para evitarlo, copiamos id_empaque como NULL.
    $id_empaque_copy = "NULL";

    // Preparar valores (copia casi todo, cambia id_almacen y limpia cantidad_disponible)
    $nombre          = addslashes($p['nombre'] ?? '');
    $descripcion     = addslashes($p['descripcion'] ?? '');
    $presentacion    = addslashes($p['presentacion'] ?? '');
    $id_categoria    = isset($p['id_categoria']) ? (int)$p['id_categoria'] : "NULL";
    $id_marca        = isset($p['id_marca']) ? (int)$p['id_marca'] : "NULL";
    $precio_compra   = isset($p['precio_compra']) ? (float)$p['precio_compra'] : 0;
    $precio_venta    = isset($p['precio_venta']) ? (float)$p['precio_venta'] : 0;
    $stock_minimo    = isset($p['stock_minimo']) ? (int)$p['stock_minimo'] : 0;
    $stock_maximo    = isset($p['stock_maximo']) ? (int)$p['stock_maximo'] : "NULL";
    $unidad_medida   = addslashes($p['unidad_medida'] ?? '');
    $id_proveedor    = isset($p['id_proveedor']) ? (int)$p['id_proveedor'] : "NULL";
    $estado          = addslashes($p['estado'] ?? 'activo');
    $codigo_barras   = isset($p['codigo_barras']) ? (int)$p['codigo_barras'] : "NULL";
    $fecha_venc      = !empty($p['fecha_vencimiento']) ? "'" . addslashes($p['fecha_vencimiento']) . "'" : "NULL";
    $imagen          = addslashes($p['imagen'] ?? '');
    $cantidad_empaque = isset($p['cantidad_empaque']) ? (int)$p['cantidad_empaque'] : "NULL";
    $tipo_embalaje   = isset($p['tipo_embalaje']) ? (int)$p['tipo_embalaje'] : "NULL";
    $estatus         = 1;
    $excento         = isset($p['excento']) ? (int)$p['excento'] : 0;

    // Precios
    $precio_publico      = isset($p['precio_publico']) ? (float)$p['precio_publico'] : 0;
    $precio_minimo_venta = isset($p['precio_minimo_venta']) ? (float)$p['precio_minimo_venta'] : 0;
    $precio_sugerido     = isset($p['precio_sugerido']) ? (float)$p['precio_sugerido'] : 0;
    $precio_maximo_venta = isset($p['precio_maximo_venta']) ? (float)$p['precio_maximo_venta'] : 0;

    $precio01 = isset($p['precio01']) ? (float)$p['precio01'] : 0;
    $precio02 = isset($p['precio02']) ? (float)$p['precio02'] : 0;
    $precio03 = isset($p['precio03']) ? (float)$p['precio03'] : 0;
    $precio04 = isset($p['precio04']) ? (float)$p['precio04'] : 0;
    $precio05 = isset($p['precio05']) ? (float)$p['precio05'] : 0;

    $precio01_nombre = addslashes($p['precio01_nombre'] ?? 'precio01');
    $precio02_nombre = addslashes($p['precio02_nombre'] ?? 'precio02');
    $precio03_nombre = addslashes($p['precio03_nombre'] ?? 'precio03');
    $precio04_nombre = addslashes($p['precio04_nombre'] ?? 'precio04');
    $precio05_nombre = addslashes($p['precio05_nombre'] ?? 'precio05');

    // Insert copia (id_empresa se deja NULL, porque tu lógica real es por almacén)
    $sqlIns = "INSERT INTO cat_productos (
        id_empresa,
        clave, nombre, descripcion, presentacion,
        id_empaque, id_categoria, id_almacen, id_marca,
        precio_compra, precio_venta,
        cantidad_disponible, stock_minimo, stock_maximo,
        unidad_medida, id_proveedor, estado,
        codigo_barras, fecha_vencimiento,
        imagen, cantidad_empaque, tipo_embalaje,
        estatus, excento,
        precio_publico, precio_minimo_venta, precio_sugerido, precio_maximo_venta,
        precio01, precio02, precio03, precio04, precio05,
        precio01_nombre, precio02_nombre, precio03_nombre, precio04_nombre, precio05_nombre
    ) VALUES (
        NULL,
        '{$clave}', '{$nombre}', '{$descripcion}', '{$presentacion}',
        {$id_empaque_copy}, {$id_categoria}, {$destino}, {$id_marca},
        {$precio_compra}, {$precio_venta},
        0, {$stock_minimo}, {$stock_maximo},
        '{$unidad_medida}', {$id_proveedor}, '{$estado}',
        {$codigo_barras}, {$fecha_venc},
        '{$imagen}', {$cantidad_empaque}, {$tipo_embalaje},
        {$estatus}, {$excento},
        {$precio_publico}, {$precio_minimo_venta}, {$precio_sugerido}, {$precio_maximo_venta},
        {$precio01}, {$precio02}, {$precio03}, {$precio04}, {$precio05},
        '{$precio01_nombre}', '{$precio02_nombre}', '{$precio03_nombre}', '{$precio04_nombre}', '{$precio05_nombre}'
    )";

    $ok = $clsConsulta->guardarGeneral($sqlIns);

    if ($ok) {
        $copiados++;
    } else {
        $omitidos++;
        $errores[] = "No se pudo copiar la clave {$clave} (posible duplicado o restricción).";
    }
}

echo json_encode([
    'ok' => true,
    'copiados' => $copiados,
    'omitidos' => $omitidos,
    'errores' => $errores
]);
