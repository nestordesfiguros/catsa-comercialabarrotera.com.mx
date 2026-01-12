<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Parámetros de DataTables
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 25;
$draw = $_POST['draw'] ?? 1;

// Filtros
$idProducto = $_POST['id_producto'] ?? 0;
$idCategoria = $_POST['id_categoria'] ?? 0;
$tipoMovimiento = $_POST['tipo_movimiento'] ?? '';
$idAlmacen = $_POST['id_almacen'] ?? 0;
$fechaDesde = $_POST['fecha_desde'] ?? '';
$fechaHasta = $_POST['fecha_hasta'] ?? '';
$referencia = $_POST['referencia'] ?? '';
$ordenarPor = $_POST['ordenar_por'] ?? 'fecha';

try {
    // Enfoque simplificado: usar solo inventario_bitacora que ya tiene el historial completo
    $sql = "
        SELECT 
            ib.fecha AS fecha_hora,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%entrada%' OR ib.tipo_movimiento LIKE '%compra%' THEN 'Entrada'
                WHEN ib.tipo_movimiento LIKE '%salida%' OR ib.tipo_movimiento LIKE '%venta%' THEN 'Salida' 
                WHEN ib.tipo_movimiento LIKE '%ajuste%' THEN 'Ajuste'
                WHEN ib.tipo_movimiento LIKE '%traslado%' THEN 'Traslado'
                ELSE ib.tipo_movimiento
            END AS tipo_movimiento,
            ca.almacen,
            ib.cantidad,
            ib.referencia AS documento,
            ib.tipo_movimiento AS referencia_detalle,
            COALESCE(u.nombre, 'Sistema') AS usuario,
            0 AS saldo_anterior,
            0 AS saldo_actual,
            '' AS observaciones,
            cp.id_producto,
            cc.id_categoria,
            ca.id AS id_almacen
        FROM 
            inventario_bitacora ib
        INNER JOIN 
            cat_productos cp ON ib.id_producto = cp.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON ib.id_almacen = ca.id
        LEFT JOIN 
            usuarios u ON ib.id_usuario = u.id
        WHERE 
            1=1
    ";

    $params = [];
    $types = "";

    // Aplicar filtros
    if ($idProducto > 0) {
        $sql .= " AND cp.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cc.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if (!empty($tipoMovimiento)) {
        switch ($tipoMovimiento) {
            case 'entrada':
                $sql .= " AND (ib.tipo_movimiento LIKE '%entrada%' OR ib.tipo_movimiento LIKE '%compra%')";
                break;
            case 'salida':
                $sql .= " AND (ib.tipo_movimiento LIKE '%salida%' OR ib.tipo_movimiento LIKE '%venta%')";
                break;
            case 'ajuste':
                $sql .= " AND ib.tipo_movimiento LIKE '%ajuste%'";
                break;
            case 'traslado':
                $sql .= " AND ib.tipo_movimiento LIKE '%traslado%'";
                break;
        }
    }

    if ($idAlmacen > 0) {
        $sql .= " AND ca.id = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if (!empty($fechaDesde)) {
        $sql .= " AND DATE(ib.fecha) >= ?";
        $params[] = $fechaDesde;
        $types .= "s";
    }

    if (!empty($fechaHasta)) {
        $sql .= " AND DATE(ib.fecha) <= ?";
        $params[] = $fechaHasta;
        $types .= "s";
    }

    if (!empty($referencia)) {
        $sql .= " AND (ib.referencia LIKE ? OR ib.tipo_movimiento LIKE ? OR cp.nombre LIKE ?)";
        $searchTerm = '%' . $referencia . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    // Consulta para contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;
    $recordsFiltered = $recordsTotal;

    // Ordenamiento
    switch ($ordenarPor) {
        case 'producto':
            $orderColumn = 'cp.nombre';
            break;
        case 'tipo_movimiento':
            $orderColumn = 'tipo_movimiento';
            break;
        case 'almacen':
            $orderColumn = 'ca.almacen';
            break;
        default:
            $orderColumn = 'ib.fecha';
    }

    $sql .= " ORDER BY $orderColumn DESC, ib.fecha DESC";
    $sql .= " LIMIT ? OFFSET ?";

    $params[] = $length;
    $params[] = $start;
    $types .= "ii";

    // Ejecutar consulta principal
    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Procesar datos para DataTables
    $data = [];
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $cantidad = floatval($item['cantidad']);
            $tipoMovimiento = $item['tipo_movimiento'];

            // Determinar clase CSS según tipo de movimiento
            $rowClass = '';
            $badgeClass = '';

            if (
                strpos(strtolower($tipoMovimiento), 'entrada') !== false ||
                strpos(strtolower($tipoMovimiento), 'compra') !== false
            ) {
                $rowClass = 'movimiento-entrada';
                $badgeClass = 'badge-success';
            } elseif (
                strpos(strtolower($tipoMovimiento), 'salida') !== false ||
                strpos(strtolower($tipoMovimiento), 'venta') !== false
            ) {
                $rowClass = 'movimiento-salida';
                $badgeClass = 'badge-danger';
            } elseif (strpos(strtolower($tipoMovimiento), 'ajuste') !== false) {
                $rowClass = 'movimiento-ajuste';
                $badgeClass = 'badge-warning';
            } elseif (strpos(strtolower($tipoMovimiento), 'traslado') !== false) {
                $rowClass = 'movimiento-traslado';
                $badgeClass = 'badge-info';
            } else {
                $badgeClass = 'badge-secondary';
            }

            // Formatear cantidad con signo
            $cantidadFormateada = $cantidad;
            if (
                strpos(strtolower($tipoMovimiento), 'entrada') !== false ||
                strpos(strtolower($tipoMovimiento), 'compra') !== false
            ) {
                $cantidadFormateada = '<span class="text-success"><strong>+' . number_format($cantidad, 2) . '</strong></span>';
            } elseif (
                strpos(strtolower($tipoMovimiento), 'salida') !== false ||
                strpos(strtolower($tipoMovimiento), 'venta') !== false
            ) {
                $cantidadFormateada = '<span class="text-danger"><strong>-' . number_format($cantidad, 2) . '</strong></span>';
            } else {
                $cantidadFormateada = '<span class="text-primary">' . number_format($cantidad, 2) . '</span>';
            }

            // Formatear fecha
            $fechaHora = !empty($item['fecha_hora']) ? date('d/m/Y H:i', strtotime($item['fecha_hora'])) : 'N/A';

            $data[] = [
                'fecha_hora' => $fechaHora,
                'producto' => $item['producto'] ?? 'N/A',
                'categoria' => $item['categoria'] ?? 'Sin categoría',
                'tipo_movimiento' => '<span class="badge ' . $badgeClass . ' badge-movimiento">' . $tipoMovimiento . '</span>',
                'almacen' => $item['almacen'] ?? 'N/A',
                'cantidad' => $cantidadFormateada,
                'documento' => $item['documento'] ?? 'N/A',
                'referencia' => $item['referencia_detalle'] ?? '',
                'usuario' => $item['usuario'] ?? 'Sistema',
                'saldo_anterior' => number_format($item['saldo_anterior'], 2),
                'saldo_actual' => number_format($item['saldo_actual'], 2),
                'observaciones' => $item['observaciones'] ?? '',
                'DT_RowClass' => $rowClass
            ];
        }
    }

    // Respuesta para DataTables
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
} catch (Exception $e) {
    error_log("Error en tabla-trazabilidad-productos: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
