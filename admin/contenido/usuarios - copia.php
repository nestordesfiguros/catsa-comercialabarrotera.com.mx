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
                                        <a style="cursor:pointer" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalaltas"><i class="fa fa-plus"></i> Agregar </a>
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
                        <table id="usuarios" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th class="text-center">Estatus</th>
                                    <!-- <th class="text-center">Tipo</th> -->
                                    <th class="text-center">Contraseña</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?PHP
                                // $con="SELECT
                                //     usuarios.*
                                //     , usuarios.id as id_usuario
                                //     , roles_nombres.nombre nombre_rol
                                //     , roles_nombres.id
                                // FROM
                                //     usuarios
                                //     INNER JOIN roles_nombres 
                                //         ON (usuarios.rol = roles_nombres.id)";
                                $con = "SELECT * FROM usuarios WHERE estatus=1";
                                $rs = $clsConsulta->consultaGeneral($con);
                                foreach ($rs as $v => $val) {
                                    $usuario = $val['nombre'] . ' ' . $val['apellido1'] . ' ' . $val['apellido2'];
                                    $usuarioID = $val['id'];
                                    $correo = $val['usr'];
                                    //$nombre_rol=$val['nombre_rol'];
                                    $estatus = ($val['estatus'] == 1) ? '<b class="text-success">ACTIVO</b>' : '<b class="text-danger">INACTIVO</b>';
                                ?>
                                    <tr>
                                        <td><?PHP echo $usuario; ?></td>
                                        <td><?PHP echo $correo; ?></td>
                                        <td class="text-center"><?PHP echo $estatus; ?></td>
                                        <!-- <td class="text-center"><?php // echo $nombre_rol; 
                                                                        ?></td> -->
                                        <td class="text-center"><a href="usuarios-pwd/<?PHP echo $usuarioID; ?>" class="link-info"> <i class="fas fa-unlock-alt fa-lg"></i> &nbsp; &nbsp; </a></td>
                                        <td class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="editar(<?php echo $usuarioID; ?>);"></i> </td>
                                        <td class="text-center"><a data-bs-toggle="modal" data-bs-target="#borrarUsuario" style="cursor:pointer" class="link-danger" onclick="borrar(<?php echo $usuarioID; ?>);"> &nbsp; &nbsp; <i class="far fa-trash-alt fa-lg"></i> &nbsp; &nbsp; </a></td>
                                    </tr>
                                <?PHP
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
            <form method="post" id="formUsuarios" action=" ">
                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="nombre" class="form-control" name="nombre">
                    </div>

                    <div class="form-group mt-4">
                        <label>Apellido1</label>
                        <input type="text" class="form-control " name="apellido1">
                    </div>

                    <div class="form-group mt-4">
                        <label>Apellido2</label>
                        <input type="text" class="form-control " name="apellido2">
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" id="usr" class="form-control minusculas" name="usr">
                    </div>

                    <div class="form-group mt-4">
                        <label>Puesto</label>
                        <select id="rol" name="rol" class="form-control validate[required]">
                            <option value="">Selecciona un Puesto</option>
                            <?php
                            $con = "SELECT * FROM cat_puestos";
                            $rs = $clsConsulta->consultaGeneral($con);
                            foreach ($rs as $v => $val) {
                                echo '<option value="' . $val['id'] . '">' . $val['puesto'] . '</option>';
                            }
                            ?>

                        </select>
                    </div>

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
            <form method="post" action="abc">
                <div class="modal-body">
                    <h5>&iquest; Esta seguro de que quiere borrar este usuario ?</h5>
                </div> <!-- /. Modal Body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" id="guardar" class="btn btn-primary">Borrar</button>
                    <input type="hidden" name="fin" value="borrar">
                    <input type="hidden" name="tabla" value="usuarios">
                    <input type="hidden" name="id" id="idusr">
                    <input type="hidden" name="url" value="usuarios">
                </div>
            </form>

        </div>
    </div>
</div>



<!-- page script -->
<script type="text/javascript">
    function borrar(id) {
        $("#idusr").val(id);
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


        $('#usuarios').dataTable({
            ordering: false,
            pageLength: 25,
            dom: "<'row'<'col-sm-10'l><'col-sm-2'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-10'l><'col-sm-2'p>>",
            initComplete: function(settings, json) {
                $('#custom_length').appendTo('body'); //jQuery for moving elements around
            },
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }

        });

        var oTable = $('#usuarios').DataTable();
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


        $("#formUsuarios").validate({
            rules: {
                nombre: {
                    required: true,
                    minlength: 3,
                    regex: /^[a-zA-Z\s]*$/
                },
                apellido1: {
                    required: true,
                    minlength: 5,
                    regex: /^[a-zA-Z\s]*$/
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
                var datos = $("#formUsuarios").serialize();


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