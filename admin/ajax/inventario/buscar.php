<?php
// ajax/inventario/buscar.php
// Auditoría por almacén/producto:
//  - ENTRADAS: cab_entradas_almacen + mov_entradas_almacen (estatus=procesada)
//  - SALIDAS:  cab_remisiones + mov_remisiones (estatus=procesada)
//  - DEVOLUCIONES: (omitidas por falta de id_almacen en cab_devoluciones)
// Filtros:
//  - id_almacen (0 = todos)
//  - id_producto (0 = todos)
//  - mostrar_coincidencias (1 = incluye coincidencias; 0 = solo diferencias)

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $idAlmacen  = isset($_REQUEST['id_almacen']) ? (int)$_REQUEST['id_almacen'] : 0;
    $idProducto = isset($_REQUEST['id_producto']) ? (int)$_REQUEST['id_producto'] : 0;
    $showMatches = isset($_REQUEST['mostrar_coincidencias']) ? (int)$_REQUEST['mostrar_coincidencias'] : 0;

    // ENTRADAS por almacén/producto
    $subEntradas = "
        SELECT 
            c.id_almacen,
            m.id_producto,
            SUM(COALESCE(m.cantidad,0)) AS entradas
        FROM mov_entradas_almacen m
        INNER JOIN cab_entradas_almacen c 
            ON c.id = m.id_orden_compra
           AND c.estatus = 'procesada'
        GROUP BY c.id_almacen, m.id_producto
    ";

    // SALIDAS por almacén/producto (remisiones procesadas)
    $subSalidas = "
        SELECT 
            r.id_almacen,
            m.id_producto,
            SUM(COALESCE(m.cantidad,0)) AS salidas
        FROM mov_remisiones m
        INNER JOIN cab_remisiones r 
            ON r.id = m.id_remision
           AND r.estatus='procesada'
        GROUP BY r.id_almacen, m.id_producto
    ";

    // INVENTARIO REGISTRADO actual en inventarios
    $subInventario = "
        SELECT 
            i.id_almacen,
            i.id_producto,
            SUM(COALESCE(i.cantidad,0)) AS registrado
        FROM inventarios i
        GROUP BY i.id_almacen, i.id_producto
    ";

    // Universo de pares (almacén, producto) que aparecen en entradas, salidas o inventarios
    $allKeys = "
        SELECT id_almacen, id_producto FROM (
            SELECT c.id_almacen, m.id_producto
            FROM mov_entradas_almacen m
            INNER JOIN cab_entradas_almacen c 
                ON c.id = m.id_orden_compra
               AND c.estatus='procesada'
            GROUP BY c.id_almacen, m.id_producto

            UNION
            SELECT r.id_almacen, m.id_producto
            FROM mov_remisiones m
            INNER JOIN cab_remisiones r 
                ON r.id = m.id_remision
               AND r.estatus='procesada'
            GROUP BY r.id_almacen, m.id_producto

            UNION
            SELECT i.id_almacen, i.id_producto
            FROM inventarios i
            GROUP BY i.id_almacen, i.id_producto
        ) t
    ";

    // Consulta final (sin devoluciones)
    $sql = "
        SELECT
            base.id_almacen,
            a.almacen,
            base.id_producto,
            p.nombre AS producto,
            COALESCE(e.entradas,0) AS entradas,
            COALESCE(s.salidas,0)  AS salidas,
            0                      AS devoluciones,
            (COALESCE(e.entradas,0) - COALESCE(s.salidas,0)) AS esperado,
            COALESCE(inv.registrado,0)  AS registrado,
            ((COALESCE(e.entradas,0) - COALESCE(s.salidas,0)) - COALESCE(inv.registrado,0)) AS diferencia
        FROM ( $allKeys ) base
        LEFT JOIN ( $subEntradas ) e 
            ON e.id_almacen = base.id_almacen AND e.id_producto = base.id_producto
        LEFT JOIN ( $subSalidas ) s 
            ON s.id_almacen = base.id_almacen AND s.id_producto = base.id_producto
        LEFT JOIN ( $subInventario ) inv 
            ON inv.id_almacen = base.id_almacen AND inv.id_producto = base.id_producto
        LEFT JOIN cat_almacenes a ON a.id = base.id_almacen
        LEFT JOIN cat_productos p ON p.id_producto = base.id_producto
        WHERE 1=1
    ";

    if ($idAlmacen > 0) {
        $sql .= " AND base.id_almacen = $idAlmacen ";
    }
    if ($idProducto > 0) {
        $sql .= " AND base.id_producto = $idProducto ";
    }

    if (!$showMatches) {
        $sql .= " HAVING diferencia <> 0 ";
    }

    $sql .= " ORDER BY ABS(diferencia) DESC, a.almacen ASC, p.nombre ASC ";

    $rs = $clsConsulta->consultaGeneral($sql);

    $rows = [];
    if ($clsConsulta->numrows > 0) {
        foreach ($rs as $i => $r) {
            if ($i === 0) continue;
            $esperado   = (float)$r['esperado'];
            $registrado = (float)$r['registrado'];
            $rows[] = [
                'id_almacen'  => (int)$r['id_almacen'],
                'almacen'     => $r['almacen'] ?? '',
                'id_producto' => (int)$r['id_producto'],
                'producto'    => $r['producto'] ?? '',
                'entradas'    => (float)$r['entradas'],
                'salidas'     => (float)$r['salidas'],
                'devoluciones' => 0.0,
                'esperado'    => $esperado,
                'registrado'  => $registrado,
                'diferencia'  => $esperado - $registrado,
                'coincide'    => ($esperado === $registrado)
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
