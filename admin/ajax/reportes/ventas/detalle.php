<?php
// admin/ajax/reportes/ventas/detalle.php
require __DIR__ . '/../../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idVendedor = (int)($_POST['vendedor'] ?? 0);
$fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
$fecha_fin = trim($_POST['fecha_fin'] ?? '');

if ($idVendedor <= 0) {
    echo '<div class="alert alert-danger">Vendedor no válido.</div>';
    exit;
}

// filtro empresa si aplica
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);
$w = "r.id_vendedor = " . $clsConsulta->sanitizar($idVendedor, true, false);
if ($idEmpresa > 0) {
    $w .= " AND r.id_empresa = " . $clsConsulta->sanitizar($idEmpresa, true, false);
}
if ($fecha_inicio !== '') {
    $w .= " AND DATE(r.fecha) >= " . $clsConsulta->sanitizar($fecha_inicio, false, false);
}
if ($fecha_fin !== '') {
    $w .= " AND DATE(r.fecha) <= " . $clsConsulta->sanitizar($fecha_fin, false, false);
}

// Traer remisiones / ventas del vendedor (ejemplo básico; ajusta columnas según tu esquema)
$sql = "
  SELECT r.id, r.id_cliente, r.fecha, r.total AS total_remision, c.razon_social
  FROM cab_remisiones r
  LEFT JOIN cat_clientes c ON c.id = r.id_cliente
  WHERE {$w} AND IFNULL(r.estatus,'') <> 'cancelada'
  ORDER BY r.fecha DESC
  LIMIT 200
";
$rs = $clsConsulta->consultaGeneral($sql);

?>
<div class="modal-header">
  <h5 class="modal-title">Detalle de ventas - Vendedor #<?= $idVendedor ?></h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">
  <?php if ($clsConsulta->numrows > 0 && is_array($rs)): ?>
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-secondary">
          <tr>
            <th>Remisión</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($rs as $k => $row) {
              if (!is_array($row) || !isset($row['id'])) continue;
              $total = floatval($row['total_remision'] ?? 0);
              ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['fecha']) ?></td>
                <td><?= htmlspecialchars($row['razon_social'] ?? '') ?></td>
                <td class="text-end">$<?= number_format($total, 2) ?></td>
              </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No se encontraron ventas para este vendedor en el periodo seleccionado.</div>
  <?php endif; ?>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
  <a class="btn btn-danger" target="_blank" href="/admin/pdf/reportes-ventas-vendedor.php?vendedor=<?= $idVendedor ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>">Exportar PDF</a>
  <a class="btn btn-success" target="_blank" href="/admin/excel/reportes-ventas-vendedor.php?vendedor=<?= $idVendedor ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>">Exportar Excel</a>
</div>
