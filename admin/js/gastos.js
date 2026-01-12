// js/gastos.js
$(function () {
  function parseJSONSeguro(d) {
    try {
      return typeof d === "string"
        ? JSON.parse(d.trim().match(/\{[\s\S]*\}$/)?.[0] || d)
        : d;
    } catch (e) {
      return null;
    }
  }
  function ok(m) {
    window.alertify ? alertify.success(m) : alert(m);
  }
  function err(m) {
    window.alertify ? alertify.error(m) : alert(m);
  }

  // validate.js: método regex
  if ($.validator && !$.validator.methods.regex) {
    $.validator.addMethod(
      "regex",
      function (v, el, r) {
        var re = r instanceof RegExp ? r : new RegExp(r);
        return this.optional(el) || re.test(v);
      },
      "Formato inválido"
    );
  }

  // Rellena selects de filtros y del modal
  function cargarCatalogos() {
    return $.ajax({
      url: "ajax/gastos/catalogos.php",
      method: "POST",
      dataType: "json",
    })
      .done(function (r) {
        if (!r || !r.ok) {
          err("No se pudieron cargar catálogos");
          return;
        }
        // Filtros
        const $fc = $("#filtroCategoria")
          .empty()
          .append('<option value="">Todas</option>');
        r.categorias.forEach((c) =>
          $fc.append(`<option value="${c.id_categoria}">${c.nombre}</option>`)
        );
        const $fm = $("#filtroMetodo")
          .empty()
          .append('<option value="">Todos</option>');
        r.metodos.forEach((m) =>
          $fm.append(`<option value="${m.id_metodo}">${m.nombre}</option>`)
        );
        // Modal
        const $cat = $("#idCategoria").empty();
        r.categorias.forEach((c) =>
          $cat.append(`<option value="${c.id_categoria}">${c.nombre}</option>`)
        );
        const $met = $("#idMetodo").empty();
        r.metodos.forEach((m) =>
          $met.append(`<option value="${m.id_metodo}">${m.nombre}</option>`)
        );
      })
      .fail(function () {
        err("Error de red al cargar catálogos");
      });
  }
  cargarCatalogos();

  // DataTable
  const dt = $("#tablaGastos").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "ajax/gastos/datatable.php",
      type: "POST",
      data: function (d) {
        d.filtroFechaInicio = $("#filtroFechaInicio").val();
        d.filtroFechaFin = $("#filtroFechaFin").val();
        d.filtroCategoria = $("#filtroCategoria").val();
        d.filtroMetodo = $("#filtroMetodo").val();
        d.filtroEstatus = $("#filtroEstatus").val();
      },
    },
    columns: [
      { data: "fecha" },
      { data: "categoria" },
      { data: "descripcion" },
      { data: "metodo" },
      { data: "referencia" },
      { data: "monto", className: "text-end" },
      { data: "estatus", className: "text-center" },
      {
        data: "acciones",
        className: "text-center",
        orderable: false,
        searchable: false,
      },
    ],
    order: [[0, "desc"]],
    dom:
      "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
    language: {
      url: "assets/datatables/Spanish.json",
      sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
    },
    responsive: true,
    drawCallback: function (settings) {
      // Recibir total del backend y mostrarlo en el <tfoot>
      const json = settings.json || {};
      const total = Number(json.sumMonto || 0);
      const mx = new Intl.NumberFormat("es-MX", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(total);
      $("#totalFiltrado").text(mx);
    },
  });

  // Búsqueda externa
  $("#searchGastos").on("keyup", function () {
    dt.search($(this).val()).draw();
  });

  // Filtros → recargar
  $(
    "#filtroFechaInicio, #filtroFechaFin, #filtroCategoria, #filtroMetodo, #filtroEstatus"
  ).on("change", function () {
    dt.ajax.reload();
  });

  // Abrir alta
  window.FnGastosAltas = function () {
    $("#formGasto")[0].reset();
    $("#idGasto").val("");
    $("#tituloModalGasto").text("Nuevo gasto");
    cargarCatalogos().always(function () {
      const m = new bootstrap.Modal(document.getElementById("modalGasto"));
      m.show();
      $("#modalGasto").on("shown.bs.modal", () =>
        $("#fechaGasto").trigger("focus")
      );
    });
  };

  // Editar
  $(document).on("click", ".btn-editar-gasto", function () {
    const id = $(this).data("id");
    if (!id) {
      err("ID inválido");
      return;
    }
    $.ajax({
      url: "ajax/gastos/obtener.php",
      method: "POST",
      dataType: "json",
      data: { idGasto: id },
    })
      .done(function (r) {
        if (!r || !r.ok || !r.data) {
          err((r && r.msg) || "No encontrado");
          return;
        }
        $("#tituloModalGasto").text("Editar gasto");
        $("#idGasto").val(r.data.id_gasto);
        $("#fechaGasto").val(r.data.fecha_gasto);
        $("#horaGasto").val(r.data.hora_gasto);
        cargarCatalogos().always(function () {
          $("#idCategoria").val(r.data.id_categoria);
          $("#descripcionGasto").val(r.data.descripcion);
          $("#montoGasto").val(r.data.monto);
          $("#idMetodo").val(r.data.id_metodo);
          $("#referenciaGasto").val(r.data.referencia || "");
          $("#notasGasto").val(r.data.notas || "");
          new bootstrap.Modal(document.getElementById("modalGasto")).show();
        });
      })
      .fail(() => err("Fallo de red/servidor al obtener"));
  });

  // Estatus
  $(document).on("click", ".btn-estatus-gasto", function () {
    const id = $(this).data("id");
    const estatus = $(this).data("estatus");
    if (!id) {
      err("ID inválido");
      return;
    }
    const nuevo = estatus === "Activo" ? "Cancelado" : "Activo";
    (window.alertify
      ? alertify.confirm
      : (t, m, ok) => {
          if (confirm(m)) ok();
        })("Confirmar", `¿Cambiar estatus a <b>${nuevo}</b>?`, function () {
      $.ajax({
        url: "ajax/gastos/cambiar-estatus.php",
        method: "POST",
        dataType: "json",
        data: { idGasto: id, estatusNuevo: nuevo },
      })
        .done((r) => {
          if (r && r.ok) {
            ok("Estatus actualizado");
            dt.ajax.reload(null, false);
          } else {
            err((r && r.msg) || "No se pudo actualizar");
          }
        })
        .fail(() => err("Fallo de red/servidor"));
    });
  });

  // Validación y submit form Gasto
  $("#formGasto").validate({
    rules: {
      fechaGasto: { required: true },
      horaGasto: { required: true },
      idCategoria: { required: true },
      descripcionGasto: { required: true, minlength: 3 },
      montoGasto: {
        required: true,
        number: true,
        regex: /^(?!0(?:\.0+)?$)\d+(\.\d{1,2})?$/,
      },
      idMetodo: { required: true },
    },
    messages: {
      fechaGasto: "Selecciona la fecha",
      horaGasto: "Selecciona la hora",
      idCategoria: "Selecciona la categoría",
      descripcionGasto: {
        required: "Escribe una descripción",
        minlength: "Mínimo 3 caracteres",
      },
      montoGasto: {
        required: "Escribe el monto",
        number: "Monto inválido",
        regex: "Mayor a 0 (máx. 2 decimales)",
      },
      idMetodo: "Selecciona el método de pago",
    },
    errorElement: "span",
    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback text-danger");
      element
        .closest(".form-group, .col-md-6, .col-md-8, .mb-3, .row")
        .append(error);
    },
    highlight: (el) => $(el).addClass("is-invalid"),
    unhighlight: (el) => $(el).removeClass("is-invalid").addClass("is-valid"),
    submitHandler: function (form) {
      const datos = $(form).serialize();
      const url = $("#idGasto").val()
        ? "ajax/gastos/actualizar.php"
        : "ajax/gastos/guardar.php";
      (window.alertify
        ? alertify.confirm
        : (t, m, ok) => {
            if (confirm(m)) ok();
          })(
        "Aviso..!",
        "¿Deseas guardar los cambios?",
        function () {
          $.ajax({ url: url, method: "POST", data: datos, dataType: "json" })
            .done(function (r) {
              if (r && r.ok) {
                ok("Guardado correctamente");
                dt.ajax.reload(null, false);
                const me = document.getElementById("modalGasto");
                (
                  bootstrap.Modal.getInstance(me) || new bootstrap.Modal(me)
                ).hide();
              } else {
                err((r && r.msg) || "No se pudo guardar");
              }
            })
            .fail(() => err("Fallo de red/servidor"));
        },
        function () {
          window.alertify && alertify.error("Cancelado");
        }
      );
    },
  });

  // Modal Nueva categoría (validate + submit)
  $("#btnNuevaCategoria").on("click", function () {
    $("#formCategoria")[0].reset();
    const m = new bootstrap.Modal(document.getElementById("modalCategoria"));
    m.show();
    $("#modalCategoria").on("shown.bs.modal", () =>
      $("#nombreCategoriaNuevo").trigger("focus")
    );
  });

  $("#formCategoria").validate({
    rules: {
      nombreCategoriaNuevo: { required: true, minlength: 2, maxlength: 120 },
    },
    messages: {
      nombreCategoriaNuevo: {
        required: "Escribe un nombre",
        minlength: "Mínimo 2",
        maxlength: "Máximo 120",
      },
    },
    errorElement: "span",
    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback text-danger");
      element.closest(".form-group").append(error);
    },
    highlight: (el) => $(el).addClass("is-invalid"),
    unhighlight: (el) => $(el).removeClass("is-invalid").addClass("is-valid"),
    submitHandler: function (form) {
      const nombre = $("#nombreCategoriaNuevo").val().trim();
      const descripcion = $("#descCategoriaNuevo").val().trim();
      const $btn = $("#btnGuardarCategoria").prop("disabled", true);
      $.ajax({
        url: "ajax/gastos/guardar-categoria.php",
        method: "POST",
        dataType: "json",
        data: { nombreCategoria: nombre, descripcionCategoria: descripcion },
      })
        .done(function (r) {
          if (r && r.ok) {
            ok("Categoría guardada");
            cargarCatalogos(); // recarga filtros y modal
            // seleccionar la nueva categoría si el endpoint devuelve id_categoria
            if (r.id_categoria) {
              $("#idCategoria").val(r.id_categoria);
            }
            const me = document.getElementById("modalCategoria");
            (bootstrap.Modal.getInstance(me) || new bootstrap.Modal(me)).hide();
          } else {
            err((r && r.msg) || "No se pudo guardar la categoría");
          }
        })
        .fail(() => err("Fallo de red/servidor"))
        .always(() => $btn.prop("disabled", false));
    },
  });

  // Exportar CSV/PDF (form POST, sin $.post)
  function getFiltros() {
    return {
      filtroFechaInicio: $("#filtroFechaInicio").val(),
      filtroFechaFin: $("#filtroFechaFin").val(),
      filtroCategoria: $("#filtroCategoria").val(),
      filtroMetodo: $("#filtroMetodo").val(),
      filtroEstatus: $("#filtroEstatus").val(),
      search: $("#searchGastos").val(),
    };
  }
  function postTo(url, data) {
    const form = $("<form>", { method: "POST", action: url, target: "_blank" });
    Object.keys(data).forEach((k) =>
      form.append($("<input>", { type: "hidden", name: k, value: data[k] }))
    );
    $("body").append(form);
    form.trigger("submit");
    form.remove();
  }
  $("#btnExportCSV").on("click", function () {
    postTo("ajax/gastos/export-csv.php", getFiltros());
  });
  $("#btnExportPDF").on("click", function () {
    postTo("ajax/gastos/export-pdf.php", getFiltros());
  });
});
