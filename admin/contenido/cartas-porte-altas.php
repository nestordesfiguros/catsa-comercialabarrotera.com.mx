<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="cartas-porte">Cartas Porte</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nueva Carta Porte</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <!-- =========================
                     FORMULARIO CARTA PORTE
                ========================== -->
                <form id="formCartaPorte">

                    <!-- =========================
                        DATOS DE LA CARTA PORTE
                    ========================== -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header fw-bold">Datos de la Carta Porte</div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <!-- Vehículo -->
                                <div class="col-md-6 mb-3">
                                    <label for="vehiculo" class="form-label">Vehículo</label>
                                    <select id="vehiculo" name="vehiculo" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Operador -->
                                <div class="col-md-6 mb-3">
                                    <label for="operador" class="form-label">Operador</label>
                                    <select id="operador" name="operador" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <!-- Fecha de salida -->
                                <div class="col-md-2 mb-3">
                                    <label for="fecha_salida" class="form-label">Fecha Salida</label>
                                    <input type="date" id="fecha_salida" name="fecha_salida" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Hora de salida -->
                                <div class="col-md-2">
                                    <label for="hora_salida" class="form-label">Hora Salida</label>
                                    <input type="text" id="hora_salida" name="hora_salida" class="form-control" placeholder="HH:MM" required>
                                    <div class="invalid-feedback">Indica la hora de salida</div>
                                </div>

                                <!-- Origen (Almacén) -->
                                <div class="col-md-4 mb-3">
                                    <label for="origen" class="form-label">Origen (Almacén)</label>
                                    <select id="origen" name="origen" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Destino -->
                                <div class="col-md-4 mb-3">
                                    <label for="destino_general" class="form-label">Destino General / Ruta</label>
                                    <input type="text" id="destino_general" name="destino_general" class="form-control" placeholder="Ej. Ruta Bajío">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                        REMISIONES ASOCIADAS
                    ========================== -->
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

                    <!-- Botones finales -->
                    <div class="mb-4">
                        <button type="submit" class="btn btn-success" id="btnGuardarCartaPorte">
                            <i class="fa fa-save"></i> Guardar Carta Porte
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
                <table id="tablaRemisionesDisponibles" class="table table-bordered table-sm">
                    <thead>
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

        // ==========================
        // Cargar remisiones agregadas al iniciar
        // ==========================
        function cargarRemisiones() {
            $("#tbodyRemisiones").load("ajax/cartas-porte/tabla-remisiones-agregadas.php");
        }

        function recargarTablaRemisiones() {
            $('#tablaRemisionesDisponibles').DataTable().ajax.reload();
        }

        cargarRemisiones();

        // ==========================
        // DataTable de remisiones disponibles
        // ==========================
        var tablaRemisiones = $('#tablaRemisionesDisponibles').DataTable({
            ajax: 'ajax/cartas-porte/tabla-remisiones-disponibles.php',
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

        // Abrir modal de selección de remisiones
        $('#btnAgregarRemision').on('click', function() {
            $('#modalRemisiones').modal('show');
            recargarTablaRemisiones();
        });

        // ==========================
        // Agregar remisión desde el modal
        // ==========================
        $(document).on('click', '.btnAgregarRemision', function() {
            let id = $(this).data('id');
            $.ajax({
                url: 'ajax/cartas-porte/agregar-remision-temporal.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id
                },
                success: function(res) {
                    if (res.success) {
                        cargarRemisiones();
                        $('#modalRemisiones').modal('hide');
                        alertify.success('Remisión agregada');
                    } else {
                        alertify.error(res.message || 'No se pudo agregar la remisión.');
                    }
                }
            });
        });

        // ==========================
        // Quitar remisión con confirmación
        // ==========================
        $(document).on('click', '.btnQuitarRemision', function() {
            let id = $(this).data('id');
            alertify.confirm(
                'Confirmar eliminación',
                '¿Deseas quitar esta remisión?',
                function() {
                    $.ajax({
                        url: 'ajax/cartas-porte/quitar-remision-temporal.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id
                        },
                        success: function(res) {
                            if (res.success) {
                                cargarRemisiones();
                                alertify.success('Remisión eliminada');
                            } else {
                                alertify.error(res.message || 'Error al quitar remisión.');
                            }
                        }
                    });
                },
                function() {
                    alertify.message("Acción cancelada");
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
            const id = $(this).data('id');
            $.post('ajax/cartas-porte/ver-detalle-remision.php', {
                id
            }, function(res) {
                try {
                    const json = JSON.parse(res);
                    if (!json.success) {
                        alertify.error(json.html || 'Error al obtener detalle');
                        return;
                    }
                    $('#detalleRemisionCabecera').html(json.cabecera);
                    $('#detalleRemisionProductos').html(json.html);
                    $('#modalDetalleRemision').modal('show');
                } catch (e) {
                    alertify.error('Error inesperado');
                }
            });
        });

        // ==========================
        // Cargar selects dinámicos
        // ==========================
        function cargarVehiculos() {
            const fecha = $('#fecha_salida').val() || new Date().toISOString().split('T')[0];
            $.ajax({
                url: 'ajax/cartas-porte/lista-vehiculos.php',
                type: 'POST',
                data: {
                    fecha
                },
                success: function(res) {
                    $('#vehiculo').html(res);
                },
                error: function() {
                    alertify.error('Error al cargar vehículos');
                }
            });
        }

        // Cada vez que cambie la fecha, recargamos la lista de vehículos
        $('#fecha_salida').on('change', cargarVehiculos);

        function cargarOperadores() {
            $.ajax({
                url: 'ajax/cartas-porte/lista-operadores.php',
                type: 'POST',
                success: function(res) {
                    $('#operador').html(res);
                },
                error: function() {
                    alertify.error('Error al cargar operadores');
                }
            });
        }

        function cargarAlmacenes() {
            $.ajax({
                url: 'ajax/cartas-porte/lista-almacenes.php',
                type: 'POST',
                success: function(res) {
                    $('#origen').html(res);
                },
                error: function() {
                    alertify.error('Error al cargar almacenes');
                }
            });
        }

        cargarVehiculos();
        cargarOperadores();
        cargarAlmacenes();

        // ===========================
        // 1. Aplicar máscara de hora
        // ===========================
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
        // Validación con jQuery Validate
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
                }
            },
            messages: {
                vehiculo: "Selecciona un vehículo",
                operador: "Selecciona un operador",
                fecha_salida: "Indica la fecha de salida",
                hora_salida: "Indica la hora de salida",
                origen: "Selecciona un almacén"
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
                // Validar que haya al menos una remisión
                if ($("#tbodyRemisiones tr").length === 0 || $("#tbodyRemisiones tr td").first().text().includes("No hay remisiones")) {
                    alertify.alert('Aviso', 'No se ha agregado ninguna remisión', function() {
                        alertify.success('Ok');
                    });
                    return;
                }

                alertify.confirm("Confirmar", "¿Deseas guardar la Carta Porte?",
                    function() {
                        document.getElementById('spinner').style.display = 'block';

                        // Deshabilitar botón para evitar doble clic
                        $("#btnGuardarCartaPorte").prop("disabled", true);

                        const formData = new FormData(form);
                        const fechaHora = $('#fecha_salida').val() + ' ' + $('#hora_salida').val() + ':00';
                        formData.append('fecha_hora_salida', fechaHora);

                        $.ajax({
                            url: "ajax/cartas-porte/guardar-carta-porte.php",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json', // <-- Se recibe directamente como objeto
                            success: function(json) {
                                document.getElementById('spinner').style.display = 'none';
                                if (json.success) {
                                    window.location.href = "cartas-porte";
                                } else {
                                    alertify.error(json.message || "Error al guardar");
                                    $("#btnGuardarCartaPorte").prop("disabled", false);
                                }
                            },
                            error: function(xhr, status, error) {
                                document.getElementById('spinner').style.display = 'none';
                                alertify.error("Error de conexión con el servidor");
                                console.error('XHR:', xhr.responseText);
                                $("#btnGuardarCartaPorte").prop("disabled", false);
                            }
                        });
                    },
                    function() {
                        alertify.message("Acción cancelada");
                    }
                ).set('labels', {
                    ok: 'Guardar',
                    cancel: 'Cancelar'
                });
            }

        });

        // Forzar cierre de modales
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });

    });
</script>