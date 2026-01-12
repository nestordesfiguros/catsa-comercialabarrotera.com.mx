<?php
// contenido/reportes-ventas-anuales-mensuales.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas Anuales/Mensuales</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i>Ventas Anuales/Mensuales</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                                <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                    <option value="anual">Anual</option>
                                    <option value="mensual" selected>Mensual</option>
                                    <option value="comparativo">Comparativo Anual</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="anio" class="form-label">Año</label>
                                <select class="form-select" id="anio" name="anio">
                                    <?php
                                    $currentYear = date('Y');
                                    for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                        $selected = $i == $currentYear ? 'selected' : '';
                                        echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="anio_comparativo" class="form-label">Año Comparativo</label>
                                <select class="form-select" id="anio_comparativo" name="anio_comparativo" style="display: none;">
                                    <?php
                                    for ($i = $currentYear - 1; $i >= $currentYear - 5; $i--) {
                                        $selected = $i == $currentYear - 1 ? 'selected' : '';
                                        echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
                                    }
                                    ?>
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
                            <div class="col-md-4">
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
                                            $nombreCompleto = trim($vend['nombre'] . ' ' . $vend['apellido1'] . ' ' . ($vend['apellido2'] ?? ''));
                                            echo "<option value=\"{$vend['id']}\">{$nombreCompleto}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label for="metrica" class="form-label">Métrica Principal</label>
                                <select class="form-select" id="metrica" name="metrica">
                                    <option value="ventas">Ventas Totales</option>
                                    <option value="cantidad">Cantidad de Ventas</option>
                                    <option value="ticket_promedio">Ticket Promedio</option>
                                    <option value="productos">Productos Vendidos</option>
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
                                                        <h6 class="card-title">Ventas Totales</h6>
                                                        <h3 class="text-primary" id="resumenVentasTotales">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Crecimiento vs Año Anterior</h6>
                                                        <h3 class="text-success" id="resumenCrecimiento">0%</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Mejor Mes</h6>
                                                        <h3 class="text-warning" id="resumenMejorMes">-</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Ventas Promedio Mensual</h6>
                                                        <h3 class="text-info" id="resumenPromedioMensual">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico Principal -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Evolución de Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerPrincipal">
                                            <canvas id="graficoPrincipal" height="400"></canvas>
                                        </div>
                                        <div id="graficoMensajePrincipal" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resultados Detallados -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle por Periodo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaVentasPeriodo" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th id="columnaPeriodo">Mes</th>
                                                        <th>Ventas Totales</th>
                                                        <th>Cantidad Ventas</th>
                                                        <th>Ticket Promedio</th>
                                                        <th>Productos Vendidos</th>
                                                        <th>Crecimiento</th>
                                                        <th>% del Total</th>
                                                        <th>Tendencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th><strong>TOTALES / PROMEDIOS</strong></th>
                                                        <th id="totalVentas">$0.00</th>
                                                        <th id="totalCantidad">0</th>
                                                        <th id="promedioTicket">$0.00</th>
                                                        <th id="totalProductos">0</th>
                                                        <th>-</th>
                                                        <th>100%</th>
                                                        <th>-</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráficos Secundarios -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Distribución por Tipo de Venta</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerTipoVenta">
                                            <canvas id="graficoTipoVenta" height="300"></canvas>
                                        </div>
                                        <div id="graficoMensajeTipoVenta" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                            <p>No hay datos para mostrar el gráfico</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title"><i class="fas fa-trophy me-2"></i>Top 5 Meses</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerTopMeses">
                                            <canvas id="graficoTopMeses" height="300"></canvas>
                                        </div>
                                        <div id="graficoMensajeTopMeses" class="text-center text-muted" style="display: none;">
                                            <i class="fas fa-trophy fa-3x mb-3"></i>
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
    let graficoPrincipal = null;
    let graficoTipoVenta = null;
    let graficoTopMeses = null;

    $(document).ready(function() {
        // Manejar cambio de tipo de reporte
        $('#tipo_reporte').on('change', function() {
            const tipoReporte = $(this).val();
            if (tipoReporte === 'comparativo') {
                $('#anio_comparativo').show();
                $('#columnaPeriodo').text('Mes');
            } else {
                $('#anio_comparativo').hide();
                $('#columnaPeriodo').text(tipoReporte === 'anual' ? 'Año' : 'Mes');
            }
        });

        // Generar reporte
        $('#btnGenerarReporte').on('click', function() {
            generarReporte();
        });

        // Limpiar filtros
        $('#btnLimpiarFiltros').on('click', function() {
            $('#tipo_reporte').val('mensual');
            $('#anio').val(new Date().getFullYear());
            $('#anio_comparativo').val(new Date().getFullYear() - 1).hide();
            $('#tipo_venta').val('');
            $('#id_vendedor').val('');
            $('#id_almacen').val('');
            $('#metrica').val('ventas');
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
            const tipoReporte = $('#tipo_reporte').val();
            const anio = $('#anio').val();
            const anioComparativo = $('#anio_comparativo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const metrica = $('#metrica').val();

            $.ajax({
                url: 'ajax/reportes/ventas-anuales-mensuales.php',
                type: 'POST',
                data: {
                    tipo_reporte: tipoReporte,
                    anio: anio,
                    anio_comparativo: anioComparativo,
                    tipo_venta: tipoVenta,
                    id_vendedor: idVendedor,
                    id_almacen: idAlmacen,
                    metrica: metrica,
                    action: 'generar'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#btnGenerarReporte').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generando...');
                },
                success: function(response) {
                    if (response.success) {
                        mostrarResumen(response.resumen);
                        mostrarDatos(response.data, tipoReporte);
                        if (typeof Chart !== 'undefined') {
                            generarGraficos(response.data, response.resumen, tipoReporte, metrica);
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
            $('#resumenVentasTotales').text('$' + parseFloat(resumen.ventas_totales).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            const crecimiento = parseFloat(resumen.crecimiento_anual);
            const crecimientoClass = crecimiento >= 0 ? 'text-success' : 'text-danger';
            const crecimientoSymbol = crecimiento >= 0 ? '+' : '';
            $('#resumenCrecimiento').removeClass('text-success text-danger').addClass(crecimientoClass)
                .text(crecimientoSymbol + crecimiento.toFixed(1) + '%');

            $('#resumenMejorMes').text(resumen.mejor_mes || '-');
            $('#resumenPromedioMensual').text('$' + parseFloat(resumen.promedio_mensual).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        function resetResumen() {
            $('#resumenVentasTotales').text('$0.00');
            $('#resumenCrecimiento').text('0%').removeClass('text-success text-danger').addClass('text-success');
            $('#resumenMejorMes').text('-');
            $('#resumenPromedioMensual').text('$0.00');
        }

        function mostrarDatos(datos, tipoReporte) {
            const tbody = $('#tablaVentasPeriodo tbody');
            tbody.empty();

            let totalVentas = 0;
            let totalCantidad = 0;
            let totalProductos = 0;
            let mejorMes = {
                valor: 0,
                nombre: ''
            };

            if (datos.length > 0) {
                datos.forEach(function(item, index) {
                    const ventas = parseFloat(item.total_ventas);
                    const cantidad = parseInt(item.cantidad_ventas);
                    const productos = parseInt(item.total_productos);
                    const ticketPromedio = cantidad > 0 ? ventas / cantidad : 0;

                    totalVentas += ventas;
                    totalCantidad += cantidad;
                    totalProductos += productos;

                    if (ventas > mejorMes.valor) {
                        mejorMes.valor = ventas;
                        mejorMes.nombre = item.periodo;
                    }

                    const crecimiento = index > 0 ? calcularCrecimiento(parseFloat(datos[index - 1].total_ventas), ventas) : 0;
                    const porcentajeTotal = (ventas / totalVentas) * 100;
                    const tendencia = getTendenciaIcon(crecimiento);

                    const fila = `
                    <tr>
                        <td><strong>${escapeHtml(item.periodo)}</strong></td>
                        <td class="text-end">$${ventas.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${cantidad.toLocaleString('es-MX')}</td>
                        <td class="text-end">$${ticketPromedio.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${productos.toLocaleString('es-MX')}</td>
                        <td class="text-center ${crecimiento >= 0 ? 'text-success' : 'text-danger'}">
                            ${crecimiento > 0 ? '+' : ''}${crecimiento.toFixed(1)}%
                        </td>
                        <td class="text-end">${porcentajeTotal.toFixed(1)}%</td>
                        <td class="text-center">${tendencia}</td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                const promedioTicket = totalCantidad > 0 ? totalVentas / totalCantidad : 0;

                $('#totalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalCantidad').text(totalCantidad.toLocaleString('es-MX'));
                $('#promedioTicket').text('$' + promedioTicket.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalProductos').text(totalProductos.toLocaleString('es-MX'));
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No se encontraron datos con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function calcularCrecimiento(anterior, actual) {
            if (anterior === 0) return actual > 0 ? 100 : 0;
            return ((actual - anterior) / anterior) * 100;
        }

        function getTendenciaIcon(crecimiento) {
            if (crecimiento > 10) {
                return '<i class="fas fa-arrow-up text-success" title="Fuerte crecimiento"></i>';
            } else if (crecimiento > 0) {
                return '<i class="fas fa-arrow-up text-success" title="Crecimiento moderado"></i>';
            } else if (crecimiento < -10) {
                return '<i class="fas fa-arrow-down text-danger" title="Fuerte decrecimiento"></i>';
            } else if (crecimiento < 0) {
                return '<i class="fas fa-arrow-down text-warning" title="Decrecimiento moderado"></i>';
            } else {
                return '<i class="fas fa-minus text-secondary" title="Sin cambios"></i>';
            }
        }

        function resetTotales() {
            $('#totalVentas').text('$0.00');
            $('#totalCantidad').text('0');
            $('#promedioTicket').text('$0.00');
            $('#totalProductos').text('0');
        }

        function generarGraficos(datos, resumen, tipoReporte, metrica) {
            destruirGraficos();

            if (datos.length === 0) {
                $('#graficoContainerPrincipal, #graficoContainerTipoVenta, #graficoContainerTopMeses').hide();
                $('#graficoMensajePrincipal, #graficoMensajeTipoVenta, #graficoMensajeTopMeses').show();
                return;
            }

            $('#graficoContainerPrincipal, #graficoContainerTipoVenta, #graficoContainerTopMeses').show();
            $('#graficoMensajePrincipal, #graficoMensajeTipoVenta, #graficoMensajeTopMeses').hide();

            // Gráfico principal - Evolución
            const labels = datos.map(item => item.periodo);
            let datasetLabel = '';
            let valores = [];

            switch (metrica) {
                case 'ventas':
                    datasetLabel = 'Ventas Totales';
                    valores = datos.map(item => parseFloat(item.total_ventas));
                    break;
                case 'cantidad':
                    datasetLabel = 'Cantidad de Ventas';
                    valores = datos.map(item => parseInt(item.cantidad_ventas));
                    break;
                case 'ticket_promedio':
                    datasetLabel = 'Ticket Promedio';
                    valores = datos.map(item => {
                        const cantidad = parseInt(item.cantidad_ventas);
                        return cantidad > 0 ? parseFloat(item.total_ventas) / cantidad : 0;
                    });
                    break;
                case 'productos':
                    datasetLabel = 'Productos Vendidos';
                    valores = datos.map(item => parseInt(item.total_productos));
                    break;
            }

            const ctxPrincipal = document.getElementById('graficoPrincipal');
            graficoPrincipal = new Chart(ctxPrincipal, {
                type: tipoReporte === 'comparativo' ? 'bar' : 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: datasetLabel,
                        data: valores,
                        backgroundColor: tipoReporte === 'comparativo' ? 'rgba(52, 152, 219, 0.7)' : 'rgba(52, 152, 219, 0.1)',
                        borderColor: '#3498db',
                        borderWidth: tipoReporte === 'comparativo' ? 1 : 3,
                        tension: 0.4,
                        fill: tipoReporte !== 'comparativo'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolución de ' + datasetLabel
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (metrica === 'ventas' || metrica === 'ticket_promedio') {
                                        return `${context.dataset.label}: $${context.parsed.y.toLocaleString('es-MX')}`;
                                    } else {
                                        return `${context.dataset.label}: ${context.parsed.y.toLocaleString('es-MX')}`;
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
                                    if (metrica === 'ventas' || metrica === 'ticket_promedio') {
                                        return '$' + value.toLocaleString('es-MX');
                                    } else {
                                        return value.toLocaleString('es-MX');
                                    }
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de pastel - Distribución por tipo de venta
            const ventasContado = parseFloat(resumen.ventas_contado || 0);
            const ventasCredito = parseFloat(resumen.ventas_credito || 0);

            if (ventasContado + ventasCredito > 0) {
                const ctxTipoVenta = document.getElementById('graficoTipoVenta');
                graficoTipoVenta = new Chart(ctxTipoVenta, {
                    type: 'pie',
                    data: {
                        labels: ['Contado', 'Crédito'],
                        datasets: [{
                            data: [ventasContado, ventasCredito],
                            backgroundColor: ['#27ae60', '#f39c12'],
                            borderColor: ['#219653', '#d68910'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = ventasContado + ventasCredito;
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return `${context.label}: $${context.parsed.toLocaleString('es-MX')} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de barras - Top meses
            const topMeses = [...datos]
                .sort((a, b) => parseFloat(b.total_ventas) - parseFloat(a.total_ventas))
                .slice(0, 5);

            if (topMeses.length > 0) {
                const labelsTop = topMeses.map(item => item.periodo);
                const valoresTop = topMeses.map(item => parseFloat(item.total_ventas));

                const ctxTopMeses = document.getElementById('graficoTopMeses');
                graficoTopMeses = new Chart(ctxTopMeses, {
                    type: 'bar',
                    data: {
                        labels: labelsTop,
                        datasets: [{
                            label: 'Ventas',
                            data: valoresTop,
                            backgroundColor: '#9b59b6',
                            borderColor: '#8e44ad',
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
                                text: 'Top 5 Meses por Ventas'
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
        }

        function destruirGraficos() {
            if (graficoPrincipal !== null) {
                graficoPrincipal.destroy();
                graficoPrincipal = null;
            }
            if (graficoTipoVenta !== null) {
                graficoTipoVenta.destroy();
                graficoTipoVenta = null;
            }
            if (graficoTopMeses !== null) {
                graficoTopMeses.destroy();
                graficoTopMeses = null;
            }
            $('#graficoContainerPrincipal, #graficoContainerTipoVenta, #graficoContainerTopMeses').hide();
            $('#graficoMensajePrincipal, #graficoMensajeTipoVenta, #graficoMensajeTopMeses').show();
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
            const tipoReporte = $('#tipo_reporte').val();
            const anio = $('#anio').val();
            const anioComparativo = $('#anio_comparativo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const metrica = $('#metrica').val();

            const url = `ajax/reportes/exportar-pdf-ventas-anuales.php?tipo_reporte=${tipoReporte}&anio=${anio}&anio_comparativo=${anioComparativo}&tipo_venta=${tipoVenta}&id_vendedor=${idVendedor}&id_almacen=${idAlmacen}&metrica=${metrica}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const tipoReporte = $('#tipo_reporte').val();
            const anio = $('#anio').val();
            const anioComparativo = $('#anio_comparativo').val();
            const tipoVenta = $('#tipo_venta').val();
            const idVendedor = $('#id_vendedor').val();
            const idAlmacen = $('#id_almacen').val();
            const metrica = $('#metrica').val();

            const url = `ajax/reportes/exportar-excel-ventas-anuales.php?tipo_reporte=${tipoReporte}&anio=${anio}&anio_comparativo=${anioComparativo}&tipo_venta=${tipoVenta}&id_vendedor=${idVendedor}&id_almacen=${idAlmacen}&metrica=${metrica}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    #graficoPrincipal,
    #graficoTipoVenta,
    #graficoTopMeses {
        max-height: 400px;
    }

    .card.bg-light {
        border: 1px solid #dee2e6;
    }

    .fa-arrow-up,
    .fa-arrow-down,
    .fa-minus {
        font-size: 1.2em;
    }
</style>