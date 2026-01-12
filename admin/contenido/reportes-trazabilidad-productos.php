<?php
// contenido/reportes-trazabilidad-productos.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Trazabilidad de Productos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-search me-2"></i>Trazabilidad de Productos - Historial Completo</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="id_producto" class="form-label">Producto</label>
                                <select class="form-select" id="id_producto" name="id_producto">
                                    <option value="">Todos los productos</option>
                                    <?php
                                    $sqlProductos = "SELECT id_producto, clave, nombre 
                                                   FROM cat_productos 
                                                   WHERE estado = 'activo' 
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
                                <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                                <select class="form-select" id="tipo_movimiento" name="tipo_movimiento">
                                    <option value="">Todos los movimientos</option>
                                    <option value="entrada">Entradas</option>
                                    <option value="salida">Salidas</option>
                                    <option value="ajuste">Ajustes</option>
                                    <option value="traslado">Traslados</option>
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
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta">
                            </div>
                            <div class="col-md-3">
                                <label for="referencia" class="form-label">Referencia/Búsqueda</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Folio, documento, observaciones...">
                            </div>
                            <div class="col-md-3">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="fecha">Fecha</option>
                                    <option value="producto">Producto</option>
                                    <option value="tipo_movimiento">Tipo de Movimiento</option>
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
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Resumen de Trazabilidad</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Total Movimientos</h6>
                                                        <h3 class="text-primary" id="resumenTotalMovimientos">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Entradas</h6>
                                                        <h3 class="text-success" id="resumenEntradas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Salidas</h6>
                                                        <h3 class="text-danger" id="resumenSalidas">0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Productos Únicos</h6>
                                                        <h3 class="text-info" id="resumenProductosUnicos">0</h3>
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
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Historial de Trazabilidad</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaTrazabilidad" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Fecha/Hora</th>
                                                        <th>Producto</th>
                                                        <th>Categoría</th>
                                                        <th>Tipo Movimiento</th>
                                                        <th>Almacén</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th>Documento</th>
                                                        <th>Referencia</th>
                                                        <th>Usuario</th>
                                                        <th>Saldo Anterior</th>
                                                        <th>Saldo Actual</th>
                                                        <th>Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
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
        let tablaTrazabilidad = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaTrazabilidad !== null) {
                tablaTrazabilidad.destroy();
            }

            tablaTrazabilidad = $('#tablaTrazabilidad').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-trazabilidad-productos.php',
                    type: 'POST',
                    data: function(d) {
                        d.id_producto = $('#id_producto').val();
                        d.id_categoria = $('#id_categoria').val();
                        d.tipo_movimiento = $('#tipo_movimiento').val();
                        d.id_almacen = $('#id_almacen').val();
                        d.fecha_desde = $('#fecha_desde').val();
                        d.fecha_hasta = $('#fecha_hasta').val();
                        d.referencia = $('#referencia').val();
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
                        data: 'fecha_hora'
                    },
                    {
                        data: 'producto'
                    },
                    {
                        data: 'categoria'
                    },
                    {
                        data: 'tipo_movimiento'
                    },
                    {
                        data: 'almacen'
                    },
                    {
                        data: 'cantidad',
                        className: 'text-center'
                    },
                    {
                        data: 'documento'
                    },
                    {
                        data: 'referencia'
                    },
                    {
                        data: 'usuario'
                    },
                    {
                        data: 'saldo_anterior',
                        className: 'text-center'
                    },
                    {
                        data: 'saldo_actual',
                        className: 'text-center'
                    },
                    {
                        data: 'observaciones'
                    }
                ],
                order: [
                    [0, 'desc']
                ], // Ordenar por fecha descendente
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
            $('#id_producto').val('');
            $('#id_categoria').val('');
            $('#tipo_movimiento').val('');
            $('#id_almacen').val('');
            $('#fecha_desde').val('');
            $('#fecha_hasta').val('');
            $('#referencia').val('');
            $('#ordenar_por').val('fecha');

            if (tablaTrazabilidad !== null) {
                tablaTrazabilidad.clear().draw();
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
            if (tablaTrazabilidad !== null) {
                const data = tablaTrazabilidad.rows().data().toArray();

                let totalMovimientos = data.length;
                let entradas = 0;
                let salidas = 0;
                let productosUnicos = new Set();

                data.forEach(function(row) {
                    const tipo = row.tipo_movimiento;
                    const producto = row.producto;

                    productosUnicos.add(producto);

                    if (tipo.includes('Entrada') || tipo.includes('Compra')) {
                        entradas++;
                    } else if (tipo.includes('Salida') || tipo.includes('Venta')) {
                        salidas++;
                    }
                });

                $('#resumenTotalMovimientos').text(totalMovimientos.toLocaleString('es-MX'));
                $('#resumenEntradas').text(entradas.toLocaleString('es-MX'));
                $('#resumenSalidas').text(salidas.toLocaleString('es-MX'));
                $('#resumenProductosUnicos').text(productosUnicos.size.toLocaleString('es-MX'));
            }
        }

        function resetResumen() {
            $('#resumenTotalMovimientos').text('0');
            $('#resumenEntradas').text('0');
            $('#resumenSalidas').text('0');
            $('#resumenProductosUnicos').text('0');
        }

        function exportarPDF() {
            const idProducto = $('#id_producto').val();
            const idCategoria = $('#id_categoria').val();
            const tipoMovimiento = $('#tipo_movimiento').val();
            const idAlmacen = $('#id_almacen').val();
            const fechaDesde = $('#fecha_desde').val();
            const fechaHasta = $('#fecha_hasta').val();
            const referencia = $('#referencia').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-pdf-trazabilidad-productos.php?id_producto=${idProducto}&id_categoria=${idCategoria}&tipo_movimiento=${tipoMovimiento}&id_almacen=${idAlmacen}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&referencia=${referencia}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const idProducto = $('#id_producto').val();
            const idCategoria = $('#id_categoria').val();
            const tipoMovimiento = $('#tipo_movimiento').val();
            const idAlmacen = $('#id_almacen').val();
            const fechaDesde = $('#fecha_desde').val();
            const fechaHasta = $('#fecha_hasta').val();
            const referencia = $('#referencia').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-excel-trazabilidad-productos.php?id_producto=${idProducto}&id_categoria=${idCategoria}&tipo_movimiento=${tipoMovimiento}&id_almacen=${idAlmacen}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&referencia=${referencia}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        // Establecer fechas por defecto (últimos 30 días)
        const fechaHasta = new Date();
        const fechaDesde = new Date();
        fechaDesde.setDate(fechaDesde.getDate() - 30);

        $('#fecha_desde').val(fechaDesde.toISOString().split('T')[0]);
        $('#fecha_hasta').val(fechaHasta.toISOString().split('T')[0]);

        // Inicializar al cargar la página
        inicializarDataTable();
    });
</script>

<style>
    .card.bg-light {
        border: 1px solid #dee2e6;
    }

    #tablaTrazabilidad th {
        font-size: 0.85rem;
    }

    #tablaTrazabilidad td {
        font-size: 0.8rem;
    }

    .movimiento-entrada {
        background-color: #d4edda !important;
        border-left: 4px solid #28a745;
    }

    .movimiento-salida {
        background-color: #f8d7da !important;
        border-left: 4px solid #dc3545;
    }

    .movimiento-ajuste {
        background-color: #fff3cd !important;
        border-left: 4px solid #ffc107;
    }

    .movimiento-traslado {
        background-color: #d1ecf1 !important;
        border-left: 4px solid #17a2b8;
    }

    .badge-movimiento {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
</style>