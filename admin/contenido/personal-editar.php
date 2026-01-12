<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>
<!-- Content Header (Page header) -->
<?php

$con="SELECT * FROM cat_vendedores WHERE id=".$cat;
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
     $id = $val['id'];
     $nombre = $val['nombre'];
     $apellido1 = $val['apellido1'];
     $apellido2 = $val['apellido2'];
     $curp = $val['curp'];
     $calle = $val['calle'];
     $num_ext = $val['num_ext'];
     $num_int = $val['num_int'];
     $colonia = $val['colonia'];
     $cp = $val['cp'];
     $id_municipio = $val['id_municipio'];
     $id_estado = $val['id_estado'];
     $localidad = $val['localidad'];
     $tel = $val['tel'];
     $contacto = $val['contacto'];
     $telcontacto = $val['telcontacto'];     
     $usr = $val['usr'];
     $pwd = $val['pwd'];          
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="vendedores">Vendedores</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Vendedores Editar</li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="row">

        <form method="post" id="formVendedores" action="">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-8">
                    <div class="card-body ">

                        <div class="row gy-3 ">
                            <div class="form-group">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo $nombre; ?>" />
                                    <label class="form-label" for="nombre">Nombre</label>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="apellido1" name="apellido1" class="form-control"  value="<?php echo $apellido1; ?>"/>
                                    <label class="form-label" for="apellido1">1er. Apellido</label>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="apellido2" name="apellido2" class="form-control"  value="<?php echo $apellido2; ?>"/>
                                    <label class="form-label" for="apellido2">2do Apellido</label>
                                </div>
                            </div>

                            <!-- <div class="row gy-2">
                                <div class="form-group col-6">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="rfc" name="rfc" class="form-control" />
                                        <label class="form-label" for="rfc">RFC</label>
                                    </div>
                                </div>
                                <div class="form-group col-6">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="correo" name="correo" class="form-control" />
                                        <label class="form-label" for="correo">Correo</label>
                                    </div>
                                </div>
                            </div> -->

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

                            <div gy-3>
                                <div class="row">
                                    <div class="form-group col-6">
                                        <!--label class="form-label select-label">Estado</label-->
                                        <select name="id_estado" id="edo" class="form-select" data-mdb-select-init onchange="fnMunicipio();">
                                            <?php
                                            $con = "SELECT * FROM estados";
                                            $rs = $clsConsulta->consultaGeneral($con);
                                            foreach ($rs as $v => $val) {
                                                if($id_estado == $val['id']){
                                                    echo '<option value="' . $val['id'] . '" selected >' . $val['nombre'] . '</option>';
                                                }else{
                                                    echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>

                                    </div>
                                    <div id="mpo" class="col-6">
                                        <div class="form-group">
                                            <!--label form="id_municipio">Municipio</label-->
                                            <select name="id_municipio" class="form-select" onchange="fnCp();">
                                            <?php
                                            $con = "SELECT * FROM municipios";
                                            $rs = $clsConsulta->consultaGeneral($con);
                                            foreach ($rs as $v => $val) {
                                                if($id_municipio == $val['id']){
                                                    echo '<option value="' . $val['id'] . '" selected >' . $val['nombre'] . '</option>';
                                                }else{
                                                    echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                                }
                                            }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row gy-3 ">
                                <div class="form-group col-sm-12 col-lg-2   ">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="cp" list="cplist" name="cp" class="form-control" onblur="fnColonias();" value="<?php echo $cp; ?>" />
                                        <label class="form-label" for="cp">Código Postal</label>
                                    </div>
                                    <datalist id="cplist">
                                    </datalist>
                                </div>
                                <div class="form-group col-sm-12 col-lg-6">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="colonia" list="colonialist" name="colonia" class="form-control" value="<?php echo $colonia; ?>" />
                                        <label class="form-label" for="colonia">Colonia</label>
                                    </div>
                                    <datalist id="colonialist">
                                    </datalist>
                                </div>
                                <div class="form-group col-sm-12 col-lg-4">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="localidad" name="localidad" class="form-control" value="<?php echo $localidad; ?>" />
                                        <label class="form-label" for="localidad">Localidad</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row gy-3">

                            <div class="form-group col-4">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="tel" name="tel" class="form-control text-end" value="<?php echo $tel; ?>" />
                                        <label class="form-label" for="tel">Teléfono</label>
                                    </div>
                                </div>

                                <div class="form-group col-4">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="contacto" name="contacto" class="form-control"  value="<?php echo $contacto; ?>" />
                                        <label class="form-label" for="contacto">Contacto (Nombre)</label>
                                    </div>
                                </div>
                                
                                <div class="form-group col-4">
                                    <div class="form-outline" data-mdb-input-init>
                                        <input type="text" id="telcontacto" name="telcontacto" class="form-control text-end" value="<?php echo $telcontacto; ?>" />
                                        <label class="form-label" for="telcontacto">Teléfono Contacto</label>
                                    </div>
                                </div>
                            </div>                            

                        </div>
                    </div>
                    <div class="card-footer text-end">                        
                        <button type="submit" class="btn btn-primary"> Guardar </button>
                        <input type="hidden" name="idestado" id="idestado">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    </div>
                </div>
                
            </div>
        </form>
    </div>
</section>

<script>
    IMask(
        document.getElementById('tel'), {
            mask: '000-000-0000'
        }
    )

    IMask(
        document.getElementById('telcontacto'), {
            mask: '000-000-0000'
        }
    )

    function fnMunicipio() {
        var id_estado = $("#edo").val();
        //console.log(id_estado);
        $.ajax({
            type: "POST",
            url: "ajax/vendedores/municipios.php",
            data: {
                id_estado: id_estado
            },
            success: function(data) {
                // console.log(data);
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    $("#mpo").html(data);
                }
            },
            error: function(xhr, status, error) {
                // Manejar errores de AJAX
                console.error(error);
            }
        });
    }

    function fnColonias() {
        cp = $("#cp").val();
        $.ajax({
            type: "POST",
            url: "ajax/vendedores/colonias.php",
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
            url: "ajax/vendedores/cp.php",
            data: {
                municipio_id: municipio_id
            },
            success: function(data) {
                //  console.log(data);
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


        $("#formVendedores").validate({
            rules: {
                nombre: {
                    required: true,
                    minlength: 4,
                    maxlength: 200
                },
                // rfc: {
                //     required: true,
                //     minlength: 13,
                //     maxlength: 14
                //     //regex: /^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/
                // },
                // correo: {
                //     required: true,
                //     email: true,
                //     regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                //     remote: {
                //         url: "ajax/Personal/busca-correo.php",
                //         type: "POST",
                //         data: {
                //             correo: function() {
                //                 return $('#correo').val();
                //             }
                //         },
                //         dataFilter: function(data) {
                //             try {
                //                 var json = JSON.parse(data);
                //                 if (json.existe) {
                //                     return `"El correo ya fue capturado en ${json.nombre}"`; // Indica que el correo ya existe
                //                 } else {
                //                     return "true"; // Indica que el correo no existe
                //                 }
                //             } catch (e) {
                //                 console.error('Error parsing JSON response:', e);
                //                 return false; // Indica error de validación
                //             }
                //         },
                //         error: function(jqXHR, textStatus, errorThrown) {
                //             console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                //             return false; // Indica error de validación
                //         }
                //     }
                // },
                contacto: {
                    maxlength: 200,
                    minlength: 5
                },                
                tel: {
                    maxlength: 12,
                    minlength: 12
                },
                telcontacto: {
                    maxlength: 12,
                    minlength: 12,
                },
                calle: {
                    required: true
                },
                num_ext: {
                    required: true
                },
                cp: {
                    minlength: 5,
                    maxlength: 5
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
                nombre: {
                    required: "Campo obligatorio",
                    minlength: "Escribe más de 4 caracteres",
                    regex: "Escribe solo letras y espacios"
                },
                // rfc: {
                //     required: "Campo obligatorio",
                //     maxlength: "El rfc debe de tener 13 o 14 caracteres",
                //     minlength: "El rfc debe de tener 13 o 14 caracteres",
                //     regex: "RFC no valido"
                // },
                // correo: {
                //     required: "Campo obligatorio",
                //     email: "Por favor, escribe una dirección de correo válida",
                //     regex: "Formato de correo no válido",
                //     remote: "El correo ya fue capturado"
                // },
                calle: {
                    required: "Campo obligatorio",
                },
                num_ext: {
                    required: "Campo obligatorio",
                },
                colonia: {
                    required: "Campo obligatorio",
                },
                tel: {
                    maxlength: "Teléfono debe de contener 10 numeros",
                    minlength: "Teléfono debe de contener 10 numeros"
                },
                cp: {
                    maxlength: "Debe de contener 5 Dígitos",
                    minlength: "Debe de contener 5 Dígitos",
                },
                contacto: {
                    maxlength: "Teléfono debe de contener 10 numeros",
                    minlength: "Teléfono debe de contener 200 numeros"
                },
                telcontacto: {
                    maxlength: "Teléfono debe de contener 10 numeros",
                    minlength: "Teléfono debe de contener 10 numeros"
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
                //submit form
                // event.preventDefault();
                alertify.confirm('Aviso..!', '¿Deseas guardar los datos?',
                    function() {

                        var datos = $("#formVendedores").serialize();
                        console.log / ('Entró !');
                        $.ajax({
                            type: "POST",
                            url: "ajax/vendedores/modificar.php",
                            data: datos,
                            success: function(data) {
                               // console.log(data);
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {

                                    // Boora el formulario
                                    //  $("#formVendedores")[0].reset(); //Limpiar el formulario
                                    location.href = "vendedores";
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
        }, );

    });
</script>