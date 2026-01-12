<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Vendedores</li>
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
                        <div class="row col-12">
                            <div class="col-8">
                                <!--button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalaltas"><i class="fa fa-plus"></i> Agregar </button-->
                                <a type="button" class="btn btn-info" href="vendedores-altas"><i class="fa fa-plus"></i> Agregar </a> &nbsp;
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
                        <table id="tablaVendedores" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Teléfono</th>
                                    <th class="text-center">Comisión</th>
                                    <th class="text-center">Clientes</th>
                                    <th class="text-center">Acceso</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody>

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

<!--MODAL Cambiar de estatus-->
<div class="modal fade" id="modalBorrar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Borrar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarBorrar();"></button>
            </div>
            <!-- FORM  -->

            <div class="modal-body">
                <h5 class="text-center"><span id="estatusActual"></span></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarBorrar();">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="borrarVendedor();"> Borrar </button>
                <input type="hidden" name="idVendedor" id="idVendedor">
            </div>

            <!-- . FORM  -->
        </div>
    </div>
</div>

<!-- page script -->
<script type="text/javascript">
    function borrarVendedor() {
        const idVendedor = $("#idVendedor").val();
        $.ajax({
            url: "ajax/vendedores/borrar.php",
            type: "POST",
            data: {
                "id": idVendedor
            },
            success: function(data) {
                // console.log(data);
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    // ACatualiza la tabla                                    
                    var table = $('#tablaVendedores').DataTable();
                    table.ajax.reload(function(json) {
                        $('#tablaVendedores').val(json.lastInput);
                    });

                    $("#modalBorrar").modal('toggle');
                }
            }
        }); /* End ajax */
    }

    function cerrarBorrar() {
        $("#modalBorrar").modal('toggle');
    }


    function borrar(id, nombre) {
        $("#modalBorrar").modal('show');
        $("#idVendedor").val(id);
        $("#estatusActual").html('¿Quieres borrar a:  <br> <b>' + nombre + ' </b>?');
    };


    function editar(id) {
        location.href = "vendedores-editar/" + id;
    };

    function fnAcceso(id) {
        location.href = "vendedores-pwd/" + id;
    }

    function fnClientes(id) {
        location.href = "vendedores-clientes/" + id;
    }

    $(document).ready(function() {


        $('#tablaVendedores').dataTable({
            ajax: 'ajax/vendedores/tabla-vendedores.php',
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",

            initComplete: function(settings, json) {
                $('#custom_length').appendTo('body'); //jQuery for moving elements around
            },
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        var oTable = $('#tablaVendedores').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });



    });
</script>