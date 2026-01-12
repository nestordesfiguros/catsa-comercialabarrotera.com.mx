<?php
// contenido/reportes-inventario-fisico.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Inventario Físico</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title"><i class="fas fa-clipboard-check me-2"></i>Inventario Físico - Conteos y Ajustes</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="fecha_conteo" class="form-label">Fecha de Conteo</label>
                                <input type="date" class="form-control" id="fecha_conteo" name="fecha_conteo" value="<?php echo date('Y-m-d'); ?>">
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
                                <label for="id_producto" class="form-label">Producto</label>
                                <select class="form-select" id="id_producto" name="id_producto">
                                    <option value="">Todos los productos</option>
                                    <?php
                                    $sqlProductos = "SELECT id_producto, clave, nombre 
                                                   FROM cat_productos 
                                                   WHERE estatus = 1 
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
                                <label for="estatus_conteo" class="form-label">Estatus Conteo</label>
                                <select class="form-select" id="estatus_conteo" name="estatus_conteo">
                                    <option value="">Todos</option>
                                    <option value="aplicado">Aplicados</option>
                                    <option value="pendiente">Pendientes</option>
                                    <option value="con_diferencias">Con Diferencias</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="responsable" class="form-label">Responsable</label>
                                <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Nombre del responsable">
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                                <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                    <option value="detallado">Detallado</option>
                                    <option value="resumen">Resumen por Producto</option>
                                    <option value="diferencias">Solo Diferencias</option>
                                    <option value="ajustes">Historial de Ajustes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="producto">Producto</option>
                                    <option value="diferencia">Diferencia</option>
                                    <option value="fecha">Fecha</option>
                                    <option value="almacen">Almacén</option>
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
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Resumen de Conteo Físico</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Productos Contados</h6>
                                                        <h3 class="text-primary" id="resumenProductosContados">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Con Diferencias</h6>
                                                        <h3 class="text-warning" id="resumenConDiferencias">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Diferencias</h6>
                                                        <h3 class="text-danger" id="resumenTotalDiferencias">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Valor Diferencias</h6>
                                                        <h3 class="text-success" id="resumenValorDiferencias">$0.00</h3>
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
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle de Conteo Físico</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaInventarioFisico" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Producto</th>
                                                        <th>Almacén</th>
                                                        <th class="text-center">Existencia Sistema</th>
                                                        <th class="text-center">Conteo Físico</th>
                                                        <th class="text-center">Diferencia</th>
                                                        <th class="text-center">% Diferencia</th>
                                                        <th class="text-end">Costo Unitario</th>
                                                        <th class="text-end">Valor Diferencia</th>
                                                        <th>Responsable</th>
                                                        <th class="text-center">Estatus</th>
                                                        <th>Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="3"><strong>TOTALES</strong></th>
                                                        <th class="text-center" id="totalSistema">0</th>
                                                        <th class="text-center" id="totalFisico">0</th>
                                                        <th class="text-center" id="totalDiferencia">0</th>
                                                        <th>-</th>
                                                        <th>-</th>
                                                        <th class="text-end" id="totalValorDiferencia">$0.00</th>
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
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        let tablaInventarioFisico = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaInventarioFisico !== null) {
                tablaInventarioFisico.destroy();
            }

            tablaInventarioFisico = $('#tablaInventarioFisico').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-inventario-fisico.php',
                    type: 'POST',
                    data: function(d) {
                        d.fecha_conteo = $('#fecha_conteo').val();
                        d.id_almacen = $('#id_almacen').val();
                        d.id_producto = $('#id_producto').val();
                        d.estatus_conteo = $('#estatus_conteo').val();
                        d.responsable = $('#responsable').val();
                        d.tipo_reporte = $('#tipo_reporte').val();
                        d.ordenar_por = $('#ordenar_por').val();
                    }
                },
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                    sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
                },
                columns: [{
                        data: 'fecha',
                        className: 'text-center'
                    },
                    {
                        data: 'producto'
                    },
                    {
                        data: 'almacen'
                    },
                    {
                        data: 'existencia_sistema',
                        className: 'text-center'
                    },
                    {
                        data: 'conteo_fisico',
                        className: 'text-center'
                    },
                    {
                        data: 'diferencia',
                        className: 'text-center'
                    },
                    {
                        data: 'porcentaje_diferencia',
                        className: 'text-center'
                    },
                    {
                        data: 'costo_unitario',
                        className: 'text-end'
                    },
                    {
                        data: 'valor_diferencia',
                        className: 'text-end'
                    },
                    {
                        data: 'responsable'
                    },
                    {
                        data: 'estatus',
                        className: 'text-center'
                    },
                    {
                        data: 'observaciones'
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                drawCallback: function(settings) {
                    actualizarResumen();
                }
            });
        }

        // Generar reporte
        $('#btnGenerarReporte').on('click', function() {
            inicializarDataTable();
            $('#btnExportarPDF').prop('disabled', false);
            $('#btnExportarExcel').prop('disabled', false);
        });

        // Limpiar filtros
        $('#btnLimpiarFiltros').on('click', function() {
            $('#fecha_conteo').val('<?php echo date('Y-m-d'); ?>');
            $('#id_almacen').val('');
            $('#id_producto').val('');
            $('#estatus_conteo').val('');
            $('#responsable').val('');
            $('#tipo_reporte').val('detallado');
            $('#ordenar_por').val('producto');

            if (tablaInventarioFisico !== null) {
                tablaInventarioFisico.clear().draw();
            }
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

        function actualizarResumen() {
            if (tablaInventarioFisico !== null) {
                const data = tablaInventarioFisico.rows().data().toArray();

                let productosContados = data.length;
                let conDiferencias = 0;
                let totalDiferencias = 0;
                let totalValorDiferencias = 0;
                let totalSistema = 0;
                let totalFisico = 0;

                data.forEach(function(row) {
                    const diferencia = parseInt(row.diferencia) || 0;
                    const valorDiferencia = parseFloat(row.valor_diferencia.replace(/[^0-9.-]+/g, "")) || 0;
                    const sistema = parseInt(row.existencia_sistema) || 0;
                    const fisico = parseInt(row.conteo_fisico) || 0;

                    totalSistema += sistema;
                    totalFisico += fisico;
                    totalDiferencias += Math.abs(diferencia);
                    totalValorDiferencias += Math.abs(valorDiferencia);

                    if (diferencia !== 0) {
                        conDiferencias++;
                    }
                });

                $('#resumenProductosContados').text(productosContados.toLocaleString('es-MX'));
                $('#resumenConDiferencias').text(conDiferencias.toLocaleString('es-MX'));
                $('#resumenTotalDiferencias').text(totalDiferencias.toLocaleString('es-MX'));
                $('#resumenValorDiferencias').text('$' + totalValorDiferencias.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                // Actualizar totales en tabla
                $('#totalSistema').text(totalSistema.toLocaleString('es-MX'));
                $('#totalFisico').text(totalFisico.toLocaleString('es-MX'));
                $('#totalDiferencia').text((totalFisico - totalSistema).toLocaleString('es-MX'));
                $('#totalValorDiferencia').text('$' + totalValorDiferencias.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        }

        function resetResumen() {
            $('#resumenProductosContados').text('0');
            $('#resumenConDiferencias').text('0');
            $('#resumenTotalDiferencias').text('0');
            $('#resumenValorDiferencias').text('$0.00');

            $('#totalSistema').text('0');
            $('#totalFisico').text('0');
            $('#totalDiferencia').text('0');
            $('#totalValorDiferencia').text('$0.00');
        }

        function exportarPDF() {
            const fechaConteo = $('#fecha_conteo').val();
            const idAlmacen = $('#id_almacen').val();
            const idProducto = $('#id_producto').val();
            const estatusConteo = $('#estatus_conteo').val();
            const responsable = $('#responsable').val();
            const tipoReporte = $('#tipo_reporte').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-pdf-inventario-fisico.php?fecha_conteo=${fechaConteo}&id_almacen=${idAlmacen}&id_producto=${idProducto}&estatus_conteo=${estatusConteo}&responsable=${responsable}&tipo_reporte=${tipoReporte}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaConteo = $('#fecha_conteo').val();
            const idAlmacen = $('#id_almacen').val();
            const idProducto = $('#id_producto').val();
            const estatusConteo = $('#estatus_conteo').val();
            const responsable = $('#responsable').val();
            const tipoReporte = $('#tipo_reporte').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-excel-inventario-fisico.php?fecha_conteo=${fechaConteo}&id_almacen=${idAlmacen}&id_producto=${idProducto}&estatus_conteo=${estatusConteo}&responsable=${responsable}&tipo_reporte=${tipoReporte}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        // Inicializar al cargar la página
        inicializarDataTable();
    });
</script>

<style>
    .card.bg-light {
        border: 1px solid #dee2e6;
    }

    #tablaInventarioFisico th {
        font-size: 0.85rem;
    }

    #tablaInventarioFisico td {
        font-size: 0.8rem;
    }

    .badge-aplicado {
        background-color: #28a745;
        color: white;
    }

    .badge-pendiente {
        background-color: #ffc107;
        color: black;
    }

    .diferencia-positiva {
        background-color: #d4edda;
        color: #155724;
    }

    .diferencia-negativa {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>