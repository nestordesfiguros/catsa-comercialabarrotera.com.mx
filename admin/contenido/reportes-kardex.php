<?php
// vista kardex
?><?php
    // contenido/reportes-kardex.php

    // Variables de navegación
    $navegar = "reportes";
    ?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kárdex</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-exchange-alt me-2"></i>Kárdex - Movimientos de Inventario</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo date('Y-m-d'); ?>">
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
                            <div class="col-md-4">
                                <label for="tipo_movimiento" class="form-label">Tipo Movimiento</label>
                                <select class="form-select" id="tipo_movimiento" name="tipo_movimiento">
                                    <option value="">Todos los movimientos</option>
                                    <option value="entrada">Entradas</option>
                                    <option value="salida">Salidas</option>
                                    <option value="ajuste">Ajustes</option>
                                    <option value="traspaso">Traspasos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Folio o referencia">
                            </div>
                            <div class="col-md-4">
                                <label for="agrupar_por" class="form-label">Agrupar Por</label>
                                <select class="form-select" id="agrupar_por" name="agrupar_por">
                                    <option value="producto">Producto</option>
                                    <option value="fecha">Fecha</option>
                                    <option value="almacen">Almacén</option>
                                    <option value="tipo">Tipo Movimiento</option>
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
                                                        <h6 class="card-title">Total Entradas</h6>
                                                        <h3 class="text-success" id="resumenEntradas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Salidas</h6>
                                                        <h3 class="text-danger" id="resumenSalidas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Saldo Final</h6>
                                                        <h3 class="text-primary" id="resumenSaldo">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Movimientos</h6>
                                                        <h3 class="text-warning" id="resumenMovimientos">0</h3>
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
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle de Movimientos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaKardex" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Producto</th>
                                                        <th>Almacén</th>
                                                        <th>Tipo Movimiento</th>
                                                        <th>Referencia</th>
                                                        <th>Entrada</th>
                                                        <th>Salida</th>
                                                        <th>Saldo</th>
                                                        <th>Costo Unitario</th>
                                                        <th>Costo Total</th>
                                                        <th>Usuario</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="5"><strong>TOTALES</strong></th>
                                                        <th id="totalEntradas">0</th>
                                                        <th id="totalSalidas">0</th>
                                                        <th id="saldoFinal">0</th>
                                                        <th>-</th>
                                                        <th id="totalCosto">$0.00</th>
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
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        let tablaKardex = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaKardex !== null) {
                tablaKardex.destroy();
            }

            tablaKardex = $('#tablaKardex').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-kardex.php',
                    type: 'POST',
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                        d.id_producto = $('#id_producto').val();
                        d.id_almacen = $('#id_almacen').val();
                        d.tipo_movimiento = $('#tipo_movimiento').val();
                        d.referencia = $('#referencia').val();
                        d.agrupar_por = $('#agrupar_por').val();
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
                        data: 'tipo_movimiento',
                        className: 'text-center'
                    },
                    {
                        data: 'referencia',
                        className: 'text-center'
                    },
                    {
                        data: 'entrada',
                        className: 'text-end'
                    },
                    {
                        data: 'salida',
                        className: 'text-end'
                    },
                    {
                        data: 'saldo',
                        className: 'text-end'
                    },
                    {
                        data: 'costo_unitario',
                        className: 'text-end'
                    },
                    {
                        data: 'costo_total',
                        className: 'text-end'
                    },
                    {
                        data: 'usuario'
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
            $('#fecha_inicio').val('<?php echo date('Y-m-01'); ?>');
            $('#fecha_fin').val('<?php echo date('Y-m-d'); ?>');
            $('#id_producto').val('');
            $('#id_almacen').val('');
            $('#tipo_movimiento').val('');
            $('#referencia').val('');
            $('#agrupar_por').val('producto');

            if (tablaKardex !== null) {
                tablaKardex.clear().draw();
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
            if (tablaKardex !== null) {
                const data = tablaKardex.rows().data().toArray();

                let totalEntradas = 0;
                let totalSalidas = 0;
                let totalCosto = 0;

                data.forEach(function(row) {
                    totalEntradas += parseFloat(row.entrada) || 0;
                    totalSalidas += parseFloat(row.salida) || 0;
                    totalCosto += parseFloat(row.costo_total) || 0;
                });

                const saldoFinal = totalEntradas - totalSalidas;

                $('#resumenEntradas').text(totalEntradas.toLocaleString('es-MX'));
                $('#resumenSalidas').text(totalSalidas.toLocaleString('es-MX'));
                $('#resumenSaldo').text(saldoFinal.toLocaleString('es-MX'));
                $('#resumenMovimientos').text(data.length.toLocaleString('es-MX'));

                $('#totalEntradas').text(totalEntradas.toLocaleString('es-MX'));
                $('#totalSalidas').text(totalSalidas.toLocaleString('es-MX'));
                $('#saldoFinal').text(saldoFinal.toLocaleString('es-MX'));
                $('#totalCosto').text('$' + totalCosto.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        }

        function resetResumen() {
            $('#resumenEntradas').text('0');
            $('#resumenSalidas').text('0');
            $('#resumenSaldo').text('0');
            $('#resumenMovimientos').text('0');

            $('#totalEntradas').text('0');
            $('#totalSalidas').text('0');
            $('#saldoFinal').text('0');
            $('#totalCosto').text('$0.00');
        }

        function exportarPDF() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idProducto = $('#id_producto').val();
            const idAlmacen = $('#id_almacen').val();
            const tipoMovimiento = $('#tipo_movimiento').val();
            const referencia = $('#referencia').val();
            const agruparPor = $('#agrupar_por').val();

            const url = `ajax/reportes/exportar-pdf-kardex.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_producto=${idProducto}&id_almacen=${idAlmacen}&tipo_movimiento=${tipoMovimiento}&referencia=${referencia}&agrupar_por=${agruparPor}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const idProducto = $('#id_producto').val();
            const idAlmacen = $('#id_almacen').val();
            const tipoMovimiento = $('#tipo_movimiento').val();
            const referencia = $('#referencia').val();
            const agruparPor = $('#agrupar_por').val();

            const url = `ajax/reportes/exportar-excel-kardex.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&id_producto=${idProducto}&id_almacen=${idAlmacen}&tipo_movimiento=${tipoMovimiento}&referencia=${referencia}&agrupar_por=${agruparPor}`;
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

    #tablaKardex th {
        font-size: 0.85rem;
    }

    #tablaKardex td {
        font-size: 0.8rem;
    }
</style>