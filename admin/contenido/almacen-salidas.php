<!-- contenido/almacen-salidas.php -->
<?php
$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Salidas de Almacén</li>
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
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos">
                                    <i class="fa-solid fa-cubes"></i> Productos
                                </a> &nbsp;

                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="lista-precios">
                                    <i class="fa-solid fa-file-invoice-dollar"></i> Lista de Precios
                                </a> &nbsp;

                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="almacen-salidas-altas">
                                    <i class="fa fa-plus"></i> Salida
                                </a> &nbsp;

                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="almacen-entradas">
                                    <i class="fa-solid fa-arrow-down-long"></i> Entradas
                                </a> &nbsp;

                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="ofertas">
                                    <i class="fas fa-tags"></i> Ofertas
                                </a>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-outline mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 col-12">
                            <div class="col-12 col-md-3">
                                <div class="form-outline mt-2 mt-md-0">
                                    <select id="filtroAlmacen" class="form-select">
                                        <option value="">-- Todos los almacenes --</option>
                                        <?php
                                        $rsAlmacenes = $clsConsulta->consultaGeneral("
                                            SELECT id, almacen
                                            FROM cat_almacenes
                                            WHERE estatus=1 AND id_empresa={$idEmpresa}
                                            ORDER BY almacen ASC
                                        ");
                                        if ($clsConsulta->numrows > 0 && is_array($rsAlmacenes)) {
                                            foreach ($rsAlmacenes as $alm) {
                                                echo '<option value="' . (int)$alm['id'] . '">' . htmlspecialchars($alm['almacen']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-body">
                        <table id="tablaSalidas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Almacén</th>
                                    <th class="text-center">Destino</th>
                                    <th class="text-center">Piezas</th>
                                    <th class="text-center">Detalle</th>
                                    <th class="text-center">PDF</th>
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
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleLabel">Detalle de salida</h5>
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
<div class="modal fade" id="estatusModal" tabindex="-1" aria-labelledby="estatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estatusLabel">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CerrarModalEstatus();"></button>
            </div>
            <div class="modal-body">
                <select id="nuevoEstatus" class="form-select">
                    <option value="1" class="text-warning">Pendiente</option>
                    <option value="2" class="text-success">Procesada</option>
                    <option value="3" class="text-danger">Cancelada</option>
                </select>
                <small class="text-muted d-block mt-2">
                    Nota: al cancelar una salida procesada, el inventario se regresa al almacén.
                </small>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="idSalida">
                <button type="button" class="btn btn-primary" onclick="fnCambiarEstatus();">Realizar Cambio</button>
            </div>
        </div>
    </div>
</div>

<script>
    function detalle(id, fecha, almacen, destino, piezas, estatus) {
        $("#detalleLabel").text("Salida #" + id);

        $("#cabDetalle").html(
            '<table class="table table-sm">' +
            '<tr>' +
            '<td><b>Folio:</b> ' + id + '</td>' +
            '<td class="text-center"><b>Fecha:</b> ' + fecha + '</td>' +
            '<td class="text-end"><b>Piezas:</b> ' + piezas + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td colspan="2"><b>Almacén:</b> ' + almacen + '</td>' +
            '<td class="text-end"><b>Estatus:</b> ' + estatus + '</td>' +
            '</tr>' +
            '<tr><td colspan="3"><b>Destino:</b> ' + destino + '</td></tr>' +
            '</table>'
        );

        $.ajax({
            url: 'ajax/almacen-salidas/detalle.php',
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

    function fnMostrarEstatus(id, estatusInt) {
        $("#estatusModal").modal('show');
        $("#idSalida").val(id);

        var select = document.getElementById("nuevoEstatus");
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].selected = (parseInt(select.options[i].value) === parseInt(estatusInt));
        }
    }

    function CerrarModalEstatus() {
        $("#estatusModal").modal('hide');
    }

    function fnCambiarEstatus() {
        const estatus = $("#nuevoEstatus").val();
        const id = $("#idSalida").val();

        $.ajax({
            url: 'ajax/almacen-salidas/estatus.php',
            method: 'POST',
            data: {
                id: id,
                estatus: estatus
            },
            success: function(respuesta) {
                const r = (respuesta || '').trim();
                if (r === 'success') {
                    $('#tablaSalidas').DataTable().ajax.reload(null, false);
                    $("#estatusModal").modal('hide');
                    alertify.success('Estatus actualizado');
                } else {
                    alertify.error(r || 'No se pudo cambiar el estatus');
                }
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    }

    $(document).ready(function() {
        var oTable = $('#tablaSalidas').DataTable({
            ajax: {
                url: 'ajax/almacen-salidas/tabla-salidas.php',
                type: 'POST',
                data: function(d) {
                    d.id_almacen = $('#filtroAlmacen').val();
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
                url: "assets/datatables/Spanish.json"
            },
            responsive: true
        });

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });
        $('#filtroAlmacen').change(function() {
            oTable.ajax.reload();
        });
    });
</script>