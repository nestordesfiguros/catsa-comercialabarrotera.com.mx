<?php
// contenido/reportes-detallado-facturas.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detallado de Facturas</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-file-invoice me-2"></i>Detallado de Facturas</h3>
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
                                <label for="estatus_factura" class="form-label">Estatus Factura</label>
                                <select class="form-select" id="estatus_factura" name="estatus_factura">
                                    <option value="">Todos</option>
                                    <option value="PENDIEENTE">Pendiente</option>
                                    <option value="PAGADA">Pagada</option>
                                    <option value="CANCELADA">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="timbrada" class="form-label">Timbrado</label>
                                <select class="form-select" id="timbrada" name="timbrada">
                                    <option value="">Todos</option>
                                    <option value="1">Timbradas</option>
                                    <option value="0">No Timbradas</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
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
                                <label for="serie" class="form-label">Serie</label>
                                <input type="text" class="form-control" id="serie" name="serie" placeholder="Ej: A">
                            </div>
                            <div class="col-md-4">
                                <label for="folio" class="form-label">Folio</label>
                                <input type="text" class="form-control" id="folio" name="folio" placeholder="Ej: 100">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" id="btnGenerarReporte" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Generar Reporte
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen de Facturas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Facturado</h6>
                                                        <h3 class="text-primary" id="resumenTotalFacturado">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Cantidad Facturas</h6>
                                                        <h3 class="text-success" id="resumenCantidadFacturas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Promedio por Factura</h6>
                                                        <h3 class="text-warning" id="resumenPromedioFactura">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Facturas Timbradas</h6>
                                                        <h3 class="text-info" id="resumenFacturasTimbradas">0</h3>
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
                                    <table id="tablaDetalleFacturas" class="table table-striped table-bordered" style="width:100%">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Folio</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>RFC</th>
                                                <th>Subtotal</th>
                                                <th>IVA</th>
                                                <th>Total</th>
                                                <th>Estatus</th>
                                                <th>Timbrado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan via AJAX -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4"><strong>TOTALES</strong></th>
                                                <th id="totalSubtotal">$0.00</th>
                                                <th id="totalIva">$0.00</th>
                                                <th id="totalGeneral">$0.00</th>
                                                <th colspan="3">-</th>
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

<script>
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
            $('#estatus_factura').val('');
            $('#timbrada').val('');
            $('#id_cliente').val('');
            $('#serie').val('');
            $('#folio').val('');
            $('#tablaDetalleFacturas tbody').empty();
            resetTotales();
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

        function generarReporte() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const estatusFactura = $('#estatus_factura').val();
            const timbrada = $('#timbrada').val();
            const idCliente = $('#id_cliente').val();
            const serie = $('#serie').val();
            const folio = $('#folio').val();

            if (!fechaInicio || !fechaFin) {
                alertify.error('Por favor seleccione un rango de fechas');
                return;
            }

            if (new Date(fechaInicio) > new Date(fechaFin)) {
                alertify.error('La fecha de inicio no puede ser mayor a la fecha fin');
                return;
            }

            $.ajax({
                url: 'ajax/reportes/detallado-facturas.php',
                type: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    estatus_factura: estatusFactura,
                    timbrada: timbrada,
                    id_cliente: idCliente,
                    serie: serie,
                    folio: folio,
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
                        $('#btnExportarPDF').prop('disabled', false);
                        $('#btnExportarExcel').prop('disabled', false);
                        alertify.success('Reporte generado correctamente');
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        resetResumen();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alertify.error('Error de conexión al generar el reporte');
                    resetResumen();
                },
                complete: function() {
                    $('#btnGenerarReporte').prop('disabled', false).html('<i class="fas fa-search me-2"></i>Generar Reporte');
                }
            });
        }

        function mostrarResumen(resumen) {
            $('#resumenTotalFacturado').text('$' + parseFloat(resumen.total_facturado).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#resumenCantidadFacturas').text(parseInt(resumen.cantidad_facturas).toLocaleString('es-MX'));
            $('#resumenPromedioFactura').text('$' + parseFloat(resumen.promedio_factura).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#resumenFacturasTimbradas').text(parseInt(resumen.facturas_timbradas).toLocaleString('es-MX'));
        }

        function resetResumen() {
            $('#resumenTotalFacturado').text('$0.00');
            $('#resumenCantidadFacturas').text('0');
            $('#resumenPromedioFactura').text('$0.00');
            $('#resumenFacturasTimbradas').text('0');
        }

        function mostrarDatos(datos) {
            const tbody = $('#tablaDetalleFacturas tbody');
            tbody.empty();

            let totalSubtotal = 0;
            let totalIva = 0;
            let totalGeneral = 0;

            if (datos.length > 0) {
                datos.forEach(function(item) {
                    const iva = parseFloat(item.total) - parseFloat(item.subtotal);
                    totalSubtotal += parseFloat(item.subtotal);
                    totalIva += iva;
                    totalGeneral += parseFloat(item.total);

                    const folioCompleto = item.serie && item.folio ? `${item.serie}-${item.folio}` : 'N/A';
                    const estatusBadge = getEstatusBadge(item.estatus);
                    const timbradoBadge = item.timbrada ?
                        '<span class="badge bg-success">Timbrada</span>' :
                        '<span class="badge bg-warning text-dark">No Timbrada</span>';

                    const fechaFormateada = item.fecha ? new Date(item.fecha).toLocaleDateString('es-MX') : 'N/A';

                    const fila = `
                    <tr>
                        <td><strong>${escapeHtml(folioCompleto)}</strong></td>
                        <td>${fechaFormateada}</td>
                        <td>${escapeHtml(item.razon_social || 'N/A')}</td>
                        <td>${escapeHtml(item.rfc || 'N/A')}</td>
                        <td class="text-end">$${parseFloat(item.subtotal).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${iva.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${estatusBadge}</td>
                        <td class="text-center">${timbradoBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalleFactura(${item.id})" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${item.timbrada && item.uuid ? `
                            <button class="btn btn-sm btn-outline-info" onclick="descargarXML(${item.id})" title="Descargar XML">
                                <i class="fas fa-download"></i>
                            </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
                    tbody.append(fila);
                });

                $('#totalSubtotal').text('$' + totalSubtotal.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalIva').text('$' + totalIva.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalGeneral').text('$' + totalGeneral.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                tbody.append('<tr><td colspan="10" class="text-center">No se encontraron facturas con los filtros seleccionados</td></tr>');
                resetTotales();
            }
        }

        function getEstatusBadge(estatus) {
            switch (estatus) {
                case 'PAGADA':
                    return '<span class="badge bg-success">Pagada</span>';
                case 'PENDIEENTE':
                    return '<span class="badge bg-warning text-dark">Pendiente</span>';
                case 'CANCELADA':
                    return '<span class="badge bg-danger">Cancelada</span>';
                default:
                    return '<span class="badge bg-secondary">' + estatus + '</span>';
            }
        }

        function resetTotales() {
            $('#totalSubtotal').text('$0.00');
            $('#totalIva').text('$0.00');
            $('#totalGeneral').text('$0.00');
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
            const estatusFactura = $('#estatus_factura').val();
            const timbrada = $('#timbrada').val();
            const idCliente = $('#id_cliente').val();
            const serie = $('#serie').val();
            const folio = $('#folio').val();

            const url = `ajax/reportes/exportar-pdf-detalle-facturas.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estatus_factura=${estatusFactura}&timbrada=${timbrada}&id_cliente=${idCliente}&serie=${serie}&folio=${folio}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const estatusFactura = $('#estatus_factura').val();
            const timbrada = $('#timbrada').val();
            const idCliente = $('#id_cliente').val();
            const serie = $('#serie').val();
            const folio = $('#folio').val();

            const url = `ajax/reportes/exportar-excel-detalle-facturas.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estatus_factura=${estatusFactura}&timbrada=${timbrada}&id_cliente=${idCliente}&serie=${serie}&folio=${folio}`;
            window.open(url, '_blank');
        }
    });

    // Funciones globales para acciones
    function verDetalleFactura(idFactura) {
        window.open(`facturas-detalle/${idFactura}`, '_blank');
    }

    function descargarXML(idFactura) {
        window.open(`facturas-descargar-xml/${idFactura}`, '_blank');
    }
</script>

<style>
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