<?php
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$idUsuario = $_SESSION['id_user'] ?? 0;

$id_vendedor = $cat ?? 0;
$fecha_inicio = $subcat ?? date('Y-m-d', strtotime('monday this week'));
$fecha_fin = $nivel3 ?? date('Y-m-d', strtotime($fecha_inicio . ' +6 days'));

$nombreVendedor = '';
$porcentajeComision = 0;

// Obtener datos del vendedor si el ID es válido
if ($id_vendedor > 0) {
  $sqlV = "SELECT CONCAT(nombre, ' ', apellido1, ' ', apellido2) AS nombre, comision FROM cat_vendedores WHERE id = $id_vendedor";
  $rsV = $clsConsulta->consultaGeneral($sqlV);
  if ($clsConsulta->numrows > 0) {
    $nombreVendedor = $rsV[1]['nombre'];
    $porcentajeComision = $rsV[1]['comision'];
  }
}
?>

<section class="content">
  <div class="container-fluid">
    <div class="ms-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
          <li class="breadcrumb-item"><a href="reporte-comisiones-vendedor">Comisiones</a></li>
          <li class="breadcrumb-item active">Detalle de Comisiones</li>
        </ol>
      </nav>

      <div class="card shadow-lg">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0">
                <i class="fas fa-user-tie me-2 text-primary"></i>
                Vendedor: <strong><?= htmlspecialchars($nombreVendedor) ?></strong>
                &nbsp; | &nbsp; Comisión: <strong><?= number_format($porcentajeComision, 2) ?>%</strong>
              </h5>
            </div>
            <div>
              <a href="reporte-comisiones-vendedor" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Regresar
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="d-flex justify-content-between mb-3">
            <div>
              <button class="btn btn-success" onclick="pagarTodasComisiones()">
                <i class="fas fa-check-circle me-1"></i> PAGAR TODO
              </button>
              <button class="btn btn-warning" onclick="pagarSeleccionadas()">
                <i class="fas fa-check-double me-1"></i> PAGAR SELECCIONADOS
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tablaDetalleComisiones" class="table table-bordered table-hover" width="100%">
              <thead>
                <tr>
                  <th><input type="checkbox" id="checkbox-todos" onclick="seleccionarTodosCheckboxes(this)"></th>
                  <th>Remisión</th>
                  <th>Fecha</th>
                  <th>Monto Venta</th>
                  <th>% Comisión</th>
                  <th>Total Comisión</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">Totales:</th>
                  <th id="totalMontoVenta">$0.00</th>
                  <th></th>
                  <th id="totalComision">$0.00</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function pagarRemision(id) {
    alertify.confirm('Confirmar pago', '¿Deseas marcar esta remisión como pagada?',
      function() {
        document.getElementById('spinner').style.display = 'block';
        fetch('ajax/comisiones/pagar-remision.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
          })
          .then(response => response.json()) // 👈 si el archivo regresa texto plano, esto fallará
          .then(data => {
            document.getElementById('spinner').style.display = 'none';
            if (data.status === 'ok') {
              alertify.success('Remisión pagada');
              $('#tablaDetalleComisiones').DataTable().ajax.reload(null, false);
            } else {
              alertify.error(data.mensaje || 'Error al pagar');
            }
          })
          .catch(() => {
            document.getElementById('spinner').style.display = 'none';
            alertify.error('Error de conexión');
          });
      },
      function() {
        alertify.message('Pago cancelado');
      }
    );
  }

  const idVendedor = <?= intval($cat) ?>;
  const fechaInicio = "<?= $fecha_inicio ?>";
  const fechaFin = "<?= $fecha_fin ?>";

  function pagarTodasComisiones() {
    alertify.confirm('¿Estás seguro?', '¿Deseas pagar TODAS las remisiones del periodo?',
      function() {
        const spinner = document.getElementById("spinner");
        if (spinner) spinner.style.display = "block";

        fetch("ajax/comisiones/pagar-todo.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              id_vendedor: idVendedor,
              fecha_inicio: fechaInicio,
              fecha_fin: fechaFin
            })
          })
          .then(res => res.json())
          .then(data => {
            if (spinner) spinner.style.display = "none";

            if (data.status === "ok") {
              alertify.success(`Se pagaron ${data.pagadas} remisiones.`);
              if (typeof tabla !== "undefined") {
                tabla.ajax.reload(null, false);
              } else {
                location.reload();
              }
            } else {
              alertify.error("Error: " + (data.message || "No se pudieron pagar las comisiones."));
            }
          })
          .catch(err => {
            if (spinner) spinner.style.display = "none";
            console.error("Error de red o parsing:", err);
            alertify.error("Error de conexión o respuesta no válida");
          });
      },
      function() {
        alertify.message("Pago cancelado");
      }
    );
  }



  function pagarSeleccionadas() {
    const seleccionados = Array.from(document.querySelectorAll('.checkbox-comision:checked')).map(cb => cb.value);
    if (seleccionados.length === 0) {
      alertify.warning('Selecciona al menos una remisión');
      return;
    }

    alertify.confirm('Confirmar', '¿Deseas pagar las remisiones seleccionadas?', function() {
      document.getElementById('spinner').style.display = 'block';

      fetch('ajax/comisiones/pagar-seleccionados.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'ids=' + encodeURIComponent(JSON.stringify(seleccionados))
        })
        .then(response => response.text())
        .then(text => {
          console.log('Respuesta completa:', text);
          try {
            const data = JSON.parse(text);
            if (data.status === 'ok') {
              alertify.success(`Se pagaron ${data.pagadas} remisiones.`);
              $('#tablaDetalleComisiones').DataTable().ajax.reload(null, false);
            } else {
              alertify.error(data.mensaje || 'Error inesperado.');
            }
          } catch (e) {
            console.error('Error al parsear JSON:', e, 'Texto:', text);
            alertify.error('Respuesta no válida del servidor');
          }
          document.getElementById('spinner').style.display = 'none';
        })
        .catch(error => {
          document.getElementById('spinner').style.display = 'none';
          console.error('Error de red:', error);
          alertify.error('Error de conexión o parsing');
        });


    }, null);
  }

  function seleccionarTodosCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.checkbox-comision');
    checkboxes.forEach(cb => cb.checked = source.checked);
  }

  $(document).ready(function() {

    var tabla = $('#tablaDetalleComisiones').DataTable({
      ajax: 'ajax/comisiones/tabla-comisiones-detalle.php?id_vendedor=<?= $id_vendedor ?>&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>',
      columns: [{
          data: 'checkbox',
          orderable: false
        },
        {
          data: 'remision'
        },
        {
          data: 'fecha'
        },
        {
          data: 'monto'
        }, // 👈 Esta es la clave correcta que devuelve tabla-comisiones-detalle.php
        {
          data: 'porcentaje'
        },
        {
          data: 'comision'
        },
        {
          data: 'acciones',
          orderable: false
        }
      ],
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
        search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
      }
    });


    // Calcula los totales 
    tabla.on('draw.dt', function() {
      let api = tabla;
      let totalMonto = 0;
      let totalComision = 0;

      api.rows({
        page: 'current'
      }).data().each(function(row) {
        totalMonto += parseFloat(row.monto.replace(/[$,]/g, '')) || 0;
        totalComision += parseFloat(row.comision.replace(/[$,]/g, '')) || 0;
      });

      document.getElementById('totalMontoVenta').innerText = '$' + totalMonto.toLocaleString('es-MX', {
        minimumFractionDigits: 2
      });
      document.getElementById('totalComision').innerText = '$' + totalComision.toLocaleString('es-MX', {
        minimumFractionDigits: 2
      });
    });

  });
</script>