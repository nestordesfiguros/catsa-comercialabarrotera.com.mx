<?php
// contenido/reportes-compras-por-proveedor.php
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compras por Proveedor</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck me-2"></i>Reporte de Compras por Proveedor</h3>
                    </div>
                    <div class="card-body">
                        <form id="formReporteComprasProveedor">
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
                                <div class="table-responsive">
                                    <table id="tablaComprasProveedor" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr class="bg-success text-white">
                                                <th>Proveedor</th>
                                                <th>Compras Totales</th>
                                                <th>Cantidad de Compras</th>
                                                <th>Promedio por Compra</th>
                                                <th>Compra Mínima</th>
                                                <th>Compra Máxima</th>
                                                <th>Última Compra</th>
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
                                                <th>-</th>
                                                <th>-</th>
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

        // Establecer fechas por defecto (mes actual)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        $('#fecha_inicio').val(firstDay.toISOString().split('T')[0]);
        $('#fecha_fin').val(lastDay.toISOString().split('T')[0]);

        // Generar reporte
        $('#formReporteComprasProveedor').on('submit', function(e) {
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
            formData.append('id_proveedor', $('#id_proveedor').val());
            formData.append('estatus', $('#estatus').val());

            $.ajax({
                url: 'ajax/reportes/compras-por-proveedor.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#tablaComprasProveedor tbody').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-success" role="status"></div> Cargando...</td></tr>');
                },
                success: function(response) {
                    if (response.success) {
                        mostrarDatos(response.data);
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', false);
                    } else {
                        alertify.error(response.message || 'Error al generar el reporte');
                        $('#tablaComprasProveedor tbody').html('<tr><td colspan="7" class="text-center text-muted">No se encontraron datos</td></tr>');
                        $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                    }
                },
                error: function() {
                    alertify.error('Error de conexión');
                    $('#tablaComprasProveedor tbody').html('<tr><td colspan="7" class="text-center text-muted">Error al cargar datos</td></tr>');
                    $('#btnExportarPDF, #btnExportarExcel').prop('disabled', true);
                }
            });
        }

        function mostrarDatos(datos) {
            let html = '';
            let totalCompras = 0;
            let totalCantidad = 0;

            if (datos && datos.length > 0) {
                datos.forEach(function(item) {
                    const promedio = item.cantidad_compras > 0 ? item.total_compras / item.cantidad_compras : 0;
                    const ultimaCompra = item.ultima_compra ? new Date(item.ultima_compra).toLocaleDateString() : 'N/A';

                    totalCompras += parseFloat(item.total_compras);
                    totalCantidad += parseInt(item.cantidad_compras);

                    html += `
                    <tr>
                        <td>${escapeHtml(item.nombre_proveedor)}</td>
                        <td class="text-end">$${parseFloat(item.total_compras).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${item.cantidad_compras}</td>
                        <td class="text-end">$${promedio.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_minima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">$${parseFloat(item.compra_maxima).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-center">${ultimaCompra}</td>
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
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted">No se encontraron compras con los filtros seleccionados</td></tr>';
                $('#totalCompras').text('$0.00');
                $('#totalCantidad').text('0');
                $('#promedioCompra').text('$0.00');
            }

            $('#tablaComprasProveedor tbody').html(html);
        }

        function exportarPDF() {
            const params = new URLSearchParams({
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                id_proveedor: $('#id_proveedor').val(),
                estatus: $('#estatus').val()
            });

            window.open('ajax/reportes/exportar-pdf-compras-proveedor.php?' + params.toString(), '_blank');
        }

        function exportarExcel() {
            const params = new URLSearchParams({
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                id_proveedor: $('#id_proveedor').val(),
                estatus: $('#estatus').val()
            });

            window.open('ajax/reportes/exportar-excel-compras-proveedor.php?' + params.toString(), '_blank');
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