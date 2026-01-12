<!-- contenido/compras.php  -->

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <!--li class="breadcrumb-item"><a href="configuracion">Configuracion</a></li-->
            <li class="breadcrumb-item active" aria-current="page"> Órdenes de Compra</li>
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
                            <div class="col-12 col-md-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="proveedores"><i class="fas fa-truck-moving"></i> Proveedores </a> &nbsp;
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="compras-altas"><i class="fa fa-plus"></i> Orden de Compra</a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxp"><i class="fas fa-hand-holding-usd"></i> CxP </a> &nbsp;
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaOrdenesCompra" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Nombre Comercial / Razón Social</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Detalle</th>
                                    <th class="text-center">Pdf</th>
                                    <th class="text-center">estatus</th>
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

<!-- Modal -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detalle de compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cabDetalle"></div>
                <div id="detalle"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                <!-- <button type="button" class="btn btn-primary">Guardar</button> -->
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="estatusModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CerrarModalEstatus();"></button>
            </div>
            <div class="modal-body">
                <select id="nuevoEstatus" class="form-select">
                    <option value="1" class="text-success">Pendiente</option>
                    <option value="2">Procesada</option>
                    <option value="3" class="text-danger">Cancelada</option>
                </select>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="idOrdenCompra">
                <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                <button type="button" class="btn btn-primary" onclick="fnCambiarEstatus();">Realizar Cambio</button>
            </div>
        </div>
    </div>
</div>

<script>
    function fnCambiarEstatus() {
        const estatus = $("#nuevoEstatus").val();
        const id = $("#idOrdenCompra").val();
        $.ajax({
            url: 'ajax/compras/estatus.php',
            method: 'POST',
            data: {
                id: id,
                estatus: estatus
            },
            success: function(respuesta) {
                //console.log(respuesta);

                // ACatualiza la tabla                                    
                var table = $('#tablaOrdenesCompra').DataTable();
                table.ajax.reload(function(json) {
                    $('#tablaOrdenesCompra').val(json.lastInput);
                });

                $("#estatusModal").modal('hide')

            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    }

    function CerrarModalEstatus() {
        $("#estatusModal").modal('hide')
    }

    function fnMostrarEstatus(id, estatus) {
        $("#estatusModal").modal('show')
        $("#idOrdenCompra").val(id);
        // Obtén el <select> por su ID
        var select = document.getElementById("nuevoEstatus");

        // Itera sobre todas las opciones dentro del select
        for (var i = 0; i < select.options.length; i++) {
            var option = select.options[i];

            // Si el valor de la opción coincide con el estatus, selecciona esa opción            
            if (parseInt(option.value) === estatus) {
                option.selected = true;
            } else {
                option.selected = false; // Desmarcar las otras opciones
            }
        }
    }


    function detalle(id, fecha, nombre /*, total YA NO LO USES */ ) {
        // Título del modal
        $("#exampleModalLabel").text(nombre);

        // Encabezado temporal sin total (lo pondremos al terminar el AJAX)
        $("#cabDetalle").html(
            '<table class="table"><tr>' +
            '<td>Folio: ' + id + '</td>' +
            '<td class="text-center">Fecha: ' + fecha + '</td>' +
            '<td class="text-end">Total: —</td>' +
            '</tr></table>'
        );

        $.ajax({
            url: 'ajax/compras/detalle.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function(respuesta) {
                $("#detalle").html(respuesta);

                // Lee el total calculado del backend
                var totalCalc = $("#tablaDetalle").data("total") || "0.00";

                // Pinta el encabezado con el total correcto
                $("#cabDetalle").html(
                    '<table class="table"><tr>' +
                    '<td>Folio: ' + id + '</td>' +
                    '<td class="text-center">Fecha: ' + fecha + '</td>' +
                    '<td class="text-end">Total: $' + totalCalc + '</td>' +
                    '</tr></table>'
                );
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    }

    $(document).ready(function() {

        $('#tablaOrdenesCompra').dataTable({
            ajax: 'ajax/compras/tabla-compras.php',
            ordering: true,
            order: [
                [0, 'desc']
            ],
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

        var oTable = $('#tablaOrdenesCompra').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });
    });
</script>