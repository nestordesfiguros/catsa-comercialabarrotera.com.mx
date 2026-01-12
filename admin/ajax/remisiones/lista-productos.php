<?php
// ajax/remisiones/lista-productos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';

$cond = " WHERE p.estatus=1 ";
if ($search !== '') {
    $searchEsc = $clsConsulta->escape('%' . $search . '%');
    $cond .= " AND (p.clave LIKE $searchEsc OR p.nombre LIKE $searchEsc) ";
}

// Total por producto sumando en todos los almacenes
$sql = "
  SELECT 
    p.id_producto,
    p.clave,
    p.nombre,
    p.id_categoria,
    p.precio_venta,
    COALESCE(SUM(i.cantidad),0) AS existencia_total
  FROM cat_productos p
  LEFT JOIN inventarios i ON i.id_producto = p.id_producto
  $cond
  GROUP BY p.id_producto
  ORDER BY p.nombre ASC
  LIMIT 200
";
$rows = $clsConsulta->consultaGeneral($sql);

$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($rows as $idx => $r) {
        if ($idx === 0) continue;

        $idProd = (int)$r['id_producto'];

        // Reservado total (todas las bodegas) -> solo reservas vigentes
        $rsResT = $clsConsulta->consultaGeneral("
            SELECT COALESCE(SUM(cantidad),0) AS reservado_total
            FROM inventarios_reservas
            WHERE id_producto = $idProd
              AND estatus = 'reservado'
              AND (vence_at IS NULL OR vence_at > NOW())
        ");
        $reservadoTotal = (float)($rsResT[1]['reservado_total'] ?? 0);

        $existenciaTotal = (float)$r['existencia_total'];
        $disponibleTotal = $existenciaTotal - $reservadoTotal;

        // Detalle por almacén (inventario > 0)
        $detInv = $clsConsulta->consultaGeneral("
            SELECT i.id_almacen, a.almacen, COALESCE(i.cantidad,0) AS inv_total
            FROM inventarios i
            INNER JOIN cat_almacenes a ON a.id = i.id_almacen
            WHERE i.id_producto = $idProd AND COALESCE(i.cantidad,0) > 0
            ORDER BY i.id_almacen ASC
        ");

        // Reservas por almacén (para mapear)
        $detRes = $clsConsulta->consultaGeneral("
            SELECT id_almacen, COALESCE(SUM(cantidad),0) AS reservado
            FROM inventarios_reservas
            WHERE id_producto = $idProd
              AND estatus = 'reservado'
              AND (vence_at IS NULL OR vence_at > NOW())
            GROUP BY id_almacen
        ");
        $mapRes = [];
        if ($clsConsulta->numrows > 0) {
            foreach ($detRes as $j => $rr) {
                if ($j === 0) continue;
                $mapRes[(int)$rr['id_almacen']] = (float)$rr['reservado'];
            }
        }

        $detalle = [];
        if (is_array($detInv) && $clsConsulta->numrows > 0) {
            foreach ($detInv as $j => $d) {
                if ($j === 0) continue;

                $idAlm = (int)$d['id_almacen'];
                $inv = (float)$d['inv_total'];
                $res = (float)($mapRes[$idAlm] ?? 0);
                $disp = $inv - $res;

                $detalle[] = [
                    'id_almacen'  => $idAlm,
                    'almacen'     => $d['almacen'],
                    'inv_total'   => $inv,
                    'reservado'   => $res,
                    'disponible'  => $disp,
                ];
            }
        }

        $data[] = [
            'id_producto'        => $idProd,
            'clave'              => $r['clave'],
            'nombre'             => $r['nombre'],
            'categoria'          => $r['id_categoria'],
            'precio_venta'       => (float)$r['precio_venta'],
            'existencia_total'   => $existenciaTotal,
            'reservado_total'    => $reservadoTotal,
            'disponible_total'   => $disponibleTotal,
            'detalle_almacenes'  => $detalle,
        ];
    }
}

echo json_encode([
    'success' => true,
    'data'    => $data
], JSON_UNESCAPED_UNICODE);
