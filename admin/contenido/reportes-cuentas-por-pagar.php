<?php
// contenido/reportes-cuentas-por-pagar.php
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cuentas por Pagar</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-file-invoice-dollar me-2"></i>Reporte de Cuentas por Pagar</h3>
                    </div>
                    <div class="card-body">
                        <form id="formReporteCuentasPagar">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_corte">Fecha de Corte:</label>
                                        <input type="date" class="form-control" id="fecha_corte" name="fecha_corte" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_proveedor">Proveedor:</label>
                                        <select class="form-control select2" id="id_proveedor" name="id_proveedor">
                                            <option value="0">Todos los Proveedores</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estatus">Estatus:</label>
                                        <select class="form-control" id="estatus" name="estatus">
                                            <option value="">Todos</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="parcial">Parcial</option>
                                            <option value="pagada">Pagada</option>
                                            <option value="vencida">Vencida</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="dias_vencimiento">Días de Vencimiento:</label>
                                        <select class="form-control" id="dias_vencimiento" name="dias_vencimiento">
                                            <option value="">Todos</option>
                                            <option value="1-30">1-30 días</option>
                                            <option value="31-60">31-60 días</option>
                                            <option value="61-90">61-90 días</option>
                                            <option value="91+">Más de 90 días</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="monto_minimo">Monto Mínimo:</label>
                                        <input type="number" class="form-control" id="monto_minimo" name="monto_minimo" placeholder="0.00" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="monto_maximo">Monto Máximo:</label>
                                        <input type="number" class="form-control" id="monto_maximo" name="monto_maximo" placeholder="Sin límite" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="orden">Ordenar por:</label>
                                        <select class="form-control" id="orden" name="orden">
                                            <option value="fecha_asc">Fecha (Más Antigua)</option>
                                            <option value="fecha_desc">Fecha (Más Reciente)</option>
                                            <option value="monto_asc">Monto (Menor a Mayor)</option>
                                            <option value="monto_desc">Monto (Mayor a Menor)</option>
                                            <option value="proveedor">Proveedor</option>
                                            <option value="dias_vencimiento">Días de Vencimiento</option>
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
                                        <h5 class="card-title">Resumen de Cuentas por Pagar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6">
                                                <div class="info-box bg-gradient-info">
                                                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Pendiente</span>
                                                        <span class="info-box-number" id="totalPendiente">$0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="info-box bg-gradient-warning">
                                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Vencido</span>
                                                        <span class="info-box-number" id="totalVencido">$0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="info-box bg-gradient-success">
                                                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Pagado</span>
                                                        <span class="info-box-number" id="totalPagado">$0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <div class="info-box bg-gradient-secondary">
                                                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Proveedores</span>
                                                        <span class="info-box-number" id="totalProveedores">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tablaCuentasPagar" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr class="bg-success text-white">
                                                <th>Proveedor</th>
                                                <th>Fecha</th>
                                                <th>Compra #</th>
                                                <th>Monto Total</th>
                                                <th>Monto Pagado</th>
                                                <th>Saldo Pendiente</th>
                                                <th>Días Vencimiento</th>
                                                <th>Estatus</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargarán via AJAX -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th colspan="3">TOTALES</th>
                                                <th id="totalMonto">$0.00</th>
                                                <th id="totalPagadoTabla">$0.00</th>
                                                <th id="totalSaldo">$0.00</th>
                                                <th colspan="3"></th>
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
        // Inicializar Select2 para proveedores
        $('#id_proveedor').select2({
            placeholder: 'Seleccione un proveedor',
            allowClear: true,
            ajax: {
                url: 'ajax/reportes/buscar-proveedores.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Establecer fecha de corte por defecto (hoy)
        const today = new Date();
        $('#fecha_corte').val(today.toISOString().split('T')[0]);

        // Generar reporte
        $('#formReporteCuentasPagar').on('submit', function(e) {
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
            formData.append('fecha_corte', $('#fecha_corte').val());
            formData.append('id_proveedor', $('#id_proveedor').val());
            formData.append('estatus', $('#estatus').val());
            formData.append('dias_vencimiento', $('#dias_vencimiento').val());
            formData.append('monto_minimo', $('#monto_minimo').val());
            formData.append('monto_maximo', $('#monto_maximo').val());
            formData.append('orden', $('#orden').val());

            $.ajax({
                url: 'ajax/reportes/cuentas-por-pagar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#tablaCuentasPagar tbody').html('<tr><td colspan="9" class="text-center"><div class="spinner-border text-success" role="status"></div> Cargando...</td></tr>');
                    // Resetear resumen
                    $('#totalPendiente, #totalVencido, #totalPagado').text('$0.00');
                    $('#totalProveedores').text('0');
                },
                success: function(response) {
                    if (response.success) {
                        mostrarDatos(response.data);
                        actualizarResumen(response.resumen);
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', false);
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        $('#tablaCuentasPagar tbody').html('<tr><td colspan="9" class="text-center text-muted">No se encontraron datos</td></tr>');
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                    }
                },
                error: function() {
                    alertify.error('Error de conexión');
                    $('#tablaCuentasPagar tbody').html('<tr><td colspan="9" class="text-center text-muted">Error al cargar datos</td></tr>');
                    $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                }
            });
        }

        function mostrarDatos(datos) {
            let html = '';
            let totalMonto = 0;
            let totalPagado = 0;
            let totalSaldo = 0;

            if (datos && datos.length > 0) {
                datos.forEach(function(item) {
                    const saldoPendiente = parseFloat(item.monto_total) - parseFloat(item.monto_pagado);
                    const fechaCompra = new Date(item.fecha);
                    const fechaCorte = new Date($('#fecha_corte').val());
                    const diasTranscurridos = Math.floor((fechaCorte - fechaCompra) / (1000 * 60 * 60 * 24));

                    let estatusClass = '';
                    let estatusText = '';

                    switch (item.estatus) {
                        case 'pendiente':
                            estatusClass = 'warning';
                            estatusText = 'Pendiente';
                            break;
                        case 'parcial':
                            estatusClass = 'info';
                            estatusText = 'Parcial';
                            break;
                        case 'pagada':
                            estatusClass = 'success';
                            estatusText = 'Pagada';
                            break;
                        case 'vencida':
                            estatusClass = 'danger';
                            estatusText = 'Vencida';
                            break;
                        default:
                            estatusClass = 'secondary';
                            estatusText = item.estatus;
                    }

                    let diasClass = '';
                    if (diasTranscurridos > 90) {
                        diasClass = 'danger';
                    } else if (diasTranscurridos > 60) {
                        diasClass = 'warning';
                    } else if (diasTranscurridos > 30) {
                        diasClass = 'info';
                    } else {
                        diasClass = 'success';
                    }

                    totalMonto += parseFloat(item.monto_total);
                    totalPagado += parseFloat(item.monto_pagado);
                    totalSaldo += saldoPendiente;

                    html += `
                    <tr>
                        <td>${escapeHtml(item.nombre_proveedor)}</td>
                        <td class="text-center">${fechaCompra.toLocaleDateString('es-MX')}</td>
                        <td class="text-center">${item.id_compra}</td>
                        <td class="text-end">$${parseFloat(item.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.monto_pagado).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${saldoPendiente.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center"><span class="badge bg-${diasClass}">${diasTranscurridos} días</span></td>
                        <td class="text-center"><span class="badge bg-${estatusClass}">${estatusText}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalleCompra(${item.id_compra})" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                });

                $('#totalMonto').text('$' + totalMonto.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalPagadoTabla').text('$' + totalPagado.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalSaldo').text('$' + totalSaldo.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                html = '<tr><td colspan="9" class="text-center text-muted">No se encontraron cuentas por pagar con los filtros seleccionados</td></tr>';
                $('#totalMonto, #totalPagadoTabla, #totalSaldo').text('$0.00');
            }

            $('#tablaCuentasPagar tbody').html(html);
        }

        function actualizarResumen(resumen) {
            if (resumen) {
                $('#totalPendiente').text('$' + parseFloat(resumen.total_pendiente || 0).toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalVencido').text('$' + parseFloat(resumen.total_vencido || 0).toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalPagado').text('$' + parseFloat(resumen.total_pagado || 0).toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalProveedores').text(resumen.total_proveedores || 0);
            }
        }

        function exportarPDF() {
            const params = new URLSearchParams({
                fecha_corte: $('#fecha_corte').val(),
                id_proveedor: $('#id_proveedor').val(),
                estatus: $('#estatus').val(),
                dias_vencimiento: $('#dias_vencimiento').val(),
                monto_minimo: $('#monto_minimo').val(),
                monto_maximo: $('#monto_maximo').val(),
                orden: $('#orden').val()
            });

            window.open('ajax/reportes/exportar-pdf-cuentas-pagar.php?' + params.toString(), '_blank');
        }

        function exportarExcel() {
            const params = new URLSearchParams({
                fecha_corte: $('#fecha_corte').val(),
                id_proveedor: $('#id_proveedor').val(),
                estatus: $('#estatus').val(),
                dias_vencimiento: $('#dias_vencimiento').val(),
                monto_minimo: $('#monto_minimo').val(),
                monto_maximo: $('#monto_maximo').val(),
                orden: $('#orden').val()
            });

            window.open('ajax/reportes/exportar-excel-cuentas-pagar.php?' + params.toString(), '_blank');
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

    // Función global para ver detalle de compra
    function verDetalleCompra(idCompra) {
        alertify.alert('Detalle de Compra', `Mostrando detalles de la compra #${idCompra}. Esta funcionalidad se implementará posteriormente.`);
    }
</script>

<style>
    .info-box {
        cursor: default;
    }

    .info-box .info-box-icon {
        border-radius: 5px;
    }
</style>