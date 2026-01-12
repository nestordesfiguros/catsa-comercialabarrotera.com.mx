<?php
// ajax/ofertas/tabla-ofertas.php
session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

$draw = $_POST['draw'] ?? 1;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 25;
$search = $_POST['search']['value'] ?? '';
$filtroVigencia = $_POST['vigencia'] ?? '';

// Construir condiciones dinámicas
$condiciones = [];

if (!empty($search)) {
    $condiciones[] = "(o.nombre LIKE '%".$clsConsulta->escape($search)."%' 
                    OR o.descripcion LIKE '%".$clsConsulta->escape($search)."%')";
}

if (!empty($filtroVigencia)) {
    switch ($filtroVigencia) {
        case 'activas':
            $condiciones[] = "CURDATE() BETWEEN DATE(o.fecha_inicio) AND DATE(o.fecha_fin)";
            break;
        case 'futuras':
            $condiciones[] = "DATE(o.fecha_inicio) > CURDATE()";
            break;
        case 'vencidas':
            $condiciones[] = "DATE(o.fecha_fin) < CURDATE()";
            break;
    }
}

$where = '';
if (!empty($condiciones)) {
    $where = ' WHERE ' . implode(' AND ', $condiciones);
}

// Consulta base
$query = "SELECT 
    o.id_oferta, 
    o.nombre, 
    o.tipo_oferta as tipo,
    CONCAT(DATE_FORMAT(o.fecha_inicio, '%d/%m/%Y'), ' - ', DATE_FORMAT(o.fecha_fin, '%d/%m/%Y')) as vigencia,
    o.estatus,
    (SELECT COUNT(*) FROM mov_ofertas_productos WHERE id_oferta = o.id_oferta) as productos_count
FROM cat_ofertas o";

// Total sin filtros
$totalQuery = "SELECT COUNT(*) as total FROM cat_ofertas";
$totalResult = $clsConsulta->consultaGeneral($totalQuery);
$totalRecords = isset($totalResult[0]['total']) ? (int)$totalResult[0]['total'] : 0;

// Total con filtros
$filteredQuery = "SELECT COUNT(*) as filtered FROM cat_ofertas o" . $where;
$filteredResult = $clsConsulta->consultaGeneral($filteredQuery);
$filteredRecords = isset($filteredResult[0]['filtered']) ? (int)$filteredResult[0]['filtered'] : 0;

// Consulta final con filtros, orden y paginación
$order = " ORDER BY o.fecha_inicio DESC";
$limit = " LIMIT $start, $length";
$finalQuery = $query . $where . $order . $limit;

$result = $clsConsulta->consultaGeneral($finalQuery);

$data = [];
if ($result && is_array($result)) {
    foreach ($result as $row) {
        $estatus = $row['estatus'] == 1 
            ? '<span class="badge bg-success">Activa</span>' 
            : '<span class="badge bg-danger">Inactiva</span>';

        $acciones = '
        <div class="btn-group">
            <button class="btn btn-sm btn-info" onclick="editarOferta('.$row['id_oferta'].')">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm '.($row['estatus']==1?'btn-success':'btn-danger').'" 
                onclick="cambiarEstatusOferta('.$row['id_oferta'].','.($row['estatus']==1?0:1).')">
                <i class="fas '.($row['estatus']==1?'fa-check':'fa-ban').'"></i>
            </button>
            <button class="btn btn-sm btn-primary" onclick="verProductosOferta('.$row['id_oferta'].')">
                <i class="fas fa-eye"></i>
            </button>
        </div>';

        $data[] = [
            'nombre' => htmlspecialchars($row['nombre'] ?? ''),
            'tipo' => ucfirst(str_replace('_', ' ', $row['tipo'] ?? '')),
            'vigencia' => $row['vigencia'] ?? '',
            'productos' => $row['productos_count'] ?? 0,
            'estatus' => $estatus,
            'acciones' => $acciones
        ];
    }
}

// Respuesta JSON
echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data
]);

?>