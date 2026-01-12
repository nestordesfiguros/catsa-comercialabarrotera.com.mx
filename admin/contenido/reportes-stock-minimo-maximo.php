<?php
// contenido/reportes-stock-minimo-maximo.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stock Mínimo y Máximo</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Stock Mínimo y Máximo - Alertas de Inventario</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
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
                                <label for="id_categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="id_categoria" name="id_categoria">
                                    <option value="">Todas las categorías</option>
                                    <?php
                                    $sqlCategorias = "SELECT id_categoria, nombre_categoria 
                                                    FROM cat_categorias 
                                                    ORDER BY nombre_categoria";
                                    $categorias = $clsConsulta->consultaGeneral($sqlCategorias);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($categorias as $i => $cat) {
                                            echo "<option value=\"{$cat['id_categoria']}\">{$cat['nombre_categoria']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="id_proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="id_proveedor" name="id_proveedor">
                                    <option value="">Todos los proveedores</option>
                                    <?php
                                    $sqlProveedores = "SELECT id, razon_social 
                                                     FROM cat_proveedores 
                                                     WHERE estatus = 1 
                                                     ORDER BY razon_social";
                                    $proveedores = $clsConsulta->consultaGeneral($sqlProveedores);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($proveedores as $i => $prov) {
                                            echo "<option value=\"{$prov['id']}\">{$prov['razon_social']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="tipo_alerta" class="form-label">Tipo de Alerta</label>
                                <select class="form-select" id="tipo_alerta" name="tipo_alerta">
                                    <option value="">Todas las alertas</option>
                                    <option value="stock_minimo">Stock Mínimo Alcanzado</option>
                                    <option value="stock_maximo">Stock Máximo Excedido</option>
                                    <option value="sin_existencia">Sin Existencia</option>
                                    <option value="critico">Stock Crítico</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="diferencia_min">Diferencia Mínimo</option>
                                    <option value="diferencia_max">Diferencia Máximo</option>
                                    <option value="producto">Producto</option>
                                    <option value="existencia">Existencia</option>
                                    <option value="nivel_critico">Nivel Crítico</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="mostrar_solo" class="form-label">Mostrar Solo</label>
                                <select class="form-select" id="mostrar_solo" name="mostrar_solo">
                                    <option value="">Todos los productos</option>
                                    <option value="con_alerta">Con Alertas</option>
                                    <option value="sin_alerta">Sin Alertas</option>
                                    <option value="activos">Productos Activos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="nivel_critico" class="form-label">Nivel Crítico (%)</label>
                                <input type="number" class="form-control" id="nivel_critico" name="nivel_critico" min="0" max="100" value="20" placeholder="Porcentaje para stock crítico">
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
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen de Alertas de Stock</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Stock Mínimo Alcanzado</h6>
                                                        <h3 class="text-warning" id="resumenStockMinimo">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Stock Máximo Excedido</h6>
                                                        <h3 class="text-info" id="resumenStockMaximo">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Sin Existencia</h6>
                                                        <h3 class="text-danger" id="resumenSinExistencia">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Stock Crítico</h6>
                                                        <h3 class="text-danger" id="resumenStockCritico">0</h3>
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
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle de Alertas de Stock</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaStockAlertas" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Categoría</th>
                                                        <th>Almacén</th>
                                                        <th class="text-center">Existencia</th>
                                                        <th class="text-center">Stock Mín</th>
                                                        <th class="text-center">Stock Máx</th>
                                                        <th class="text-center">Diferencia Mín</th>
                                                        <th class="text-center">Diferencia Máx</th>
                                                        <th class="text-center">% Stock Mín</th>
                                                        <th class="text-center">Nivel</th>
                                                        <th class="text-center">Alertas</th>
                                                        <th class="text-end">Valor en Riesgo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="11"><strong>TOTALES</strong></th>
                                                        <th class="text-end" id="totalValorRiesgo">$0.00</th>
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
        let tablaStockAlertas = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaStockAlertas !== null) {
                tablaStockAlertas.destroy();
            }

            tablaStockAlertas = $('#tablaStockAlertas').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-stock-minimo-maximo.php',
                    type: 'POST',
                    data: function(d) {
                        d.id_almacen = $('#id_almacen').val();
                        d.id_categoria = $('#id_categoria').val();
                        d.id_proveedor = $('#id_proveedor').val();
                        d.tipo_alerta = $('#tipo_alerta').val();
                        d.ordenar_por = $('#ordenar_por').val();
                        d.mostrar_solo = $('#mostrar_solo').val();
                        d.nivel_critico = $('#nivel_critico').val();
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
                        data: 'producto'
                    },
                    {
                        data: 'categoria'
                    },
                    {
                        data: 'almacen'
                    },
                    {
                        data: 'existencia',
                        className: 'text-center'
                    },
                    {
                        data: 'stock_minimo',
                        className: 'text-center'
                    },
                    {
                        data: 'stock_maximo',
                        className: 'text-center'
                    },
                    {
                        data: 'diferencia_min',
                        className: 'text-center'
                    },
                    {
                        data: 'diferencia_max',
                        className: 'text-center'
                    },
                    {
                        data: 'porcentaje_min',
                        className: 'text-center'
                    },
                    {
                        data: 'nivel',
                        className: 'text-center'
                    },
                    {
                        data: 'alertas',
                        className: 'text-center'
                    },
                    {
                        data: 'valor_riesgo',
                        className: 'text-end'
                    }
                ],
                order: [
                    [6, 'asc']
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
            $('#id_almacen').val('');
            $('#id_categoria').val('');
            $('#id_proveedor').val('');
            $('#tipo_alerta').val('');
            $('#ordenar_por').val('diferencia_min');
            $('#mostrar_solo').val('');
            $('#nivel_critico').val('20');

            if (tablaStockAlertas !== null) {
                tablaStockAlertas.clear().draw();
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
            if (tablaStockAlertas !== null) {
                const data = tablaStockAlertas.rows().data().toArray();

                let stockMinimo = 0;
                let stockMaximo = 0;
                let sinExistencia = 0;
                let stockCritico = 0;
                let totalValorRiesgo = 0;

                data.forEach(function(row) {
                    const alertas = row.alertas;
                    const valorRiesgo = parseFloat(row.valor_riesgo.replace(/[^0-9.-]+/g, "")) || 0;

                    totalValorRiesgo += valorRiesgo;

                    if (alertas.includes('stock_minimo')) stockMinimo++;
                    if (alertas.includes('stock_maximo')) stockMaximo++;
                    if (alertas.includes('sin_existencia')) sinExistencia++;
                    if (alertas.includes('critico')) stockCritico++;
                });

                $('#resumenStockMinimo').text(stockMinimo.toLocaleString('es-MX'));
                $('#resumenStockMaximo').text(stockMaximo.toLocaleString('es-MX'));
                $('#resumenSinExistencia').text(sinExistencia.toLocaleString('es-MX'));
                $('#resumenStockCritico').text(stockCritico.toLocaleString('es-MX'));

                // Actualizar totales en tabla
                $('#totalValorRiesgo').text('$' + totalValorRiesgo.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        }

        function resetResumen() {
            $('#resumenStockMinimo').text('0');
            $('#resumenStockMaximo').text('0');
            $('#resumenSinExistencia').text('0');
            $('#resumenStockCritico').text('0');
            $('#totalValorRiesgo').text('$0.00');
        }

        function exportarPDF() {
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const idProveedor = $('#id_proveedor').val();
            const tipoAlerta = $('#tipo_alerta').val();
            const ordenarPor = $('#ordenar_por').val();
            const mostrarSolo = $('#mostrar_solo').val();
            const nivelCritico = $('#nivel_critico').val();

            const url = `ajax/reportes/exportar-pdf-stock-minimo-maximo.php?id_almacen=${idAlmacen}&id_categoria=${idCategoria}&id_proveedor=${idProveedor}&tipo_alerta=${tipoAlerta}&ordenar_por=${ordenarPor}&mostrar_solo=${mostrarSolo}&nivel_critico=${nivelCritico}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const idProveedor = $('#id_proveedor').val();
            const tipoAlerta = $('#tipo_alerta').val();
            const ordenarPor = $('#ordenar_por').val();
            const mostrarSolo = $('#mostrar_solo').val();
            const nivelCritico = $('#nivel_critico').val();

            const url = `ajax/reportes/exportar-excel-stock-minimo-maximo.php?id_almacen=${idAlmacen}&id_categoria=${idCategoria}&id_proveedor=${idProveedor}&tipo_alerta=${tipoAlerta}&ordenar_por=${ordenarPor}&mostrar_solo=${mostrarSolo}&nivel_critico=${nivelCritico}`;
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

    #tablaStockAlertas th {
        font-size: 0.85rem;
    }

    #tablaStockAlertas td {
        font-size: 0.8rem;
    }

    .nivel-critico {
        background-color: #dc3545;
        color: white;
        font-weight: bold;
    }

    .nivel-bajo {
        background-color: #ffc107;
        color: black;
    }

    .nivel-normal {
        background-color: #28a745;
        color: white;
    }

    .nivel-exceso {
        background-color: #17a2b8;
        color: white;
    }

    .badge-alerta {
        font-size: 0.7rem;
        margin: 1px;
    }
</style>