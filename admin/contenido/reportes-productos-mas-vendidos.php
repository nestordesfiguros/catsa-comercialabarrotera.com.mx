<?php
// contenido/reportes-productos-mas-vendidos.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Productos Más Vendidos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title"><i class="fas fa-trophy me-2"></i>Productos Más Vendidos</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="id_almacen" class="form-label">Almacén</label>
                                <select class="form-select" id="id_almacen" name="id_almacen">
                                    <option value="">Todos los almacenes</option>
                                    <?php
                                    $sqlAlmacenes = "SELECT id, almacen 
                                                   FROM cat_almacenes 
                                                   WHERE estatus = 1 
                                                   ORDER BY almacen";
                                    $almacenes = $clsConsulta->consultaGeneral($sqlAlmacenes);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($almacenes as $i => $alm) {
                                            echo "<option value=\"{$alm['id']}\">{$alm['almacen']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="id_categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="id_categoria" name="id_categoria">
                                    <option value="">Todas las categorías</option>
                                    <?php
                                    $sqlCategorias = "SELECT id_categoria, nombre_categoria 
                                                    FROM cat_categorias 
                                                    ORDER BY nombre_categoria";
                                    $categorias = $clsConsulta->consultaGeneral($sqlCategorias);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($categorias as $i => $cat) {
                                            echo "<option value=\"{$cat['id_categoria']}\">{$cat['nombre_categoria']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="tipo_venta" class="form-label">Tipo de Venta</label>
                                <select class="form-select" id="tipo_venta" name="tipo_venta">
                                    <option value="">Todos</option>
                                    <option value="contado">Contado</option>
                                    <option value="credito">Crédito</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="top_n" class="form-label">Top N Productos</label>
                                <select class="form-select" id="top_n" name="top_n">
                                    <option value="10">Top 10</option>
                                    <option value="20">Top 20</option>
                                    <option value="50">Top 50</option>
                                    <option value="100">Top 100</option>
                                    <option value="0">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="cantidad">Cantidad Vendida</option>
                                    <option value="ventas">Ventas Totales</option>
                                    <option value="utilidad">Utilidad</option>
                                    <option value="margen">Margen %</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" id="btnGenerarReporte" class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Generar Reporte
                                </button>
                                <button type="button" id="btnExportarPDF" class="btn btn-danger" disabled>
                                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                                </button>
                                <button type="button" id="btnExportarExcel" class="btn btn-success" disabled>
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </button>
                                <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary">
                                    <i class="fas fa-broom me-2"></i>Limpiar Filtros
                                </button>
                            </div>
                        </div>

                        <!-- Resumen General -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen del Periodo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Ventas</h6>
                                                        <h3 class="text-primary" id="resumenTotalVentas">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Productos Vendidos</h6>
                                                        <h3 class="text-success" id="resumenProductosVendidos">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Utilidad Total</h6>
                                                        <h3 class="text-warning" id="resumenUtilidadTotal">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Margen Promedio</h6>
                                                        <h3 class="text-danger" id="resumenMargenPromedio">0%</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Barras -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Top Productos por Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainer">
                                            <canvas id="graficoTopProductos" height="400"></canvas>
                                        </div>
                                        <div id="graficoMensaje" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resultados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Ranking de Productos Más Vendidos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaProductosVendidos" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th>Producto</th>
                                                        <th>Categoría</th>
                                                        <th class="text-center">Cantidad Vendida</th>
                                                        <th class="text-end">Ventas Totales</th>
                                                        <th class="text-end">Costo Total</th>
                                                        <th class="text-end">Utilidad</th>
                                                        <th class="text-center">Margen %</th>
                                                        <th class="text-center">% del Total</th>
                                                        <th class="text-center">Tendencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="3"><strong>TOTALES</strong></th>
                                                        <th class="text-center" id="totalCantidad">0</th>
                                                        <th class="text-end" id="totalVentas">$0.00</th>
                                                        <th class="text-end" id="totalCosto">$0.00</th>
                                                        <th class="text-end" id="totalUtilidad">$0.00</th>
                                                        <th class="text-center" id="margenPromedio">0%</th>
                                                        <th class="text-center">100%</th>
                                                        <th>-</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        let tablaProductosVendidos = null;
        let graficoTopProductos = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaProductosVendidos !== null) {
                tablaProductosVendidos.destroy();
            }

            tablaProductosVendidos = $('#tablaProductosVendidos').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-productos-mas-vendidos.php',
                    type: 'POST',
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                        d.id_almacen = $('#id_almacen').val();
                        d.id_categoria = $('#id_categoria').val();
                        d.tipo_venta = $('#tipo_venta').val();
                        d.top_n = $('#top_n').val();
                        d.ordenar_por = $('#ordenar_por').val();
                    }
                },
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                    sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
                },
                columns: [{
                        data: null,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'producto'
                    },
                    {
                        data: 'categoria'
                    },
                    {
                        data: 'cantidad_vendida',
                        className: 'text-center'
                    },
                    {
                        data: 'ventas_totales',
                        className: 'text-end'
                    },
                    {
                        data: 'costo_total',
                        className: 'text-end'
                    },
                    {
                        data: 'utilidad',
                        className: 'text-end'
                    },
                    {
                        data: 'margen',
                        className: 'text-center'
                    },
                    {
                        data: 'porcentaje_total',
                        className: 'text-center'
                    },
                    {
                        data: 'tendencia',
                        className: 'text-center'
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                drawCallback: function(settings) {
                    actualizarResumen();
                    generarGrafico();
                }
            });
        }

        // Generar reporte
        $('#btnGenerarReporte').on('click', function() {
            inicializarDataTable();
            $('#btnExportarPDF').prop('disabled', false);
            $('#btnExportarExcel').prop('disabled', false);
        });

        // Limpiar filtros
        $('#btnLimpiarFiltros').on('click', function() {
            $('#fecha_inicio').val('<?php echo date('Y-m-01'); ?>');
            $('#fecha_fin').val('<?php echo date('Y-m-d'); ?>');
            $('#id_almacen').val('');
            $('#id_categoria').val('');
            $('#tipo_venta').val('');
            $('#top_n').val('10');
            $('#ordenar_por').val('cantidad');

            if (tablaProductosVendidos !== null) {
                tablaProductosVendidos.clear().draw();
            }
            destruirGrafico();
            resetResumen();
            $('#btnExportarPDF').prop('disabled', true);
            $('#btnExportarExcel').prop('disabled', true);
        });

        // Exportar PDF
        $('#btnExportarPDF').on('click', function() {
            exportarPDF();
        });

        // Exportar Excel
        $('#btnExportarExcel').on('click', function() {
            exportarExcel();
        });

        function actualizarResumen() {
            if (tablaProductosVendidos !== null) {
                const data = tablaProductosVendidos.rows().data().toArray();

                let totalVentas = 0;
                let totalProductosVendidos = 0;
                let totalUtilidad = 0;
                let totalCosto = 0;

                data.forEach(function(row) {
                    totalVentas += parseFloat(row.ventas_totales.replace(/[^0-9.-]+/g, "")) || 0;
                    totalProductosVendidos += parseInt(row.cantidad_vendida.replace(/[^0-9.-]+/g, "")) || 0;
                    totalUtilidad += parseFloat(row.utilidad.replace(/[^0-9.-]+/g, "")) || 0;
                    totalCosto += parseFloat(row.costo_total.replace(/[^0-9.-]+/g, "")) || 0;
                });

                const margenPromedio = totalVentas > 0 ? (totalUtilidad / totalVentas) * 100 : 0;

                $('#resumenTotalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#resumenProductosVendidos').text(totalProductosVendidos.toLocaleString('es-MX'));
                $('#resumenUtilidadTotal').text('$' + totalUtilidad.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#resumenMargenPromedio').text(margenPromedio.toFixed(1) + '%');

                // Actualizar totales en tabla
                $('#totalCantidad').text(totalProductosVendidos.toLocaleString('es-MX'));
                $('#totalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalCosto').text('$' + totalCosto.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalUtilidad').text('$' + totalUtilidad.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#margenPromedio').text(margenPromedio.toFixed(1) + '%');
            }
        }

        function resetResumen() {
            $('#resumenTotalVentas').text('$0.00');
            $('#resumenProductosVendidos').text('0');
            $('#resumenUtilidadTotal').text('$0.00');
            $('#resumenMargenPromedio').text('0%');

            $('#totalCantidad').text('0');
            $('#totalVentas').text('$0.00');
            $('#totalCosto').text('$0.00');
            $('#totalUtilidad').text('$0.00');
            $('#margenPromedio').text('0%');
        }

        function generarGrafico() {
            if (tablaProductosVendidos !== null) {
                const data = tablaProductosVendidos.rows().data().toArray();

                if (data.length === 0) {
                    $('#graficoContainer').hide();
                    $('#graficoMensaje').show();
                    return;
                }

                $('#graficoContainer').show();
                $('#graficoMensaje').hide();

                // Destruir gráfico anterior
                if (graficoTopProductos !== null) {
                    graficoTopProductos.destroy();
                }

                const ordenarPor = $('#ordenar_por').val();
                let datasetLabel = '';
                let valores = [];
                const labels = [];
                const colores = [];

                // Tomar solo los primeros 10 para el gráfico
                const topData = data.slice(0, 10);

                topData.forEach(function(row, index) {
                    labels.push(row.producto.substring(0, 20) + '...');

                    switch (ordenarPor) {
                        case 'cantidad':
                            datasetLabel = 'Cantidad Vendida';
                            valores.push(parseInt(row.cantidad_vendida.replace(/[^0-9.-]+/g, "")) || 0);
                            break;
                        case 'ventas':
                            datasetLabel = 'Ventas Totales ($)';
                            valores.push(parseFloat(row.ventas_totales.replace(/[^0-9.-]+/g, "")) || 0);
                            break;
                        case 'utilidad':
                            datasetLabel = 'Utilidad ($)';
                            valores.push(parseFloat(row.utilidad.replace(/[^0-9.-]+/g, "")) || 0);
                            break;
                        case 'margen':
                            datasetLabel = 'Margen (%)';
                            valores.push(parseFloat(row.margen) || 0);
                            break;
                    }

                    // Generar colores diferentes
                    const hue = (index * 137.5) % 360; // Golden angle approximation
                    colores.push(`hsla(${hue}, 70%, 60%, 0.7)`);
                });

                const ctx = document.getElementById('graficoTopProductos');
                graficoTopProductos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: datasetLabel,
                            data: valores,
                            backgroundColor: colores,
                            borderColor: colores.map(color => color.replace('0.7', '1')),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Top 10 Productos - ' + datasetLabel
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (ordenarPor === 'margen') {
                                            return `${context.dataset.label}: ${context.parsed.y.toFixed(1)}%`;
                                        } else if (ordenarPor === 'cantidad') {
                                            return `${context.dataset.label}: ${context.parsed.y.toLocaleString('es-MX')}`;
                                        } else {
                                            return `${context.dataset.label}: $${context.parsed.y.toLocaleString('es-MX')}`;
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        if (ordenarPor === 'margen') {
                                            return value + '%';
                                        } else if (ordenarPor === 'cantidad') {
                                            return value.toLocaleString('es-MX');
                                        } else {
                                            return '$' + value.toLocaleString('es-MX');
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        function destruirGrafico() {
            if (graficoTopProductos !== null) {
                graficoTopProductos.destroy();
                graficoTopProductos = null;
            }
            $('#graficoContainer').hide();
            $('#graficoMensaje').show();
        }

        function exportarPDF() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const tipoVenta = $('#tipo_venta').val();
            const topN = $('#top_n').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-pdf-productos-mas-vendidos.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_almacen=${idAlmacen}&id_categoria=${idCategoria}&tipo_venta=${tipoVenta}&top_n=${topN}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const tipoVenta = $('#tipo_venta').val();
            const topN = $('#top_n').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-excel-productos-mas-vendidos.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_almacen=${idAlmacen}&id_categoria=${idCategoria}&tipo_venta=${tipoVenta}&top_n=${topN}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        // Inicializar al cargar la página
        inicializarDataTable();
    });
</script>

<style>
    .card.bg-light {
        border: 1px solid #dee2e6;
    }

    #tablaProductosVendidos th {
        font-size: 0.85rem;
    }

    #tablaProductosVendidos td {
        font-size: 0.8rem;
    }

    #graficoTopProductos {
        max-height: 400px;
    }

    .tendencia-up {
        color: #28a745;
    }

    .tendencia-down {
        color: #dc3545;
    }

    .tendencia-stable {
        color: #6c757d;
    }
</style>