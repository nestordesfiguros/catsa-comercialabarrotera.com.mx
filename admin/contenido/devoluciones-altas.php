<?php
// contenido/devoluciones-altas.php
$clientes   = $clsConsulta->consultaGeneral("SELECT id, razon_social, nombre_comercial FROM cat_clientes ORDER BY razon_social");
$facturas   = $clsConsulta->consultaGeneral("SELECT folio FROM cab_facturas ORDER BY fecha DESC");
$remisiones = $clsConsulta->consultaGeneral("SELECT id FROM cab_remisiones ORDER BY fecha DESC");
$almacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes WHERE estatus = 1 ORDER BY almacen");
?>

<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
      <li class="breadcrumb-item"><a href="devoluciones">Devoluciones</a></li>
      <li class="breadcrumb-item active" aria-current="page">Nueva Devolución</li>
    </ol>
  </nav>
</div>

<div class="container mt-4">
  <h2>Capturar Devolución</h2>
  <form id="formDevolucion" autocomplete="off">

    <div class="row mb-3">
      <div class="col-md-2">
        <div class="form-check form-switch mt-4">
          <input class="form-check-input" type="checkbox" id="generar_nota_credito" name="generar_nota_credito" value="1">
          <label class="form-check-label" for="generar_nota_credito"><b>Generar Nota de Crédito</b></label>
        </div>
      </div>
      <div class="col-md-5">
        <label class="form-label">Almacén receptor</label>
        <select id="id_almacen" name="id_almacen" class="form-select" required>
          <option value="">-- Selecciona un almacén --</option>
          <?php foreach ($almacenes as $alm): ?>
            <option value="<?= $alm['id'] ?>"><?= htmlspecialchars($alm['almacen']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-5">
        <label class="form-label">Remisión origen</label>
        <div class="input-group">
          <input type="text" id="documento_numero" name="documento_numero" class="form-control" placeholder="Selecciona una remisión" readonly required>
          <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRemisiones">
            Buscar
          </button>
        </div>
      </div>

    </div>

    <input type="hidden" name="tipo_documento" id="tipo_documento" value="remision">
    <input type="hidden" name="id_cliente" id="id_cliente">

    <div class="row mb-3">
      <div class="col-md-12">
        <div class="border rounded p-3 bg-light" id="ficha_cliente" style="display:none;">
          <strong>Cliente:</strong>
          <div id="cliente_nombre" class="fw-bold text-primary mt-1"></div>
        </div>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-12">
        <label class="form-label">Observaciones</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
      </div>
    </div>

    <div class="alert alert-info small mb-3">
      Selecciona el tipo y número de documento. Podrás devolver solo algunos productos si lo deseas.<br>
      Modifica la <strong>cantidad</strong> si es menor a la original e ingresa un <strong>motivo</strong>.<br>
      También puedes <strong>eliminar filas</strong> si no deseas devolver ese producto. <br>
      <strong>Los productos que aparecen en esta lista son los que serán devueltos</strong>
    </div>

    <h5>Detalle de Productos</h5>
    <table class="table table-bordered" id="tablaDetalles">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Motivo</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <div id="alertaValidacion" class="text-danger small mb-3" style="display:none;">
      Debes ingresar al menos un producto con cantidad válida. Si la cantidad es menor a la original, el motivo es obligatorio.
    </div>

    <button type="submit" class="btn btn-primary" disabled>Guardar Devolución</button>
  </form>
</div>


<!-- Modal: Buscar remisión -->
<div class="modal fade" id="modalRemisiones" tabindex="-1" aria-labelledby="modalRemisionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRemisionesLabel">Seleccionar Remisión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <table id="tablaRemisiones" class="table table-striped" style="width:100%">
          <thead>
            <tr>
              <th>Folio</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver productos de la remisión -->
<div class="modal fade" id="modalVerProductosRemision" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow-lg border border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Productos de la Remisión</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <!-- Cabecera dinámica de remisión -->
        <div id="cabeceraRemision" class="mb-3 p-2 border rounded bg-light">
          <strong>Folio:</strong> <span id="cab_folio"></span> |
          <strong>Cliente:</strong> <span id="cab_cliente"></span> |
          <strong>Fecha:</strong> <span id="cab_fecha"></span> |
          <strong>Total:</strong> $<span id="cab_total"></span>
        </div>

        <table class="table table-striped table-sm" id="tablaProductosRemision">
          <thead class="table-secondary">
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<script>
  function validarProductos() {
    let alMenosUno = false;
    let todasValidas = true;

    $('#tablaDetalles tbody tr').each(function() {
      const fila = $(this);
      const cantidad = parseFloat(fila.find('input[name$="[cantidad]"]').val());
      const max = parseFloat(fila.find('input[name$="[cantidad]"]').data('cantidad-original')) || 0;
      const motivo = fila.find('input[name$="[motivo]"]').val().trim();

      if (isNaN(cantidad) || cantidad <= 0) return;
      if (cantidad > max || (cantidad < max && motivo.length === 0)) {
        todasValidas = false;
        return false;
      }
      alMenosUno = true;
    });

    const puedeGuardar = todasValidas && alMenosUno;
    $('button[type="submit"]').prop('disabled', !puedeGuardar);
    $('#alertaValidacion').toggle(!puedeGuardar);
  }

  // Cargar DataTable al abrir el modal
  $('#modalRemisiones').on('shown.bs.modal', function() {
    if (!$.fn.DataTable.isDataTable('#tablaRemisiones')) {
      $('#tablaRemisiones').DataTable({
        ajax: 'ajax/devoluciones/tabla-remisiones-modal.php',
        columns: [{
            data: 'folio'
          },
          {
            data: 'cliente'
          },
          {
            data: 'fecha'
          },
          {
            data: 'total'
          },
          {
            data: null,
            render: function(data, type, row) {
              return `
              <div class="btn-group">
                <button class="btn btn-sm btn-primary seleccionar-remision me-3"
                        data-id="${row.id}"
                        data-folio="${row.folio}">
                  SELECCIONAR
                </button>
                <button class="btn btn-sm btn-info ver-productos-remision"
                        data-id="${row.id}"
                        title="Ver productos de la remisión">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            `;
            }
          }
        ],
        language: {
          url: "assets/datatables/Spanish.json"
        }
      });
    }
  });



  // Seleccionar remisión desde la tabla
  $(document).on('click', '.seleccionar-remision', function() {
    const id = $(this).data('id');
    const folio = $(this).data('folio');

    // Asignar el ID (que se usa para buscar los productos)
    $('#documento_numero').val(id);

    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalRemisiones'));
    if (modal) {
      modal.hide();
    }

    // Disparar la carga de productos automáticamente
    $('#documento_numero').trigger('blur');
  });

  // ======================================
  // VER PRODUCTOS DE REMISIÓN (solo lectura)
  // ======================================
  $(document).on('click', '.ver-productos-remision', function() {
    const id = $(this).data('id');

    $('#tablaProductosRemision tbody').html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');
    const modal = new bootstrap.Modal(document.getElementById('modalVerProductosRemision'));
    modal.show();

    $.get('ajax/devoluciones/ver-productos-remision.php', {
      id_remision: id
    }, function(res) {
      const tbody = $('#tablaProductosRemision tbody');
      tbody.empty();

      if (res.success) {
        // Llenar cabecera
        $('#cab_folio').text(res.cabecera.folio);
        $('#cab_cliente').text(res.cabecera.cliente);
        $('#cab_fecha').text(res.cabecera.fecha);
        $('#cab_total').text(res.cabecera.total.toFixed(2));

        // Llenar productos
        if (res.productos.length > 0) {
          res.productos.forEach(p => {
            tbody.append(`
                        <tr>
                            <td>${p.nombre}</td>
                            <td>${p.cantidad}</td>
                            <td>${p.precio_unitario.toFixed(2)}</td>
                            <td>${(p.cantidad * p.precio_unitario).toFixed(2)}</td>
                        </tr>
                    `);
          });
        } else {
          tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin productos</td></tr>');
        }
      } else {
        tbody.html('<tr><td colspan="4" class="text-center text-danger">Error al cargar datos</td></tr>');
      }
    }, 'json').fail(function() {
      $('#tablaProductosRemision tbody').html('<tr><td colspan="4" class="text-center text-danger">Error de conexión</td></tr>');
    });
  });



  $('#tipo_documento').on('change', function() {
    const tipo = $(this).val();
    $('#documento_numero').val('');
    $('#cliente_nombre').text('');
    $('#ficha_cliente').hide();
    $('#id_cliente').val('');
    $('#tablaDetalles tbody').empty();

    if (tipo === 'factura') {
      $('#documento_numero').attr('list', 'datalist_facturas');
    } else if (tipo === 'remision') {
      $('#documento_numero').attr('list', 'datalist_remisiones');
    } else {
      $('#documento_numero').attr('list', '');
    }
  });

  $('#documento_numero').on('blur', function() {
    const tipo = $('#tipo_documento').val();
    const numero = $(this).val().trim();
    console.log('Tipo: ' + tipo + ' Número: ' + numero);
    if (!tipo || !numero) return;

    $.post('ajax/devoluciones/obtener-productos-documento.php', {
      tipo_documento: tipo,
      documento_numero: numero
    }, function(res) {
      $('#tablaDetalles tbody').empty();
      if (res.success) {
        $('#id_cliente').val(res.id_cliente);
        $('#cliente_nombre').text(res.cliente_nombre);
        $('#ficha_cliente').show();

        res.productos.forEach(p => {
          $('#tablaDetalles tbody').append(`
          <tr class="form-group">
            <td>
              <input type="hidden" name="detalles[][id_producto]" value="${p.id_producto}">
              <input class="form-control form-control-sm" value="${p.nombre}" readonly>
            </td>
            <td>
              <input type="number"
                     name="detalles[][cantidad]"
                     class="form-control form-control-sm cantidad-devuelta"
                     min="0"
                     max="${p.cantidad}"
                     data-cantidad-original="${p.cantidad}"
                     value="${p.cantidad}"
                     required>
            </td>
            <td>
              <input type="text" class="form-control form-control-sm" value="${p.precio_unitario}" readonly>
              <input type="hidden" name="detalles[][precio_unitario]" value="${p.precio_unitario}">
            </td>
            <td>
              <input type="text" name="detalles[][motivo]" class="form-control form-control-sm" maxlength="255">
              <div class="mensaje-motivo small text-muted mt-1">Motivo no requerido si la cantidad no se modifica.</div>
            </td>
            <td class="text-center align-middle">
              <button type="button" class="btn btn-sm btn-outline-danger eliminar-producto" title="Eliminar"><i class="fa fa-times"></i></button>
            </td>
          </tr>
        `);
        });
        validarProductos();
      } else {
        alertify.error(res.message || 'Documento no válido');
        $('#cliente_nombre').text('');
        $('#ficha_cliente').hide();
      }
    }, 'json');
  });

  $(document).on('input', 'input[name$="[cantidad]"], input[name$="[motivo]"], input[name$="[precio_unitario]"]', validarProductos);

  $(document).on('click', '.eliminar-producto', function() {
    $(this).closest('tr').remove();
    validarProductos();
  });

  $('#formDevolucion').on('submit', function(e) {
    e.preventDefault();

    const cabecera = {
      tipo_documento: $('#tipo_documento').val(),
      documento_numero: $('#documento_numero').val().trim(),
      id_cliente: $('#id_cliente').val(),
      observaciones: $('#observaciones').val().trim(),
      id_almacen: $('#id_almacen').val(),
      generar_nota_credito: $('#generar_nota_credito').is(':checked') ? 1 : 0
    };


    const detalles = [];
    $('#tablaDetalles tbody tr').each(function() {
      const fila = $(this);
      const cantidad = parseFloat(fila.find('input[name$="[cantidad]"]').val());
      if (!isNaN(cantidad) && cantidad > 0) {
        detalles.push({
          id_producto: fila.find('input[name$="[id_producto]"]').val(),
          cantidad: cantidad,
          motivo: fila.find('input[name$="[motivo]"]').val().trim(),
          precio_unitario: fila.find('input[name$="[precio_unitario]"]').val()
        });
      }
    });

    alertify.confirm('Confirmar', '¿Deseas guardar esta devolución?',
      function() {
        $.post('ajax/devoluciones/guardar.php', {
          cabecera: cabecera,
          detalles: detalles
        }, function(resp) {
          if (resp.success) {
            alertify.success(resp.message);
            window.location.href = 'devoluciones';
          } else {
            alertify.error(resp.message || 'Error al guardar');
          }
        }, 'json');
      },
      function() {
        alertify.error('Cancelado');
      }
    ).set('labels', {
      ok: 'Sí',
      cancel: 'No'
    });
  });
</script>