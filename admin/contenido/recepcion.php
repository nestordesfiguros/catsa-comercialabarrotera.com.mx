<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recepciones</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="recepcion-altas">
                                            <i class="fa fa-plus"></i> Agregar Entrada
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <select id="estatus" class="form-select mt-2 mt-md-0">
                                            <option value="pendiente" selected>Pendientes</option>
                                            <option value="procesada">Procesadas</option>
                                            <option value="cancelada">Canceladas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="col-12">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="search" class="form-control" />
                                        <label class="form-label" for="search">Buscar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabla-recepcion" class="table table-hover table-striped w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Referencia</th>
                                    <th>Proveedor</th>
                                    <th>Diferencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        let tabla = $('#tabla-recepcion').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/recepcion/tabla-recepcion.php',
                type: 'POST',
                data: function(d) {
                    d.estatus = $('#estatus').val();
                }
            },
            columns: [{
                    data: 0
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4,
                    orderable: false
                }
            ],
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        $('#estatus').on('change', function() {
            tabla.ajax.reload();
        });

        $('#search').on('keyup', function() {
            tabla.search(this.value).draw();
        });

        // Inicializa tooltips de Bootstrap
        $('body').tooltip({
            selector: '[data-bs-toggle="tooltip"]'
        });
    });
</script>