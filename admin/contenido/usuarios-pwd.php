<?PHP
$con = "SELECT * FROM usuarios WHERE id=" . $cat;
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $v => $val) {
    $usuario = $val['nombre']; // . ' ' . $val['apellido1'] . ' ' . $val['apellido2'];
    $usr = (isset($val['usr'])) ? $val['usr'] : '';
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Usuario y Contraseña</li>
        </ol>
    </nav>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-lg-center">
            <div class="col-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Usuario y Contraseña de <b><?PHP echo $usuario; ?></b></h3>
                    </div>
                    <div class="card-body">
                        <form action="" id="formpwd" method="post">
                            <h6 class="text-center">Ingresa el usuario y la contraseña</h6>
                            <div class="form-group">
                                <label for="usr">Usuario</label>
                                <input type="email" id="mail" name="usr" class="form-control" placeholder="Correo electrónico" value="<?PHP echo $usr; ?>">
                                <input type="hidden" id="mailanterior" name="usr2" value="<?PHP echo $usr; ?>">
                            </div>
                            <div class="form-group mt-4">
                                <label for="pwd">Contraseña</label>
                                <input type="password" name="pwd" id="pwd" class="form-control" placeholder="Contraseña" title="Debe contener al menos una letra mayúscula, un número y al menos 5 o más caracteres" required="required">
                            </div>
                            <div class="form-group mt-4">
                                <label for="pwd2">Repite la contraseña</label>
                                <input type="password" name="pwd2" id="pwd2" class="form-control" placeholder="Repite la Contraseña">
                            </div>
                            <div class="row mt-4">
                                <small class="text-dark"><b>La contraseña Debe contener al menos una letra mayúscula, un número y al menos 6 caracteres o más </b></small>
                            </div>
                            <div class="row">
                                <div class="col-8 mt-5">
                                    <div>
                                        <button type="button" class="btn btn-secondary" id="abremodalpwd"> Genera Contraseña
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                                     <input type="hidden" name="id" value="<?PHP echo $cat; ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalpwd" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Generar contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="text" class="form-control" id="pass">
                </div>
                <div class="form-group mt-3 text-end">
                    <button type="button" class="btn btn-secondary" id="newpwd">Generar nueva</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelarpwd" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="aplicarpwd" class="btn btn-primary">Aplicar</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $(".btn-close").click(function(){
            $('#modalpwd').modal('hide');
        });

        $("#cancelarpwd").click(function(){
            $('#modalpwd').modal('hide');
        });

        $("#abremodalpwd").click(function(){
            if($("#mail").val()==''){
                alertify.alert('Aviso..!', 'Primero tiene que capturar el correo', function(){ /* alertify.success('Ok'); */ });
            } else {
                $.ajax({
                    type: "POST",
                    url: "ajax/usuarios/genera-pwd.php",
                    data: {mail:function(){
                        return $("#mail").val();
                    }},
                    success: function(data) {
                        $("#pass").val(data);
                        $('#modalpwd').modal('show');
                    }
                }); /* End Ajax */
            }
        });

        $("#aplicarpwd").click(function(){
            alertify.confirm('Aviso..!', '¿Deseas Aplicar esta Contraseña?',
                function(){
                    var pwd = $("#pass").val();
                    $("#pwd").val(pwd);
                    $("#pwd2").val(pwd);
                    $('#modalpwd').modal('hide');
                },
                function(){ alertify.error('Cancel')}
            ); /* End Alertify*/
        });

        $("#newpwd").click(function(){
            $.ajax({
                type: "POST",
                url: "ajax/usuarios/genera-pwd.php",
                data: {mail:function(){
                    return $("#mail").val();
                }},
                success: function(data) {
                    console.log(data);
                    $("#pass").val(data);
                }
            }); /* End Ajax */
        });

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

        $("#formpwd").validate({
            rules: {
                usr: {
                    required: true,
                    email: true,
                    maxlength: 50,
                    regex: /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/
                },
                pwd: {
                    required: true,
                    regex: /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}/
                },
                pwd2: {
                    required: true,
                    equalTo: "#pwd"
                }
            },
            messages: {
                usr: {
                    required: "Escribe un correo",
                    email: "Tu correo necesita un formato nombre@dominio.com",
                    maxlength: "El tamaño máximo es de 50 caracteres",
                    remote: "El correo ya existe"
                },
                pwd: {
                    required: "Escriba una contraseña"
                },
                pwd2: {
                    required: "Repite la contraseña",
                    equalTo: "Las contraseñas no coinciden"
                }
            },
            errorElement : 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                error.addClass('text-danger');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('text-danger d-flex justify-content-end');
            },
            submitHandler: function(form){
                var datos = $("#formpwd").serialize();
                alertify.confirm('Aviso..!', '¿Deseas Guardar los cambios?',
                    function(){
                        $.ajax({
                            type: "POST",
                            url: "ajax/usuarios/modifica-pwd.php",
                            data: datos,
                            success: function(data) {
                                console.log(data);
                                if(data==1){
                                    alert("Fallo el server");
                                } else {
                                    // location.reload();
                                    location.href = "usuarios";
                                }
                            }
                        }); /* End Ajax */
                    },
                    function(){ alertify.error('Cancel')}
                );
            }
        });
    });
</script>