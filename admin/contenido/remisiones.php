<?php
// remisiones.php
$semanaActual = date('W');

?>

<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">remisiones </li>
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
                            <div class="col-12">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                                <a type="button" class="btn btn-info  btn-fixed mt-2 mt-md-0" href="remisiones-altas" title="Agregar Remisión"><i class="fas fa-plus"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="devoluciones"><i class="fas fa-undo"></i> Devoluciones </a>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label for="filtroCliente" class="form-label mb-0">Cliente</label>
                                    <select id="filtroCliente" class="form-select"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroVendedor" class="form-label mb-0">Vendedor</label>
                                    <select id="filtroVendedor" class="form-select"></select>
                                </div>
                                <div class="col-md-1">
                                    <label for="filtroEstatus" class="form-label mb-0">Estatus</label>
                                    <select id="filtroEstatus" class="form-select">
                                        <option value="pendiente" selected>Pendiente</option>
                                        <option value="procesada">Procesada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">Limpiar</button>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-0">Buscar</label>
                                    <input type="text" id="search" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaRemisiones" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Tipo Venta</th>
                                    <th class="text-center">Monto</th>
                                    <th class="text-center">Detalle</th>
                                    <th class="text-center">Imprimir</th>
                                    <th class="text-center">Estatus</th>
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


<!-- Modal -->
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación Estatus -->
<div class="modal fade" id="estatusModal" tabindex="-1" aria-labelledby="estatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estatusModalLabel">Cambiar estatus de remisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="remisionId">

                <div id="grupoAlmacen" class="mb-3">
                    <label for="id_almacen" class="form-label">Selecciona el almacén para surtir:</label>
                    <select id="id_almacen" class="form-select">
                        <option value="">Seleccionar almacén</option>
                        <!-- opciones dinámicas -->
                    </select>
                    <small id="errorAlmacen" class="text-danger mt-1 d-block" style="font-size: 0.875em; display: none;">
                        Debes seleccionar un almacén para procesar.
                    </small>
                </div>



                <div class="form-group">
                    <label for="nuevoEstatus">Selecciona el nuevo estatus:</label>
                    <select class="form-select" id="nuevoEstatus">
                        <option value="procesada">Procesada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="comentario">Comentario (opcional):</label>
                    <textarea class="form-control" id="comentario" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmarCambioEstatus" class="btn btn-primary">CONFIRMAR</button>
            </div>
        </div>
    </div>
</div>

<!-- CDN de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function cambiarEstatus(id, estatusActual) {
        if (estatusActual !== 'pendiente') {
            alertify.warning('No se puede cambiar el estatus de una remisión ' + estatusActual);
            return;
        }

        $('#remisionId').val(id);
        $('#nuevoEstatus').val('procesada');
        $('#comentario').val('');
        $('#errorAlmacen').hide().text('');
        $('#grupoAlmacen').show(); // ✅ mostrar el campo almacén al abrir
        cargarAlmacenes(); // ✅ cargar opciones en el select
        $('#estatusModal').modal('show');
    }


    // Modifica la función que genera la tabla para incluir el estatus clickeable
    function formatearEstatus(estatus) {
        const clases = {
            'pendiente': 'badge bg-warning',
            'procesada': 'badge bg-success',
            'cancelada': 'badge bg-danger'
        };

        const texto = estatus.charAt(0).toUpperCase() + estatus.slice(1);
        const clase = clases[estatus] || 'badge bg-secondary';

        return `<span class="${clase}" style="cursor: pointer;" onclick="cambiarEstatus(${id}, '${estatus}')">${texto}</span>`;
    }

    function limpiarFiltros() {
        $('#filtroCliente').val(null).trigger('change');
        $('#filtroVendedor').val(null).trigger('change');
        $('#filtroEstatus').val('pendiente');
        $('#tablaRemisiones').DataTable().ajax.reload();
    }

    function detalle(id, fecha, nombre, total) {
        //console.log('Folio: '+id+' Fecha: '+fecha+' nombre: '+nombre);
        $("#cabDetalle").html('<table class="table"><tr><td>Folio:' + id + '</td> <td class="text-center">Fecha: ' + fecha + '</td><td class="text-end">Total: $' + total + '</td></tr></table>');
        $("#exampleModalLabel").text(nombre);
        $.ajax({
            url: 'ajax/remisiones/detalle.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function(respuesta) {
                //  console.log(respuesta);
                $("#detalle").html(respuesta);
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    };

    function editar(id) {
        location.href = "remisiones-editar/" + id;
    }

    function fnImprimir(id) {
        location.href = ''
    }

    function cargarAlmacenes() {
        $.post('ajax/remisiones/buscar-almacenes.php', {}, function(data) {
            const $select = $('#id_almacen');
            $select.empty().append('<option value="">Seleccionar almacén</option>');
            data.forEach(item => {
                $select.append(`<option value="${item.id}">${item.text}</option>`);
            });
        }, 'json');
    }


    // # # # # # # # # # # #   Document Ready   # # # #
    $(document).ready(function() {

        $(document).on('click', '#confirmarCambioEstatus', function() {
            const idRemision = $('#remisionId').val();
            const nuevoEstatus = $('#nuevoEstatus').val();
            const comentario = $('#comentario').val();
            const idAlmacen = $('#id_almacen').val();

            $('#errorAlmacen').hide().text('');

            if (nuevoEstatus === 'procesada' && (!idAlmacen || idAlmacen === '')) {
                $('#errorAlmacen').text('Debes seleccionar un almacén para procesar.').show();
                return;
            }

            alertify.confirm('¿Estás seguro de cambiar el estatus de esta remisión?', function() {
                // ✅ Solo si confirma
                $.ajax({
                    url: 'ajax/remisiones/cambiar-estatus.php',
                    method: 'POST',
                    data: {
                        id: idRemision,
                        estatus: nuevoEstatus,
                        comentario: comentario,
                        id_almacen: idAlmacen
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#spinner').show();
                    },
                    success: function(resp) {
                        console.log(resp);
                        $('#spinner').hide();
                        $('#estatusModal').modal('hide');
                        $('#tablaRemisiones').DataTable().ajax.reload();

                        if (resp.success) {
                            alertify.success(resp.message || 'Remisión actualizada.');
                        } else {
                            alertify.error(resp.message || 'No se pudo actualizar el estatus.');
                        }
                    },
                    error: function() {
                        $('#spinner').hide();
                        alertify.error('Error en la solicitud al servidor.');
                    }
                });
            }, function() {
                alertify.message('Acción cancelada');
            });
        });


        $('#estatusModal').on('hidden.bs.modal', function() {
            $('#remisionId').val('');
            $('#nuevoEstatus').val('procesada');
            $('#comentario').val('');
            $('#id_almacen').val('');
            $('#id_almacen').empty().append('<option value="">Seleccionar almacén</option>');
            $('#grupoAlmacen').hide();
            $('#errorAlmacen').hide().text('');
        });


        const tabla = $('#tablaRemisiones').DataTable({
            ajax: {
                url: 'ajax/remisiones/tabla-remisiones.php',
                type: 'POST',
                data: function(d) {
                    d.estatus = $('#filtroEstatus').val();
                    d.id_cliente = $('#filtroCliente').val();
                    d.id_vendedor = $('#filtroVendedor').val();
                }
            },
            responsive: true,
            ordering: true,
            fixedHeader: true,
            order: [
                [0, 'desc']
            ],
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            initComplete: function() {
                $('#custom_length').appendTo('body');
            },
            language: {
                url: "assets/datatables/Spanish.json",
                Search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }
        });

        // Buscador general
        $('#search').keyup(function() {
            tabla.search($(this).val()).draw();
        });

        // Filtros individuales
        $('#filtroEstatus, #filtroCliente, #filtroVendedor').on('change', function() {
            tabla.ajax.reload();
        });

        // Activar Select2 para cliente
        $('#filtroCliente').select2({
            placeholder: 'Seleccionar cliente',
            allowClear: true,
            ajax: {
                url: 'ajax/remisiones/buscar-clientes.php',
                method: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                }
            }
        });

        // Activar Select2 para vendedor
        $('#filtroVendedor').select2({
            placeholder: 'Seleccionar vendedor',
            allowClear: true,
            ajax: {
                url: 'ajax/remisiones/buscar-vendedores.php',
                method: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                }
            }
        });

        $('#nuevoEstatus').on('change', function() {
            const valor = $(this).val();
            if (valor === 'procesada') {
                $('#grupoAlmacen').show();
                cargarAlmacenes();
            } else {
                $('#grupoAlmacen').hide();
                $('#id_almacen').empty().append('<option value="">Seleccionar almacén</option>');
                $('#errorAlmacen').hide().text('');
            }
        });



    });
</script>