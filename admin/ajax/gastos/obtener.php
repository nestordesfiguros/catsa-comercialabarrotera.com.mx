<?php
// ajax/gastos/obtener.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$idGasto   = intval($_POST['idGasto'] ?? 0);
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);

if ($idGasto <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

/* Intento 1: por empresa + id (seguridad/consistencia) */
$cond = "g.id_gasto = {$idGasto}";
if ($idEmpresa > 0) {
    $cond .= " AND g.id_empresa = {$idEmpresa}";
}

$sql = "SELECT
            g.id_gasto,
            g.id_empresa,
            g.id_usuario_captura,
            g.fecha_gasto,
            g.hora_gasto,
            g.id_categoria,
            g.descripcion,
            g.monto,
            g.id_metodo,
            g.referencia,
            g.id_proveedor,
            g.notas,
            g.estatus
        FROM mov_gastos g
        WHERE {$cond}
        LIMIT 1";

$rs = $cls->aplicaQuery($sql);

if (!$rs || $rs->num_rows === 0) {
    /* Fallback opcional: si no halló por empresa (p. ej. sesión vacía), busca solo por id */
    if ($idEmpresa > 0) {
        $sql2 = "SELECT
                    id_gasto, id_empresa, id_usuario_captura, fecha_gasto, hora_gasto,
                    id_categoria, descripcion, monto, id_metodo, referencia,
                    id_proveedor, notas, estatus
                 FROM mov_gastos
                 WHERE id_gasto = {$idGasto}
                 LIMIT 1";
        $rs2 = $cls->aplicaQuery($sql2);
        if ($rs2 && $rs2->num_rows > 0) {
            $data = mysqli_fetch_assoc($rs2);
            echo json_encode(["ok" => true, "data" => $data]);
            exit;
        }
    }
    echo json_encode(["ok" => false, "msg" => "No encontrado"]);
    exit;
}

$data = mysqli_fetch_assoc($rs);
echo json_encode(["ok" => true, "data" => $data]);
