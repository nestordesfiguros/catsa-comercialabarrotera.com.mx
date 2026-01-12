<?php
// contenido/admin-roles-permisos.php

// Obtener el ID del rol desde $cat (definido en index.php o controlador)
$rolId = isset($cat) ? intval($cat) : 0;

// Validar ID de rol
if ($rolId <= 0) {
    echo '<div class="alert alert-danger">ID de rol inválido.</div>';
    return; // No seguir cargando vista
}

$rs = $clsConsulta->consultaGeneral("SELECT nombre FROM roles WHERE id = $rolId");
if (!is_array($rs) || !isset($rs[1]['nombre'])) {
    echo '<div class="alert alert-danger">Rol no encontrado.</div>';
    return; // No seguir cargando vista
} else {
    $rolNombre = htmlspecialchars($rs[1]['nombre']);
}
?>

<style>
    #btnGuardarPermisos {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        /* Para que no se vea muy pequeño */
        padding: 10px 20px;
        font-size: 1rem;
    }
</style>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="admin-roles">Administrar Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permisos por Rol</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestión de permisos para el rol: <strong> <?= $rolNombre ?></strong></h5>
            </div>

            <div class="card-body">

                <div id="tablaPermisosContainer" class="table-responsive d-none">
                    <form id="formPermisos" autocomplete="off">
                        <input type="hidden" name="id_rol" value="<?= $rolId ?>">

                        <table class="table table-bordered align-middle text-center mb-5">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Tipo</th>
                                    <th>
                                        Ver <br>
                                        <input type="checkbox" id="checkAllVer">
                                    </th>
                                    <th>
                                        Crear <br>
                                        <input type="checkbox" id="checkAllCrear">
                                    </th>
                                    <th>
                                        Editar <br>
                                        <input type="checkbox" id="checkAllEditar">
                                    </th>
                                    <th>
                                        Eliminar <br>
                                        <input type="checkbox" id="checkAllEliminar">
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tablaPermisosBody">
                                <!-- Se llena dinámicamente con JS -->
                            </tbody>
                        </table>

                        <!-- Botón guardado ahora fijo -->
                        <button type="submit" id="btnGuardarPermisos" class="btn btn-success">
                            Guardar permisos
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</section>

<script>
    $(document).ready(function() {
        const rolId = $('#formPermisos input[name="id_rol"]').val();

        function cargarPermisos(rol) {
            $.ajax({
                url: "ajax/permisos/lista-permisos.php",
                method: "POST",
                data: {
                    accion: "listar_permisos",
                    rol: rol
                },
                success: function(res) {
                    try {
                        const json = JSON.parse(res);
                        const tbody = $("#tablaPermisosBody");
                        tbody.empty();

                        json.modulos.forEach((modulo) => {
                            const p = modulo.permisos;

                            const esSubmodulo =
                                modulo.modulo_padre !== null && modulo.modulo_padre != 0;
                            const claseNombre = esSubmodulo ?
                                "ms-4 fst-italic text-secondary" :
                                "";

                            tbody.append(`
              <tr>
                <td class="text-start ps-3 ${claseNombre}">${modulo.nombre}</td>
                <td>${modulo.tipo_modulo || ''}</td>
                <td><input type="checkbox" name="permisos[${modulo.id}][ver]" ${p.ver ? "checked" : ""}></td>
                <td><input type="checkbox" name="permisos[${modulo.id}][crear]" ${p.crear ? "checked" : ""}></td>
                <td><input type="checkbox" name="permisos[${modulo.id}][editar]" ${p.editar ? "checked" : ""}></td>
                <td><input type="checkbox" name="permisos[${modulo.id}][eliminar]" ${p.eliminar ? "checked" : ""}></td>
              </tr>
            `);
                        });

                        $("#tablaPermisosContainer").removeClass("d-none");
                    } catch (e) {
                        alertify.error("Error al cargar permisos");
                        console.error(res);
                    }
                },
                error: function() {
                    alertify.error("Error en la petición AJAX");
                }
            });
        }

        // Cargar los permisos del rol al cargar la página
        cargarPermisos(rolId);

        // Checkboxes maestros para seleccionar/deseleccionar todo
        $("#checkAllVer").on("change", function() {
            const checked = $(this).is(":checked");
            $("#tablaPermisosBody input[name*='[ver]']").prop("checked", checked);
        });

        $("#checkAllCrear").on("change", function() {
            const checked = $(this).is(":checked");
            $("#tablaPermisosBody input[name*='[crear]']").prop("checked", checked);
        });

        $("#checkAllEditar").on("change", function() {
            const checked = $(this).is(":checked");
            $("#tablaPermisosBody input[name*='[editar]']").prop("checked", checked);
        });

        $("#checkAllEliminar").on("change", function() {
            const checked = $(this).is(":checked");
            $("#tablaPermisosBody input[name*='[eliminar]']").prop("checked", checked);
        });

        // Guardar permisos
        $("#formPermisos").on("submit", function(e) {
            e.preventDefault();

            // Validar que haya al menos un permiso marcado
            const algunPermiso = $("#tablaPermisosBody input[type='checkbox']:checked").length > 0;
            if (!algunPermiso) {
                alertify.error("Debes seleccionar al menos un permiso.");
                return;
            }

            alertify.confirm(
                "¿Guardar cambios de permisos?",
                function() {
                    // Mostrar spinner
                    document.getElementById("spinner").style.display = "block";

                    $.ajax({
                        type: "POST",
                        url: "ajax/permisos/guardar-permisos-rol.php",
                        data: $("#formPermisos").serialize(),
                        success: function(res) {
                            document.getElementById("spinner").style.display = "none";

                            try {
                                const json = typeof res === "string" ? JSON.parse(res) : res;
                                if (json.success) {
                                    alertify.success("Permisos guardados correctamente");
                                } else {
                                    alertify.error(json.message || "Error al guardar permisos");
                                }
                            } catch (e) {
                                alertify.error("Error inesperado al procesar la respuesta");
                                console.error(res);
                            }
                        },
                        error: function() {
                            document.getElementById("spinner").style.display = "none";
                            alertify.error("Error en la petición AJAX");
                        }
                    });
                },
                function() {
                    alertify.message("Acción cancelada");
                }
            );
        });
    });
</script>