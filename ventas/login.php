<?php

session_start();

// Si el usuario ya está logueado, redirigir a welcome.php

if (isset($_SESSION['id_vendedor'])) {

    header("Location: welcome.php");

    exit;
}

?>

<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <!-- Bootstrap 5 CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Alertify CSS -->

    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />

    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css" />



    <!-- jQuery -->

    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>



    <!-- jQuery Validate -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

    <!-- Incluir el archivo de traducción al español -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/localization/messages_es.min.js"></script>



    <!-- Alertify JS -->

    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

</head>



<body>

    <div class="container">

        <div class="row justify-content-center mt-5">

            <div class="col-md-6 col-lg-4">

                <div class="card shadow">

                    <div class="card-body">

                        <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>



                        <?php if (isset($_GET['error'])): ?>

                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>

                        <?php endif; ?>



                        <form id="loginForm" action="" method="POST">

                            <div class="mb-3">

                                <label for="username" class="form-label">Usuario</label>

                                <input type="text" class="form-control" id="username" name="username" required>

                            </div>

                            <div class="mb-3">

                                <label for="password" class="form-label">Contraseña</label>

                                <input type="password" class="form-control" id="password" name="password" required>

                            </div>

                            <div class="d-grid gap-2">

                                <button type="submit" class="btn btn-primary">Ingresar</button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- Bootstrap 5 JS Bundle with Popper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    <script>
        $(document).ready(function() {

            // Configuración de Alertify

            alertify.set('notifier', 'position', 'top-right');



            // Validación del formulario de login

            $("#loginForm").validate({

                rules: {

                    username: {

                        required: true,

                        minlength: 4,

                        maxlength: 150

                    },

                    password: {

                        required: true,

                        minlength: 6,

                        regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/ // Al menos una mayúscula, una minúscula y un número

                    }

                },

                messages: {

                    username: {

                        required: "Por favor ingresa tu nombre de usuario",

                        minlength: "El usuario debe tener al menos 4 caracteres",

                        maxlength: "El usuario no puede exceder los 150 caracteres"

                    },

                    password: {

                        required: "Por favor ingresa tu contraseña",

                        minlength: "La contraseña debe tener al menos 6 caracteres",

                        regex: "La contraseña debe contener al menos una mayúscula, una minúscula y un número"

                    }

                },

                errorElement: 'span',

                errorPlacement: function(error, element) {

                    error.addClass('invalid-feedback');

                    element.closest('.mb-3').append(error);

                },

                highlight: function(element, errorClass, validClass) {

                    $(element).addClass('is-invalid');

                },

                unhighlight: function(element, errorClass, validClass) {

                    $(element).removeClass('is-invalid');

                },

                submitHandler: function(form) {

                    // Mostrar mensaje de carga

                    alertify.message('Verificando credenciales...', 2);



                    // Evitar envío normal del formulario

                    event.preventDefault();



                    var datos = $("#loginForm").serialize();



                    $.ajax({

                        type: "POST",

                        url: "php/verifica.php",

                        data: datos,

                        dataType: "json", // Esperamos una respuesta JSON

                        beforeSend: function() {

                            // Deshabilitar el botón para evitar múltiples envíos

                            $('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verificando...');

                        },

                        success: function(response) {

                            console.log(response);



                            if (response.success) {

                                // Autenticación exitosa

                                alertify.success('Bienvenido ' + response.nombre);

                                setTimeout(function() {

                                    location.href = "inicio";

                                }, 1500);

                            } else {

                                // Error en autenticación

                                alertify.error('Usuario o contraseña incorrectos');
                                // Limpiar campo de contraseña
                                $('#password').val('').focus();



                                // Agregar clase de error

                                $('#username, #password').addClass('is-invalid');



                                // Habilitar el botón nuevamente

                                $('button[type="submit"]').prop('disabled', false).text('Ingresar');

                            }

                        },

                        error: function(xhr, status, error) {

                            console.error(xhr.responseText);

                            alertify.error('Error en el servidor. Intente nuevamente.');



                            // Habilitar el botón nuevamente

                            $('button[type="submit"]').prop('disabled', false).text('Ingresar');

                        },

                        complete: function() {

                            // Esto se ejecuta siempre, haya éxito o error

                        }

                    });

                }

            });



            // Agregar método de validación personalizado para regex

            $.validator.addMethod("regex", function(value, element, regexp) {

                var re = new RegExp(regexp);

                return this.optional(element) || re.test(value);

            }, "Por favor introduce un valor válido.");

        });
    </script>

</body>



</html>