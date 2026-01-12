<!-- admin/contenido/productos-altas.php -->

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="productos">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Alta</li>
        </ol>
    </nav>
</div>

<section class="content">
    <form method="post" action="" id="formProductos">
        <div class="row">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-12 col-md-8">

                    <div class="card-header">
                        <h3>Agrega un nuevo producto</h3>
                    </div>

                    <div class="card-body">

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-9">
                                <div class="form-floating">
                                    <input type="text" name="clave" id="clave" class="form-control" placeholder="Clave">
                                    <label for="clave">Clave del producto <span class="text-danger">*</span></label>
                                </div>
                                <div class="form-text" id="claveHelp">Mínimo 3 caracteres. La clave debe ser única.</div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <label class="form-label mb-2 fw-bold">Tratamiento I.V.A.</label>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="excento" id="excento_si" value="1">
                                            <label class="form-check-label" for="excento_si">
                                                <span class="badge bg-success">Exento</span>
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="excento" id="excento_no" value="0" checked>
                                            <label class="form-check-label" for="excento_no">
                                                <span class="badge bg-info">Aplica I.V.A.</span>
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre">
                                <label for="nombre">Nombre del producto <span class="text-danger">*</span></label>
                            </div>
                            <div class="form-text">Entre 5 y 100 caracteres. Sea descriptivo.</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <textarea class="form-control" name="descripcion" id="descripcion" style="height: 110px" placeholder="Descripción"></textarea>
                                <label for="descripcion">Descripción</label>
                            </div>
                            <div class="form-text">Opcional. Detalles adicionales del producto.</div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3 mb-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Unidad de medida <span class="text-danger">*</span></label>
                                <select name="unidad_medida" id="unidad_medida" class="form-select" aria-label="Seleccione unidad de medida">
                                    <option value="" disabled selected>-- Seleccione --</option>
                                    <?php
                                    $con = "SELECT * FROM cat_unidades_de_medida ORDER BY nombre_unidad ASC";
                                    $rs = $clsConsulta->consultaGeneral($con);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($rs as $v => $val) {
                                            echo '<option value="' . (int)$val['id_unidad'] . '">' . htmlspecialchars($val['nombre_unidad']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="form-text">Seleccione cómo se mide este producto.</div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="productos" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Guardar producto
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </form>
</section>

<!-- Overlay / bloqueo de pantalla -->
<div id="pageOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
    style="z-index: 2000; background: rgba(0,0,0,.35);">
    <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
        <div class="mt-3 fw-semibold" id="overlayText">Procesando...</div>
    </div>
</div>


<script>
    $(document).ready(function() {

        var __isSubmitting = false;

        function bloquearPantalla(btn, texto) {
            __isSubmitting = true;

            $('#overlayText').text(texto || 'Procesando...');
            $('#pageOverlay').removeClass('d-none');

            if (btn && btn.length) {
                if (!btn.data('original-html')) {
                    btn.data('original-html', btn.html());
                }
                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (texto || 'Procesando...'));
            }
        }

        function desbloquearPantalla(btn) {
            __isSubmitting = false;

            $('#pageOverlay').addClass('d-none');

            if (btn && btn.length) {
                var original = btn.data('original-html');
                if (original) btn.html(original);
                btn.prop('disabled', false);
            }
        }


        // Validación visual de clave en tiempo real
        $('#clave').on('input', function() {
            var clave = $(this).val();
            if (clave.length >= 3) {
                $.ajax({
                    url: 'ajax/productos/busca-clave.php',
                    type: 'POST',
                    data: {
                        clave: clave
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.existe) {
                            $('#clave').addClass('is-invalid').removeClass('is-valid');
                            $('#claveHelp').html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Esta clave ya está en uso</span>');
                        } else {
                            $('#clave').removeClass('is-invalid').addClass('is-valid');
                            $('#claveHelp').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Clave disponible</span>');
                        }
                    }
                });
            } else {
                $('#clave').removeClass('is-invalid is-valid');
                $('#claveHelp').html('Mínimo 3 caracteres. La clave debe ser única.');
            }
        });

        $("#formProductos").validate({
            rules: {
                clave: {
                    required: true,
                    minlength: 3,
                    remote: {
                        url: "ajax/productos/busca-clave.php",
                        type: "POST",
                        data: {
                            clave: function() {
                                return $('#clave').val();
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            return json.existe ? '"La clave ya existe en el sistema"' : 'true';
                        }
                    }
                },
                nombre: {
                    required: true,
                    minlength: 5,
                    maxlength: 100
                },
                unidad_medida: {
                    required: true
                }
            },
            messages: {
                clave: {
                    required: "La clave es obligatoria",
                    minlength: "Mínimo 3 caracteres"
                },
                nombre: {
                    required: "El nombre es obligatorio",
                    minlength: "Mínimo 5 caracteres",
                    maxlength: "Máximo 100 caracteres"
                },
                unidad_medida: {
                    required: "Seleccione una unidad de medida"
                }
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            errorPlacement: function(error, element) {
                if (element.parent('.form-floating').length) {
                    error.insertAfter(element.parent('.form-floating'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function(form) {
                event.preventDefault();

                if (__isSubmitting) return;

                var btnGuardar = $('#formProductos button[type="submit"]').first();
                var datos = $("#formProductos").serialize();

                alertify.confirm(
                    'Confirmar guardado',
                    '¿Está seguro de guardar este producto?<br><small class="text-muted">Podrá modificarlo después si es necesario.</small>',
                    function() {

                        // Bloquear UI al confirmar
                        bloquearPantalla(btnGuardar, 'Guardando...');

                        $.ajax({
                            type: "POST",
                            url: "ajax/productos/guardar.php",
                            data: datos,
                            success: function(response) {
                                if (response === "success") {
                                    // Sin mensajes de éxito: redirigir directo
                                    location.href = "productos";
                                } else {
                                    desbloquearPantalla(btnGuardar);
                                    alertify.error('Error al guardar: ' + response);
                                }
                            },
                            error: function() {
                                desbloquearPantalla(btnGuardar);
                                alertify.error('Error de conexión con el servidor');
                            }
                        });
                    },
                    function() {
                        // Cancelado
                    }
                ).set('labels', {
                    ok: '<i class="fas fa-check me-1"></i> Sí, guardar',
                    cancel: '<i class="fas fa-times me-1"></i> Cancelar'
                }).set('transition', 'zoom');
            }

        });

        // Atajo Ctrl+Enter
        $(document).on('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                if ($("#formProductos").valid()) {
                    $("#formProductos").submit();
                }
            }
        });

    });
</script>