<?php
// ajax/inventario/actualizar.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/*
  Entrada esperada por almacén/producto:
    Esperado = Entradas + Devoluciones + Traspasos_entrantes
               - Remisiones - Traspasos_salientes
*/

$raw   = $_POST['items'] ?? '[]';
$items = json_decode($raw, true);
if (!is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'Formato inválido de items']);
    exit;
}

$updated = [];
$errors  = [];

// Helpers
function qf($v)
{
    return is_numeric($v) ? $v : 0;
}

foreach ($items as $idx => $it) {
    $id_almacen  = (int)($it['id_almacen']  ?? 0);
    $id_producto = (int)($it['id_producto'] ?? 0);

    if ($id_almacen <= 0 || $id_producto <= 0) {
        $errors[] = ['index' => $idx, 'message' => 'id_almacen / id_producto inválido'];
        continue;
    }

    try {
        // ---- 1) Calcular movimientos consolidados para este almacén / producto
        // ENTRADAS (cab_entradas_almacen + mov_entradas_almacen)
        $sqlEntradas = "
      SELECT COALESCE(SUM(m.cantidad),0) AS entradas
      FROM mov_entradas_almacen m
      INNER JOIN cab_entradas_almacen c
              ON c.id = m.id_orden_compra
             AND c.estatus = 'procesada'
      WHERE c.id_almacen = $id_almacen
        AND m.id_producto = $id_producto
    ";
        $rE = $clsConsulta->consultaGeneral($sqlEntradas);
        $entradas = qf($rE[1]['entradas'] ?? 0);

        // SALIDAS (cab_remisiones + mov_remisiones)
        $sqlSalidas = "
      SELECT COALESCE(SUM(m.cantidad),0) AS salidas
      FROM mov_remisiones m
      INNER JOIN cab_remisiones r
              ON r.id = m.id_remision
             AND r.estatus = 'procesada'
      WHERE r.id_almacen = $id_almacen
        AND m.id_producto = $id_producto
    ";
        $rS = $clsConsulta->consultaGeneral($sqlSalidas);
        $salidas = qf($rS[1]['salidas'] ?? 0);

        // TRASPASOS ENTRANTES (cab_traspasos_almacen + mov_traspasos_almacen)
        $sqlTin = "
      SELECT COALESCE(SUM(t.cantidad),0) AS trasp_in
      FROM mov_traspasos_almacen t
      INNER JOIN cab_traspasos_almacen c
              ON c.id_traspaso = t.id_traspaso
             AND c.estatus = 'activo'
      WHERE c.id_almacen_destino = $id_almacen
        AND t.id_producto = $id_producto
    ";
        $rTin = $clsConsulta->consultaGeneral($sqlTin);
        $trasp_in = qf($rTin[1]['trasp_in'] ?? 0);

        // TRASPASOS SALIENTES
        $sqlTout = "
      SELECT COALESCE(SUM(t.cantidad),0) AS trasp_out
      FROM mov_traspasos_almacen t
      INNER JOIN cab_traspasos_almacen c
              ON c.id_traspaso = t.id_traspaso
             AND c.estatus = 'activo'
      WHERE c.id_almacen_origen = $id_almacen
        AND t.id_producto = $id_producto
    ";
        $rTout = $clsConsulta->consultaGeneral($sqlTout);
        $trasp_out = qf($rTout[1]['trasp_out'] ?? 0);

        // DEVOLUCIONES (solo si cab_devoluciones tiene id_almacen)
        // Si tu tabla no tiene id_almacen, esto quedará en 0 y no afectará.
        $sqlDev = "
      SELECT COALESCE(SUM(m.cantidad),0) AS devoluciones
      FROM mov_devoluciones m
      INNER JOIN cab_devoluciones d
              ON d.id_devolucion = m.id_devolucion
             /* Quita la siguiente línea si tu cab_devoluciones NO tiene id_almacen */
             AND d.id_almacen = $id_almacen
      WHERE m.id_producto = $id_producto
        AND (d.estatus IS NULL OR d.estatus <> 'cancelada')
    ";
        $rDev = $clsConsulta->consultaGeneral($sqlDev);
        $devoluciones = qf($rDev[1]['devoluciones'] ?? 0);

        // Esperado = Entradas + Devoluciones + Traspasos_in - Salidas - Traspasos_out
        $esperado = ($entradas + $devoluciones + $trasp_in) - ($salidas + $trasp_out);

        // ---- 2) Upsert a inventarios (id_almacen, id_producto)
        $sqlExist = "
      SELECT id FROM inventarios
      WHERE id_almacen = $id_almacen AND id_producto = $id_producto
      LIMIT 1
    ";
        $rsExist = $clsConsulta->consultaGeneral($sqlExist);
        $existe = ($clsConsulta->numrows > 0);

        if ($existe) {
            $sqlUpd = "
        UPDATE inventarios
           SET cantidad = $esperado
         WHERE id_almacen = $id_almacen AND id_producto = $id_producto
      ";
            $ok = $clsConsulta->aplicaQuery($sqlUpd);
            if (!$ok) {
                $errors[] = ['index' => $idx, 'message' => 'Error al actualizar inventarios'];
                continue;
            }
        } else {
            $sqlIns = "
        INSERT INTO inventarios (id_almacen, id_producto, cantidad)
        VALUES ($id_almacen, $id_producto, $esperado)
      ";
            $ok = $clsConsulta->aplicaQuery($sqlIns);
            if (!$ok) {
                $errors[] = ['index' => $idx, 'message' => 'No se pudo insertar en inventarios'];
                continue;
            }
        }

        // ---- 3) Recalcular cantidad_disponible global del producto
        $sqlSum = "
      SELECT COALESCE(SUM(cantidad),0) AS total
      FROM inventarios
      WHERE id_producto = $id_producto
    ";
        $rsSum = $clsConsulta->consultaGeneral($sqlSum);
        $totalProd = qf($rsSum[1]['total'] ?? 0);

        $sqlProd = "
      UPDATE cat_productos
         SET cantidad_disponible = $totalProd
       WHERE id_producto = $id_producto
    ";
        $okProd = $clsConsulta->aplicaQuery($sqlProd);
        if (!$okProd) {
            $errors[] = ['index' => $idx, 'message' => 'No se pudo actualizar cat_productos'];
            continue;
        }

        $updated[] = [
            'id_almacen'   => $id_almacen,
            'id_producto'  => $id_producto,
            'esperado'     => $esperado,
            'total_global' => $totalProd
        ];
    } catch (Throwable $e) {
        $errors[] = ['index' => $idx, 'message' => $e->getMessage()];
    }
}

echo json_encode([
    'success' => count($errors) === 0,
    'updated' => $updated,
    'errors'  => $errors
], JSON_UNESCAPED_UNICODE);
