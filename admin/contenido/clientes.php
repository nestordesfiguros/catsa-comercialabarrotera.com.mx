<!-- admin/contenido/clientes.php  -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Clientes</li>
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
                            <div class="col-12 col-md-10">
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="clientes-altas"><i class="fa fa-plus"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="devoluciones"><i class="fas fa-undo"></i> Devoluciones </a>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="tablaClientes" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th class="text-center">Número</th>
                                    <th class="text-center">Razón Social</th>
                                    <th class="text-center">Nombre Comercial</th>
                                    <th class="text-center">Lista de precios</th>
                                    <th class="text-center">Contacto</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Ubicación</th>
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

<!-- Modal Cambiar Estatus -->
<div class="modal fade" id="modalEdditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Cambiar estatus</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarEstatus();"></button>
            </div>

            <div class="modal-body">
                <h6 class="mb-0"><span id="estatusActual"></span></h6>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="cerrarEstatus();">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnCambiarEstatus" onclick="modificaEstatus();">
                    Confirmar
                </button>

                <input type="hidden" id="estatusnuevo">
                <input type="hidden" id="idCliente">
            </div>
        </div>
    </div>
</div>

<!-- Overlay / bloqueo de pantalla -->
<div id="pageOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
    style="z-index: 2000; background: rgba(0,0,0,.35);">
    <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
        <div class="mt-3 fw-semibold" id="overlayText">Procesando...</div>
    </div>
</div>

<script type="text/javascript">
    let __isSubmitting = false;

    function bloquearPantalla(btn, texto) {
        __isSubmitting = true;
        $('#overlayText').text(texto || 'Procesando...');
        $('#pageOverlay').removeClass('d-none');

        if (btn && btn.length) {
            if (!btn.data('original-html')) btn.data('original-html', btn.html());
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (texto || 'Procesando...'));
        }
    }

    function desbloquearPantalla(btn) {
        __isSubmitting = false;
        $('#pageOverlay').addClass('d-none');

        if (btn && btn.length) {
            const original = btn.data('original-html');
            if (original) btn.html(original);
            btn.prop('disabled', false);
        }
    }

    function editar(id) {
        location.href = "clientes-editar/" + id;
    }

    function cerrarEstatus() {
        $("#modalEdditar").modal('hide');
    }

    function valorEstatus(id, estatus) {
        $("#modalEdditar").modal('show');
        $("#idCliente").val(id);

        if (parseInt(estatus, 10) === 1) {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-danger">Inactivo</b>?');
            $("#estatusnuevo").val(0);
        } else {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-success">Activo</b>?');
            $("#estatusnuevo").val(1);
        }
    }

    function modificaEstatus() {
        if (__isSubmitting) return;

        const idCliente = $("#idCliente").val();
        const estatusnuevo = $("#estatusnuevo").val();
        const btn = $("#btnCambiarEstatus");

        bloquearPantalla(btn, 'Actualizando...');

        $.ajax({
            url: "ajax/clientes/modifica-estatus.php",
            type: "POST",
            data: {
                idCliente,
                estatusnuevo
            },
            success: function(resp) {
                if (resp == 1) {
                    desbloquearPantalla(btn);
                    alert("Fallo el server");
                    return;
                }

                // refresca tabla
                const table = $('#tablaClientes').DataTable();
                table.ajax.reload(null, false);

                $("#modalEdditar").modal('hide');
                desbloquearPantalla(btn);
            },
            error: function(xhr, status, error) {
                desbloquearPantalla(btn);
                alert("Error de conexión: " + error);
            }
        });
    }

    $(document).ready(function() {

        const table = $('#tablaClientes').DataTable({
            ajax: {
                url: 'ajax/clientes/tabla-clientes.php',
                type: 'POST',
                dataSrc: 'data'
            },
            ordering: true,
            pageLength: 10,
            responsive: true,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columnDefs: [{
                targets: [5, 6, 7],
                orderable: false
            }]
        });

        $('#search').on('keyup', function() {
            table.search(this.value).draw();
        });

    });
</script>