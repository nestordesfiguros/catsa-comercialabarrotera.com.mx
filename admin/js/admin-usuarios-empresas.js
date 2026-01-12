$(document).ready(function () {
  // Cargar usuarios al iniciar
  $.ajax({
    url: "ajax/usuarios/lista-usuarios-empresas.php",
    method: "POST",
    data: { accion: "usuarios" },
    success: function (res) {
      try {
        const json = JSON.parse(res);
        const $select = $("#usuarioSelect");
        $select
          .empty()
          .append('<option value="">Seleccione un usuario</option>');
        json.usuarios.forEach((usuario) => {
          $select.append(
            `<option value="${usuario.id}">${usuario.nombre}</option>`
          );
        });
      } catch (e) {
        alertify.error("Error al cargar usuarios");
        console.error(res);
      }
    },
  });

  // Cargar empresas del usuario
  $("#usuarioSelect").on("change", function () {
    const id_usuario = $(this).val();
    if (!id_usuario) return $("#empresasContainer").addClass("d-none");

    $.ajax({
      url: "ajax/usuarios/lista-usuarios-empresas.php",
      method: "POST",
      data: { accion: "empresas", id_usuario: id_usuario },
      success: function (res) {
        try {
          const json = JSON.parse(res);
          const $lista = $("#listaEmpresas");
          $lista.empty();

          json.empresas.forEach((empresa) => {
            const checked = empresa.asignada ? "checked" : "";
            $lista.append(`
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="empresas[]" value="${empresa.id}" id="empresa_${empresa.id}" ${checked}>
                  <label class="form-check-label" for="empresa_${empresa.id}">${empresa.razon_social}</label>
                </div>
              </div>
            `);
          });

          $("#formEmpresas").data("usuario", id_usuario);
          $("#empresasContainer").removeClass("d-none");
        } catch (e) {
          alertify.error("Error al cargar empresas");
          console.error(res);
        }
      },
    });
  });

  // Guardar asignación
  $("#formEmpresas").on("submit", function (e) {
    e.preventDefault();
    const id_usuario = $(this).data("usuario");
    if (!id_usuario) return alertify.error("Seleccione un usuario");

    const formData = $(this).serializeArray();
    formData.push({ name: "id_usuario", value: id_usuario });

    $.ajax({
      url: "ajax/usuarios/guardar-usuarios-empresas.php",
      method: "POST",
      data: formData,
      success: function (res) {
        try {
          const json = JSON.parse(res);
          if (json.success) {
            alertify.success("Empresas asignadas correctamente");
          } else {
            alertify.error("Error al guardar");
          }
        } catch (e) {
          alertify.error("Respuesta inválida del servidor");
          console.error(res);
        }
      },
    });
  });
});
