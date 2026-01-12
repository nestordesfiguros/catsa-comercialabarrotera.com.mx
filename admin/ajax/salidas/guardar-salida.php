<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$response = ['success' => false, 'message' => ''];

try {
    // Validar datos recibidos
    $fecha = $_POST['fecha'] ?? '';
    $id_vehiculo = intval($_POST['id_vehiculo'] ?? 0);
    $id_operador = intval($_POST['id_operador'] ?? 0);
    $remisiones = $_POST['remisiones'] ?? [];

    if (!$fecha || $id_vehiculo <= 0 || $id_operador <= 0 || !is_array($remisiones) || count($remisiones) === 0) {
        throw new Exception('Todos los campos son obligatorios.');
    }

    // Validar que todas las remisiones existen y no estén asignadas ya
    $remisiones_validadas = [];
    $origen_fijo = null;

    foreach ($remisiones as $id_remision) {
        $id_remision = intval($id_remision);
        if ($id_remision <= 0) continue;

        $sql = "SELECT id, id_almacen, estatus FROM cab_remisiones WHERE id = $id_remision";
        $res = $clsConsulta->consultaGeneral($sql);

        if ($clsConsulta->numrows === 0) {
            throw new Exception("La remisión ID $id_remision no existe.");
        }

        $remision = $res[1];

        if ($remision['estatus'] !== 'pendiente') {
            throw new Exception("La remisión ID $id_remision no está pendiente.");
        }

        // Validar que no esté asignada ya
        $existe = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS total FROM salida_remisiones WHERE id_remision = $id_remision");
        if (intval($existe[1]['total']) > 0) {
            throw new Exception("La remisión ID $id_remision ya fue asignada a una salida.");
        }

        // Validar que todas las remisiones provengan del mismo almacén
        $id_almacen = intval($remision['id_almacen']);
        if (!$id_almacen) {
            throw new Exception("La remisión ID $id_remision no tiene almacén asignado.");
        }

        if (is_null($origen_fijo)) {
            $origen_fijo = $id_almacen;
        } elseif ($origen_fijo !== $id_almacen) {
            throw new Exception("Todas las remisiones deben provenir del mismo almacén.");
        }

        $remisiones_validadas[] = $id_remision;
    }

    // Obtener total de todas las remisiones
    $ids = implode(',', $remisiones_validadas);
    $suma = $clsConsulta->consultaGeneral("SELECT SUM(total) AS total FROM cab_remisiones WHERE id IN ($ids)");
    $total_salida = floatval($suma[1]['total'] ?? 0);

    // Insertar en cabecera
    $id_usuario = $_SESSION['id_user'] ?? 0;
    $sqlCab = "
        INSERT INTO cab_salida_almacen (id_almacen, id_vehiculo, id_operador, fecha, estatus, total)
        VALUES ($origen_fijo, $id_vehiculo, $id_operador, '$fecha', 'pendiente', $total_salida)
    ";
    $clsConsulta->guardarGeneral($sqlCab);
    $id_salida = $clsConsulta->ultimoid;

    // Insertar detalle
    foreach ($remisiones_validadas as $id_remision) {
        $sqlMov = "INSERT INTO salida_remisiones (id_salida, id_remision) VALUES ($id_salida, $id_remision)";
        $clsConsulta->aplicaQuery($sqlMov);
    }

    $response['success'] = true;
    $response['message'] = 'Salida registrada correctamente.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
