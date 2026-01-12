<?php
// contenido/ofertas.php
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ofertas y Promociones</li>
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
                            <div class="col-10">
                                <div class="row">
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos"><i class="fa-solid fa-cubes"></i> Productos </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 " href="lista-precios"><i class="fa-solid fa-file-invoice-dollar"></i> Lista de Precios </a>
                                    </div>
                                    <div class="col-12 col-md-2 ">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="almacen-entradas"><i class="fa-solid fa-warehouse"></i> Entradas </a>
                                    </div>
                                    <div class="col-12 col-md-2 ">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="almacen-salidas"><i class="fa-solid fa-warehouse"></i> Salidas </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="ofertas-altas"><i class="fa-solid fa fa-plus"></i> Ofertas </a>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <div class="col-12">
                                            <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                                <input type="text" id="search" class="form-control" />
                                                <label class="form-label" for="form12">Buscar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row col-12 mt-3">
                                <div class="col-12 col-md-2">
                                    <select id="filtroVigencia" class="form-select">
                                        <option value="">Todas</option>
                                        <option value="activas">Activas</option>
                                        <option value="futuras">Futuras</option>
                                        <option value="vencidas">Vencidas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tablaOfertas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Vigencia</th>
                                        <th>Productos</th>
                                        <!-- <th>Estatus</th> -->
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
</section>


<!-- Modal Detalle Productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="modalProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductosLabel">Productos de la Oferta</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" id="tablaDetalleProductos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Valor Oferta</th>
                            <th>Cantidad Mínima</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        // Inicializa DataTable
        const tabla = $('#tablaOfertas').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/ofertas/tabla-ofertas.php',
                type: 'POST',
                data: function(d) {
                    d.vigencia = $('#filtroVigencia').val();
                }
            },
            columns: [{
                    data: 'nombre'
                },
                {
                    data: 'tipo'
                },
                {
                    data: 'vigencia'
                },
                {
                    data: 'productos'
                },
                // {
                //     data: 'estatus'
                // },
                {
                    data: 'acciones',
                    orderable: false
                }
            ],
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }
        });

        // Listener del filtro
        $('#filtroVigencia').on('change', function() {
            tabla.ajax.reload();
        });

    });

    // Editar
    function editarOferta(id) {
        location.href = "ofertas-modificar/" + id;
    }

    // Cambiar estatus
    function cambiarEstatusOferta(id, estatus) {
        const texto = estatus === 1 ? "activar" : "desactivar";

        alertify.confirm(
            'Confirmar cambio de estatus',
            '¿Estás seguro que deseas ' + texto + ' esta oferta?',
            function() {
                $.ajax({
                    url: 'ajax/ofertas/cambiar-estatus.php',
                    type: 'POST',
                    data: {
                        id: id,
                        estatus: estatus
                    },
                    success: function(response) {
                        if (response.success) {
                            alertify.success(response.message);
                            $('#tablaOfertas').DataTable().ajax.reload();
                        } else {
                            alertify.error(response.message);
                        }
                    }
                });
            },
            function() {
                alertify.message('Acción cancelada');
            }
        );
    }


    // Ver productos (modal)
    function verProductosOferta(id_oferta) {
        $.ajax({
            url: 'ajax/ofertas/ver-productos-oferta.php',
            type: 'POST',
            data: {
                id_oferta: id_oferta
            },
            success: function(response) {
                const tbody = $('#tablaDetalleProductos tbody');
                tbody.empty();

                if (response.length === 0) {
                    tbody.append('<tr><td colspan="4" class="text-center">Sin productos</td></tr>');
                } else {
                    response.forEach(p => {
                        tbody.append(`
                        <tr>
                            <td>${p.producto}</td>
                            <td>${p.valor_oferta}</td>
                            <td>${p.cantidad_minima}</td>
                            <td>${p.tipo}</td>
                        </tr>
                    `);
                    });
                }

                $('#modalProductos').modal('show');
            }
        });
    }
</script>