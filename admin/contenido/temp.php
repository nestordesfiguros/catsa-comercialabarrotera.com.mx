<div id="mpo" class="col-6">
    <div class="form-group">
        <!--label form="id_municipio">Municipio</label-->
        <select name="id_municipio" class="form-select" onchange="fnCp();">
            <option value="">Selecciona un Municipio</option>
        </select>
    </div>
</div>

<div class="row gy-3">
    <div class="form-group col-sm-12 col-lg-2">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="cp" list="cplist" name="cp" class="form-control" onblur="fnColonias();" autocomplete="off" />
            <label class="form-label" for="cp">Código Postal</label>
        </div>
        <datalist id="cplist"></datalist>
    </div>
    <div class="form-group col-sm-12 col-lg-6">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="colonia" list="colonialist" name="colonia" class="form-control" autocomplete="off" />
            <label class="form-label" for="colonia">Colonia</label>
        </div>
        <datalist id="colonialist"></datalist>
    </div>
    <div class="form-group col-sm-12 col-lg-4">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="localidad" name="localidad" class="form-control" />
            <label class="form-label" for="localidad">Localidad</label>
        </div>
    </div>
</div>

<div class="row gy-3">
    <div class="form-group col-4">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="contacto" name="contacto" class="form-control" />
            <label class="form-label" for="contacto">Contacto (Nombre)</label>
        </div>
    </div>
    <div class="form-group col-4">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="tel1" name="tel1" class="form-control text-end" />
            <label class="form-label" for="tel1">Teléfono</label>
        </div>
    </div>
    <div class="form-group col-4">
        <div class="form-outline" data-mdb-input-init>
            <input type="text" id="tel2" name="tel2" class="form-control text-end" />
            <label class="form-label" for="tel2">Teléfono</label>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="form-outline" data-mdb-input-init>
        <input type="text" id="mapa" name="mapa" class="form-control" />
        <label class="form-label" for="mapa">Mapa</label>
    </div>
</div>

<div class="card-footer text-end">
    <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
    <input type="hidden" name="idestado" id="idestado">
    <input type="hidden" name="accion" value="ALTA">
</div>

<script>
    IMask(
        document.getElementById('tel1'), {
            mask: '000-000-0000'
        }
    );

    IMask(
        document.getElementById('tel2'), {
            mask: '000-000-0000'
        }
    );

    function fnMunicipio() {
        var id_estado = $("#edo").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/municipios.php", // Cambié la URL para proveedores
            data: {
                id_estado: id_estado
            },
            success: function(data) {
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    $("#mpo").html(data);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function fnColonias() {
        cp = $("#cp").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/obten-colonias.php", // Cambié la URL para proveedores
            data: {
                cp: cp
            },
            success: function(data) {
                $("#colonialist").html(data);
            }
        });
    }

    function fnCp() {
        municipio_id = $("#id_municipio").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/obten-cp.php", // Cambié la URL para proveedores
            data: {
                municipio_id: municipio_id
            },
            success: function(data) {
                $("#cplist").html(data);
            }
        });
    }

    $(document).ready(function() {

        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                if (regexp.constructor != RegExp)
                    regexp = new RegExp(regexp);
                else if (regexp.global)
                    regexp.lastIndex = 0;
                return this.optional(element) || regexp.test(value);
            },
            "Revisa ."
        );

        $("#formProveedor").validate({ // Cambié el ID del formulario para proveedores
            rules: {
                razonSocial: {
                    required: true,
                    minlength: 4,
                    maxlength: 200
                },
                correo: {
                    required: true,
                    email: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                    remote: {
                        url: "ajax/proveedores/busca-correo.php", // Cambié la URL para proveedores
                        type: "POST",
                        data: {
                            correo: function() {
                                return $('#correo').val();
                            }
                        },
                        dataFilter: function(data) {
                            try {
                                var json = JSON.parse(data);
                                if (json.existe) {
                                    return `"El correo ya fue capturado en ${json.nombre}"`; 
                                } else {
                                    return "true"; 
                                }
                            } catch (e) {
                                console.error('Error parsing JSON response:', e);
                                return false;
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                            return false;
                        }
                    }
                },
                tel1: {
                    maxlength: 12,
                    minlength: 12
                },
                tel2: {
                    maxlength: 12,
                    minlength: 12,
                },
                calle: {
                    required: true
                },
                numExt: {
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
                razonSocial: {
                    required: "Campo obligatorio",
                    minlength: "Escribe más de 4 caracteres"
                },
                correo: {
                    required: "Campo obligatorio",
                    email: "Por favor, escribe una dirección de correo válida",
                    regex: "Formato de correo no válido",
                    remote: "El correo ya fue capturado"
                },
                calle: {
                    required: "Campo obligatorio",
                },
                numExt: {
                    required: "Campo obligatorio",
                },
                colonia: {
                    required: "Campo obligatorio",
                },
                tel1: {
                    maxlength: "Teléfono debe de contener 10 números",
                    minlength: "Teléfono debe de contener 10 números"
                },
                tel2: {
                    maxlength: "Teléfono debe de contener 10 números",
                    minlength: "Teléfono debe de contener 10 números"
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
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
            },

            submitHandler: function(form) {
                alertify.confirm('Aviso..!', '¿Deseas guardar los datos?',
                    function() {
                        var datos = $("#formProveedor").serialize();
                        $.ajax({
                            type: "POST",
                            url: "ajax/proveedores/guarda-proveedores.php", // Cambié la URL para proveedores
                            data: datos,
                            success: function(data) {
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    location.href = "proveedores"; // Redirigir a la lista de proveedores
                                }
                            }
                        });
                    },
                    function() {
                        alertify.error('Cancelado')
                    }
                ).set('labels', {
                    ok: 'Sí',
                    cancel: 'No'
                });
            }
        });
    });
</script>
