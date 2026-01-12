<?php
// ajax/inventario/auditoria-historial.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_producto = intval($_POST['id_producto'] ?? 0);
$id_almacen  = intval($_POST['id_almacen'] ?? 0);
if (!$id_producto || !$id_almacen) {
  echo '<div class="alert alert-warning">Parámetros inválidos.</div>';
  exit;
}

$sql = "
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
  /* Remisiones solo informativas */
  UNION ALL
  (
    SELECT DATE_FORMAT(cr.fecha,'%Y-%m-%d %H:%i:%s') fecha_mov,'REMISION' tipo, CONCAT('REM ',cr.id) referencia, mr.cantidad, cr.id_usuario usuario
    FROM mov_remisiones mr JOIN cab_remisiones cr ON cr.id=mr.id_remision
    WHERE mr.id_producto=$id_producto AND cr.id_almacen=$id_almacen AND cr.estatus='procesada'
  )
  ORDER BY fecha_mov DESC
  LIMIT 200
";
$r = $clsConsulta->consultaGeneral($sql);
?>
<div class="table-responsive">
  <table class="table table-sm">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Referencia</th>
        <th class="text-end">Cantidad</th>
        <th>Usuario</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($clsConsulta->numrows > 0): for ($i = 1; $i <= $clsConsulta->numrows; $i++): $m = $r[$i]; ?>
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
          <td colspan="5" class="text-center text-muted">Sin historial.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>