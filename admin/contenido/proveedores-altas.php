<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="clientes">Inicio</a></li>
            <li class="breadcrumb-item"><a href="proveedores">Proveedores</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Alta de proveedor</li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="d-flex justify-content-center align-items-center w-100">
            <div class="card col-12 col-md-8">
                <div class="card-header">
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- Form -->
                    <form id="formProveedor" method="post" action="">
                        <div class="row g-3">

                            <div class="form-group">
                                <div class="form-outline">
                                    <input type="text" name="razonSocial" id="razonSocial" class="form-control" />
                                    <label class="form-label" for="razonSocial">Razón Social</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-outline">
                                    <input type="text" name="nombreComercial" id="nombreComercial" class="form-control" />
                                    <label class="form-label" for="nombreComercial">Nombre Comercial</label>
                                </div>
                            </div>


                            <div class="row g-3">
                                <div class="form-group col-12 col-md-4">
                                    <div class="form-outline">
                                        <input type="text" name="telefono" id="telefono" class="form-control" />
                                        <label class="form-label" for="telefono">Teléfono</label>
                                    </div>
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <div class="form-outline">
                                        <input type="email" name="correo" id="correo" class="form-control" />
                                        <label class="form-label" for="correo">Correo Electrónico</label>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="form-outline">
                                    <input type="text" name="calle" id="calle" class="form-control" />
                                    <label class="form-label" for="calle">Calle</label>
                                </div>
                            </div>

                            <div>
                                <div class="row g-3">
                                    <div class="form-group col-12 col-md-6">
                                        <div class="form-outline">
                                            <input type="text" name="numExt" id="numExt" class="form-control" />
                                            <label class="form-label" for="numExt">Número Exterior</label>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <div class="form-outline">
                                            <input type="text" name="numInt" id="numInt" class="form-control" />
                                            <label class="form-label" for="numInt">Número Interior</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div>
                                <div class="row g-3">
                                    <div class="form-group col-12 col-md-6">
                                        <select name="id_estado" id="edo" class="form-select" data-mdb-select-init onchange="fnMunicipio();">
                                            <option value="">Selecciona un Estado</option>
                                            <?php
                                            $con = "SELECT * FROM estados";
                                            $rs = $clsConsulta->consultaGeneral($con);
                                            foreach ($rs as $v => $val) {
                                                echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div id="mpo" class="col-12 col-md-6">
                                        <div class="form-group">
                                            <!--label form="id_municipio">Municipio</label-->
                                            <select name="id_municipio" id="id_municipio" class="form-select" onchange="fnCp();">
                                                <option value="">Selecciona un Municipio</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="row g-3">
                                    <div class="form-group col-12 col-md-3">
                                        <div class="form-outline" data-mdb-input-init>
                                            <input type="text" id="cp" list="cplist" name="cp" class="form-control" onblur="fnColonias();" autocomplete="off" />
                                            <label class="form-label" for="cp">Código Postal</label>
                                        </div>
                                        <datalist id="cplist">
                                        </datalist>
                                    </div>
                                    <div class="form-group col-12 col-md-9">
                                        <div class="form-outline" data-mdb-input-init>
                                            <input type="text" id="colonia" list="colonialist" name="colonia" class="form-control" autocomplete="off" />
                                            <label class="form-label" for="colonia">Colonia</label>
                                        </div>
                                        <datalist id="colonialist">
                                        </datalist>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="row g-3">
                                    <div class="form-group col-12 col-md-4">
                                        <div class="form-outline">
                                            <input type="text" name="telContacto" id="telContacto" class="form-control" />
                                            <label class="form-label" for="telContacto">Teléfono de Contacto</label>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <div class="form-outline">
                                            <input type="text" name="contacto" id="contacto" class="form-control" />
                                            <label class="form-label" for="contacto">Nombre de Contacto</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-outline">
                                    <input type="text" name="mapa" id="mapa" class="form-control" oninput="this.value = this.value.toLowerCase();" />
                                    <label class="form-label" for="mapa">Mapa</label>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary"> Guardar </button>
                                <input type="hidden" name="accion" value="ALTA">
                            </div>

                        </div>
                    </form>
                    <!-- /. From -->
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    // Aplica la máscara para el teléfono de contacto
    IMask(
        document.getElementById('telefono'), {
            mask: '000-000-0000'
        }
    );

    IMask(
        document.getElementById('telContacto'), {
            mask: '000-000-0000'
        }
    );

    // Función para obtener los municipios según el estado
    function fnMunicipio() {
        var id_estado = $("#edo").val();
        console.log(id_estado);
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/municipios.php",
            data: {
                id_estado: id_estado
            },
            success: function(data) {
                if (data == 1) {
                    alert("Fallo el servidor al obtener los municipios");
                } else {
                    $("#mpo").html(data);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    // Función para obtener el código postal dependiendo del municipio
    function fnCp() {
        var municipio_id = $("#id_municipio").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/obten-cp.php",
            data: {
                municipio_id: municipio_id
            },
            success: function(data) {
                $("#cp").val(data); // Asume que el servidor retorna solo el código postal
            }
        });
    }

    function fnColonias() {
        cp = $("#cp").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/obten-colonias.php",
            data: {
                cp: cp
            },
            success: function(data) {
                // console.log(data);
                $("#colonialist").html(data);
            }
        });
    }

    function fnCp() {
        municipio_id = $("#id_municipio").val();
        $.ajax({
            type: "POST",
            url: "ajax/proveedores/obten-cp.php",
            data: {
                municipio_id: municipio_id
            },
            success: function(data) {
                //console.log(data);
                $("#cplist").html(data);
            }
        });
    }

    // Validación del formulario para el alta de proveedor
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
            "Formato inválido."
        );

        $("#formProveedor").validate({
            rules: {
                razonSocial: {
                    required: true,
                    minlength: 4,
                    maxlength: 200
                },
                /*
                telefono: {
                    required: true,
                    maxlength: 12,
                    minlength: 12
                },
                correo: {
                    required: true,
                    email: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                    remote: {
                        url: "ajax/proveedores/busca-correo.php",
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
                                    return `"El correo ya fue capturado en ${json.nombre}"`; // Indica que el correo ya existe
                                } else {
                                    return "true"; // Indica que el correo no existe
                                }
                            } catch (e) {
                                console.error('Error parsing JSON response:', e);
                                return false; // Indica error de validación
                            }
                        }
                    }
                },
                */
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
                razonSocial: {
                    required: "Campo obligatorio",
                    minlength: "Escribe más de 4 caracteres",
                    maxlength: "No debe exceder los 200 caracteres"
                },
                telefono: {
                    required: "Campo obligatorio",
                    maxlength: "Teléfono debe de contener 10 números",
                    minlength: "Teléfono debe de contener 10 números"
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
                            url: "ajax/proveedores/guardar.php",
                            data: datos,
                            success: function(data) {
                                // console.log(data);
                                if (data == 1) {
                                    alert("Fallo el servidor al guardar los datos");
                                } else {
                                    // Redirige a la página de proveedores después de guardar
                                    location.href = "proveedores";
                                }
                            }
                        });
                    },
                    function() {
                        alertify.error('Cancelado');
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });
            }
        });
    });
</script>