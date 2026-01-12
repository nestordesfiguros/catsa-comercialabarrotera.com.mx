<?php
// contenido/reportes-ventas-por-cliente.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas por Cliente</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-users me-2"></i>Reporte de Ventas por Cliente</h3>
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
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="id_cliente" name="id_cliente">
                                    <option value="">Todos los clientes</option>
                                    <?php
                                    $sqlClientes = "SELECT id, no_cliente, razon_social, nombre_comercial 
                                                   FROM cat_clientes 
                                                   WHERE estatus = 1 
                                                   ORDER BY razon_social";
                                    $clientes = $clsConsulta->consultaGeneral($sqlClientes);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($clientes as $i => $cli) {
                                            $nombreCliente = $cli['nombre_comercial'] ?: $cli['razon_social'];
                                            echo "<option value=\"{$cli['id']}\">{$cli['no_cliente']} - {$nombreCliente}</option>";
                                        }
                                    }
                                    ?>
                                </select>
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
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="tipo_venta" class="form-label">Tipo de Venta</label>
                                <select class="form-select" id="tipo_venta" name="tipo_venta">
                                    <option value="">Todos</option>
                                    <option value="contado">Contado</option>
                                    <option value="credito">Crédito</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="orden" class="form-label">Ordenar por</label>
                                <select class="form-select" id="orden" name="orden">
                                    <option value="total_ventas">Total Ventas</option>
                                    <option value="cantidad_compras">Cantidad de Compras</option>
                                    <option value="nombre_cliente">Nombre Cliente</option>
                                    <option value="ticket_promedio">Ticket Promedio</option>
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
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" id="btnGenerarReporte" class="btn btn-info">
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
                                    <table id="tablaVentasCliente" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Vendedor</th>
                                                <th>Tipo Venta</th>
                                                <th>Cantidad Compras</th>
                                                <th>Ticket Promedio</th>
                                                <th>Compra Mínima</th>
                                                <th>Compra Máxima</th>
                                                <th>Total Ventas</th>
                                                <th>Última Compra</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3"><strong>TOTALES</strong></th>
                                                <th id="totalCompras">0</th>
                                                <th id="ticketPromedioGeneral">$0.00</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th id="totalVentas">$0.00</th>
                                                <th>-</th>
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
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Top Clientes por Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerVentas">
                                            <canvas id="graficoVentasCliente" height="250"></canvas>
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Distribución por Tipo de Venta</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerTipoVenta">
                                            <canvas id="graficoTipoVenta" height="250"></canvas>
                                        </div>
                                        <div id="graficoMensajeTipoVenta" class="text-center text-muted" style="display: none;">
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
    let ventasClienteChart = null;
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
            $('#id_cliente').val('');
            $('#id_vendedor').val('');
            $('#tipo_venta').val('');
            $('#orden').val('total_ventas');
            $('#direccion').val('DESC');
            $('#limite').val('0');
            $('#tablaVentasCliente tbody').empty();
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
            const idCliente = $('#id_cliente').val();
            const idVendedor = $('#id_vendedor').val();
            const tipoVenta = $('#tipo_venta').val();
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
                url: 'ajax/reportes/ventas-por-cliente.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    id_cliente: idCliente,
                    id_vendedor: idVendedor,
                    tipo_venta: tipoVenta,
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
            const tbody = $('#tablaVentasCliente tbody');
            tbody.empty();

            let totalVentas = 0;
            let totalCompras = 0;
            let sumaTickets = 0;

            if (datos.length > 0) {
                datos.forEach(function(item) {
                    const ticketPromedio = item.cantidad_compras > 0 ? item.total_ventas / item.cantidad_compras : 0;

                    totalVentas += parseFloat(item.total_ventas);
                    totalCompras += parseInt(item.cantidad_compras);
                    sumaTickets += ticketPromedio;

                    const fila = `
                    <tr>
                        <td>
                            <strong>${escapeHtml(item.no_cliente)}</strong><br>
                            <small class="text-muted">${escapeHtml(item.nombre_cliente)}</small>
                        </td>
                        <td>${escapeHtml(item.nombre_vendedor || 'N/A')}</td>
                        <td>
                            <span class="badge ${item.tipo_venta === 'credito' ? 'bg-warning' : 'bg-success'}">
                                ${item.tipo_venta === 'credito' ? 'Crédito' : 'Contado'}
                            </span>
                        </td>
                        <td class="text-center">${item.cantidad_compras}</td>
                        <td class="text-end">$${ticketPromedio.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_minima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_maxima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end"><strong>$${parseFloat(item.total_ventas).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                        <td class="text-center">${item.ultima_compra ? new Date(item.ultima_compra).toLocaleDateString('es-MX') : 'N/A'}</td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                const ticketPromedioGeneral = totalCompras > 0 ? totalVentas / totalCompras : 0;

                $('#totalCompras').text(totalCompras.toLocaleString('es-MX'));
                $('#ticketPromedioGeneral').text('$' + ticketPromedioGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalVentas').text('$' + totalVentas.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                tbody.append('<tr><td colspan="9" class="text-center">No se encontraron datos con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function resetTotales() {
            $('#totalCompras').text('0');
            $('#ticketPromedioGeneral').text('$0.00');
            $('#totalVentas').text('$0.00');
        }

        function generarGraficos(datos) {
            destruirGraficos();

            if (datos.length === 0) {
                $('#graficoContainerVentas, #graficoContainerTipoVenta').hide();
                $('#graficoMensajeVentas, #graficoMensajeTipoVenta').show();
                return;
            }

            $('#graficoContainerVentas, #graficoContainerTipoVenta').show();
            $('#graficoMensajeVentas, #graficoMensajeTipoVenta').hide();

            // Gráfico de barras - Top clientes por ventas (máximo 10)
            const topClientes = datos.slice(0, 10);
            const labelsVentas = topClientes.map(item => item.no_cliente);
            const valoresVentas = topClientes.map(item => parseFloat(item.total_ventas));

            const ctxVentas = document.getElementById('graficoVentasCliente');
            ventasClienteChart = new Chart(ctxVentas, {
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
                            text: 'Top 10 Clientes por Ventas'
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

            // Gráfico de pie - Distribución por tipo de venta
            const tiposVentaMap = {
                'contado': 0,
                'credito': 0
            };

            datos.forEach(item => {
                const tipo = item.tipo_venta || 'contado';
                tiposVentaMap[tipo] += parseFloat(item.total_ventas);
            });

            const labelsTipoVenta = ['Contado', 'Crédito'];
            const valoresTipoVenta = [tiposVentaMap['contado'], tiposVentaMap['credito']];

            const ctxTipoVenta = document.getElementById('graficoTipoVenta');
            tipoVentaChart = new Chart(ctxTipoVenta, {
                type: 'pie',
                data: {
                    labels: labelsTipoVenta,
                    datasets: [{
                        data: valoresTipoVenta,
                        backgroundColor: ['#27ae60', '#f39c12'],
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
                            text: 'Distribución por Tipo de Venta'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = valoresTipoVenta.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: $${value.toLocaleString('es-MX')} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function destruirGraficos() {
            if (ventasClienteChart !== null) {
                ventasClienteChart.destroy();
                ventasClienteChart = null;
            }
            if (tipoVentaChart !== null) {
                tipoVentaChart.destroy();
                tipoVentaChart = null;
            }
            $('#graficoContainerVentas, #graficoContainerTipoVenta').hide();
            $('#graficoMensajeVentas, #graficoMensajeTipoVenta').show();
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
            const idCliente = $('#id_cliente').val();
            const idVendedor = $('#id_vendedor').val();
            const tipoVenta = $('#tipo_venta').val();
            const orden = $('#orden').val();
            const direccion = $('#direccion').val();
            const limite = $('#limite').val();

            const url = `ajax/reportes/exportar-pdf-ventas-cliente.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_cliente=${idCliente}&id_vendedor=${idVendedor}&tipo_venta=${tipoVenta}&orden=${orden}&direccion=${direccion}&limite=${limite}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idCliente = $('#id_cliente').val();
            const idVendedor = $('#id_vendedor').val();
            const tipoVenta = $('#tipo_venta').val();
            const orden = $('#orden').val();
            const direccion = $('#direccion').val();
            const limite = $('#limite').val();

            const url = `ajax/reportes/exportar-excel-ventas-cliente.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_cliente=${idCliente}&id_vendedor=${idVendedor}&tipo_venta=${tipoVenta}&orden=${orden}&direccion=${direccion}&limite=${limite}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    #graficoVentasCliente,
    #graficoTipoVenta {
        max-height: 250px;
    }

    .badge {
        font-size: 0.75em;
    }
</style>