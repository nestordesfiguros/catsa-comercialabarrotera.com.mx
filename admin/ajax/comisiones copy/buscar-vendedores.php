    <?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Obtener término de búsqueda enviado por Select2
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// Escapar el término
$search = $clsConsulta->escape($search);

// Consulta base
$sql = "
    SELECT id, CONCAT(nombre, ' ', apellido1, ' ', apellido2) AS nombre
    FROM cat_vendedores
    WHERE 1
";

// Si hay búsqueda, se aplica filtro por nombre
if ($search !== '') {
    $sql .= " AND (nombre LIKE '%$search%' OR apellido1 LIKE '%$search%' OR apellido2 LIKE '%$search%')";
}

$sql .= " ORDER BY nombre ASC LIMIT 30";

$resultados = $clsConsulta->consultaGeneral($sql);

$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($resultados as $v) {
        $data[] = [
            'id' => $v['id'],
            'text' => $v['nombre']
        ];
    }
}

// Devolver en formato JSON
header('Content-Type: application/json');
echo json_encode($data);
