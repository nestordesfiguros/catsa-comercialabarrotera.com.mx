// Reportes → Ventas (Período) SIN moment.js ni daterangepicker
// Usa dos <input type="date">: #fechaIni y #fechaFin

(function () {
  const $spinner = document.getElementById("spinner");

  // Helpers de fecha (formato local YYYY-MM-DD)
  function ymdLocal(d) {
    // Convierte a "local ISO" evitando el desfase de zona
    const tz = d.getTimezoneOffset();
    const local = new Date(d.getTime() - tz * 60000);
    return local.toISOString().slice(0, 10);
  }
  function firstDayOfMonth(d) {
    return new Date(d.getFullYear(), d.getMonth(), 1);
  }

  // Referencias a inputs
  const $ini = document.getElementById("fechaIni");
  const $fin = document.getElementById("fechaFin");

  // Setea valores por defecto (mes en curso)
  (function setDefaultDates() {
    const today = new Date();
    const start = firstDayOfMonth(today);
    if ($ini) $ini.value = ymdLocal(start);
    if ($fin) $fin.value = ymdLocal(today);
  })();

  // Validación rápida de fechas
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

  // Formateo de moneda MXN
  const fmtMXN = new Intl.NumberFormat("es-MX", {
    style: "currency",
    currency: "MXN",
    maximumFractionDigits: 2,
  });
  function renderMXN(val) {
    const num = parseFloat(val || 0);
    if (isNaN(num)) return "—";
    return fmtMXN.format(num);
  }

  // Config DataTable
  const config = {
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: {
      url: "ajax/reportes/ventas-periodo.php",
      type: "POST",
      data: function (d) {
        const dates = getValidatedDates();
        d.fecha_ini = dates ? dates.fechaIni : "";
        d.fecha_fin = dates ? dates.fechaFin : "";
        d.vendedor = $("#filtroVendedor").val() || "";
        d.cliente = $("#filtroCliente").val() || "";
        d.forma = $("#filtroForma").val() || "";
        d.estatus = $("#filtroEstatus").val() || "";
      },
      beforeSend: function () {
        if ($spinner) $spinner.style.display = "block";
      },
      complete: function () {
        if ($spinner) $spinner.style.display = "none";
      },
    },
    order: [[0, "desc"]], // fecha desc
    columns: [
      { data: "fecha" },
      { data: "folio" },
      { data: "cliente" },
      { data: "vendedor" },
      {
        data: "total",
        render: function (d) {
          return renderMXN(d);
        },
        className: "text-end",
      },
      { data: "tipo_venta" }, // <-- antes: forma_pago
      { data: "estatus" },
    ],

    language: {
      url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
      search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
    },
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    pageLength: 25,
    drawCallback: function () {
      // Sumar total mostrado
      let api = this.api();
      let total = 0;
      api
        .column(4, { page: "current" })
        .data()
        .each(function (v) {
          const num = parseFloat(v || 0);
          if (!isNaN(num)) total += num;
        });
      $("#footerTotal").text(renderMXN(total));
    },
  };

  // Botones de exportación (si están cargados)
  if ($.fn.dataTable.Buttons) {
    config.dom = "Bfrtip";
    config.buttons = [
      { extend: "excelHtml5", title: "ventas_periodo" },
      { extend: "csvHtml5", title: "ventas_periodo" },
      { extend: "pdfHtml5", title: "ventas_periodo" },
    ];
  }

  const tabla = $("#tablaVentasPeriodo").DataTable(config);

  // Búsqueda externa en #search (patrón institucional)
  if ($("#search").length) {
    $("#search")
      .off("keyup.reportes")
      .on("keyup.reportes", function () {
        tabla.search($(this).val()).draw();
      });
  }

  // KPIs
  function cargarKPIs() {
    const dates = getValidatedDates();
    if (!dates) return;
    $.ajax({
      url: "ajax/reportes/kpis-ventas.php",
      type: "POST",
      dataType: "json",
      data: {
        fecha_ini: dates.fechaIni,
        fecha_fin: dates.fechaFin,
        vendedor: $("#filtroVendedor").val() || "",
        cliente: $("#filtroCliente").val() || "",
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
        $("#kpiTotalVentas").text(renderMXN(r.total_ventas || 0));
        $("#kpiTickets").text(r.tickets || 0);
        $("#kpiTicketPromedio").text(renderMXN(r.ticket_promedio || 0));
      },
      error: function () {
        $("#kpiTotalVentas").text("—");
        $("#kpiTickets").text("—");
        $("#kpiTicketPromedio").text("—");
      },
    });
  }

  // Botones
  function recargar() {
    // Si las fechas no son válidas, no hace reload
    if (!getValidatedDates()) return;
    tabla.ajax.reload();
    cargarKPIs();
  }

  $("#btnAplicar").on("click", function () {
    recargar();
  });

  $("#btnLimpiar").on("click", function () {
    const today = new Date();
    const start = firstDayOfMonth(today);
    if ($ini) $ini.value = ymdLocal(start);
    if ($fin) $fin.value = ymdLocal(today);
    $("#filtroVendedor").val("");
    $("#filtroCliente").val("");
    $("#filtroForma").val("");
    $("#filtroEstatus").val("");
    recargar();
  });

  // (Opcional) recargar automáticamente al cambiar fechas:
  // $('#fechaIni, #fechaFin').on('change', recargar);

  // Carga inicial de KPIs
  cargarKPIs();
})();
