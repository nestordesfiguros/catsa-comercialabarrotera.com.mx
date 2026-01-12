<?php 
$con="SELECT * FROM cat_almacenes WHERE id=".$cat;
$rs = $clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $id = $val['id'];
    $almacen = $val['almacen'];
    $calle = $val['calle'];
    $num_ext = $val['num_ext'];
    $num_int = $val['num_int'];
    $cp = $val['cp'];
    $estado_id = $val['estado_id'];
    $municipio_id = $val['municipio_id'];
    $estatus = $val['estatus'];
    $fecha_alta = $val['fecha_alta'];
    $mapa = $val['mapa'];
    $id_usuario = $val['id_usuario'];
}
?>
<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="almacenes">Almacenes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Alta </li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <form method="post" action="" id="formAlmacen"> <!-- Form -->
        <div class="row">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-8">

                    <div class="card-header">

                    </div>

                    <!-- /.card-header -->
                    <div class="card-body">

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group  g-3">
                                    <div class="form-outline">
                                        <input type="text" name="almacen" id="almacen" class="form-control" value="<?php echo $almacen; ?>" />
                                        <label class="form-label" for="almacen">Nombre del Almacén</label>
                                    </div>
                                </div>
                                <div class="form-group  g-3">
                                    <div class="form-outline">
                                        <input type="text" name="calle" id="calle" class="form-control" value="<?php echo $calle; ?>" />
                                        <label class="form-label" for="calle">Calle</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-6 g-3">
                                            <div class="form-outline">
                                                <input type="text" name="num_ext" id="num_ext" class="form-control" value="<?php echo $num_ext; ?>" />
                                                <label class="form-label" for="num_ext">Número Exterior</label>
                                            </div>
                                        </div>
                                        <div class="col-6 g-3">
                                            <div class="form-outline">
                                                <input type="text" name="num_int" id="num_int" class="form-control"  value="<?php echo $num_int; ?>" />
                                                <label class="form-label" for="num_int">Número Interior</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group  g-3">
                                    <div class="form-outline">
                                        <input type="text" name="cp" id="cp" class="form-control" value="<?php echo $cp; ?>" />
                                        <label class="form-label" for="cp">Código Postal</label>
                                    </div>
                                </div>
                                <div class="form-group g-3">

                                    <select id="edo" name="estado_id" class="form-select" onchange="fnMunicipio();">                                        
                                        <?php
                                        $con = "SELECT * FROM estados  ORDER BY nombre ASC";
                                        $rs = $clsConsulta->consultaGeneral($con);
                                        foreach ($rs as $v => $val) {
                                            if($val['id']== $estado_id){
                                                echo '<option value="' . $val['id'] . '" selected>' . $val['nombre'] . '</option>';
                                            }else{
                                                echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>

                                </div>
                                <div class="form-group g-3">

                                    <select name="municipio_id" id="mpo" class="form-select" >
                                    <?php
                                        $con = "SELECT * FROM municipios WHERE estado_id = ".$estado_id." ORDER BY nombre ASC";
                                        $rs = $clsConsulta->consultaGeneral($con);
                                        foreach ($rs as $v => $val) {
                                            if($val['id']== $municipio_id){
                                                echo '<option value="'.$val['id'].'"  selected>'.$val['nombre'].'</option>';
                                            }else{
                                                echo '<option value="'.$val['id'].'"  >'.$val['nombre'].'</option>';
                                            }
                                        }
                                    ?>
                                    </select>

                                </div>

                                <div class="form-group g-3">
                                    <div class="form-outline">
                                        <input type="text" name="mapa" id="mapa" class="form-control " />
                                        <label class="form-label" for="mapa">Link Mapa</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-group text-end mt-3">
                            <!--button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalAltas();">Cerrar</button-->
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <input type="hidden" name="id" value="<?php echo $cat; ?>" />
                            <input type="hidden" name="id_usuario" value="<?php echo $_SESSION["id_user"] ?>" />
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form> <!-- /. Form -->
</section>

<script>
    function fnMunicipio() {
        console.log('Entro');        
        var idEstado = $("#edo").val();
        
        $.ajax({
            type: "POST",
            url: "ajax/configuracion/municipios.php",
            data: {
                idEstado: idEstado
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
            "Revisa que el RFC tenga un formato valido"
        );

        $("#formAlmacen").validate({
            rules: {
                almacen: {
                    required: true,
                    minlength: 8,
                    //      regex: /^[A-ZÑa-zñáéíóúÁÉÍÓÚ'°., ]+$/,  // Solo letras y espacios
                    /*
                    remote: {
                        url: "ajax/almacenes/busca-almacen.php",
                        type: "POST",
                        data: {
                            almacen: function() {
                                return $('#almacen').val();
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            if (json.almacen === "true" || json.almacen === true) {
                                return '"true"';
                            } else {
                                return '"El almacen ya existe"';
                            }
                        }
                    }
                        */
                },
                calle: {
                    required: true,
                    minlength: 5,
                    maxlength: 100
                },
                num_ext: {
                    minlength: 1,
                    required: true,
                    maxlength: 50
                },
                num_int: {
                    maxlength: 50
                },
                cp: {
                    required: true,
                    maxlength: 5,
                    minlength: 5
                },
                rfc: {
                    required: true,
                    maxlength: 13,
                    minlength: 12,
                    regex: /^[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{2}[0-9A]$/ /* Valida RFC*/
                }
            },
            messages: {
                almacen: {
                    required: "Campo obligatorio",
                    regex: "Escribe solo letras y espacios",
                    //remote: "Razón Social ya existe",
                    minlength: "Escriba más de 8 caracteres"
                },
                calle: {
                    required: "Campo obligatorio",
                    minlength: "Escriba más de 5 caracteres"
                },
                num_ext: {
                    required: "Campo obligatorio",
                    minlength: "Escriba más de 5 caracteres"
                },
                cp: {
                    required: "Campo obligatorio",
                    minlength: "Escriba 5 números",
                    maxlength: "Escriba 5 números",
                },
                rfc: {
                    required: "Campo obligatorio",
                    maxlength: "El máximo de caractes es de 13",
                    minlength: "El mínimo de caracteres es de 12"
                    //  regex: "El RFC No tiene un formato valido"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.addClass(' text-danger');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('text-danger d-flex justify-content-end');
            },
            submitHandler: function(form) {
                //submit form
                event.preventDefault();
                var datos = $("#formAlmacen").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar los datos capturados?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/almacenes/modifica-almacen.php",
                            data: datos,
                            success: function(data) {
                                console.log(data);
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    location.href = "almacen";
                                    //   location.reload();    
                                   
                                }
                            }
                        }); /* End ajax */
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
    });
</script>