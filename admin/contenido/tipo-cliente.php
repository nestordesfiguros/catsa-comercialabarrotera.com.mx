<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Tipo de Cliente</li>
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
                                <button type="button" class="btn btn-info" onclick="fnModalAltas();"><i class="fas fa-plus"></i> Agregar Tipo</button>
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
                        <table id="tablaTipoCliente" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Descuento</th>
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
                <h5 class="modal-title" id="exampleModalLabel">Agregar tipo de cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalAltas();"></button>
            </div>
            <form method="post" action="" id="formAltas" novalidate>
                <div class="modal-body">
                    <div class="row gy-3">

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="tipo" name="tipo" class="form-control" />
                                <label class="form-label" for="tipo">Nombre del tipo de cliente</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" name="descuento" id="descuento" class="form-control" />
                                <label class="form-label" for="descuento">% Descuento</label>
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
                                <input type="text" id="tipoEditar" name="tipo" class="form-control" />
                                <label class="form-label" for="tipo">Nombre del tipo de cliente</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" name="descuento" id="descuentoEditar" class="form-control" />
                                <label class="form-label" for="descuento">% Descuento</label>
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

    function editar(id, tipo, descuento) {
        $("#modalEditar").modal('show');
        $("#tipoEditar").val(tipo);
        $("#descuentoEditar").val(descuento);
        $("#id").val(id);
    }

    function cerrarModalAltas() {
        $("#modalAltas").modal('hide');
    }

    function cerrarModalEditar() {
        $("#modalEditar").modal('toggle');
    }

    function borrar(id, tipo, descuento) {
        alertify.confirm('<p class="text-danger">Aviso..!</p>', '¿Deseas Borrar el tipo de cliente <b>' + tipo + '</b>?',
            function() {

                $.ajax({
                    type: "POST",
                    url: "ajax/tipoCliente/borrar.php",
                    data: {
                        "id": id
                    },
                    success: function(data) {
                        console.log('Borrar ::> ' + data);

                        var table = $('#tablaTipoCliente').DataTable();
                        table.ajax.reload(function(json) {
                            $('#tablaTipoCliente').val(json.lastInput);
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


        $('#tablaTipoCliente').dataTable({
            ajax: "ajax/tipoCliente/tabla_tipo_cliente.php",
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

        var oTable = $('#tablaTipoCliente').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        /* Validacion de formulario   */
        $("#formAltas").validate({
            rules: {
                tipo: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                descuento: {
                    required: true,
                    minlength: 1,
                    maxlength: 2
                }
            },
            messages: {
                tipo: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 3",
                    maxlength: "El número máximo de caracteres es 100"
                },
                descuento: {
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

                alertify.confirm('Aviso..!', '¿Deseas guardar la categoría?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/tipoCliente/guardar.php",
                            data: datos,
                            success: function(data) {
                                // console.log('Guardar ::> ' + data);

                                var table = $('#tablaTipoCliente').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaTipoCliente').val(json.lastInput);
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
                tipo: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                descuento: {
                    required: true,
                    minlength: 1,
                    maxlength: 2
                }
            },
            messages: {
                tipo: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 3",
                    maxlength: "El número máximo de caracteres es 100"
                },
                descuento: {
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

                alertify.confirm('Aviso..!', '¿Deseas modificar la categoría?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/tipoCliente/modificar.php",
                            data: datos,
                            success: function(data) {
                                // console.log('Modificar ::> ' + data);

                                var table = $('#tablaTipoCliente').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaTipoCliente').val(json.lastInput);
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