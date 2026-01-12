<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Parámetros de DataTable
$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Filtros
$filtro_cliente   = isset($_POST['filtro_cliente']) ? intval($_POST['filtro_cliente']) : 0;
$filtro_fecha_ini = $_POST['filtro_fecha_ini'] ?? '';
$filtro_fecha_fin = $_POST['filtro_fecha_fin'] ?? '';
$filtro_estatus   = $_POST['filtro_estatus'] ?? '';

// WHERE
$where = "1";
if ($filtro_cliente > 0) {
    $where .= " AND cxc.id_cliente = $filtro_cliente";
}
if ($filtro_fecha_ini) {
    $where .= " AND cxc.fecha_emision >= '$filtro_fecha_ini'";
}
if ($filtro_fecha_fin) {
    $where .= " AND cxc.fecha_emision <= '$filtro_fecha_fin'";
}
if ($filtro_estatus) {
    $where .= " AND cxc.estatus = '$filtro_estatus'";
}
if ($search != '') {
    $like = "%" . $search . "%";
    $where .= " AND (
        cl.razon_social LIKE '$like'
        OR cxc.monto LIKE '$like'
        OR cxc.estatus LIKE '$like'
        OR cxc.id_cxc LIKE '$like'
    )";
}

// Total sin filtro
$sql_total = "SELECT COUNT(*) AS total FROM cab_cxc cxc INNER JOIN cat_clientes cl ON cxc.id_cliente = cl.id WHERE 1";
$res_total = $clsConsulta->consultaGeneral($sql_total);
$recordsTotal = $res_total[0]['total'] ?? 0;

// Total con filtro
$sql_filtrado = "SELECT COUNT(*) AS total FROM cab_cxc cxc INNER JOIN cat_clientes cl ON cxc.id_cliente = cl.id WHERE $where";
$res_filtrado = $clsConsulta->consultaGeneral($sql_filtrado);
$recordsFiltered = $res_filtrado[0]['total'] ?? 0;

// Consulta principal
$sql = "
    SELECT
        cxc.id_cxc,
        cxc.id_cliente,
        cxc.fecha_emision,
        cxc.fecha_vencimiento,
        cl.razon_social AS cliente,
        IFNULL((
            SELECT SUM(mr.cantidad * mr.precio)
            FROM mov_remisiones mr
            WHERE mr.id_remision = cxc.id_remision
        ), 0) AS monto,
        IFNULL((
            SELECT SUM(m.abono)
            FROM mov_cxc m
            WHERE m.id_cxc = cxc.id_cxc
        ), 0) AS total_abonos,
        cxc.estatus,
        cxc.observaciones
    FROM cab_cxc cxc
    INNER JOIN cat_clientes cl ON cxc.id_cliente = cl.id
    WHERE $where
    ORDER BY cxc.fecha_emision DESC, cxc.id_cxc DESC
    LIMIT $start, $length
";

$rows = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($rows) {
    foreach ($rows as $row) {
        $folio = 'CXC-' . str_pad($row['id_cxc'], 6, '0', STR_PAD_LEFT);
        $monto = floatval($row['monto']);
        $abonos = floatval($row['total_abonos']);
        $saldo = max(0, $monto - $abonos); // evitar negativos

        // Etiqueta de estatus
        $label_estatus = '<span class="badge bg-secondary">DESCONOCIDO</span>';
        if ($saldo == 0 && $monto > 0) {
            $label_estatus = '<span class="badge bg-success text-white">PAGADA</span>';
        } elseif ($saldo > 0 && $abonos > 0) {
            $label_estatus = '<span class="badge bg-info text-white">PARCIALMENTE PAGADA</span>';
        } elseif ($saldo > 0 && $abonos == 0) {
            $label_estatus = '<span class="badge bg-warning text-dark">PENDIENTE</span>';
        }
        if ($row['estatus'] == 'Cancelada') {
            $label_estatus = '<span class="badge bg-dark text-white">CANCELADA</span>';
        } elseif ($row['estatus'] == 'Vencida') {
            $label_estatus = '<span class="badge bg-danger text-white">VENCIDA</span>';
        }

        // Botón abonar
        if ($saldo > 0) {
            $btnAbonar = '<button class="btn btn-outline-success btn-sm me-1 btn-abonar-cxc" title="Abonar"
                data-id="' . $row['id_cxc'] . '"
                data-saldo="' . $saldo . '"
                data-cliente="' . htmlspecialchars($row['cliente']) . '"
                data-folio="' . $folio . '"
            ><i class="fas fa-cash-register"></i></button>';
        } else {
            $btnAbonar = '<button class="btn btn-outline-secondary btn-sm me-1" title="Cuenta pagada" disabled>
                <i class="fas fa-cash-register"></i></button>';
        }

        $acciones = $btnAbonar . '
            <button class="btn btn-outline-info btn-sm" title="Detalle"
                onclick="window.location.href=\'cxc-detalle/' . $row['id_cxc'] . '\'">
                <i class="fas fa-eye"></i>
            </button>';

        $data[] = [
            'folio'            => $folio,
            'cliente'          => htmlspecialchars($row['cliente']),
            'fecha_emision'    => $row['fecha_emision'],
            'fecha_vencimiento'=> $row['fecha_vencimiento'],
            'monto'            => $monto,
            'saldo'            => $saldo,
            'estatus'          => $label_estatus,
            'acciones'         => $acciones
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
