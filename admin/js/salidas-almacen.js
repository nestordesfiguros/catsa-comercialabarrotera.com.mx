/* global $, alertify, bootstrap */
(function () {
  let dtSalidas, dtRemisiones;
  const $spinner = $("#spinner");

  function setSpinner(v) {
    $spinner.toggle(!!v);
  }

  // Util
  function formatMoney(v) {
    const n = parseFloat(v || 0);
    return n.toLocaleString("es-MX", {
      style: "currency",
      currency: "MXN",
      maximumFractionDigits: 2,
    });
  }

  function actualizarTotal() {
    let total = 0;
    $("#tbodySalida tr.fila").each(function () {
      const cant = parseFloat($(this).find(".cantidad").val()) || 0;
      const pu = parseFloat($(this).find(".precio").val()) || 0;
      total += cant * pu;
    });
    $("#totalSalida").text(formatMoney(total));
    return total;
  }

  function filaVaciaOnOff() {
    const has = $("#tbodySalida tr.fila").length > 0;
    $("#filaVacia").toggle(!has);
  }

  function addPartida(prod) {
    // prod: {id_producto, nombre, cantidad, precio_unitario, id_remision?}
    const idp = prod.id_producto;
    const remTxt = prod.id_remision ? "REM " + prod.id_remision : "-";

    // Si ya existe misma clave + misma remision => acumula cantidad
    let fusionada = false;
    $("#tbodySalida tr.fila").each(function () {
      const idExist = parseInt($(this).data("idp"), 10);
      const remExist = $(this).data("idrem")
        ? parseInt($(this).data("idrem"), 10)
        : null;
      if (
        idExist === idp &&
        String(remExist || "") === String(prod.id_remision || "")
      ) {
        const input = $(this).find(".cantidad");
        const val = parseFloat(input.val()) || 0;
        input.val((val + parseFloat(prod.cantidad || 0)).toFixed(2));
        fusionada = true;
      }
    });
    if (fusionada) {
      actualizarTotal();
      return;
    }

    const tr = $(`
      <tr class="fila" data-idp="${idp}" data-idrem="${prod.id_remision || ""}">
        <td>${prod.nombre}</td>
        <td class="text-end">
          <input type="number" min="0.01" step="0.01" class="form-control form-control-sm cantidad text-end" value="${parseFloat(
            prod.cantidad || 0
          ).toFixed(2)}">
        </td>
        <td class="text-end">
          <input type="number" min="0.00" step="0.01" class="form-control form-control-sm precio text-end" value="${parseFloat(
            prod.precio_unitario || 0
          ).toFixed(2)}">
        </td>
        <td>${remTxt}
          <input type="hidden" class="id_remision" value="${
            prod.id_remision || ""
          }">
        </td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-danger btn-del"><i class="fa fa-times"></i></button>
        </td>
      </tr>
    `);
    $("#tbodySalida").append(tr);
    filaVaciaOnOff();
    actualizarTotal();
  }

  function payloadGuardar() {
    const cabecera = {
      id_almacen: $("#id_almacen").val(),
      id_cliente: $("#id_cliente").val() || "",
      observaciones: $("#observaciones").val() || "",
    };
    const detalles = [];
    $("#tbodySalida tr.fila").each(function () {
      const id_producto = parseInt($(this).data("idp"), 10);
      const cantidad = parseFloat($(this).find(".cantidad").val()) || 0;
      const precio_unitario = parseFloat($(this).find(".precio").val()) || 0;
      const id_remision = $(this).find(".id_remision").val() || null;
      if (id_producto > 0 && cantidad > 0) {
        detalles.push({ id_producto, cantidad, precio_unitario, id_remision });
      }
    });

    // remisiones relacionadas desde el detalle (únicas)
    const remisiones = [];
    $("#tbodySalida .id_remision").each(function () {
      const v = $(this).val();
      if (v && !remisiones.includes(v)) remisiones.push(v);
    });

    return {
      cabecera,
      detalles,
      remisiones,
      resolver_faltantes: $("#resolver_faltantes").is(":checked") ? 1 : 0,
    };
  }

  // ===== Listado principal =====
  $(function () {
    dtSalidas = $("#tablaSalidas").DataTable({
      serverSide: true,
      processing: true,
      dom:
        "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
      ajax: { url: "ajax/salidas-almacen/listar.php", type: "POST" },
      ordering: true,
      pageLength: 10,
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
      },
      columnDefs: [
        { targets: [0, 5, 6], className: "text-center" },
        { targets: [4], className: "text-end" },
        { targets: [6], orderable: false },
      ],
    });

    // Nueva salida
    $("#btnNuevaSalida").on("click", function () {
      $("#tituloModalSalida").text("Nueva salida de almacén");
      $("#formSalida")[0].reset();
      $("#id_salida").val("");
      $("#tbodySalida")
        .empty()
        .append(
          '<tr id="filaVacia"><td colspan="5" class="text-center text-muted">Sin partidas</td></tr>'
        );
      $("#modalSalida").modal("show");
      actualizarTotal();
    });

    // Ver salida (detalle)
    $(document).on("click", ".btn-ver-salida", function () {
      const id = $(this).data("id");
      setSpinner(true);
      $.get(
        "ajax/salidas-almacen/obtener.php",
        { id },
        function (r) {
          setSpinner(false);
          if (!r || !r.success) {
            alertify.error(r && r.message ? r.message : "No encontrada");
            return;
          }
          const cab = r.cabecera;
          const det = r.detalle || [];

          let html = `
          <div class="modal-header">
            <h5 class="modal-title">Salida #${cab.id_salida}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2"><b>Fecha:</b> ${cab.fecha || ""}</div>
            <div class="mb-2"><b>Almacén:</b> #${cab.id_almacen}</div>
            <div class="mb-2"><b>Cliente:</b> ${cab.cliente || "-"}</div>
            <div class="mb-2"><b>Observaciones:</b> ${
              cab.observaciones || "-"
            }</div>
            <div class="mb-2"><b>Estatus:</b> ${cab.estatus}</div>

            <div class="table-responsive mt-3">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio unitario</th>
                    <th>Remisión</th>
                  </tr>
                </thead>
                <tbody>`;
          if (det.length === 0) {
            html += `<tr><td colspan="4" class="text-center text-muted">Sin partidas</td></tr>`;
          } else {
            det.forEach((p) => {
              html += `
              <tr>
                <td>${p.nombre}</td>
                <td class="text-end">${parseFloat(p.cantidad || 0).toFixed(
                  2
                )}</td>
                <td class="text-end">${formatMoney(p.precio_unitario)}</td>
                <td>${p.id_remision ? "REM " + p.id_remision : "-"}</td>
              </tr>`;
            });
          }
          html += `
                </tbody>
              </table>
              <div class="text-end h5">Total: ${formatMoney(
                cab.total || 0
              )}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        `;
          $("#contenedorVerSalida").html(html);
          new bootstrap.Modal(document.getElementById("modalVerSalida")).show();
        },
        "json"
      ).fail(function () {
        setSpinner(false);
        alertify.error("Error de conexión");
      });
    });

    // Cancelar salida
    $(document).on("click", ".btn-cancelar-salida", function () {
      const id = $(this).data("id");
      alertify
        .confirm(
          "Confirmar",
          "¿Deseas cancelar esta salida?",
          function () {
            setSpinner(true);
            $.post(
              "ajax/salidas-almacen/cancelar.php",
              { id },
              function (r) {
                setSpinner(false);
                if (r && r.success) {
                  alertify.success(r.message || "Cancelada");
                  dtSalidas.ajax.reload(null, false);
                } else {
                  alertify.error(
                    r && r.message ? r.message : "No se pudo cancelar"
                  );
                }
              },
              "json"
            ).fail(function () {
              setSpinner(false);
              alertify.error("Error de conexión");
            });
          },
          function () {
            alertify.message("Acción cancelada");
          }
        )
        .set("labels", { ok: "Sí", cancel: "No" });
    });

    // Abrir selector de remisiones
    $("#btnAbrirRemisiones").on("click", function () {
      const id_cliente = $("#id_cliente").val() || "";
      if (!$.fn.DataTable.isDataTable("#tablaRemisionesSelect")) {
        dtRemisiones = $("#tablaRemisionesSelect").DataTable({
          ajax: {
            url: "ajax/salidas-almacen/buscar-remisiones.php",
            type: "POST",
            data: function (d) {
              d.search = d.search?.value || "";
              d.id_cliente = id_cliente;
            },
            dataSrc: "",
          },
          columns: [
            { data: "id", render: (id) => "REM " + id },
            { data: "cliente" },
            { data: null, render: () => "-", className: "text-center" }, // si luego deseas fecha formateada, añádela en endpoint
            {
              data: "total",
              render: (v) => formatMoney(v),
              className: "text-end",
            },
            {
              data: null,
              className: "text-center",
              render: (r) =>
                `<button class="btn btn-sm btn-primary btn-add-rem" data-id="${r.id}">Agregar</button>`,
            },
          ],
          language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
          },
        });
      } else {
        dtRemisiones.ajax.reload(null, false);
      }
      $("#modalRemisiones").modal("show");
    });

    // Agregar partidas de una remisión
    $(document).on("click", ".btn-add-rem", function () {
      const id_rem = $(this).data("id");
      const id_almacen = $("#id_almacen").val();
      if (!id_almacen) {
        alertify.error("Selecciona primero un almacén");
        return;
      }

      setSpinner(true);
      $.get(
        "ajax/salidas-almacen/productos-remision.php",
        { id_remision: id_rem },
        function (r) {
          setSpinner(false);
          if (!r || !r.success) {
            alertify.error(
              r && r.message ? r.message : "No se pudo cargar la remisión"
            );
            return;
          }
          const productos = r.productos || [];
          if (productos.length === 0) {
            alertify.message("La remisión no tiene partidas");
            return;
          }

          productos.forEach((p) => {
            addPartida({
              id_producto: p.id_producto,
              nombre: p.nombre,
              cantidad: p.cantidad, // puedes traer la cantidad total o sugerida
              precio_unitario: p.precio_unitario, // de mov_remisiones.precio_unitario
              id_remision: id_rem,
            });
          });
          alertify.success("Partidas agregadas");
        },
        "json"
      ).fail(function () {
        setSpinner(false);
        alertify.error("Error de conexión");
      });
    });

    // Detalle: eventos
    $(document).on("input", ".cantidad, .precio", actualizarTotal);
    $(document).on("click", ".btn-del", function () {
      $(this).closest("tr").remove();
      filaVaciaOnOff();
      actualizarTotal();
    });

    // Guardar
    $("#btnGuardarSalida").on("click", function () {
      const id_almacen = $("#id_almacen").val();
      if (!id_almacen) {
        alertify.error("Selecciona el almacén");
        return;
      }
      if ($("#tbodySalida tr.fila").length === 0) {
        alertify.error("Agrega al menos una partida");
        return;
      }

      const payload = payloadGuardar();
      alertify
        .confirm(
          "Confirmar",
          "¿Deseas guardar la salida?",
          function () {
            setSpinner(true);
            $.post(
              "ajax/salidas-almacen/guardar.php",
              payload,
              function (r) {
                setSpinner(false);
                if (r && r.success) {
                  alertify.success(r.message || "Guardado");
                  $("#modalSalida").modal("hide");
                  dtSalidas.ajax.reload(null, false);
                } else {
                  if (r && r.faltantes) {
                    // Mostrar faltantes en texto
                    const lista = Object.entries(r.faltantes)
                      .map(([idp, c]) => `Prod ${idp}: ${c}`)
                      .join("<br>");
                    alertify.alert("<b>Inventario insuficiente</b>", lista);
                  }
                  alertify.error(
                    r && r.message ? r.message : "No se pudo guardar"
                  );
                }
              },
              "json"
            ).fail(function () {
              setSpinner(false);
              alertify.error("Error de conexión");
            });
          },
          function () {
            alertify.message("Cancelado");
          }
        )
        .set("labels", { ok: "Sí", cancel: "No" });
    });
  });
})();
