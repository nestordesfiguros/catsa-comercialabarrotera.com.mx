$(function () {
  const dt = $("#tablaEmpresas").DataTable({
    ajax: "ajax/empresas/tabla-empresas.php",
    ordering: true,
    pageLength: 10,
    dom:
      "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
    language: {
      url: "assets/datatables/Spanish.json",
      sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
    },
    responsive: true,
  });

  $("#search").on("keyup", function () {
    dt.search($(this).val()).draw();
  });
});

function editar(id) {
  location.href = "empresas-editar/" + id;
}
function timbrado(id) {
  location.href = "empresas-timbrado/" + id;
}

function valorEstatus(id, estatus) {
  $("#modalEstatus").modal("show");
  $("#idEmpresaEstatus").val(id);
  if (parseInt(estatus, 10) === 1) {
    $("#estatusActual").html('¿Cambiar a <b class="text-danger">Inactivo</b>?');
    $("#estatusNuevo").val(0);
  } else {
    $("#estatusActual").html('¿Cambiar a <b class="text-success">Activo</b>?');
    $("#estatusNuevo").val(1);
  }
}
function cerrarEstatus() {
  $("#modalEstatus").modal("hide");
}

function modificaEstatus() {
  $.ajax({
    url: "ajax/empresas/modifica-estatus.php",
    type: "POST",
    dataType: "json",
    data: {
      idEmpresa: $("#idEmpresaEstatus").val(),
      estatusNuevo: $("#estatusNuevo").val(),
    },
  })
    .done(function (r) {
      if (r && r.ok) {
        alertify.success("Estatus actualizado");
        $("#tablaEmpresas").DataTable().ajax.reload(null, false);
        cerrarEstatus();
      } else {
        alertify.error(r?.msg || "No fue posible actualizar");
      }
    })
    .fail(function () {
      alertify.error("Fallo de red/servidor");
    });
}
