<?php
// ajax/gastos/actualizar.php
session_start();
require_once '../../lib/clsConsultas.php';

$cls        = new Consultas();
$idEmpresa  = intval($_SESSION['id_empresa'] ?? 0);

/* ===== Inputs ===== */
$idGasto          = intval($_POST['idGasto'] ?? 0);
$fechaGasto       = $_POST['fechaGasto']       ?? '';
$horaGasto        = $_POST['horaGasto']        ?? '';
$idCategoria      = intval($_POST['idCategoria'] ?? 0);
$descripcionGasto = trim($_POST['descripcionGasto'] ?? '');
$montoGasto       = $_POST['montoGasto']       ?? '0';
$idMetodo         = intval($_POST['idMetodo']    ?? 0);
$referenciaGasto  = trim($_POST['referenciaGasto'] ?? '');
$notasGasto       = trim($_POST['notasGasto']      ?? '');

if ($idGasto <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID no válido"]);
    exit;
}

/* ===== Si no viene id_empresa en sesión, inferir por el gasto o categoría ===== */
if ($idEmpresa <= 0) {
    $rsG = $cls->aplicaQuery("SELECT id_empresa FROM mov_gastos WHERE id_gasto={$idGasto} LIMIT 1");
    if ($rsG && $rsG->num_rows > 0) {
        $rowG = mysqli_fetch_assoc($rsG);
        $idEmpresa = intval($rowG['id_empresa']);
    } elseif ($idCategoria > 0) {
        $rsEmp = $cls->aplicaQuery("SELECT id_empresa FROM cat_gastos_categorias WHERE id_categoria={$idCategoria} LIMIT 1");
        if ($rsEmp && $rsEmp->num_rows > 0) {
            $rowEmp   = mysqli_fetch_assoc($rsEmp);
            $idEmpresa = intval($rowEmp['id_empresa']);
        }
    }
}
if ($idEmpresa <= 0) {
    echo json_encode(["ok" => false, "msg" => "Empresa inválida"]);
    exit;
}

/* ===== Validar FKs (usar $rs->num_rows) ===== */
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

/* ===== Update ===== */
$sql = "UPDATE mov_gastos SET
          fecha_gasto = " . $cls->sanitizar($fechaGasto) . ",
          hora_gasto  = " . $cls->sanitizar($horaGasto) . ",
          id_categoria = {$idCategoria},
          descripcion  = " . $cls->sanitizar($descripcionGasto) . ",
          monto        = " . floatval($montoGasto) . ",
          id_metodo    = {$idMetodo},
          referencia   = " . ($referenciaGasto === '' ? 'NULL' : $cls->sanitizar($referenciaGasto)) . ",
          notas        = " . ($notasGasto === '' ? 'NULL' : $cls->sanitizar($notasGasto)) . ",
          fecha_modificacion = NOW()
        WHERE id_gasto = {$idGasto} AND id_empresa = {$idEmpresa}
        LIMIT 1";

$ok = $cls->aplicaQuery($sql);
echo json_encode(["ok" => $ok ? true : false, "msg" => $ok ? '' : 'No se pudo actualizar']);
