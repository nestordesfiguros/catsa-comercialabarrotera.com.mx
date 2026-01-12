<?php // contenido/reportes-utilidades-por-producto.php 
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Utilidades por Producto</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <div class="form-outline">
                            <input type="date" id="f_ini" class="form-control">
                            <label class="form-label" for="f_ini">Desde</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-outline">
                            <input type="date" id="f_fin" class="form-control">
                            <label class="form-label" for="f_fin">Hasta</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <button id="btnFiltrar" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                            <th class="text-end">Unidades</th>
                            <th class="text-end">Venta</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">Utilidad Bruta</th>
                            <th class="text-end">Utilidad Neta</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
    $(function() {
        const t = $('#tabla').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/reportes/tabla-utilidades-por-producto.php',
                type: 'POST',
                data: function(d) {
                    d.f_ini = $('#f_ini').val();
                    d.f_fin = $('#f_fin').val();
                }
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                url: 'assets/datatables/Spanish.json'
            },
            columns: [{
                    data: 0
                }, {
                    data: 1
                },
                {
                    data: 2,
                    className: 'text-end'
                },
                {
                    data: 3,
                    className: 'text-end'
                },
                {
                    data: 4,
                    className: 'text-end'
                },
                {
                    data: 5,
                    className: 'text-end'
                },
                {
                    data: 6,
                    className: 'text-end'
                }
            ]
        });
        $('#btnFiltrar').on('click', () => t.ajax.reload());
    });
</script>