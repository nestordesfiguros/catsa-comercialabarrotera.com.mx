<?php
// contenido/reportes-productos-pendientes.php

// Variables de navegación
$navegar = "reportes";
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Productos Pendientes</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title"><i class="fas fa-clock me-2"></i>Productos Pendientes - Órdenes y Entregas</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="tipo_pendiente" class="form-label">Tipo de Pendiente</label>
                                <select class="form-select" id="tipo_pendiente" name="tipo_pendiente">
                                    <option value="">Todos los pendientes</option>
                                    <option value="orden_compra">Órdenes de Compra</option>
                                    <option value="nota_venta">Notas de Venta</option>
                                    <option value="traslado">Traslados</option>
                                    <option value="devolucion">Devoluciones</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="parcial">Parcialmente Entregado</option>
                                    <option value="atrasado">Atrasado</option>
                                    <option value="urgente">Urgente</option>
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
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="id_cliente" name="id_cliente">
                                    <option value="">Todos los clientes</option>
                                    <?php
                                    $sqlClientes = "SELECT id, razon_social 
                                                  FROM cat_clientes 
                                                  WHERE estatus = 1 
                                                  ORDER BY razon_social";
                                    $clientes = $clsConsulta->consultaGeneral($sqlClientes);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($clientes as $i => $cli) {
                                            echo "<option value=\"{$cli['id']}\">{$cli['razon_social']}</option>";
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
                                <label for="dias_atraso" class="form-label">Días de Atraso Mínimo</label>
                                <input type="number" class="form-control" id="dias_atraso" name="dias_atraso" min="0" value="0" placeholder="Días de atraso">
                            </div>
                            <div class="col-md-3">
                                <label for="ordenar_por" class="form-label">Ordenar Por</label>
                                <select class="form-select" id="ordenar_por" name="ordenar_por">
                                    <option value="fecha_entrega">Fecha de Entrega</option>
                                    <option value="dias_atraso">Días de Atraso</option>
                                    <option value="prioridad">Prioridad</option>
                                    <option value="total">Monto Total</option>
                                    <option value="producto">Producto</option>
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
                                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Resumen de Productos Pendientes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center" id="resumenGeneral">
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Órdenes de Compra</h6>
                                                        <h3 class="text-primary" id="resumenOrdenesCompra">0</h3>
                                                        <small class="text-muted" id="resumenMontoCompra">$0.00</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Notas de Venta</h6>
                                                        <h3 class="text-success" id="resumenNotasVenta">0</h3>
                                                        <small class="text-muted" id="resumenMontoVenta">$0.00</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Pendientes Atrasados</h6>
                                                        <h3 class="text-danger" id="resumenAtrasados">0</h3>
                                                        <small class="text-muted" id="resumenMontoAtrasado">$0.00</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Urgentes</h6>
                                                        <h3 class="text-warning" id="resumenUrgentes">0</h3>
                                                        <small class="text-muted" id="resumenMontoUrgente">$0.00</small>
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
                                        <h5 class="card-title"><i class="fas fa-table me-2"></i>Detalle de Productos Pendientes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tablaProductosPendientes" class="table table-striped table-bordered" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Tipo</th>
                                                        <th>Documento</th>
                                                        <th>Producto</th>
                                                        <th>Proveedor/Cliente</th>
                                                        <th class="text-center">Cant. Pendiente</th>
                                                        <th class="text-center">Cant. Entregada</th>
                                                        <th class="text-center">Cant. Total</th>
                                                        <th class="text-center">% Avance</th>
                                                        <th class="text-center">Fecha Pedido</th>
                                                        <th class="text-center">Fecha Entrega</th>
                                                        <th class="text-center">Días Atraso</th>
                                                        <th class="text-center">Estado</th>
                                                        <th class="text-center">Prioridad</th>
                                                        <th class="text-end">Total Pendiente</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan via AJAX -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="13"><strong>TOTALES</strong></th>
                                                        <th class="text-end" id="totalPendiente">$0.00</th>
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
        let tablaProductosPendientes = null;

        // Inicializar DataTable
        function inicializarDataTable() {
            if (tablaProductosPendientes !== null) {
                tablaProductosPendientes.destroy();
            }

            tablaProductosPendientes = $('#tablaProductosPendientes').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax/reportes/tabla-productos-pendientes.php',
                    type: 'POST',
                    data: function(d) {
                        d.tipo_pendiente = $('#tipo_pendiente').val();
                        d.estado = $('#estado').val();
                        d.id_proveedor = $('#id_proveedor').val();
                        d.id_cliente = $('#id_cliente').val();
                        d.fecha_desde = $('#fecha_desde').val();
                        d.fecha_hasta = $('#fecha_hasta').val();
                        d.dias_atraso = $('#dias_atraso').val();
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
                        data: 'tipo'
                    },
                    {
                        data: 'documento'
                    },
                    {
                        data: 'producto'
                    },
                    {
                        data: 'contacto'
                    },
                    {
                        data: 'cantidad_pendiente',
                        className: 'text-center'
                    },
                    {
                        data: 'cantidad_entregada',
                        className: 'text-center'
                    },
                    {
                        data: 'cantidad_total',
                        className: 'text-center'
                    },
                    {
                        data: 'porcentaje_avance',
                        className: 'text-center'
                    },
                    {
                        data: 'fecha_pedido',
                        className: 'text-center'
                    },
                    {
                        data: 'fecha_entrega',
                        className: 'text-center'
                    },
                    {
                        data: 'dias_atraso',
                        className: 'text-center'
                    },
                    {
                        data: 'estado',
                        className: 'text-center'
                    },
                    {
                        data: 'prioridad',
                        className: 'text-center'
                    },
                    {
                        data: 'total_pendiente',
                        className: 'text-end'
                    }
                ],
                order: [
                    [10, 'desc']
                ], // Ordenar por días de atraso descendente
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
            $('#tipo_pendiente').val('');
            $('#estado').val('');
            $('#id_proveedor').val('');
            $('#id_cliente').val('');
            $('#fecha_desde').val('');
            $('#fecha_hasta').val('');
            $('#dias_atraso').val('0');
            $('#ordenar_por').val('fecha_entrega');

            if (tablaProductosPendientes !== null) {
                tablaProductosPendientes.clear().draw();
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
            if (tablaProductosPendientes !== null) {
                const data = tablaProductosPendientes.rows().data().toArray();

                let ordenesCompra = 0;
                let notasVenta = 0;
                let atrasados = 0;
                let urgentes = 0;

                let montoCompra = 0;
                let montoVenta = 0;
                let montoAtrasado = 0;
                let montoUrgente = 0;
                let totalPendiente = 0;

                data.forEach(function(row) {
                    const tipo = row.tipo;
                    const estado = row.estado;
                    const total = parseFloat(row.total_pendiente.replace(/[^0-9.-]+/g, "")) || 0;

                    totalPendiente += total;

                    if (tipo.includes('Orden Compra')) {
                        ordenesCompra++;
                        montoCompra += total;
                    } else if (tipo.includes('Nota Venta')) {
                        notasVenta++;
                        montoVenta += total;
                    }

                    if (estado.includes('Atrasado')) {
                        atrasados++;
                        montoAtrasado += total;
                    }

                    if (estado.includes('Urgente')) {
                        urgentes++;
                        montoUrgente += total;
                    }
                });

                // Actualizar resumen
                $('#resumenOrdenesCompra').text(ordenesCompra.toLocaleString('es-MX'));
                $('#resumenNotasVenta').text(notasVenta.toLocaleString('es-MX'));
                $('#resumenAtrasados').text(atrasados.toLocaleString('es-MX'));
                $('#resumenUrgentes').text(urgentes.toLocaleString('es-MX'));

                $('#resumenMontoCompra').text('$' + montoCompra.toLocaleString('es-MX', {
                    minimumFractionDigits: 2
                }));
                $('#resumenMontoVenta').text('$' + montoVenta.toLocaleString('es-MX', {
                    minimumFractionDigits: 2
                }));
                $('#resumenMontoAtrasado').text('$' + montoAtrasado.toLocaleString('es-MX', {
                    minimumFractionDigits: 2
                }));
                $('#resumenMontoUrgente').text('$' + montoUrgente.toLocaleString('es-MX', {
                    minimumFractionDigits: 2
                }));

                // Actualizar total en tabla
                $('#totalPendiente').text('$' + totalPendiente.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        }

        function resetResumen() {
            $('#resumenOrdenesCompra').text('0');
            $('#resumenNotasVenta').text('0');
            $('#resumenAtrasados').text('0');
            $('#resumenUrgentes').text('0');

            $('#resumenMontoCompra').text('$0.00');
            $('#resumenMontoVenta').text('$0.00');
            $('#resumenMontoAtrasado').text('$0.00');
            $('#resumenMontoUrgente').text('$0.00');

            $('#totalPendiente').text('$0.00');
        }

        function exportarPDF() {
            const tipoPendiente = $('#tipo_pendiente').val();
            const estado = $('#estado').val();
            const idProveedor = $('#id_proveedor').val();
            const idCliente = $('#id_cliente').val();
            const fechaDesde = $('#fecha_desde').val();
            const fechaHasta = $('#fecha_hasta').val();
            const diasAtraso = $('#dias_atraso').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-pdf-productos-pendientes.php?tipo_pendiente=${tipoPendiente}&estado=${estado}&id_proveedor=${idProveedor}&id_cliente=${idCliente}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&dias_atraso=${diasAtraso}&ordenar_por=${ordenarPor}`;
            window.open(url, '_blank');
        }

        function exportarExcel() {
            const tipoPendiente = $('#tipo_pendiente').val();
            const estado = $('#estado').val();
            const idProveedor = $('#id_proveedor').val();
            const idCliente = $('#id_cliente').val();
            const fechaDesde = $('#fecha_desde').val();
            const fechaHasta = $('#fecha_hasta').val();
            const diasAtraso = $('#dias_atraso').val();
            const ordenarPor = $('#ordenar_por').val();

            const url = `ajax/reportes/exportar-excel-productos-pendientes.php?tipo_pendiente=${tipoPendiente}&estado=${estado}&id_proveedor=${idProveedor}&id_cliente=${idCliente}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&dias_atraso=${diasAtraso}&ordenar_por=${ordenarPor}`;
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

    #tablaProductosPendientes th {
        font-size: 0.85rem;
    }

    #tablaProductosPendientes td {
        font-size: 0.8rem;
    }

    .estado-pendiente {
        background-color: #ffc107;
        color: black;
        font-weight: bold;
    }

    .estado-atrasado {
        background-color: #dc3545;
        color: white;
        font-weight: bold;
    }

    .estado-parcial {
        background-color: #17a2b8;
        color: white;
        font-weight: bold;
    }

    .estado-urgente {
        background-color: #fd7e14;
        color: white;
        font-weight: bold;
    }

    .estado-completado {
        background-color: #28a745;
        color: white;
        font-weight: bold;
    }

    .prioridad-alta {
        color: #dc3545;
        font-weight: bold;
    }

    .prioridad-media {
        color: #fd7e14;
        font-weight: bold;
    }

    .prioridad-baja {
        color: #28a745;
        font-weight: bold;
    }

    .badge-estado {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
</style>