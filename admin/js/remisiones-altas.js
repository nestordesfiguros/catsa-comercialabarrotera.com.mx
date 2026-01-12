// js/remisiones-altas.js

var almacenId;
var dataTable;

// ------ Utilidades ------
function actualizarMensajeTablaVacia() {
  const $tbody = $("#tbodyProductos");
  if ($tbody.children(".fila-producto").length === 0) {
    $tbody.html(
      '<tr id="filaVacia"><td colspan="5">Ningún producto agregado</td></tr>'
    );
  } else {
    $("#filaVacia").remove();
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat("es-MX", {
    style: "currency",
    currency: "MXN",
    maximumFractionDigits: 2,
    useGrouping: true,
  }).format(amount);
}

// Valida cantidad contra existencia y muestra mensajes
function validarCantidadVsExistencia($fila, mostrarMensajeOk = false) {
  const existencia = parseInt($fila.data("existencia")) || 0;
  let cantidad = parseInt($fila.find(".cantidad").val()) || 0;

  if (cantidad <= 0) cantidad = 1;

  if (cantidad > existencia) {
    $fila.find(".cantidad").val(existencia).addClass("is-invalid");
    alertify.error(
      `La cantidad excede la existencia (${existencia}). Se ajustó a ${existencia}.`
    );
    return false;
  } else {
    $fila.find(".cantidad").removeClass("is-invalid").addClass("is-valid");
    if (mostrarMensajeOk) {
      alertify.success(
        `Cantidad válida. Existencia disponible: ${existencia}.`
      );
    }
    return true;
  }
}

// ------ Modal Productos ------
function FnAgregarModal() {
  var almacenInputValue = $("#almacenInput").val();
  var almacenIdSelected = $(
    '#listAlmacenes option[value="' + almacenInputValue + '"]'
  ).data("id");

  if (almacenIdSelected) {
    almacenId = almacenIdSelected;
    $("#modalAddProductos").modal("show");
    if (dataTable) dataTable.ajax.reload();
  } else {
    almacenId = null;
    alertify.alert("Aviso", "No hay productos en el almacén ", function () {});
  }
}
function FnCerrarModal() {
  $("#modalAddProductos").modal("hide");
}

// ------ Cálculos por fila y totales ------
function actualizarTotalPorFila(fila, validarPrecio = false) {
  const cantidad = parseFloat(fila.find(".cantidad").val()) || 0;

  const $inputPrecio = fila.find(".precio");
  const precioTexto = ($inputPrecio.val() || "").replace(/[^\d.-]/g, "");
  let precioVenta = parseFloat(precioTexto) || 0;

  if (validarPrecio) {
    const precioMinimo = parseFloat($inputPrecio.data("precio-minimo")) || 0;
    if (precioVenta < precioMinimo) {
      alertify.error(
        "El precio unitario no puede ser menor al precio mínimo: " +
          formatCurrency(precioMinimo)
      );
      precioVenta = precioMinimo;
      $inputPrecio.val(precioVenta.toFixed(2));
    }
  }

  fila.find(".precio_venta").val(precioVenta);
  const total = cantidad * precioVenta;
  fila.find(".total-producto").text(formatCurrency(total));
}

function actualizarSumaTotal() {
  let sumaTotal = 0;
  $("#tbodyProductos tr.fila-producto").each(function () {
    const cantidad = parseFloat($(this).find(".cantidad").val()) || 0;
    const precioVenta = parseFloat($(this).find(".precio_venta").val()) || 0;
    sumaTotal += cantidad * precioVenta;
  });
  $("#totalPedido").text(formatCurrency(sumaTotal));
  $("#totalInput").val(sumaTotal.toFixed(2)); // hidden total para POST
}

// ------ Agregar producto a la tabla ------
function agregarProducto(fila) {
  const $fila = $(fila);

  const id_producto = $fila.data("id");
  const clave = $fila.data("clave");
  const nombre = $fila.data("nombre");
  const precioSugerido = parseFloat($fila.data("precio_venta")) || 0; // se muestra
  const precioMinimo = parseFloat($fila.data("precio_minimo")) || 0; // validación
  const existencia = parseInt($fila.data("existencia")) || 0;

  // Duplicado por clave
  if ($(`input[value="${clave}"]`).length > 0) {
    alertify.warning("¡Este producto ya está en la lista!");
    return;
  }

  // Precio inicial nunca menor al mínimo
  const precioInicial = Math.max(precioSugerido, precioMinimo);

  const nuevaFila = `
    <tr class="fila-producto" data-existencia="${existencia}">
      <td>
        <input type="number" class="form-control cantidad text-end" value="1" min="1" name="cantidad[]" required>
        <div class="form-text text-end text-muted">Existencia: ${existencia}</div>
      </td>
      <td>${nombre}</td>
      <td class="text-end">
        <input type="text" class="form-control precio text-end"
               value="${precioInicial.toFixed(2)}"
               name="precio_display[]" data-precio-minimo="${precioMinimo}">
        <input type="hidden" class="precio_venta" name="precio_venta[]" value="${precioInicial}">
      </td>
      <td class="text-end total-producto">${formatCurrency(precioInicial)}</td>
      <td class="text-center"><i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer"></i></td>
      <input type="hidden" name="producto_id[]" value="${id_producto}">
      <input type="hidden" name="clave[]" value="${clave}">
      <input type="hidden" name="iva[]" value="0">
    </tr>
  `;

  $("#tbodyProductos").append(nuevaFila);
  actualizarMensajeTablaVacia();

  // Máscara numérica sin símbolo $
  $("#tbodyProductos tr").last().find(".precio").inputmask("numeric", {
    radixPoint: ".",
    groupSeparator: ",",
    autoGroup: true,
    digits: 2,
    digitsOptional: false,
    placeholder: "0",
    clearMaskOnLostFocus: false,
  });

  // Validar cantidad inicial y totales
  const $nueva = $("#tbodyProductos tr").last();
  validarCantidadVsExistencia($nueva, false);
  actualizarTotalPorFila($nueva, true);
  actualizarSumaTotal();

  $("#modalAddProductos").modal("hide");
}

// ------ Guardado ------
function guardarPedido(form) {
  if ($("#tbodyProductos tr.fila-producto").length === 0) {
    alertify.alert(
      '<b class="text-danger">Aviso</b>',
      "No se ha capturado ningún producto",
      function () {}
    );
    return false;
  }

  // Validación de precios numéricos > 0
  let preciosValidos = true;
  $(".precio").each(function () {
    const precioTexto = ($(this).val() || "").replace(/[^\d.-]/g, "");
    const precio = parseFloat(precioTexto);
    if (isNaN(precio) || precio <= 0) {
      preciosValidos = false;
      $(this).addClass("is-invalid");
    } else {
      $(this).removeClass("is-invalid");
    }
  });
  if (!preciosValidos) {
    alertify.alert(
      '<b class="text-danger">Aviso</b>',
      "Hay precios inválidos. Por favor revise los precios ingresados.",
      function () {}
    );
    return false;
  }

  // Validación de existencias por fila
  let cantidadesOk = true;
  $("#tbodyProductos tr.fila-producto").each(function () {
    const ok = validarCantidadVsExistencia($(this), false);
    if (!ok) cantidadesOk = false;
  });
  if (!cantidadesOk) {
    alertify.error("Corrige las cantidades que exceden la existencia.");
    return false;
  }

  actualizarSumaTotal(); // asegurar total

  alertify.confirm(
    "Confirmación",
    "¿Deseas guardar la remisión?",
    function () {
      $("#modalSpiner").modal("show");
      $.ajax({
        url: "ajax/remisiones/guardar.php",
        type: "POST",
        data: $(form).serialize(),
        success: function (respuesta) {
          $("#modalSpiner").modal("hide");
          if ((respuesta || "").trim() === "success") {
            alertify.success("Remisión guardada exitosamente");
            location.href = "remisiones";
          } else {
            alertify.error("Error: " + respuesta);
          }
        },
        error: function (xhr) {
          $("#modalSpiner").modal("hide");
          alertify.error("Error de conexión: " + xhr.statusText);
        },
      });
    },
    function () {
      alertify.error("Guardado cancelado");
    }
  );
}

// ------ Datos de cliente ------
function obtenerDatosCliente(clienteId) {
  $.ajax({
    url: "ajax/pedidos/busca-cliente.php",
    method: "POST",
    data: { idCliente: clienteId },
    dataType: "json",
    success: function (respuesta) {
      if (!respuesta.error) {
        const direccion = `
          ${respuesta.calle} ${respuesta.num_ext || ""}
          ${respuesta.num_int ? "Int. " + respuesta.num_int : ""},
          ${respuesta.colonia},
          ${respuesta.cp},
          ${respuesta.nmunicipio},
          ${respuesta.nestado}
        `
          .replace(/\n/g, "")
          .replace(/  +/g, " ");
        $("#domicilio")
          .val(direccion.trim())
          .removeClass("is-invalid")
          .addClass("is-valid");
        $("#clienteInput").removeClass("is-invalid").addClass("is-valid");
        $("#errorCliente").hide();
      } else {
        mostrarErrorCliente(
          "Cliente no encontrado. Por favor, escribe el nombre completo o verifica que esté registrado."
        );
      }
    },
    error: function () {
      mostrarErrorCliente(
        "Error al obtener datos del cliente. Verifica la conexión o intenta nuevamente."
      );
    },
  });
}
function mostrarErrorCliente(mensaje) {
  $("#errorCliente").text(mensaje).show();
  $("#clienteInput").addClass("is-invalid");
  $("#domicilio").val("");
  $("#clienteInput").focus();
}

// ------ Document Ready ------
$(document).ready(function () {
  actualizarMensajeTablaVacia();
  $("#plazo_credito_container").hide();
  $("#plazo_credito").prop("required", false);

  // Mostrar/ocultar plazo según tipo de venta
  $("#tipo_venta").on("change", function () {
    if ($(this).val() === "credito") {
      $("#plazo_credito_container").show();
      $("#plazo_credito").prop("required", true);
    } else {
      $("#plazo_credito_container").hide();
      $("#plazo_credito").prop("required", false);
    }
  });

  // Cliente por datalist
  $("#clienteInput").on("input", function () {
    const opcion = $("#clientes option").filter(function () {
      return $(this).val() === $("#clienteInput").val();
    });
    if (opcion.length > 0) {
      const clienteId = opcion.data("id");
      $("#clienteId").val(clienteId);
      obtenerDatosCliente(clienteId);

      $.ajax({
        url: "ajax/clientes/get-plazo-cliente.php",
        type: "POST",
        dataType: "json",
        data: { id: clienteId },
        success: function (resp) {
          if (
            resp &&
            resp.plazo_credito &&
            $("#tipo_venta").val() === "credito"
          ) {
            $("#plazo_credito").val(resp.plazo_credito);
          }
        },
      });
    } else {
      $("#clienteId").val("");
      $("#domicilio").val("");
      $("#clienteInput").removeClass("is-valid").addClass("is-invalid");
    }
  });

  // Reglas de validate.js
  $.validator.addMethod(
    "clientExists",
    function () {
      return $("#clienteId").val() !== "";
    },
    "Selecciona un cliente válido de la lista."
  );

  $("#formPedidos").validate({
    rules: {
      fecha: { required: true },
      cliente_nombre: { required: true, clientExists: true },
      direccion_envio: { required: true },
      tipo_venta: { required: true },
      plazo_credito: {
        required: function () {
          return $("#tipo_venta").val() === "credito";
        },
        min: 1,
      },
    },
    messages: {
      fecha: { required: "Escribe una fecha" },
      cliente_nombre: { required: "Selecciona un cliente" },
      direccion_envio: { required: "Escribe el domicilio de entrega" },
      tipo_venta: { required: "Selecciona el tipo de venta" },
      plazo_credito: {
        required: "Escribe el plazo",
        min: "El plazo debe ser mayor a 0",
      },
    },
    errorElement: "div",
    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback");
      error.insertAfter(element);
    },
    highlight: function (element) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element) {
      $(element).removeClass("is-invalid").addClass("is-valid");
    },
    submitHandler: function (form) {
      guardarPedido(form);
    },
  });

  // DataTable del modal de productos
  dataTable = $("#TableListaProductos").DataTable({
    ajax: {
      url: "ajax/remisiones/obtener-productos-almacen.php",
      type: "POST",
      data: function (d) {
        d.almacenId = almacenId;
        return d;
      },
    },
    ordering: true,
    pageLength: 10,
    dom:
      "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-6'l><'col-sm-6'p>>",
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
      sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar',
    },
    responsive: true,
  });

  // Buscador en modal
  $("#search").keyup(function () {
    dataTable.search($(this).val()).draw();
  });

  // Eliminar fila de producto
  $("#tbodyProductos").on("click", ".btn-eliminar", function () {
    const $fila = $(this).closest(".fila-producto");
    const nombreProducto = $fila.find("td:eq(1)").text().trim();
    alertify.confirm(
      "Confirmación",
      `¿Estás seguro de que deseas eliminar el producto: ${nombreProducto}?`,
      function () {
        $fila.remove();
        actualizarMensajeTablaVacia();
        actualizarSumaTotal();
        alertify.success("Producto eliminado");
      },
      function () {
        alertify.error("Eliminación cancelada");
      }
    );
  });

  // Cambios en cantidad y precio
  $(document).on("input", ".cantidad", function () {
    const $fila = $(this).closest("tr");
    validarCantidadVsExistencia($fila, false);
    actualizarTotalPorFila($fila, false);
    actualizarSumaTotal();
  });
  $(document).on("blur", ".cantidad", function () {
    const $fila = $(this).closest("tr");
    validarCantidadVsExistencia($fila, true); // mensaje OK
    actualizarTotalPorFila($fila, false);
    actualizarSumaTotal();
  });
  $(document).on("input", ".precio", function () {
    const $fila = $(this).closest("tr");
    actualizarTotalPorFila($fila, false);
    actualizarSumaTotal();
  });
  $(document).on("blur", ".precio", function () {
    const $fila = $(this).closest("tr");
    actualizarTotalPorFila($fila, true); // valida min
    actualizarSumaTotal();
  });

  // Click en “agregar” del modal
  $(document).on("click", ".btn-agregar", function () {
    const $icono = $(this);
    const fila = $("<tr>")
      .data("id", $icono.data("id"))
      .data("clave", $icono.data("clave"))
      .data("nombre", $icono.data("nombre"))
      .data("precio_venta", $icono.data("precio_venta"))
      .data("precio_minimo", $icono.data("precio_minimo"))
      .data("excento", $icono.data("excento"))
      .data("existencia", $icono.data("existencia"));
    agregarProducto(fila);
  });

  // Evitar submit con Enter fuera de textarea
  document
    .getElementById("formPedidos")
    .addEventListener("keydown", function (event) {
      if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
        event.preventDefault();
      }
    });
});
