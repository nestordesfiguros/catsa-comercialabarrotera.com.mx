<?php
// contenido/reportes-ventas-por-vendedor.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas por Vendedor</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i>Reporte de Ventas por Vendedor</h3>
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
                                <label for="id_vendedor" class="form-label">Vendedor</label>
                                <select class="form-select" id="id_vendedor" name="id_vendedor">
                                    <option value="">Todos los vendedores</option>
                                    <?php
                                    $sqlVendedores = "SELECT id, nombre, apellido1, apellido2 
                                                     FROM cat_vendedores 
                                                     WHERE estatus = 1 
                                                     ORDER BY nombre, apellido1";
                                    $vendedores = $clsConsulta->consultaGeneral($sqlVendedores);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($vendedores as $i => $vend) {
                                            $nombreCompleto = $vend['nombre'] . ' ' . $vend['apellido1'] . ' ' . $vend['apellido2'];
                                            echo "<option value=\"{$vend['id']}\">{$nombreCompleto}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estatus" class="form-label">Estatus</label>
                                <select class="form-select" id="estatus" name="estatus">
                                    <option value="">Todos</option>
                                    <option value="procesada">Procesadas</option>
                                    <option value="pendiente">Pendientes</option>
                                    <option value="cancelada">Canceladas</option>
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

                        <!-- Resultados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tablaVentasVendedor" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Vendedor</th>
                                                <th>Total Ventas</th>
                                                <th>Cantidad de Ventas</th>
                                                <th>Promedio por Venta</th>
                                                <th>Venta Mínima</th>
                                                <th>Venta Máxima</th>
                                                <th>Comisión Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th><strong>TOTALES</strong></th>
                                                <th id="totalVentas">$0.00</th>
                                                <th id="totalCantidad">0</th>
                                                <th id="promedioGeneral">$0.00</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th id="totalComision">$0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Distribución de Ventas por Vendedor</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainer">
                                            <canvas id="graficoVentasVendedor" height="100"></canvas>
                                        </div>
                                        <div id="graficoMensaje" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
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

<!-- Cargar Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Variable global para el gráfico
    let ventasChart = null;

    $(document).ready(function() {
        // Verificar si Chart.js está cargado
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no está cargado');
            $('#graficoContainer').hide();
            $('#graficoMensaje').show().html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Chart.js no está disponible. El gráfico no se mostrará.
            </div>
        `);
        }

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
            $('#id_vendedor').val('');
            $('#estatus').val('');
            $('#tablaVentasVendedor tbody').empty();
            resetTotales();
            destruirGrafico();
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
            const idVendedor = $('#id_vendedor').val();
            const estatus = $('#estatus').val();

            if (!fechaInicio || !fechaFin) {
                alertify.error('Por favor seleccione un rango de fechas');
                return;
            }

            if (new Date(fechaInicio) > new Date(fechaFin)) {
                alertify.error('La fecha de inicio no puede ser mayor a la fecha fin');
                return;
            }

            $.ajax({
                url: 'ajax/reportes/ventas-por-vendedor.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    id_vendedor: idVendedor,
                    estatus: estatus,
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
                            generarGrafico(response.data);
                        }
                        $('#btnExportarPDF').prop('disabled', false);
                        $('#btnExportarExcel').prop('disabled', false);
                        alertify.success('Reporte generado correctamente');
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        destruirGrafico();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alertify.error('Error de conexión al generar el reporte');
                    destruirGrafico();
                },
                complete: function() {
                    $('#btnGenerarReporte').prop('disabled', false).html('<i class="fas fa-play me-2"></i>Generar Reporte');
                }
            });
        }

        function mostrarDatos(datos) {
            const tbody = $('#tablaVentasVendedor tbody');
            tbody.empty();

            let totalVentas = 0;
            let totalCantidad = 0;
            let totalComision = 0;

            if (datos.length > 0) {
                datos.forEach(function(item) {
                    const promedioVenta = item.cantidad_ventas > 0 ? item.total_ventas / item.cantidad_ventas : 0;
                    const comisionTotal = item.total_ventas * (item.porcentaje_comision / 100);

                    totalVentas += parseFloat(item.total_ventas);
                    totalCantidad += parseInt(item.cantidad_ventas);
                    totalComision += comisionTotal;

                    const fila = `
                    <tr>
                        <td>${escapeHtml(item.nombre_vendedor)}</td>
                        <td class="text-end">$${parseFloat(item.total_ventas).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${item.cantidad_ventas}</td>
                        <td class="text-end">$${promedioVenta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.venta_minima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.venta_maxima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${comisionTotal.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                const promedioGeneral = totalCantidad > 0 ? totalVentas / totalCantidad : 0;

                $('#totalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalCantidad').text(totalCantidad);
                $('#promedioGeneral').text('$' + promedioGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalComision').text('$' + totalComision.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                tbody.append('<tr><td colspan="7" class="text-center">No se encontraron datos con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function resetTotales() {
            $('#totalVentas').text('$0.00');
            $('#totalCantidad').text('0');
            $('#promedioGeneral').text('$0.00');
            $('#totalComision').text('$0.00');
        }

        function generarGrafico(datos) {
            const ctx = document.getElementById('graficoVentasVendedor');

            // Destruir gráfico anterior si existe
            destruirGrafico();

            if (datos.length === 0) {
                $('#graficoContainer').hide();
                $('#graficoMensaje').show();
                return;
            }

            $('#graficoContainer').show();
            $('#graficoMensaje').hide();

            const labels = datos.map(item => item.nombre_vendedor);
            const valores = datos.map(item => parseFloat(item.total_ventas));

            // Colores para el gráfico
            const colores = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                '#7CB5EC', '#434348', '#90ED7D', '#F7A35C',
                '#8085E9', '#F15C80', '#E4D354', '#2B908F'
            ];

            ventasChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total de Ventas',
                        data: valores,
                        backgroundColor: colores.slice(0, datos.length),
                        borderColor: colores.slice(0, datos.length),
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
                            text: 'Distribución de Ventas por Vendedor',
                            font: {
                                size: 16
                            }
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
                            },
                            title: {
                                display: true,
                                text: 'Monto de Ventas'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Vendedores'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function destruirGrafico() {
            if (ventasChart !== null) {
                ventasChart.destroy();
                ventasChart = null;
            }
            $('#graficoContainer').hide();
            $('#graficoMensaje').show();
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
            const idVendedor = $('#id_vendedor').val();
            const estatus = $('#estatus').val();

            const url = `ajax/reportes/exportar-pdf-ventas-vendedor.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_vendedor=${idVendedor}&estatus=${estatus}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idVendedor = $('#id_vendedor').val();
            const estatus = $('#estatus').val();

            const url = `ajax/reportes/exportar-excel-ventas-vendedor.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_vendedor=${idVendedor}&estatus=${estatus}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .list-group-item a {
        text-decoration: none;
        color: #495057;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .list-group-item a:hover {
        color: #007bff;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    #graficoVentasVendedor {
        max-height: 400px;
    }
</style>