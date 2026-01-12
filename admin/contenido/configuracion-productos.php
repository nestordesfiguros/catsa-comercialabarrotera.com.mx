<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="configuracion">Configuración</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Productos</li>
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
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAltas">Agregar Producto</button>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaProductos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">UdM</th>
                                    <th class="text-center">Categoría</th>
                                    <th class="text-center">Precio Unitario</th>
                                    <th class="text-center">Cantidad Disponible</th>
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
                <h5 class="modal-title" id="exampleModalLabel">Agregar un producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span></button>
            </div>
            <form method="post" action="" id="formAltas" novalidate>
                <div class="modal-body row gy-3">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select class="form-select" name="ID_Categoria">
                            <option value="">Selecciona una categoría</option>
                            <?php
                            $con = "SELECT * FROM cat_categorias";
                            $rs = $clsConsulta->consultaGeneral($con);
                            foreach ($rs as $v => $val) {
                                echo '<option value="' . $val['id_categoria'] . '">' . $val['nombre_categoria'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unidad de medida</label>
                        <select class="form-select" name="Unidad_Medida">
                            <option value="">Selecciona una unidad de medida</option>
                            <?php
                            $con = "SELECT * FROM unidades_de_medida";
                            $rs = $clsConsulta->consultaGeneral($con);
                            foreach ($rs as $v => $val) {
                                echo '<option value="' . $val['id_unidad'] . '">' . $val['nombre_unidad'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control " name="Nombre">
                    </div>
                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea class="form-control" name="Descripcion"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Precio Unitario</label>
                        <input type="text" class="form-control " name="Precio_Unitario">
                    </div>
                    <div class="form-group">
                        <label>Cantidad Disponible</label>
                        <input type="text" class="form-control " name="Cantidad_Disponible">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span></button>
            </div>
            <form method="post" action="" id="formEditar" novalidate>
                <div id="modificaProductos"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <input type="hidden" name="id" id="id">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editar(id, nombre) {
        $("#nombre_categoria").val(nombre)
        $("#id").val(id)
        $.ajax({
            type: "POST",
            url: "ajax/productos/editar.php",
            data: {
                "id": id
            },
            success: function(data) {
                console.log('borrar categoria ::> ' + data);

                $("#modificaProductos").html(data);
            }
        }); /* End ajax */

    }

    function borrar(id) {
        alertify.confirm('Aviso..!', '¿Deseas Borrar la categoría?',
            function() {

                $.ajax({
                    type: "POST",
                    url: "ajax/productos/borrar.php",
                    data: {
                        "id": id
                    },
                    success: function(data) {
                        console.log('borrar categoria ::> ' + data);

                        var table = $('#tablaProductos').DataTable();
                        table.ajax.reload(function(json) {
                            $('#tablaProductos').val(json.lastInput);
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


        $('#tablaProductos').dataTable({
            ajax: "ajax/productos/tabla_productos.php",
            ordering: true,
            fixedHeader: false,
            paging: true, // Agregar paginación
            dom: "<'row'<'col-sm-6'l><'col-sm-2 mb-3'><'col-sm-4'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6 mt-3'l><'col-sm-2 mt-3'><'col-sm-4 mt-3'p>>",

            language: {
                url: "assets/datatables/Spanish.json"
            },
        });

        var oTable = $('#tablaProductos').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        /* Validacion de formulario   */
        $("#formAltas").validate({
            rules: {
                ID_Categoria: {
                    required: true
                },
                Nombre: {
                    required: true,
                    minlength: 6,
                    maxlength: 100
                },
                Unidad_Medida: {
                    required: true
                }
            },
            messages: {
                ID_Categoria: {
                    required: "Campo obligatorio"
                },
                Nombre: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 3",
                    maxlength: "El número máximo de caracteres es 10"
                },
                Unidad_Medida: {
                    required: "Campo obligatorio"
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

                alertify.confirm('Aviso..!', '¿Deseas guardar el producto?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/productos/guardar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Guardarcategoria ::> ' + data);

                                var table = $('#tablaProductos').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaProductos').val(json.lastInput);
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
                            url: "ajax/productos/modificar.php",
                            data: datos,
                            success: function(data) {
                                console.log('Modificar productos ::> ' + data);

                                var table = $('#tablaProductos').DataTable();
                                table.ajax.reload(function(json) {
                                    $('#tablaProductos').val(json.lastInput);
                                });
                            }
                        }); /* End ajax */
                        if ($('#modalEditar').is(':visible')) {
                            $('#modalEditar').modal('toggle');
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