<?php
session_start();
require_once '../../lib/config.php';
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();


// Validar sesión y datos
$id_vendedor = intval($_POST['vendedor'] ?? 0);
$id_usuario  = intval($_SESSION['id_user'] ?? 0);

$desde = $_POST['desde'] ?? '';
$hasta = $_POST['hasta'] ?? '';


if (!$id_vendedor || !$desde || !$hasta || !$id_usuario) {
    echo json_encode(['success' => false, 'msg' => 'Faltan datos']);
    exit;
}

// Buscar si ya existe el registro
$sql_check = "
    SELECT id FROM comisiones
    WHERE id_vendedor = '$id_vendedor'
    AND fecha_inicio = '$desde'
    AND fecha_fin = '$hasta'
    LIMIT 1
";
$existe = $cls->consultaGeneral($sql_check);

if ($cls->numrows > 0) {
    // Actualizar estatus a pagado
    $id_comision = $existe[0]['id'];
    $sql_update = "
        UPDATE comisiones
        SET estatus = 'pagado',
            fecha_pago = NOW(),
            id_usuario_pago = '$id_usuario'
        WHERE id = $id_comision
    ";
    $cls->aplicaQuery($sql_update);
} else {
    // Insertar nuevo registro como pagado
    $sql_insert = "
        INSERT INTO comisiones (id_vendedor, fecha_inicio, fecha_fin, estatus, fecha_pago, id_usuario_pago)
        VALUES ('$id_vendedor', '$desde', '$hasta', 'pagado', NOW(), '$id_usuario')
    ";
    $cls->aplicaQuery($sql_insert);
}

echo json_encode(['success' => true]);
