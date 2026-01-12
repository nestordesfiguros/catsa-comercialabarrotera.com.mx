<?php
// admin/contenido/productos-editar.php

$con = "SELECT * FROM cat_productos WHERE id_producto = " . (int)$cat;
$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $id_producto   = $val['id_producto'];
        $clave         = $val['clave'];
        $nombre        = $val['nombre'];
        $descripcion   = $val['descripcion'];
        // Si viene NULL real o viene como string "NULL", no mostrar nada
        if ($descripcion === null) {
            $descripcion = '';
        } else {
            $tmp = trim((string)$descripcion);
            if ($tmp === '' || strtoupper($tmp) === 'NULL') {
                $descripcion = '';
            }
        }
        $unidad_medida = $val['unidad_medida'];

        if (isset($val['excento']) && (int)$val['excento'] == 1) {
            $excento1 = 'checked';
            $excento2 = '';
        } else {
            $excento1 = '';
            $excento2 = 'checked';
        }
    }
} else {
    header("Location: productos");
    exit();
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="productos">Productos</a></li>
            <li class="breadcrumb-item">
                <a href="productos-editar/<?php echo (int)$id_producto; ?>">
                    <?php echo htmlspecialchars(substr($nombre, 0, 20)) . (strlen($nombre) > 20 ? '...' : ''); ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Modificar</li>
        </ol>
    </nav>
</div>

<section class="content">
    <form method="post" action="" id="formProductos">
        <div class="row">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-12 col-md-8">

                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Modificar producto
                            </h3>
                            <span class="badge bg-light text-primary">
                                ID: <?php echo (int)$id_producto; ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                </div>
                                <div>
                                    <small>
                                        <strong>Importante:</strong> Está editando el producto "<strong><?php echo htmlspecialchars($nombre); ?></strong>".
                                        Los cambios se reflejarán inmediatamente en todo el sistema.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-9">
                                <div class="form-floating">
                                    <input type="text" name="clave" id="clave" class="form-control"
                                        value="<?php echo htmlspecialchars($clave); ?>"
                                        placeholder="Clave" />
                                    <label for="clave"><span class="text-danger">*</span> Clave</label>
                                </div>
                                <div class="form-text" id="claveHelp">Identificador único. Mínimo 3 caracteres.</div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <label class="form-label mb-2 fw-bold">Tratamiento I.V.A.</label>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="excento" id="excento_si" value="1" <?php echo $excento1; ?>>
                                            <label class="form-check-label" for="excento_si">
                                                <span class="badge bg-success">Exento</span>
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="excento" id="excento_no" value="0" <?php echo $excento2; ?>>
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
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                    value="<?php echo htmlspecialchars($nombre); ?>"
                                    placeholder="Nombre" />
                                <label for="nombre"><span class="text-danger">*</span> Nombre</label>
                            </div>
                            <div class="form-text">Entre 5 y 100 caracteres.</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <textarea class="form-control" name="descripcion" id="descripcion" style="height: 110px"
                                    placeholder="Descripción"><?php echo htmlspecialchars($descripcion); ?></textarea>
                                <label for="descripcion">Descripción</label>
                            </div>
                            <div class="form-text">Opcional. Información adicional sobre el producto.</div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3 mb-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Unidad de medida <span class="text-danger">*</span></label>
                                <select name="unidad_medida" id="unidad_medida" class="form-select" aria-label="Seleccione unidad de medida">
                                    <option value="" disabled>-- Seleccione --</option>
                                    <?php
                                    $conUM = "SELECT * FROM cat_unidades_de_medida ORDER BY nombre_unidad ASC";
                                    $rsUM = $clsConsulta->consultaGeneral($conUM);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($rsUM as $vv => $row) {
                                            $selected = ((int)$row['id_unidad'] == (int)$unidad_medida) ? 'selected' : '';
                                            echo '<option value="' . (int)$row['id_unidad'] . '" ' . $selected . '>' . htmlspecialchars($row['nombre_unidad']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="form-text">Cómo se mide este producto.</div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="productos" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Guardar cambios
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <input type="hidden" name="id_producto" value="<?php echo (int)$id_producto; ?>">
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


        // Validar duplicados de clave en tiempo real (no cambia tu flujo)
        $('#clave').on('input', function() {
            var clave = $(this).val();
            var id_producto = <?php echo (int)$id_producto; ?>;

            if (clave.length >= 3) {
                $.ajax({
                    url: 'ajax/productos/busca-clave.php',
                    type: 'POST',
                    data: {
                        clave: clave,
                        id_producto: id_producto
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.existe) {
                            $('#clave').addClass('is-invalid').removeClass('is-valid');
                            $('#claveHelp').html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Esta clave ya está en uso por otro producto</span>');
                        } else {
                            $('#clave').removeClass('is-invalid').addClass('is-valid');
                            $('#claveHelp').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Clave disponible</span>');
                        }
                    }
                });
            } else {
                $('#clave').removeClass('is-invalid is-valid');
                $('#claveHelp').html('Identificador único. Mínimo 3 caracteres.');
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
                            },
                            id_producto: function() {
                                return <?php echo (int)$id_producto; ?>;
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            return json.existe ? '"Esta clave ya está en uso por otro producto"' : 'true';
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
                    'Confirmar cambios',
                    '¿Está seguro de guardar los cambios en este producto?<br><small class="text-muted">Los cambios afectarán todas las operaciones relacionadas.</small>',
                    function() {

                        // Bloquear UI al confirmar
                        bloquearPantalla(btnGuardar, 'Guardando...');

                        $.ajax({
                            type: "POST",
                            url: "ajax/productos/modificar.php",
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
                            error: function(xhr, status, error) {
                                desbloquearPantalla(btnGuardar);
                                alertify.error('Error de conexión con el servidor: ' + error);
                            }
                        });
                    },
                    function() {
                        // Cancelado: no hacemos nada
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