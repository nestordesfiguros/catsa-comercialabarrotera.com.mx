<?php
// admin/contenido/clientes-editar.php

// Empresa actual (multiempresa)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$idCliente = (int)$cat;

// Scope seguro (permite legacy NULL/0)
$whereEmpresa = "";
if ($idEmpresa > 0) {
    $whereEmpresa = " AND (id_empresa = {$idEmpresa} OR id_empresa IS NULL OR id_empresa = 0)";
}

$con = "SELECT * FROM cat_clientes WHERE id = {$idCliente} {$whereEmpresa} LIMIT 1";
$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows <= 0) {
    header("Location: clientes");
    exit;
}

$val = $rs[1];

function vtxt($v)
{
    if ($v === null) return '';
    $v = (string)$v;
    $v = trim($v);
    if ($v === '' || strtoupper($v) === 'NULL') return '';
    return $v;
}

$no_cliente        = vtxt($val['no_cliente'] ?? '');
$razon_social      = vtxt($val['razon_social'] ?? '');
$nombre_comercial  = vtxt($val['nombre_comercial'] ?? '');
$rfc              = vtxt($val['rfc'] ?? '');
$calle            = vtxt($val['calle'] ?? '');
$num_ext          = vtxt($val['num_ext'] ?? '');
$num_int          = vtxt($val['num_int'] ?? '');
$colonia          = vtxt($val['colonia'] ?? '');
$cp               = vtxt($val['cp'] ?? '');
$id_estado         = (int)($val['id_estado'] ?? 0);
$id_municipio      = (int)($val['id_municipio'] ?? 0);
$correo           = vtxt($val['correo'] ?? '');
$correo_factura   = vtxt($val['correo_factura'] ?? '');
$localidad        = vtxt($val['localidad'] ?? '');
$contacto         = vtxt($val['contacto'] ?? '');
$tel1             = vtxt($val['tel1'] ?? '');
$tel2             = vtxt($val['tel2'] ?? '');
$mapa             = vtxt($val['mapa'] ?? '');
$credito_dias      = (int)($val['credito_dias'] ?? 0);
$credito_limite_db = $val['credito_limite'] ?? 0;
$credito_limite    = (is_numeric($credito_limite_db) ? number_format((float)$credito_limite_db, 2, '.', ',') : '');
$lista_precios     = isset($val['lista_precios']) ? (int)$val['lista_precios'] : 0;

// ====== Nombres de listas de precio (desde cat_productos.precio0X_nombre) ======
$listaNombres = [
    1 => 'Lista 1',
    2 => 'Lista 2',
    3 => 'Lista 3',
    4 => 'Lista 4',
    5 => 'Lista 5',
];

if ($idEmpresa > 0) {
    // Tomar el primer producto del primer almacén de la empresa (consistencia UX)
    $sqlNombres = "
        SELECT
            p.precio01_nombre, p.precio02_nombre, p.precio03_nombre, p.precio04_nombre, p.precio05_nombre
        FROM cat_almacenes a
        INNER JOIN cat_productos p ON p.id_almacen = a.id
        WHERE a.id_empresa = {$idEmpresa}
          AND p.estatus = 1
        ORDER BY a.almacen ASC, p.id_producto ASC
        LIMIT 1
    ";
    $rsN = $clsConsulta->consultaGeneral($sqlNombres);
    if ($clsConsulta->numrows > 0) {
        $row = $rsN[1];
        for ($i = 1; $i <= 5; $i++) {
            $k = 'precio0' . $i . '_nombre';
            $v = isset($row[$k]) ? trim((string)$row[$k]) : '';
            if ($v !== '' && strtoupper($v) !== 'NULL' && !preg_match('/^precio0?' . $i . '$/i', $v)) {
                $listaNombres[$i] = $v;
            }
        }
    }
}
?>

<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="clientes">Clientes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="row">
        <form method="post" id="formClientes" action="">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-12 col-lg-9 col-xl-8">

                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="fa fa-user-pen me-2"></i>Editar Cliente</h5>
                        <small class="text-muted">Campos con <span class="text-danger">*</span> son obligatorios</small>
                    </div>

                    <div class="card-body">

                        <!-- Datos generales -->
                        <div class="mb-3">
                            <h6 class="mb-0">Datos generales</h6>
                            <small class="text-muted">Actualiza la información principal del cliente.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="no_cliente" name="no_cliente" class="form-control" placeholder="Número de cliente"
                                        value="<?php echo htmlspecialchars($no_cliente); ?>" />
                                    <label for="no_cliente"><span class="text-danger">*</span> Número de cliente</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <select id="lista_precios" name="lista_precios" class="form-select">
                                        <option value="" <?php echo ($lista_precios <= 0 ? 'selected' : ''); ?>>Sin asignar</option>
                                        <option value="1" <?php echo ($lista_precios === 1 ? 'selected' : ''); ?>><?php echo htmlspecialchars($listaNombres[1]); ?></option>
                                        <option value="2" <?php echo ($lista_precios === 2 ? 'selected' : ''); ?>><?php echo htmlspecialchars($listaNombres[2]); ?></option>
                                        <option value="3" <?php echo ($lista_precios === 3 ? 'selected' : ''); ?>><?php echo htmlspecialchars($listaNombres[3]); ?></option>
                                        <option value="4" <?php echo ($lista_precios === 4 ? 'selected' : ''); ?>><?php echo htmlspecialchars($listaNombres[4]); ?></option>
                                        <option value="5" <?php echo ($lista_precios === 5 ? 'selected' : ''); ?>><?php echo htmlspecialchars($listaNombres[5]); ?></option>
                                    </select>
                                    <label for="lista_precios">Lista de precios (opcional)</label>
                                </div>
                                <div class="form-text">Si no asignas una lista, el sistema usará la configuración por defecto al vender.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="rfc" name="rfc" class="form-control" placeholder="RFC"
                                        value="<?php echo htmlspecialchars($rfc); ?>" />
                                    <label for="rfc">RFC</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" id="razon_social" name="razon_social" class="form-control" placeholder="Razón social"
                                        value="<?php echo htmlspecialchars($razon_social); ?>" />
                                    <label for="razon_social">Razón social (opcional)</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" id="nombre_comercial" name="nombre_comercial" class="form-control" placeholder="Nombre comercial"
                                        value="<?php echo htmlspecialchars($nombre_comercial); ?>" />
                                    <label for="nombre_comercial"><span class="text-danger">*</span> Nombre comercial</label>
                                </div>
                                <div class="form-text">Obligatorio. Es el nombre que verás en ventas y reportes.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" id="correo" name="correo" class="form-control" placeholder="Correo"
                                        value="<?php echo htmlspecialchars($correo); ?>" />
                                    <label for="correo">Correo</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" id="correo_factura" name="correo_factura" class="form-control" placeholder="Correo factura"
                                        value="<?php echo htmlspecialchars($correo_factura); ?>" />
                                    <label for="correo_factura">Correo factura</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Dirección -->
                        <div class="mb-3">
                            <h6 class="mb-0">Dirección</h6>
                            <small class="text-muted">Esta información ayuda en rutas y facturación.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" id="calle" name="calle" class="form-control" placeholder="Calle"
                                        value="<?php echo htmlspecialchars($calle); ?>" />
                                    <label for="calle"><span class="text-danger">*</span> Calle</label>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="form-floating">
                                    <input type="text" id="num_ext" name="num_ext" class="form-control" placeholder="Número exterior"
                                        value="<?php echo htmlspecialchars($num_ext); ?>" />
                                    <label for="num_ext">Número exterior</label>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="form-floating">
                                    <input type="text" id="num_int" name="num_int" class="form-control" placeholder="Número interior"
                                        value="<?php echo htmlspecialchars($num_int); ?>" />
                                    <label for="num_int">Número interior</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1"><span class="text-danger">*</span> Estado</label>
                                <select name="id_estado" id="edo" class="form-select" onchange="fnMunicipio();">
                                    <option value="">Selecciona un Estado</option>
                                    <?php
                                    $conE = "SELECT * FROM estados";
                                    $rsE = $clsConsulta->consultaGeneral($conE);
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($rsE as $rowE) {
                                            $sel = ((int)$rowE['id'] === (int)$id_estado) ? 'selected' : '';
                                            echo '<option value="' . (int)$rowE['id'] . '" ' . $sel . '>' . htmlspecialchars($rowE['nombre']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div id="mpo" class="col-12 col-md-6">
                                <label class="form-label mb-1"><span class="text-danger">*</span> Municipio</label>
                                <select name="id_municipio" id="id_municipio" class="form-select" onchange="fnCp();">
                                    <option value="">Selecciona un Municipio</option>
                                    <?php
                                    if ($id_estado > 0) {
                                        $conM = "SELECT id, nombre FROM municipios WHERE estado_id = {$id_estado} ORDER BY nombre ASC";
                                        $rsM = $clsConsulta->consultaGeneral($conM);
                                        if ($clsConsulta->numrows > 0) {
                                            foreach ($rsM as $rowM) {
                                                $sel = ((int)$rowM['id'] === (int)$id_municipio) ? 'selected' : '';
                                                echo '<option value="' . (int)$rowM['id'] . '" ' . $sel . '>' . htmlspecialchars($rowM['nombre']) . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-floating">
                                    <input type="text" id="cp" list="cplist" name="cp" class="form-control" placeholder="C.P."
                                        onblur="fnColonias();" autocomplete="off"
                                        value="<?php echo htmlspecialchars($cp); ?>" />
                                    <label for="cp">C.P.</label>
                                </div>
                                <datalist id="cplist">
                                    <?php if ($cp !== ''): ?>
                                        <option value="<?php echo htmlspecialchars($cp); ?>"></option>
                                    <?php endif; ?>
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" id="colonia" list="colonialist" name="colonia" class="form-control" placeholder="Colonia"
                                        autocomplete="off" value="<?php echo htmlspecialchars($colonia); ?>" />
                                    <label for="colonia">Colonia</label>
                                </div>
                                <datalist id="colonialist">
                                    <?php if ($colonia !== ''): ?>
                                        <option value="<?php echo htmlspecialchars($colonia); ?>"></option>
                                    <?php endif; ?>
                                </datalist>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="localidad" name="localidad" class="form-control" placeholder="Localidad"
                                        value="<?php echo htmlspecialchars($localidad); ?>" />
                                    <label for="localidad">Localidad</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Contacto -->
                        <div class="mb-3">
                            <h6 class="mb-0">Contacto</h6>
                            <small class="text-muted">Para comunicación y seguimiento.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="contacto" name="contacto" class="form-control" placeholder="Contacto"
                                        value="<?php echo htmlspecialchars($contacto); ?>" />
                                    <label for="contacto">Contacto (Nombre)</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="tel1" name="tel1" class="form-control text-end" placeholder="Teléfono"
                                        value="<?php echo htmlspecialchars($tel1); ?>" />
                                    <label for="tel1">Teléfono</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="tel2" name="tel2" class="form-control text-end" placeholder="Teléfono 2"
                                        value="<?php echo htmlspecialchars($tel2); ?>" />
                                    <label for="tel2">Teléfono 2</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Crédito -->
                        <div class="mb-3">
                            <h6 class="mb-0">Crédito</h6>
                            <small class="text-muted">Opcional. Si no aplica, deja en blanco.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="credito_dias" name="credito_dias" class="form-control" placeholder="Días"
                                        value="<?php echo htmlspecialchars((string)$credito_dias); ?>" />
                                    <label for="credito_dias">Días de crédito</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="credito_limite" name="credito_limite" class="form-control text-end" placeholder="Límite"
                                        value="<?php echo htmlspecialchars($credito_limite); ?>" />
                                    <label for="credito_limite">Límite de crédito</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-floating">
                                    <input type="text" id="mapa" name="mapa" class="form-control" placeholder="Mapa"
                                        value="<?php echo htmlspecialchars($mapa); ?>" />
                                    <label for="mapa">Mapa</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="clientes" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                            Guardar cambios
                        </button>

                        <input type="hidden" name="id" value="<?php echo (int)$idCliente; ?>">
                    </div>

                </div>
            </div>
        </form>
    </div>
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
    // ====== Máscaras ======
    IMask(document.getElementById('tel1'), {
        mask: '000-000-0000'
    });
    IMask(document.getElementById('tel2'), {
        mask: '000-000-0000'
    });
    IMask(document.getElementById('credito_dias'), {
        mask: '00'
    });

    var element = document.getElementById('credito_limite');
    if (element) {
        IMask(element, {
            mask: Number,
            thousandsSeparator: ',',
            radix: '.',
            scale: 2,
            normalizeZeros: true,
            min: 0,
        });
    }

    // ====== Overlay / anti doble envío ======
    var __isSubmitting = false;

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
            var original = btn.data('original-html');
            if (original) btn.html(original);
            btn.prop('disabled', false);
        }
    }

    // ====== Estado -> Municipios ======
    function fnMunicipio() {
        var id_estado = $("#edo").val();
        $.ajax({
            type: "POST",
            url: "ajax/clientes/municipios.php",
            data: {
                id_estado: id_estado
            },
            success: function(data) {
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    // municipios.php devuelve SOLO label + select
                    $("#mpo").html(data);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function fnColonias() {
        var cp = $("#cp").val();
        $.ajax({
            type: "POST",
            url: "ajax/clientes/obten-colonias.php",
            data: {
                cp: cp
            },
            success: function(data) {
                $("#colonialist").html(data);
            }
        });
    }

    function fnCp() {
        var municipio_id = $("#id_municipio").val();
        $.ajax({
            type: "POST",
            url: "ajax/clientes/obten-cp.php",
            data: {
                municipio_id: municipio_id
            },
            success: function(data) {
                $("#cplist").html(data);
            }
        });
    }

    $(document).ready(function() {

        $("#formClientes").validate({
            rules: {
                no_cliente: {
                    required: true
                },

                // Reglas nuevas
                nombre_comercial: {
                    required: true,
                    minlength: 3,
                    maxlength: 200
                },
                razon_social: {
                    required: false,
                    minlength: 4,
                    maxlength: 255
                },

                calle: {
                    required: true
                },
                id_estado: {
                    required: true
                },
                id_municipio: {
                    required: true
                }
            },
            messages: {
                no_cliente: {
                    required: 'El número de cliente es obligatorio'
                },
                nombre_comercial: {
                    required: "El nombre comercial es obligatorio",
                    minlength: "Mínimo 3 caracteres",
                    maxlength: "Máximo 200 caracteres"
                },
                razon_social: {
                    minlength: "Escribe más de 4 caracteres",
                    maxlength: "Máximo 255 caracteres"
                },
                calle: {
                    required: "Campo obligatorio"
                },
                id_estado: {
                    required: "Campo obligatorio"
                },
                id_municipio: {
                    required: "Campo obligatorio"
                }
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            errorPlacement: function(error, element) {
                error.insertAfter(element);
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

                var btnGuardar = $('#btnGuardarCliente');
                var datos = $("#formClientes").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar los cambios?',
                    function() {

                        bloquearPantalla(btnGuardar, 'Guardando...');

                        $.ajax({
                            type: "POST",
                            url: "ajax/clientes/modifica-clientes.php",
                            data: datos,
                            success: function(resp) {
                                if (resp === "success") {
                                    // Sin mensaje de éxito (UX)
                                    location.href = "clientes";
                                } else {
                                    desbloquearPantalla(btnGuardar);
                                    alertify.error('Error al guardar: ' + resp);
                                }
                            },
                            error: function(xhr, status, error) {
                                desbloquearPantalla(btnGuardar);
                                alertify.error('Error de conexión con el servidor: ' + error);
                            }
                        });
                    },
                    function() {
                        // cancelado
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });
            }
        });

    });
</script>