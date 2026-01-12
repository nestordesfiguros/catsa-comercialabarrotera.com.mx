<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// ==========================
// 1. OBTENER DATOS PRINCIPALES DE LA CARTA PORTE
// ==========================
$con = "
    SELECT 
        id,
        id_vehiculo AS vehiculo,
        id_operador AS operador,
        DATE(fecha_salida) AS fecha_salida,
        TIME_FORMAT(fecha_salida,'%H:%i') AS hora_salida,
        origen,
        destino
    FROM cartas_porte
    WHERE id = $id
    LIMIT 1
";

$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Carta Porte no encontrada']);
    exit;
}

$data = $rs[1]; // Primer registro

// ==========================
// 2. OBTENER REMISIONES ASOCIADAS
// ==========================
$conRem = "
    SELECT 
        r.id,
        CONCAT('R-', LPAD(r.id,6,'0')) AS folio,
        c.razon_social AS cliente,
        DATE(r.fecha) AS fecha,
        IFNULL(SUM(m.cantidad*m.precio),0) AS total
    FROM cab_remisiones r
    INNER JOIN cat_clientes c ON r.id_cliente = c.id
    INNER JOIN cartas_porte_remisiones cr ON cr.id_remision = r.id
    LEFT JOIN mov_remisiones m ON m.id_remision = r.id
    WHERE cr.id_carta_porte = $id
    GROUP BY r.id
    ORDER BY r.id DESC
";

$rsRem = $clsConsulta->consultaGeneral($conRem);

$htmlRemisiones = '';
$totalGeneral = 0;
$_SESSION['remisiones_cartaporte'] = []; // Reiniciar sesión

if ($clsConsulta->numrows > 0) {
    foreach ($rsRem as $v => $val) {
        $_SESSION['remisiones_cartaporte'][] = intval($val['id']); // Guardar en sesión

        $htmlRemisiones .= '<tr>
            <td>' . $val['folio'] . '</td>
            <td>' . $val['cliente'] . '</td>
            <td>' . $val['fecha'] . '</td>
            <td class="text-end">$' . number_format($val['total'], 2) . '</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btnQuitarRemision" data-id="' . $val['id'] . '">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>';

        $totalGeneral += floatval($val['total']);
    }
} else {
    $htmlRemisiones = '<tr><td colspan="5" class="text-center">No hay remisiones agregadas</td></tr>';
}

// ==========================
// 3. CARGAR OPCIONES PARA LOS SELECTS
// ==========================

// Vehículos
$conVeh = "SELECT id, nombre_vehiculo FROM cat_vehiculos WHERE activo = 1 ORDER BY nombre_vehiculo ASC";
$rsVeh = $clsConsulta->consultaGeneral($conVeh);
$vehiculos = '<option value="">Selecciona...</option>';
if ($clsConsulta->numrows > 0) {
    foreach ($rsVeh as $v => $val) {
        $selected = ($data['vehiculo'] == $val['id']) ? ' selected' : '';
        $vehiculos .= '<option value="' . $val['id'] . '"' . $selected . '>' . $val['nombre_vehiculo'] . '</option>';
    }
}

// Operadores
$conOp = "SELECT id, nombre AS nombre FROM cat_operadores ORDER BY nombre";
$rsOp = $clsConsulta->consultaGeneral($conOp);
$operadores = '<option value="">Selecciona...</option>';
if ($clsConsulta->numrows > 0) {
    foreach ($rsOp as $v => $val) {
        $selected = ($data['operador'] == $val['id']) ? ' selected' : '';
        $operadores .= '<option value="' . $val['id'] . '"' . $selected . '>' . $val['nombre'] . '</option>';
    }
}

// Almacenes
$conAlm = "SELECT id, almacen AS nombre FROM cat_almacenes ORDER BY almacen";
$rsAlm = $clsConsulta->consultaGeneral($conAlm);
$origenes = '<option value="">Selecciona...</option>';
if ($clsConsulta->numrows > 0) {
    foreach ($rsAlm as $v => $val) {
        $selected = ($data['origen'] == $val['id']) ? ' selected' : '';
        $origenes .= '<option value="' . $val['id'] . '"' . $selected . '>' . $val['nombre'] . '</option>';
    }
}

// ==========================
// 4. RESPUESTA JSON
// ==========================
echo json_encode([
    'success' => true,
    'data' => [
        'vehiculo' => $data['vehiculo'],
        'operador' => $data['operador'],
        'fecha_salida' => $data['fecha_salida'],
        'hora_salida' => $data['hora_salida'],
        'origen' => $data['origen'],
        'destino' => $data['destino']
    ],
    'htmlRemisiones' => $htmlRemisiones,
    'total_general' => number_format($totalGeneral, 2),
    'vehiculos' => $vehiculos,
    'operadores' => $operadores,
    'origenes' => $origenes
]);
