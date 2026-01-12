<?php
// ajax/gastos/guardar.php
session_start();
require_once '../../lib/clsConsultas.php';

$cls        = new Consultas();
$idEmpresa  = intval($_SESSION['id_empresa'] ?? 0);
$idUsuario  = intval($_SESSION['id_usuario'] ?? 0);
$ipCaptura  = $_SERVER['REMOTE_ADDR'] ?? null;

/* ===== Inputs ===== */
$fechaGasto       = $_POST['fechaGasto']       ?? '';
$horaGasto        = $_POST['horaGasto']        ?? '';
$idCategoria      = intval($_POST['idCategoria'] ?? 0);
$descripcionGasto = trim($_POST['descripcionGasto'] ?? '');
$montoGasto       = $_POST['montoGasto']       ?? '0';
$idMetodo         = intval($_POST['idMetodo']    ?? 0);
$referenciaGasto  = trim($_POST['referenciaGasto'] ?? '');
$notasGasto       = trim($_POST['notasGasto']      ?? '');

/* ===== Validaciones mínimas ===== */
if ($fechaGasto === '' || $horaGasto === '' || $idCategoria <= 0 || $idMetodo <= 0 || $descripcionGasto === '' || floatval($montoGasto) <= 0) {
    echo json_encode(["ok" => false, "msg" => "Datos incompletos"]);
    exit;
}

/* ===== Si no viene id_empresa en sesión, intentamos inferirlo por la categoría ===== */
if ($idEmpresa <= 0 && $idCategoria > 0) {
    $rsEmp = $cls->aplicaQuery("SELECT id_empresa FROM cat_gastos_categorias WHERE id_categoria={$idCategoria} LIMIT 1");
    if ($rsEmp && $rsEmp->num_rows > 0) {
        $rowEmp   = mysqli_fetch_assoc($rsEmp);
        $idEmpresa = intval($rowEmp['id_empresa']);
    }
}
if ($idEmpresa <= 0) {
    echo json_encode(["ok" => false, "msg" => "Empresa inválida"]);
    exit;
}

/* ===== Validación de FKs (usar $rs->num_rows) ===== */
$rsCat = $cls->aplicaQuery(
    "SELECT 1 FROM cat_gastos_categorias 
   WHERE id_categoria={$idCategoria} AND id_empresa={$idEmpresa} AND activo=1 
   LIMIT 1"
);
if (!$rsCat || $rsCat->num_rows === 0) {
    echo json_encode(["ok" => false, "msg" => "Categoría no válida"]);
    exit;
}

$rsMet = $cls->aplicaQuery("SELECT 1 FROM cat_metodos_pago WHERE id={$idMetodo} LIMIT 1");
if (!$rsMet || $rsMet->num_rows === 0) {
    echo json_encode(["ok" => false, "msg" => "Método de pago no válido"]);
    exit;
}

/* ===== Insert ===== */
$sql = "INSERT INTO mov_gastos
        (id_empresa, id_usuario_captura, fecha_gasto, hora_gasto, id_categoria, descripcion, monto, id_metodo, referencia, id_proveedor, notas, estatus, ip_captura, fecha_creacion)
        VALUES (
          {$idEmpresa}, {$idUsuario},
          " . $cls->sanitizar($fechaGasto) . ", " . $cls->sanitizar($horaGasto) . ",
          {$idCategoria}, " . $cls->sanitizar($descripcionGasto) . ", " . floatval($montoGasto) . ",
          {$idMetodo}, " . ($referenciaGasto === '' ? 'NULL' : $cls->sanitizar($referenciaGasto)) . ",
          NULL, " . ($notasGasto === '' ? 'NULL' : $cls->sanitizar($notasGasto)) . ",
          'Activo', " . ($ipCaptura ? $cls->sanitizar($ipCaptura) : 'NULL') . ", NOW()
        )";

$ok = $cls->guardarGeneral($sql);
echo json_encode(["ok" => $ok ? true : false, "msg" => $ok ? '' : 'No se pudo guardar']);
