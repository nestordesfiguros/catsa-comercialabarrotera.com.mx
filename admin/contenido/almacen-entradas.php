<!-- contenido/almacen-entradas.php -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <!--li class="breadcrumb-item"><a href="configuracion">Configuracion</a></li-->
            <li class="breadcrumb-item active" aria-current="page"> Entradas de productos</li>
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
                            <div class="col-10">
                                <div class="row">
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos"><i class="fa-solid fa-cubes"></i> Productos </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="lista-precios"><i class="fa-solid fa-file-invoice-dollar"></i> Lista de Precios </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="almacen-entradas-altas"><i class="fa fa-plus"></i> Entradas</a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="almacen-salidas"><i class="fa-solid fa-warehouse"></i> Salidas </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="ofertas"><i class="fas fa-tags"></i> Ofertas </a>
                                    </div>
                                    <div class="col-2">
                                        <div class="col-12">
                                            <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                                <input type="text" id="search" class="form-control" />
                                                <label class="form-label" for="form12">Buscar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 col-12">
                            <div class="col-2 col-md-2">
                                <div class="form-outline mt-2 mt-md-0">
                                    <select id="filtroAlmacen" class="form-select">
                                        <option value="">-- Todos los almacenes --</option>
                                        <?php
                                        $rsAlmacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes ORDER BY almacen ASC");
                                        if ($clsConsulta->numrows > 0) {
                                            foreach ($rsAlmacenes as $alm) {
                                                echo '<option value="' . $alm['id'] . '">' . $alm['almacen'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
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
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Estado</th> <!-- Nueva columna -->
                                    <th class="text-center">Detalle</th>
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
    function detalle(id, fecha, nombre, total) {
        //console.log('Folio: '+id+' Fecha: '+fecha+' nombre: '+nombre);
        $("#cabDetalle").html('<table class="table"><tr><td>Folio:' + id + '</td> <td class="text-center">Fecha: ' + fecha + '</td><td class="text-end">Total: $' + total + '</td></tr></table>');
        $("#exampleModalLabel").text(nombre);
        $.ajax({
            url: 'ajax/almacen-entradas/detalle.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function(respuesta) {
                //console.log(respuesta);
                $("#detalle").html(respuesta);
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    };

    $(document).ready(function() {

        var oTable = $('#tablaOrdenesCompra').DataTable({
            ajax: {
                url: 'ajax/almacen-entradas/tabla-entradas.php',
                type: 'POST',
                data: function(d) {
                    d.id_almacen = $('#filtroAlmacen').val();
                }
            },
            ordering: true,
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        // Buscar normal
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        // Filtro por almacén
        $('#filtroAlmacen').change(function() {
            oTable.ajax.reload();
        });
    });
</script>