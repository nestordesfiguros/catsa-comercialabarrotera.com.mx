<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = $_POST['id'];
$con = "SELECT * FROM usuarios WHERE id=" . $id;
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $v => $val) {
    $id = $val['id'];
    /*   $usr = $val['usr'];
     $pwd = $val['pwd'];  */
    $nombre = $val['nombre'];
    $apellido1 = $val['apellido1'];
    $apellido2 = $val['apellido2'];
    $clave = $val['clave'];
    $usr = $val['usr'];
    $id_puesto = $val['id_puesto'];
    $modifica = $val['modifica'];
    $nivel = $val['nivel'];
    $estatus = $val['estatus'];
    $empresa = $val['empresa'];
    $foto = $val['foto'];
    $fecha_alta = $val['fecha_alta'];
}

?>


<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Modificar usuario</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="post" id="formUsuariosEditar" action=" ">
    <div class="modal-body">

        <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" id="nombre" class="form-control" name="nombre" value="<?php echo $nombre; ?>">
        </div>

        <!-- <div class="form-group mt-4">
            <label>Apellido1</label>
            <input type="text" class="form-control " name="apellido1" value="<?php echo $apellido1; ?>">
        </div>

        <div class="form-group mt-4">
            <label>Apellido2</label>
            <input type="text" class="form-control " name="apellido2" value="<?php echo $apellido2; ?>">
        </div> -->

        <div class="form-group">
            <label>Correo</label>
            <input type="email" id="usr" class="form-control minusculas" name="usr" value="<?php echo $usr; ?>">
        </div>

        <!-- <div class="form-group mt-4">
            <label>Puesto</label>
            <select id="id_puesto" name="id_puesto" class="form-control validate[required]">
                <?php
                /*
                $con = "SELECT * FROM cat_puestos WHERE estatus = 1 ORDER BY puesto ASC";
                $rs = $clsConsulta->consultaGeneral($con);
                foreach ($rs as $v => $val) {
                    if ($id_puesto == $val['id']) {
                        echo '<option value="' . $val['id'] . '" selected>' . $val['puesto'] . '</option>';
                    } else {
                        echo '<option value="' . $val['id'] . '">' . $val['puesto'] . '</option>';
                    }
                }
                    */
                ?>

            </select>
        </div> -->

    </div> <!-- /. Modal Body -->

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
        <input type="hidden" name="fin" value="borrar">
        <input type="hidden" name="url" value="accesos">
        <input type="hidden" name="tabla" value="usuarios">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
    </div>
</form>

<script>
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
            "Please check your input."
        );


        $("#formUsuariosEditar").validate({
            rules: {
                nombre: {
                    //    required: true,
                    minlength: 3,
                    //   regex: /^[a-zA-Z\s]*$/
                },
                apellido1: {
                    required: true,
                    minlength: 5,
                    //   regex: /^[a-zA-Z\s]*$/
                },
                usr: {
                    required: true,
                    email: true,
                    maxlength: 50,
                    /*
              remote: {
                  url:"ajax/usuarios/verificacorreo.php",              
                  type: 'POST',
                  data: {
                      correo: function() {                           
                          return $('#usr').val();
                      }                        
                    },                  
                  dataFilter: function(data){                      
                    //  console.log(data);
                      var json = JSON.parse(data);                                        
                      if(json.existe=="true" || json.existe==true){                   
                          return false;
                      }else{
                          return true;
                      }
                  }                  
               } */
                }
            },
            messages: {
                nombre: {
                    required: "Escribe un nombre",
                    regex: "Solo acepta letras y espacios"
                },
                apellido1: {
                    required: "Escribe un Apellido",
                    minlength: "Escriba más de 5 caracteres",
                    regex: "Solo acepta letras y espacios"
                },

                usr: {
                    required: "Escribe un correo",
                    email: "Tu correo necesita un formato nombre@dominio.com",
                    maxlength: "El tamaño máximo es de 50 caracteres",
                    remote: "El correo ya existe"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.addClass('btn btn-danger btn-sm text-white');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
            },

            submitHandler: function(form) {
                //submit form
                event.preventDefault();
                var datos = $("#formUsuariosEditar").serialize();


                alertify.confirm('Aviso..!', '¿Deseas Guardar los cambios?',
                    function() {
                        //    alertify.success('Ok')                         
                        $.ajax({
                            type: "POST",
                            url: "ajax/usuarios/guardar-modifica-usuarios.php",
                            data: datos,
                            success: function(data) {
                                console.log(data);
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    location.reload();
                                }
                            }
                        }); /* End Ajax */

                    },
                    function() {
                        alertify.error('Cancel')
                    }
                );




            }
        });


    });
</script>