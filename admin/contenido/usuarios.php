<!-- usuarios.php -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Usuarios</li>
        </ol>
    </nav>
</div>
<!-- Main content -->
<section class="content">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between">
                            <div class="col-6">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a href="usuarios-altas" class="nav-link text-dark active"><i class="fa fa-plus"></i> Agregar </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-6 d-flex justify-content-end">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <div class="input-group rounded">
                                            <input type="search" id="search" class="form-control rounded mt-3 ms-3" placeholder=" Buscar" aria-label="Buscar" />
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tblUsuarios" class="table table-bordered table-striped">
                            <thead class="table-secondary">
                                <tr>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Correo</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Contraseña</th>
                                    <th class="text-center">Empresas</th>
                                    <th class="text-center">Rol</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $con = "SELECT 
                                    usuarios.id AS idUsr
                                    , usuarios.usr
                                    , usuarios.nombre 
                                    , usuarios.estatus
                                    , usuarios.rol AS idRol
                                    , roles.nombre AS nombreRol  
                                    FROM
                                    usuarios
                                    LEFT JOIN roles 
                                        ON usuarios.rol = roles.id 
                                    WHERE usuarios.estatus = 1 ";

                                $rs = $clsConsulta->consultaGeneral($con);
                                if ($clsConsulta->numrows > 0) {
                                    foreach ($rs as $v => $val) {
                                        //  $usuario = $val['nombre'] . ' ' . $val['apellido1'] . ' ' . $val['apellido2'];
                                        $usuario = htmlspecialchars($val['nombre'] ?? '');
                                        $usuarioID = $val['idUsr'];
                                        //$id_personal = $val['id_personal'];
                                        $correo = $val['usr'];
                                        //$nombre_rol=$val['nombre_rol'];
                                        $estatus = ($val['estatus'] == 1) ? '<b class="text-success">ACTIVO</b>' : '<b class="text-danger">INACTIVO</b>';
                                ?>
                                        <tr>
                                            <td class="dt-control text-start"><?php echo $usuario; ?></td>
                                            <td><?php echo $correo; ?></td>
                                            <td class="text-center"><?php echo $estatus; ?></td>

                                            <td class="text-center"><a href="usuarios-pwd/<?PHP echo $usuarioID; ?>" class="link-info"> <i class="fas fa-unlock-alt fa-lg"></i> &nbsp; &nbsp; </a></td>

                                            <!-- Empresas -->
                                            <td class="text-center">
                                                <a href="usuarios-asignar-empresas/<?= $usuarioID ?>" class="text-info">
                                                    <i class="fas fa-building fa-lg" data-bs-toggle="tooltip" title="Asignar empresas"></i>
                                                </a>
                                            </td>

                                            <!-- Roles -->
                                            <td class="text-center rol-col" data-id="<?= (int)$val['idUsr'] ?>">
                                                <?php
                                                $rolId   = (int)($val['idRol'] ?? 0);
                                                $rolName = trim($val['nombreRol'] ?? '');
                                                $texto   = $rolId > 0 ? htmlspecialchars($rolName) : 'No Asignado';
                                                $clase   = $rolId > 0 ? 'text-info' : 'text-danger fw-semibold';
                                                ?>
                                                <span class="cambiarRol <?= $clase ?>" style="cursor:pointer"
                                                    data-id="<?= (int)$val['idUsr'] ?>"
                                                    data-idrol="<?= $rolId ?>"
                                                    data-nombrerol="<?= htmlspecialchars($rolName) ?>"
                                                    data-bs-toggle="modal" data-bs-target="#modalCambioRol">
                                                    <?= $texto ?>
                                                </span>
                                            </td>


                                            <td class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="editar(<?php echo $usuarioID; ?>);"></i> </td>

                                            <td class="text-center"><a data-bs-toggle="modal" data-bs-target="#borrarUsuario" style="cursor:pointer" class="link-danger" onclick="borrar(<?php echo $usuarioID; ?>);"> &nbsp; &nbsp; <i class="far fa-trash-alt fa-lg"></i> &nbsp; &nbsp; </a></td>
                                        </tr>
                                <?PHP
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /.container-fluid -->
</section>

<!-- Modal Cambio de Rol -->
<div class="modal fade" id="modalCambioRol" tabindex="-1" aria-labelledby="modalCambioRolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formCambioRol">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambioRolLabel">Cambiar Rol de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- corregido ID: de idUsuario a usuarioID -->
                    <input type="hidden" id="usuarioID" name="usuarioID">
                    <div class="mb-3">
                        <label for="nuevoRol" class="form-label">Nuevo Rol</label>
                        <!-- corregido ID: de rolNuevo a nuevoRol -->
                        <select class="form-select" name="nuevoRol" id="nuevoRol" required>
                            <option value="">Seleccione un rol</option>
                            <?php
                            $roles = $clsConsulta->consultaGeneral("SELECT id, nombre FROM roles ORDER BY nombre");
                            foreach ($roles as $rol) {
                                echo "<option value='{$rol['id']}'>{$rol['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>




<!--MODAL MODIFICAR -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div id="usrEditar"></div>
        </div>

    </div>
</div>

<!--MODAL ALTAS -->
<div class="modal fade" id="modalaltas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Alta usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="formUsuariosAltas" action=" ">
                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="nombre" class="form-control" name="nombre" list="listausuarios" autocomplete="off">
                        <datalist id="listausuarios">
                            <?php
                            $rs = $clsConsulta->consultaGeneral("SELECT * FROM cat_personal");
                            foreach ($rs as $v => $val) {
                                echo '<option value="' . $val['nombre'] . ' ' . $val['apellido1'] . ' ' . $val['apellido2'] . '">';
                            }
                            ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" id="usr" class="form-control minusculas" name="usr">
                    </div>

                    <!-- <div class="form-group mt-4">
                        <label>Puesto</label>
                        <select id="puesto" name="puesto" class="form-control validate[required]">
                            <option value="">Selecciona un Puesto</option>
                            <?php
                            /*
                            $con = "SELECT * FROM cat_puestos WHERE estatus = 1 ORDER BY puesto ASC";
                            $rs = $clsConsulta->consultaGeneral($con);
                            foreach ($rs as $v => $val) {
                                echo '<option value="' . $val['id'] . '">' . $val['puesto'] . '</option>';
                            }
                                */
                            ?>

                        </select>
                    </div> -->

                </div> <!-- /. Modal Body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!--MODAL BORRAR -->
<div class="modal fade" id="borrarUsuario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Borrar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>&iquest; Esta seguro de que quiere borrar este usuario ?</h5>
            </div> <!-- /. Modal Body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" onclick="borrarusr()" class="btn btn-primary">Borrar</button>
                <input type="hidden" name="fin" value="borrar">
                <input type="hidden" name="id" id="idusr">
            </div>

        </div>
    </div>
</div>



<!-- page script -->
<script type="text/javascript">
    function borrar(id) {
        $("#idusr").val(id);
    }

    function borrarusr() {
        const id = $("#idusr").val();
        $.ajax({
            type: "POST",
            url: "ajax/usuarios/borrar.php",
            data: {
                id: id
            },
            success: function(data) {
                // console.log(data);
                location.href = "usuarios";
            }
        });
    }

    function editar(id) {
        $.ajax({
            type: "POST",
            url: "ajax/usuarios/editar.php",
            data: {
                id: id
            },
            success: function(data) {
                //      console.log(data);
                $("#usrEditar").html(data);
            }
        });
    }

    $(document).ready(function() {


        $('#tblUsuarios').DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            columnDefs: [{
                    className: 'dt-control',
                    targets: 0
                },
                {
                    orderable: false,
                    targets: [2, 3, 4, 5, 6, 7]
                }
            ],
            order: [
                [0, 'asc']
            ], // ✅ ordena por Nombre (columna 0)
            pageLength: 10,
            dom: "<'row'<'col-sm-2'l><'col-sm-10'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-2'l><'col-sm-8'i><'col-sm-2'p>>",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }
        });

        var oTable = $('#tblUsuarios').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                if (regexp.constructor != RegExp)
                    regexp = new RegExp(regexp);
                else if (regexp.global)
                    regexp.lastIndex = 0;
                return this.optional(element) || regexp.test(value);
            },
            "Please check your input."
        );

        // Roles 
        // Abrir modal y preseleccionar rol actual
        $(document).on('click', '.cambiarRol', function() {
            const usuarioId = $(this).data('id');
            const rolIdActual = Number($(this).data('idrol')) || 0; // 0 si no asignado

            $('#usuarioID').val(usuarioId);
            cargarRoles(rolIdActual); // llena el <select> y preselecciona si aplica
        });

        function cargarRoles(rolIdActual) {
            $.ajax({
                url: 'ajax/usuarios/lista-roles.php',
                method: 'POST'
            }).done(function(res) {
                try {
                    const roles = JSON.parse(res);
                    const $select = $('#nuevoRol');
                    $select.empty().append('<option value="">Seleccione un rol</option>');
                    roles.forEach(r => {
                        const selected = (Number(r.id) === Number(rolIdActual)) ? 'selected' : '';
                        $select.append(`<option value="${r.id}" ${selected}>${r.nombre}</option>`);
                    });
                } catch (e) {
                    alertify.error('Error al cargar roles');
                    console.error(e, res);
                }
            }).fail(function() {
                alertify.error('No se pudo cargar roles');
            });
        }

        // Guardar cambio de rol
        $('#formCambioRol').submit(function(e) {
            e.preventDefault();

            const id = $('#usuarioID').val();
            const rolNuevo = $('#nuevoRol').val();

            if (!rolNuevo) {
                alertify.error('Selecciona un rol');
                return;
            }

            $.ajax({
                url: 'ajax/usuarios/cambiar-rol.php',
                method: 'POST',
                data: {
                    id,
                    rolNuevo
                }
            }).done(function(res) {
                try {
                    const json = JSON.parse(res);
                    if (json.success) {
                        alertify.success(json.message);

                        // Texto del rol y refresco de data-atributos en la tabla
                        const nuevoTexto = $('#nuevoRol option:selected').text();
                        const $celda = $(`.rol-col[data-id="${id}"] .cambiarRol`);

                        $celda
                            .text(nuevoTexto || 'No Asignado')
                            .attr('data-idrol', rolNuevo)
                            .attr('data-nombrerol', nuevoTexto || '')
                            .removeClass('text-danger fw-semibold text-info')
                            .addClass(nuevoTexto ? 'text-info' : 'text-danger fw-semibold');

                        // Cerrar modal (BS5 o fallback jQuery)
                        if (window.bootstrap?.Modal) {
                            bootstrap.Modal.getInstance(document.getElementById('modalCambioRol')).hide();
                        } else {
                            $('#modalCambioRol').modal('hide');
                        }
                    } else {
                        alertify.error(json.message || 'No se pudo actualizar el rol');
                    }
                } catch (e) {
                    alertify.error('Error inesperado');
                    console.error(e, res);
                }
            }).fail(function() {
                alertify.error('No se pudo actualizar el rol');
            });
        });


        $("#formUsuariosAltas").validate({
            rules: {
                nombre: {
                    required: true,
                    minlength: 3
                    // regex: /^[a-zA-Z\s]*$/
                },
                apellido1: {
                    required: true,
                    minlength: 5,
                    // regex: /^[a-zA-Z\s]*$/
                },
                puesto: {
                    required: true,
                },
                usr: {
                    required: true,
                    email: true,
                    maxlength: 50,
                    remote: {
                        url: "ajax/usuarios/verificacorreo.php",
                        type: 'POST',
                        data: {
                            correo: function() {
                                return $('#usr').val();
                            }
                        },
                        dataFilter: function(data) {
                            console.log(data);
                            var json = JSON.parse(data);
                            if (json.existe == "true" || json.existe == true) {
                                return false;
                            } else {
                                return true;
                            }
                        }
                    }
                }
            },
            messages: {
                nombre: {
                    required: "Escribe un nombre",
                    regex: "Solo acepta letras y espacios"
                },
                apellido1: {
                    required: "Escribe un Apellido",
                    minlength: "Escriba más de 5 caracteres",
                    regex: "Solo acepta letras y espacios"
                },
                puesto: {
                    required: "Selecciona un puesto"
                },
                usr: {
                    required: "Escribe un correo",
                    email: "Tu correo necesita un formato nombre@dominio.com",
                    maxlength: "El tamaño máximo es de 50 caracteres",
                    remote: "El correo ya existe"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.addClass('text-danger');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('text-danger d-flex justify-content-end');
            },

            submitHandler: function(form) {
                //submit form
                event.preventDefault();
                var datos = $("#formUsuariosAltas").serialize();


                alertify.confirm('Aviso..!', '¿Deseas Guardar los cambios?',
                    function() {
                        //    alertify.success('Ok')                         
                        $.ajax({
                            type: "POST",
                            url: "ajax/usuarios/guardausuarios.php",
                            data: datos,
                            success: function(data) {
                                console.log(data);
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    location.reload();
                                    // Acatualiza la tabla                                    
                                    // var table = $('#usuarios').DataTable();
                                    // table.ajax.reload(function(json) {
                                    //     $('#usuarios').val(json.lastInput);
                                    // });
                                }
                            }
                        }); /* End Ajax */

                    },
                    function() {
                        alertify.error('Cancel')
                    }
                );

            }
        });


    });
</script>