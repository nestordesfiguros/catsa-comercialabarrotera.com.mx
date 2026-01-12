<?php
// facturas.php
$semanaActual = date('W');

?>

<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Facturas </li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-10">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                                <button onclick="FnFacturasAltas()" type="button" class="btn btn-info  btn-fixed mt-2 mt-md-0" title="Agregar factura"><i class="fas fa-plus"></i> Facturas </button> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="devoluciones"><i class="fas fa-undo"></i> Devoluciones </a>
                            </div>
                            <div class="col-2">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaFacturas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Ver</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Enviar CFDI por correo -->
<div class="modal fade" id="modalEnviarCFDI" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-envelope me-2"></i>Enviar CFDI por correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEnviarCFDI" autocomplete="off">
                <div class="modal-body">
                    <div class="mb-2 small text-muted">
                        Si dejas el campo vacío, se utilizará el correo registrado del cliente.
                    </div>
                    <div class="form-group">
                        <label for="inputCorreoCFDI" class="form-label">Correo destino (opcional)</label>
                        <input type="email" class="form-control" id="inputCorreoCFDI" placeholder="cliente@dominio.com">
                    </div>
                    <input type="hidden" id="enviarIdFactura" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnEnviarCFDIConfirm">
                        <i class="fa fa-paper-plane me-1"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    function FnFacturasAltas() {
        $.ajax({
                url: "ajax/facturas/obtener-numero-factura.php",
                type: "POST",
                dataType: "json"
            })
            .done(function(data) {
                // Esperamos un arreglo con al menos un objeto que tenga fact_serie y fact_no
                if (!data || !Array.isArray(data) || !data[0] || !data[0].fact_serie || !data[0].fact_no) {
                    console.error("Respuesta inesperada:", data);
                    alertify.error("No se pudo obtener el folio. Intenta de nuevo.");
                    return;
                }
                var fact_serie = data[0].fact_serie;
                var fact_no = data[0].fact_no;
                // Redirige al alta con serie y folio
                location.href = "facturas-altas/" + fact_serie + "/" + fact_no;
            })
            .fail(function(xhr, status, err) {
                console.error("Error obtener-numero-factura:", status, err, xhr?.responseText);
                alertify.error("Error al obtener el folio de la factura.");
            });
    }

    // ===== Helpers =====
    function parseJSONSeguro(data) {
        try {
            if (typeof data !== 'string') return data;
            const t = data.trim();
            const m = t.match(/\{[\s\S]*\}$/);
            return JSON.parse(m ? m[0] : t);
        } catch (e) {
            console.error('JSON inválido:', data, e);
            return null;
        }
    }

    function confirmUI(titulo, msg, onOK, onCancel) {
        if (window.alertify && alertify.confirm) {
            alertify.confirm(titulo, msg, onOK, onCancel || function() {}).set('labels', {
                ok: 'Sí',
                cancel: 'No'
            });
        } else {
            if (confirm(`[${titulo}] ${msg}`)) onOK();
            else if (onCancel) onCancel();
        }
    }

    function ok(msg) {
        window.alertify ? alertify.success(msg) : alert(msg);
    }

    function err(msg) {
        window.alertify ? alertify.error(msg) : alert(msg);
    }

    $(function() {
        // ===== DataTable =====
        $('#tablaFacturas').DataTable({
            ajax: 'ajax/facturas/tabla-facturas.php',
            ordering: true,
            fixedHeader: true,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            initComplete: function(settings, json) {
                $('#custom_length').appendTo('body');
            },
            language: {
                url: "assets/datatables/Spanish.json",
                Search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        var oTable = $('#tablaFacturas').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });


        // ===== Timbrar =====
        $(document).on('click', '.btn-timbrar', function() {
            var id = $(this).data('id');
            if (!id) {
                err('ID inválido');
                return;
            }
            confirmUI('Timbrar factura', '¿Deseas timbrar esta factura?',
                function() {
                    $.post('ajax/facturas/timbrar.php', {
                        id: id
                    }, function(resp) {
                        var r = parseJSONSeguro(resp) || {};
                        if (r.success) {
                            ok(r.msg || 'Factura timbrada');
                            dt.ajax.reload(null, false); // recarga sin mover de página
                        } else {
                            err(r.msg || 'No se pudo timbrar');
                        }
                    }).fail(function() {
                        err('Fallo de red/servidor');
                    });
                }
            );
        });

        // ===== Re-timbrar (reintento / regenerar) =====
        $(document).on('click', '.btn-retimbrar', function() {
            var id = $(this).data('id');
            if (!id) {
                err('ID inválido');
                return;
            }
            confirmUI('Re-timbrar / Regenerar', '¿Deseas reintentar el timbrado o regenerar XML/PDF?',
                function() {
                    $.post('ajax/facturas/retimbrar.php', {
                        id: id
                    }, function(resp) {
                        var r = parseJSONSeguro(resp) || {};
                        if (r.success) {
                            ok(r.msg || 'Proceso completado');
                            dt.ajax.reload(null, false);
                        } else {
                            err(r.msg || 'No fue posible completar la operación');
                        }
                    }).fail(function() {
                        err('Fallo de red/servidor');
                    });
                }
            );
        });

        // ===== Cancelar CFDI =====
        $(document).on('click', '.btn-cancelar-cfdi', function() {
            const id = $(this).data('id');
            const motivo = prompt('Motivo (01/02/03/04):', '02');
            if (!motivo) return;
            let uuid_sustitucion = '';
            if (motivo === '01') {
                uuid_sustitucion = prompt('UUID de sustitución:', '');
                if (!uuid_sustitucion) return;
            }
            $.post('ajax/facturas/cancelar.php', {
                id,
                motivo,
                uuid_sustitucion
            }, function(r) {
                if (r && r.success) {
                    alertify.success(r.msg || 'Listo');
                    // si ya tienes una instancia de DataTable, recarga:
                    var dt = $('#tablaFacturas').DataTable();
                    dt.ajax.reload(null, false);
                } else {
                    alertify.error(r && r.msg ? r.msg : 'Error al cancelar');
                }
            }, 'json').fail(() => alertify.error('Fallo de red/servidor'));
        });

        // ===== Enviar CFDI por correo =====
        // ===== Enviar CFDI por correo (con modal) =====
        let _modalEnviarCFDI = null;

        $(document).on('click', '.btn-enviar-cfdi', function() {
            const id = $(this).data('id');
            if (!id) {
                alertify.error('ID inválido');
                return;
            }

            $('#enviarIdFactura').val(id);
            $('#inputCorreoCFDI').val('');

            const el = document.getElementById('modalEnviarCFDI');
            _modalEnviarCFDI = bootstrap.Modal.getOrCreateInstance(el);
            _modalEnviarCFDI.show();

            setTimeout(() => $('#inputCorreoCFDI').trigger('focus'), 150);
        });

        // Envío del formulario del modal
        $('#formEnviarCFDI').on('submit', function(e) {
            e.preventDefault();

            const id = $('#enviarIdFactura').val();
            const correo = ($('#inputCorreoCFDI').val() || '').trim();

            // Validación simple de email (si se capturó)
            if (correo !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                alertify.error('Correo inválido');
                $('#inputCorreoCFDI').focus();
                return;
            }

            const $btn = $('#btnEnviarCFDIConfirm');
            $btn.prop('disabled', true);

            $.post('ajax/facturas/enviar.php', {
                    id,
                    correo
                }, function(r) {
                    if (r && r.success) {
                        alertify.success(r.msg || 'Correo enviado');
                    } else {
                        alertify.error((r && r.msg) ? r.msg : 'No fue posible enviar el correo');
                    }
                }, 'json')
                .fail(function() {
                    alertify.error('Fallo de red/servidor');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                    if (_modalEnviarCFDI) _modalEnviarCFDI.hide();
                });
        });



    });
</script>