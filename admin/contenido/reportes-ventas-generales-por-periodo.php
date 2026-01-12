<?php
// contenido/reportes-ventas-generales-por-periodo.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas Generales por Periodo</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-chart-line me-2"></i>Reporte de Ventas Generales por Periodo</h3>
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
                                <label for="tipo_periodo" class="form-label">Agrupar por</label>
                                <select class="form-select" id="tipo_periodo" name="tipo_periodo">
                                    <option value="dia">Día</option>
                                    <option value="semana">Semana</option>
                                    <option value="mes" selected>Mes</option>
                                    <option value="anio">Año</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="tipo_venta" class="form-label">Tipo de Venta</label>
                                <select class="form-select" id="tipo_venta" name="tipo_venta">
                                    <option value="">Todos</option>
                                    <option value="contado">Contado</option>
                                    <option value="credito">Crédito</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
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
                                <label for="estatus" class="form-label">Estatus</label>
                                <select class="form-select" id="estatus" name="estatus">
                                    <option value="">Todos</option>
                                    <option value="procesada">Procesadas</option>
                                    <option value="pendiente">Pendientes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="orden" class="form-label">Ordenar por</label>
                                <select class="form-select" id="orden" name="orden">
                                    <option value="periodo">Periodo</option>
                                    <option value="total_ventas">Total Ventas</option>
                                    <option value="cantidad_ventas">Cantidad de Ventas</option>
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen General del Periodo</h5>
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
                                                        <h6 class="card-title">Cantidad de Ventas</h6>
                                                        <h3 class="text-success" id="resumenCantidadVentas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Ticket Promedio</h6>
                                                        <h3 class="text-warning" id="resumenTicketPromedio">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Días con Ventas</h6>
                                                        <h3 class="text-info" id="resumenDiasVentas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resultados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tablaVentasPeriodo" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Periodo</th>
                                                <th>Cantidad de Ventas</th>
                                                <th>Total Ventas</th>
                                                <th>Ticket Promedio</th>
                                                <th>Venta Mínima</th>
                                                <th>Venta Máxima</th>
                                                <th>% del Total</th>
                                                <th>Tendencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th><strong>TOTALES</strong></th>
                                                <th id="totalCantidad">0</th>
                                                <th id="totalVentas">$0.00</th>
                                                <th id="ticketPromedio">$0.00</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>100%</th>
                                                <th>-</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos -->
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Evolución de Ventas por Periodo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerLineal">
                                            <canvas id="graficoLinealVentas" height="300"></canvas>
                                        </div>
                                        <div id="graficoMensajeLineal" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Distribución por Tipo de Venta</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerTipoVenta">
                                            <canvas id="graficoTipoVenta" height="300"></canvas>
                                        </div>
                                        <div id="graficoMensajeTipoVenta" class="text-center text-muted" style="display: none;">
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

<script>
    // Variables globales para los gráficos
    let ventasLinealChart = null;
    let tipoVentaChart = null;

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
            $('#tipo_periodo').val('mes');
            $('#tipo_venta').val('');
            $('#id_vendedor').val('');
            $('#id_almacen').val('');
            $('#estatus').val('');
            $('#orden').val('periodo');
            $('#tablaVentasPeriodo tbody').empty();
            resetTotales();
            resetResumen();
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
            const tipoPeriodo = $('#tipo_periodo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const estatus = $('#estatus').val();
            const orden = $('#orden').val();

            if (!fechaInicio || !fechaFin) {
                alertify.error('Por favor seleccione un rango de fechas');
                return;
            }

            if (new Date(fechaInicio) > new Date(fechaFin)) {
                alertify.error('La fecha de inicio no puede ser mayor a la fecha fin');
                return;
            }

            $.ajax({
                url: 'ajax/reportes/ventas-generales-por-periodo.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    tipo_periodo: tipoPeriodo,
                    tipo_venta: tipoVenta,
                    id_vendedor: idVendedor,
                    id_almacen: idAlmacen,
                    estatus: estatus,
                    orden: orden,
                    action: 'generar'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#btnGenerarReporte').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generando...');
                },
                success: function(response) {
                    if (response.success) {
                        mostrarResumen(response.resumen);
                        mostrarDatos(response.data);
                        if (typeof Chart !== 'undefined') {
                            generarGraficos(response.data, response.resumen);
                        }
                        $('#btnExportarPDF').prop('disabled', false);
                        $('#btnExportarExcel').prop('disabled', false);
                        alertify.success('Reporte generado correctamente');
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        resetResumen();
                        destruirGraficos();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alertify.error('Error de conexión al generar el reporte');
                    resetResumen();
                    destruirGraficos();
                },
                complete: function() {
                    $('#btnGenerarReporte').prop('disabled', false).html('<i class="fas fa-play me-2"></i>Generar Reporte');
                }
            });
        }

        function mostrarResumen(resumen) {
            $('#resumenTotalVentas').text('$' + parseFloat(resumen.total_ventas).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#resumenCantidadVentas').text(parseInt(resumen.cantidad_ventas).toLocaleString('es-MX'));
            $('#resumenTicketPromedio').text('$' + parseFloat(resumen.ticket_promedio).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#resumenDiasVentas').text(parseInt(resumen.dias_con_ventas).toLocaleString('es-MX'));
        }

        function resetResumen() {
            $('#resumenTotalVentas').text('$0.00');
            $('#resumenCantidadVentas').text('0');
            $('#resumenTicketPromedio').text('$0.00');
            $('#resumenDiasVentas').text('0');
        }

        function mostrarDatos(datos) {
            const tbody = $('#tablaVentasPeriodo tbody');
            tbody.empty();

            let totalVentas = 0;
            let totalCantidad = 0;

            if (datos.length > 0) {
                datos.forEach(function(item, index) {
                    const porcentajeTotal = (parseFloat(item.total_ventas) / parseFloat(datos[0].total_general)) * 100;
                    const tendencia = index > 0 ? calcularTendencia(parseFloat(datos[index - 1].total_ventas), parseFloat(item.total_ventas)) : 'N/A';

                    totalVentas += parseFloat(item.total_ventas);
                    totalCantidad += parseInt(item.cantidad_ventas);

                    const fila = `
                    <tr>
                        <td><strong>${escapeHtml(item.periodo)}</strong></td>
                        <td class="text-center">${parseInt(item.cantidad_ventas).toLocaleString('es-MX')}</td>
                        <td class="text-end">$${parseFloat(item.total_ventas).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.venta_minima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.venta_maxima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">${porcentajeTotal.toFixed(1)}%</td>
                        <td class="text-center">${tendencia}</td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                const ticketPromedioGeneral = totalCantidad > 0 ? totalVentas / totalCantidad : 0;

                $('#totalCantidad').text(totalCantidad.toLocaleString('es-MX'));
                $('#totalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#ticketPromedio').text('$' + ticketPromedioGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No se encontraron datos con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function calcularTendencia(ventaAnterior, ventaActual) {
            if (ventaAnterior === 0) return '<span class="badge bg-success">↑ Nuevo</span>';

            const variacion = ((ventaActual - ventaAnterior) / ventaAnterior) * 100;

            if (variacion > 10) {
                return `<span class="badge bg-success">↑ ${Math.abs(variacion).toFixed(1)}%</span>`;
            } else if (variacion < -10) {
                return `<span class="badge bg-danger">↓ ${Math.abs(variacion).toFixed(1)}%</span>`;
            } else {
                return `<span class="badge bg-secondary">→ ${Math.abs(variacion).toFixed(1)}%</span>`;
            }
        }

        function resetTotales() {
            $('#totalCantidad').text('0');
            $('#totalVentas').text('$0.00');
            $('#ticketPromedio').text('$0.00');
        }

        function generarGraficos(datos, resumen) {
            destruirGraficos();

            if (datos.length === 0) {
                $('#graficoContainerLineal, #graficoContainerTipoVenta').hide();
                $('#graficoMensajeLineal, #graficoMensajeTipoVenta').show();
                return;
            }

            $('#graficoContainerLineal, #graficoContainerTipoVenta').show();
            $('#graficoMensajeLineal, #graficoMensajeTipoVenta').hide();

            // Gráfico lineal - Evolución de ventas
            const labelsLineal = datos.map(item => item.periodo);
            const valoresLineal = datos.map(item => parseFloat(item.total_ventas));

            const ctxLineal = document.getElementById('graficoLinealVentas');
            ventasLinealChart = new Chart(ctxLineal, {
                type: 'line',
                data: {
                    labels: labelsLineal,
                    datasets: [{
                        label: 'Total de Ventas',
                        data: valoresLineal,
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderColor: '#3498db',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
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
                            text: 'Evolución de Ventas por Periodo'
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

            // Gráfico de barras - Distribución por tipo de venta
            const tiposVentaMap = {
                'contado': parseFloat(resumen.ventas_contado || 0),
                'credito': parseFloat(resumen.ventas_credito || 0)
            };

            const labelsTipoVenta = ['Contado', 'Crédito'];
            const valoresTipoVenta = [tiposVentaMap['contado'], tiposVentaMap['credito']];

            const ctxTipoVenta = document.getElementById('graficoTipoVenta');
            tipoVentaChart = new Chart(ctxTipoVenta, {
                type: 'bar',
                data: {
                    labels: labelsTipoVenta,
                    datasets: [{
                        data: valoresTipoVenta,
                        backgroundColor: ['#27ae60', '#f39c12'],
                        borderColor: ['#219653', '#d68910'],
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
                            text: 'Distribución por Tipo de Venta'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = valoresTipoVenta.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: $${context.parsed.y.toLocaleString('es-MX')} (${percentage}%)`;
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
                        }
                    }
                }
            });
        }

        function destruirGraficos() {
            if (ventasLinealChart !== null) {
                ventasLinealChart.destroy();
                ventasLinealChart = null;
            }
            if (tipoVentaChart !== null) {
                tipoVentaChart.destroy();
                tipoVentaChart = null;
            }
            $('#graficoContainerLineal, #graficoContainerTipoVenta').hide();
            $('#graficoMensajeLineal, #graficoMensajeTipoVenta').show();
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
            const tipoPeriodo = $('#tipo_periodo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const estatus = $('#estatus').val();
            const orden = $('#orden').val();

            const url = `ajax/reportes/exportar-pdf-ventas-periodo.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_periodo=${tipoPeriodo}&tipo_venta=${tipoVenta}&id_vendedor=${idVendedor}&id_almacen=${idAlmacen}&estatus=${estatus}&orden=${orden}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const tipoPeriodo = $('#tipo_periodo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const estatus = $('#estatus').val();
            const orden = $('#orden').val();

            const url = `ajax/reportes/exportar-excel-ventas-periodo.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_periodo=${tipoPeriodo}&tipo_venta=${tipoVenta}&id_vendedor=${idVendedor}&id_almacen=${idAlmacen}&estatus=${estatus}&orden=${orden}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    #graficoLinealVentas,
    #graficoTipoVenta {
        max-height: 300px;
    }

    .badge {
        font-size: 0.75em;
    }

    .card.bg-light {
        border: 1px solid #dee2e6;
    }
</style>