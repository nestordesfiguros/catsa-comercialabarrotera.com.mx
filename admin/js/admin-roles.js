$(document).ready(function () {
  // Cargar roles al iniciar
  cargarRoles();

  function cargarRoles() {
    $.ajax({
      url: "ajax/roles/lista-roles.php",
      method: "POST",
      success: function (res) {
        try {
          const json = JSON.parse(res);
          const $tbody = $("#tablaRoles tbody");
          $tbody.empty();

          json.roles.forEach((rol) => {
            $tbody.append(`
            <tr>
                <td>${rol.nombre}</td>
                <td><a href="admin-roles-permisos/${rol.id}">Permisos</a></td>
                <td>
                <button class="btn btn-sm btn-danger eliminarRol" data-id="${rol.id}" data-nombre="${rol.nombre}">
                    <i class="fa fa-trash"></i>
                </button>
                </td>
                
            </tr>
            `);
          });
        } catch (e) {
          alertify.error("Error al cargar roles");
          console.error(res);
        }
      },
    });
  }

  // Guardar nuevo rol
  $("#formNuevoRol").on("submit", function (e) {
    e.preventDefault();
    const nombre = $(this).find('input[name="nombre"]').val().trim();
    if (nombre === "") return alertify.error("El nombre no puede estar vacío");

    alertify.confirm(
      "¿Confirmar?",
      `¿Deseas agregar el rol <b>${nombre}</b>?`,
      function () {
        $.ajax({
          url: "ajax/roles/guardar-rol.php",
          method: "POST",
          data: { nombre: nombre },
          success: function (res) {
            try {
              const json = JSON.parse(res);
              if (json.success) {
                alertify.success("Rol agregado");
                $("#formNuevoRol")[0].reset();
                cargarRoles();
              } else {
                alertify.error(json.message || "Error al guardar");
              }
            } catch (e) {
              alertify.error("Error al procesar respuesta");
              console.error(res);
            }
          },
        });
      },
      function () {
        alertify.message("Cancelado");
      }
    );
  });

  $(document).on("click", ".eliminarRol", function () {
    const id = $(this).data("id");
    const nombre = $(this).data("nombre");

    alertify.confirm(
      "Eliminar rol",
      `¿Estás seguro de eliminar el rol <b>${nombre}</b>? Esta acción no se puede deshacer.`,
      function () {
        $.ajax({
          url: "ajax/roles/eliminar-rol.php",
          method: "POST",
          data: { id: id },
          success: function (res) {
            try {
              const json = JSON.parse(res);
              if (json.success) {
                alertify.success("Rol eliminado");
                cargarRoles();
              } else {
                alertify.error(json.message || "No se pudo eliminar");
              }
            } catch (e) {
              alertify.error("Error al procesar respuesta");
              console.error(res);
            }
          },
        });
      },
      function () {
        alertify.message("Cancelado");
      }
    );
  });
});
