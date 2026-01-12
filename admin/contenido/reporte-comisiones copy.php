<?php
$hoy = date('Y-m-d');
$semanaInicio = date('Y-m-d', strtotime('monday this week'));
$semanaFin = date('Y-m-d', strtotime('sunday this week'));
?>

<!-- Content Header -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reporte de comisiones</li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label mb-0">Desde</label>
                            <input type="date" id="filtroDesde" class="form-control" value="<?= $semanaInicio ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-0">Hasta</label>
                            <input type="date" id="filtroHasta" class="form-control" value="<?= $semanaFin ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-0">Vendedor</label>
                            <select id="filtroVendedor" class="form-select"></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-0">Estatus</label>
                            <select id="filtroEstatus" class="form-select">
                                <option value="pendiente" selected>Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="cancelado">Cancelado</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="tablaComisiones" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>% Comisión</th>
                            <th>Total Ventas</th>
                            <th>Total Comisión</th>
                            <th>Estatus</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Pago</th>
                            <th>Detalle</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
</section>


<!-- Modal Detalle de Comisiones -->
<div class="modal fade" id="modalDetalleComisiones" tabindex="-1" aria-labelledby="detalleComisionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detalleComisionesLabel">
                    Detalle de Remisiones por Vendedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar" onclick="fnmodalDetalleComisionesCerrar();"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaDetalleComisiones" class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total Remisión</th>
                                <th>Comisión Aplicada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llena dinámicamente vía JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">Comisiones calculadas según porcentaje vigente en el periodo seleccionado</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="fnmodalDetalleComisionesCerrar();">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Select2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function verComprobante(id_comision) {
        const url = `ajax/comisiones/generar-comprobante-comision.php?id=${id_comision}`;
        window.open(url, '_blank');
    }

    function fnmodalDetalleComisionesCerrar() {
        $("#modalDetalleComisiones").modal('hide');
    }

    function marcarPagado(id_vendedor, desde, hasta) {
        if (!id_vendedor || !desde || !hasta) {
            alertify.error("Datos incompletos para aplicar el pago.");
            return;
        }

        alertify.confirm("Confirmar pago", "¿Deseas marcar como pagada esta comisión?", function() {
            $.post('ajax/comisiones/marcar-pagado.php', {
                vendedor: id_vendedor,
                desde: desde,
                hasta: hasta
            }, function(resp) {
                if (resp.success) {
                    alertify.success('Comisión marcada como pagada.');
                    $('#tablaComisiones').DataTable().ajax.reload();
                } else {
                    alertify.error('Error al marcar como pagado.');
                }
            }, 'json');
        }, function() {
            alertify.message("Cancelado");
        });
    }


    function limpiarFiltros() {
        $('#filtroVendedor').val(null).trigger('change');
    }


    function verDetalleComisiones(id_vendedor, fecha_inicio, fecha_fin) {
        $('#detalleComisionesLabel').text('Detalle de comisiones');
        $('#tablaDetalleComisiones tbody').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');

        $.ajax({
            url: 'ajax/comisiones/detalle-comisiones.php',
            type: 'POST',
            dataType: 'html',
            data: {
                id_vendedor: id_vendedor,
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin
            },
            success: function(response) {
                $('#tablaDetalleComisiones tbody').html(response);
                $('#modalDetalleComisiones').modal('show');
            },
            error: function(xhr) {
                alertify.error('Error al consultar detalle: ' + xhr.statusText);
                $('#modalDetalleComisiones').modal('hide');
            }
        });
    }


    $(document).ready(function() {
        let tabla;

        tabla = $('#tablaComisiones').DataTable({
            ajax: {
                url: 'ajax/comisiones/tabla-reporte-comisiones.php',
                type: 'POST',
                data: function(d) {
                    d.desde = $('#filtroDesde').val();
                    d.hasta = $('#filtroHasta').val();
                    d.vendedor = $('#filtroVendedor').val();
                    d.estatus = $('#filtroEstatus').val(); // ✅ Aquí pasa el estatus
                }
            },
            ordering: false,
            responsive: true,
            destroy: true,
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        // Filtros automáticos al cambiar
        $('#filtroDesde, #filtroHasta, #filtroEstatus').on('change', function() {
            tabla.ajax.reload();
        });

        $('#filtroVendedor').select2({
            placeholder: 'Selecciona un vendedor',
            allowClear: true,
            ajax: {
                url: 'ajax/remisiones/buscar-vendedores.php',
                method: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function(data) {
                    data.unshift({
                        id: '0',
                        text: 'Todos'
                    });
                    return {
                        results: data
                    };
                }
            }
        }).on('change', function() {
            tabla.ajax.reload();
        });

        // Filtro Estatus
        $('#filtroEstatus').on('change', function() {
            tabla.ajax.reload();
        });

        // Listeners para recargar automáticamente
        $('#filtroDesde, #filtroHasta').on('change', function() {
            tabla.ajax.reload();
        });

        $('#btnFiltrar').on('click', function() {
            tabla.ajax.reload();
        });

        // Cuando cambia el rango de fechas
        $('#filtroDesde, #filtroHasta').on('change', function() {
            tabla.ajax.reload();
        });

        // Cuando cambia el vendedor
        $('#filtroVendedor').on('change', function() {
            tabla.ajax.reload();
        });


        $('#btnMarcarPagado').on('click', function() {
            const desde = $('#filtroDesde').val();
            const hasta = $('#filtroHasta').val();
            const vendedor = $('#filtroVendedor').val();

            if (!desde || !hasta || !vendedor) {
                alertify.error("Debe seleccionar fechas y vendedor.");
                return;
            }

            $.post('ajax/comisiones/marcar-pagado.php', {
                desde,
                hasta,
                vendedor
            }, function(resp) {
                if (resp.success) {
                    alertify.success('Comisión marcada como pagada.');
                    tabla.ajax.reload();
                } else {
                    alertify.error('No se pudo marcar como pagado.');
                }
            }, 'json');
        });

        $('#btnExportarExcel').on('click', function() {
            const desde = $('#filtroDesde').val();
            const hasta = $('#filtroHasta').val();
            const vendedor = $('#filtroVendedor').val();
            window.open(`ajax/comisiones/exportar-reporte-comisiones.php?desde=${desde}&hasta=${hasta}&vendedor=${vendedor}`, '_blank');
        });

    });
</script>