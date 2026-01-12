<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Categorías</li>
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
                                <button type="button" class="btn btn-info" onclick="fnModalAltas();"> <i class="fa-solid fa-plus"></i> Agregar Categoría</button>
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
                        <table id="tablaCategorias" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Categoría</th>
                                    <th class="text-center">Editar</th>
                                    <!-- <th class="text-center">Sub Categorías</th> -->
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
                <h5 class="modal-title" id="exampleModalLabel">Agregar una categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalAltas();"></button>
            </div>
            <form method="post" action="" id="formAltas" novalidate>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre de la Categoria</label>
                        <input type="text" class="form-control " name="Nombre_Categoria">
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
                    <div class="form-group">
                        <label>Nombre de la Categoria</label>
                        <input type="text" class="form-control " name="nombre_categoria" id="nombre_categoria">
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

    function editar(id, nombre) {
        $("#modalEditar").modal('show');
        $("#nombre_categoria").val(nombre)
        $("#id").val(id)
    }

    function cerrarModalAltas() {
        $("#modalAltas").modal('hide');
    }

    function cerrarModalEditar() {
        $("#modalEditar").modal('toggle');
    }

    function borrar(id) {
        alertify.confirm('Aviso..!', '¿Deseas Borrar la categoría?',
            function() {

                $.ajax({
                    type: "POST",
                    url: "ajax/categorias/borrar.php",
                    data: {
                        "id": id
                    },
                    success: function(data) {
                        console.log('borrar categoria ::> ' + data);

                        var table = $('#tablaCategorias').DataTable();
                        table.ajax.reload(function(json) {
                            $('#tablaCategorias').val(json.lastInput);
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


        $('#tablaCategorias').dataTable({
            ajax: "ajax/categorias/tabla_categorias.php",
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

        var oTable = $('#tablaCategorias').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        /* Validacion de formulario   */
        $("#formAltas").validate({
            rules: {
                Nombre_Categoria: {
                    required: true,
                    minlength: 4,
                    maxlength: 100
                }
            },
            messages: {
                Nombre_Categoria: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 4",
                    maxlength: "El número máximo de caracteres es 100"
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

                var datos = $("#formAltas").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar la categoría?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/categorias/guardar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Guardarcategoria ::> ' + data);

                                var table = $('#tablaCategorias').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaCategorias').val(json.lastInput);
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
                nombre_categoria: {
                    required: true,
                    minlength: 8,
                    maxlength: 100
                }
            },
            messages: {
                nombre_categoria: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 1",
                    maxlength: "El número máximo de caracteres es 10"
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
                            url: "ajax/categorias/modificar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Modificar categoria ::> ' + data);

                                var table = $('#tablaCategorias').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaCategorias').val(json.lastInput);
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