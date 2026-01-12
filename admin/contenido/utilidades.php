<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Usuarios</li>
        </ol>
    </nav>
</div>
<!-- Main content -->
<section class="content">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-chart-line"></i> Reporte de Utilidades</h5>
                    </div>
                    <div class="card-body">
                        <form id="filtroUtilidades" class="mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Fecha Inicio</label>
                                    <input type="date" id="fecha_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label>Fecha Fin</label>
                                    <input type="date" id="fecha_fin" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Vendedor</label>
                                    <select id="id_vendedor" class="form-control">
                                        <option value="">Todos</option>
                                        <?php
                                        $vendedores = $clsConsulta->consultaGeneral("SELECT * FROM cat_vendedores WHERE estatus = 1");
                                        if ($clsConsulta->numrows > 0) {
                                            foreach ($vendedores as $v) {
                                                echo '<option value="' . $v['id'] . '">' . $v['nombre'] . ' ' . $v['apellido1'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2 align-self-end me-0">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                                <div class="col-md-3 align-self-end">
                                    <button id="btnExportExcel" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Exportar a Excel
                                    </button>
                                </div>

                            </div>
                        </form>

                        <div class="table-responsive mt-3">
                            <table id="tablaUtilidades" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Remisión</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Total Venta</th>
                                        <th>Costo</th>
                                        <th>Utilidad</th> <!-- Antes: Utilidad Bruta -->
                                        <th>Comisión</th>
                                        <th>Utilidad Neta</th>
                                    </tr>
                                </thead>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="4">TOTALES</th>
                                        <th id="totalVenta">$0.00</th>
                                        <th id="totalCosto">$0.00</th>
                                        <th id="totalUtilidadBruta">$0.00</th>
                                        <th id="totalComision">$0.00</th>
                                        <th id="totalUtilidadNeta">$0.00</th>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</section>

<script>
    $(document).ready(function() {

        $('#btnExportExcel').on('click', function() {
            tabla.button('.buttons-excel').trigger();
        });

        function formatCurrency(value) {
            if (value === 0) return '$0.00';
            return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Inicializar DataTable
        var tabla = $('#tablaUtilidades').DataTable({
            dom: '<"top"lf>rt<"bottom"ip>',
            buttons: [{
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success mx-2',
                title: 'Reporte de Utilidades'
            }],
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columnDefs: [{
                targets: [4, 5, 6, 7, 8],
                className: 'text-end'
            }]
        });

        $('#filtroUtilidades').on('submit', function(e) {
            e.preventDefault();
            cargarUtilidades();
        });

        // Función para formatear moneda
        function formatCurrency(value) {
            if (value === 0) return '$0.00';
            return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Filtrar datos
        $('#filtroUtilidades').on('submit', function(e) {
            e.preventDefault();
            cargarUtilidades();
        });

        function cargarUtilidades() {
            $.ajax({
                url: 'ajax/utilidades/calcular-utilidades.php',
                type: 'POST',
                data: {
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    id_vendedor: $('#id_vendedor').val()
                },
                dataType: 'json',
                success: function(response) {
                    tabla.clear().draw();

                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(i, item) {
                            let claseUtilidad = item.utilidad_neta < 0 ? 'text-danger' : 'text-success';

                            tabla.row.add([
                                item.folio,
                                item.fecha,
                                item.cliente,
                                item.vendedor,
                                formatCurrency(item.total_venta),
                                formatCurrency(item.costo_total),
                                `<span class="${claseUtilidad}">${formatCurrency(item.utilidad_bruta)}</span>`,
                                formatCurrency(item.comision),
                                `<span class="${claseUtilidad}">${formatCurrency(item.utilidad_neta)}</span>`
                            ]).draw(false);
                        });

                        // Totales
                        $('#totalVenta').text(formatCurrency(response.totales.total_ventas));
                        $('#totalCosto').text(formatCurrency(response.totales.total_costos));

                        let claseTotalUtilidad = response.totales.total_utilidad_neta < 0 ? 'text-danger' : 'text-success';

                        $('#totalUtilidadBruta').html(`<span class="${claseTotalUtilidad}">${formatCurrency(response.totales.total_utilidad_bruta)}</span>`);
                        $('#totalComision').text(formatCurrency(response.totales.total_comisiones));
                        $('#totalUtilidadNeta').html(`<span class="${claseTotalUtilidad}">${formatCurrency(response.totales.total_utilidad_neta)}</span>`);
                    } else {
                        tabla.row.add([
                            '',
                            'No se encontraron registros',
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            ''
                        ]).draw();
                    }
                }
            });
        }
    });
</script>