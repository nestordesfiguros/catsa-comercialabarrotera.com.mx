<?php
// ajax/inventario/auditar.php
require_once __DIR__ . '/../../lib/clsConsultas.php';
header('Content-Type: application/json; charset=utf-8');

$clsConsulta = new Consultas();

$id_almacen = isset($_POST['id_almacen']) && $_POST['id_almacen'] !== '' ? (int)$_POST['id_almacen'] : null;
$q          = trim($_POST['q'] ?? '');

try {
    // ---- 1) EXPECTED por almacén/producto (compras + devoluciones) - (remisiones + salidas_almacen)
    $whereAlm = $id_almacen ? " WHERE t.id_almacen = $id_almacen " : "";

    $sqlEsperado = "
      SELECT t.id_almacen, t.id_producto, SUM(t.cant) AS esperado_almacen
      FROM (
        /* ENTRADAS: compras procesadas */
        SELECT cp.id_almacen, mp.id_producto, SUM(mp.cantidad) AS cant
        FROM mov_compras mp
        INNER JOIN cab_compras cp ON cp.id = mp.id_orden_compra
        WHERE cp.estatus = 'procesada'
        GROUP BY cp.id_almacen, mp.id_producto

        UNION ALL
        /* ENTRADAS: devoluciones activas -> regresan a almacén seleccionado */
        SELECT cd.id_almacen, md.id_producto, SUM(md.cantidad) AS cant
        FROM mov_devoluciones md
        INNER JOIN cab_devoluciones cd ON cd.id_devolucion = md.id_devolucion
        WHERE cd.estatus = 'activo'
        GROUP BY cd.id_almacen, md.id_producto

        UNION ALL
        /* SALIDAS: remisiones procesadas */
        SELECT cr.id_almacen, mr.id_producto, -SUM(mr.cantidad) AS cant
        FROM mov_remisiones mr
        INNER JOIN cab_remisiones cr ON cr.id = mr.id_remision
        WHERE cr.estatus = 'procesada'
        GROUP BY cr.id_almacen, mr.id_producto

        UNION ALL
        /* SALIDAS: salidas de almacén procesadas */
        SELECT cs.id_almacen, ms.id_producto, -SUM(ms.cantidad) AS cant
        FROM mov_salidas_almacen ms
        INNER JOIN cab_salidas_almacen cs ON cs.id_salida = ms.id_salida
        WHERE cs.estatus = 'procesada'
        GROUP BY cs.id_almacen, ms.id_producto
      ) t
      GROUP BY t.id_almacen, t.id_producto
    ";

    // ---- 2) INVENTARIO actual por almacén/producto
    $sqlActual = "SELECT id_almacen, id_producto, cantidad AS inventario_almacen FROM inventarios";

    // ---- 3) GLOBAL esperado por producto
    $sqlGlobalEsperado = "
      SELECT t.id_producto, SUM(t.cant) AS global_esperado
      FROM (
        SELECT mp.id_producto, SUM(mp.cantidad) AS cant
        FROM mov_compras mp
        INNER JOIN cab_compras cp ON cp.id = mp.id_orden_compra
        WHERE cp.estatus = 'procesada'
        GROUP BY mp.id_producto

        UNION ALL
        SELECT md.id_producto, SUM(md.cantidad) AS cant
        FROM mov_devoluciones md
        INNER JOIN cab_devoluciones cd ON cd.id_devolucion = md.id_devolucion
        WHERE cd.estatus = 'activo'
        GROUP BY md.id_producto

        UNION ALL
        SELECT mr.id_producto, -SUM(mr.cantidad) AS cant
        FROM mov_remisiones mr
        INNER JOIN cab_remisiones cr ON cr.id = mr.id_remision
        WHERE cr.estatus = 'procesada'
        GROUP BY mr.id_producto

        UNION ALL
        SELECT ms.id_producto, -SUM(ms.cantidad) AS cant
        FROM mov_salidas_almacen ms
        INNER JOIN cab_salidas_almacen cs ON cs.id_salida = ms.id_salida
        WHERE cs.estatus = 'procesada'
        GROUP BY ms.id_producto
      ) t
      GROUP BY t.id_producto
    ";

    // Cargar a memoria
    $mapEsperado = []; // [almacen][producto] = esperado
    $rsE = $clsConsulta->consultaGeneral($sqlEsperado);
    if ($clsConsulta->numrows > 0) {
        foreach ($rsE as $i => $r) {
            if ($i === 0) continue;
            $mapEsperado[(int)$r['id_almacen']][(int)$r['id_producto']] = (float)$r['esperado_almacen'];
        }
    }

    $mapActual = []; // [almacen][producto] = inventario_almacen
    $rsA = $clsConsulta->consultaGeneral($sqlActual);
    if ($clsConsulta->numrows > 0) {
        foreach ($rsA as $i => $r) {
            if ($i === 0) continue;
            $mapActual[(int)$r['id_almacen']][(int)$r['id_producto']] = (float)$r['inventario_almacen'];
        }
    }

    $globalEsperado = []; // [producto] = global esperado
    $rsGE = $clsConsulta->consultaGeneral($sqlGlobalEsperado);
    if ($clsConsulta->numrows > 0) {
        foreach ($rsGE as $i => $r) {
            if ($i === 0) continue;
            $globalEsperado[(int)$r['id_producto']] = (float)$r['global_esperado'];
        }
    }

    // Global actual por producto (suma de inventarios) y nombres
    $rsNombres = $clsConsulta->consultaGeneral("
      SELECT p.id_producto, p.nombre AS producto, p.cantidad_disponible AS global_actual FROM cat_productos p
    ");
    $nombreProd = [];
    $globalActual = [];
    if ($clsConsulta->numrows > 0) {
        foreach ($rsNombres as $i => $r) {
            if ($i === 0) continue;
            $nombreProd[(int)$r['id_producto']] = $r['producto'];
            $globalActual[(int)$r['id_producto']] = (float)$r['global_actual'];
        }
    }

    $rsAlm = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes");
    $nombreAlm = [];
    if ($clsConsulta->numrows > 0) {
        foreach ($rsAlm as $i => $r) {
            if ($i === 0) continue;
            $nombreAlm[(int)$r['id']] = $r['almacen'];
        }
    }

    // Construir lista de discrepancias
    $rows = [];
    // Recorremos todos los combos que aparecen en esperado o actual
    $almKeys = array_unique(array_merge(array_keys($mapEsperado), array_keys($mapActual)));
    foreach ($almKeys as $idAlm) {
        $prods = [];
        if (isset($mapEsperado[$idAlm])) $prods += $mapEsperado[$idAlm];
        if (isset($mapActual[$idAlm]))   $prods += $mapActual[$idAlm];

        foreach ($prods as $idProd => $_) {
            $esp = (float)($mapEsperado[$idAlm][$idProd] ?? 0);
            $act = (float)($mapActual[$idAlm][$idProd]   ?? 0);

            if ($esp == $act) continue; // sin discrepancia

            // filtro por almacén
            if ($id_almacen && $id_almacen != $idAlm) continue;

            // filtro por búsqueda
            if ($q !== '') {
                $needle = mb_strtolower($q, 'UTF-8');
                $comp   = mb_strtolower(($nombreProd[$idProd] ?? '') . ' ' . $idProd, 'UTF-8');
                if (mb_strpos($comp, $needle) === false) continue;
            }

            $rows[] = [
                'id_almacen'        => $idAlm,
                'almacen'           => $nombreAlm[$idAlm] ?? ('ALM ' . $idAlm),
                'id_producto'       => $idProd,
                'producto'          => $nombreProd[$idProd] ?? ('Producto ' . $idProd),
                'esperado_almacen'  => $esp,
                'inventario_almacen' => $act,
                'global_esperado'   => (float)($globalEsperado[$idProd] ?? 0),
                'global_actual'     => (float)($globalActual[$idProd] ?? 0),
            ];
        }
    }

    echo json_encode(['success' => true, 'rows' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
