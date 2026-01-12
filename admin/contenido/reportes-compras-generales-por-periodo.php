<?php
// contenido/reportes-compras-generales-por-periodo.php
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compras Generales por Periodo</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i>Reporte de Compras Generales por Periodo</h3>
                    </div>
                    <div class="card-body">
                        <form id="formReporteComprasGenerales">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha Inicio:</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_fin">Fecha Fin:</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="agrupacion">Agrupación:</label>
                                        <select class="form-control" id="agrupacion" name="agrupacion">
                                            <option value="diario">Diario</option>
                                            <option value="semanal">Semanal</option>
                                            <option value="mensual" selected>Mensual</option>
                                            <option value="anual">Anual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estatus">Estatus:</label>
                                        <select class="form-control" id="estatus" name="estatus">
                                            <option value="">Todos</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="procesada">Procesada</option>
                                            <option value="cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Generar Reporte
                                    </button>
                                    <button type="button" id="btnExportarPDF" class="btn btn-danger" disabled>
                                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                                    </button>
                                    <button type="button" id="btnExportarExcel" class="btn btn-success" disabled>
                                        <i class="fas fa-file-excel me-1"></i> Exportar Excel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Resumen Gráfico</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="graficoCompras" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tablaComprasGenerales" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr class="bg-success text-white">
                                                <th>Periodo</th>
                                                <th>Total Compras</th>
                                                <th>Cantidad de Compras</th>
                                                <th>Promedio por Compra</th>
                                                <th>Compra Mínima</th>
                                                <th>Compra Máxima</th>
                                                <th>Proveedores Únicos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargarán via AJAX -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th>TOTALES</th>
                                                <th id="totalCompras">$0.00</th>
                                                <th id="totalCantidad">0</th>
                                                <th id="promedioCompra">$0.00</th>
                                                <th id="compraMinima">$0.00</th>
                                                <th id="compraMaxima">$0.00</th>
                                                <th id="totalProveedores">0</th>
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
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        let graficoCompras = null;

        // Establecer fechas por defecto (últimos 6 meses)
        const today = new Date();
        const sixMonthsAgo = new Date(today.getFullYear(), today.getMonth() - 6, 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        $('#fecha_inicio').val(sixMonthsAgo.toISOString().split('T')[0]);
        $('#fecha_fin').val(lastDay.toISOString().split('T')[0]);

        // Generar reporte
        $('#formReporteComprasGenerales').on('submit', function(e) {
            e.preventDefault();
            generarReporte();
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
            const formData = new FormData();
            formData.append('action', 'generar');
            formData.append('fecha_inicio', $('#fecha_inicio').val());
            formData.append('fecha_fin', $('#fecha_fin').val());
            formData.append('agrupacion', $('#agrupacion').val());
            formData.append('estatus', $('#estatus').val());

            $.ajax({
                url: 'ajax/reportes/compras-generales-por-periodo.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#tablaComprasGenerales tbody').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-success" role="status"></div> Cargando...</td></tr>');
                    if (graficoCompras) {
                        graficoCompras.destroy();
                    }
                },
                success: function(response) {
                    if (response.success) {
                        mostrarDatos(response.data);
                        crearGrafico(response.data);
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', false);
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        $('#tablaComprasGenerales tbody').html('<tr><td colspan="7" class="text-center text-muted">No se encontraron datos</td></tr>');
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                    }
                },
                error: function() {
                    alertify.error('Error de conexión');
                    $('#tablaComprasGenerales tbody').html('<tr><td colspan="7" class="text-center text-muted">Error al cargar datos</td></tr>');
                    $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                }
            });
        }

        function mostrarDatos(datos) {
            let html = '';
            let totalCompras = 0;
            let totalCantidad = 0;
            let compraMinima = Number.MAX_VALUE;
            let compraMaxima = 0;
            let totalProveedores = new Set();

            if (datos && datos.length > 0) {
                datos.forEach(function(item) {
                    const promedio = item.cantidad_compras > 0 ? item.total_compras / item.cantidad_compras : 0;

                    totalCompras += parseFloat(item.total_compras);
                    totalCantidad += parseInt(item.cantidad_compras);

                    if (item.compra_minima < compraMinima) compraMinima = item.compra_minima;
                    if (item.compra_maxima > compraMaxima) compraMaxima = item.compra_maxima;

                    // Contar proveedores únicos
                    if (item.proveedores_unicos) {
                        const proveedores = item.proveedores_unicos.split(',');
                        proveedores.forEach(prov => totalProveedores.add(prov.trim()));
                    }

                    html += `
                    <tr>
                        <td>${escapeHtml(item.periodo)}</td>
                        <td class="text-end">$${parseFloat(item.total_compras).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${item.cantidad_compras}</td>
                        <td class="text-end">$${promedio.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_minima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_maxima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${item.proveedores_unicos_count || 0}</td>
                    </tr>
                `;
                });

                const promedioGeneral = totalCantidad > 0 ? totalCompras / totalCantidad : 0;

                $('#totalCompras').text('$' + totalCompras.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalCantidad').text(totalCantidad);
                $('#promedioCompra').text('$' + promedioGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#compraMinima').text('$' + (compraMinima !== Number.MAX_VALUE ? compraMinima.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '0.00'));
                $('#compraMaxima').text('$' + compraMaxima.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalProveedores').text(totalProveedores.size);
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted">No se encontraron compras con los filtros seleccionados</td></tr>';
                $('#totalCompras').text('$0.00');
                $('#totalCantidad').text('0');
                $('#promedioCompra').text('$0.00');
                $('#compraMinima').text('$0.00');
                $('#compraMaxima').text('$0.00');
                $('#totalProveedores').text('0');
            }

            $('#tablaComprasGenerales tbody').html(html);
        }

        function crearGrafico(datos) {
            const ctx = document.getElementById('graficoCompras').getContext('2d');

            if (!datos || datos.length === 0) {
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#999';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos para mostrar', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const labels = datos.map(item => item.periodo);
            const comprasData = datos.map(item => parseFloat(item.total_compras));
            const cantidadData = datos.map(item => parseInt(item.cantidad_compras));

            graficoCompras = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Total Compras ($)',
                            data: comprasData,
                            backgroundColor: 'rgba(39, 174, 96, 0.8)',
                            borderColor: 'rgba(39, 174, 96, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Cantidad de Compras',
                            data: cantidadData,
                            backgroundColor: 'rgba(52, 152, 219, 0.6)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1,
                            type: 'line',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Total Compras ($)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-MX');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Cantidad de Compras'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'y') {
                                        label += '$' + context.parsed.y.toLocaleString('es-MX', {
                                            minimumFractionDigits: 2
                                        });
                                    } else {
                                        label += context.parsed.y.toLocaleString('es-MX');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        function exportarPDF() {
            const params = new URLSearchParams({
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                agrupacion: $('#agrupacion').val(),
                estatus: $('#estatus').val()
            });

            window.open('ajax/reportes/exportar-pdf-compras-generales.php?' + params.toString(), '_blank');
        }

        function exportarExcel() {
            const params = new URLSearchParams({
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                agrupacion: $('#agrupacion').val(),
                estatus: $('#estatus').val()
            });

            window.open('ajax/reportes/exportar-excel-compras-generales.php?' + params.toString(), '_blank');
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    });
</script>