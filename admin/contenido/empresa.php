<?php
$con = "SELECT * FROM empresa WHERE id=1";
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $v => $val) {
    $id = $val['id'];
    $razon_social = $val['razon_social'];
    $nombre_comercial = $val['nombre_comercial'];
    $rfc = $val['rfc'];
    $correo = $val['correo'];
    $calle = $val['calle'];
    $num_ext = $val['num_ext'];
    $num_int = $val['num_int'];
    $id_estado = $val['id_estado'];
    $id_municipio = $val['id_municipio'];
    $cp = $val['cp'];
    $colonia = $val['colonia'];
    $tel1 = $val['tel1'];
    $tel2 = $val['tel2'];
    $tel3 = $val['tel3'];
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Empresa</li>
        </ol>
    </nav>
</div>

<style>
    .no-uppercase {
        text-transform: none !important;
    }
</style>
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- === Tarjeta: Datos de la empresa (TU ORIGINAL) === -->
            <div class="card col-6">
                <div class="card-header">
                    <h5 class="text-center">Datos de la empresa</h5>
                </div>
                <div class="card-body">
                    <form method="post" id="formEmpresa" action="">
                        <div class="d-flex justify-content-center align-items-center w-100">
                            <div class="card col-12">
                                <div class="card-body ">
                                    <div class="row gy-3 ">

                                        <div class="form-group">
                                            <div class="form-outline" data-mdb-input-init>
                                                <input type="text" id="razon_social" name="razon_social" class="form-control" value="<?php echo $razon_social; ?>" />
                                                <label class="form-label" for="razon_social">Razón Social</label>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <div class="form-outline" data-mdb-input-init>
                                                <input type="text" id="nombre_comercial" name="nombre_comercial" class="form-control" value="<?php echo $nombre_comercial; ?>" />
                                                <label class="form-label" for="nombre_comercial">Nombre Comercial</label>
                                            </div>
                                        </div>

                                        <div class="gy-3">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="rfc" name="rfc" class="form-control" value="<?php echo $rfc; ?>" />
                                                        <label class="form-label" for="rfc">RFC</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-6">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="correo" name="correo" class="form-control" value="<?php echo $correo; ?>" />
                                                        <label class="form-label" for="correo">Correo</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gy-3">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="calle" name="calle" class="form-control" value="<?php echo $calle; ?>" />
                                                        <label class="form-label" for="calle">Calle</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-3">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="num_ext" name="num_ext" class="form-control" value="<?php echo $num_ext; ?>" />
                                                        <label class="form-label" for="num_ext">Número Exterior</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-3">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="num_int" name="num_int" class="form-control" value="<?php echo $num_int; ?>" />
                                                        <label class="form-label" for="num_int">Número Interior</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gy-3">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <select name="id_estado" id="edo" class="form-select" data-mdb-select-init onchange="fnMunicipio();">
                                                        <option value="">Selecciona un Estado</option>
                                                        <?php
                                                        $con = "SELECT * FROM estados";
                                                        $rs = $clsConsulta->consultaGeneral($con);
                                                        foreach ($rs as $v => $val) {
                                                            if ($val['id'] == $id_estado) {
                                                                echo '<option value="' . $val['id'] . '" selected>' . strtoupper($val['nombre']) . '</option>';
                                                            } else {
                                                                echo '<option value="' . $val['id'] . '">' . strtoupper($val['nombre']) . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div id="mpo" class="col-6">
                                                    <div class="form-group">
                                                        <select name="id_municipio" id="id_municipio" class="form-select" onchange="fnCp();">
                                                            <?php
                                                            $con = "SELECT * FROM municipios WHERE estado_id=" . $id_estado . " ORDER BY nombre ASC";
                                                            $rs = $clsConsulta->consultaGeneral($con);
                                                            foreach ($rs as $v => $val) {
                                                                if ($val['id'] == $id_municipio) {
                                                                    echo '<option value="' . $val['id'] . '" selected>' . $val['nombre'] . '</option>';
                                                                } else {
                                                                    echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gy-3">
                                            <div class="row">
                                                <div class="form-group col-12 col-lg-4">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="cp" list="cplist" name="cp" class="form-control" onblur="fnColonias();" autocomplete="off" value="<?php echo $cp; ?>" />
                                                        <label class="form-label" for="cp">Código Postal</label>
                                                    </div>
                                                    <datalist id="cplist"></datalist>
                                                </div>
                                                <div class="form-group col-12 col-lg-8">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" id="colonia" list="colonialist" name="colonia" class="form-control" autocomplete="off" value="<?php echo $colonia; ?>" />
                                                        <label class="form-label" for="colonia">Colonia</label>
                                                    </div>
                                                    <datalist id="colonialist"></datalist>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gy-3">
                                            <div class="row">
                                                <div class="form-group col-4">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" name="tel1" id="tel1" class="form-control" value="<?php echo $tel1; ?>" />
                                                        <label class="form-label" for="tel1">Teléfono</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-4">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" name="tel2" id="tel2" class="form-control" value="<?php echo $tel2; ?>" />
                                                        <label class="form-label" for="tel2">Teléfono</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-4">
                                                    <div class="form-outline" data-mdb-input-init>
                                                        <input type="text" name="tel3" id="tel3" class="form-control" value="<?php echo $tel3; ?>" />
                                                        <label class="form-label" for="tel3">Teléfono</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary"> Guardar Cambios</button>
                                    <input type="hidden" name="idestado" id="idestado">
                                    <input type="hidden" name="accion" value="ALTA">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- === Tarjeta: Cargar CSD para timbrado (NUEVA) === -->
            <div class="card col-6">
                <form id="formCSD" enctype="multipart/form-data">

                    <div class="card-header">
                        <h5 class="text-center">Datos para timbrado (CSD)</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipoCsd" id="tipoPfx" value="pfx" checked>
                                <label class="form-check-label" for="tipoPfx">PFX</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipoCsd" id="tipoCerKey" value="cerkey">
                                <label class="form-check-label" for="tipoCerKey">CER + KEY</label>
                            </div>
                        </div>

                        <!-- PFX -->
                        <div id="bloquePfx">
                            <div class="form-group mb-3">
                                <label class="form-label" for="csdPfx">Archivo PFX (CSD)</label>
                                <input type="file" class="form-control" id="csdPfx" name="pfx" accept=".pfx">
                            </div>
                        </div>

                        <!-- CER/KEY -->
                        <div id="bloqueCerKey" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="csdCer">Archivo CER</label>
                                        <input type="file" class="form-control" id="csdCer" name="cer" accept=".cer">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="csdKey">Archivo KEY</label>
                                        <input type="file" class="form-control" id="csdKey" name="key" accept=".key">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="csdPass">Contraseña del certificado</label>
                            <input
                                type="password"
                                class="form-control"
                                id="csdPass"
                                name="pass"
                                placeholder="En DEV suele ser 12345678a"
                                required
                                autocomplete="new-password">
                        </div>

                        <div class="form-group mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="verPass">
                                <label class="form-check-label" for="verPass">Mostrar contraseña</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" id="btnProbarCsd" class="btn btn-outline-secondary">
                                <i class="fa fa-stethoscope"></i> Probar CSD
                            </button>
                            <div class="text-end">
                                <button type="button" id="btnInstalarCsd" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Instalar CSD
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted">
                            Estado: <span id="estadoCsd">—</span>
                        </div>
                        <div class="mt-1 small">
                            <b>Importante:</b> El RFC de la tabla <code>empresa</code> debe coincidir con el certificado cargado
                            (en DEV: <code>EKU9003173C9</code> o el que corresponda a tu CSD de pruebas).
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</section>


<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<script>
    /* ============================================================
     Parche jQuery Validate: permitir accept=".pfx,.cer,.key" (extensiones)
     sin que marque "valid mimetype". Debe cargarse ANTES de .validate()
     ============================================================ */
    (function() {
        if (!$.validator || !$.validator.methods || !$.validator.methods.accept) return;
        var originalAccept = $.validator.methods.accept;
        $.validator.methods.accept = function(value, element, param) {
            if (typeof param === 'string' && /^\s*\./.test(param)) {
                var exts = param.split(',').map(function(s) {
                    return s.trim().replace(/^\./, '');
                }).filter(Boolean).join('|');
                if (!exts) return true;
                var re = new RegExp("\\.(" + exts + ")$", "i");
                return this.optional(element) || re.test(value);
            }
            return originalAccept.call(this, value, element, param);
        };
        $.extend($.validator.messages, {
            accept: 'Selecciona un archivo con el tipo/extensión permitido.'
        });
    })();

    // Máscaras
    IMask(document.getElementById('tel1'), {
        mask: '000-000-0000'
    });
    IMask(document.getElementById('tel2'), {
        mask: '000-000-0000'
    });
    IMask(document.getElementById('tel3'), {
        mask: '000-000-0000'
    });
    IMask(document.getElementById('cp'), {
        mask: '00000'
    });

    // AJAX selects (tus originales)
    function fnMunicipio() {
        var id_estado = $("#edo").val();
        $.ajax({
            type: "POST",
            url: "ajax/clientes/municipios.php",
            data: {
                id_estado: id_estado
            },
            success: function(data) {
                $("#mpo").html(data);
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

    // Helpers UI
    function showSpinner(show) {
        document.getElementById('spinner').style.display = show ? 'block' : 'none';
    }

    function setEstado(texto) {
        $('#estadoCsd').text(texto || '—');
    }

    // ======= VALIDATE: Empresa (tu original con mínimos ajustes) =======
    $(document).ready(function() {
        $.validator.addMethod("regex", function(value, element, regexp) {
            if (regexp.constructor != RegExp) regexp = new RegExp(regexp);
            else if (regexp.global) regexp.lastIndex = 0;
            return this.optional(element) || regexp.test(value);
        }, "Revisa .");

        $("#formEmpresa").validate({
            rules: {
                razon_social: {
                    required: true,
                    minlength: 4,
                    maxlength: 200
                },
                // rfc: { required: true, minlength: 13, maxlength: 14 },
                correo: {
                    required: true,
                    email: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
                },
                calle: {
                    required: true
                },
                cp: {
                    required: true
                },
                num_ext: {
                    required: true
                },
                colonia: {
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
                razon_social: {
                    required: "Campo obligatorio",
                    minlength: "Escribe más de 4 caracteres",
                    regex: "Escribe solo letras y espacios"
                },
                rfc: {
                    required: "Campo obligatorio",
                    maxlength: "El rfc debe de tener 13 o 14 caracteres",
                    minlength: "El rfc debe de tener 13 o 14 caracteres",
                    regex: "RFC no valido"
                },
                correo: {
                    required: "Campo obligatorio",
                    email: "Por favor, escribe una dirección de correo válida",
                    regex: "Formato de correo no válido",
                    remote: "El correo ya fue capturado"
                },
                calle: {
                    required: "Campo obligatorio"
                },
                num_ext: {
                    required: "Campo obligatorio"
                },
                colonia: {
                    required: "Campo obligatorio"
                },
                id_estado: {
                    required: "Campo obligatorio"
                },
                id_municipio: {
                    required: "Campo obligatorio"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function(form) {
                alertify.confirm('Aviso..!', '¿Deseas guardar los datos de la empresa?',
                    function() {
                        var datos = $("#formEmpresa").serialize();
                        $.ajax({
                            type: "POST",
                            url: "ajax/empresa/modificar.php",
                            data: datos,
                            success: function(data) {
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    alertify.success('Ok');
                                    location.href = "empresa";
                                }
                            }
                        });
                    },
                    function() {
                        alertify.error('Cancel')
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });
            }
        });

        // ======= Toggle PFX vs CER/KEY =======
        $('input[name="tipoCsd"]').on('change', function() {
            const tipo = $('input[name="tipoCsd"]:checked').val();
            if (tipo === 'pfx') {
                $('#bloquePfx').show();
                $('#bloqueCerKey').hide();
                $('#csdCer, #csdKey').val('').removeClass('is-invalid');
            } else {
                $('#bloquePfx').hide();
                $('#bloqueCerKey').show();
                $('#csdPfx').val('').removeClass('is-invalid');
            }
            setEstado('—');
            const v = $('#formCSD').data('validator');
            if (v) {
                v.resetForm();
                $('.is-invalid').removeClass('is-invalid');
            }
        });

        // ======= Mostrar/ocultar contraseña =======
        $('#verPass').on('change', function() {
            $('#csdPass').attr('type', this.checked ? 'text' : 'password');
        });

        // ======= VALIDATE: CSD =======
        $('#formCSD').validate({
            ignore: [],
            rules: {
                pfx: {
                    required: {
                        depends: function() {
                            return $('input[name="tipoCsd"]:checked').val() === 'pfx';
                        }
                    },
                    extension: 'pfx',
                    accept: '.pfx'
                },
                cer: {
                    required: {
                        depends: function() {
                            return $('input[name="tipoCsd"]:checked').val() === 'cerkey';
                        }
                    },
                    extension: 'cer',
                    accept: '.cer'
                },
                key: {
                    required: {
                        depends: function() {
                            return $('input[name="tipoCsd"]:checked').val() === 'cerkey';
                        }
                    },
                    extension: 'key',
                    accept: '.key'
                },
                pass: {
                    required: true
                }
            },
            messages: {
                pfx: {
                    required: 'Selecciona el archivo .pfx',
                    extension: 'Debe ser .pfx'
                },
                cer: {
                    required: 'Selecciona el archivo .cer',
                    extension: 'Debe ser .cer'
                },
                key: {
                    required: 'Selecciona el archivo .key',
                    extension: 'Debe ser .key'
                },
                pass: {
                    required: 'Ingresa la contraseña del certificado'
                }
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function(el) {
                $(el).addClass('is-invalid');
            },
            unhighlight: function(el) {
                $(el).removeClass('is-invalid');
            },
            errorPlacement: function(error, element) {
                if (element.parent('.input-group').length) error.insertAfter(element.parent());
                else error.insertAfter(element);
            }
        });

        // ======= INSTALAR CSD (sube e instala en servidor) =======
        $('#btnInstalarCsd').on('click', function() {
            if (!$('#formCSD').valid()) return;

            const tipo = $('input[name="tipoCsd"]:checked').val();
            const fd = new FormData(document.getElementById('formCSD'));
            fd.append('tipo', tipo);

            alertify.confirm('Confirmación', '¿Instalar/actualizar CSD en el servidor?',
                function() {
                    showSpinner(true);
                    $.ajax({
                        url: 'ajax/timbrado/instalar-csd.php',
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    }).done(function(resp) {
                        try {
                            const r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (r.success) {
                                const parts = [];
                                if (r.msg) parts.push(r.msg);
                                if (r.rfc_cert) parts.push('RFC: ' + r.rfc_cert);
                                setEstado(parts.join(' | ') || 'OK');
                                alertify.success('CSD instalado.');
                            } else {
                                setEstado(r.msg || 'Error');
                                alertify.error(r.msg || 'Error al instalar CSD');
                            }
                        } catch (e) {
                            console.error(resp);
                            setEstado('Respuesta inesperada');
                            alertify.error('Respuesta inesperada del servidor');
                        }
                    }).fail(function() {
                        setEstado('Fallo de red o servidor');
                        alertify.error('Fallo de red o servidor');
                    }).always(function() {
                        showSpinner(false);
                    });
                },
                function() {
                    alertify.error('Cancelado');
                }
            ).set('labels', {
                ok: 'Sí',
                cancel: 'No'
            });
        });

        // ======= PROBAR CSD (si hay archivos seleccionados -> probar esos; si no, probar instalados) =======
        $('#btnProbarCsd').on('click', function() {
            alertify.confirm('Probar CSD', 'Se realizará el diagnóstico del CSD. ¿Continuar?',
                function() {
                    showSpinner(true);

                    const tipo = $('input[name="tipoCsd"]:checked').val();
                    const pass = $('#csdPass').val().trim();
                    const tienePfx = $('#csdPfx')[0] && $('#csdPfx')[0].files.length > 0;
                    const tieneCer = $('#csdCer')[0] && $('#csdCer')[0].files.length > 0;
                    const tieneKey = $('#csdKey')[0] && $('#csdKey')[0].files.length > 0;

                    // Si el usuario seleccionó archivos, probamos esos SIN instalar
                    if ((tipo === 'pfx' && tienePfx) || (tipo === 'cerkey' && tieneCer && tieneKey)) {
                        const fd = new FormData();
                        fd.append('modo', 'probar_archivos'); // le decimos al backend que pruebe archivos ad-hoc
                        fd.append('tipo', tipo);
                        fd.append('pass', pass);

                        if (tipo === 'pfx') {
                            fd.append('pfx', $('#csdPfx')[0].files[0]);
                        } else {
                            fd.append('cer', $('#csdCer')[0].files[0]);
                            fd.append('key', $('#csdKey')[0].files[0]);
                        }

                        $.ajax({
                                url: 'ajax/timbrado/diagnosticar-csd.php',
                                method: 'POST',
                                data: fd,
                                processData: false,
                                contentType: false
                            }).done(renderDiagnostico)
                            .fail(function() {
                                setEstado('Fallo de red o servidor');
                                alertify.error('Fallo de red o servidor');
                            })
                            .always(function() {
                                showSpinner(false);
                            });

                    } else {
                        // De lo contrario, probamos los PEM ya instalados en el servidor
                        $.ajax({
                                url: 'ajax/timbrado/diagnosticar-csd.php',
                                method: 'POST',
                                data: {
                                    modo: 'probar_instalado'
                                }
                            }).done(renderDiagnostico)
                            .fail(function() {
                                setEstado('Fallo de red o servidor');
                                alertify.error('Fallo de red o servidor');
                            })
                            .always(function() {
                                showSpinner(false);
                            });
                    }
                },
                function() {
                    /* cancelado */ }
            ).set('labels', {
                ok: 'Sí',
                cancel: 'No'
            });

            function renderDiagnostico(resp) {
                try {
                    const r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                    if (!r.success) {
                        setEstado(r.msg || 'Error en diagnóstico');
                        alertify.error(r.msg || 'Error en diagnóstico');
                        return;
                    }
                    const bits = [];
                    if (r.rfc_cert) bits.push('RFC: ' + r.rfc_cert);
                    if (r.nocert) bits.push('NoCert/serial: ' + r.nocert);
                    if (r.valid_from) bits.push('Vigencia: ' + r.valid_from + ' → ' + r.valid_to);
                    if (typeof r.pair_ok !== 'undefined') bits.push('Par CER/KEY: ' + (r.pair_ok ? 'OK' : 'NO'));
                    if (typeof r.sign_ok !== 'undefined' || typeof r.verify_ok !== 'undefined') {
                        bits.push('Firma: ' + (r.sign_ok && r.verify_ok ? 'OK' : 'FALLO'));
                    }
                    setEstado(bits.join(' | ') || 'OK');
                    alertify.success('Diagnóstico completo');
                } catch (e) {
                    console.error(resp);
                    setEstado('Respuesta inesperada');
                    alertify.error('Respuesta inesperada');
                }
            }
        });

        // Reset de estado al cambiar entradas
        $('#csdPfx, #csdCer, #csdKey, #csdPass').on('input change', function() {
            setEstado('—');
        });
    });
</script>