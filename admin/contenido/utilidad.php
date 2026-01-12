<div class="container-fluid px-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Corte de Caja / Utilidad por Fechas</h5>
            <button class="btn btn-success" id="btnCorteCaja"><i class="fas fa-cash-register"></i> Realizar Corte</button>
        </div>
        <div class="card-body">
            <form id="formFiltroUtilidad" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" id="btnBuscarUtilidad">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnExportarExcel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button>
                </div>
            </form>
            <div class="table-responsive">
                <table id="tablaUtilidad" class="table table-striped table-bordered nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cantidad vendida</th>
                            <th>Precio compra</th>
                            <th>Precio venta</th>
                            <th>Importe venta</th>
                            <th>Costo total</th>
                            <th>Utilidad bruta</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">Totales:</th>
                            <th id="total_costo"></th>
                            <th id="total_utilidad"></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <!-- DataTable server-side -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Corte de Caja (básico, puedes personalizar) -->
<div class="modal fade" id="modalCorteCaja" tabindex="-1" aria-labelledby="modalCorteCajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCorteCaja" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCorteCajaLabel">Realizar Corte de Caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Fecha inicial</label>
                        <input type="date" class="form-control" id="corte_fecha_inicio" name="fecha_inicio" required readonly>
                    </div>
                    <div class="mb-3">
                        <label>Fecha final</label>
                        <input type="date" class="form-control" id="corte_fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="mb-3">
                        <label>Total ventas</label>
                        <input type="text" class="form-control" id="corte_total_ventas" name="total_ventas" required readonly>
                    </div>
                    <div class="mb-3">
                        <label>Total costo</label>
                        <input type="text" class="form-control" id="corte_total_costo" name="total_costo" required readonly>
                    </div>
                    <div class="mb-3">
                        <label>Utilidad</label>
                        <input type="text" class="form-control" id="corte_utilidad" name="utilidad" required readonly>
                    </div>
                    <div class="mb-3">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="corte_observaciones" name="observaciones"></textarea>
                    </div>
                    <div class="alert alert-info" id="resumenCorte" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="btnGuardarCorte"><i class="fas fa-check"></i> Guardar corte</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(function() {
        var tablaUtilidad = $('#tablaUtilidad').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/utilidad/tabla-utilidad.php',
                type: 'POST',
                data: function(d) {
                    d.fecha_inicio = $('#fecha_inicio').val();
                    d.fecha_fin = $('#fecha_fin').val();
                }
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            columns: [{
                    data: 'clave'
                },
                {
                    data: 'producto'
                },
                {
                    data: 'unidad'
                },
                {
                    data: 'cantidad'
                },
                {
                    data: 'precio_compra'
                },
                {
                    data: 'precio_venta'
                },
                {
                    data: 'importe_venta'
                },
                {
                    data: 'costo_total'
                },
                {
                    data: 'utilidad'
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                // Calcular totales del pie (solo utilidad y costo total)
                let totalCosto = api.column(7, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
                let totalUtilidad = api.column(8, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
                $('#total_costo').html(totalCosto.toFixed(2));
                $('#total_utilidad').html(totalUtilidad.toFixed(2));
            },
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        $('#formFiltroUtilidad').on('submit', function(e) {
            e.preventDefault();
            tablaUtilidad.ajax.reload();
        });

        $('#btnExportarExcel').on('click', function() {
            // Lógica para exportar a Excel con PDFMake o JSZip
            // Se implementa después
        });

        $('#btnCorteCaja').on('click', function() {
            $('#corte_fecha_inicio').val($('#fecha_inicio').val());
            $('#corte_fecha_fin').val($('#fecha_fin').val());

            // Suma totales directo del footer del DataTable
            let totalVentas = 0;
            let totalCosto = 0;
            let totalUtilidad = 0;

            $('#tablaUtilidad tbody tr').each(function() {
                totalVentas += parseFloat($(this).find('td').eq(6).text().replace(/,/g, '') || 0); // Importe venta
                totalCosto += parseFloat($(this).find('td').eq(7).text().replace(/,/g, '') || 0); // Costo total
                totalUtilidad += parseFloat($(this).find('td').eq(8).text().replace(/,/g, '') || 0); // Utilidad
            });

            $('#corte_total_ventas').val(totalVentas.toFixed(2));
            $('#corte_total_costo').val(totalCosto.toFixed(2));
            $('#corte_utilidad').val(totalUtilidad.toFixed(2));
            $('#corte_observaciones').val('');
            $('#resumenCorte').hide();

            $('#modalCorteCaja').modal('show');
        });

        // Guardar corte vía AJAX
        $('#btnCorteCaja').on('click', function() {
            // Al abrir modal, obtiene la fecha del último corte y la primera remisión por AJAX
            $.post('ajax/utilidad/obtener-fechas-corte.php', function(res) {
                if (!res.success) {
                    alertify.error(res.msg || 'No se pudieron obtener las fechas base.');
                    return;
                }

                // Fecha inicial lógica:
                $('#corte_fecha_inicio').val(res.fecha_inicio);
                $('#corte_fecha_inicio').prop('readonly', true);

                // Fecha final: por default el día actual, ajusta min/max
                let hoy = new Date().toISOString().slice(0, 10);
                $('#corte_fecha_fin').val(hoy);
                $('#corte_fecha_fin').attr('min', res.fecha_inicio);
                $('#corte_fecha_fin').attr('max', hoy);

                // Limpia totales previos
                $('#corte_total_ventas').val('');
                $('#corte_total_costo').val('');
                $('#corte_utilidad').val('');
                $('#corte_observaciones').val('');
                $('#resumenCorte').hide();

                $('#modalCorteCaja').modal('show');
            }, 'json');
        });

        // Cuando el usuario selecciona una fecha final, calcula los totales de utilidad para ese periodo
        $('#corte_fecha_fin').on('change', function() {
            let fecha_inicio = $('#corte_fecha_inicio').val();
            let fecha_fin = $('#corte_fecha_fin').val();

            if (!fecha_inicio || !fecha_fin) return;

            if (fecha_fin < fecha_inicio) {
                alertify.error('La fecha final no puede ser anterior a la inicial.');
                $('#corte_fecha_fin').val(fecha_inicio);
                return;
            }

            // Llama endpoint que devuelve los totales de ventas/costo/utilidad para ese rango
            $.post('ajax/utilidad/calcular-totales-corte.php', {
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin
            }, function(resp) {
                if (resp.success) {
                    $('#corte_total_ventas').val(resp.total_ventas);
                    $('#corte_total_costo').val(resp.total_costo);
                    $('#corte_utilidad').val(resp.utilidad);
                } else {
                    $('#corte_total_ventas').val('');
                    $('#corte_total_costo').val('');
                    $('#corte_utilidad').val('');
                    alertify.error(resp.msg || 'No se pudieron calcular los totales.');
                }
            }, 'json');
        });

        // Guardar corte vía AJAX
        $('#formCorteCaja').on('submit', function(e) {
            e.preventDefault();

            let fecha_inicio = $('#corte_fecha_inicio').val();
            let fecha_fin = $('#corte_fecha_fin').val();
            let total_ventas = $('#corte_total_ventas').val();
            let total_costo = $('#corte_total_costo').val();
            let utilidad = $('#corte_utilidad').val();

            if (!fecha_inicio || !fecha_fin || !total_ventas || !total_costo || !utilidad) {
                alertify.error('Completa todos los datos antes de guardar el corte.');
                return;
            }

            // No permitir corte sin utilidad o sin ventas
            if (parseFloat(total_ventas) <= 0) {
                alertify.error('No hay ventas registradas en este periodo.');
                return;
            }

            $('#btnGuardarCorte').prop('disabled', true);

            $.ajax({
                url: 'ajax/utilidad/guardar-corte.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        alertify.success(resp.msg);
                        $('#modalCorteCaja').modal('hide');
                        // Puedes recargar la tabla de cortes si existe
                    } else {
                        alertify.error(resp.msg);
                        $('#resumenCorte').html(resp.msg).show();
                    }
                },
                error: function() {
                    alertify.error('Error de comunicación con el servidor.');
                },
                complete: function() {
                    $('#btnGuardarCorte').prop('disabled', false);
                }
            });
        });

    });
</script>