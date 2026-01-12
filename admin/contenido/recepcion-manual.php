<?php
$nav = 'recepcion';
$cat = $cat ?? null;
$subcat = $subcat ?? null;

// Solo se muestra formulario de alta manual de entrada
?>
<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="recepcion">Inicio</a></li>
      <li class="breadcrumb-item active" aria-current="page"> Alta manual</li>
    </ol>
  </nav>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Registrar entrada sin ODC</h3>
          </div>
          <div class="card-body">
            <form id="formRecepcionManual">
              <div class="row mb-3">
                <div class="col-md-4">
                  <label>Fecha de entrada</label>
                  <input type="date" name="fecha" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label>Proveedor</label>
                  <select name="id_proveedor" class="form-select" required>
                    <option value="" disabled selected>Selecciona proveedor</option>
                    <?php
                    $proveedores = $clsConsulta->consultaGeneral("SELECT id, razon_social FROM cat_proveedores WHERE estatus = 1");
                    foreach ($proveedores as $prov) {
                      echo "<option value='{$prov['id']}'>{$prov['razon_social']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label>Almacén</label>
                  <select name="id_almacen" class="form-select" required>
                    <option value="" disabled selected>Selecciona almacén</option>
                    <?php
                    $almacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes WHERE estatus = 1");
                    foreach ($almacenes as $alm) {
                      echo "<option value='{$alm['id']}'>{$alm['almacen']}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label>Observaciones</label>
                  <textarea name="observaciones" class="form-control" rows="2"></textarea>
                </div>
              </div>
              <hr>
              <h5>Productos a ingresar</h5>
              <div id="productosContainer"></div>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarProducto()">
                <i class="fa fa-plus"></i> Agregar producto
              </button>
              <hr>
              <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar entrada</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function agregarProducto() {
  const cont = document.getElementById('productosContainer');
  const index = cont.children.length;
  const html = `
    <div class="row mb-2 producto-item">
      <div class="col-md-6">
        <select name="productos[${index}][id_producto]" class="form-select" required>
          <option value="">Selecciona producto</option>
          <?php
          $productos = $clsConsulta->consultaGeneral("SELECT id_producto, nombre FROM cat_productos WHERE estatus = 1");
          foreach ($productos as $prod) {
            echo "<option value='{$prod['id_producto']}'>{$prod['nombre']}</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-md-4">
        <input type="number" name="productos[${index}][cantidad]" class="form-control" min="1" placeholder="Cantidad" required>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.producto-item').remove()">
          <i class="fa fa-trash"></i>
        </button>
      </div>
    </div>
  `;
  cont.insertAdjacentHTML('beforeend', html);
}

$('#formRecepcionManual').on('submit', function(e) {
  e.preventDefault();
  const formData = $(this).serialize();
  $.post('ajax/recepcion/guardar-manual.php', formData, function(resp) {
    if (resp.success) {
      alertify.success('Entrada registrada correctamente');
      location.href = 'recepcion';
    } else {
      alertify.error(resp.message || 'Error al guardar');
    }
  }, 'json');
});
</script>
