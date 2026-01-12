<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="cartas-porte">Cartas Porte</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Carta Porte</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <form id="formCartaPorte">
                    <input type="hidden" name="id" id="idCartaPorte" value="<?= $cat ?>">

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header fw-bold">Datos de la Carta Porte</div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="vehiculo" class="form-label">Vehículo</label>
                                    <select id="vehiculo" name="vehiculo" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="operador" class="form-label">Operador</label>
                                    <select id="operador" name="operador" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-2 mb-3">
                                    <label for="fecha_salida" class="form-label">Fecha Salida</label>
                                    <input type="date" id="fecha_salida" name="fecha_salida" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="hora_salida" class="form-label">Hora Salida</label>
                                    <input type="text" id="hora_salida" name="hora_salida" class="form-control" placeholder="HH:MM" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="origen" class="form-label">Origen (Almacén)</label>
                                    <select id="origen" name="origen" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="destino" class="form-label">Destino General / Ruta</label>
                                    <input type="text" id="destino" name="destino" class="form-control" placeholder="Ej. Ruta Bajío">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remisiones -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Remisiones asociadas</span>
                            <button type="button" class="btn btn-info btn-sm" id="btnAgregarRemision">
                                <i class="fa-solid fa-plus"></i> Agregar Remisiones
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyRemisiones">
                                    <tr>
                                        <td colspan="5" class="text-center">No hay remisiones agregadas</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-end p-2 fw-bold" id="totalGeneral">Total General: $0.00</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-success" id="btnGuardarCartaPorte">
                            <i class="fa fa-save"></i> Actualizar Carta Porte
                        </button>
                        <a href="cartas-porte" class="btn btn-secondary">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     MODAL: SELECCIONAR REMISIONES
========================== -->
<div class="modal fade" id="modalRemisiones" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seleccionar Remisiones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table id="tablaRemisionesDisponibles" class="table table-bordered table-sm" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- =========================
     MODAL: DETALLE REMISION
========================== -->
<div class="modal fade" id="modalDetalleRemision" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle de Remisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="detalleRemisionCabecera" class="mb-3 p-2 border rounded bg-light fw-bold small"></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center" style="width:80px;">Cantidad</th>
                                <th class="text-end" style="width:120px;">Precio</th>
                                <th class="text-end" style="width:120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detalleRemisionProductos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {

        const idCarta = $('#idCartaPorte').val();

        // ==========================
        // Forzar cierre de modales con la X
        // ==========================
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });

        // ==========================
        // Cargar datos de la carta porte
        // ==========================
        function cargarCartaPorte() {
            $.ajax({
                url: 'ajax/cartas-porte-editar/obtener.php',
                type: 'POST',
                data: {
                    id: idCarta
                },
                dataType: 'json',
                success: function(json) {
                    if (!json.success) {
                        alertify.error(json.message || "Error al cargar datos");
                        return;
                    }

                    // Llenar selects y campos
                    $('#vehiculo').html(json.vehiculos).val(json.data.vehiculo);
                    $('#operador').html(json.operadores).val(json.data.operador);
                    $('#origen').html(json.origenes).val(json.data.origen);
                    $('#fecha_salida').val(json.data.fecha_salida);
                    $('#hora_salida').val(json.data.hora_salida);
                    $('#destino').val(json.data.destino);

                    // Remisiones iniciales
                    $('#tbodyRemisiones').html(json.htmlRemisiones);
                    $('#totalGeneral').text('Total General: $' + json.total_general);
                },
                error: function() {
                    alertify.error("Error inesperado al cargar datos");
                }
            });
        }

        // ==========================
        // Cargar remisiones actuales
        // ==========================
        function cargarRemisiones() {
            $.ajax({
                url: 'ajax/cartas-porte-editar/tabla-remisiones.php',
                type: 'POST',
                data: {
                    id: idCarta
                },
                success: function(html) {
                    $('#tbodyRemisiones').html(html);

                    // Recalcular total
                    let total = 0;
                    $('#tbodyRemisiones tr').each(function() {
                        const valor = $(this).find('td:eq(3)').text().replace('$', '').replace(',', '').trim();
                        if (!isNaN(valor) && valor !== '') total += parseFloat(valor);
                    });
                    $('#totalGeneral').text('Total General: $' + total.toFixed(2));
                },
                error: function() {
                    alertify.error('Error al cargar remisiones');
                }
            });
        }

        // ==========================
        // Abrir modal de remisiones disponibles
        // ==========================
        $('#btnAgregarRemision').on('click', function() {
            $('#modalRemisiones').modal('show');
            $('#tablaRemisionesDisponibles').DataTable().ajax.reload();
        });

        // ==========================
        // Agregar remisión (igual que altas)
        // ==========================
        $(document).on('click', '.btnAgregarRemision', function() {
            let idRemision = $(this).data('id');
            $.ajax({
                url: 'ajax/cartas-porte-editar/agregar-remision.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_carta: idCarta,
                    id_remision: idRemision
                },
                success: function(res) {
                    if (res.success) {
                        cargarRemisiones();
                        $('#modalRemisiones').modal('hide');
                        alertify.success('Remisión agregada');
                    } else {
                        alertify.error(res.message || 'No se pudo agregar la remisión.');
                    }
                },
                error: function() {
                    alertify.error('Error de conexión al agregar remisión');
                }
            });
        });

        // ==========================
        // QUITAR REMISION CON CONFIRMACION
        // ==========================
        $(document).on('click', '.btnQuitarRemision', function() {
            const idRemision = $(this).data('id');

            alertify.confirm(
                'Confirmar eliminación',
                '¿Deseas quitar esta remisión?',
                function() {
                    $.ajax({
                        url: 'ajax/cartas-porte-editar/quitar-remision.php',
                        type: 'POST',
                        data: {
                            id_carta: idCarta,
                            id_remision: idRemision
                        },
                        dataType: 'json',
                        success: function(json) {
                            if (json.success) {
                                alertify.success(json.message);

                                // 1️⃣ Recargar tabla de remisiones asociadas
                                cargarRemisiones();

                                // 2️⃣ Recargar modal para que reaparezca la remisión eliminada
                                if ($.fn.DataTable.isDataTable('#tablaRemisionesDisponibles')) {
                                    $('#tablaRemisionesDisponibles').DataTable().ajax.reload(null, false);
                                }

                            } else {
                                alertify.error(json.message || 'Error al quitar remisión');
                            }
                        },
                        error: function() {
                            alertify.error('Error de conexión al quitar remisión');
                        }
                    });
                },
                function() {
                    alertify.message('Acción cancelada');
                }
            ).set('labels', {
                ok: 'Eliminar',
                cancel: 'Cancelar'
            });
        });


        // ==========================
        // Ver detalle de remisión
        // ==========================
        $(document).on('click', '.btnDetalleRemision', function() {
            const idRemision = $(this).data('id');
            $.ajax({
                url: 'ajax/cartas-porte-editar/ver-detalle-remision.php',
                type: 'POST',
                data: {
                    id: idRemision
                },
                dataType: 'json',
                success: function(json) {
                    if (!json.success) {
                        alertify.error(json.html || 'Error al obtener detalle');
                        return;
                    }
                    $('#detalleRemisionCabecera').html(json.cabecera);
                    $('#detalleRemisionProductos').html(json.html);
                    $('#modalDetalleRemision').modal('show');
                },
                error: function() {
                    alertify.error('Error inesperado al mostrar detalle');
                }
            });
        });

        // ==========================
        // DataTable de remisiones disponibles
        // ==========================
        var tablaRemisionesDisponibles = $('#tablaRemisionesDisponibles').DataTable({
            ajax: 'ajax/cartas-porte-editar/tabla-remisiones-disponibles.php',
            paging: true,
            responsive: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columns: [{
                    title: "Folio"
                },
                {
                    title: "Cliente"
                },
                {
                    title: "Fecha"
                },
                {
                    title: "Total"
                },
                {
                    title: "Acción",
                    orderable: false
                }
            ]
        });

        // ==========================
        // Máscara para hora
        // ==========================
        var horaMask = IMask(document.getElementById('hora_salida'), {
            mask: 'HH:MM',
            blocks: {
                HH: {
                    mask: IMask.MaskedRange,
                    from: 0,
                    to: 23
                },
                MM: {
                    mask: IMask.MaskedRange,
                    from: 0,
                    to: 59
                }
            }
        });

        // ==========================
        // Validación y guardado
        // ==========================
        $('#formCartaPorte').validate({
            rules: {
                vehiculo: {
                    required: true
                },
                operador: {
                    required: true
                },
                fecha_salida: {
                    required: true,
                    date: true
                },
                hora_salida: {
                    required: true
                },
                origen: {
                    required: true
                },
                destino: {
                    required: true
                }
            },
            messages: {
                vehiculo: "Selecciona un vehículo",
                operador: "Selecciona un operador",
                fecha_salida: "Indica la fecha de salida",
                hora_salida: "Indica la hora de salida",
                origen: "Selecciona un almacén",
                destino: "Indica el destino o ruta"
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function(form) {
                alertify.confirm("Confirmar", "¿Deseas actualizar la Carta Porte?",
                    function() {
                        document.getElementById('spinner').style.display = 'block';
                        $("#btnGuardarCartaPorte").prop("disabled", true);

                        const formData = new FormData(form);

                        $.ajax({
                            url: 'ajax/cartas-porte-editar/guardar.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(json) {
                                document.getElementById('spinner').style.display = 'none';
                                if (json.success) {
                                    alertify.success(json.message);
                                    window.location.href = "cartas-porte";
                                } else {
                                    alertify.error(json.message || "Error al actualizar");
                                    $("#btnGuardarCartaPorte").prop("disabled", false);
                                }
                            },
                            error: function() {
                                document.getElementById('spinner').style.display = 'none';
                                alertify.error("Error de conexión con el servidor");
                                $("#btnGuardarCartaPorte").prop("disabled", false);
                            }
                        });
                    },
                    function() {
                        alertify.message("Acción cancelada");
                    }
                ).set('labels', {
                    ok: 'Actualizar',
                    cancel: 'Cancelar'
                });
            }
        });

        // Cargas iniciales
        cargarCartaPorte();
        cargarRemisiones();

    });
</script>