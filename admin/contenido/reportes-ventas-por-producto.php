<?php
// contenido/reportes-ventas-por-producto.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas por Producto</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-cube me-2"></i>Reporte de Ventas por Producto</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                            <div class="col-md-3">
                                <label for="id_producto" class="form-label">Producto</label>
                                <select class="form-select" id="id_producto" name="id_producto">
                                    <option value="">Todos los productos</option>
                                    <?php
                                    $sqlProductos = "SELECT id_producto, clave, nombre 
                                                    FROM cat_productos 
                                                    WHERE estado = 'activo' 
                                                    ORDER BY nombre";
                                    $productos = $clsConsulta->consultaGeneral($sqlProductos);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($productos as $i => $prod) {
                                            echo "<option value=\"{$prod['id_producto']}\">{$prod['clave']} - {$prod['nombre']}</option>";
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
                                <label for="orden" class="form-label">Ordenar por</label>
                                <select class="form-select" id="orden" name="orden">
                                    <option value="total_ventas">Total Ventas</option>
                                    <option value="cantidad_vendida">Cantidad Vendida</option>
                                    <option value="nombre">Nombre Producto</option>
                                    <option value="utilidad">Utilidad</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <select class="form-select" id="direccion" name="direccion">
                                    <option value="DESC">Descendente</option>
                                    <option value="ASC">Ascendente</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="limite" class="form-label">Límite</label>
                                <select class="form-select" id="limite" name="limite">
                                    <option value="0">Todos</option>
                                    <option value="10">Top 10</option>
                                    <option value="25">Top 25</option>
                                    <option value="50">Top 50</option>
                                    <option value="100">Top 100</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" id="btnGenerarReporte" class="btn btn-success">
                                    <i class="fas fa-play me-2"></i>Generar Reporte
                                </button>
                                <button type="button" id="btnExportarPDF" class="btn btn-danger" disabled>
                                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                                </button>
                                <button type="button" id="btnExportarExcel" class="btn btn-primary" disabled>
                                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                                </button>
                                <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary">
                                    <i class="fas fa-broom me-2"></i>Limpiar Filtros
                                </button>
                            </div>
                        </div>

                        <!-- Resultados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tablaVentasProducto" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Producto</th>
                                                <th>Categoría</th>
                                                <th>Almacén</th>
                                                <th>Cantidad Vendida</th>
                                                <th>Precio Promedio</th>
                                                <th>Total Ventas</th>
                                                <th>Costo Total</th>
                                                <th>Utilidad</th>
                                                <th>Margen %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3"><strong>TOTALES</strong></th>
                                                <th id="totalCantidad">0</th>
                                                <th>-</th>
                                                <th id="totalVentas">$0.00</th>
                                                <th id="totalCosto">$0.00</th>
                                                <th id="totalUtilidad">$0.00</th>
                                                <th id="margenPromedio">0%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Top Productos por Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerVentas">
                                            <canvas id="graficoVentasProducto" height="250"></canvas>
                                        </div>
                                        <div id="graficoMensajeVentas" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Distribución por Categoría</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerCategoria">
                                            <canvas id="graficoCategoriaProducto" height="250"></canvas>
                                        </div>
                                        <div id="graficoMensajeCategoria" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
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
    // Variables globales para los gráficos
    let ventasProductoChart = null;
    let categoriaProductoChart = null;

    $(document).ready(function() {
        // Establecer fechas por defecto (mes actual)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
        $('#fecha_fin').val(lastDay.toISOString().split('T')[0]);

        // Generar reporte
        $('#btnGenerarReporte').on('click', function() {
            generarReporte();
        });

        // Limpiar filtros
        $('#btnLimpiarFiltros').on('click', function() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            $('#id_producto').val('');
            $('#id_categoria').val('');
            $('#id_almacen').val('');
            $('#orden').val('total_ventas');
            $('#direccion').val('DESC');
            $('#limite').val('0');
            $('#tablaVentasProducto tbody').empty();
            resetTotales();
            destruirGraficos();
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

        function generarReporte() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idProducto = $('#id_producto').val();
            const idCategoria = $('#id_categoria').val();
            const idAlmacen = $('#id_almacen').val();
            const orden = $('#orden').val();
            const direccion = $('#direccion').val();
            const limite = $('#limite').val();

            if (!fechaInicio || !fechaFin) {
                alertify.error('Por favor seleccione un rango de fechas');
                return;
            }

            if (new Date(fechaInicio) > new Date(fechaFin)) {
                alertify.error('La fecha de inicio no puede ser mayor a la fecha fin');
                return;
            }

            $.ajax({
                url: 'ajax/reportes/ventas-por-producto.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    id_producto: idProducto,
                    id_categoria: idCategoria,
                    id_almacen: idAlmacen,
                    orden: orden,
                    direccion: direccion,
                    limite: limite,
                    action: 'generar'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#btnGenerarReporte').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generando...');
                },
                success: function(response) {
                    if (response.success) {
                        mostrarDatos(response.data);
                        if (typeof Chart !== 'undefined') {
                            generarGraficos(response.data);
                        }
                        $('#btnExportarPDF').prop('disabled', false);
                        $('#btnExportarExcel').prop('disabled', false);
                        alertify.success('Reporte generado correctamente');
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        destruirGraficos();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alertify.error('Error de conexión al generar el reporte');
                    destruirGraficos();
                },
                complete: function() {
                    $('#btnGenerarReporte').prop('disabled', false).html('<i class="fas fa-play me-2"></i>Generar Reporte');
                }
            });
        }

        function mostrarDatos(datos) {
            const tbody = $('#tablaVentasProducto tbody');
            tbody.empty();

            let totalVentas = 0;
            let totalCantidad = 0;
            let totalCosto = 0;
            let totalUtilidad = 0;

            if (datos.length > 0) {
                datos.forEach(function(item) {
                    const utilidad = parseFloat(item.total_ventas) - parseFloat(item.costo_total);
                    const margen = parseFloat(item.total_ventas) > 0 ? (utilidad / parseFloat(item.total_ventas)) * 100 : 0;

                    totalVentas += parseFloat(item.total_ventas);
                    totalCantidad += parseInt(item.cantidad_vendida);
                    totalCosto += parseFloat(item.costo_total);
                    totalUtilidad += utilidad;

                    const fila = `
                    <tr>
                        <td>
                            <strong>${escapeHtml(item.clave_producto)}</strong><br>
                            <small class="text-muted">${escapeHtml(item.nombre_producto)}</small>
                        </td>
                        <td>${escapeHtml(item.nombre_categoria || 'N/A')}</td>
                        <td>${escapeHtml(item.nombre_almacen || 'N/A')}</td>
                        <td class="text-center">${parseInt(item.cantidad_vendida).toLocaleString('es-MX')}</td>
                        <td class="text-end">$${parseFloat(item.precio_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.total_ventas).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.costo_total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end ${utilidad >= 0 ? 'text-success' : 'text-danger'}">
                            <strong>$${utilidad.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                        </td>
                        <td class="text-end ${margen >= 0 ? 'text-success' : 'text-danger'}">
                            <strong>${margen.toFixed(2)}%</strong>
                        </td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                const margenPromedio = totalVentas > 0 ? (totalUtilidad / totalVentas) * 100 : 0;

                $('#totalCantidad').text(totalCantidad.toLocaleString('es-MX'));
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
                $('#margenPromedio').text(margenPromedio.toFixed(2) + '%');
            } else {
                tbody.append('<tr><td colspan="9" class="text-center">No se encontraron datos con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function resetTotales() {
            $('#totalCantidad').text('0');
            $('#totalVentas').text('$0.00');
            $('#totalCosto').text('$0.00');
            $('#totalUtilidad').text('$0.00');
            $('#margenPromedio').text('0%');
        }

        function generarGraficos(datos) {
            destruirGraficos();

            if (datos.length === 0) {
                $('#graficoContainerVentas, #graficoContainerCategoria').hide();
                $('#graficoMensajeVentas, #graficoMensajeCategoria').show();
                return;
            }

            $('#graficoContainerVentas, #graficoContainerCategoria').show();
            $('#graficoMensajeVentas, #graficoMensajeCategoria').hide();

            // Gráfico de barras - Top productos por ventas (máximo 10)
            const topProductos = datos.slice(0, 10);
            const labelsVentas = topProductos.map(item => item.clave_producto);
            const valoresVentas = topProductos.map(item => parseFloat(item.total_ventas));

            const ctxVentas = document.getElementById('graficoVentasProducto');
            ventasProductoChart = new Chart(ctxVentas, {
                type: 'bar',
                data: {
                    labels: labelsVentas,
                    datasets: [{
                        label: 'Total de Ventas',
                        data: valoresVentas,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
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
                            text: 'Top 10 Productos por Ventas'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Ventas: $${context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-MX');
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });

            // Gráfico de pie - Distribución por categoría
            const categoriasMap = {};
            datos.forEach(item => {
                const categoria = item.nombre_categoria || 'Sin Categoría';
                if (!categoriasMap[categoria]) {
                    categoriasMap[categoria] = 0;
                }
                categoriasMap[categoria] += parseFloat(item.total_ventas);
            });

            const labelsCategoria = Object.keys(categoriasMap);
            const valoresCategoria = Object.values(categoriasMap);

            const colores = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#C9CBCF', '#7CB5EC', '#434348'
            ];

            const ctxCategoria = document.getElementById('graficoCategoriaProducto');
            categoriaProductoChart = new Chart(ctxCategoria, {
                type: 'pie',
                data: {
                    labels: labelsCategoria,
                    datasets: [{
                        data: valoresCategoria,
                        backgroundColor: colores.slice(0, labelsCategoria.length),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Distribución de Ventas por Categoría'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = valoresCategoria.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: $${value.toLocaleString('es-MX')} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function destruirGraficos() {
            if (ventasProductoChart !== null) {
                ventasProductoChart.destroy();
                ventasProductoChart = null;
            }
            if (categoriaProductoChart !== null) {
                categoriaProductoChart.destroy();
                categoriaProductoChart = null;
            }
            $('#graficoContainerVentas, #graficoContainerCategoria').hide();
            $('#graficoMensajeVentas, #graficoMensajeCategoria').show();
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function exportarPDF() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idProducto = $('#id_producto').val();
            const idCategoria = $('#id_categoria').val();
            const idAlmacen = $('#id_almacen').val();
            const orden = $('#orden').val();
            const direccion = $('#direccion').val();
            const limite = $('#limite').val();

            const url = `ajax/reportes/exportar-pdf-ventas-producto.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_producto=${idProducto}&id_categoria=${idCategoria}&id_almacen=${idAlmacen}&orden=${orden}&direccion=${direccion}&limite=${limite}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idProducto = $('#id_producto').val();
            const idCategoria = $('#id_categoria').val();
            const idAlmacen = $('#id_almacen').val();
            const orden = $('#orden').val();
            const direccion = $('#direccion').val();
            const limite = $('#limite').val();

            const url = `ajax/reportes/exportar-excel-ventas-producto.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_producto=${idProducto}&id_categoria=${idCategoria}&id_almacen=${idAlmacen}&orden=${orden}&direccion=${direccion}&limite=${limite}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    #graficoVentasProducto,
    #graficoCategoriaProducto {
        max-height: 250px;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }
</style>