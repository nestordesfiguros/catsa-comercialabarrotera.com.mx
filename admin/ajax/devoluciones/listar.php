<?php
// ajax/devoluciones/listar.php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start  = intval($_POST['start']  ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw']   ?? 1);
$search = trim($_POST['search']['value'] ?? '');

$where = "WHERE 1=1";
if ($search !== '') {
    $searchEscapado = addslashes($search);
    $where .= " AND (
        d.id_devolucion LIKE '%$searchEscapado%' OR
        d.id_documento  LIKE '%$searchEscapado%' OR
        c.razon_social  LIKE '%$searchEscapado%'
    )";
}

/* Total sin filtro (para DataTables) */
$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM cab_devoluciones d
";
$resTotal = $clsConsulta->consultaGeneral($sqlTotal);
$total = isset($resTotal[1]['total']) ? intval($resTotal[1]['total']) : 0;

/* Total filtrado (aplicando búsqueda) */
$sqlFiltrado = "
    SELECT COUNT(*) AS total
    FROM cab_devoluciones d
    LEFT JOIN cat_clientes c ON d.id_cliente = c.id
    $where
";
$resFiltrado = $clsConsulta->consultaGeneral($sqlFiltrado);
$totalFiltrado = isset($resFiltrado[1]['total']) ? intval($resFiltrado[1]['total']) : 0;

/* Datos paginados */
$sql = "
    SELECT 
        d.id_devolucion,
        d.id_cliente,
        d.tipo_documento,
        d.id_documento,
        DATE_FORMAT(d.fecha, '%d/%m/%Y %H:%i') AS fecha,
        c.razon_social AS cliente,
        COALESCE(d.monto_total, 0) AS monto_total,
        d.estatus
    FROM cab_devoluciones d
    LEFT JOIN cat_clientes c ON d.id_cliente = c.id
    $where
    ORDER BY d.fecha DESC
    LIMIT $start, $length
";
$res = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($res as $i => $row) {
        if ($i === 0) continue; // por la estructura del wrapper

        $id_devolucion = (int)$row['id_devolucion'];
        $id_cliente    = (int)$row['id_cliente'];

        $doc = strtoupper($row['tipo_documento']) . ' ' . $row['id_documento'];
        $monto = '$' . number_format((float)$row['monto_total'], 2);
        $estatus = ucfirst($row['estatus']);

        $acciones = '
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary btn-ver-devolucion" data-id="' . $id_devolucion . '">VER</button>
                <a href="pdf/pdf-devolucion.php?id=' . $id_devolucion . '&idc=' . $id_cliente . '" target="_blank" class="btn btn-sm btn-outline-danger">PDF</a>
            </div>';

        $data[] = [
            $id_devolucion,
            $doc,
            $row['fecha'],
            $row['cliente'],
            $monto,
            $estatus,
            $acciones
        ];
    }
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,          // total sin filtro
    'recordsFiltered' => $totalFiltrado,  // total con filtro
    'data'            => $data
]);
