<?php
$idEmpresa = $_SESSION['id_empresa'] ?? 0;

?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cartas Porte</li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Cartas Porte</h5>
                            <a href="cartas-porte-altas" class="btn btn-primary">Nueva Carta Porte</a>
                        </div>

                        <table id="tablaCartasPorte" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Fecha Salida</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Estatus</th>
                                    <th class="text-center">Acciones</th>
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

<!-- Modal Cambiar Estatus -->
<div class="modal fade" id="modalConfirmarCambio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Cambiar Estatus de Carta Porte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idCartaEstatus">
                <label for="nuevoEstatus" class="form-label">Nuevo Estatus</label>
                <select id="nuevoEstatus" class="form-select">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_transito">En tránsito</option>
                    <option value="finalizada">Finalizada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarEstatus">Modificar</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        const tabla = $('#tablaCartasPorte').DataTable({
            ajax: {
                url: 'ajax/cartas-porte/tabla-cartas-porte.php',
                dataSrc: 'data'
            },
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        // ==============================
        // Abrir modal al hacer click en el badge
        // ==============================
        $(document).on('click', '.btnCambiarEstatus', function() {
            const id = $(this).data('id');
            const estatus = $(this).data('estatus');

            $('#idCartaEstatus').val(id);
            $('#nuevoEstatus').val(estatus);
            $('#modalConfirmarCambio').modal('show');
        });

        // ==============================
        // Guardar cambio de estatus
        // ==============================
        $('#btnGuardarEstatus').on('click', function() {
            const id = $('#idCartaEstatus').val();
            const nuevoEstatus = $('#nuevoEstatus').val();

            $.post('ajax/cartas-porte/cambiar-estatus.php', {
                id,
                estatus: nuevoEstatus
            }, function(res) {
                try {
                    const json = JSON.parse(res);
                    if (json.success) {
                        alertify.success(json.message);
                        $('#modalConfirmarCambio').modal('hide');
                        $('#tablaCartasPorte').DataTable().ajax.reload(null, false);
                    } else {
                        alertify.error(json.message || 'No se pudo cambiar el estatus');
                    }
                } catch (e) {
                    alertify.error('Error inesperado en la respuesta');
                }
            });
        });

        // Forzar cierre de modales
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });
    });
</script>