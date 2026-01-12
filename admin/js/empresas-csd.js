// js/empresas-csd.js
$(function () {
  const $form = $("#formCSD");
  const empresaId = Number($("#empresa_id").val() || 0);

  // ---- Mostrar / ocultar contraseña
  $("#btnTogglePwd").on("click", function () {
    const $i = $(this).find("i");
    const isPwd = $("#pwd").attr("type") === "password";
    $("#pwd").attr("type", isPwd ? "text" : "password");
    $i.toggleClass("fa-eye fa-eye-slash");
  });

  // ---- Estado inicial (—), no "cargando"
  function setEstado(cer, key, activo, nombreCer, nombreKey) {
    const badge = (ok) => (ok ? "bg-success" : "bg-danger");
    $("#estado-cer")
      .removeClass()
      .addClass("badge " + (cer === null ? "bg-secondary" : badge(cer)))
      .text(cer === null ? "—" : cer ? "Sí" : "No");
    $("#estado-key")
      .removeClass()
      .addClass("badge " + (key === null ? "bg-secondary" : badge(key)))
      .text(key === null ? "—" : key ? "Sí" : "No");
    $("#estado-activo")
      .removeClass()
      .addClass(
        "badge " +
          (activo === null
            ? "bg-secondary"
            : activo
            ? "bg-success"
            : "bg-danger")
      )
      .text(activo === null ? "—" : activo ? "Sí" : "No");

    $("#nombre-cer").text(nombreCer || "—");
    $("#nombre-key").text(nombreKey || "—");
  }
  setEstado(null, null, null); // placeholder

  // ---- Cargar estado real
  function cargarEstado() {
    $.ajax({
      url: "ajax/empresas/estado-csd.php",
      type: "POST",
      dataType: "json",
      data: { id_empresa: empresaId },
    })
      .done(function (r) {
        if (!r || !r.ok) {
          setEstado(false, false, false);
          return;
        }
        setEstado(!!r.cer_ok, !!r.key_ok, !!r.activo, r.cer_name, r.key_name);
      })
      .fail(function () {
        setEstado(false, false, false);
      });
  }
  cargarEstado();

  // ---- validate.js: regla por extensión (sin usar attribute accept)
  if (!$.validator.methods.extension) {
    $.validator.addMethod(
      "extension",
      function (value, element, param) {
        if (this.optional(element)) return true;
        const exts = String(param || "")
          .replace(/\./g, "")
          .split("|");
        const ext = (value.split(".").pop() || "").toLowerCase();
        return exts.indexOf(ext) !== -1;
      },
      "Extensión inválida"
    );
  }

  $form.validate({
    rules: {
      cer: { required: true, extension: "cer" },
      key: { required: true, extension: "key" },
      pwd: { required: true, minlength: 4 },
    },
    messages: {
      cer: {
        required: "Selecciona el archivo .cer",
        extension: "Debe ser .cer",
      },
      key: {
        required: "Selecciona el archivo .key",
        extension: "Debe ser .key",
      },
      pwd: { required: "Escribe la contraseña" },
    },
    errorElement: "span",
    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback text-danger");
      element.closest(".mb-3, .input-group").append(error);
    },
    highlight: (el) => $(el).addClass("is-invalid"),
    unhighlight: (el) => $(el).removeClass("is-invalid").addClass("is-valid"),
    submitHandler: function () {
      instalar();
    },
  });

  // ---- Probar CSD (no persiste)
  $("#btnProbar").on("click", function () {
    if (!$form.valid()) return;
    const fd = new FormData($form[0]);
    $.ajax({
      url: "ajax/empresas/probar-csd.php",
      type: "POST",
      data: fd,
      processData: false,
      contentType: false,
      dataType: "json",
    })
      .done(function (r) {
        if (r && r.ok) alertify.success(r.msg || "CSD válido");
        else alertify.error(r?.msg || "CSD inválido");
      })
      .fail(function () {
        alertify.error("Fallo de red/servidor");
      });
  });

  // ---- Instalar (sube archivos a ajax/timbrado/csd/{empresa}/ y activa)
  function instalar() {
    const fd = new FormData($form[0]);
    alertify.confirm(
      "Confirmar",
      "¿Instalar / actualizar el CSD para esta empresa?",
      function () {
        $.ajax({
          url: "ajax/empresas/guardar-csd.php",
          type: "POST",
          data: fd,
          processData: false,
          contentType: false,
          dataType: "json",
        })
          .done(function (r) {
            if (r && r.ok) {
              alertify.success(r.msg || "CSD instalado");
              cargarEstado();
            } else {
              alertify.error(r?.msg || "No fue posible instalar");
            }
          })
          .fail(function () {
            alertify.error("Fallo de red/servidor");
          });
      },
      function () {}
    );
  }
});
