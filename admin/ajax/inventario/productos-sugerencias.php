<?php
// ajax/inventario/productos-sugerencias.php
require_once __DIR__ . '/../../lib/clsConsultas.php';
header('Content-Type: application/json; charset=utf-8');

$clsConsulta = new Consultas();

$q = trim($_POST['q'] ?? '');
$id_almacen = isset($_POST['id_almacen']) && $_POST['id_almacen'] !== '' ? (int)$_POST['id_almacen'] : null;

try {
    if ($q === '') {
        echo json_encode(['success'=>true,'data'=>[]]);
        exit;
    }

    $qEsc = $clsConsulta->escape('%'.$q.'%');

    // Si quieres filtrar por almacén en las sugerencias, puedes hacer join a inventarios.
    // Aquí solo buscamos en cat_productos por nombre o clave:
    $sql = "
      SELECT p.id_producto, p.clave, p.nombre
      FROM cat_productos p
      WHERE p.estatus = 1
        AND (p.nombre LIKE '$qEsc' OR p.clave LIKE '$qEsc' OR CAST(p.id_producto AS CHAR) LIKE '$qEsc')
      ORDER BY p.nombre
      LIMIT 20
    ";
    $rs = $clsConsulta->consultaGeneral($sql);

    $data = [];
    if (($clsConsulta->numrows ?? 0) > 0) {
        foreach ($rs as $i=>$r) {
            if ($i===0) continue;
            $etiqueta = trim(($r['clave'] ? $r['clave'].' · ' : '').($r['nombre'] ?? '')).' (#'.$r['id_producto'].')';
            $data[] = [
              'id_producto' => (int)$r['id_producto'],
              'etiqueta'    => $etiqueta
            ];
        }
    }

    echo json_encode(['success'=>true,'data'=>$data]);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
