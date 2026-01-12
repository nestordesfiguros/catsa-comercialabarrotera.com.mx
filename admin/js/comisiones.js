$(function () {
  let tabla = $('#tablaCortes').DataTable({
    ajax: {
      url: 'ajax/comisiones/tabla-comisiones.php',
      data: function (d) {
        d.vendedor = $('#filtro_vendedor').val();
        let fechas = $('#rangoFechas').val().split(' - ');
        d.fecha_ini = fechas[0];
        d.fecha_fin = fechas[1];
      }
    },
    columns: [
      null, null, null, null, null, null, null
    ],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json' }
  });

  $('#filtro_vendedor, #rangoFechas').on('change', function () {
    tabla.ajax.reload();
  });

  // carga vendedores al iniciar
  $.getJSON('ajax/catalogos/vendedores.php', function (res) {
    let $select = $('#filtro_vendedor');
    $select.append(`<option value="">-- Todos --</option>`);
    res.forEach(v => {
      $select.append(`<option value="${v.id}">${v.nombre}</option>`);
    });
  });

  $('#rangoFechas').daterangepicker({
    locale: { format: 'YYYY-MM-DD' },
    startDate: moment().startOf('week'),
    endDate: moment().endOf('week')
  });
});

function abrirModalCorte() {
  // abrir modal para capturar corte (pendiente crear)
  alertify.alert('Falta construir el modal para nuevo corte');
}

function verCorte(id) {
  alertify.message("Funcionalidad de detalle en construcción");
}
