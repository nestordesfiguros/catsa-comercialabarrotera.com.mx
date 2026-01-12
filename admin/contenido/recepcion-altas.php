<?php
$cat = $cat ?? null;
$subcat = $subcat ?? null;

$entrada = $clsConsulta->presentaDetalle('cab_entradas_almacen', $cat);
$entrada = $entrada[1] ?? [];

if (!$entrada) {
  echo '<div class="alert alert-danger">Entrada no encontrada</div>';
  return;
}

$productos = $clsConsulta->consultaGeneral("SELECT cp.id_producto, cp.nombre FROM cat_productos cp WHERE cp.estatus = 1");
$listaProductos = [];
foreach ($productos as $p) {
  $listaProductos[$p['id_producto']] = $p['nombre'];
}

$orden = $entrada['id_odc'] ?? 0;
$detalleOC = $clsConsulta->presentaDetalleid('mov_compras', 'id_orden_compra', $orden);

?>
<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="inicio">inicio</a></li>
      <li class="breadcrumb-item"><a href="recepcion">Recepciones</a></li>
      <li class="breadcrumb-item active" aria-current="page">Procesar entrada</li>
    </ol>
  </nav>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recepción de mercancía</h3>
          </div>
          <div class="card-body">
            <form id="formRecepcionODC">
              <input type="hidden" name="id_entrada" value="<?= $entrada['id'] ?>">
              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Producto</th>
                      <th>Solicitado</th>
                      <th>Recibido</th>
                      <th>Diferencia</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($detalleOC as $d) {
                      $nombre = $listaProductos[$d['id_producto']] ?? 'Producto #' . $d['id_producto'];
                      echo "<tr>
                              <td>$nombre</td>
                              <td class='solicitado' data-prod='{$d['id_producto']}'> {$d['cantidad']} </td>
                              <td><input type='number' name='recibido[{$d['id_producto']}]' min='0' class='form-control form-control-sm recibido' data-prod='{$d['id_producto']}' value='{$d['cantidad']}'></td>
                              <td class='diferencia' data-prod='{$d['id_producto']}'>0</td>
                            </tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <hr>
              <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$('.recibido').on('input', function () {
  const prod = $(this).data('prod');
  const solicitado = parseInt($(`.solicitado[data-prod='${prod}']`).text());
  const recibido = parseInt($(this).val()) || 0;
  const diferencia = recibido - solicitado;
  const $dif = $(`.diferencia[data-prod='${prod}']`);
  $dif.text(diferencia);

  if (recibido <= 0) {
    $(this).addClass('is-invalid');
    $dif.text('Inválido').css('color', 'red');
    return;
  }

  if (diferencia > 0) {
    $(this).addClass('is-invalid');
    $dif.css('color', 'red');
  } else if (diferencia < 0) {
    $(this).addClass('is-warning');
    $dif.css('color', 'orange');
  } else {
    $(this).removeClass('is-invalid is-warning');
    $dif.css('color', '');
  }
});

$('#formRecepcionODC').on('submit', function(e) {
  e.preventDefault();

  let tieneError = false;
  let tieneDiferencia = false;
  $('.recibido').each(function () {
    const val = parseInt($(this).val()) || 0;
    const solicitado = parseInt($(`.solicitado[data-prod='${$(this).data('prod')}']`).text());
    if (val <= 0) tieneError = true;
    if (val !== solicitado) tieneDiferencia = true;
  });

  if (tieneError) {
    alertify.error('Todas las cantidades deben ser mayores a cero');
    return;
  }

  if (tieneDiferencia) {
    alertify.confirm('Diferencia detectada', 'Existen diferencias entre lo solicitado y lo recibido. ¿Deseas continuar?',
      function () {
        enviarRecepcion();
      },
      function () {
        alertify.error('Operación cancelada');
      });
  } else {
    enviarRecepcion();
  }
});

function enviarRecepcion() {
  const data = $('#formRecepcionODC').serialize();
  $.post('ajax/recepcion/guardar.php', data, function(resp) {
    if (resp.success) {
      alertify.success('Entrada procesada');
      location.href = 'recepcion';
    } else {
      alertify.error(resp.message || 'Error al guardar');
    }
  }, 'json');
}
</script>
