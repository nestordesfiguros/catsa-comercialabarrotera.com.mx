<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Operadores </li>
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
                    </div>


                    <?php
                    $idEmpresa = $_SESSION['id_empresa'] ?? 0;
                    ?>

                    <div class="container mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Catálogo de Operadores</h5>
                            <button type="button" class="btn btn-primary" id="btnNuevoOperador">Nuevo Operador</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tablaOperadores" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>RFC</th>
                                    <th>CURP</th>
                                    <th>Licencia</th>
                                    <th>Tipo</th>
                                    <th>Vigencia</th>
                                    <th>Teléfono</th>
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
<!-- Modal Operador -->
<div class="modal fade" id="modalOperador" tabindex="-1" aria-labelledby="modalOperadorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalOperadorLabel">Nuevo Operador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Formulario -->
            <form id="formOperador" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="idOperador" name="id">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="apellido1" name="apellido1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="apellido2" name="apellido2">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">RFC</label>
                            <input type="text" class="form-control" id="rfc" name="rfc" maxlength="13" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">CURP</label>
                            <input type="text" class="form-control" id="curp" name="curp" maxlength="18" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">N° Licencia</label>
                            <input type="text" class="form-control" id="licencia_numero" name="licencia_numero" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo Licencia</label>
                            <select class="form-select" id="licencia_tipo" name="licencia_tipo" required>
                                <option value="">Selecciona...</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Vigencia Licencia</label>
                            <input type="date" class="form-control" id="licencia_vigencia" name="licencia_vigencia">
                        </div>
                    </div>
                </div>

                <!-- Footer solo con Guardar -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">GUARDAR</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const tabla = $('#tablaOperadores').DataTable({
            ajax: {
                url: 'ajax/operadores/tabla-operadores.php',
                dataSrc: 'data'
            },
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        function limpiarFormularioOperador() {
            $('#formOperador')[0].reset();
            $('#idOperador').val('');
            $('#formOperador .is-valid, #formOperador .is-invalid').removeClass('is-valid is-invalid');
        }

        $('#modalOperador').on('hidden.bs.modal', limpiarFormularioOperador);

        // Nuevo
        $('#btnNuevoOperador').click(function() {
            limpiarFormularioOperador();
            $('#modalOperadorLabel').text('Nuevo Operador');
            $('#modalOperador').modal('show');
        });

        // Editar
        $(document).on('click', '.btnEditarOperador', function() {
            const id = $(this).data('id');
            limpiarFormularioOperador();
            $('#idOperador').val(id);
            $('#modalOperadorLabel').text('Editar Operador');

            $.post('ajax/operadores/obtener-operador.php', {
                id
            }, function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#nombre').val(d.nombre);
                    $('#apellido1').val(d.apellido1);
                    $('#apellido2').val(d.apellido2);
                    $('#rfc').val(d.rfc);
                    $('#curp').val(d.curp);
                    $('#telefono').val(d.telefono);
                    $('#licencia_numero').val(d.licencia_numero);
                    $('#licencia_tipo').val(d.licencia_tipo);
                    $('#licencia_vigencia').val(d.licencia_vigencia);
                    $('#modalOperador').modal('show');
                } else {
                    alertify.error('Operador no encontrado.');
                }
            }, 'json');
        });

        // Eliminar
        $(document).on('click', '.btnEliminarOperador', function() {
            const id = $(this).data('id');
            alertify.confirm('¿Estás seguro de eliminar este operador?', function() {
                $.post('ajax/operadores/eliminar-operador.php', {
                    id
                }, function(res) {
                    if (res.success) {
                        alertify.success('Operador eliminado');
                        tabla.ajax.reload();
                    } else {
                        alertify.error(res.message || 'No se pudo eliminar');
                    }
                }, 'json');
            });
        });

        // Cambiar estatus
        $(document).on('click', '.cambiarEstatusOperador', function() {
            const id = $(this).data('id');
            const estatusActual = parseInt($(this).data('valor'));
            const nuevoEstatus = estatusActual === 1 ? 0 : 1;
            alertify.confirm('¿Deseas cambiar el estatus?', function() {
                $.post('ajax/operadores/cambiar-estatus-operador.php', {
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

        // Validación y Guardado
        $("#formOperador").validate({
            rules: {
                nombre: {
                    required: true,
                    minlength: 2
                },
                apellido1: {
                    required: true,
                    minlength: 2
                },
                rfc: {
                    required: true,
                    minlength: 12,
                    maxlength: 13
                },
                curp: {
                    required: true,
                    minlength: 18,
                    maxlength: 18
                },
                licencia_numero: {
                    required: true
                },
                licencia_tipo: {
                    required: true
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                const id = $('#idOperador').val();
                const esNuevo = id === '' || parseInt(id) === 0;
                alertify.confirm(
                    esNuevo ? '¿Deseas guardar este nuevo operador?' : '¿Deseas modificar los datos?',
                    function() {
                        $.post('ajax/operadores/guardar-operador.php', $(form).serialize(), function(res) {
                            if (res.success) {
                                alertify.success(esNuevo ? 'Operador guardado correctamente' : 'Operador modificado correctamente');
                                $('#modalOperador').modal('hide');
                                limpiarFormularioOperador();
                                tabla.ajax.reload();
                            } else {
                                alertify.error(res.message || 'No se pudo guardar');
                            }
                        }, 'json');
                    }
                );
            }
        });

    });
</script>