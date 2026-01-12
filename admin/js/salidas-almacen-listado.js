// salidas-almacen-listado.js

$(document).ready(function () {
  // Inicializar DataTable para la tabla de salidas
  const tabla = $("#tablaSalidas").DataTable({
    ajax: {
      url: "ajax/salidas/tabla-salidas.php",
      type: "GET",
      data: function (d) {
        d.fecha_ini = $("#fecha_ini").val();
        d.fecha_fin = $("#fecha_fin").val();
        d.estatus = $("#filtro_estatus").val();
      },
      dataSrc: "data",
    },
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
      search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
    },
    ordering: false,
    paging: true,
    info: true,
    columnDefs: [{ targets: 0, orderable: false }],
    footerCallback: function (row, data) {
      // Calcular el total de la columna 'Total'
      let total = 0;
      for (let i = 0; i < data.length; i++) {
        total += parseFloat(data[i][6].replace(/[^0-9.-]+/g, "")) || 0;
      }
      $("#footerTotal").text(
        `$${total.toLocaleString("es-MX", { minimumFractionDigits: 2 })}`
      );
    },
  });

  // Recargar tabla al cambiar filtros
  $("#fecha_ini, #fecha_fin, #filtro_estatus").on("change", function () {
    tabla.ajax.reload();
  });

  // Botón: Ver PDF
  $(document).on("click", ".btnVerPDF", function () {
    const id = $(this).data("id");
    window.open(`reportes/crear-pdf-carta-porte.php?id=${id}`, "_blank");
  });

  // Botón: Cancelar salida
  $(document).on("click", ".btnCancelarSalida", function () {
    const id = $(this).data("id");
    alertify.confirm("¿Estás seguro de cancelar esta salida?", function () {
      fetch("ajax/salidas/cancelar-salida.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`,
      })
        .then((res) => res.json())
        .then((resp) => {
          if (resp.success) {
            alertify.success(resp.message);
            tabla.ajax.reload();
          } else {
            alertify.error(resp.message);
          }
        })
        .catch((err) => alertify.error("Error al cancelar"));
    });
  });

  // Botón: Marcar como procesada
  $(document).on("click", ".btnProcesarSalida", function () {
    const id = $(this).data("id");
    alertify.confirm("¿Deseas marcar esta salida como procesada?", function () {
      fetch("ajax/salidas/procesar-salida.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`,
      })
        .then((res) => res.json())
        .then((resp) => {
          if (resp.success) {
            alertify.success(resp.message);
            tabla.ajax.reload();
          } else {
            alertify.error(resp.message);
          }
        })
        .catch((err) => alertify.error("Error al procesar"));
    });
  });

  // Prevención de envío con Enter
  $(document).on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      return false;
    }
  });
});
