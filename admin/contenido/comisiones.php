<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Comisiones</li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-8">
                                <button type="button" class="btn btn-secondary" onclick="fnModalAltas();"><i class="fas fa-plus"></i> Agregar Comisión</button>
                            </div>
                            <div class="col-4">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaComisiones" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">nombre_comision Vendedor</th>
                                    <th class="text-center">Comision</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Altas -->
<div class="modal fade" id="modalAltas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar Comisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalAltas();"></button>
            </div>
            <form method="post" action="" id="formAltas" novalidate>
                <div class="modal-body">
                    <div class="row gy-3">

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="nombre_comision" name="nombre_comision" class="form-control" />
                                <label class="form-label" for="nombre_comision">Nombre de la comisión</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" name="porcentaje" id="porcentaje" class="form-control" />
                                <label class="form-label" for="porcentaje">Porcentaje de Comisión</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalAltas();">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modificar categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalEditar();"></button>

            </div>
            <form method="post" action="" id="formEditar" novalidate>
                <div class="modal-body">
                    <div class="row gy-3">

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="nombre_comisionEditar" name="nombre_comision" class="form-control" />
                                <label class="form-label" for="nombre_comision">Nombre del nombre_comision de cliente</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" name="porcentaje" id="porcentajeEditar" class="form-control" />
                                <label class="form-label" for="porcentaje">% porcentaje</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalEditar();">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <input type="hidden" name="id" id="id">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function fnModalAltas() {
        $("#modalAltas").modal('show');
    }

    function editar(id, nombre_porcentaje, porcentaje) {
        $("#modalEditar").modal('show');
        $("#nombre_comisionEditar").val(nombre_porcentaje);
        $("#porcentajeEditar").val(porcentaje);
        $("#id").val(id);
    }

    function cerrarModalAltas() {
        $("#modalAltas").modal('hide');
    }

    function cerrarModalEditar() {
        $("#modalEditar").modal('toggle');
    }

    function borrar(id, nombre_comision, porcentaje) {
        alertify.confirm('<p class="text-danger">Aviso..!</p>', '¿Deseas Borrar  <b>' + nombre_comision + '</b>?',
            function() {

                $.ajax({
                    type: "POST",
                    url: "ajax/comisiones/borrar.php",
                    data: {
                        "id": id
                    },
                    success: function(data) {
                        console.log('Borrar ::> ' + data);

                        var table = $('#tablaComisiones').DataTable();
                        table.ajax.reload(function(json) {
                            $('#tablaComisiones').val(json.lastInput);
                        });
                    }
                }); /* End ajax */

            },
            function() {
                alertify.error('Cancel')
            }
        ).set('labels', {
            ok: 'Si',
            cancel: 'No'
        });
    }

    $(document).ready(function() {


        $('#tablaComisiones').dataTable({
            ajax: "ajax/comisiones/tabla_comisiones.php",
            ordering: true,
            fixedHeader: false,
            paging: true, // Agregar paginación
            dom: "<'row'<'col-sm-6'l><'col-sm-2 mb-3'><'col-sm-4'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6 mt-3'l><'col-sm-2 mt-3'><'col-sm-4 mt-3'p>>",

            language: {
                url: "assets/datatables/Spanish.json"
            },
            responsive: true
        });

        var oTable = $('#tablaComisiones').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        /* Validacion de formulario   */
        $("#formAltas").validate({
            rules: {
                nombre_comision: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                porcentaje: {
                    required: true,
                    minlength: 1,
                    maxlength: 2
                }
            },
            messages: {
                nombre_comision: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 3",
                    maxlength: "El número máximo de caracteres es 100"
                },
                porcentaje: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 1",
                    maxlength: "El número máximo de caracteres es 2"
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
                //    event.preventDefault();                

                var datos = $("#formAltas").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar la comisión?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/comisiones/guardar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Guardar ::> ' + data);

                                var table = $('#tablaComisiones').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaComisiones').val(json.lastInput);
                                });
                            }
                        }); /* End ajax */
                        $("#modalAltas").modal('toggle');
                    },
                    function() {
                        alertify.error('Cancel')
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });

            }

        });


        $("#formEditar").validate({
            rules: {
                nombre_comision: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                porcentaje: {
                    required: true,
                    minlength: 1,
                    maxlength: 2
                }
            },
            messages: {
                nombre_comision: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 3",
                    maxlength: "El número máximo de caracteres es 100"
                },
                porcentaje: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 1",
                    maxlength: "El número máximo de caracteres es 2"
                }
            },

            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.addClass('btn btn-danger btn-sm text-white');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
            },

            submitHandler: function(form) {
                //submit form
                //    event.preventDefault();
                //   console.log('Entro');

                var datos = $("#formEditar").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar los cambios?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/comisiones/modificar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Modificar ::> ' + data);

                                var table = $('#tablaComisiones').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaComisiones').val(json.lastInput);
                                });
                            }
                        }); /* End ajax */
                        if ($('#modalEditar').is(':visible')) {
                            $("#modalEditar").modal('toggle');
                        }
                    },
                    function() {
                        alertify.error('Cancel')
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });

            }

        });


    });
</script>