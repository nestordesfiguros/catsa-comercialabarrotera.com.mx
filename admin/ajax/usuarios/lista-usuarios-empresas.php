<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$accion = $_POST['accion'] ?? '';

if ($accion === 'usuarios') {
    $usuarios = [];

    $sql = "SELECT u.id, CONCAT(p.nombre, ' ', p.apellido1, ' ', p.apellido2) AS nombre
            FROM usuarios u
            LEFT JOIN cat_personal p ON u.id_personal = p.id
            ORDER BY nombre";
    $res = $clsConsulta->consultaGeneral($sql);

    foreach ($res as $u) {
        $usuarios[] = [
            'id' => $u['id'],
            'nombre' => $u['nombre']
        ];
    }

    echo json_encode(['usuarios' => $usuarios]);
    exit;
}

if ($accion === 'empresas') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $empresas = [];

    if ($id_usuario <= 0) {
        echo json_encode(['empresas' => []]);
        exit;
    }

    $sqlEmpresas = "SELECT e.id, e.razon_social,
        CASE WHEN ue.id IS NULL THEN 0 ELSE 1 END AS asignada
        FROM cat_empresas e
        LEFT JOIN usuarios_empresas ue
          ON ue.id_empresa = e.id AND ue.id_usuario = $id_usuario
        ORDER BY e.razon_social";

    $res = $clsConsulta->consultaGeneral($sqlEmpresas);

    foreach ($res as $e) {
        $empresas[] = [
            'id' => $e['id'],
            'razon_social' => $e['razon_social'],
            'asignada' => (bool)$e['asignada']
        ];
    }

    echo json_encode(['empresas' => $empresas]);
    exit;
}

echo json_encode(['error' => 'Acción inválida']);
