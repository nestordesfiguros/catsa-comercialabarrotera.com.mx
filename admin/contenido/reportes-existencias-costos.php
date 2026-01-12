<?php
// contenido/reportes-existencias-costos.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Existencias y Costos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-boxes me-2"></i>Existencias y Costos de Inventario</h3>
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
                                <label for="estatus_stock" class="form-label">Estado Stock</label>
                                <select class="form-select" id="estatus_stock" name="estatus_stock">
                                    <option value="">Todos</option>
                                    <option value="stock_minimo">Stock Mínimo</option>
                                    <option value="stock_maximo">Stock Máximo</option>
                                    <option value="sin_existencia">Sin Existencia</option>
                                    <option value="con_existencia">Con Existencia</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="nombre">Nombre Producto</option>
                                    <option value="existencia">Existencia</option>
                                    <option value="costo">Costo Unitario</option>
                                    <option value="valor">Valor Total</option>
                                    <option value="clave">Clave</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="direccion_orden" class="form-label">Dirección</label>
                                <select class="form-select" id="direccion_orden" name="direccion_orden">
                                    <option value="asc">Ascendente</option>
                                    <option value="desc">Descendente</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="mostrar_solo" class="form-label">Mostrar Solo</label>
                                <select class="form-select" id="mostrar_solo" name="mostrar_solo">
                                    <option value="">Todos los productos</option>
                                    <option value="activos">Productos Activos</option>
                                    <option value="inactivos">Productos Inactivos</option>
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen de Inventario</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Productos</h6>
                                                        <h3 class="text-primary" id="resumenTotalProductos">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Valor Total Inventario</h6>
                                                        <h3 class="text-success" id="resumenValorTotal">$0.00</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Productos Sin Stock</h6>
                                                        <h3 class="text-danger" id="resumenSinStock">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Stock Mínimo Alcanzado</h6>
                                                        <h3 class="text-warning" id="resumenStockMinimo">0</h3>
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
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle de Existencias y Costos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaExistencias" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Clave</th>
                                                        <th>Producto</th>
                                                        <th>Categoría</th>
                                                        <th>Almacén</th>
                                                        <th>Proveedor</th>
                                                        <th class="text-center">Existencia</th>
                                                        <th class="text-center">Stock Mín</th>
                                                        <th class="text-center">Stock Máx</th>
                                                        <th class="text-center">Estado</th>
                                                        <th class="text-end">Costo Unitario</th>
                                                        <th class="text-end">Precio Venta</th>
                                                        <th class="text-end">Valor Total</th>
                                                        <th class="text-center">Última Actualización</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="5"><strong>TOTALES / PROMEDIOS</strong></th>
                                                        <th class="text-center" id="totalExistencia">0</th>
                                                        <th colspan="2">-</th>
                                                        <th>-</th>
                                                        <th class="text-end" id="promedioCosto">$0.00</th>
                                                        <th class="text-end" id="promedioVenta">$0.00</th>
                                                        <th class="text-end" id="totalValor">$0.00</th>
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
        let tablaExistencias = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaExistencias !== null) {
                tablaExistencias.destroy();
            }

            tablaExistencias = $('#tablaExistencias').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-existencias-costos.php',
                    type: 'POST',
                    data: function(d) {
                        d.id_almacen = $('#id_almacen').val();
                        d.id_categoria = $('#id_categoria').val();
                        d.id_proveedor = $('#id_proveedor').val();
                        d.estatus_stock = $('#estatus_stock').val();
                        d.ordenar_por = $('#ordenar_por').val();
                        d.direccion_orden = $('#direccion_orden').val();
                        d.mostrar_solo = $('#mostrar_solo').val();
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
                        data: 'clave',
                        className: 'text-center'
                    },
                    {
                        data: 'producto'
                    },
                    {
                        data: 'categoria'
                    },
                    {
                        data: 'almacen'
                    },
                    {
                        data: 'proveedor'
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
                        data: 'estado',
                        className: 'text-center'
                    },
                    {
                        data: 'costo_unitario',
                        className: 'text-end'
                    },
                    {
                        data: 'precio_venta',
                        className: 'text-end'
                    },
                    {
                        data: 'valor_total',
                        className: 'text-end'
                    },
                    {
                        data: 'ultima_actualizacion',
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'asc']
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
            $('#estatus_stock').val('');
            $('#ordenar_por').val('nombre');
            $('#direccion_orden').val('asc');
            $('#mostrar_solo').val('');

            if (tablaExistencias !== null) {
                tablaExistencias.clear().draw();
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
            if (tablaExistencias !== null) {
                const data = tablaExistencias.rows().data().toArray();

                let totalProductos = data.length;
                let totalValor = 0;
                let sinStock = 0;
                let stockMinimo = 0;

                data.forEach(function(row) {
                    totalValor += parseFloat(row.valor_total.replace(/[^0-9.-]+/g, "")) || 0;

                    if (parseInt(row.existencia) === 0) {
                        sinStock++;
                    }

                    if (parseInt(row.existencia) <= parseInt(row.stock_minimo) && parseInt(row.existencia) > 0) {
                        stockMinimo++;
                    }
                });

                $('#resumenTotalProductos').text(totalProductos.toLocaleString('es-MX'));
                $('#resumenValorTotal').text('$' + totalValor.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#resumenSinStock').text(sinStock.toLocaleString('es-MX'));
                $('#resumenStockMinimo').text(stockMinimo.toLocaleString('es-MX'));

                // Actualizar totales en tabla
                let totalExistencia = data.reduce((sum, row) => sum + (parseInt(row.existencia) || 0), 0);
                let totalCosto = data.reduce((sum, row) => sum + (parseFloat(row.costo_unitario.replace(/[^0-9.-]+/g, "")) || 0), 0);
                let totalVenta = data.reduce((sum, row) => sum + (parseFloat(row.precio_venta.replace(/[^0-9.-]+/g, "")) || 0), 0);

                let promedioCosto = totalProductos > 0 ? totalCosto / totalProductos : 0;
                let promedioVenta = totalProductos > 0 ? totalVenta / totalProductos : 0;

                $('#totalExistencia').text(totalExistencia.toLocaleString('es-MX'));
                $('#promedioCosto').text('$' + promedioCosto.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#promedioVenta').text('$' + promedioVenta.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#totalValor').text('$' + totalValor.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        }

        function resetResumen() {
            $('#resumenTotalProductos').text('0');
            $('#resumenValorTotal').text('$0.00');
            $('#resumenSinStock').text('0');
            $('#resumenStockMinimo').text('0');

            $('#totalExistencia').text('0');
            $('#promedioCosto').text('$0.00');
            $('#promedioVenta').text('$0.00');
            $('#totalValor').text('$0.00');
        }

        function exportarPDF() {
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const idProveedor = $('#id_proveedor').val();
            const estatusStock = $('#estatus_stock').val();
            const ordenarPor = $('#ordenar_por').val();
            const direccionOrden = $('#direccion_orden').val();
            const mostrarSolo = $('#mostrar_solo').val();

            const url = `ajax/reportes/exportar-pdf-existencias-costos.php?id_almacen=${idAlmacen}&id_categoria=${idCategoria}&id_proveedor=${idProveedor}&estatus_stock=${estatusStock}&ordenar_por=${ordenarPor}&direccion_orden=${direccionOrden}&mostrar_solo=${mostrarSolo}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const idAlmacen = $('#id_almacen').val();
            const idCategoria = $('#id_categoria').val();
            const idProveedor = $('#id_proveedor').val();
            const estatusStock = $('#estatus_stock').val();
            const ordenarPor = $('#ordenar_por').val();
            const direccionOrden = $('#direccion_orden').val();
            const mostrarSolo = $('#mostrar_solo').val();

            const url = `ajax/reportes/exportar-excel-existencias-costos.php?id_almacen=${idAlmacen}&id_categoria=${idCategoria}&id_proveedor=${idProveedor}&estatus_stock=${estatusStock}&ordenar_por=${ordenarPor}&direccion_orden=${direccionOrden}&mostrar_solo=${mostrarSolo}`;
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

    #tablaExistencias th {
        font-size: 0.85rem;
    }

    #tablaExistencias td {
        font-size: 0.8rem;
    }

    .badge-stock-ok {
        background-color: #28a745;
        color: white;
    }

    .badge-stock-min {
        background-color: #ffc107;
        color: black;
    }

    .badge-stock-critico {
        background-color: #dc3545;
        color: white;
    }
</style>