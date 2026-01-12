<?php
// ajax/devoluciones/detalle.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  echo '<div class="alert alert-danger">ID no válido.</div>';
  exit;
}

/* ===== CABECERA ===== */
$sqlCab = "
  SELECT d.*, c.razon_social AS cliente
  FROM cab_devoluciones d
  LEFT JOIN cat_clientes c ON d.id_cliente = c.id
  WHERE d.id_devolucion = $id
";
$datosCab = $clsConsulta->consultaGeneral($sqlCab);
$numCab   = $clsConsulta->numrows;

if (!$datosCab || $numCab < 1 || !isset($datosCab[1])) {
  echo '<div class="alert alert-warning">No se encontró la devolución.</div>';
  exit;
}
$cab = $datosCab[1];

/* ===== DETALLE ===== */
$sqlDet = "
  SELECT m.*, p.nombre
  FROM mov_devoluciones m
  LEFT JOIN cat_productos p ON m.id_producto = p.id_producto
  WHERE m.id_devolucion = $id
";

$datosDet = $clsConsulta->consultaGeneral($sqlDet);
$numDet   = $clsConsulta->numrows;
?>
<div class="modal-header">
  <h5 class="modal-title">Detalle de Devolución #<?= (int)$cab['id_devolucion'] ?></h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">
  <p><strong>Cliente:</strong> <?= htmlspecialchars($cab['cliente']) ?></p>
  <p><strong>Tipo Documento:</strong> <?= strtoupper($cab['tipo_documento']) ?> <?= htmlspecialchars($cab['id_documento']) ?></p>
  <p><strong>Observaciones:</strong> <?= nl2br(htmlspecialchars($cab['observaciones'])) ?></p>
  <p><strong>Fecha:</strong> <?= htmlspecialchars($cab['fecha']) ?></p>

  <table class="table table-sm table-bordered table-striped mt-3">
    <thead class="table-secondary">
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Motivo</th>
        <th>Precio Unitario</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $mostro = false;
      if (is_array($datosDet) && $numDet > 0) {
        foreach ($datosDet as $k => $det) {
          // Algunos wrappers devuelven en [0] metadatos: se filtra por ser array con campos esperados
          if (!is_array($det) || !isset($det['id_producto'])) continue;

          $mostro = true;
          $nombre  = isset($det['nombre']) ? $det['nombre'] : '';
          $cant    = isset($det['cantidad']) ? (float)$det['cantidad'] : 0;
          $motivo  = isset($det['motivo']) ? $det['motivo'] : '';
          $precioU = isset($det['precio_unitario']) ? (float)$det['precio_unitario'] : 0.0;
      ?>
          <tr>
            <td><?= htmlspecialchars($nombre) ?></td>
            <td><?= $cant ?></td>
            <td><?= htmlspecialchars($motivo) ?></td>
            <td>$<?= number_format($precioU, 2) ?></td>
          </tr>
      <?php
        }
      }

      if (!$mostro) {
        echo '<tr><td colspan="4" class="text-center text-muted">Sin partidas para esta devolución.</td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>
<div class="modal-footer">
  <a href="pdf/devolucion-generar.php?id=<?= (int)$cab['id_devolucion'] ?>&idc=<?= (int)$cab['id_cliente'] ?>" target="_blank" class="btn btn-outline-danger">
    <i class="fa fa-file-pdf-o"></i> Descargar PDF
  </a>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>