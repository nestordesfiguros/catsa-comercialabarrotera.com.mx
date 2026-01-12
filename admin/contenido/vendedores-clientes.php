<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="vendedores">Vendedores</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Clientes Asignados</li>
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
                                <button type="button" class="btn btn-info" onclick="fnModalAltas();"><i class="fa fa-plus"></i> Agregar </button>
                                &nbsp;
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
                                    <th class="text-center">Razón Social</th>
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

<!--MODAL Alta -->
<div class="modal fade" id="modalAltas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Lista de clientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="fnCerrarModalAltas();"></button>
            </div>
            <!-- FORM  -->

            <div class="modal-body">
                <div>
                    <div class="form-outline" data-mdb-input-init>
                        <input type="text" id="buscar" class="form-control" />
                        <label class="form-label" for="form12">Buscar</label>
                    </div>
                </div>
                <div class="mt-3">
                    <table id="tablaClientes" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">No. Cliente</th>
                                <th class="text-center">Razón Social</th>
                                <th class="text-center">Nombre Comercial</th>
                                <th class="text-center"> Agregar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            /*
                            $rs = $clsConsulta->consultaGeneral("SELECT cc.*
                                    FROM cat_clientes cc
                                    WHERE NOT EXISTS (
                                        SELECT 1
                                        FROM vendedores_clientes vc
                                        WHERE vc.id_cliente = cc.id
                                    )
                                    AND cc.deleted_at IS NULL;");
                            foreach ($rs as $v => $val) {
                                echo '<tr>';
                                echo '<td>' . $val['razon_social'] . '</td>';
                                echo '<td>' . $val['nombre_comercial'] . '</td>';
                                echo '<td class="text-center"> <i class="fas fa-user-plus fa-lg text-info" style="cursor:pointer;" onclick="fnAddClientes(' . $val['id'] . ')"></i> </td>';
                                echo '</tr>';
                            }
                                */
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="fnCerrarModalAltas();">Cerrar</button>
                <!-- <button type="button" class="btn btn-primary" onclick="borrarVendedor();"> Agregar </button> -->
                <input type="hidden" name="idVendedor" id="idVendedor">
            </div>

            <!-- . FORM  -->
        </div>
    </div>
</div>

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

<script>
    function fnModalAltas() {
        $('#modalAltas').on('show.bs.modal', function() {
            if ($.fn.DataTable.isDataTable('#tablaClientes')) {
                $('#tablaClientes').DataTable().ajax.reload();
            }
        });
        $("#modalAltas").modal('show');
    }

    function fnCerrarModalAltas() {
        $("#modalAltas").modal('hide');
    }

    function fnborrarClientes(id) {
        alertify.confirm('Aviso..!', '¿Deseas quitar al cliente?',
            function() {
                $.ajax({
                    type: "POST",
                    url: "ajax/vendedores/borrar-cliente-asignado.php",
                    data: {
                        "id": id
                    },
                    success: function(data) {
                        console.log('borrar cliente Asignado ::> ' + data);

                        var table = $('#tablaVendedores').DataTable();
                        table.ajax.reload(function(json) {
                            $('#tablaVendedores').val(json.lastInput);
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

    function fnAddClientes(idCliente) {
        const idVendedor = <?= $cat; ?>;
        $.ajax({
            url: 'ajax/vendedores/agrega-cliente.php',
            method: 'POST',
            data: {
                idCliente: idCliente,
                idVendedor: idVendedor
            },
            dataType: 'json',
            success: function(respuesta) {
                console.log(respuesta); // Verificar qué se recibe en la respuesta
                // Recargar el DataTable
                $("#modalAltas").modal('hide');

                var table = $('#tablaVendedores').DataTable();
                table.ajax.reload(function(json) {
                    $('#tablaVendedores').val(json.lastInput);
                });
            }
        });
    }


    $(document).ready(function() {

        const idVendedor = <?= $cat; ?>;
        $('#tablaVendedores').dataTable({
            ajax: 'ajax/vendedores/tabla-vendedores-clientes.php?idVendedor=' + idVendedor,
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

        $('#tablaClientes').dataTable({
            ajax: 'ajax/vendedores/tabla-clientes-disponibles.php?idVendedor=' + idVendedor,
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

        var oTable = $('#tablaClientes').DataTable();
        $('#buscar').keyup(function() {
            oTable.search($(this).val()).draw();
        });

    });
</script>