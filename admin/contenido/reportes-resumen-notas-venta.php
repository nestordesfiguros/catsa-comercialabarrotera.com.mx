<?php
// contenido/reportes-resumen-notas-venta.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Resumen de Notas de Venta</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-clipboard-list me-2"></i>Resumen de Notas de Venta</h3>
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
                                <label for="tipo_venta" class="form-label">Tipo de Venta</label>
                                <select class="form-select" id="tipo_venta" name="tipo_venta">
                                    <option value="">Todos</option>
                                    <option value="contado">Contado</option>
                                    <option value="credito">Crédito</option>
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
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="id_cliente" name="id_cliente">
                                    <option value="">Todos los clientes</option>
                                    <?php
                                    $sqlClientes = "SELECT id, razon_social, nombre_comercial 
                                                   FROM cat_clientes 
                                                   WHERE estatus = 1 
                                                   ORDER BY razon_social";
                                    $clientes = $clsConsulta->consultaGeneral($sqlClientes);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($clientes as $i => $cli) {
                                            $nombre = $cli['nombre_comercial'] ?: $cli['razon_social'];
                                            echo "<option value=\"{$cli['id']}\">{$nombre}</option>";
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen General</h5>
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
                                                        <h6 class="card-title">Cantidad Notas</h6>
                                                        <h3 class="text-success" id="resumenCantidadNotas">0</h3>
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
                                                        <h6 class="card-title">Productos Vendidos</h6>
                                                        <h3 class="text-info" id="resumenProductosVendidos">0</h3>
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
                                    <table id="tablaResumenNotas" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Folio</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Vendedor</th>
                                                <th>Almacén</th>
                                                <th>Tipo Venta</th>
                                                <th>Productos</th>
                                                <th>Subtotal</th>
                                                <th>Total</th>
                                                <th>Estatus</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="6"><strong>TOTALES</strong></th>
                                                <th id="totalProductos">0</th>
                                                <th id="totalSubtotal">$0.00</th>
                                                <th id="totalGeneral">$0.00</th>
                                                <th colspan="2">-</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Distribución por Tipo de Venta -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
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
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Top 5 Vendedores</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="graficoContainerTopVendedores">
                                            <canvas id="graficoTopVendedores" height="250"></canvas>
                                        </div>
                                        <div id="graficoMensajeTopVendedores" class="text-center text-muted" style="display: none;">
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
    let tipoVentaChart = null;
    let topVendedoresChart = null;

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
            $('#tipo_venta').val('');
            $('#estatus').val('');
            $('#id_vendedor').val('');
            $('#id_cliente').val('');
            $('#id_almacen').val('');
            $('#tablaResumenNotas tbody').empty();
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
            const tipoVenta = $('#tipo_venta').val();
            const estatus = $('#estatus').val();
            const idVendedor = $('#id_vendedor').val();
            const idCliente = $('#id_cliente').val();
            const idAlmacen = $('#id_almacen').val();

            if (!fechaInicio || !fechaFin) {
                alertify.error('Por favor seleccione un rango de fechas');
                return;
            }

            if (new Date(fechaInicio) > new Date(fechaFin)) {
                alertify.error('La fecha de inicio no puede ser mayor a la fecha fin');
                return;
            }

            $.ajax({
                url: 'ajax/reportes/resumen-notas-venta.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    tipo_venta: tipoVenta,
                    estatus: estatus,
                    id_vendedor: idVendedor,
                    id_cliente: idCliente,
                    id_almacen: idAlmacen,
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
                            generarGraficos(response.resumen, response.top_vendedores);
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
            $('#resumenCantidadNotas').text(parseInt(resumen.cantidad_notas).toLocaleString('es-MX'));
            $('#resumenTicketPromedio').text('$' + parseFloat(resumen.ticket_promedio).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#resumenProductosVendidos').text(parseInt(resumen.total_productos).toLocaleString('es-MX'));
        }

        function resetResumen() {
            $('#resumenTotalVentas').text('$0.00');
            $('#resumenCantidadNotas').text('0');
            $('#resumenTicketPromedio').text('$0.00');
            $('#resumenProductosVendidos').text('0');
        }

        // En la función mostrarDatos, reemplazar esta parte:
        function mostrarDatos(datos) {
            const tbody = $('#tablaResumenNotas tbody');
            tbody.empty();

            let totalGeneral = 0;
            let totalProductos = 0;

            if (datos.length > 0) {
                datos.forEach(function(item) {
                    totalGeneral += parseFloat(item.total);
                    totalProductos += parseInt(item.total_productos);

                    const fechaFormateada = item.fecha ? new Date(item.fecha).toLocaleDateString('es-MX') : 'N/A';
                    const estatusBadge = getEstatusBadge(item.estatus);
                    const tipoVentaBadge = item.tipo_venta === 'credito' ?
                        '<span class="badge bg-warning text-dark">Crédito</span>' :
                        '<span class="badge bg-success">Contado</span>';

                    // Calcular subtotal aproximado (80% del total como estimación del subtotal sin IVA)
                    const subtotalEstimado = parseFloat(item.total) / 1.16;

                    const fila = `
                <tr>
                    <td><strong>#${item.id}</strong></td>
                    <td>${fechaFormateada}</td>
                    <td>${escapeHtml(item.cliente || 'N/A')}</td>
                    <td>${escapeHtml(item.vendedor || 'N/A')}</td>
                    <td>${escapeHtml(item.almacen || 'N/A')}</td>
                    <td class="text-center">${tipoVentaBadge}</td>
                    <td class="text-center">${parseInt(item.total_productos).toLocaleString('es-MX')}</td>
                    <td class="text-end">$${subtotalEstimado.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end">$${parseFloat(item.total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-center">${estatusBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalleNota(${item.id})" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="imprimirNota(${item.id})" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                </tr>
            `;
                    tbody.append(fila);
                });

                const subtotalTotalEstimado = totalGeneral / 1.16;

                $('#totalProductos').text(totalProductos.toLocaleString('es-MX'));
                $('#totalSubtotal').text('$' + subtotalTotalEstimado.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalGeneral').text('$' + totalGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                tbody.append('<tr><td colspan="11" class="text-center">No se encontraron notas de venta con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function getEstatusBadge(estatus) {
            switch (estatus) {
                case 'procesada':
                    return '<span class="badge bg-success">Procesada</span>';
                case 'pendiente':
                    return '<span class="badge bg-warning text-dark">Pendiente</span>';
                case 'cancelada':
                    return '<span class="badge bg-danger">Cancelada</span>';
                default:
                    return '<span class="badge bg-secondary">' + estatus + '</span>';
            }
        }

        function resetTotales() {
            $('#totalProductos').text('0');
            $('#totalSubtotal').text('$0.00');
            $('#totalGeneral').text('$0.00');
        }

        function generarGraficos(resumen, topVendedores) {
            destruirGraficos();

            if (resumen.total_ventas == 0) {
                $('#graficoContainerTipoVenta, #graficoContainerTopVendedores').hide();
                $('#graficoMensajeTipoVenta, #graficoMensajeTopVendedores').show();
                return;
            }

            $('#graficoContainerTipoVenta, #graficoContainerTopVendedores').show();
            $('#graficoMensajeTipoVenta, #graficoMensajeTopVendedores').hide();

            // Gráfico de pastel - Distribución por tipo de venta
            const ventasContado = parseFloat(resumen.ventas_contado || 0);
            const ventasCredito = parseFloat(resumen.ventas_credito || 0);

            const ctxTipoVenta = document.getElementById('graficoTipoVenta');
            tipoVentaChart = new Chart(ctxTipoVenta, {
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
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
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

            // Gráfico de barras - Top vendedores
            if (topVendedores && topVendedores.length > 0) {
                const labelsVendedores = topVendedores.map(v => v.vendedor.substring(0, 15) + (v.vendedor.length > 15 ? '...' : ''));
                const valoresVendedores = topVendedores.map(v => parseFloat(v.total_ventas));

                const ctxTopVendedores = document.getElementById('graficoTopVendedores');
                topVendedoresChart = new Chart(ctxTopVendedores, {
                    type: 'bar',
                    data: {
                        labels: labelsVendedores,
                        datasets: [{
                            label: 'Ventas',
                            data: valoresVendedores,
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
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Ventas: $${context.parsed.y.toLocaleString('es-MX')}`;
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
        }

        function destruirGraficos() {
            if (tipoVentaChart !== null) {
                tipoVentaChart.destroy();
                tipoVentaChart = null;
            }
            if (topVendedoresChart !== null) {
                topVendedoresChart.destroy();
                topVendedoresChart = null;
            }
            $('#graficoContainerTipoVenta, #graficoContainerTopVendedores').hide();
            $('#graficoMensajeTipoVenta, #graficoMensajeTopVendedores').show();
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
            const tipoVenta = $('#tipo_venta').val();
            const estatus = $('#estatus').val();
            const idVendedor = $('#id_vendedor').val();
            const idCliente = $('#id_cliente').val();
            const idAlmacen = $('#id_almacen').val();

            const url = `ajax/reportes/exportar-pdf-resumen-notas.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_venta=${tipoVenta}&estatus=${estatus}&id_vendedor=${idVendedor}&id_cliente=${idCliente}&id_almacen=${idAlmacen}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const tipoVenta = $('#tipo_venta').val();
            const estatus = $('#estatus').val();
            const idVendedor = $('#id_vendedor').val();
            const idCliente = $('#id_cliente').val();
            const idAlmacen = $('#id_almacen').val();

            const url = `ajax/reportes/exportar-excel-resumen-notas.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_venta=${tipoVenta}&estatus=${estatus}&id_vendedor=${idVendedor}&id_cliente=${idCliente}&id_almacen=${idAlmacen}`;
            window.open(url, '_blank');
        }
    });
</script>

<style>
    #graficoTipoVenta,
    #graficoTopVendedores {
        max-height: 250px;
    }

    .badge {
        font-size: 0.75em;
    }

    .card.bg-light {
        border: 1px solid #dee2e6;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<script>
    // Funciones globales para acciones
    function verDetalleNota(idNota) {
        window.open(`remisiones-detalle/${idNota}`, '_blank');
    }

    function imprimirNota(idNota) {
        window.open(`remisiones-imprimir/${idNota}`, '_blank');
    }
</script>