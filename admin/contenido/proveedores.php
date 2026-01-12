<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Proveedores</li>
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
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="proveedores-altas"><i class="fas fa-plus"></i> Proveedores </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="compras"><i class="fas fa-file-invoice"></i> Ordenes Compra </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxp"><i class="fas fa-hand-holding-usd"></i> CxP </a> &nbsp;
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
                        <table id="tablaProveedores" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Razón Social / Nombre Comercial</th>
                                    <th class="text-center">Contacto</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Ubicación</th>
                                    <th class="text-center">Estatus</th>
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
<div class="modal fade" id="modalEdditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cliente Cambiar de estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarEstatus();"></button>
            </div>
            <!-- FORM  -->

            <div class="modal-body">
                <h5><span id="estatusActual"></span></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarEstatus();">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="modificaEstatus();"> Modificar </button>
                <input type="hidden" name="estatus" id="estatusnuevo">
                <input type="hidden" name="fin" value="modificar">
                <input type="hidden" name="tabla" value="cat_clientes">
                <input type="hidden" id="idCliente">
            </div>

            <!-- . FORM  -->
        </div>
    </div>
</div>

<!-- page script -->
<script type="text/javascript">
    function modificaEstatus() {
        const idCliente = $("#idCliente").val();
        const estatusnuevo = $("#estatusnuevo").val();
        $.ajax({
            url: "ajax/clientes/modifica-estatus.php",
            type: "POST",
            data: {
                "idCliente": idCliente,
                "estatusnuevo": estatusnuevo
            },
            success: function(data) {
                console.log(data);
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    // ACatualiza la tabla                                    
                    var table = $('#tablaProveedores').DataTable();
                    table.ajax.reload(function(json) {
                        $('#tablaProveedores').val(json.lastInput);
                    });

                    $("#modalEdditar").modal('toggle');
                }
            }
        }); /* End ajax */
    }

    function cerrarEstatus() {
        $("#modalEdditar").modal('toggle');
    }

    function valorEstatus(id, estatus) {
        $("#modalEdditar").modal('show');

        $("#idCliente").val(id);
        if (estatus == 1) {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-danger">Inactivo</b>?');
            estatus = 0;
        } else {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-success">Activo</b>?');
            estatus = 1;
        }
        $("#estatusnuevo").val(estatus);
    }

    function borrar(id, estatus) {
        document.getElementById('idborrar').value = id;
        $("#idborrar").val(id);
        if (estatus == 1) {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-danger">Inactivo</b>?');
            estatus = 0;
        } else {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-success">Activo</b>?');
            estatus = 1;
        }
        $("#estatusnuevo").val(estatus);
    };


    function editar(id) {
        location.href = "proveedores-editar/" + id;
    };



    $(document).ready(function() {


        $('#tablaProveedores').dataTable({
            ajax: 'ajax/proveedores/tabla-proveedores.php',
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

        var oTable = $('#tablaProveedores').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });


    });
</script>