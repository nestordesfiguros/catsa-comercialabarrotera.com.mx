$(document).ready(function () {
  // Cargar roles al iniciar
  $.ajax({
    url: "ajax/permisos/lista-permisos.php",
    method: "POST",
    data: { accion: "listar_roles" },
    success: function (res) {
      try {
        const json = JSON.parse(res);
        const $select = $("#rolSelect");
        $select.empty().append('<option value="">Seleccione un rol</option>');
        json.roles.forEach((rol) => {
          $select.append(`<option value="${rol.id}">${rol.nombre}</option>`);
        });
      } catch (e) {
        alertify.error("Error al cargar roles");
        console.error(res);
      }
    },
  });

  // Al seleccionar un rol, cargar módulos y permisos
  $("#rolSelect").on("change", function () {
    const rol = $(this).val();
    if (!rol) return $("#tablaPermisosContainer").addClass("d-none");

    $.ajax({
      url: "ajax/permisos/lista-permisos.php",
      method: "POST",
      data: { accion: "listar_permisos", rol: rol },
      success: function (res) {
        try {
          const json = JSON.parse(res);
          const tbody = $("#tablaPermisosBody");
          tbody.empty();

          json.modulos.forEach((modulo) => {
            const p = modulo.permisos;

            // Detectar si es submódulo para aplicar clases Bootstrap 5
            const esSubmodulo =
              modulo.modulo_padre !== null && modulo.modulo_padre != 0;
            const claseNombre = esSubmodulo
              ? "ms-4 fst-italic text-secondary"
              : "";

            tbody.append(`
              <tr>
                <td class="text-start ps-3 ${claseNombre}">${modulo.nombre}</td>
                <td>${modulo.tipo_modulo}</td>
                <td><input type="checkbox" name="permisos[${modulo.id}][ver]" ${
              p.ver ? "checked" : ""
            }></td>
                <td><input type="checkbox" name="permisos[${
                  modulo.id
                }][crear]" ${p.crear ? "checked" : ""}></td>
                <td><input type="checkbox" name="permisos[${
                  modulo.id
                }][editar]" ${p.editar ? "checked" : ""}></td>
                <td><input type="checkbox" name="permisos[${
                  modulo.id
                }][eliminar]" ${p.eliminar ? "checked" : ""}></td>
              </tr>
            `);
          });

          $("#tablaPermisosContainer").removeClass("d-none");
        } catch (e) {
          alertify.error("Error al cargar permisos");
          console.error(res);
        }
      },
    });
  });

  // Guardar cambios
  $("#formPermisos").on("submit", function (e) {
    e.preventDefault();

    alertify.confirm(
      "¿Guardar cambios de permisos?",
      function () {
        const rolId = $("#rolSelect").val();
        if (!rolId) {
          alertify.error("Selecciona un rol");
          return;
        }

        const permisos = [];

        $("#tablaPermisosBody tr").each(function () {
          const fila = $(this);
          const idModulo = fila
            .find("input[type=checkbox]")
            .first()
            .attr("name")
            .match(/\[(\d+)\]/)[1];

          const ver = fila
            .find(`input[name="permisos[${idModulo}][ver]"]`)
            .is(":checked")
            ? 1
            : 0;
          const crear = fila
            .find(`input[name="permisos[${idModulo}][crear]"]`)
            .is(":checked")
            ? 1
            : 0;
          const editar = fila
            .find(`input[name="permisos[${idModulo}][editar]"]`)
            .is(":checked")
            ? 1
            : 0;
          const eliminar = fila
            .find(`input[name="permisos[${idModulo}][eliminar]"]`)
            .is(":checked")
            ? 1
            : 0;

          if (ver || crear || editar || eliminar) {
            permisos.push({
              id_modulo: idModulo,
              ver,
              crear,
              editar,
              eliminar,
            });
          }
        });

        if (permisos.length === 0) {
          alertify.error("Debes seleccionar al menos un permiso");
          return;
        }

        document.getElementById("spinner").style.display = "block";

        $.ajax({
          type: "POST",
          url: "ajax/permisos/guardar-permisos.php",
          contentType: "application/json",
          data: JSON.stringify({
            id_rol: rolId,
            permisos: permisos,
          }),
          success: function (res) {
            document.getElementById("spinner").style.display = "none";

            try {
              const json = typeof res === "string" ? JSON.parse(res) : res;
              if (json.success) {
                alertify.success("Permisos guardados correctamente");
              } else {
                alertify.error(json.message || "Error al guardar");
              }
            } catch (e) {
              console.error(res);
              alertify.error("Error inesperado del servidor");
            }
          },
          error: function (xhr) {
            document.getElementById("spinner").style.display = "none";
            alertify.error("Error al guardar (conexión)");
            console.error(xhr.responseText);
          },
        });
      },
      function () {
        alertify.error("Acción cancelada");
      }
    );
  });

  // Checkbox maestro para cada permiso
  $("#checkAllVer").on("change", function () {
    const checked = $(this).is(":checked");
    $("#tablaPermisosBody input[name*='[ver]']").prop("checked", checked);
  });

  $("#checkAllCrear").on("change", function () {
    const checked = $(this).is(":checked");
    $("#tablaPermisosBody input[name*='[crear]']").prop("checked", checked);
  });

  $("#checkAllEditar").on("change", function () {
    const checked = $(this).is(":checked");
    $("#tablaPermisosBody input[name*='[editar]']").prop("checked", checked);
  });

  $("#checkAllEliminar").on("change", function () {
    const checked = $(this).is(":checked");
    $("#tablaPermisosBody input[name*='[eliminar]']").prop("checked", checked);
  });
});
