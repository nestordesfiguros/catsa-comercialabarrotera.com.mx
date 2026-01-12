<?php
$idEmpresa = $_SESSION['id_empresa'] ?? 0;
?>


<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Vehiculos</li>
        </ol>
    </nav>
</div>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Catálogo de Vehículos</h5>
        <button type="button" class="btn btn-primary" id="btnNuevoVehiculo">Nuevo Vehículo</button>
    </div>

    <table id="tablaVehiculos" class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th>Nombre / N° Económico</th>
                <th>Placas</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Capacidad (kg)</th>
                <th>Tipo de Unidad</th>
                <th>Estatus</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-labelledby="modalVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalVehiculoLabel">Nuevo Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!--  Form solo envuelve body y el botón Guardar -->
            <form id="formVehiculo" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="idVehiculo" name="id">

                    <div class="mb-3">
                        <label for="nombre_vehiculo" class="form-label">Nombre o N° Económico</label>
                        <input type="text" class="form-control" id="nombre_vehiculo" name="nombre_vehiculo" required>
                    </div>

                    <div class="mb-3">
                        <label for="placas" class="form-label">Placas</label>
                        <input type="text" class="form-control" id="placas" name="placas" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2099" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="capacidad_kg" class="form-label">Capacidad (kg)</label>
                            <input type="number" class="form-control" id="capacidad_kg" name="capacidad_kg" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tipo_unidad" class="form-label">Tipo de Unidad</label>
                            <select class="form-select" id="tipo_unidad" name="tipo_unidad" required>
                                <option value="">Selecciona...</option>
                                <option value="Camioneta">Camioneta</option>
                                <option value="Camión">Camión</option>
                                <option value="Moto">Moto</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold mb-3">Datos para Carta Porte</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="config_vehicular" class="form-label">Config. Vehicular</label>
                            <select class="form-select" id="config_vehicular" name="config_vehicular">
                                <option value="">Selecciona...</option>
                                <option value="C2">C2 - Camión unitario 2 ejes</option>
                                <option value="C3">C3 - Camión unitario 3 ejes</option>
                                <option value="T2S1">T2S1 - Tracto 2 ejes / Semirremolque 1 eje</option>
                                <option value="T2S2">T2S2 - Tracto 2 ejes / Semirremolque 2 ejes</option>
                                <option value="T3S1">T3S1 - Tracto 3 ejes / Semirremolque 1 eje</option>
                                <option value="T3S2">T3S2 - Tracto 3 ejes / Semirremolque 2 ejes</option>
                                <option value="T3S3">T3S3 - Tracto 3 ejes / Semirremolque 3 ejes</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tipo_permiso_sct" class="form-label">Tipo Permiso SCT</label>
                            <select class="form-select" id="tipo_permiso_sct" name="tipo_permiso_sct">
                                <option value="">Selecciona...</option>
                                <option value="TPAF01">TPAF01 - Autotransporte Federal de Carga</option>
                                <option value="TPAF02">TPAF02 - Transporte Privado</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="numero_permiso_sct" class="form-label">Número Permiso SCT</label>
                            <input type="text" class="form-control" id="numero_permiso_sct" name="numero_permiso_sct">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="aseguradora" class="form-label">Aseguradora</label>
                            <input type="text" class="form-control" id="aseguradora" name="aseguradora">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="num_poliza" class="form-label">Número de Póliza</label>
                            <input type="text" class="form-control" id="num_poliza" name="num_poliza">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="remolque1_tipo" class="form-label">Remolque 1 Tipo</label>
                            <select class="form-select" id="remolque1_tipo" name="remolque1_tipo">
                                <option value="">Selecciona...</option>
                                <option value="CTR001">CTR001 - Caja seca</option>
                                <option value="CTR002">CTR002 - Plataforma</option>
                                <option value="CTR003">CTR003 - Refrigerado</option>
                                <option value="CTR004">CTR004 - Tanque</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="remolque1_placa" class="form-label">Remolque 1 Placa</label>
                            <input type="text" class="form-control" id="remolque1_placa" name="remolque1_placa">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="remolque2_tipo" class="form-label">Remolque 2 Tipo</label>
                            <select class="form-select" id="remolque2_tipo" name="remolque2_tipo">
                                <option value="">Selecciona...</option>
                                <option value="CTR001">CTR001 - Caja seca</option>
                                <option value="CTR002">CTR002 - Plataforma</option>
                                <option value="CTR003">CTR003 - Refrigerado</option>
                                <option value="CTR004">CTR004 - Tanque</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="remolque2_placa" class="form-label">Remolque 2 Placa</label>
                            <input type="text" class="form-control" id="remolque2_placa" name="remolque2_placa">
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                <button type="submit" form="formVehiculo" class="btn btn-success">GUARDAR</button>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const tabla = $('#tablaVehiculos').DataTable({
            ajax: {
                url: 'ajax/vehiculos/tabla-vehiculos.php',
                dataSrc: 'data'
            },
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        function limpiarFormularioVehiculo() {
            $('#formVehiculo')[0].reset();
            $('#idVehiculo').val('');
            $('#formVehiculo .is-valid, #formVehiculo .is-invalid').removeClass('is-valid is-invalid');
        }

        $('#modalVehiculo').on('hidden.bs.modal', limpiarFormularioVehiculo);

        // ==============================
        // Nuevo Vehículo
        // ==============================
        $('#btnNuevoVehiculo').click(function() {
            limpiarFormularioVehiculo();
            $('#modalVehiculoLabel').text('Nuevo Vehículo');
            $('#modalVehiculo').modal('show');
        });

        // ==============================
        // Editar Vehículo
        // ==============================
        $(document).on('click', '.btnEditarVehiculo', function() {
            const id = $(this).data('id');
            limpiarFormularioVehiculo();
            $('#idVehiculo').val(id);
            $('#modalVehiculoLabel').text('Editar Vehículo');

            $.post('ajax/vehiculos/obtener-vehiculo.php', {
                id
            }, function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#nombre_vehiculo').val(d.nombre_vehiculo);
                    $('#placas').val(d.placas);
                    $('#marca').val(d.marca);
                    $('#modelo').val(d.modelo);
                    $('#anio').val(d.anio);
                    $('#capacidad_kg').val(d.capacidad_kg);
                    $('#tipo_unidad').val(d.tipo_unidad);
                    $('#config_vehicular').val(d.config_vehicular);
                    $('#tipo_permiso_sct').val(d.tipo_permiso_sct);
                    $('#numero_permiso_sct').val(d.numero_permiso_sct);
                    $('#aseguradora').val(d.aseguradora);
                    $('#num_poliza').val(d.num_poliza);
                    $('#remolque1_tipo').val(d.remolque1_tipo);
                    $('#remolque1_placa').val(d.remolque1_placa);
                    $('#remolque2_tipo').val(d.remolque2_tipo);
                    $('#remolque2_placa').val(d.remolque2_placa);

                    $('#modalVehiculo').modal('show');
                } else {
                    alertify.error('Vehículo no encontrado.');
                }
            }, 'json');
        });

        // ==============================
        // Eliminar Vehículo
        // ==============================
        $(document).on('click', '.btnEliminarVehiculo', function() {
            const id = $(this).data('id');
            alertify.confirm('¿Estás seguro de eliminar este vehículo?', function() {
                $.post('ajax/vehiculos/eliminar-vehiculo.php', {
                    id
                }, function(res) {
                    if (res.success) {
                        alertify.success('Vehículo eliminado');
                        tabla.ajax.reload();
                    } else {
                        alertify.error(res.message || 'No se pudo eliminar');
                    }
                }, 'json');
            });
        });

        // ==============================
        // Cambiar Estatus
        // ==============================
        $(document).on('click', '.cambiarEstatusVehiculo', function() {
            const id = $(this).data('id');
            const estatusActual = parseInt($(this).data('valor'));
            const nuevoEstatus = estatusActual === 1 ? 0 : 1;
            alertify.confirm('¿Deseas cambiar el estatus?', function() {
                $.post('ajax/vehiculos/cambiar-estatus-vehiculo.php', {
                    id,
                    valor: nuevoEstatus
                }, function(res) {
                    if (res.success) {
                        alertify.success('Estatus actualizado');
                        tabla.ajax.reload();
                    } else {
                        alertify.error(res.message || 'Error al actualizar estatus');
                    }
                }, 'json');
            });
        });

        // ==============================
        // Validación y Guardado
        // ==============================
        $("#formVehiculo").validate({
            rules: {
                nombre_vehiculo: {
                    required: true,
                    minlength: 3
                },
                placas: {
                    required: true,
                    minlength: 3
                },
                marca: {
                    required: true,
                    minlength: 2
                },
                modelo: {
                    required: true,
                    minlength: 1
                },
                anio: {
                    required: true,
                    number: true,
                    min: 2000,
                    max: 2099
                },
                capacidad_kg: {
                    required: true,
                    number: true,
                    min: 1
                },
                tipo_unidad: {
                    required: true
                },
                remolque1_tipo: {
                    required: function() {
                        return $("#remolque1_placa").val().trim() !== "";
                    }
                },
                remolque1_placa: {
                    required: function() {
                        return $("#remolque1_tipo").val().trim() !== "";
                    }
                },
                remolque2_tipo: {
                    required: function() {
                        return $("#remolque2_placa").val().trim() !== "";
                    }
                },
                remolque2_placa: {
                    required: function() {
                        return $("#remolque2_tipo").val().trim() !== "";
                    }
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                const id = $('#idVehiculo').val();
                const esNuevo = id === '' || parseInt(id) === 0;
                alertify.confirm(
                    esNuevo ? '¿Deseas guardar este nuevo vehículo?' : '¿Deseas modificar los datos?',
                    function() {
                        $.post('ajax/vehiculos/guardar-vehiculo.php', $(form).serialize(), function(res) {
                            if (res.success) {
                                alertify.success(esNuevo ? 'Vehículo guardado correctamente' : 'Vehículo modificado correctamente');

                                // ✅ Cerrar modal correctamente
                                $('#modalVehiculo').modal('hide');

                                limpiarFormularioVehiculo();
                                tabla.ajax.reload();
                            } else {
                                alertify.error(res.message || 'No se pudo guardar');
                            }
                        }, 'json');
                    }
                );
            }
        });

        // En el ready de tu documento, agrega esto:
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });

    });
</script>