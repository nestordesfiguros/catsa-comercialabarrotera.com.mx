/* global $ */
$(function () {
  const $form = $("#formCSD");
  const EMPRESA_ID = parseInt($form.data("empresa-id"), 10) || 0;
  const $cer = $("#cer");
  const $key = $("#key");
  const $pwd = $("#pwd");
  const $msg = $("#csdMsg");

  const $estadoCER = $("#estadoCER");
  const $estadoKEY = $("#estadoKEY");
  const $estadoActivo = $("#estadoActivo");

  // ===== Helpers de UI =====
  function badge(val) {
    if (val === "—") return '<span class="text-muted">—</span>';
    return val
      ? '<span class="badge bg-success">Sí</span>'
      : '<span class="badge bg-danger">No</span>';
  }
  function paintEstado(data) {
    const cer = !!data.cer;
    const key = !!data.key;
    const activo = !!data.activo;
    $estadoCER.html(badge(cer));
    $estadoKEY.html(badge(key));
    $estadoActivo.html(badge(activo));
  }
  function cleanMsg() {
    $msg.removeClass().empty();
  }
  function showOK(text) {
    $msg.removeClass().addClass("alert alert-success").html(text);
  }
  function showErr(text) {
    $msg.removeClass().addClass("alert alert-danger").html(text);
  }

  // ===== Toggle de contraseña =====
  $("#btnTogglePwd").on("click", function () {
    const isPass = $pwd.attr("type") === "password";
    $pwd.attr("type", isPass ? "text" : "password");
    $("#icoEye").toggleClass("fa-eye fa-eye-slash");
  });

  // ===== jQuery Validate (usar extension, no MIME) =====
  const validator = $form.validate({
    ignore: [],
    rules: {
      cer: { required: true, extension: "cer" },
      key: { required: true, extension: "key" },
      pwd: { required: true },
    },
    messages: {
      cer: {
        required: "Sube el archivo .cer",
        extension: "Debe ser un archivo .cer",
      },
      key: {
        required: "Sube la llave .key",
        extension: "Debe ser un archivo .key",
      },
      pwd: { required: "Escribe la contraseña del CSD" },
    },
    errorClass: "is-invalid",
    validClass: "is-valid",
    errorPlacement: function (err, el) {
      // Muestra mensaje debajo del control
      err.addClass("invalid-feedback");
      if ($(el).closest(".input-group").length) {
        $(el).closest(".input-group").after(err);
      } else {
        $(el).after(err);
      }
    },
  });

  // ===== Estado actual al cargar =====
  function cargarEstado() {
    $estadoCER.html(badge("—"));
    $estadoKEY.html(badge("—"));
    $estadoActivo.html(badge("—"));
    $.post("ajax/empresas/estado-csd.php", { id_empresa: EMPRESA_ID })
      .done(function (r) {
        if (r && r.ok) paintEstado(r);
        else paintEstado({ cer: 0, key: 0, activo: 0 });
      })
      .fail(function () {
        paintEstado({ cer: 0, key: 0, activo: 0 });
      });
  }
  cargarEstado();

  // ===== Probar CSD (no guarda) =====
  $("#btnProbar").on("click", function () {
    cleanMsg();
    if (!validator.form()) return;

    const fd = new FormData($form[0]);
    $.ajax({
      url: "ajax/empresas/probar-csd.php",
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      dataType: "json",
    })
      .done(function (r) {
        if (r && r.ok) {
          showOK(r.msg || "CSD válido.");
        } else {
          showErr(r && r.msg ? r.msg : "No se pudo validar el CSD.");
        }
      })
      .fail(function () {
        showErr("Fallo de red/servidor al probar CSD.");
      });
  });

  // ===== Instalar CSD (guarda y activa) =====
  $("#btnInstalar").on("click", function () {
    cleanMsg();
    if (!validator.form()) return;

    const fd = new FormData($form[0]);
    $.ajax({
      url: "ajax/empresas/instalar-csd.php",
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      dataType: "json",
    })
      .done(function (r) {
        if (r && r.ok) {
          showOK(r.msg || "CSD instalado y activado.");
          // Limpiar campos de archivo y password
          $cer.val("");
          $key.val("");
          $pwd.val("").removeClass("is-valid is-invalid");
          $cer.removeClass("is-valid is-invalid");
          $key.removeClass("is-valid is-invalid");
          cargarEstado();
        } else {
          showErr(r && r.msg ? r.msg : "No se pudo instalar el CSD.");
        }
      })
      .fail(function () {
        showErr("Fallo de red/servidor al instalar CSD.");
      });
  });
});
