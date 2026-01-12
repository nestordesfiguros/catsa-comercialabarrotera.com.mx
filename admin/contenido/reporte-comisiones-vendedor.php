<?php
//$idEmpresa = $_SESSION['id_empresa'];
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('monday this week'));
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime($fechaInicio . ' +6 days'));
?>
<div class="ms-4 me-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
      <!-- <li class="breadcrumb-item"><a href="comisiones">Comisiones</a></li> -->
      <li class="breadcrumb-item active">Reporte por Vendedor</li>
    </ol>
  </nav>

  <div class="row mb-3 align-items-end">
    <div class="col-md-2">
      <label>Fecha inicio</label>
      <input type="date" id="fecha_inicio" value="<?= $fechaInicio ?>" class="form-control">
    </div>
    <div class="col-md-2">
      <label>Fecha fin</label>
      <input type="date" id="fecha_fin" value="<?= $fechaFin ?>" class="form-control">
    </div>
    <div class="col-md-2">
      <label>Estatus comisión</label>
      <select id="estatus_comision" class="form-select">
        <option value="pendiente">Pendiente</option>
        <option value="pagado">Pagada</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary" id="btnBuscar">
        <i class="fas fa-search me-1"></i> Buscar
      </button>
    </div>
    <div class="col-md-3 text-center d-flex justify-content-end">
      <button id="btnExportExcel" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Exportar a Excel
      </button>
    </div>
  </div>



  <div class="table-responsive">
    <table class="table table-bordered" id="tablaComisionesVendedor">
      <thead>
        <tr>
          <th>Vendedor</th>
          <th>Total Remisiones</th>
          <th>Total Comisión</th>
          <th>Estatus</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal: Detalle de remisiones -->
<div class="modal fade" id="modalDetalleComisiones" tabindex="-1" aria-labelledby="modalDetalleComisionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalDetalleComisionesLabel">
          <i class="fas fa-list me-2"></i>Detalle de Remisiones del Vendedor
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="tablaDetalleRemisiones">
            <thead>
              <tr>
                <th>Folio CxC</th>
                <th>Folio Remisión</th>
                <th>Monto Venta</th>
                <th>% Comisión</th>
                <th>Total Comisión</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Confirmar pago -->
<div class="modal fade" id="modalPagarComision" tabindex="-1" aria-labelledby="modalPagarComisionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalPagarComisionLabel">
          <i class="fas fa-check-circle me-2"></i>Confirmar Pago de Comisión
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_vendedor_pagar">
        <p class="mb-3">¿Estás seguro que deseas marcar como <strong>Pagadas</strong> todas las remisiones de este vendedor en el periodo seleccionado?</p>
        <div class="d-grid">
          <button class="btn btn-success" onclick="confirmarPagoComision()">Sí, Marcar como Pagado</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function pagarRemision(id) {
    alertify.confirm("¿Estás seguro de marcar como pagada esta comisión?",
      function() {
        fetch('ajax/comisiones/pagar-remision.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === 'OK') {
              alertify.success('Comisión pagada correctamente');
              location.reload();
            } else {
              alertify.error(res);
            }
          });
      },
      function() {
        alertify.message('Cancelado');
      });
  }



  function marcarPagado(idVendedor) {
    document.getElementById('id_vendedor_pagar').value = idVendedor;
    $('#modalPagarComision').modal('show');
  }

  function confirmarPagoComision() {
    const idVendedor = document.getElementById('id_vendedor_pagar').value;
    const fecha_ini = document.getElementById('fecha_inicio').value;
    const fecha_fin = document.getElementById('fecha_fin').value;

    document.getElementById('spinner').style.display = 'block';

    fetch('ajax/comisiones/guardar-corte.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id_vendedor=${idVendedor}&inicio=${fecha_ini}&fin=${fecha_fin}`
      })
      .then(res => res.text())
      .then(res => {
        document.getElementById('spinner').style.display = 'none';
        if (res === 'ok') {
          alertify.success('Comisión marcada como pagada');
          $('#modalPagarComision').modal('hide');
          $('#tablaComisionesVendedor').DataTable().ajax.reload();
        } else {
          alertify.error('Error: ' + res);
        }
      });
  }

  function cargarComisiones() {
    const fecha_inicio = $('#fecha_inicio').val();
    const fecha_fin = $('#fecha_fin').val();
    const estatus = $('#estatus_comision').val();

    $('#tablaComisionesVendedor').DataTable().destroy();
    $('#tablaComisionesVendedor').DataTable({
      ajax: {
        url: 'ajax/comisiones/tabla-comisiones-vendedor.php',
        type: 'GET',
        data: {
          fecha_inicio,
          fecha_fin,
          estatus
        }
      },
      processing: true,
      serverSide: true,
      responsive: true,
      dom: "<'row'<'col-sm-12 text-center'>>" +
        "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-6'i><'col-sm-6'p>>",
      buttons: [{
        extend: 'excel',
        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
        className: 'btn btn-success',
        title: 'Comisiones por Vendedor'
      }],

      columns: [{
          data: 'vendedor'
        },
        {
          data: 'total_remisiones'
        },
        {
          data: 'total_comision'
        },
        {
          data: 'estatus'
        }, // ← Nueva columna
        {
          data: 'acciones',
          orderable: false,
          searchable: false
        }
      ],

      language: {
        url: "assets/datatables/Spanish.json",
        sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
      }
    });
  }

  $(document).ready(function() {
    cargarComisiones();
    $('#btnBuscar').click(cargarComisiones);

    $('#btnExportExcel').on('click', function() {
      $('#tablaComisionesVendedor').DataTable().button('.buttons-excel').trigger();
    });

  });
</script>