<!-- contenido/compras.php  -->

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Órdenes de Compra</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-12 col-md-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="proveedores"><i class="fas fa-truck-moving"></i> Proveedores </a> &nbsp;
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="compras-altas"><i class="fa fa-plus"></i> Orden de Compra</a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxp"><i class="fas fa-hand-holding-usd"></i> CxP </a> &nbsp;
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mt-2 mt-md-0">
                                    <div class="form-floating">
                                        <input type="text" id="search" class="form-control" placeholder="Buscar" />
                                        <label for="search">Buscar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="tablaOrdenesCompra" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Nombre Comercial / Razón Social</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Detalle</th>
                                    <th class="text-center">Pdf</th>
                                    <th class="text-center">estatus</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detalle de compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cabDetalle"></div>
                <div id="detalle"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estatus -->
<div class="modal fade" id="estatusModal" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CerrarModalEstatus();"></button>
            </div>
            <div class="modal-body">

                <select id="nuevoEstatus" class="form-select">
                    <option value="1" class="text-success">Pendiente</option>
                    <option value="2">Procesada</option>
                    <option value="3" class="text-danger">Cancelada</option>
                </select>

                <div class="mt-3" id="wrapAlmacen" style="display:none;">
                    <label class="form-label mb-1">¿A qué almacén afecta el movimiento?</label>
                    <select id="idAlmacenMovimiento" class="form-select">
                        <option value="">Selecciona almacén...</option>
                    </select>
                    <div class="form-text">
                        Requerido cuando el cambio afecte inventario.
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <input type="hidden" id="idOrdenCompra">
                <button type="button" class="btn btn-primary" onclick="fnCambiarEstatus();">Realizar Cambio</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ==================================================
    // DETALLE COMPRA (GLOBAL) - FIX DEFINITIVO
    // ==================================================

    // Función NUEVA (por si ya la llamas en otro lugar)
    window.fnDetalleCompra = function(id, fecha, nombre /*, total YA NO LO USES */ ) {

        // Asegura abrir modal (aunque ya venga data-bs-toggle/target)
        if ($('#detalleModal').length) $('#detalleModal').modal('show');

        // Título
        $("#exampleModalLabel").text(nombre || 'Detalle de compra');

        // Encabezado temporal
        $("#cabDetalle").html(
            '<table class="table"><tr>' +
            '<td>Folio: ' + id + '</td>' +
            '<td class="text-center">Fecha: ' + (fecha || '') + '</td>' +
            '<td class="text-end">Total: —</td>' +
            '</tr></table>'
        );

        // Limpia contenido anterior para que se note el refresh
        $("#detalle").html('<div class="text-center p-3">Cargando...</div>');

        $.ajax({
            url: 'ajax/compras/detalle.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function(respuesta) {
                $("#detalle").html(respuesta);

                // Tomar data-total de #tablaDetalle (tu backend ya lo agrega)
                var totalCalc = ($("#tablaDetalle").attr("data-total") || "0.00");

                $("#cabDetalle").html(
                    '<table class="table"><tr>' +
                    '<td>Folio: ' + id + '</td>' +
                    '<td class="text-center">Fecha: ' + (fecha || '') + '</td>' +
                    '<td class="text-end">Total: $' + totalCalc + '</td>' +
                    '</tr></table>'
                );
            },
            error: function(xhr) {
                $("#detalle").html('');
                alertify.alert('Error', 'Error de conexión: ' + xhr.statusText);
            }
        });
    };

    // Función ORIGINAL que tu DataTable llama: onclick="detalle(...)"
    // ✅ Compatibilidad total con tu tabla-compras.php
    window.detalle = function(id, fecha, nombre) {
        return window.fnDetalleCompra(id, fecha, nombre);
    };

    // ==================================================
    // ESTATUS (mantengo tu lógica tal como venía)
    // ==================================================
    window._ocEstatusActual = 0;

    function CerrarModalEstatus() {
        $("#estatusModal").modal('hide');
    }

    // OJO: estas funciones existen pero tu backend (estatus.php / almacenes.php)
    // debe responder según lo que ya estabas implementando.
    function cargarAlmacenesMovimiento() {
        return $.ajax({
            url: 'ajax/compras/almacenes.php',
            method: 'POST',
            dataType: 'json'
        }).then(function(res) {
            var $sel = $('#idAlmacenMovimiento');
            $sel.empty().append('<option value="">Selecciona almacén...</option>');

            if (res && res.success && Array.isArray(res.data) && res.data.length) {
                res.data.forEach(function(a) {
                    $sel.append('<option value="' + a.id + '">' + String(a.almacen) + '</option>');
                });
                if (!$sel.val()) $sel.val(String(res.data[0].id));
            }
        }).catch(function() {});
    }

    function getAlmacenSeleccionadoTexto() {
        var $sel = $('#idAlmacenMovimiento');
        return String($sel.find('option:selected').text() || '').trim();
    }

    function requiereAlmacen(nuevo) {
        var nuevoE = parseInt(nuevo, 10) || 0;
        if (window._ocEstatusActual === 1 && nuevoE === 2) return true; // pendiente->procesada
        if (window._ocEstatusActual === 2 && nuevoE === 3) return true; // procesada->cancelada
        return false;
    }

    window.fnMostrarEstatus = function(id, estatus) {
        window._ocEstatusActual = parseInt(estatus, 10) || 0;

        if (window._ocEstatusActual === 3) {
            alertify.alert('Aviso', 'Esta orden ya está cancelada y no se puede modificar.');
            return;
        }

        $("#estatusModal").modal('show');
        $("#idOrdenCompra").val(id);

        $('#wrapAlmacen').hide();
        $('#idAlmacenMovimiento').val('');

        var select = document.getElementById("nuevoEstatus");
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].disabled = false;
            select.options[i].hidden = false;
        }

        if (window._ocEstatusActual === 2) {
            for (var j = 0; j < select.options.length; j++) {
                if (parseInt(select.options[j].value, 10) === 1) {
                    select.options[j].disabled = true;
                    select.options[j].hidden = true;
                }
            }
        }

        for (var k = 0; k < select.options.length; k++) {
            var op = select.options[k];
            op.selected = (parseInt(op.value, 10) === window._ocEstatusActual);
        }

        cargarAlmacenesMovimiento();
    };

    $('#nuevoEstatus').on('change', function() {
        var val = $(this).val();

        if (requiereAlmacen(val)) {
            $('#wrapAlmacen').show();

            var $sel = $('#idAlmacenMovimiento');
            if (!$sel.val()) {
                var firstVal = $sel.find('option:eq(1)').val();
                if (firstVal) $sel.val(firstVal);
            }

            var almTxt = getAlmacenSeleccionadoTexto();
            if (almTxt) {
                alertify.alert(
                    'Movimiento de inventario',
                    'El movimiento se aplicará en el almacén: <b>' + almTxt + '</b>.<br>Si deseas, puedes seleccionar otro almacén antes de confirmar.'
                );
            } else {
                alertify.alert('Movimiento de inventario', 'Debes seleccionar el almacén donde se aplicará el movimiento.');
            }
        } else {
            $('#wrapAlmacen').hide();
            $('#idAlmacenMovimiento').val('');
        }
    });

    $('#idAlmacenMovimiento').on('change', function() {
        var almTxt = getAlmacenSeleccionadoTexto();
        if (almTxt) alertify.alert('Movimiento de inventario', 'El movimiento se aplicará en el almacén: <b>' + almTxt + '</b>.');
    });

    window.fnCambiarEstatus = function() {
        var estatus = parseInt($("#nuevoEstatus").val(), 10) || 0;
        var id = parseInt($("#idOrdenCompra").val(), 10) || 0;

        if (id <= 0 || !estatus) {
            alertify.alert('Aviso', 'Datos inválidos.');
            return;
        }

        if (window._ocEstatusActual === 3) {
            alertify.alert('Aviso', 'Esta orden está cancelada y no se puede modificar.');
            return;
        }

        if (window._ocEstatusActual === 2 && estatus === 1) {
            alertify.alert('Aviso', 'Una orden procesada no puede regresar a pendiente. Solo se puede cancelar.');
            return;
        }

        var idAlmacen = '';
        var almTxt = '';

        if (requiereAlmacen(estatus)) {
            idAlmacen = String($('#idAlmacenMovimiento').val() || '').trim();
            almTxt = getAlmacenSeleccionadoTexto();

            if (!idAlmacen) {
                alertify.alert('Movimiento de inventario', 'Selecciona el almacén para aplicar el movimiento.');
                return;
            }

            alertify.alert(
                'Movimiento de inventario',
                'El movimiento se aplicará en el almacén: <b>' + almTxt + '</b>.<br>Si deseas cambiarlo, selecciona otro almacén y vuelve a presionar “Realizar Cambio”.'
            );
        }

        alertify.confirm(
            'Confirmación',
            requiereAlmacen(estatus) ? ('¿Confirmas el cambio de estatus?<br><br><b>Almacén:</b> ' + almTxt) : '¿Confirmas el cambio de estatus?',
            function() {
                $.ajax({
                    url: 'ajax/compras/estatus.php',
                    method: 'POST',
                    data: {
                        id: id,
                        estatus: estatus,
                        id_almacen: idAlmacen
                    },
                    success: function(respuesta) {
                        var r = (respuesta || '').trim();

                        if (r === 'success') {
                            var table = $('#tablaOrdenesCompra').DataTable();
                            table.ajax.reload(function() {}, false);
                            $("#estatusModal").modal('hide');
                            alertify.alert('Listo', 'Estatus actualizado correctamente.');
                            return;
                        }

                        if (r === 'ya_cancelada') alertify.alert('Aviso', 'La orden ya está cancelada y no se puede modificar.');
                        else if (r === 'no_regresar') alertify.alert('Aviso', 'Una orden procesada no puede regresar a pendiente. Solo se puede cancelar.');
                        else if (r === 'almacen_requerido') alertify.alert('Movimiento de inventario', 'Debes seleccionar el almacén para aplicar el movimiento.');
                        else if (r === 'almacen_invalido') alertify.alert('Movimiento de inventario', 'El almacén seleccionado no es válido para esta empresa.');
                        else if (r === 'no_encontrada') alertify.alert('Aviso', 'Orden no encontrada.');
                        else if (r === 'sin_cambios') alertify.alert('Aviso', 'No hay cambios para aplicar.');
                        else alertify.alert('Aviso', 'No se pudo actualizar el estatus.');
                    },
                    error: function(xhr) {
                        alertify.alert('Error', 'Error de conexión: ' + xhr.statusText);
                    }
                });
            },
            function() {
                alertify.alert('Aviso', 'Cambio cancelado.');
            }
        );
    };

    // ==================================================
    // DATATABLE
    // ==================================================
    $(document).ready(function() {

        $('#tablaOrdenesCompra').dataTable({
            ajax: 'ajax/compras/tabla-compras.php',
            ordering: true,
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            initComplete: function(settings, json) {
                $('#custom_length').appendTo('body');
            },
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        var oTable = $('#tablaOrdenesCompra').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });
    });
</script>