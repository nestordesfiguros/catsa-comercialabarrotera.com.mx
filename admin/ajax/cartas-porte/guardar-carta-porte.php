<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
session_start();

header('Content-Type: application/json');

$idUsuario = intval($_SESSION['id_user'] ?? 0);

// =============================
// 1. Obtener datos enviados
// =============================
$idVehiculo     = intval($_POST['vehiculo'] ?? 0);
$idOperador     = intval($_POST['operador'] ?? 0);
$fechaSalida    = $_POST['fecha_hora_salida'] ?? '';
$origen         = trim($_POST['origen'] ?? '');
$destino        = trim($_POST['destino_general'] ?? '');

// =============================
// 2. Generar folio incremental
// =============================
$conFolio = "SELECT folio FROM cartas_porte ORDER BY folio DESC LIMIT 1";
$rsFolio  = $clsConsulta->consultaGeneral($conFolio);

$folio = 'CP-000001';
if ($clsConsulta->numrows > 0 && !empty($rsFolio[0]['folio'])) {
    $ultimoFolio = $rsFolio[0]['folio'];
    $num = intval(substr($ultimoFolio, 3)) + 1;
    $folio = 'CP-' . str_pad($num, 6, '0', STR_PAD_LEFT);
}

// =============================
// 3. Insertar cabecera
// =============================
$sqlCab = "
    INSERT INTO cartas_porte
        (folio, id_vehiculo, id_operador, fecha_salida, origen, destino, id_usuario, fecha_creacion)
    VALUES
        ('$folio', $idVehiculo, $idOperador, '$fechaSalida', '$origen', '$destino', $idUsuario, NOW())
";

if (!$clsConsulta->guardarGeneral($sqlCab)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la Carta Porte']);
    exit;
}

// Obtener el ID insertado
$idCartaPorte = $clsConsulta->ultimoid;

// =============================
// 4. Insertar remisiones desde $_SESSION
// =============================
if (!empty($_SESSION['remisiones_cartaporte']) && is_array($_SESSION['remisiones_cartaporte'])) {
    foreach ($_SESSION['remisiones_cartaporte'] as $idRemision) {
        $idRemision = intval($idRemision);
        $sqlDet = "
            INSERT INTO cartas_porte_remisiones (id_carta_porte, id_remision, fecha_asignacion)
            VALUES ($idCartaPorte, $idRemision, NOW())
        ";
        $clsConsulta->aplicaquery($sqlDet);
    }

    // Limpiar remisiones de sesión
    unset($_SESSION['remisiones_cartaporte']);
}

// =============================
// 5. Respuesta final
// =============================
echo json_encode([
    'success' => true,
    'message' => 'Carta Porte guardada correctamente',
    'id'      => $idCartaPorte,
    'folio'   => $folio
]);
