<!-- ***************
   pedidos.php 
 ***************-->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Pedidos</li>
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
                            <div class="col-12 col-md-12">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="pedidos-altas"><i class="fa fa-plus"></i>Pedidos </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="devoluciones"><i class="fas fa-undo"></i> Devoluciones </a>
                            </div>

                            <?php
                            $rsVendedores = $clsConsulta->consultaGeneral("SELECT id, CONCAT(nombre, ' ', apellido1, ' ', apellido2) as nombre FROM cat_vendedores WHERE estatus = 1");
                            ?>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-3">
                                    <select id="filtroEstatus" class="form-select">
                                        <option value="pendiente">Pendientes</option>
                                        <option value="procesado">Procesados</option>
                                        <option value="cancelado">Cancelados</option>
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filtroVendedor" class="form-select">
                                        <option value="">Todos los vendedores</option>
                                        <?php foreach ($rsVendedores as $vend): ?>
                                            <option value="<?= $vend['id'] ?>"><?= htmlspecialchars($vend['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 d-flex justify-content-end">
                                    <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                        <input type="text" id="search" class="form-control" />
                                        <label class="form-label" for="form12">Buscar</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <table id="tablaPedidos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">No. Pedido</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Nombre Comercial / Razón Social</th>
                                    <th class="text-center">Vendedor</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Detalle</th>
                                    <th class="text-center">Pdf</th>
                                    <th class="text-center">Estatus</th>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estatus -->
<div class="modal fade" id="estatusModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CerrarModalEstatus();"></button>
            </div>
            <div class="modal-body">
                <select id="nuevoEstatus" class="form-select">
                    <option value="pendiente" class="text-success">Pendiente</option>
                    <option value="procesado">Procesado (Genera Remisión)</option>
                    <option value="cancelado" class="text-danger">Cancelado</option>
                </select>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="idOrdenCompra">
                <button type="button" class="btn btn-primary" id="btnRealizarCambio" onclick="fnCambiarEstatus();">
                    Realizar Cambio
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Flag para bloquear cierres/acciones mientras se procesa
    let procesandoCambio = false;

    function editar(id) {
        location.href = "pedidos-editar/" + id;
    }

    function fnCambiarEstatus() {
        if (procesandoCambio) return; // evita doble submit

        const estatus = $("#nuevoEstatus").val(); // pendiente|procesado|cancelado
        const id = $("#idOrdenCompra").val();
        const btn = document.getElementById('btnRealizarCambio');

        procesandoCambio = true;
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Procesando...';
        }
        $('#spinner').show();

        $.ajax({
            url: 'ajax/pedidos/estatus.php',
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                estatus: estatus
            },
            success: function(resp) {
                if (!resp || !resp.success) {
                    alertify.error((resp && resp.mensaje) ? resp.mensaje : 'No se pudo actualizar');
                    return;
                }

                // ✅ si procesado => abrir remisión recién creada
                if (estatus === 'procesado' && resp.remision_id && parseInt(resp.remision_id, 10) > 0) {
                    $("#estatusModal").modal('hide');
                    alertify.success('Remisión generada. Abriendo...');
                    location.href = "remisiones-editar/" + parseInt(resp.remision_id, 10);
                    return;
                }

                // flujo normal
                var table = $('#tablaPedidos').DataTable();
                table.ajax.reload(null, false);

                $("#estatusModal").modal('hide');
                alertify.success(resp.mensaje || 'Estatus actualizado.');
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            },
            complete: function() {
                procesandoCambio = false;
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Realizar Cambio';
                }
                $('#spinner').hide();
            }
        });
    }

    function CerrarModalEstatus() {
        if (procesandoCambio) return;
        $("#estatusModal").modal('hide');
    }

    function fnMostrarEstatus(id, estatus) {
        if (procesandoCambio) return;
        $("#estatusModal").modal('show');
        $("#idOrdenCompra").val(id);

        const select = document.getElementById("nuevoEstatus");
        if (select) select.value = String(estatus);
    }

    function detalle(id, fecha, nombre, total) {
        $("#cabDetalle").html('<table class="table"><tr><td>Folio:' + id + '</td> <td class="text-center">Fecha: ' + fecha + '</td><td class="text-end">Total: $' + total + '</td></tr></table>');
        $("#exampleModalLabel").text(nombre);

        $.ajax({
            url: 'ajax/pedidos/detalle.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function(respuesta) {
                $("#detalle").html(respuesta);
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    }

    $(document).ready(function() {
        $('#tablaPedidos').dataTable({
            ajax: {
                url: 'ajax/pedidos/tabla-pedidos.php',
                data: function(d) {
                    d.estatus = $('#filtroEstatus').val();
                    d.id_vendedor = $('#filtroVendedor').val();
                }
            },
            ordering: true,
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        var oTable = $('#tablaPedidos').DataTable();

        $('#filtroEstatus, #filtroVendedor').change(function() {
            oTable.ajax.reload();
        });

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });
    });
</script>