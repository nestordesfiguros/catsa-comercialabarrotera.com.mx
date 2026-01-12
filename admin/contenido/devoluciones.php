<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Devoluciones</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a> &nbsp;
                                <a onclick="FnNuevaDevolucion()" type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" title="Agregar devolución">
                                    <i class="fas fa-plus"></i> Nueva Devolución
                                </a>
                            </div>
                            <div class="col-3">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaDevoluciones" class="table table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Documento</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detalle Devolución -->
<div class="modal fade" id="modalDetalleDevolucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalDetalleContenido">
            <!-- Aquí se carga el contenido por AJAX -->
        </div>

    </div>
</div>



<script>
    $(document).ready(function() {

        $(document).on('click', '.btn-ver-devolucion', function() {
            const id = $(this).data('id');

            $.post('ajax/devoluciones/detalle.php', {
                id
            }, function(html) {
                $('#modalDetalleContenido').html(html);
                const modal = new bootstrap.Modal(document.getElementById('modalDetalleDevolucion'));
                modal.show();
            });
        });




        var oTable = $('#tablaDevoluciones').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/devoluciones/listar.php',
                type: 'POST'
            },
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>>" +
                "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columnDefs: [{
                    targets: [0, 5, 6],
                    className: 'text-center'
                },
                {
                    targets: [4],
                    className: 'text-end'
                },
                {
                    targets: [6],
                    orderable: false
                }
            ]
        });

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });
    });

    function FnNuevaDevolucion() {
        window.location.href = "devoluciones-altas";
    }
</script>