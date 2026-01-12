// js/empresas-form.js
$(function () {
  const $form = $("#formEmpresa");

  function parseIdFromPath() {
    const uri = (location.pathname || "") + (location.search || "");
    const m = uri.match(/empresas-(?:editar|timbrado)\/(\d+)/i);
    return m ? parseInt(m[1], 10) : 0;
  }
  function v(x) {
    return x == null ? "" : String(x);
  }

  // ===== ID ROBUSTO: URL > hidden > 0 =====
  let empresaId = parseIdFromPath();
  if (!(empresaId > 0)) {
    empresaId = Number($("#empresa_id").val() || 0);
  }
  if (empresaId > 0) {
    $("#empresa_id").val(empresaId); // fuerza el hidden a lo que dice la URL
  }

  function cargarMunicipios(idEstado, seleccionado) {
    if (!idEstado) {
      $("#id_municipio")
        .empty()
        .append('<option value="">Selecciona...</option>');
      return;
    }
    $.ajax({
      url: "ajax/empresas/catalogos.php",
      type: "POST",
      dataType: "json",
      data: { tipo: "municipios", id_estado: idEstado },
    }).done(function (r) {
      const $mun = $("#id_municipio");
      $mun.empty().append('<option value="">Selecciona...</option>');
      (r.municipios || []).forEach((m) =>
        $mun.append(`<option value="${m.id_municipio}">${m.nombre}</option>`)
      );
      if (seleccionado) $mun.val(String(seleccionado));
    });
  }

  function cargarEmpresa(id) {
    if (id <= 0) return;
    $.ajax({
      url: "ajax/empresas/obtener.php",
      type: "POST",
      dataType: "json",
      data: { id: id },
    })
      .done(function (r) {
        if (!r || !r.ok) {
          alertify.error(r?.msg || "No encontrado");
          return;
        }
        const d = r.data || {};
        $("#razon_social").val(v(d.razon_social));
        $("#nombre_comercial").val(v(d.nombre_comercial));
        $("#rfc").val(v(d.rfc));
        $("#correo").val(v(d.correo));
        $("#calle").val(v(d.calle));
        $("#num_ext").val(v(d.num_ext));
        $("#num_int").val(v(d.num_int));
        $("#cp").val(v(d.cp));
        $("#colonia").val(v(d.colonia));
        $("#tel1").val(v(d.tel1));
        $("#tel2").val(v(d.tel2));
        if (d.id_estado) $("#id_estado").val(String(d.id_estado));
        if (d.regimen_fiscal)
          $("#regimen_fiscal").val(String(d.regimen_fiscal));
        if (d.id_estado) cargarMunicipios(d.id_estado, d.id_municipio || "");
      })
      .fail(function () {
        alertify.error("Fallo de red/servidor");
      });
  }

  // Cargar catálogos primero
  $.ajax({
    url: "ajax/empresas/catalogos.php",
    type: "POST",
    dataType: "json",
    data: { tipo: "estados_y_regimen" },
  }).done(function (r) {
    if (!r || !r.ok) return;
    const $est = $("#id_estado")
      .empty()
      .append('<option value="">Selecciona...</option>');
    (r.estados || []).forEach((e) =>
      $est.append(`<option value="${e.id_estado}">${e.nombre}</option>`)
    );

    const $reg = $("#regimen_fiscal")
      .empty()
      .append('<option value="">Selecciona...</option>');
    (r.regimenes || []).forEach((x) =>
      $reg.append(
        `<option value="${x.codigo}">${x.codigo} - ${x.descripcion}</option>`
      )
    );

    if (empresaId > 0) cargarEmpresa(empresaId);
  });

  $("#id_estado").on("change", function () {
    cargarMunicipios($(this).val());
  });

  // ===== VALIDATE.JS =====
  $.validator.addMethod(
    "regex",
    function (value, element, regexp) {
      if (regexp.constructor !== RegExp) regexp = new RegExp(regexp);
      else if (regexp.global) regexp.lastIndex = 0;
      return this.optional(element) || regexp.test(value);
    },
    "Formato inválido"
  );

  $form.validate({
    rules: {
      razon_social: { required: true, maxlength: 250 },
      rfc: { required: true, minlength: 12, maxlength: 13 },
      correo: {
        email: true,
        maxlength: 100,
        regex: /^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,}$/,
      },
      regimen_fiscal: { required: true },
      cp: { digits: true, maxlength: 5 },
    },
    messages: {
      razon_social: { required: "Escribe la razón social" },
      rfc: { required: "Escribe el RFC" },
      regimen_fiscal: { required: "Selecciona el régimen fiscal" },
    },
    errorElement: "span",
    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback text-danger");
      element
        .closest(".form-group, .mb-3, .col-md-4, .col-md-6, .col-md-12")
        .append(error);
    },
    highlight: (el) => $(el).addClass("is-invalid"),
    unhighlight: (el) => $(el).removeClass("is-invalid").addClass("is-valid"),
    submitHandler: function () {
      const isEdit = empresaId > 0;
      const url = isEdit
        ? "ajax/empresas/actualizar.php"
        : "ajax/empresas/guardar.php";
      if (isEdit) {
        if ($("#empresa_id").length === 0)
          $form.prepend(
            `<input type="hidden" id="empresa_id" name="id" value="${empresaId}">`
          );
        else $("#empresa_id").val(empresaId);
      }
      alertify.confirm(
        "Confirmar",
        "¿Deseas guardar los cambios?",
        function () {
          $.ajax({
            url,
            type: "POST",
            dataType: "json",
            data: $form.serialize(),
          })
            .done(function (r) {
              if (r && r.ok) {
                // 👉 Regresar siempre a empresas
                location.href = "empresas";
              } else {
                alertify.error(r?.msg || "No fue posible guardar");
              }
            })
            .fail(function () {
              alertify.error("Fallo de red/servidor");
            });
        },
        function () {}
      );
    },
  });
});
