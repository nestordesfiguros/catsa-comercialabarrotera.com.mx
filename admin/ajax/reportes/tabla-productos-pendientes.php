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
$tipoPendiente = $_POST['tipo_pendiente'] ?? '';
$estado = $_POST['estado'] ?? '';
$idProveedor = $_POST['id_proveedor'] ?? 0;
$idCliente = $_POST['id_cliente'] ?? 0;
$fechaDesde = $_POST['fecha_desde'] ?? '';
$fechaHasta = $_POST['fecha_hasta'] ?? '';
$diasAtraso = $_POST['dias_atraso'] ?? 0;
$ordenarPor = $_POST['ordenar_por'] ?? 'fecha_entrega';

try {
    // Primero, vamos a obtener datos de manera más simple - sin UNION
    $datos = [];
    $recordsTotal = 0;

    // Consulta 1: Pedidos pendientes
    if (empty($tipoPendiente) || $tipoPendiente === 'nota_venta') {
        $sqlPedidos = "
            SELECT 
                'Pedido' AS tipo,
                CONCAT('PED-', cp.id) AS documento,
                cprod.nombre AS producto,
                ccli.razon_social AS contacto,
                mp.cantidad AS cantidad_pendiente,
                0 AS cantidad_entregada,
                mp.cantidad AS cantidad_total,
                0 AS porcentaje_avance,
                cp.fecha AS fecha_pedido,
                DATE_ADD(cp.fecha, INTERVAL 7 DAY) AS fecha_entrega,
                DATEDIFF(CURDATE(), DATE_ADD(cp.fecha, INTERVAL 7 DAY)) AS dias_atraso,
                mp.precio AS precio_unitario,
                (mp.cantidad * mp.precio) AS total_pendiente,
                cp.estatus AS estado,
                'media' AS prioridad
            FROM 
                cab_pedidos cp
            INNER JOIN 
                mov_pedidos mp ON cp.id = mp.id_pedido
            INNER JOIN 
                cat_productos cprod ON mp.id_producto = cprod.id_producto
            INNER JOIN 
                cat_clientes ccli ON cp.id_cliente = ccli.id
            WHERE 
                cp.estatus = 'pendiente'
        ";

        $params = [];
        $types = "";

        // Aplicar filtros específicos para pedidos
        if ($idCliente > 0) {
            $sqlPedidos .= " AND cp.id_cliente = ?";
            $params[] = $idCliente;
            $types .= "i";
        }

        if (!empty($fechaDesde)) {
            $sqlPedidos .= " AND cp.fecha >= ?";
            $params[] = $fechaDesde;
            $types .= "s";
        }

        if (!empty($fechaHasta)) {
            $sqlPedidos .= " AND cp.fecha <= ?";
            $params[] = $fechaHasta . ' 23:59:59';
            $types .= "s";
        }

        // Aplicar filtro de días de atraso
        if ($diasAtraso > 0) {
            $sqlPedidos .= " AND DATEDIFF(CURDATE(), DATE_ADD(cp.fecha, INTERVAL 7 DAY)) >= ?";
            $params[] = $diasAtraso;
            $types .= "i";
        }

        // Aplicar filtro de estado
        if (!empty($estado)) {
            switch ($estado) {
                case 'pendiente':
                    // Todos los pedidos están pendientes por defecto
                    break;
                case 'atrasado':
                    $sqlPedidos .= " AND DATEDIFF(CURDATE(), DATE_ADD(cp.fecha, INTERVAL 7 DAY)) > 0";
                    break;
                case 'urgente':
                    $sqlPedidos .= " AND DATEDIFF(CURDATE(), DATE_ADD(cp.fecha, INTERVAL 7 DAY)) > 3";
                    break;
            }
        }

        $sqlPedidos .= " ORDER BY cp.fecha ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;
        $types .= "ii";

        $pedidos = $clsConsulta->consultaPreparada($sqlPedidos, $params, $types);
        if ($pedidos) {
            $datos = array_merge($datos, $pedidos);
        }
    }

    // Consulta 2: Remisiones pendientes
    if (empty($tipoPendiente) || $tipoPendiente === 'nota_venta') {
        $sqlRemisiones = "
            SELECT 
                'Remisión' AS tipo,
                CONCAT('REM-', cr.id) AS documento,
                cp.nombre AS producto,
                ccli.razon_social AS contacto,
                mr.cantidad AS cantidad_pendiente,
                0 AS cantidad_entregada,
                mr.cantidad AS cantidad_total,
                0 AS porcentaje_avance,
                cr.fecha AS fecha_pedido,
                DATE_ADD(cr.fecha, INTERVAL 3 DAY) AS fecha_entrega,
                DATEDIFF(CURDATE(), DATE_ADD(cr.fecha, INTERVAL 3 DAY)) AS dias_atraso,
                mr.precio AS precio_unitario,
                (mr.cantidad * mr.precio) AS total_pendiente,
                cr.estatus AS estado,
                'alta' AS prioridad
            FROM 
                cab_remisiones cr
            INNER JOIN 
                mov_remisiones mr ON cr.id = mr.id_remision
            INNER JOIN 
                cat_productos cp ON mr.id_producto = cp.id_producto
            INNER JOIN 
                cat_clientes ccli ON cr.id_cliente = ccli.id
            WHERE 
                cr.estatus = 'pendiente'
        ";

        $params = [];
        $types = "";

        // Aplicar filtros específicos para remisiones
        if ($idCliente > 0) {
            $sqlRemisiones .= " AND cr.id_cliente = ?";
            $params[] = $idCliente;
            $types .= "i";
        }

        if (!empty($fechaDesde)) {
            $sqlRemisiones .= " AND cr.fecha >= ?";
            $params[] = $fechaDesde;
            $types .= "s";
        }

        if (!empty($fechaHasta)) {
            $sqlRemisiones .= " AND cr.fecha <= ?";
            $params[] = $fechaHasta . ' 23:59:59';
            $types .= "s";
        }

        // Aplicar filtro de días de atraso
        if ($diasAtraso > 0) {
            $sqlRemisiones .= " AND DATEDIFF(CURDATE(), DATE_ADD(cr.fecha, INTERVAL 3 DAY)) >= ?";
            $params[] = $diasAtraso;
            $types .= "i";
        }

        // Aplicar filtro de estado
        if (!empty($estado)) {
            switch ($estado) {
                case 'pendiente':
                    // Todas las remisiones están pendientes por defecto
                    break;
                case 'atrasado':
                    $sqlRemisiones .= " AND DATEDIFF(CURDATE(), DATE_ADD(cr.fecha, INTERVAL 3 DAY)) > 0";
                    break;
                case 'urgente':
                    $sqlRemisiones .= " AND DATEDIFF(CURDATE(), DATE_ADD(cr.fecha, INTERVAL 3 DAY)) > 1";
                    break;
            }
        }

        $sqlRemisiones .= " ORDER BY cr.fecha ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;
        $types .= "ii";

        $remisiones = $clsConsulta->consultaPreparada($sqlRemisiones, $params, $types);
        if ($remisiones) {
            $datos = array_merge($datos, $remisiones);
        }
    }

    // Consulta 3: Entradas de almacén pendientes
    if (empty($tipoPendiente) || $tipoPendiente === 'orden_compra') {
        $sqlEntradas = "
            SELECT 
                'Entrada Almacén' AS tipo,
                CONCAT('EA-', cea.id) AS documento,
                cp.nombre AS producto,
                cprov.razon_social AS contacto,
                mea.cantidad AS cantidad_pendiente,
                0 AS cantidad_entregada,
                mea.cantidad AS cantidad_total,
                0 AS porcentaje_avance,
                cea.fecha AS fecha_pedido,
                DATE_ADD(cea.fecha, INTERVAL 5 DAY) AS fecha_entrega,
                DATEDIFF(CURDATE(), DATE_ADD(cea.fecha, INTERVAL 5 DAY)) AS dias_atraso,
                mea.precio AS precio_unitario,
                (mea.cantidad * mea.precio) AS total_pendiente,
                cea.estatus AS estado,
                'media' AS prioridad
            FROM 
                cab_entradas_almacen cea
            INNER JOIN 
                mov_entradas_almacen mea ON cea.id = mea.id_orden_compra
            INNER JOIN 
                cat_productos cp ON mea.id_producto = cp.id_producto
            INNER JOIN 
                cat_proveedores cprov ON cea.id_proveedor = cprov.id
            WHERE 
                cea.estatus = 'pendiente'
        ";

        $params = [];
        $types = "";

        // Aplicar filtros específicos para entradas
        if ($idProveedor > 0) {
            $sqlEntradas .= " AND cea.id_proveedor = ?";
            $params[] = $idProveedor;
            $types .= "i";
        }

        if (!empty($fechaDesde)) {
            $sqlEntradas .= " AND cea.fecha >= ?";
            $params[] = $fechaDesde;
            $types .= "s";
        }

        if (!empty($fechaHasta)) {
            $sqlEntradas .= " AND cea.fecha <= ?";
            $params[] = $fechaHasta . ' 23:59:59';
            $types .= "s";
        }

        // Aplicar filtro de días de atraso
        if ($diasAtraso > 0) {
            $sqlEntradas .= " AND DATEDIFF(CURDATE(), DATE_ADD(cea.fecha, INTERVAL 5 DAY)) >= ?";
            $params[] = $diasAtraso;
            $types .= "i";
        }

        // Aplicar filtro de estado
        if (!empty($estado)) {
            switch ($estado) {
                case 'pendiente':
                    // Todas las entradas están pendientes por defecto
                    break;
                case 'atrasado':
                    $sqlEntradas .= " AND DATEDIFF(CURDATE(), DATE_ADD(cea.fecha, INTERVAL 5 DAY)) > 0";
                    break;
                case 'urgente':
                    $sqlEntradas .= " AND DATEDIFF(CURDATE(), DATE_ADD(cea.fecha, INTERVAL 5 DAY)) > 2";
                    break;
            }
        }

        $sqlEntradas .= " ORDER BY cea.fecha ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;
        $types .= "ii";

        $entradas = $clsConsulta->consultaPreparada($sqlEntradas, $params, $types);
        if ($entradas) {
            $datos = array_merge($datos, $entradas);
        }
    }

    // Para simplificar, usamos el count de los datos obtenidos
    $recordsTotal = count($datos);
    $recordsFiltered = $recordsTotal;

    // Procesar datos para DataTables
    $data = [];
    if (count($datos) > 0) {
        foreach ($datos as $item) {
            $cantidadPendiente = floatval($item['cantidad_pendiente']);
            $cantidadEntregada = floatval($item['cantidad_entregada']);
            $cantidadTotal = floatval($item['cantidad_total']);
            $porcentajeAvance = floatval($item['porcentaje_avance']);
            $diasAtraso = intval($item['dias_atraso']);
            $totalPendiente = floatval($item['total_pendiente']);
            $prioridad = $item['prioridad'] ?? 'media';
            $estado = $item['estado'] ?? 'pendiente';

            // Determinar estado y clase CSS
            $estadoDisplay = '';
            $estadoClass = '';
            $prioridadClass = '';

            if ($diasAtraso > 7) {
                $estadoDisplay = 'Muy Atrasado';
                $estadoClass = 'estado-atrasado';
            } elseif ($diasAtraso > 3) {
                $estadoDisplay = 'Atrasado';
                $estadoClass = 'estado-atrasado';
            } elseif ($prioridad === 'alta') {
                $estadoDisplay = 'Urgente';
                $estadoClass = 'estado-urgente';
            } elseif ($cantidadEntregada === 0) {
                $estadoDisplay = 'Pendiente';
                $estadoClass = 'estado-pendiente';
            } elseif ($cantidadPendiente > 0) {
                $estadoDisplay = 'Parcial';
                $estadoClass = 'estado-parcial';
            } else {
                $estadoDisplay = 'Completado';
                $estadoClass = 'estado-completado';
            }

            // Clase para prioridad
            switch ($prioridad) {
                case 'alta':
                    $prioridadClass = 'prioridad-alta';
                    break;
                case 'media':
                    $prioridadClass = 'prioridad-media';
                    break;
                case 'baja':
                    $prioridadClass = 'prioridad-baja';
                    break;
                default:
                    $prioridadClass = 'prioridad-media';
            }

            // Formatear fechas
            $fechaPedido = !empty($item['fecha_pedido']) ? date('d/m/Y', strtotime($item['fecha_pedido'])) : 'N/A';
            $fechaEntrega = !empty($item['fecha_entrega']) ? date('d/m/Y', strtotime($item['fecha_entrega'])) : 'N/A';

            $data[] = [
                'tipo' => $item['tipo'],
                'documento' => $item['documento'],
                'producto' => $item['producto'],
                'contacto' => $item['contacto'],
                'cantidad_pendiente' => number_format($cantidadPendiente, 2),
                'cantidad_entregada' => number_format($cantidadEntregada, 2),
                'cantidad_total' => number_format($cantidadTotal, 2),
                'porcentaje_avance' => number_format($porcentajeAvance, 1) . '%',
                'fecha_pedido' => $fechaPedido,
                'fecha_entrega' => $fechaEntrega,
                'dias_atraso' => $diasAtraso > 0 ?
                    '<span class="text-danger"><strong>' . $diasAtraso . ' días</strong></span>' :
                    '<span class="text-success">' . abs($diasAtraso) . ' días</span>',
                'estado' => '<span class="badge-estado ' . $estadoClass . '">' . $estadoDisplay . '</span>',
                'prioridad' => '<span class="' . $prioridadClass . '">' . ucfirst($prioridad) . '</span>',
                'total_pendiente' => '$' . number_format($totalPendiente, 2)
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
    error_log("Error en tabla-productos-pendientes: " . $e->getMessage());

    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
