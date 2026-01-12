<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// ==========================
// 1. Validaciones iniciales
// ==========================
$idCarta = intval($_POST['id'] ?? 0);
$vehiculo = intval($_POST['vehiculo'] ?? 0);
$operador = intval($_POST['operador'] ?? 0);
$fecha_salida = $_POST['fecha_salida'] ?? '';
$hora_salida = $_POST['hora_salida'] ?? '';
$origen = intval($_POST['origen'] ?? 0);
$destino = trim($_POST['destino'] ?? '');

if ($idCarta <= 0 || $vehiculo <= 0 || $operador <= 0 || $fecha_salida == '' || $hora_salida == '' || $origen <= 0 || $destino == '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar la Carta Porte']);
    exit;
}

// Validar que haya al menos una remisión en sesión
if (!isset($_SESSION['remisiones_cartaporte']) || count($_SESSION['remisiones_cartaporte']) === 0) {
    echo json_encode(['success' => false, 'message' => 'Debe agregar al menos una remisión']);
    exit;
}

// ==========================
// 2. Actualizar la carta porte
// ==========================
$fechaHora = $fecha_salida . ' ' . $hora_salida . ':00';
$conUpdate = "
    UPDATE cartas_porte
    SET 
        id_vehiculo = $vehiculo,
        id_operador = $operador,
        fecha_salida = '$fechaHora',
        origen = $origen,
        destino = '$destino'
    WHERE id = $idCarta
    LIMIT 1
";

if (!$clsConsulta->aplicaQuery($conUpdate)) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la Carta Porte']);
    exit;
}

// ==========================
// 3. Sincronizar remisiones
// ==========================
// Eliminar remisiones previas
$clsConsulta->aplicaQuery("DELETE FROM cartas_porte_remisiones WHERE id_carta_porte = $idCarta");

// Insertar las actuales de sesión
$remisiones = $_SESSION['remisiones_cartaporte'];
foreach ($remisiones as $idRem) {
    $idRem = intval($idRem);
    $clsConsulta->aplicaQuery("
        INSERT INTO cartas_porte_remisiones (id_carta_porte, id_remision) 
        VALUES ($idCarta, $idRem)
    ");
}

// ==========================
// 4. Limpiar sesión y responder
// ==========================
unset($_SESSION['remisiones_cartaporte']);

echo json_encode(['success' => true, 'message' => 'Carta Porte actualizada correctamente.']);
