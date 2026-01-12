<?php
// ajax/inventario/auditoria-detalle.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
$db = $clsConsulta->getConexion();

$id_producto = intval($_POST['id_producto'] ?? 0);
$id_almacen  = intval($_POST['id_almacen'] ?? 0);

if (!$id_producto || !$id_almacen) {
  echo '<div class="alert alert-warning">Parámetros inválidos.</div>';
  exit;
}

/* Producto */
$sqlProd = "
  SELECT cp.id_producto, cp.clave AS sku,
         COALESCE(cp.nombre, cp.descripcion, CONCAT('Producto ', cp.id_producto)) AS producto
  FROM cat_productos cp
  WHERE cp.id_producto = $id_producto
";
$rProd = $clsConsulta->consultaGeneral($sqlProd);
$sku     = $rProd[1]['sku']      ?? '';
$nomProd = $rProd[1]['producto'] ?? '';

/* Inventario teórico */
$rTeo = $clsConsulta->consultaGeneral("
  SELECT COALESCE(cantidad,0) AS stock_teorico
  FROM inventarios
  WHERE id_producto=$id_producto AND id_almacen=$id_almacen
");
$teorico = (float)($rTeo[1]['stock_teorico'] ?? 0);

/* Último conteo (si existe) */
$rC = $clsConsulta->consultaGeneral("
  SELECT cantidad, aplicado, fecha_conteo, responsable, observaciones
  FROM inv_conteos
  WHERE id_producto=$id_producto AND id_almacen=$id_almacen
  ORDER BY fecha_conteo DESC LIMIT 1
");
$tieneConteo = ($clsConsulta->numrows > 0);
$contado     = $tieneConteo ? (float)$rC[1]['cantidad'] : null;
$aplicado    = $tieneConteo ? intval($rC[1]['aplicado']) : 0;
$resp        = $tieneConteo ? ($rC[1]['responsable'] ?? '') : '';
$obs         = $tieneConteo ? ($rC[1]['observaciones'] ?? '') : '';
$fch         = $tieneConteo ? ($rC[1]['fecha_conteo'] ?? '') : '';

/* Sumas por tipo (impacto real inventario) + diagnóstico */
$rS = $clsConsulta->consultaGeneral("
  SELECT
    (SELECT COALESCE(SUM(me.cantidad),0)
       FROM mov_entradas_almacen me
       JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra
      WHERE me.id_producto=$id_producto AND ce.id_almacen=$id_almacen AND ce.estatus='procesada') AS sum_ent,
    (SELECT COALESCE(SUM(ms.cantidad),0)
       FROM mov_salidas_almacen ms
       JOIN cab_salidas_almacen cs ON cs.id=ms.id_salida
      WHERE ms.id_producto=$id_producto AND cs.id_almacen=$id_almacen AND cs.estatus='procesada') AS sum_sal,
    (SELECT COALESCE(SUM(mt.cantidad),0)
       FROM mov_traspasos_almacen mt
       JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
      WHERE mt.id_producto=$id_producto AND ct.id_almacen_destino=$id_almacen AND ct.estatus='activo') AS sum_tent,
    (SELECT COALESCE(SUM(mt2.cantidad),0)
       FROM mov_traspasos_almacen mt2
       JOIN cab_traspasos_almacen ct2 ON ct2.id_traspaso=mt2.id_traspaso
      WHERE mt2.id_producto=$id_producto AND ct2.id_almacen_origen=$id_almacen AND ct2.estatus='activo') AS sum_tsal,
    (SELECT COALESCE(SUM(mr.cantidad),0)
       FROM mov_remisiones mr
       JOIN cab_remisiones cr ON cr.id=mr.id_remision
      WHERE mr.id_producto=$id_producto AND cr.id_almacen=$id_almacen AND cr.estatus='procesada') AS sum_rem,
    (SELECT COALESCE(SUM(me2.cantidad),0)
       FROM mov_entradas_almacen me2
       JOIN cab_entradas_almacen ce2 ON ce2.id=me2.id_orden_compra
      WHERE me2.id_producto=$id_producto AND ce2.id_almacen=$id_almacen) AS sum_oc
");
$sum_ent  = (float)$rS[1]['sum_ent'];
$sum_sal  = (float)$rS[1]['sum_sal'];
$sum_tent = (float)$rS[1]['sum_tent'];
$sum_tsal = (float)$rS[1]['sum_tsal'];
$sum_rem  = (float)$rS[1]['sum_rem'];
$sum_oc   = (float)$rS[1]['sum_oc'];

/* Kardex SIN remisiones */
$kardex = $sum_ent + $sum_tent - $sum_sal - $sum_tsal;

/* Diferencias */
$dif_conteo = $tieneConteo ? ($contado - $teorico) : null;
$dif_kardex = $kardex - $teorico;

/* Diagnóstico */
$salidas_faltantes  = max(0, $sum_rem - $sum_sal); // remisiones sin su salida
$entradas_faltantes = max(0, $sum_oc  - $sum_ent); // compras/OC sin su entrada

/* Historial (últimos 50) */
$sqlMov = "
  (
    SELECT DATE_FORMAT(ce.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'ENTRADA' tipo, CONCAT('OC ',ce.id) referencia, me.cantidad, ce.id_usuario usuario
    FROM mov_entradas_almacen me JOIN cab_entradas_almacen ce ON ce.id=me.id_orden_compra
    WHERE me.id_producto=$id_producto AND ce.id_almacen=$id_almacen AND ce.estatus='procesada'
  )
  UNION ALL
  (
    SELECT DATE_FORMAT(cs.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'SALIDA' tipo, CONCAT('SAL ',cs.id) referencia, ms.cantidad, cs.id_usuario usuario
    FROM mov_salidas_almacen ms JOIN cab_salidas_almacen cs ON cs.id=ms.id_salida
    WHERE ms.id_producto=$id_producto AND cs.id_almacen=$id_almacen AND cs.estatus='procesada'
  )
  UNION ALL
  (
    SELECT DATE_FORMAT(ct.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'TRASPASO_SALIDA' tipo, CONCAT('TR ',ct.id_traspaso) referencia, mt.cantidad, ct.id_usuario usuario
    FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
    WHERE mt.id_producto=$id_producto AND ct.id_almacen_origen=$id_almacen AND ct.estatus='activo'
  )
  UNION ALL
  (
    SELECT DATE_FORMAT(ct.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'TRASPASO_ENTRADA' tipo, CONCAT('TR ',ct.id_traspaso) referencia, mt.cantidad, ct.id_usuario usuario
    FROM mov_traspasos_almacen mt JOIN cab_traspasos_almacen ct ON ct.id_traspaso=mt.id_traspaso
    WHERE mt.id_producto=$id_producto AND ct.id_almacen_destino=$id_almacen AND ct.estatus='activo'
  )
  /* Remisiones solo para ver (diagnóstico) */
  UNION ALL
  (
    SELECT DATE_FORMAT(cr.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'REMISION' tipo, CONCAT('REM ',cr.id) referencia, mr.cantidad, cr.id_usuario usuario
    FROM mov_remisiones mr JOIN cab_remisiones cr ON cr.id=mr.id_remision
    WHERE mr.id_producto=$id_producto AND cr.id_almacen=$id_almacen AND cr.estatus='procesada'
  )
  ORDER BY fecha_mov DESC
  LIMIT 50
";
$rMov = $clsConsulta->consultaGeneral($sqlMov);
$numMov = $clsConsulta->numrows;
?>
<div class="mb-2">
  <?php if ($sku || $nomProd): ?>
    <div class="mb-1"><strong>Producto:</strong> <?= htmlspecialchars($sku) ?> — <?= htmlspecialchars($nomProd) ?></div>
  <?php endif; ?>

  <div class="row g-2">
    <div class="col-md-3"><strong>Inventario (teórico):</strong> <?= number_format($teorico, 4, '.', '') ?></div>
    <div class="col-md-3"><strong>Kardex (movimientos):</strong> <?= number_format($kardex, 4, '.', '') ?></div>
    <div class="col-md-3"><strong>Contado:</strong> <?= $tieneConteo ? number_format($contado, 4, '.', '') : '—' ?></div>
    <div class="col-md-3"><strong>Conteo aplicado:</strong> <?= $tieneConteo ? ($aplicado ? 'Sí' : 'No') : '—' ?></div>
  </div>

  <div class="row g-2 mt-2">
    <div class="col-md-4"><strong>Dif. por conteo:</strong> <?= $tieneConteo ? number_format($dif_conteo, 4, '.', '') : '—' ?></div>
    <div class="col-md-4"><strong>Dif. por movimientos:</strong> <?= number_format($dif_kardex, 4, '.', '') ?></div>
    <div class="col-md-4"><strong>Fecha conteo:</strong> <?= $tieneConteo ? htmlspecialchars($fch) : '—' ?></div>
  </div>

  <hr class="my-2">
  <h6 class="bg-info text-white p-1">Diagnóstico</h6>
  <div class="row g-2">
    <div class="col-md-3"><small>Entradas (almacén): <b><?= number_format($sum_ent, 4, '.', '') ?></b></small></div>
    <div class="col-md-3"><small>Salidas (almacén): <b><?= number_format($sum_sal, 4, '.', '') ?></b></small></div>
    <div class="col-md-3"><small>Trasp. entrada: <b><?= number_format($sum_tent, 4, '.', '') ?></b></small></div>
    <div class="col-md-3"><small>Trasp. salida: <b><?= number_format($sum_tsal, 4, '.', '') ?></b></small></div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-md-4"><small>Remisiones procesadas (diag.): <b><?= number_format($sum_rem, 4, '.', '') ?></b></small></div>
    <div class="col-md-4"><small>OC/Compras (diag.): <b><?= number_format($sum_oc, 4, '.', '') ?></b></small></div>
    <div class="col-md-4">
      <small><span class="<?= $salidas_faltantes > 0 ? 'text-danger' : 'text-success' ?>">Salidas faltantes por remisiones: <b><?= number_format($salidas_faltantes, 4, '.', '') ?></b></span></small><br>
      <small><span class="<?= $entradas_faltantes > 0 ? 'text-danger' : 'text-success' ?>">Entradas faltantes por compras: <b><?= number_format($entradas_faltantes, 4, '.', '') ?></b></span></small>
    </div>
  </div>
</div>

<h6>Últimos movimientos</h6>
<div class="table-responsive">
  <table class="table table-sm table-striped">
    <thead class="table-info">
      <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Referencia</th>
        <th class="text-end">Cantidad</th>
        <th>Usuario</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($numMov > 0): for ($i = 1; $i <= $numMov; $i++): $m = $rMov[$i]; ?>
          <tr>
            <td><?= htmlspecialchars($m['fecha_mov']) ?></td>
            <td><?= htmlspecialchars($m['tipo']) ?></td>
            <td><?= htmlspecialchars($m['referencia']) ?></td>
            <td class="text-end"><?= number_format((float)$m['cantidad'], 4, '.', '') ?></td>
            <td><?= htmlspecialchars($m['usuario']) ?></td>
          </tr>
        <?php endfor;
      else: ?>
        <tr>
          <td colspan="5" class="text-center text-muted">Sin movimientos recientes.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>