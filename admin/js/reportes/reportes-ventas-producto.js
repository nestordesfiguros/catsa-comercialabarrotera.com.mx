// Reportes → Ventas por producto (mix y ranking)
// Sin moment/daterangepicker. Dos inputs type="date". DataTables server-side.

(function () {
  const $spinner = document.getElementById("spinner");

  // Helpers fecha local
  function ymdLocal(d) {
    const tz = d.getTimezoneOffset();
    const local = new Date(d.getTime() - tz * 60000);
    return local.toISOString().slice(0, 10);
  }
  function firstDayOfMonth(d) {
    return new Date(d.getFullYear(), d.getMonth(), 1);
  }

  const $ini = document.getElementById("fechaIni");
  const $fin = document.getElementById("fechaFin");

  (function setDefaultDates() {
    const today = new Date();
    const start = firstDayOfMonth(today);
    if ($ini) $ini.value = ymdLocal(start);
    if ($fin) $fin.value = ymdLocal(today);
  })();

  function getValidatedDates() {
    const i = $ini && $ini.value ? $ini.value : "";
    const f = $fin && $fin.value ? $fin.value : "";
    if (!i || !f) {
      if (window.alertify) alertify.error("Selecciona fecha inicial y final");
      return null;
    }
    if (i > f) {
      if (window.alertify)
        alertify.error("La fecha inicial no puede ser mayor que la final");
      return null;
    }
    return { fechaIni: i, fechaFin: f };
  }

  // Formatos
  const fmtMXN = new Intl.NumberFormat("es-MX", {
    style: "currency",
    currency: "MXN",
    maximumFractionDigits: 2,
  });
  function renderMXN(v) {
    const n = parseFloat(v || 0);
    return isNaN(n) ? "—" : fmtMXN.format(n);
  }
  const fmtPct = new Intl.NumberFormat("es-MX", { maximumFractionDigits: 2 });

  // DataTable config
  const config = {
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: {
      url: "ajax/reportes/ventas-producto.php",
      type: "POST",
      data: function (d) {
        const dt = getValidatedDates() || { fechaIni: "", fechaFin: "" };
        d.fecha_ini = dt.fechaIni;
        d.fecha_fin = dt.fechaFin;
        d.vendedor = $("#filtroVendedor").val() || "";
        d.cliente = $("#filtroCliente").val() || "";
        d.producto = $("#filtroProducto").val() || "";
        d.forma = $("#filtroForma").val() || ""; // tipo_venta
        d.estatus = $("#filtroEstatus").val() || "";
      },
      beforeSend: function () {
        if ($spinner) $spinner.style.display = "block";
      },
      complete: function () {
        if ($spinner) $spinner.style.display = "none";
      },
    },
    order: [[2, "desc"]], // ventas desc
    columns: [
      { data: "producto" },
      { data: "unidades", className: "text-end" },
      { data: "ventas", render: (d) => renderMXN(d), className: "text-end" },
      {
        data: "precio_promedio",
        render: (d) => renderMXN(d),
        className: "text-end",
      },
      {
        data: "participacion",
        render: (d) => (d != null ? fmtPct.format(d) + "%" : "—"),
        className: "text-end",
      },
    ],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
    },
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    pageLength: 25,
    drawCallback: function () {
      const api = this.api();
      let tUn = 0,
        tVta = 0;
      api
        .column(1, { page: "current" })
        .data()
        .each((v) => {
          v = parseFloat(v || 0);
          if (!isNaN(v)) tUn += v;
        });
      api
        .column(2, { page: "current" })
        .data()
        .each((v) => {
          v = parseFloat(v || 0);
          if (!isNaN(v)) tVta += v;
        });
      $("#ftUnidades").text(fmtPct.format(tUn));
      $("#ftVentas").text(renderMXN(tVta));
    },
  };

  if ($.fn.dataTable && $.fn.dataTable.Buttons) {
    config.dom = "Bfrtip";
    config.buttons = [
      { extend: "excelHtml5", title: "ventas_por_producto" },
      { extend: "csvHtml5", title: "ventas_por_producto" },
      { extend: "pdfHtml5", title: "ventas_por_producto" },
    ];
  }

  const tabla = $("#tablaVentasProducto").DataTable(config);

  // Búsqueda externa si existe #search
  if ($("#search").length) {
    $("#search")
      .off("keyup.rptProd")
      .on("keyup.rptProd", function () {
        tabla.search($(this).val()).draw();
      });
  }

  // KPIs
  function cargarKPIs() {
    const dt = getValidatedDates();
    if (!dt) return;
    $.ajax({
      url: "ajax/reportes/kpis-ventas-producto.php",
      type: "POST",
      dataType: "json",
      data: {
        fecha_ini: dt.fechaIni,
        fecha_fin: dt.fechaFin,
        vendedor: $("#filtroVendedor").val() || "",
        cliente: $("#filtroCliente").val() || "",
        producto: $("#filtroProducto").val() || "",
        forma: $("#filtroForma").val() || "",
        estatus: $("#filtroEstatus").val() || "",
      },
      beforeSend: function () {
        if ($spinner) $spinner.style.display = "block";
      },
      complete: function () {
        if ($spinner) $spinner.style.display = "none";
      },
      success: function (r) {
        $("#kpiVtaTotal").text(renderMXN(r.total_ventas || 0));
        $("#kpiUnidades").text(fmtPct.format(r.unidades || 0));
        $("#kpiTopProducto").text(r.top_producto || "—");
      },
      error: function () {
        $("#kpiVtaTotal").text("—");
        $("#kpiUnidades").text("—");
        $("#kpiTopProducto").text("—");
      },
    });
  }

  function recargar() {
    if (!getValidatedDates()) return;
    tabla.ajax.reload();
    cargarKPIs();
  }

  $("#btnAplicar").on("click", recargar);
  $("#btnLimpiar").on("click", function () {
    const today = new Date(),
      start = firstDayOfMonth(today);
    if ($ini) $ini.value = ymdLocal(start);
    if ($fin) $fin.value = ymdLocal(today);
    $(
      "#filtroVendedor,#filtroCliente,#filtroProducto,#filtroForma,#filtroEstatus"
    ).val("");
    recargar();
  });

  // Carga inicial
  cargarKPIs();
})();
